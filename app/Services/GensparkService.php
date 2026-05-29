<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\UsageStat;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * PHP wrapper around the public Genspark AI HTTP API + key dispatcher.
 *
 * Two upstream endpoints (reverse-engineered from @genspark/cli v1.0.18):
 *
 *   1. LLM proxy (OpenAI-compatible):
 *      POST https://www.genspark.ai/api/llm_proxy/v1/chat/completions
 *      Auth:  Authorization: Bearer gsk-…
 *
 *   2. Tool CLI (171 tools — image, video, audio, search, …):
 *      GET  https://www.genspark.ai/api/tool_cli/me              ← key validation
 *      GET  https://www.genspark.ai/api/tool_cli/opencode-config ← live model list
 *      GET  https://www.genspark.ai/api/tool_cli/tools           ← list every tool
 *      POST https://www.genspark.ai/api/tool_cli/<name>          ← invoke tool
 *      Auth:  X-Api-Key: gsk-…   (+ optional X-Project-ID)
 *
 * SaaS-mode features:
 *   • Per-user key (passed via setApiKey / setUser).
 *   • setUser($user) enables the key dispatcher: tries the user's primary
 *     key first; on 401/429/5xx retries with the next active key (if the
 *     user has auto_failover = true).
 *   • Every successful or failed call is recorded in usage_stats.
 *
 * The class can also be used in single-tenant dev mode (with the global
 * GENSPARK_API_KEY env var) by simply constructing without setUser().
 */
class GensparkService
{
    protected ?string $apiKey   = null;
    protected ?string $projectId = null;
    protected ?User   $user      = null;
    protected ?ApiKey $usedKey   = null;       // resolved by dispatcher
    protected string  $llmBaseUrl;
    protected string  $toolBaseUrl;
    protected int     $timeout;

    /** Last error captured by validateApiKey() — for surfacing to the user. */
    public ?string $lastError = null;

    public function __construct(
        ?string $apiKey  = null,
        ?string $baseUrl = null,
        ?int    $timeout = null,
    ) {
        $this->apiKey      = $apiKey ?: ((string) config('genspark.api_key') ?: null);
        $base              = rtrim($baseUrl ?: (string) config('genspark.base_url'), '/');
        $this->llmBaseUrl  = $base . '/api/llm_proxy/v1';
        $this->toolBaseUrl = $base . '/api/tool_cli';
        $this->timeout     = $timeout ?: (int) config('genspark.timeout', 60);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Configuration
    // ─────────────────────────────────────────────────────────────────────

    public function setApiKey(?string $key): self
    {
        $this->apiKey = $key ?: null;
        return $this;
    }

    public function setProjectId(?string $id): self
    {
        $this->projectId = $id ?: null;
        return $this;
    }

    /**
     * Bind the service to a User. Enables the key dispatcher and per-user
     * project defaulting and usage accounting.
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;
        if ($user) {
            $primary = $user->primaryApiKey();
            if ($primary) {
                $this->apiKey = $primary->key;
                $this->usedKey = $primary;
            }
            $proj = $user->defaultProject();
            if ($proj) $this->projectId = $proj->gsk_id;
        }
        return $this;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    public function usedKey(): ?ApiKey
    {
        return $this->usedKey;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  LLM PROXY
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Send a chat completion request.
     *
     * @return array{content:string,tokens:?int,model:string,raw:array,api_key_id:?int}
     */
    public function chat(array $messages, string $model, array $options = []): array
    {
        return $this->dispatch(function (string $key) use ($messages, $model, $options) {
            $payload = [
                'model'       => $model,
                'messages'    => $messages,
                'max_tokens'  => $options['max_tokens']  ?? (int)   config('genspark.max_tokens', 2048),
                'temperature' => $options['temperature'] ?? (float) config('genspark.temperature', 0.7),
                'stream'      => false,
            ];

            $response = Http::withToken($key)
                ->acceptJson()
                ->asJson()
                ->timeout($this->timeout)
                ->post($this->llmBaseUrl . '/chat/completions', $payload);

            if (! $response->successful()) {
                $this->throwHttpError($response, 'chat/completions');
            }

            $data    = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $tokens  = $data['usage']['total_tokens'] ?? null;

            return [
                'content'    => trim((string) $content),
                'tokens'     => $tokens,
                'model'      => $data['model'] ?? $model,
                'raw'        => $data,
                'api_key_id' => $this->usedKey?->id,
            ];
        }, tool: 'chat', model: $model);
    }

    public function generateTitle(string $firstUserMessage, string $model): string
    {
        try {
            $result = $this->chat(
                [
                    ['role' => 'system', 'content' => 'You generate a concise, 3-6 word title for a chat. No quotes. No trailing punctuation.'],
                    ['role' => 'user',   'content' => "Title this conversation:\n\n" . $firstUserMessage],
                ],
                $model,
                ['max_tokens' => 24, 'temperature' => 0.3]
            );

            $title = trim(str_replace(['"', "\n"], '', $result['content']));

            return $title !== '' ? mb_substr($title, 0, 80) : __('chat.conv_default_title');
        } catch (\Throwable $e) {
            return mb_substr($firstUserMessage, 0, 60) ?: __('chat.conv_default_title');
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  TOOL CLI
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Validate an API key against `GET /me`. Returns the account payload on
     * success or null on failure (with $this->lastError populated).
     */
    public function validateApiKey(): ?array
    {
        $this->lastError = null;

        if (! $this->isConfigured()) {
            $this->lastError = __('settings.api_key_empty');
            return null;
        }

        try {
            $response = Http::withHeaders([
                    'X-Api-Key' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->timeout(min($this->timeout, 20))
                ->get($this->toolBaseUrl . '/me');

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && (isset($data['email']) || isset($data['plan']))) {
                    return $data;
                }
                $this->lastError = 'Unexpected response from Genspark /me: ' . mb_substr($response->body(), 0, 200);
                return null;
            }

            $this->lastError = $this->extractErrorMessage($response, 'GET /me');
            Log::warning('Genspark /me validation failed', [
                'status' => $response->status(),
                'body'   => mb_substr($response->body(), 0, 500),
            ]);
            return null;
        } catch (\Throwable $e) {
            $this->lastError = 'Network error contacting Genspark: ' . $e->getMessage();
            Log::error('Genspark /me network error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function fetchModels(): ?array
    {
        if (! $this->isConfigured()) return null;

        try {
            $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
                ->timeout(min($this->timeout, 15))
                ->get($this->toolBaseUrl . '/opencode-config');

            if (! $response->successful()) return null;

            $data   = $response->json();
            $models = $data['provider']['genspark-llm-proxy']['models'] ?? null;
            if (! is_array($models)) return null;

            $out = [];
            foreach ($models as $id => $meta) {
                $out[$id] = [
                    'name'    => $meta['name']  ?? $id,
                    'context' => $meta['limit']['context'] ?? null,
                    'vision'  => in_array('image', $meta['modalities']['input'] ?? [], true),
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Invoke any Genspark tool by its CLI name. Goes through the dispatcher
     * so quota / rate-limit failures roll over to the next active key when
     * auto-failover is enabled on the user.
     */
    public function callTool(string $toolName, array $args = []): array
    {
        return $this->dispatch(function (string $key) use ($toolName, $args) {
            $headers = [
                'X-Api-Key'    => $key,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ];
            if ($this->projectId) {
                $headers['X-Project-ID'] = $this->projectId;
            }

            $response = Http::withHeaders($headers)
                ->timeout($this->timeout)
                ->post($this->toolBaseUrl . '/' . ltrim($toolName, '/'), $args);

            if (! $response->successful()) {
                $this->throwHttpError($response, "tool_cli/$toolName");
            }

            return $response->json() ?: [];
        }, tool: $toolName);
    }

    public function imageGeneration(string $query, array $opts = []): array
    {
        return $this->callTool('image_generation', array_merge(['query' => $query], $opts));
    }

    public function videoGeneration(string $query, string $model, array $opts = []): array
    {
        return $this->callTool('video_generation', array_merge(['query' => $query, 'model' => $model], $opts));
    }

    public function audioGeneration(string $query, string $model, array $opts = []): array
    {
        return $this->callTool('audio_generation', array_merge(['query' => $query, 'model' => $model], $opts));
    }

    public function webSearch(string $query, array $opts = []): array
    {
        return $this->callTool('web_search', array_merge(['q' => $query], $opts));
    }

    public function imageSearch(string $query, array $opts = []): array
    {
        return $this->callTool('image_search', array_merge(['query' => $query], $opts));
    }

    public function crawler(string $url, array $opts = []): array
    {
        return $this->callTool('crawler', array_merge(['url' => $url], $opts));
    }

    public function audioTranscribe(array $audioUrls, array $opts = []): array
    {
        return $this->callTool('audio_transcribe', array_merge(['audio_urls' => $audioUrls], $opts));
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Key dispatcher
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Wrap an upstream call so failures can roll over to the next active key
     * in the user's pool. Statuses 401, 402, 403, 429, 5xx trigger failover
     * (we mark the key as `disabled_until` for a cool-down period).
     *
     * The callable receives the chosen gsk-… string and must return its
     * result array (or throw RuntimeException on upstream error).
     */
    protected function dispatch(callable $call, string $tool, ?string $model = null): array
    {
        // Build the candidate list:
        //  - if bound to a user with auto_failover, all active keys in priority order
        //  - else just the single $this->apiKey
        $candidates = $this->candidateKeys();

        if (empty($candidates)) {
            throw new RuntimeException(__('chat.error_key_missing'));
        }

        $lastEx = null;
        foreach ($candidates as $cand) {
            $this->usedKey = $cand['model'];
            try {
                $result = $call($cand['key']);

                // Account a successful call.
                $this->record($tool, $model, $result['tokens'] ?? 0, false);
                if ($cand['model']) {
                    $cand['model']->forceFill([
                        'last_used_at' => now(),
                        'last_error'   => null,
                    ])->save();
                }
                return $result;
            } catch (RuntimeException $e) {
                $lastEx = $e;
                $msg    = $e->getMessage();
                $status = $this->extractStatus($msg);

                // Record the failure under this key.
                $this->record($tool, $model, 0, true);

                if ($cand['model']) {
                    $cand['model']->forceFill([
                        'last_error'     => mb_substr($msg, 0, 500),
                        'disabled_until' => $this->disableFor($status),
                    ])->save();
                }

                // If the status is not retryable, fail fast.
                if (! $this->isRetryable($status)) {
                    throw $e;
                }
                // else continue with the next candidate
            }
        }

        // All candidates failed.
        throw $lastEx ?? new RuntimeException('All API keys failed.');
    }

    /**
     * Return the ordered list of [['key' => 'gsk-…', 'model' => ApiKey|null]]
     * that the dispatcher will try in sequence.
     */
    protected function candidateKeys(): array
    {
        // No user bound — only the explicit / env key.
        if (! $this->user) {
            return $this->apiKey ? [['key' => $this->apiKey, 'model' => null]] : [];
        }

        $keys = $this->user->apiKeys()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('disabled_until')->orWhere('disabled_until', '<', now());
            })
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get();

        if ($keys->isEmpty()) {
            // Fallback to the legacy single-key column if present.
            $legacy = $this->user->getRawOriginal('genspark_api_key');
            if ($legacy && $this->user->genspark_api_key) {
                return [['key' => $this->user->genspark_api_key, 'model' => null]];
            }
            return [];
        }

        $list = [];
        foreach ($keys as $k) {
            $plain = $k->key;
            if (! $plain) continue;
            $list[] = ['key' => $plain, 'model' => $k];
            if (! $this->user->auto_failover) break;   // failover disabled → only the primary
        }
        return $list;
    }

    protected function record(string $tool, ?string $model, int $tokens, bool $isError): void
    {
        if (! $this->user) return;
        try {
            UsageStat::record(
                userId:    $this->user->id,
                apiKeyId:  $this->usedKey?->id,
                tool:      $tool,
                model:     $model,
                tokens:    $tokens,
                isError:   $isError,
            );
        } catch (\Throwable $e) {
            Log::warning('UsageStat::record failed', ['error' => $e->getMessage()]);
        }
    }

    /** Extract HTTP status from a message we built ourselves (`[ctx · HTTP 429] …`) */
    protected function extractStatus(string $msg): int
    {
        if (preg_match('/HTTP (\d{3})/', $msg, $m)) return (int) $m[1];
        return 0;
    }

    protected function isRetryable(int $status): bool
    {
        return in_array($status, [401, 402, 403, 429], true) || $status >= 500;
    }

    protected function disableFor(int $status): ?\Illuminate\Support\Carbon
    {
        return match (true) {
            $status === 429 => now()->addMinutes(2),    // rate-limit cool-down
            $status === 401,
            $status === 403 => now()->addHours(24),     // probably revoked → quarantine
            $status >= 500  => now()->addMinutes(5),    // upstream blip
            default         => null,
        };
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Error helpers
    // ─────────────────────────────────────────────────────────────────────

    protected function extractErrorMessage(Response $response, string $context): string
    {
        $status = $response->status();
        $body   = (string) $response->body();
        $json   = null;
        try { $json = $response->json(); } catch (\Throwable $e) {}

        $msg = null;
        if (is_array($json)) {
            $msg = $json['error']['message']
                ?? $json['error']
                ?? $json['detail']
                ?? $json['message']
                ?? $json['statusMessage']
                ?? null;
            if (is_array($msg)) $msg = json_encode($msg);
        }
        if (! $msg) {
            $msg = mb_substr(trim($body), 0, 300) ?: ('HTTP ' . $status);
        }

        $hint = match (true) {
            $status === 401 => __('settings.api_key_hint_401'),
            $status === 403 => __('settings.api_key_hint_403'),
            $status === 404 => __('settings.api_key_hint_404'),
            $status === 429 => __('settings.api_key_hint_429'),
            $status >= 500  => __('settings.api_key_hint_5xx'),
            default         => null,
        };

        return trim("[$context · HTTP $status] $msg" . ($hint ? ' — ' . $hint : ''));
    }

    protected function throwHttpError(Response $response, string $context): void
    {
        $msg = $this->extractErrorMessage($response, $context);
        Log::error('Genspark API error', [
            'context' => $context,
            'status'  => $response->status(),
            'body'    => mb_substr($response->body(), 0, 1000),
        ]);
        throw new RuntimeException($msg);
    }
}

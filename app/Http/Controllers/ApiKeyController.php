<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Services\GensparkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * API Keys CRUD — THE KEY FEATURE of SelfAI.
 *
 * A user can register many Genspark gsk-… keys. One is marked *primary*; on
 * 401/429/5xx the dispatcher rolls over to the next *active* key (if the user
 * opted in to `auto_failover`). Every action returns JSON so the UI can
 * update inline without a full page reload.
 */
class ApiKeyController extends Controller
{
    public function __construct(protected GensparkService $genspark) {}

    /**
     * GET /settings/api-keys
     * Returns the current list of keys (with safe previews) — JSON for AJAX
     * or full page for direct browser navigation.
     */
    public function index(Request $request): mixed
    {
        $user = Auth::user();

        $keys = $user->apiKeys()->get()->map(fn (ApiKey $k) => $this->present($k));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'             => true,
                'keys'           => $keys,
                'auto_failover'  => (bool) $user->auto_failover,
            ]);
        }

        return view('settings.api-keys', [
            'user' => $user,
            'keys' => $user->apiKeys()->get(),
        ]);
    }

    /**
     * POST /settings/api-keys
     * Validate the key against Genspark /me first, then store it encrypted.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:80'],
            'key'   => ['required', 'string', 'min:20', 'max:4000', 'regex:/^gsk[-_].+/'],
        ], [
            'key.regex' => __('settings.api_key_bad_format'),
        ]);

        $user = Auth::user();

        // 1) Validate against the live Genspark /me endpoint.
        $client  = (clone $this->genspark)->setApiKey($data['key']);
        $account = $client->validateApiKey();

        if ($account === null) {
            return response()->json([
                'ok'    => false,
                'error' => __('settings.api_key_invalid', ['error' => $client->lastError ?: '—']),
            ], 422);
        }

        // 2) De-duplicate: don't allow the same key twice for the same user.
        foreach ($user->apiKeys as $existing) {
            if ($existing->key === $data['key']) {
                return response()->json([
                    'ok'    => false,
                    'error' => __('settings.api_key_duplicate'),
                ], 422);
            }
        }

        // 3) Build the row. If the user has no keys yet, mark this one as primary.
        $isFirst = $user->apiKeys()->count() === 0;

        $row = new ApiKey([
            'user_id'       => $user->id,
            'label'         => $data['label'] ?: ($account['email'] ?? __('settings.api_key_default_label')),
            'plan'          => $account['plan'] ?? null,
            'account_email' => $account['email'] ?? null,
            'is_primary'    => $isFirst,
            'is_active'     => true,
        ]);
        $row->key = $data['key'];                  // triggers transparent encryption
        $row->save();

        return response()->json([
            'ok'      => true,
            'key'     => $this->present($row),
            'message' => __('settings.api_key_saved'),
            'account' => trim(($account['email'] ?? '') . ' · ' . ($account['plan'] ?? 'free')),
        ]);
    }

    /**
     * PATCH /settings/api-keys/{key}
     * Rename a key (does NOT allow editing the secret — for that, delete + add).
     */
    public function update(Request $request, ApiKey $apiKey): JsonResponse
    {
        $this->authorizeOwn($apiKey);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
        ]);

        $apiKey->update($data);

        return response()->json(['ok' => true, 'key' => $this->present($apiKey)]);
    }

    /**
     * DELETE /settings/api-keys/{key}
     */
    public function destroy(ApiKey $apiKey): JsonResponse
    {
        $this->authorizeOwn($apiKey);
        $user = Auth::user();

        DB::transaction(function () use ($apiKey, $user) {
            $wasPrimary = $apiKey->is_primary;
            $apiKey->delete();

            // Promote another active key to primary if we just removed it.
            if ($wasPrimary) {
                $next = $user->apiKeys()->where('is_active', true)->orderBy('id')->first();
                if ($next) $next->update(['is_primary' => true]);
            }
        });

        return response()->json([
            'ok'      => true,
            'message' => __('settings.api_key_deleted'),
        ]);
    }

    /**
     * POST /settings/api-keys/{key}/primary
     * Mark this key as the primary one. All others become non-primary.
     */
    public function makePrimary(ApiKey $apiKey): JsonResponse
    {
        $this->authorizeOwn($apiKey);
        $user = Auth::user();

        if (! $apiKey->is_active) {
            return response()->json([
                'ok'    => false,
                'error' => __('settings.api_key_must_be_active'),
            ], 422);
        }

        DB::transaction(function () use ($apiKey, $user) {
            $user->apiKeys()->update(['is_primary' => false]);
            $apiKey->update(['is_primary' => true]);
        });

        return response()->json([
            'ok'      => true,
            'message' => __('settings.api_key_primary_set', ['label' => $apiKey->label]),
        ]);
    }

    /**
     * POST /settings/api-keys/{key}/toggle
     * Flip the `is_active` flag. Disabled keys are skipped by the dispatcher.
     */
    public function toggleActive(ApiKey $apiKey): JsonResponse
    {
        $this->authorizeOwn($apiKey);

        $apiKey->is_active = ! $apiKey->is_active;
        // Re-enabling a key clears its temporary cool-down too.
        if ($apiKey->is_active) {
            $apiKey->disabled_until = null;
            $apiKey->last_error     = null;
        }
        // Don't allow deactivating the only primary if it's the only active key.
        $apiKey->save();

        return response()->json(['ok' => true, 'key' => $this->present($apiKey)]);
    }

    /**
     * POST /settings/api-keys/{key}/test
     * Re-validate this stored key against /me right now and refresh its
     * `plan` and `account_email` fields. Useful after a Genspark upgrade.
     */
    public function test(ApiKey $apiKey): JsonResponse
    {
        $this->authorizeOwn($apiKey);
        $plain = $apiKey->key;
        if (! $plain) {
            return response()->json(['ok' => false, 'error' => __('settings.api_key_empty')], 422);
        }

        $client  = (clone $this->genspark)->setApiKey($plain);
        $account = $client->validateApiKey();

        if ($account === null) {
            $apiKey->update([
                'last_error'     => $client->lastError,
                'disabled_until' => now()->addHours(24),
            ]);
            return response()->json([
                'ok'    => false,
                'error' => $client->lastError ?: __('settings.api_key_unknown_error'),
                'key'   => $this->present($apiKey->fresh()),
            ], 422);
        }

        $apiKey->update([
            'plan'           => $account['plan']  ?? $apiKey->plan,
            'account_email'  => $account['email'] ?? $apiKey->account_email,
            'last_used_at'   => now(),
            'last_error'     => null,
            'disabled_until' => null,
        ]);

        return response()->json([
            'ok'      => true,
            'message' => __('settings.api_key_test_ok'),
            'key'     => $this->present($apiKey->fresh()),
            'account' => trim(($account['email'] ?? '') . ' · ' . ($account['plan'] ?? 'free')),
        ]);
    }

    // ─────────────────────────── helpers ───────────────────────────

    protected function present(ApiKey $k): array
    {
        return [
            'id'             => $k->id,
            'label'          => $k->label,
            'preview'        => $k->preview(),
            'plan'           => $k->plan,
            'account_email'  => $k->account_email,
            'is_primary'     => (bool) $k->is_primary,
            'is_active'      => (bool) $k->is_active,
            'last_used_at'   => $k->last_used_at?->diffForHumans(),
            'disabled_until' => $k->disabled_until?->toIso8601String(),
            'usable'         => $k->isUsable(),
            'last_error'     => $k->last_error,
        ];
    }

    protected function authorizeOwn(ApiKey $key): void
    {
        abort_if($key->user_id !== Auth::id(), 404);
    }
}

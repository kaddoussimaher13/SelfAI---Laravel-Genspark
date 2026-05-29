<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\GensparkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

/**
 * Chat controller.
 *
 * Two response modes per action:
 *   • AJAX (JSON) — when the client sends `Accept: application/json` or
 *     `X-Requested-With: XMLHttpRequest`. Returns the new messages so the
 *     UI can append them without a full reload.
 *   • Classic redirect — for old-school form posts (kept as a fallback).
 *
 * Every upstream call goes through GensparkService::setUser($user), so the
 * key dispatcher will automatically fail over to the next active key when
 * the primary returns 401/402/403/429/5xx (if `auto_failover` is on).
 */
class ChatController extends Controller
{
    public function __construct(protected GensparkService $genspark) {}

    public function index(Request $request, $conversationId = null): View
    {
        $user = Auth::user();
        $this->genspark->setUser($user);

        $conversations = Conversation::orderByDesc('updated_at')
            ->get(['id', 'title', 'model', 'updated_at']);

        $currentConversation = $conversationId
            ? Conversation::with('messages')->findOrFail($conversationId)
            : null;

        $messages = $currentConversation ? $currentConversation->messages : collect();

        return view('chat.index', [
            'conversations'       => $conversations,
            'currentConversation' => $currentConversation,
            'messages'            => $messages,
            'availableModels'     => config('genspark.models', []),
            'defaultModel'        => $user->preferred_model ?: config('genspark.default_model'),
            'modelGroups'         => $this->groupModels(config('genspark.models', [])),
        ]);
    }

    /**
     * Send a chat message — accepts AJAX (returns JSON) or classic form (redirect).
     */
    public function send(Request $request, $conversationId = null): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:8000'],
            'model'   => ['nullable', 'string', 'max:100'],
        ]);

        $user  = Auth::user();
        $model = $data['model'] ?? ($user->preferred_model ?: config('genspark.default_model'));

        $this->genspark->setUser($user);

        if ($conversationId) {
            $conversation = Conversation::with('messages')->findOrFail($conversationId);
        } else {
            $conversation = Conversation::create([
                'title'         => __('chat.conv_default_title'),
                'model'         => $model,
                'system_prompt' => $user->system_prompt,
            ]);
        }

        $userMessage = $conversation->messages()->create([
            'role'    => 'user',
            'content' => $data['message'],
            'model'   => $model,
        ]);

        $assistantMessage = $this->generateReply($conversation, $model, $user);

        // Auto-title the conversation after the first exchange.
        $renamed = false;
        if ($conversation->title === __('chat.conv_default_title') && $conversation->messages()->count() >= 2) {
            $title = $this->genspark->generateTitle($data['message'], $model);
            $conversation->update(['title' => $title]);
            $renamed = true;
        }

        $conversation->update(['model' => $model]);
        $conversation->touch();

        if ($this->wantsJson($request)) {
            return response()->json([
                'ok'           => true,
                'conversation' => [
                    'id'    => $conversation->id,
                    'title' => $conversation->title,
                    'model' => $conversation->model,
                ],
                'renamed'     => $renamed,
                'user_msg'    => $this->presentMessage($userMessage),
                'assistant'   => $this->presentMessage($assistantMessage),
                'used_key_id' => $this->genspark->usedKey()?->id,
            ]);
        }

        return redirect()->route('chat.show', $conversation->id);
    }

    public function regenerate(Request $request, $conversationId): JsonResponse|RedirectResponse
    {
        $user         = Auth::user();
        $conversation = Conversation::with('messages')->findOrFail($conversationId);
        $this->genspark->setUser($user);

        $last = $conversation->messages()->latest('id')->first();
        if ($last && $last->role === 'assistant') {
            $last->delete();
        }

        $model     = $conversation->model ?: ($user->preferred_model ?: config('genspark.default_model'));
        $assistant = $this->generateReply($conversation->fresh('messages'), $model, $user);
        $conversation->touch();

        if ($this->wantsJson($request)) {
            return response()->json([
                'ok'        => true,
                'assistant' => $this->presentMessage($assistant),
            ]);
        }

        return redirect()->route('chat.show', $conversation->id);
    }

    public function editMessage(Request $request, $conversationId, $messageId): JsonResponse|RedirectResponse
    {
        $user         = Auth::user();
        $conversation = Conversation::with('messages')->findOrFail($conversationId);
        $message      = Message::findOrFail($messageId);

        abort_if($message->conversation_id !== $conversation->id, 404);
        abort_if($message->role !== 'user', 422);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:8000'],
        ]);

        $this->genspark->setUser($user);

        $message->update(['content' => $data['content']]);
        $conversation->messages()->where('id', '>', $message->id)->delete();

        $model     = $conversation->model ?: ($user->preferred_model ?: config('genspark.default_model'));
        $assistant = $this->generateReply($conversation->fresh('messages'), $model, $user);
        $conversation->touch();

        if ($this->wantsJson($request)) {
            return response()->json([
                'ok'        => true,
                'message'   => $this->presentMessage($message->fresh()),
                'assistant' => $this->presentMessage($assistant),
            ]);
        }

        return redirect()->route('chat.show', $conversation->id);
    }

    // ─────────────────────────── helpers ───────────────────────────

    protected function generateReply(Conversation $conversation, string $model, $user): Message
    {
        try {
            $result = $this->genspark->chat(
                $conversation->toApiMessages(),
                $model,
                $this->userOptions($user)
            );

            return $conversation->messages()->create([
                'role'    => 'assistant',
                'content' => $result['content'] !== '' ? $result['content'] : __('chat.msg_empty_response'),
                'tokens'  => $result['tokens'],
                'model'   => $result['model'],
            ]);
        } catch (Throwable $e) {
            return $conversation->messages()->create([
                'role'    => 'assistant',
                'content' => __('chat.error_api', ['error' => $e->getMessage()]),
                'model'   => $model,
            ]);
        }
    }

    protected function userOptions($user): array
    {
        $opts = [];
        if ($user->temperature !== null) { $opts['temperature'] = (float) $user->temperature; }
        if ($user->max_tokens  !== null) { $opts['max_tokens']  = (int)   $user->max_tokens;  }
        return $opts;
    }

    protected function presentMessage(Message $m): array
    {
        return [
            'id'      => $m->id,
            'role'    => $m->role,
            'content' => $m->content,
            'tokens'  => $m->tokens,
            'model'   => $m->model,
        ];
    }

    protected function wantsJson(Request $r): bool
    {
        return $r->wantsJson() || $r->ajax() || str_contains((string) $r->header('Accept'), 'application/json');
    }

    /**
     * Group the model id list by family for prettier select boxes.
     * Returns ['Claude' => [id => label, …], 'GPT' => …, 'Other' => …].
     */
    protected function groupModels(array $models): array
    {
        $groups = [];
        foreach ($models as $id => $label) {
            $family = match (true) {
                str_starts_with($id, 'claude') => 'Claude',
                str_starts_with($id, 'gpt')    => 'GPT',
                str_starts_with($id, 'deep-seek') || str_starts_with($id, 'deepseek') => 'DeepSeek',
                str_starts_with($id, 'gemini') => 'Gemini',
                str_starts_with($id, 'llama')  => 'Llama',
                str_starts_with($id, 'grok')   => 'Grok',
                str_starts_with($id, 'qwen')   => 'Qwen',
                str_starts_with($id, 'kimi')   => 'Kimi',
                str_starts_with($id, 'mistral')=> 'Mistral',
                default                        => 'Other',
            };
            $groups[$family][$id] = $label;
        }
        return $groups;
    }
}

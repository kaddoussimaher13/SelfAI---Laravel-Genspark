<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(): View
    {
        $conversations = Conversation::withCount('messages')
            ->orderByDesc('updated_at')
            ->get();

        return view('conversations.index', compact('conversations'));
    }

    public function update(Request $request, $conversationId): JsonResponse|RedirectResponse
    {
        $conversation = Conversation::findOrFail($conversationId);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
        ]);

        $conversation->update($data);

        if ($this->wantsJson($request)) {
            return response()->json([
                'ok'    => true,
                'id'    => $conversation->id,
                'title' => $conversation->title,
            ]);
        }

        return back()->with('status', 'conversation-renamed');
    }

    public function destroy(Request $request, $conversationId): JsonResponse|RedirectResponse
    {
        $conversation = Conversation::findOrFail($conversationId);
        $conversation->delete();

        if ($this->wantsJson($request)) {
            return response()->json(['ok' => true, 'id' => (int) $conversationId]);
        }

        return redirect()
            ->route('chat.index')
            ->with('status', 'conversation-deleted');
    }

    protected function wantsJson(Request $r): bool
    {
        return $r->wantsJson() || $r->ajax() || str_contains((string) $r->header('Accept'), 'application/json');
    }
}

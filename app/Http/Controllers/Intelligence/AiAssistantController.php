<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\Intelligence\AiManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    protected AiManager $aiManager;

    public function __construct(AiManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $conversations = AiConversation::where('user_id', $user->id)->latest()->get();

        $activeConversation = null;
        if ($request->has('c')) {
            $activeConversation = AiConversation::with('messages')
                ->where('user_id', $user->id)
                ->where('id', $request->input('c'))
                ->first();
        }

        if (!$activeConversation && $conversations->isNotEmpty()) {
            $activeConversation = $conversations->first()->load('messages');
        }

        $activeProvider = $this->aiManager->getProvider()->getProviderName();

        return view('intelligence.assistant', compact('conversations', 'activeConversation', 'activeProvider'));
    }

    public function newConversation(): JsonResponse
    {
        $user = Auth::user();
        $conversation = AiConversation::create([
            'user_id' => $user->id,
            'title' => 'Percakapan ' . now()->translatedFormat('d M H:i'),
        ]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'redirect' => route('intelligence.assistant.index', ['c' => $conversation->id]),
        ]);
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'conversation_id' => 'nullable|exists:ai_conversations,id',
        ]);

        $user = Auth::user();
        $prompt = trim($request->input('message'));

        // Resolve or create conversation
        if ($request->filled('conversation_id')) {
            $conversation = AiConversation::where('id', $request->conversation_id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $conversation = AiConversation::create([
                'user_id' => $user->id,
                'title' => mb_substr($prompt, 0, 40) . '...',
            ]);
        }

        // Store user message
        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $prompt,
            'provider' => $this->aiManager->getProvider()->getProviderName(),
        ]);

        // Build history
        $history = $conversation->messages()
            ->latest()
            ->take(6)
            ->get()
            ->reverse()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        // Role-aware context building
        $context = [
            'user_role' => $user->roles->pluck('name')->first() ?? 'Staff',
            'user_name' => $user->name,
            'has_financial_access' => $user->can('intelligence.financial'),
        ];

        // Process query via AI Manager
        $replyData = $this->aiManager->chat($prompt, $context, $history);

        // Store assistant message
        $botMessage = AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $replyData['content'],
            'grounding_data' => $replyData['grounding'] ?? null,
            'tokens_used' => $replyData['tokens_used'] ?? 0,
            'provider' => $replyData['provider'] ?? 'local_heuristic',
        ]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'reply' => $botMessage->content,
            'grounding' => $botMessage->grounding_data,
            'provider' => $botMessage->provider,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\AI\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ChatbotController extends Controller
{
    public function __construct(private ChatbotService $chatbot) {}

    public function index(): View
    {
        return view('ai.chatbot');
    }

    /**
     * Terima pesan user, kembalikan jawaban AI.
     */
    public function message(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:500'],
        ]);

        // Rate limit: 20 pesan per menit per user
        $rateLimitKey = 'chatbot_rate_' . auth()->id();
        $count        = (int) Cache::get($rateLimitKey, 0);

        if ($count >= 20) {
            return response()->json([
                'error' => 'Terlalu banyak pesan. Tunggu sebentar ya!',
            ], 429);
        }

        Cache::put($rateLimitKey, $count + 1, now()->addMinute());

        try {
            $reply = $this->chatbot->reply(auth()->user(), $request->message);

            return response()->json(['reply' => $reply]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 503);
        }
    }

    /**
     * Reset riwayat percakapan.
     */
    public function reset(): JsonResponse
    {
        $this->chatbot->clearHistory(auth()->id());

        return response()->json([
            'reply' => 'Halo lagi! Percakapan baru dimulai. Ada yang bisa saya bantu?',
        ]);
    }
}
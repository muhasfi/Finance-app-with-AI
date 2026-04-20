<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AI\ChatbotService;
use App\Services\AI\InsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ChatbotService $chatbot,
        private InsightService $insight
    ) {}

    /**
     * Kirim pesan ke chatbot Fina.
     */
    public function chat(Request $request): JsonResponse
    {
        if (! config('services.gemini.api_key')) {
            return $this->error('Fitur AI belum diaktifkan.', 503);
        }

        $request->validate([
            'message'         => ['required', 'string', 'max:500'],
            'conversation_id' => ['nullable', 'string', 'max:100'],
        ]);

        $user           = $request->user();
        $conversationId = $request->get('conversation_id', 'api_' . $user->id);

        try {
            $reply = $this->chatbot->chat($user, $request->message, $conversationId);

            return $this->success([
                'reply'           => $reply,
                'conversation_id' => $conversationId,
            ]);
        } catch (\Exception $e) {
            return $this->error('Gagal menghubungi AI. Coba lagi.', 503);
        }
    }

    /**
     * Reset percakapan chatbot.
     */
    public function resetChat(Request $request): JsonResponse
    {
        $request->validate([
            'conversation_id' => ['nullable', 'string'],
        ]);

        $conversationId = $request->get('conversation_id', 'api_' . $request->user()->id);
        $this->chatbot->clearHistory($conversationId);

        return $this->success(null, 'Percakapan berhasil direset.');
    }

    /**
     * Ambil insight bulanan.
     */
    public function insights(Request $request): JsonResponse
    {
        if (! config('services.gemini.api_key')) {
            return $this->error('Fitur AI belum diaktifkan.', 503);
        }

        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);
        $user  = $request->user();

        $cacheKey = "ai_insight_{$user->id}_{$year}_{$month}";
        $cached   = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if ($cached) {
            return $this->success([
                'insight' => $cached,
                'cached'  => true,
                'month'   => $month,
                'year'    => $year,
            ]);
        }

        try {
            // Panggil method yang benar sesuai InsightService
            $insightData = [
                'summary'   => $this->insight->monthlySummary($user, $month, $year),
                'tips'      => $this->insight->savingTips($user),
                'anomalies' => $this->insight->detectAnomalies($user),
            ];

            \Illuminate\Support\Facades\Cache::put($cacheKey, $insightData, now()->addHours(24));

            return $this->success([
                'insight' => $insightData,
                'cached'  => false,
                'month'   => $month,
                'year'    => $year,
            ]);
        } catch (\Exception $e) {
            return $this->error('Gagal generate insight. Coba lagi.', 503);
        }
    }

    /**
     * Trigger generate insight di background (async).
     */
    public function generateInsight(Request $request): JsonResponse
    {
        if (! config('services.gemini.api_key')) {
            return $this->error('Fitur AI belum diaktifkan.', 503);
        }

        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        \App\Jobs\GenerateInsightJob::dispatch($request->user(), $month, $year)->onQueue('ai');

        return $this->success(null, 'Insight sedang diproses. Cek kembali dalam beberapa detik.');
    }
}

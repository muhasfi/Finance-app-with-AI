<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInsightJob;
use App\Services\AI\InsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AiInsightController extends Controller
{
    public function __construct(private InsightService $insightService) {}

    /**
     * Halaman utama insight AI.
     */
    public function index(): View
    {
        $user     = auth()->user();
        $month    = now()->month;
        $year     = now()->year;
        $cacheKey = "ai_insight_{$user->id}_{$year}_{$month}";
        $insight  = Cache::get($cacheKey);

        return view('ai.insights', compact('insight', 'month', 'year'));
    }

    /**
     * Dispatch job generate insight baru.
     * Dipanggil via AJAX saat user klik "Generate" atau "Refresh".
     */
    public function generate(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        // Cek apakah sedang diproses (flag di cache selama 2 menit)
        $processingKey = "ai_insight_processing_{$user->id}_{$year}_{$month}";
        if (Cache::get($processingKey)) {
            return response()->json([
                'status'  => 'processing',
                'message' => 'Insight sedang diproses, mohon tunggu...',
            ]);
        }

        Cache::put($processingKey, true, now()->addMinutes(2));
        GenerateInsightJob::dispatch($user, $month, $year)->onQueue('ai');

        return response()->json([
            'status'  => 'queued',
            'message' => 'Sedang memproses insight AI...',
        ]);
    }

    /**
     * Polling — cek apakah insight sudah siap.
     */
    public function status(Request $request): JsonResponse
    {
        $user     = auth()->user();
        $month    = $request->integer('month', now()->month);
        $year     = $request->integer('year', now()->year);
        $cacheKey = "ai_insight_{$user->id}_{$year}_{$month}";
        $insight  = Cache::get($cacheKey);

        if (! $insight) {
            return response()->json(['ready' => false]);
        }

        return response()->json([
            'ready'   => true,
            'insight' => $insight,
        ]);
    }

    /**
     * Endpoint AJAX untuk saran hemat (dipanggil di dashboard).
     */
    public function tips(): JsonResponse
    {
        $user     = auth()->user();
        $cacheKey = "ai_tips_{$user->id}_" . now()->format('Y_m');

        $tips = Cache::remember($cacheKey, now()->addHours(6), function () use ($user) {
            return $this->insightService->savingTips($user);
        });

        return response()->json(['tips' => $tips]);
    }

    /**
     * Endpoint AJAX untuk anomali (dipanggil di dashboard).
     */
    public function anomalies(): JsonResponse
    {
        $user     = auth()->user();
        $cacheKey = "ai_anomalies_{$user->id}_" . now()->format('Y_m_d');

        $anomalies = Cache::remember($cacheKey, now()->addHours(3), function () use ($user) {
            return $this->insightService->detectAnomalies($user);
        });

        return response()->json(['anomalies' => $anomalies]);
    }
}
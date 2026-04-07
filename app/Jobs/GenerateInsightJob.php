<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AI\InsightService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateInsightJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(
        private User $user,
        private int $month,
        private int $year,
    ) {}

    public function handle(InsightService $service): void
    {
        try {
            $insight = [
                'summary'      => $service->monthlySummary($this->user, $this->month, $this->year),
                'anomalies'    => $service->detectAnomalies($this->user),
                'tips'         => $service->savingTips($this->user),
                'generated_at' => now()->toIso8601String(),
                'month'        => $this->month,
                'year'         => $this->year,
            ];

            // Cache 24 jam
            Cache::put(
                $this->cacheKey(),
                $insight,
                now()->addHours(24)
            );

            Log::info('Insight generated', [
                'user_id' => $this->user->id,
                'month'   => $this->month,
                'year'    => $this->year,
            ]);
        } catch (\Exception $e) {
            Log::error('GenerateInsightJob failed', [
                'user_id' => $this->user->id,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function cacheKey(): string
    {
        return "ai_insight_{$this->user->id}_{$this->year}_{$this->month}";
    }
}
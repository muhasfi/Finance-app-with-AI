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
use Throwable;

class GenerateInsightJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maksimal percobaan sebelum job dianggap gagal permanen.
     */
    public int $tries = 4;

    /**
     * Timeout per attempt (detik).
     * 3 panggilan Gemini x 30 detik masing-masing + buffer.
     */
    public int $timeout = 120;

    /**
     * Jangan retry jika exception ini terjadi (error permanen).
     */
    public array $dontRetry = [];

    public function __construct(
        private readonly User $user,
        private readonly int  $month,
        private readonly int  $year,
    ) {}

    // -------------------------------------------------------------------------
    // Retry Strategy
    // -------------------------------------------------------------------------

    /**
     * Jeda antar retry: 30s → 60s → 120s (exponential backoff).
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    /**
     * Batas waktu absolut job boleh di-retry.
     */
    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(15);
    }

    // -------------------------------------------------------------------------
    // Main Handler
    // -------------------------------------------------------------------------

    public function handle(InsightService $service): void
    {
        Log::info('[InsightJob] Starting', $this->context());

        $insight = [
            'summary'      => $service->monthlySummary($this->user, $this->month, $this->year),
            'anomalies'    => $service->detectAnomalies($this->user),
            'tips'         => $service->savingTips($this->user),
            'generated_at' => now()->toIso8601String(),
            'month'        => $this->month,
            'year'         => $this->year,
        ];

        Cache::put($this->insightCacheKey(), $insight, now()->addHours(24));

        // Hapus processing flag — user boleh request lagi setelah ini
        $this->clearProcessingFlag();

        Log::info('[InsightJob] Done', $this->context());
    }

    // -------------------------------------------------------------------------
    // Failure Handler
    // -------------------------------------------------------------------------

    /**
     * Dipanggil setiap kali attempt gagal.
     * Bedakan: error sementara (503) vs permanen.
     */
    public function failed(Throwable $e): void
    {
        // Selalu bersihkan processing flag agar user bisa coba lagi manual
        $this->clearProcessingFlag();

        $isFinal = $this->attempts() >= $this->tries;

        Log::error('[InsightJob] Failed', array_merge($this->context(), [
            'attempt'   => $this->attempts(),
            'is_final'  => $isFinal,
            'error'     => $e->getMessage(),
            'exception' => get_class($e),
        ]));

        // Kalau sudah final fail, simpan error state ke cache
        // supaya frontend bisa tampilkan pesan yang tepat
        if ($isFinal) {
            Cache::put(
                $this->errorCacheKey(),
                [
                    'message'    => $this->friendlyErrorMessage($e),
                    'failed_at'  => now()->toIso8601String(),
                ],
                now()->addMinutes(30) // error state cukup 30 menit
            );
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function insightCacheKey(): string
    {
        return "ai_insight_{$this->user->id}_{$this->year}_{$this->month}";
    }

    public function errorCacheKey(): string
    {
        return "ai_insight_error_{$this->user->id}_{$this->year}_{$this->month}";
    }

    private function processingCacheKey(): string
    {
        return "ai_insight_processing_{$this->user->id}_{$this->year}_{$this->month}";
    }

    private function clearProcessingFlag(): void
    {
        Cache::forget($this->processingCacheKey());
    }

    /**
     * Context array untuk semua log entry.
     */
    private function context(): array
    {
        return [
            'user_id' => $this->user->id,
            'month'   => $this->month,
            'year'    => $this->year,
        ];
    }

    /**
     * Terjemahkan exception ke pesan yang ramah untuk ditampilkan ke user.
     */
    private function friendlyErrorMessage(Throwable $e): string
    {
        $msg = $e->getMessage();

        return match (true) {
            str_contains($msg, '503'), str_contains($msg, 'UNAVAILABLE')
                => 'Layanan AI sedang sibuk. Coba generate ulang beberapa saat lagi.',
            str_contains($msg, '429'), str_contains($msg, 'quota')
                => 'Kuota AI hari ini sudah habis. Coba lagi besok.',
            str_contains($msg, '401'), str_contains($msg, 'API key')
                => 'Konfigurasi AI bermasalah. Hubungi administrator.',
            default
                => 'Gagal membuat insight. Silakan coba lagi.',
        };
    }
}
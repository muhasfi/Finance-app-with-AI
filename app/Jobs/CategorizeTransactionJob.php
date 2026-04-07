<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\AI\CategorizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CategorizeTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 15; // detik antar retry

    public function __construct(private Transaction $transaction) {}

    public function handle(CategorizationService $service): void
    {
        // Skip jika sudah punya kategori manual (bukan dari AI)
        if ($this->transaction->category_id && ! $this->transaction->ai_categorized) {
            return;
        }

        // Skip jika tidak ada deskripsi untuk dianalisis
        if (blank($this->transaction->note)) {
            return;
        }

        try {
            $applied = $service->applyToTransaction($this->transaction, threshold: 70);

            Log::info('AI categorization done', [
                'transaction_id' => $this->transaction->id,
                'applied'        => $applied,
            ]);
        } catch (\Exception $e) {
            Log::error('CategorizeTransactionJob failed', [
                'transaction_id' => $this->transaction->id,
                'error'          => $e->getMessage(),
            ]);
            throw $e; // lempar ulang agar queue retry
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('CategorizeTransactionJob permanently failed', [
            'transaction_id' => $this->transaction->id,
            'error'          => $e->getMessage(),
        ]);
    }
}
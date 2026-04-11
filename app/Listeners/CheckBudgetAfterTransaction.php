<?php

namespace App\Listeners;

use App\Events\BudgetAlertEvent;
use App\Models\Budget;
use App\Models\Transaction;
use App\Enums\TransactionType;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckBudgetAfterTransaction implements ShouldQueue
{
    /**
     * Dipanggil setelah transaksi pengeluaran baru dibuat.
     * Cek apakah ada budget yang terpengaruh dan perlu dikirim alert.
     */
    public function handle(object $event): void
    {
        // Ambil transaksi dari event (TransactionCreated)
        $transaction = $event->transaction ?? null;
        if (! $transaction) return;

        // Hanya cek untuk pengeluaran
        if ($transaction->type !== TransactionType::Expense) return;
        if (! $transaction->category_id) return;

        $userId = $transaction->account->user_id;
        $month  = $transaction->date->month;
        $year   = $transaction->date->year;

        // Cari budget yang sesuai
        $budget = Budget::where('user_id', $userId)
            ->where('category_id', $transaction->category_id)
            ->where('month', $month)
            ->where('year', $year)
            ->where('is_active', true)
            ->with('category:id,name,color', 'user')
            ->first();

        if (! $budget) return;

        $percentage = $budget->percentage();

        // Kirim alert jika mencapai threshold
        // Hindari spam — cek apakah sudah pernah kirim notif untuk level ini
        $cacheKey = "budget_alert_{$budget->id}_" . $this->getAlertLevel($percentage, $budget->alert_threshold);

        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) return;

        if ($percentage >= $budget->alert_threshold) {
            broadcast(new BudgetAlertEvent($budget, $percentage));

            // Cache selama 1 jam agar tidak spam notif
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHour());
        }
    }

    private function getAlertLevel(float $pct, int $threshold): string
    {
        if ($pct >= 100) return 'exceeded';
        if ($pct >= $threshold) return 'danger';
        return 'normal';
    }
}

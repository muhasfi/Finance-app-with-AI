<?php

namespace App\Observers;

use App\Events\TransactionCreated;
use App\Models\AuditLog;
use App\Models\Transaction;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->updateBalance($transaction);

        AuditLog::record(
            'transaction.created',
            "Transaksi baru: {$transaction->type->label()} Rp " . number_format($transaction->amount, 0, ',', '.'),
            $transaction
        );

        // Dispatch event agar CheckBudgetAfterTransaction listener bisa cek budget
        TransactionCreated::dispatch($transaction);
    }

    public function updated(Transaction $transaction): void
    {
        if ($transaction->wasChanged(['amount', 'type', 'account_id'])) {
            $transaction->account->recalculateBalance();

            if ($transaction->wasChanged('account_id')) {
                $oldId = $transaction->getOriginal('account_id');
                \App\Models\Account::find($oldId)?->recalculateBalance();
            }
        }

        if ($transaction->wasChanged() && ! $transaction->wasChanged(['ai_categorized', 'ai_confidence', 'category_id'])) {
            AuditLog::record(
                'transaction.updated',
                "Transaksi diperbarui: Rp " . number_format($transaction->amount, 0, ',', '.'),
                $transaction
            );
        }
    }

    public function deleted(Transaction $transaction): void
    {
        $transaction->account->recalculateBalance();

        AuditLog::record(
            'transaction.deleted',
            "Transaksi dihapus: {$transaction->type->label()} Rp " . number_format($transaction->amount, 0, ',', '.'),
            $transaction
        );
    }

    public function restored(Transaction $transaction): void
    {
        $transaction->account->recalculateBalance();
    }

    private function updateBalance(Transaction $transaction): void
    {
        match ($transaction->type->value) {
            'income'  => $transaction->account->increment('balance', $transaction->amount),
            'expense' => $transaction->account->decrement('balance', $transaction->amount),
            default   => null,
        };
    }
}

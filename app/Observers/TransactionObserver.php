<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\AuditLog;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        $this->updateBalance($transaction);

        AuditLog::record(
            'create',
            'Menambahkan transaksi',
            $transaction
        );
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

        AuditLog::record(
            'update',
            'Mengubah transaksi',
            $transaction
        );
    }

    public function deleted(Transaction $transaction): void
    {
        $transaction->account->recalculateBalance();

        AuditLog::record(
            'delete',
            'Menghapus transaksi',
            $transaction
        );
    }

    public function restored(Transaction $transaction): void
    {
        $transaction->account->recalculateBalance();

        AuditLog::record(
            'restore',
            'Restore transaksi',
            $transaction
        );
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
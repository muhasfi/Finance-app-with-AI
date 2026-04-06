<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransactionService
{
    public function create(array $data, ?UploadedFile $receipt = null): Transaction
    {
        return DB::transaction(function () use ($data, $receipt) {
            if ($receipt) {
                $data['receipt_path'] = $receipt->store('receipts/' . auth()->id(), 'private');
            }

            $data['amount_base'] = $data['amount'];
            $data['currency']    = auth()->user()->currency;

            return Transaction::create($data);
        });
    }

    public function createTransfer(Account $from, Account $to, float $amount, string $date, ?string $note = null): array
    {
        abort_if($from->user_id !== auth()->id(), 403);
        abort_if($to->user_id !== auth()->id(), 403);
        abort_if($from->id === $to->id, 422, 'Akun asal dan tujuan tidak boleh sama.');
        abort_if($from->balance < $amount, 422, 'Saldo tidak mencukupi.');

        return DB::transaction(function () use ($from, $to, $amount, $date, $note) {
            $out = Transaction::create([
                'account_id'  => $from->id,
                'type'        => TransactionType::Transfer,
                'amount'      => $amount,
                'amount_base' => $amount,
                'currency'    => $from->currency,
                'date'        => $date,
                'note'        => $note ?? "Transfer ke {$to->name}",
            ]);

            $in = Transaction::create([
                'account_id'      => $to->id,
                'type'            => TransactionType::Transfer,
                'amount'          => $amount,
                'amount_base'     => $amount,
                'currency'        => $to->currency,
                'date'            => $date,
                'note'            => $note ?? "Transfer dari {$from->name}",
                'transfer_pair_id' => $out->id,
            ]);

            $out->update(['transfer_pair_id' => $in->id]);

            $from->decrement('balance', $amount);
            $to->increment('balance', $amount);

            return [$out, $in];
        });
    }

    public function update(Transaction $transaction, array $data, ?UploadedFile $receipt = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $data, $receipt) {
            if ($receipt) {
                if ($transaction->receipt_path) {
                    Storage::disk('private')->delete($transaction->receipt_path);
                }
                $data['receipt_path'] = $receipt->store('receipts/' . auth()->id(), 'private');
            }

            $transaction->update($data);
            return $transaction->fresh();
        });
    }

    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            if ($transaction->transfer_pair_id) {
                Transaction::find($transaction->transfer_pair_id)?->delete();
            }
            $transaction->delete();
        });
    }

    public function monthlySummary(string $userId, int $month, int $year): array
    {
        $base    = Transaction::forUser($userId)->whereMonth('date', $month)->whereYear('date', $year);
        $income  = (clone $base)->income()->sum('amount_base');
        $expense = (clone $base)->expense()->sum('amount_base');

        return [
            'income'  => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }

    public function trendChart(string $userId): array
    {
        return collect(range(5, 0))->map(function ($i) use ($userId) {
            $date = now()->subMonths($i);
            $base = Transaction::forUser($userId)->whereMonth('date', $date->month)->whereYear('date', $date->year);

            return [
                'label'   => $date->format('M Y'),
                'income'  => (clone $base)->income()->sum('amount_base'),
                'expense' => (clone $base)->expense()->sum('amount_base'),
            ];
        })->values()->toArray();
    }

    public function expenseByCategory(string $userId, int $month, int $year): array
    {
        return Transaction::forUser($userId)
            ->expense()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->selectRaw('category_id, SUM(amount_base) as total')
            ->groupBy('category_id')
            ->with('category:id,name,color')
            ->get()
            ->map(fn($row) => [
                'label'  => $row->category?->name ?? 'Tanpa kategori',
                'color'  => $row->category?->color ?? '#6b7280',
                'amount' => (float) $row->total,
            ])
            ->sortByDesc('amount')
            ->values()
            ->toArray();
    }
}

<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'account_id', 'category_id', 'transfer_pair_id', 'recurring_plan_id',
        'type', 'amount', 'amount_base', 'currency',
        'date', 'note', 'tags', 'receipt_path',
    ];

    protected function casts(): array
    {
        return [
            'type'        => TransactionType::class,
            'amount'      => 'decimal:2',
            'amount_base' => 'decimal:2',
            'date'        => 'date',
            'tags'        => 'array',
        ];
    }

    // ── Relations ────────────────────────────────────────────────────

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transferPair(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transfer_pair_id');
    }

    public function recurringPlan(): BelongsTo
    {
        return $this->belongsTo(RecurringPlan::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeIncome($query)
    {
        return $query->where('type', TransactionType::Income);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', TransactionType::Expense);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                     ->whereYear('date', now()->year);
    }

    public function scopeInDateRange($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('account', fn($q) => $q->where('user_id', $userId));
    }

    // ── Accessors ────────────────────────────────────────────────────

    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->type === TransactionType::Expense ? '-' : '+';
        return $sign . 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}

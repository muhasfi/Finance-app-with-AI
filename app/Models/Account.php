<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'type', 'balance', 'currency',
        'color', 'icon', 'description', 'is_active',
        'credit_limit', 'due_date_day',
    ];

    protected function casts(): array
    {
        return [
            'type'         => AccountType::class,
            'balance'      => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'is_active'    => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurringPlans(): HasMany
    {
        return $this->hasMany(RecurringPlan::class);
    }

    public function recalculateBalance(): void
    {
        $income  = $this->transactions()->where('type', 'income')->sum('amount');
        $expense = $this->transactions()->where('type', 'expense')->sum('amount');
        $this->update(['balance' => $income - $expense]);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'Rp ' . number_format($this->balance, 0, ',', '.');
    }
}

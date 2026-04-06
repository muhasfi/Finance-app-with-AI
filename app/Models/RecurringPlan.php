<?php

namespace App\Models;

use App\Enums\RecurringFrequency;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringPlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'account_id', 'category_id', 'name', 'type',
        'amount', 'frequency', 'start_date',
        'next_run_at', 'ends_at', 'note', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type'        => TransactionType::class,
            'frequency'   => RecurringFrequency::class,
            'amount'      => 'decimal:2',
            'start_date'  => 'date',
            'next_run_at' => 'date',
            'ends_at'     => 'date',
            'is_active'   => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', true)
                     ->whereDate('next_run_at', '<=', today());
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'category_id',
        'amount', 'month', 'year',
        'alert_threshold', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount'          => 'decimal:2',
            'is_active'       => 'boolean',
            'alert_threshold' => 'integer',
        ];
    }

    // ── Relations ────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Computed helpers ─────────────────────────────────────────────

    /**
     * Hitung total pengeluaran di kategori ini bulan ini.
     */
    public function spent(): float
    {
        return (float) Transaction::whereHas('account', fn($q) =>
                $q->where('user_id', $this->user_id)
            )
            ->where('category_id', $this->category_id)
            ->where('type', 'expense')
            ->whereMonth('date', $this->month)
            ->whereYear('date', $this->year)
            ->sum('amount_base');
    }

    /**
     * Sisa anggaran.
     */
    public function remaining(): float
    {
        return max(0, $this->amount - $this->spent());
    }

    /**
     * Persentase terpakai (0-100+).
     */
    public function percentage(): float
    {
        if ($this->amount <= 0) return 0;
        return round(($this->spent() / $this->amount) * 100, 1);
    }

    /**
     * Status budget: normal, warning, danger, exceeded.
     */
    public function status(): string
    {
        $pct = $this->percentage();

        if ($pct >= 100)                         return 'exceeded';
        if ($pct >= $this->alert_threshold)      return 'danger';
        if ($pct >= $this->alert_threshold - 15) return 'warning';

        return 'normal';
    }

    /**
     * Warna Bootstrap sesuai status.
     */
    public function statusColor(): string
    {
        return match ($this->status()) {
            'exceeded' => 'danger',
            'danger'   => 'danger',
            'warning'  => 'warning',
            default    => 'success',
        };
    }

    /**
     * Label status dalam Bahasa Indonesia.
     */
    public function statusLabel(): string
    {
        return match ($this->status()) {
            'exceeded' => 'Melebihi anggaran',
            'danger'   => 'Hampir habis',
            'warning'  => 'Perlu perhatian',
            default    => 'Aman',
        };
    }
}

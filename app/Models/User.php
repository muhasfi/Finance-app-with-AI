<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'status',
        'currency', 'timezone', 'avatar',
        'last_login_at', 'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
     protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'role'              => UserRole::class,
        ];
    }

    // ── Role helpers ─────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // ── Relations ────────────────────────────────────────────────────

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function activeAccounts(): HasMany
    {
        return $this->hasMany(Account::class)->where('is_active', true);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', UserRole::Admin);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function totalBalance(): float
    {
        return (float) $this->activeAccounts()->sum('balance');
    }

    public function recordLogin(string $ip): void
    {
        $this->updateQuietly([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    // public function transactions(): HasMany
    // {
    //     // Transaksi milik user via akun
    //     return $this->hasManyThrough(Transaction::class, Account::class);
    // }
}

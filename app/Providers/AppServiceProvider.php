<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Transaction;
use App\Observers\TransactionObserver;
use App\Policies\AccountPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observer — auto-update balance saat transaksi berubah
        Transaction::observe(TransactionObserver::class);

        // Policies
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);

        // Gate admin
        Gate::define('admin', fn($user) => $user->isAdmin());

        //── Number formatting locale ─────────────────────────────────
        \Illuminate\Support\Number::useLocale('id');
    }
}

<?php

namespace App\Providers;

use App\Events\TransactionCreated;
use App\Listeners\CheckBudgetAfterTransaction;
use App\Models\Account;
use App\Models\Transaction;
use App\Observers\TransactionObserver;
use App\Policies\AccountPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        // Rate limiter untuk login — 5 percobaan per menit per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('email') . '|' . $request->ip())
                ->response(function () {
                    return back()
                        ->withErrors(['email' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.'])
                        ->withInput();
                });
        });

        // Rate limiter untuk forgot password — 3 request per 5 menit per IP
        RateLimiter::for('forgot-password', function (Request $request) {
            return Limit::perMinutes(5, 3)
                ->by($request->ip());
        });

        // Rate limiter untuk AI chatbot — 20 pesan per menit per user
        RateLimiter::for('ai-chat', function (Request $request) {
            return Limit::perMinute(20)
                ->by(auth()->id() ?? $request->ip());
        });

        // ── Event → Listener ─────────────────────────────────────────
        Event::listen(
            TransactionCreated::class,
            CheckBudgetAfterTransaction::class,
        );

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

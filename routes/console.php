<?php

use App\Events\BillDueEvent;
use App\Jobs\CategorizeTransactionJob;
use App\Jobs\Generateinsightjob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\RecurringPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $duePlans = RecurringPlan::due()->with('account')->get();

    foreach ($duePlans as $plan) {
        app(TransactionService::class)->create([
            'account_id'        => $plan->account_id,
            'category_id'       => $plan->category_id,
            'type'              => $plan->type->value,
            'amount'            => $plan->amount,
            'date'              => today()->toDateString(),
            'note'              => $plan->name . ' (otomatis)',
            'recurring_plan_id' => $plan->id,
        ]);

        $plan->update([
            'next_run_at' => $plan->frequency->nextDate(today()),
        ]);

        if ($plan->ends_at && today()->greaterThan($plan->ends_at)) {
            $plan->update(['is_active' => false]);
        }
    }
})->dailyAt('07:00')->name('process-recurring-transactions');

// ── Notifikasi tagihan jatuh tempo — setiap hari jam 08:00 ───────────────
Schedule::call(function () {
    // Tagihan hari ini
    RecurringPlan::where('is_active', true)
        ->whereDate('next_run_at', today())
        ->with(['account.user', 'account'])
        ->get()
        ->each(fn($plan) => broadcast(new BillDueEvent($plan->account->user, $plan, true)));

    // Tagihan besok (pengingat H-1)
    RecurringPlan::where('is_active', true)
        ->whereDate('next_run_at', today()->addDay())
        ->with(['account.user', 'account'])
        ->get()
        ->each(fn($plan) => broadcast(new BillDueEvent($plan->account->user, $plan, false)));
})->dailyAt('08:00')->name('notify-bill-due');

// ── Auto-kategorisasi transaksi tanpa kategori (AI) ──────────────────────
// Jalankan setiap 30 menit
if (config('services.gemini.api_key')) {
    Schedule::call(function () {
        \App\Models\Transaction::whereNull('category_id')
            ->whereNotNull('note')
            ->where('ai_categorized', false)
            ->limit(30) // batch kecil agar tidak abuse API
            ->get()
            ->each(fn($tx) => \App\Jobs\CategorizeTransactionJob::dispatch($tx)->onQueue('ai'));
    })->everyThirtyMinutes()->name('ai-auto-categorize');

    // Generate insight bulanan untuk semua user aktif (tanggal 1, jam 08:00)
    Schedule::call(function () {
        \App\Models\User::active()->chunk(20, function ($users) {
            foreach ($users as $user) {
                \App\Jobs\GenerateInsightJob::dispatch($user, now()->month, now()->year)
                    ->onQueue('ai')
                    ->delay(now()->addSeconds(rand(0, 120)));
            }
        });
    })->monthlyOn(1, '08:00')->name('ai-monthly-insights');
}
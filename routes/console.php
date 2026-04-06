<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\RecurringPlan;
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

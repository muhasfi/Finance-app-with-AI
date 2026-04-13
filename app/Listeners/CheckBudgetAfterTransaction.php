<?php

namespace App\Listeners;

use App\Events\BudgetAlertEvent;
use App\Mail\BudgetAlertMail;
use App\Models\Budget;
use App\Enums\TransactionType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class CheckBudgetAfterTransaction implements ShouldQueue
{
    public function handle(object $event): void
    {
        $transaction = $event->transaction ?? null;
        if (! $transaction) return;
        if ($transaction->type !== TransactionType::Expense) return;
        if (! $transaction->category_id) return;

        $userId = $transaction->account->user_id;

        $budget = Budget::where('user_id', $userId)
            ->where('category_id', $transaction->category_id)
            ->where('month', $transaction->date->month)
            ->where('year',  $transaction->date->year)
            ->where('is_active', true)
            ->with('category:id,name,color', 'user')
            ->first();

        if (! $budget) return;

        $percentage = $budget->percentage();
        if ($percentage < $budget->alert_threshold) return;

        $level    = $percentage >= 100 ? 'exceeded' : 'danger';
        $cacheKey = "budget_alert_{$budget->id}_{$level}";

        // Anti-spam — hanya kirim sekali per jam per level
        if (Cache::has($cacheKey)) return;
        Cache::put($cacheKey, true, now()->addHour());

        // 1. Broadcast real-time ke Pusher
        broadcast(new BudgetAlertEvent($budget, $percentage));

        // 2. Kirim email notifikasi
        Mail::to($budget->user->email)
            ->queue(new BudgetAlertMail($budget, $percentage));
    }
}

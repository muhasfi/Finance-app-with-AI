<?php

namespace App\Events;

use App\Models\RecurringPlan;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillDueEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $message;
    public array  $data;

    public function __construct(
        public User $user,
        RecurringPlan $plan,
        bool $isToday = true
    ) {
        $amount   = number_format($plan->amount, 0, ',', '.');
        $dueDate  = $plan->next_run_at->translatedFormat('d F Y');
        $when     = $isToday ? 'hari ini' : 'besok';

        $this->message = "Tagihan \"{$plan->name}\" jatuh tempo {$when} — Rp {$amount} ({$dueDate}).";

        $this->data = [
            'plan_id'     => $plan->id,
            'name'        => $plan->name,
            'amount'      => $plan->amount,
            'due_date'    => $plan->next_run_at->toDateString(),
            'is_today'    => $isToday,
            'account'     => $plan->account->name,
        ];

        // Simpan ke database notifications
        $user->notify(new \App\Notifications\BillDueNotification(
            $this->message,
            $this->data
        ));
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'bill.due';
    }
}

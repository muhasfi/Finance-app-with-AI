<?php

namespace App\Events;

use App\Models\Budget;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetAlertEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $type;
    public string $message;
    public array  $data;
    public string $userId;

    public function __construct(Budget $budget, float $percentage)
    {
        $categoryName = $budget->category->name;
        $spent        = number_format($budget->spent(), 0, ',', '.');
        $limit        = number_format($budget->amount, 0, ',', '.');

        if ($percentage >= 100) {
            $this->type    = 'exceeded';
            $this->message = "Budget {$categoryName} telah terlampaui! Terpakai Rp {$spent} dari Rp {$limit}.";
        } elseif ($percentage >= $budget->alert_threshold) {
            $this->type    = 'danger';
            $this->message = "Budget {$categoryName} hampir habis ({$percentage}%). Terpakai Rp {$spent} dari Rp {$limit}.";
        } else {
            $this->type    = 'warning';
            $this->message = "Budget {$categoryName} sudah {$percentage}% terpakai.";
        }

        // Simpan user_id sebagai property — dipakai di broadcastOn()
        $this->userId = (string) $budget->user_id;

        $this->data = [
            'budget_id'      => $budget->id,
            'user_id'        => $budget->user_id,
            'category_name'  => $categoryName,
            'category_color' => $budget->category->color,
            'percentage'     => $percentage,
            'spent'          => $budget->spent(),
            'limit'          => $budget->amount,
            'month'          => $budget->month,
            'year'           => $budget->year,
            'type'           => $this->type,
            'message'        => $this->message,
        ];

        // Simpan ke database notifications
        $budget->user->notify(new \App\Notifications\BudgetAlertNotification(
            $this->type,
            $this->message,
            $this->data
        ));
    }

    public function broadcastOn(): array
    {
        // ✅ Pakai user_id bukan budget_id
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'budget.alert';
    }

    /**
     * Data yang dikirim ke client via Pusher.
     * Harus include semua yang dibutuhkan JS di frontend.
     */
    public function broadcastWith(): array
    {
        return [
            'type'    => $this->type,
            'message' => $this->message,
            'data'    => $this->data,
        ];
    }
}

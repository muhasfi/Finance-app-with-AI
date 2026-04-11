<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BillDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $message,
        private array  $data
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'bill_due',
            'message' => $this->message,
            'icon'    => 'bi-calendar-check',
            'color'   => 'info',
            'url'     => '/recurring',
            'data'    => $this->data,
        ];
    }
}

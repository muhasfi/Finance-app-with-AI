<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BudgetAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $type,
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
            'type'    => $this->type,
            'message' => $this->message,
            'icon'    => match ($this->type) {
                'exceeded' => 'bi-exclamation-octagon',
                'danger'   => 'bi-exclamation-triangle',
                default    => 'bi-exclamation-circle',
            },
            'color'   => match ($this->type) {
                'exceeded' => 'danger',
                'danger'   => 'danger',
                default    => 'warning',
            },
            'url'     => '/budgets?' . http_build_query([
                'month' => $this->data['month'],
                'year'  => $this->data['year'],
            ]),
            'data'    => $this->data,
        ];
    }
}

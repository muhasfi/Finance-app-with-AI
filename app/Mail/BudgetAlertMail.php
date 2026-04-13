<?php

namespace App\Mail;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BudgetAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $categoryName;
    public float  $percentage;
    public float  $spent;
    public float  $limit;
    public string $status;
    public string $monthName;

    public function __construct(public Budget $budget, float $percentage)
    {
        $this->categoryName = $budget->category->name;
        $this->percentage   = $percentage;
        $this->spent        = $budget->spent();
        $this->limit        = $budget->amount;
        $this->status       = $percentage >= 100 ? 'exceeded' : 'warning';
        $this->monthName    = \Carbon\Carbon::createFromDate($budget->year, $budget->month, 1)
            ->translatedFormat('F Y');
    }

    public function envelope(): Envelope
    {
        $subject = $this->status === 'exceeded'
            ? "⚠️ Budget {$this->categoryName} Telah Terlampaui!"
            : "🔔 Budget {$this->categoryName} Hampir Habis ({$this->percentage}%)";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.budget-alert');
    }
}

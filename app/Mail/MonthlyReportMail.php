<?php

namespace App\Mail;

use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlyReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array  $summary;
    public array  $byCategory;
    public string $monthName;

    public function __construct(
        public User $user,
        public int  $month,
        public int  $year
    ) {
        $service          = app(TransactionService::class);
        $this->summary    = $service->monthlySummary($user->id, $month, $year);
        $this->byCategory = $service->expenseByCategory($user->id, $month, $year);
        $this->monthName  = \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Laporan Keuangan {$this->monthName} — " . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.monthly-report',
        );
    }
}

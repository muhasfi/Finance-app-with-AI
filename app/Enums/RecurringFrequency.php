<?php

namespace App\Enums;

enum RecurringFrequency: string
{
    case Daily   = 'daily';
    case Weekly  = 'weekly';
    case Monthly = 'monthly';
    case Yearly  = 'yearly';

    public function label(): string
    {
        return match($this) {
            RecurringFrequency::Daily   => 'Harian',
            RecurringFrequency::Weekly  => 'Mingguan',
            RecurringFrequency::Monthly => 'Bulanan',
            RecurringFrequency::Yearly  => 'Tahunan',
        };
    }

    public function nextDate(\Carbon\Carbon $from): \Carbon\Carbon
    {
        return match($this) {
            RecurringFrequency::Daily   => $from->copy()->addDay(),
            RecurringFrequency::Weekly  => $from->copy()->addWeek(),
            RecurringFrequency::Monthly => $from->copy()->addMonth(),
            RecurringFrequency::Yearly  => $from->copy()->addYear(),
        };
    }
}

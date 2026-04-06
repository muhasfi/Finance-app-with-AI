<?php

namespace App\Enums;

enum AccountType: string
{
    case Cash    = 'cash';
    case Bank    = 'bank';
    case Ewallet = 'ewallet';
    case Credit  = 'credit';
    case Savings = 'savings';

    public function label(): string
    {
        return match($this) {
            AccountType::Cash    => 'Kas / Tunai',
            AccountType::Bank    => 'Rekening Bank',
            AccountType::Ewallet => 'E-Wallet',
            AccountType::Credit  => 'Kartu Kredit',
            AccountType::Savings => 'Tabungan',
        };
    }
}

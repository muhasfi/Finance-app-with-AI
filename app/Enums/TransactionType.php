<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income   = 'income';
    case Expense  = 'expense';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match($this) {
            TransactionType::Income   => 'Pemasukan',
            TransactionType::Expense  => 'Pengeluaran',
            TransactionType::Transfer => 'Transfer',
        };
    }

    public function sign(): int
    {
        return match($this) {
            TransactionType::Income   =>  1,
            TransactionType::Expense  => -1,
            TransactionType::Transfer =>  0,
        };
    }
}

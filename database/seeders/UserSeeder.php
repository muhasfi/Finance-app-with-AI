<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\UserRole;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'id'                => Str::uuid(),
            'name'              => 'Administrator',
            'email'             => 'admin@financeapp.test',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'role'              => UserRole::Admin,
            'status'            => 'active',
            'currency'          => 'IDR',
            'timezone'          => 'Asia/Jakarta',
        ]);

        // Demo user
        $user = User::create([
            'id'                => Str::uuid(),
            'name'              => 'Budi Santoso',
            'email'             => 'budi@financeapp.test',
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'role'              => UserRole::User,
            'status'            => 'active',
            'currency'          => 'IDR',
            'timezone'          => 'Asia/Jakarta',
        ]);

        // Akun demo
        $accounts = [
            ['name' => 'Dompet Tunai', 'type' => AccountType::Cash,    'balance' => 500000,   'color' => '#22c55e', 'icon' => 'bi-cash'],
            ['name' => 'BCA Tabungan', 'type' => AccountType::Bank,    'balance' => 12500000, 'color' => '#3b82f6', 'icon' => 'bi-bank'],
            ['name' => 'GoPay',        'type' => AccountType::Ewallet, 'balance' => 150000,   'color' => '#06b6d4', 'icon' => 'bi-phone'],
        ];

        foreach ($accounts as $acc) {
            Account::create(['id' => Str::uuid(), 'user_id' => $user->id, 'currency' => 'IDR', 'is_active' => true, ...$acc]);
        }
    }
}

<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): Response
    {
        if (! $user->isActive()) {
            return Response::deny('Akun anda sedang disuspend.');
        }

        return ($user->isAdmin() || $transaction->account->user_id === $user->id)
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke transaksi ini.');
    }

    public function create(User $user): Response
    {
        return $user->isActive()
            ? Response::allow()
            : Response::deny('Akun anda sedang disuspend. Tidak dapat membuat transaksi.');
    }

    public function update(User $user, Transaction $transaction): Response
    {
        if (! $user->isActive()) {
            return Response::deny('Akun anda sedang disuspend.');
        }

        return ($user->isAdmin() || $transaction->account->user_id === $user->id)
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin untuk mengubah transaksi ini.');
    }

    public function delete(User $user, Transaction $transaction): Response
    {
        if (! $user->isActive()) {
            return Response::deny('Akun anda sedang disuspend.');
        }

        return ($user->isAdmin() || $transaction->account->user_id === $user->id)
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin untuk menghapus transaksi ini.');
    }
}
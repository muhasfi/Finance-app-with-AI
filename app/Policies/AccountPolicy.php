<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AccountPolicy
{
    public function view(User $user, Account $account): Response
    {
        if (! $user->isActive()) {
            return Response::deny('Akun anda sedang disuspend.');
        }

        return ($user->isAdmin() || $account->user_id === $user->id)
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke akun ini.');
    }

    public function create(User $user): Response
    {
        return $user->isActive()
            ? Response::allow()
            : Response::deny('Akun anda sedang disuspend. Tidak dapat membuat akun.');
    }

    public function update(User $user, Account $account): Response
    {
        if (! $user->isActive()) {
            return Response::deny('Akun anda sedang disuspend.');
        }

        return ($user->isAdmin() || $account->user_id === $user->id)
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin untuk mengubah akun ini.');
    }

    public function delete(User $user, Account $account): Response
    {
        if (! $user->isActive()) {
            return Response::deny('Akun anda sedang disuspend.');
        }

        if ($account->transactions()->exists()) {
            return Response::deny('Akun tidak bisa dihapus karena sudah memiliki transaksi.');
        }

        return ($user->isAdmin() || $account->user_id === $user->id)
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin untuk menghapus akun ini.');
    }
}
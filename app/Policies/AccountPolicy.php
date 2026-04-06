<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function view(User $user, Account $account): bool
    {
        return $user->isAdmin() || $account->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Account $account): bool
    {
        return $user->isAdmin() || $account->user_id === $user->id;
    }

    public function delete(User $user, Account $account): bool
    {
        if ($account->transactions()->exists()) {
            return false;
        }
        return $user->isAdmin() || $account->user_id === $user->id;
    }
}

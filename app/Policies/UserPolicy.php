<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model)
    {
        return $user->id === $model->id || $user->isAdmin();
    }

    public function delete(User $user)
    {
        return $user->isAdmin();
    }
}

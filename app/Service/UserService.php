<?php

namespace App\Service;

use App\User;

class UserService
{
    public function createUser(string $name, string $email, string $password, string $permissions): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'permissions' => $permissions,
        ]);
    }

    public function updateUser(User $user, string $name, string $email): User
    {
        $user->name = $name;
        $user->email = $email;
        $user->save();

        return $user;
    }

    public function deleteUser(User $user)
    {
        $user->delete();
    }
}

<?php

namespace App\Service;

use App\User;

class UserService
{
    public function create(string $name, string $email, string $password, string $permissions): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'permissions' => $permissions,
        ]);
    }
}

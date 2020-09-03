<?php

use App\User;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminEmail = "admin@gmail.com";

        $exists = User::where(['email' => $adminEmail])->exists();

        if ( $exists ) {
            echo 'Admin user already exists\\n';
        } else {
            $user = new User();
            $user->name = "Admin";
            $user->email = $adminEmail;
            $user->permissions = User::PERMISSIONS_ADMIN;
            $user->password = bcrypt("secret");
            $user->save();
            Log::info('Admin user was created');
        }
    }
}

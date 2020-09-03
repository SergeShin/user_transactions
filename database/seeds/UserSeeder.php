<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        DB::table("users")
            ->where("permissions", User::PERMISSIONS_USER)
            ->delete();

        factory(App\User::class, 40)->create()->each(function ($user) use ($faker) {
            $transactionsCount = $faker->numberBetween(10, 30);
            for ($i = 0; $i < $transactionsCount; $i++) {
                $user->transactions()->save(factory(App\Transaction::class)->make([
                    'user_id' => $user->id
                ]));
            }
        });
    }
}

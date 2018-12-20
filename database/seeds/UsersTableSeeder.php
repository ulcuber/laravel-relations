<?php

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Phone;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        factory(User::class, 20)->create()
        ->each(function (User $user) {
            factory(Phone::class)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}

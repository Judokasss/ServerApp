<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Seeder
{
    public function run()
    {
        User::firstOrCreate(
            ['email' => "AlexAdmin@mail.ru"],
            [
                'username' => "AlexAdmin",
                'password' => Hash::make("Qwerty2003@"),
                'birthday' => "2003-05-24",
            ]
        );
    }
}

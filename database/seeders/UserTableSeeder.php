<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       User::create([
            'first_name' => 'Lester',
            'middle_name' => 'Carbungco',
            'last_name' => 'Tuazon',
            'email' => 'lester@gmail.com',
            'password' => Hash::make('lester123'),
            'is_admin' => 1,
            'profile_image' => null
        ]);

        User::create([
            'first_name' => 'Jerome',
            'last_name' => 'Tomamao',
            'email' => 'jerome@gmail.com',
            'password' => Hash::make('lester123'),
            'is_admin' => 0,
            'profile_image' => null
        ]);

        User::create([
            'first_name' => 'Edmar',
            'last_name' => 'Montoya',
            'email' => 'edmar@gmail.com',
            'password' => Hash::make('lester123'),
            'is_admin' => 0,
            'profile_image' => null
        ]);
    }
}

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
            'firstName' => 'Lester',
            'middleName' => 'Carbungco',
            'lastName' => 'Tuazon',
            'email' => 'lester@gmail.com',
            'password' => Hash::make('lester123'),
            'isAdmin' => 1,
            'profileImage' => null
        ]);

        User::create([
            'firstName' => 'Jerome',
            'lastName' => 'Tomamao',
            'email' => 'jerome@gmail.com',
            'password' => Hash::make('lester123'),
            'isAdmin' => 0,
            'profileImage' => null
        ]);

        User::create([
            'firstName' => 'Edmar',
            'lastName' => 'Montoya',
            'email' => 'edmar@gmail.com',
            'password' => Hash::make('lester123'),
            'isAdmin' => 0,
            'profileImage' => null
        ]);
    }
}

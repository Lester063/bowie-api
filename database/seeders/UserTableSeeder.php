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
            'name' => 'Lester',
            'email' => 'lester@gmail.com',
            'password' => Hash::make('lester123'),
            'is_admin' => 1,
        ]);

        User::create([
            'name' => 'Lester1',
            'email' => 'lester1@gmail.com',
            'password' => Hash::make('lester123'),
            'is_admin' => 0,
        ]);
    }
}

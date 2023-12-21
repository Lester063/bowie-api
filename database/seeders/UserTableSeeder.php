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
            'name' => 'Jerome',
            'email' => 'jerome@gmail.com',
            'password' => Hash::make('lester123'),
            'is_admin' => 0,
        ]);

        User::create([
            'name' => 'Edmar',
            'email' => 'edmar@gmail.com',
            'password' => Hash::make('lester123'),
            'is_admin' => 0,
        ]);
    }
}

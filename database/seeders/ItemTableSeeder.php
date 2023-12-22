<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Item::create([
            'itemname' => 'NVISION Monitor',
            'itemcode' => 'NVM1',
            'is_available' => true,
        ]);

        Item::create([
            'itemname' => 'NVISION Monitor',
            'itemcode' => 'NVM2',
            'is_available' => true,
        ]);

        Item::create([
            'itemname' => 'NK Keyboard',
            'itemcode' => 'NKK1',
            'is_available' => true,
        ]);
    }
}

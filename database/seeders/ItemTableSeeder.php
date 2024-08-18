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
            'itemName' => 'NVISION Monitor',
            'itemCode' => 'NVM1',
            'itemImage' => null,
            'isAvailable' => true,
        ]);

        Item::create([
            'itemName' => 'NVISION Monitor',
            'itemCode' => 'NVM2',
            'itemImage' => null,
            'isAvailable' => true,
        ]);

        Item::create([
            'itemName' => 'NK Keyboard',
            'itemCode' => 'NKK1',
            'itemImage' => null,
            'isAvailable' => true,
        ]);
    }
}

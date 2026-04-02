<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $units = [
            ['name' => 'Piece',      'code' => 'pc'],
            ['name' => 'Gram',       'code' => 'gm'],
            ['name' => 'Litre',      'code' => 'lt'],
            ['name' => 'Milliliter', 'code' => 'ml'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['code' => $unit['code']],
                array_merge($unit, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}

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
        if (env('DEMO', false)) {
            Unit::insert([
                [
                    'name'       => 'Piece',
                    'code'       => 'pc',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name'       => 'Gram',
                    'code'       => 'gm',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name'       => 'Litre',
                    'code'       => 'lt',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'name'       => 'Milliliter',
                    'code'       => 'ml',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
            ]);
        }
    }
}

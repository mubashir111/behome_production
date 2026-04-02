<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $taxes = [
            ['name' => 'No-VAT', 'code' => 'VAT-0%',  'tax_rate' => 0],
            ['name' => 'VAT-5',  'code' => 'VAT-5%',  'tax_rate' => 5],
            ['name' => 'VAT-10', 'code' => 'VAT-10%', 'tax_rate' => 10],
            ['name' => 'VAT-20', 'code' => 'VAT-20%', 'tax_rate' => 20],
        ];

        foreach ($taxes as $tax) {
            Tax::firstOrCreate(
                ['code' => $tax['code']],
                array_merge($tax, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}

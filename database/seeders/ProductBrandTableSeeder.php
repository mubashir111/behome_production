<?php

namespace Database\Seeders;

use App\Enums\Status;
use Illuminate\Support\Str;
use App\Models\ProductBrand;
use Illuminate\Database\Seeder;

class ProductBrandTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public array $fashionBrands = [
        'Babymel',
        'Burberry',
        'Camper',
        'Chanel',
        'Dr. Martens',
        'Fila',
        'Levi\'s',
        'puma'
    ];

    public function run(): void
    {
        if (env('DEMO', false) && env('DISPLAY', false) == 'fashion') {
            foreach ($this->fashionBrands as $fashionBrand) {
                $productBand = ProductBrand::create([
                    'name'        => $fashionBrand,
                    'slug'        => Str::slug($fashionBrand),
                    'description' => null,
                    'status'      => Status::ACTIVE,
                ]);

                if (file_exists(public_path('/images/seeder/brand/' . env('DISPLAY') . '/' . strtolower(str_replace(' ', '_', $fashionBrand)) . '.png'))) {
                    $productBand->addMedia(public_path('/images/seeder/brand/' . env('DISPLAY') . '/' . strtolower(str_replace(' ', '_', $fashionBrand)) . '.png'))->preservingOriginal()->toMediaCollection('product-brand');
                }
            }
        }
    }
}

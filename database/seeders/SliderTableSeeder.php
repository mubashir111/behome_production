<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Slider;
use Illuminate\Database\Seeder;

class SliderTableSeeder extends Seeder
{
    public array $sliders = [
        [
            'title'       => 'Elevate your living',
            'description' => 'Premium modern homes & furniture',
            'badge_text'  => 'Premium Collection',
            'button_text' => 'SHOP NOW',
            'link'        => '/shop',
            'image_path'  => 'frontend/public/images/new/hero2.png',
        ],
        [
            'title'       => 'Verona sofas',
            'description' => 'Price starting from $259.00',
            'badge_text'  => 'New Arrival',
            'button_text' => 'Shop Now',
            'link'        => '/shop',
            'image_path'  => 'frontend/public/images/new/hero3.png',
        ],
    ];

    public function run(): void
    {
        foreach ($this->sliders as $sliderData) {
            $imagePath = $sliderData['image_path'];
            unset($sliderData['image_path']);
            
            $slider = Slider::create(array_merge($sliderData, [
                'status' => Status::ACTIVE,
            ]));

            $fullPath = base_path($imagePath);
            if (file_exists($fullPath)) {
                $slider->addMedia($fullPath)
                    ->preservingOriginal()
                    ->toMediaCollection('slider');
            }
        }
    }
}

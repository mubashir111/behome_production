<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = Product::where('slug', 'derong-dr-1148-lounge')->first();

        if ($product) {
            $product->details = [
                [
                    "type" => "feature_split",
                    "label" => "Designer thoughts",
                    "title" => "Minimalist design and modern chair.",
                    "text" => "Lorem ipsum is simply dummy text of the printing and typesetting industry lorem ipsum has been the standard dummy text typesetting.",
                    "bullets" => [
                        "FSC certified natural wood teak product.",
                        "Removable cushion with polypropylene.",
                        "Durability wood & lightweight modern.",
                        "Topstitch detailing along back of seat."
                    ],
                    "image" => "https://mubashir111.github.io/blackrock_ecommerse/images/demo-decor-store-product-detail-tab-01.jpg",
                    "reverse" => false
                ],
                [
                    "type" => "hero_banner",
                    "title" => "The dining chair design for those looking for a new level of comfort.",
                    "text" => "Lorem ipsum is simply dummy text printing typesetting industry lorem ipsum has been standard dummy text lorem ipsum.",
                    "image" => "https://mubashir111.github.io/blackrock_ecommerse/images/demo-decor-store-product-detail-tab-02.jpg"
                ],
                [
                    "type" => "icon_grid",
                    "items" => [
                        [
                            "title" => "WOODEN",
                            "text" => "Lorem ipsum simply dummy text printing typesetting.",
                            "image" => "https://mubashir111.github.io/blackrock_ecommerse/images/demo-decor-store-product-detail-tab-05.jpg"
                        ],
                        [
                            "title" => "FABRIC",
                            "text" => "Lorem ipsum simply dummy text printing typesetting.",
                            "image" => "https://mubashir111.github.io/blackrock_ecommerse/images/demo-decor-store-product-detail-tab-06.jpg"
                        ],
                        [
                            "title" => "STRENGTH",
                            "text" => "Lorem ipsum simply dummy text printing typesetting.",
                            "image" => "https://mubashir111.github.io/blackrock_ecommerse/images/demo-decor-store-product-detail-tab-07.jpg"
                        ],
                        [
                            "title" => "COMFORT",
                            "text" => "Lorem ipsum simply dummy text printing typesetting.",
                            "image" => "https://mubashir111.github.io/blackrock_ecommerse/images/demo-decor-store-product-detail-tab-08.jpg"
                        ]
                    ]
                ]
            ];
            $product->save();
        }
    }
}

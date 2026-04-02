<?php

namespace Database\Seeders;

use App\Enums\PromotionType;
use App\Enums\Status;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use App\Services\ProductService;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class PromotionTableSeeder extends Seeder
{
    public array $behomePromotions = [
        [
            'name'     => 'The Art of Living',
            'subtitle' => 'New Spring Collection 2025',
            'link'     => '/shop?category=sofa',
            'type'     => 10, // BIG
            'image'    => 'database/seeders/images/secret_realm_curved_sofa_v5_1774928953422.png'
        ],
        [
            'name'     => 'Modern Minimalism',
            'subtitle' => 'Up to 30% Off',
            'link'     => '/shop?category=tableware',
            'type'     => 5, // SMALL
            'image'    => 'database/seeders/images/yalong_signature_tableware_v5_1774928996290.png'
        ],
        [
            'name'     => 'Outdoor Sanctuary',
            'subtitle' => 'Built to Last',
            'link'     => '/shop?category=outdoor-furniture',
            'type'     => 5, // SMALL
            'image'    => 'database/seeders/images/howvin_outdoor_set_v5_1774928970239.png'
        ],
        [
            'name'     => 'Signature Lounge',
            'subtitle' => 'Exclusively at Behome',
            'link'     => '/shop?category=sofa',
            'type'     => 1, // FEATURE
            'image'    => 'database/seeders/images/ink_shadow_modular_sofa_v5_1774928925715.png'
        ],
    ];

    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Promotion::truncate();
        PromotionProduct::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $productService = new ProductService();

        foreach ($this->behomePromotions as $data) {
            $promotion = Promotion::create([
                'name'     => $data['name'],
                'slug'     => Str::slug($data['name']),
                'subtitle' => $data['subtitle'],
                'link'     => $data['link'],
                'type'     => $data['type'],
                'status'   => Status::ACTIVE,
            ]);

            // Add Image
            $fullPath = base_path($data['image']);
            if (file_exists($fullPath)) {
                try {
                    $promotion->addMedia($fullPath)
                        ->preservingOriginal()
                        ->toMediaCollection('promotion');
                } catch (\Exception $e) {
                    echo "Error adding promotion media: " . $e->getMessage() . "\n";
                }
            }

            // Link some products to the promotion (arbitrary)
            $products = Product::select('id')->inRandomOrder()->limit(10)->get();
            foreach ($products as $product) {
                PromotionProduct::create([
                    'promotion_id' => $promotion->id,
                    'product_id'   => $product->id,
                ]);
            }
        }
    }
}

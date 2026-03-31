<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Stock;
use App\Models\ProductBrand;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeOption;
use App\Models\ProductVariation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealProductSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Self-clean to allow multiple runs
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \App\Models\Product::truncate();
        \App\Models\ProductCategory::truncate();
        \App\Models\ProductBrand::truncate();
        \App\Models\ProductAttribute::truncate();
        \App\Models\ProductAttributeOption::truncate();
        \App\Models\ProductVariation::truncate();
        \App\Models\Stock::truncate();
        \App\Models\Purchase::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 1. Create Brands
        $brands = [
            'Ink Home' => 'Luxury modular furniture specialist.',
            'Secret Realm' => 'High-end curved and organic furniture design.',
            'Howvin' => 'Premium outdoor architectural furniture.',
            'Yalong' => 'Exquisite fine bone china and tableware.'
        ];

        foreach ($brands as $name => $desc) {
            ProductBrand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $desc,
                'status' => 5 // Active
            ]);
        }

        // 2. Create Categories
        $categories = [
            'Sofa' => ['ink_shadow_modular_sofa_v5_1774928925715.png', 'secret_realm_curved_sofa_v5_1774928953422.png'],
            'Outdoor Furniture' => ['howvin_outdoor_set_v5_1774928970239.png'],
            'Tableware' => ['yalong_signature_tableware_v5_1774928996290.png'],
        ];

        $catModels = [];
        foreach ($categories as $name => $imgs) {
            $catModels[$name] = ProductCategory::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Premium collection of authentic $name products.",
                'status' => 5
            ]);
        }

        // 3. Create Attributes
        $colorAttr = ProductAttribute::create(['name' => 'Color']);

        $colorOptions = [
            'Ink Black'     => '#1a1a1a',
            'Smoke Grey'    => '#7a7a7a',
            'Emerald Green' => '#043927',
            'Royal Velvet'  => '#4b0082',
            'Teak Natural'  => '#8b4513'
        ];

        $optModels = [];
        foreach ($colorOptions as $name => $hex) {
            $optModels[$name] = ProductAttributeOption::create([
                'product_attribute_id' => $colorAttr->id,
                'name'                 => $name
            ]);
        }

        // 4. Create a Dummy Supplier and Purchase to handle Stock Inventory
        $supplier = \App\Models\Supplier::updateOrCreate(
            ['email' => 'importer@behome.com'],
            [
                'company' => 'Luxury Furniture Importer',
                'name' => 'John Importer',
                'phone' => '123456789',
                'address' => 'Global Logistics Center',
                'country' => 'UAE'
            ]
        );

        $purchase = \App\Models\Purchase::create([
            'supplier_id' => $supplier->id,
            'date' => now(),
            'reference_no' => 'MIG-' . time(),
            'status' => 10, // Received
            'tax' => 0,
            'discount' => 0,
            'subtotal' => 100000,
            'total' => 100000
        ]);

        // 5. Create Products
        $products = [
            [
                'name' => '墨影 / Ink Shadow Modular Sofa',
                'category' => 'Sofa',
                'brand' => 'Ink Home',
                'price' => 4500.00,
                'desc' => 'A modular black sofa set designed for free DIY combinations. Features integrated small side tables and memory foam comfort.',
                'image' => '/Users/mubashirt/.gemini/antigravity/brain/c71c6385-e841-4437-bad8-b888bea5b73f/ink_shadow_modular_sofa_v5_1774928925715.png',
                'variations' => ['Ink Black', 'Smoke Grey']
            ],
            [
                'name' => '秘境 / Secret Realm Curved Sofa (SF2130)',
                'category' => 'Sofa',
                'brand' => 'Secret Realm',
                'price' => 6200.00,
                'desc' => 'A luxury curved sofa set finished in full frosted emerald green leather. Unique visual symbol with curved armrests.',
                'image' => '/Users/mubashirt/.gemini/antigravity/brain/c71c6385-e841-4437-bad8-b888bea5b73f/secret_realm_curved_sofa_v5_1774928953422.png',
                'variations' => ['Emerald Green', 'Royal Velvet']
            ],
            [
                'name' => 'Howvin 2025 Ocean Series Lounge Set',
                'category' => 'Outdoor Furniture',
                'brand' => 'Howvin',
                'price' => 3800.00,
                'desc' => 'Coastal architectural furniture featuring a teak wood base and navy woven back chairs with grey cushions.',
                'image' => '/Users/mubashirt/.gemini/antigravity/brain/c71c6385-e841-4437-bad8-b888bea5b73f/howvin_outdoor_set_v5_1774928970239.png'
            ],
            [
                'name' => 'Yalong Signature Gold Rim Tableware',
                'category' => 'Tableware',
                'brand' => 'Yalong',
                'price' => 850.00,
                'desc' => 'Boutique fine bone china with elegant 24k gold-rimmed detailing and minimalist patterns.',
                'image' => '/Users/mubashirt/.gemini/antigravity/brain/c71c6385-e841-4437-bad8-b888bea5b73f/yalong_signature_tableware_v5_1774928996290.png'
            ],
            [
                'name' => 'Firenze Elegant Sofa (SF2115)',
                'category' => 'Sofa',
                'brand' => 'Secret Realm',
                'price' => 5400.00,
                'desc' => 'Modern round contours with high-density sponge. Dimension: 310 x 116 x 76 cm.',
                'image' => '/Users/mubashirt/.gemini/antigravity/brain/c71c6385-e841-4437-bad8-b888bea5b73f/secret_realm_curved_sofa_v5_1774928953422.png'
            ]
        ];

        foreach ($products as $p) {
            $sku = strtoupper(Str::random(10));
            $product = Product::create([
                'name' => $p['name'],
                'slug' => Str::slug($p['name']),
                'product_category_id' => $catModels[$p['category']]->id,
                'product_brand_id' => isset($p['brand']) ? ProductBrand::where('name', $p['brand'])->first()->id : null,
                'sku' => $sku,
                'description' => $p['desc'],
                'status' => 5, // Status::ACTIVE
                'can_purchasable' => 5,
                'show_stock_out' => 5,
                'refundable' => 5,
                'unit_id' => 1,
                'buying_price' => $p['price'] * 0.7,
                'selling_price' => $p['price'],
            ]);

            // Add Image via Spatie Media Library
            if (file_exists($p['image'])) {
                try {
                    $product->addMedia($p['image'])
                        ->preservingOriginal()
                        ->toMediaCollection('product');
                } catch (\Exception $e) {
                    echo "Error adding media: " . $e->getMessage() . "\n";
                }
            }

            // Create Variations if defined
            if (isset($p['variations'])) {
                foreach ($p['variations'] as $vName) {
                    $varSku = $sku . '-' . strtoupper(substr($vName, 0, 3));
                    $variation = ProductVariation::create([
                        'product_id'                  => $product->id,
                        'product_attribute_id'        => $colorAttr->id,
                        'product_attribute_option_id' => $optModels[$vName]->id,
                        'price'                       => $p['price'],
                        'sku'                         => $varSku,
                        'order'                       => 1
                    ]);

                    // Create Stock for variation
                    Stock::create([
                        'product_id' => $product->id,
                        'model_type' => \App\Models\Purchase::class,
                        'model_id' => $purchase->id,
                        'item_type' => 'App\Models\ProductVariation',
                        'item_id' => $variation->id,
                        'price' => $p['price'],
                        'sku' => $varSku,
                        'quantity' => 25,
                        'status' => 5
                    ]);
                }
            } else {
                // Create Stock for non-variation product
                Stock::create([
                    'product_id' => $product->id,
                    'model_type' => \App\Models\Purchase::class,
                    'model_id' => $purchase->id,
                    'item_type' => 'App\Models\Product',
                    'item_id' => $product->id,
                    'price' => $p['price'],
                    'sku' => $sku,
                    'quantity' => 50,
                    'status' => 5
                ]);
            }
        }
    }
}

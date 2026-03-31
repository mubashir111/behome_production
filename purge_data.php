<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting data purge...\n";

Schema::disableForeignKeyConstraints();

// 1. Clear Product Data
echo "Cleaning products and categories...\n";
$tablesToClear = [
    'product_variations', 'product_attribute_values', 'product_attributes', 
    'product_tags', 'product_brands', 'products', 'product_categories', 
    'attributes', 'attribute_values', 'order_products', 'order_coupons', 
    'orders', 'carts', 'wishlists', 'coupons', 'stocks', 
    'stock_taxes', 'transactions', 'media'
];

foreach ($tablesToClear as $table) {
    if (Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "Truncated table: $table\n";
    }
}

Schema::enableForeignKeyConstraints();

// 5. Clear Physics Storage (Spatie Media Library stores in storage/app/public)
echo "Cleaning storage directories...\n";
$publicMediaDir = storage_path('app/public');
if (File::exists($publicMediaDir)) {
    // Only delete numeric folders (which Spatie uses for media)
    $directories = File::directories($publicMediaDir);
    foreach ($directories as $dir) {
        $basename = basename($dir);
        if (is_numeric($basename)) {
            File::deleteDirectory($dir);
            echo "Deleted media folder: " . $basename . "\n";
        }
    }
}

echo "Data purge complete!\n";

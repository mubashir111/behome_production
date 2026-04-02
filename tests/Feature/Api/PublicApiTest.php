<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use App\Enums\Status;

class PublicApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure we have at least one category and one product for listing tests
        $category = ProductCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'status' => Status::ACTIVE
        ]);

        Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-SKU-' . Str::random(5),
            'product_category_id' => $category->id,
            'buying_price' => 100,
            'selling_price' => 150,
            'status' => Status::ACTIVE,
            'can_purchasable' => 1,
            'show_stock_out' => 5 // Status::ACTIVE is 5
        ]);
    }

    protected function getWithApiKey($uri)
    {
        return $this->withHeader('x-api-key', config('app.mix_api_key'))->getJson($uri);
    }

    /** @test */
    public function setting_api_is_reachable()
    {
        $response = $this->getWithApiKey('/api/frontend/setting');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['company_name', 'company_email']]);
    }

    /** @test */
    public function slider_api_is_reachable()
    {
        $response = $this->getWithApiKey('/api/frontend/slider');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    /** @test */
    public function product_category_tree_api_is_reachable()
    {
        $response = $this->getWithApiKey('/api/frontend/product-category/tree');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    /** @test */
    public function product_listing_api_is_reachable()
    {
        $response = $this->getWithApiKey('/api/frontend/product');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    /** @test */
    public function product_detail_api_is_reachable()
    {
        $product = Product::first();

        $response = $this->getWithApiKey("/api/frontend/product/show/{$product->slug}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['name', 'slug', 'price']]);
    }
}

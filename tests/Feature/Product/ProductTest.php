<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_products_require_authentication(): void
    {
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', '')
            ->getJson('/api/v1/products')
            ->assertUnauthorized();
    }

    public function test_user_can_get_paginated_products(): void
    {
        Product::factory()->count(3)->create();

        $this->getJson('/api/v1/products?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data.products')
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonPath('data.pagination.last_page', 2);
    }

    public function test_product_list_returns_average_rating_and_only_the_first_image(): void
    {
        $product = Product::factory()->create();
        $firstImage = ProductImage::factory()->for($product)->create();
        ProductImage::factory()->count(2)->for($product)->create();
        ProductRating::factory()->for($product)->create(['rating' => 5]);
        ProductRating::factory()->for($product)->create(['rating' => 2]);
        Product::factory()->create();

        $response = $this->getJson('/api/v1/products?sort_by=name')->assertOk();

        $listed = collect($response->json('data.products'))->keyBy('id');

        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'price', 'average_rating', 'category_name', 'image_url'],
            array_keys($listed[$product->id])
        );
        $this->assertSame(3.5, $listed[$product->id]['average_rating']);
        $this->assertSame($product->category->name, $listed[$product->id]['category_name']);
        $this->assertSame(Storage::disk('public')->url($firstImage->image_path), $listed[$product->id]['image_url']);
        $this->assertNull($listed->firstWhere('id', '!=', $product->id)['image_url']);
    }

    public function test_products_can_be_filtered_by_search_category_and_price(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create(['name' => 'Gaming Laptop', 'price' => 500]);
        Product::factory()->for($category)->create(['name' => 'Office Laptop', 'price' => 50]);
        Product::factory()->create(['name' => 'Laptop Bag', 'price' => 500]);

        $this->getJson("/api/v1/products?search=laptop&category_id={$category->id}&min_price=100")
            ->assertOk()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.name', 'Gaming Laptop');
    }

    public function test_search_treats_like_wildcards_literally(): void
    {
        Product::factory()->create(['name' => '100% Cotton Shirt']);
        Product::factory()->create(['name' => '100 Cotton Socks']);

        $this->getJson('/api/v1/products?search=100%25')
            ->assertOk()
            ->assertJsonCount(1, 'data.products')
            ->assertJsonPath('data.products.0.name', '100% Cotton Shirt');
    }

    public function test_products_can_be_sorted_by_price(): void
    {
        Product::factory()->create(['price' => 30]);
        Product::factory()->create(['price' => 10]);
        Product::factory()->create(['price' => 20]);

        $this->getJson('/api/v1/products?sort_by=price&sort_order=asc')
            ->assertOk()
            ->assertJsonPath('data.products.0.price', '10.00')
            ->assertJsonPath('data.products.2.price', '30.00');
    }

    public function test_products_can_be_sorted_by_average_rating_with_unrated_as_lowest(): void
    {
        $unrated = Product::factory()->create();
        $low = Product::factory()->create();
        ProductRating::factory()->for($low)->create(['rating' => 2]);
        $high = Product::factory()->create();
        ProductRating::factory()->for($high)->create(['rating' => 5]);
        ProductRating::factory()->for($high)->create(['rating' => 4]);

        $this->getJson('/api/v1/products?sort_by=rating&sort_order=desc')
            ->assertOk()
            ->assertJsonPath('data.products.*.id', [$high->id, $low->id, $unrated->id]);

        $this->getJson('/api/v1/products?sort_by=rating&sort_order=asc')
            ->assertOk()
            ->assertJsonPath('data.products.*.id', [$unrated->id, $low->id, $high->id]);
    }

    public function test_product_list_rejects_invalid_filters(): void
    {
        $this->getJson('/api/v1/products?sort_by=password&min_price=50&max_price=10')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort_by', 'max_price']);
    }

    public function test_user_can_get_product_by_id_with_average_rating(): void
    {
        $product = Product::factory()->has(ProductImage::factory()->count(2), 'images')->create();
        ProductRating::factory()->for($product)->create(['rating' => 5]);
        ProductRating::factory()->for($product)->create(['rating' => 4]);

        $this->getJson("/api/v1/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.category.id', $product->category_id)
            ->assertJsonCount(2, 'data.images')
            ->assertJsonPath('data.average_rating', 4.5)
            ->assertJsonPath('data.ratings_count', 2);
    }

    public function test_get_product_returns_not_found_for_missing_product(): void
    {
        $this->getJson('/api/v1/products/999')->assertNotFound();
    }

    public function test_user_can_create_product_with_images(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Wireless Mouse',
            'description' => 'Ergonomic mouse',
            'category_id' => $category->id,
            'price' => 25.5,
            'images' => [$this->fakeImage('one.png'), $this->fakeImage('two.png')],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Wireless Mouse')
            ->assertJsonPath('data.average_rating', 0)
            ->assertJsonCount(2, 'data.images');

        $this->assertDatabaseHas('products', ['name' => 'Wireless Mouse', 'category_id' => $category->id]);
        $this->assertEqualsCanonicalizing(['id', 'image_url'], array_keys($response->json('data.images.0')));
        Storage::disk('public')->assertExists(ProductImage::findOrFail($response->json('data.images.0.id'))->image_path);
    }

    public function test_create_product_fails_validation(): void
    {
        $existing = Product::factory()->create();

        $this->postJson('/api/v1/products', [
            'name' => $existing->name,
            'category_id' => 999,
            'price' => -1,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'category_id', 'price']);
    }

    public function test_user_can_update_product(): void
    {
        $product = Product::factory()->create(['description' => 'Old description']);

        $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Name',
            'description' => null,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.price', $product->price);
    }

    public function test_update_keeps_own_name_valid(): void
    {
        $product = Product::factory()->create();

        $this->putJson("/api/v1/products/{$product->id}", ['name' => $product->name])
            ->assertOk();
    }

    public function test_update_returns_not_found_for_missing_product(): void
    {
        $this->putJson('/api/v1/products/999', ['name' => 'Anything'])->assertNotFound();
    }

    public function test_user_can_delete_product_and_its_image_files(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $imagePath = $this->fakeImage('photo.png')->store('products', 'public');
        ProductImage::factory()->for($product)->create(['image_path' => $imagePath]);

        $this->deleteJson("/api/v1/products/{$product->id}")->assertOk();

        $this->assertModelMissing($product);
        $this->assertDatabaseMissing('product_images', ['product_id' => $product->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_delete_returns_not_found_for_missing_product(): void
    {
        $this->deleteJson('/api/v1/products/999')->assertNotFound();
    }

    public function test_product_list_reflects_changes_after_cached_read(): void
    {
        $product = Product::factory()->create(['name' => 'Before']);

        $this->getJson('/api/v1/products')->assertJsonPath('data.products.0.name', 'Before');

        $this->putJson("/api/v1/products/{$product->id}", ['name' => 'After'])->assertOk();

        $this->getJson('/api/v1/products')->assertJsonPath('data.products.0.name', 'After');
    }
}

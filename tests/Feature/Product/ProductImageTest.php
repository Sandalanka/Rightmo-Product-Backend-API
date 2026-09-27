<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    public function test_user_can_add_images_to_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson("/api/v1/products/{$product->id}/images", [
            'images' => [$this->fakeImage('a.png'), $this->fakeImage('b.png')],
        ]);

        $response->assertCreated()->assertJsonCount(2, 'data.images');

        $this->assertSame(2, $product->images()->count());
        Storage::disk('public')->assertExists(ProductImage::findOrFail($response->json('data.images.1.id'))->image_path);
    }

    public function test_add_images_rejects_non_image_files(): void
    {
        $product = Product::factory()->create();

        $this->postJson("/api/v1/products/{$product->id}/images", [
            'images' => [UploadedFile::fake()->create('script.php', 10, 'application/x-php')],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('images.0');
    }

    public function test_add_images_returns_not_found_for_missing_product(): void
    {
        $this->postJson('/api/v1/products/999/images', [
            'images' => [$this->fakeImage('a.png')],
        ])->assertNotFound();
    }

    public function test_user_can_replace_image_and_old_file_is_deleted(): void
    {
        $oldPath = $this->fakeImage('old.png')->store('products', 'public');
        $image = ProductImage::factory()->create(['image_path' => $oldPath]);

        $response = $this->putJson("/api/v1/products/{$image->product_id}/images/{$image->id}", [
            'image' => $this->fakeImage('new.png'),
        ]);

        $response->assertOk();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($image->refresh()->image_path);
        $response->assertJsonPath('data.image_url', Storage::disk('public')->url($image->image_path));
    }

    public function test_user_can_delete_image(): void
    {
        $path = $this->fakeImage('photo.png')->store('products', 'public');
        $image = ProductImage::factory()->create(['image_path' => $path]);

        $this->deleteJson("/api/v1/products/{$image->product_id}/images/{$image->id}")->assertOk();

        $this->assertModelMissing($image);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_image_of_another_product_is_not_found(): void
    {
        $image = ProductImage::factory()->create();
        $otherProduct = Product::factory()->create();

        $this->deleteJson("/api/v1/products/{$otherProduct->id}/images/{$image->id}")->assertNotFound();

        $this->assertModelExists($image);
    }
}

<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\ProductRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRatingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_user_can_get_product_ratings_without_emails(): void
    {
        $product = Product::factory()->create();
        ProductRating::factory()->count(3)->for($product)->create();
        ProductRating::factory()->create();

        $this->getJson("/api/v1/products/{$product->id}/ratings")
            ->assertOk()
            ->assertJsonCount(3, 'data.ratings')
            ->assertJsonMissingPath('data.ratings.0.user.email');
    }

    public function test_get_ratings_returns_not_found_for_missing_product(): void
    {
        $this->getJson('/api/v1/products/999/ratings')->assertNotFound();
    }

    public function test_user_can_rate_product_and_rating_again_updates_it(): void
    {
        $product = Product::factory()->create();

        $this->postJson("/api/v1/products/{$product->id}/ratings", ['rating' => 4, 'comment' => 'Good'])
            ->assertCreated()
            ->assertJsonPath('data.rating', 4)
            ->assertJsonPath('data.user.id', $this->user->id);

        $this->postJson("/api/v1/products/{$product->id}/ratings", ['rating' => 2])
            ->assertOk()
            ->assertJsonPath('data.rating', 2);

        $this->assertSame(1, $product->ratings()->count());
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $product = Product::factory()->create();

        $this->postJson("/api/v1/products/{$product->id}/ratings", ['rating' => 6])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rating');
    }

    public function test_rate_returns_not_found_for_missing_product(): void
    {
        $this->postJson('/api/v1/products/999/ratings', ['rating' => 5])->assertNotFound();
    }

    public function test_new_rating_updates_cached_product_average(): void
    {
        $product = Product::factory()->create();

        $this->getJson("/api/v1/products/{$product->id}")->assertJsonPath('data.average_rating', 0);

        $this->postJson("/api/v1/products/{$product->id}/ratings", ['rating' => 5])->assertCreated();

        $this->getJson("/api/v1/products/{$product->id}")
            ->assertJsonPath('data.average_rating', 5)
            ->assertJsonPath('data.ratings_count', 1);
    }

    public function test_user_can_delete_only_their_own_rating(): void
    {
        $product = Product::factory()->create();
        $ownRating = ProductRating::factory()->for($product)->for($this->user)->create();
        $otherRating = ProductRating::factory()->for($product)->create();

        $this->deleteJson("/api/v1/products/{$product->id}/ratings")->assertOk();

        $this->assertModelMissing($ownRating);
        $this->assertModelExists($otherRating);
    }

    public function test_delete_rating_returns_not_found_when_user_has_not_rated(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson("/api/v1/products/{$product->id}/ratings")->assertNotFound();
    }
}

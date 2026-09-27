<?php

namespace Tests\Feature\Product;

use App\Constants\MessageConstant;
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

    public function test_user_can_rate_the_same_product_several_times(): void
    {
        $product = Product::factory()->create();

        $this->postJson("/api/v1/products/{$product->id}/ratings", ['rating' => 4, 'comment' => 'Good'])
            ->assertCreated()
            ->assertJsonPath('data.rating', 4)
            ->assertJsonPath('data.user.id', $this->user->id);

        $this->postJson("/api/v1/products/{$product->id}/ratings", ['rating' => 2])
            ->assertCreated()
            ->assertJsonPath('data.rating', 2);

        $this->assertSame(2, $product->ratings()->where('user_id', $this->user->id)->count());
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

    public function test_user_can_update_their_own_rating(): void
    {
        $product = Product::factory()->create();
        $rating = ProductRating::factory()->for($product)->for($this->user)->create(['rating' => 2, 'comment' => 'Meh']);

        $this->putJson("/api/v1/products/{$product->id}/ratings/{$rating->id}", ['rating' => 5, 'comment' => 'Better now'])
            ->assertOk()
            ->assertJsonPath('data.id', $rating->id)
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.comment', 'Better now');

        $this->getJson("/api/v1/products/{$product->id}")->assertJsonPath('data.average_rating', 5);
    }

    public function test_update_rating_can_change_only_the_comment(): void
    {
        $product = Product::factory()->create();
        $rating = ProductRating::factory()->for($product)->for($this->user)->create(['rating' => 3]);

        $this->putJson("/api/v1/products/{$product->id}/ratings/{$rating->id}", ['comment' => null])
            ->assertOk()
            ->assertJsonPath('data.rating', 3)
            ->assertJsonPath('data.comment', null);
    }

    public function test_update_rating_validates_input(): void
    {
        $product = Product::factory()->create();
        $rating = ProductRating::factory()->for($product)->for($this->user)->create();

        $this->putJson("/api/v1/products/{$product->id}/ratings/{$rating->id}", ['rating' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rating');
    }

    public function test_user_cannot_update_or_delete_another_users_rating(): void
    {
        $product = Product::factory()->create();
        $otherRating = ProductRating::factory()->for($product)->create(['rating' => 1]);

        $this->putJson("/api/v1/products/{$product->id}/ratings/{$otherRating->id}", ['rating' => 5])
            ->assertForbidden()
            ->assertJsonPath('message', MessageConstant::PRODUCT_RATING_FORBIDDEN);

        $this->deleteJson("/api/v1/products/{$product->id}/ratings/{$otherRating->id}")->assertForbidden();

        $this->assertSame(1, $otherRating->fresh()->rating);
    }

    public function test_user_can_delete_one_of_their_ratings(): void
    {
        $product = Product::factory()->create();
        [$firstRating, $secondRating] = ProductRating::factory()->count(2)->for($product)->for($this->user)->create();

        $this->deleteJson("/api/v1/products/{$product->id}/ratings/{$firstRating->id}")->assertOk();

        $this->assertModelMissing($firstRating);
        $this->assertModelExists($secondRating);
    }

    public function test_rating_endpoints_return_not_found_for_a_rating_of_another_product(): void
    {
        $product = Product::factory()->create();
        $ratingOfOtherProduct = ProductRating::factory()->for($this->user)->create();

        $this->putJson("/api/v1/products/{$product->id}/ratings/{$ratingOfOtherProduct->id}", ['rating' => 5])->assertNotFound();
        $this->deleteJson("/api/v1/products/{$product->id}/ratings/{$ratingOfOtherProduct->id}")->assertNotFound();
        $this->deleteJson("/api/v1/products/{$product->id}/ratings/999")->assertNotFound();

        $this->assertModelExists($ratingOfOtherProduct);
    }
}

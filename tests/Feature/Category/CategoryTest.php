<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_all_categories_ordered_by_name(): void
    {
        Category::factory()->create(['name' => 'Sports']);
        Category::factory()->create(['name' => 'Books']);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonCount(2, 'data.categories')
            ->assertJsonPath('data.categories.0.name', 'Books')
            ->assertJsonPath('data.categories.1.name', 'Sports');
    }

    public function test_categories_require_authentication(): void
    {
        $this->getJson('/api/v1/categories')->assertUnauthorized();
    }
}

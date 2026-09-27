<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductRating;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductRatingSeeder extends Seeder
{
    /**
     * Fixed reviewer accounts, so running the seeder again reuses them instead of adding new users
     */
    protected const REVIEWERS = [
        ['name' => 'Nimal Perera', 'email' => 'nimal@example.com'],
        ['name' => 'Kasuni Silva', 'email' => 'kasuni@example.com'],
        ['name' => 'Ruwan Fernando', 'email' => 'ruwan@example.com'],
        ['name' => 'Dilini Jayasinghe', 'email' => 'dilini@example.com'],
        ['name' => 'Tharindu Bandara', 'email' => 'tharindu@example.com'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviewers = collect(self::REVIEWERS)->map(fn (array $reviewer) => User::firstOrCreate(
            ['email' => $reviewer['email']],
            ['name' => $reviewer['name'], 'password' => 'password', 'email_verified_at' => now()]
        ));

        Product::all()->each(function (Product $product) use ($reviewers) {
            $reviewers->random(fake()->numberBetween(1, $reviewers->count()))->each(function (User $user) use ($product) {
                // One rating per user per product (unique index), so update when it already exists
                ProductRating::updateOrCreate(
                    ['product_id' => $product->id, 'user_id' => $user->id],
                    ['rating' => fake()->numberBetween(1, 5), 'comment' => fake()->optional()->sentence()]
                );
            });
        });
    }
}

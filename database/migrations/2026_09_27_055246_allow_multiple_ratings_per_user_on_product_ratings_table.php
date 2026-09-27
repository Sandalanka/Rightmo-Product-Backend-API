<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A user may now write several ratings for the same product. The product_id foreign key
     * keeps using the (product_id, rating) index, and user_id gets its own index for ownership lookups.
     */
    public function up(): void
    {
        Schema::table('product_ratings', function (Blueprint $table) {
            $table->index('user_id');
            $table->dropUnique(['product_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Fails if a user already has more than one rating for a product.
     */
    public function down(): void
    {
        Schema::table('product_ratings', function (Blueprint $table) {
            $table->unique(['product_id', 'user_id']);
            $table->dropIndex(['user_id']);
        });
    }
};

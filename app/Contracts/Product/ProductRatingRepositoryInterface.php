<?php

namespace App\Contracts\Product;

use App\Models\ProductRating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRatingRepositoryInterface
{
    public function getForProduct(int $productId, int $perPage): LengthAwarePaginator;

    public function updateOrCreate(int $productId, int $userId, array $ratingData): ProductRating;

    public function findForUser(int $productId, int $userId): ?ProductRating;

    public function delete(ProductRating $productRating): void;
}

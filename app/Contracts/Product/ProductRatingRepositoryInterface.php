<?php

namespace App\Contracts\Product;

use App\Models\ProductRating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRatingRepositoryInterface
{
    public function getForProduct(int $productId, int $perPage): LengthAwarePaginator;

    public function create(int $productId, int $userId, array $ratingData): ProductRating;

    public function findForProduct(int $productId, int $ratingId): ?ProductRating;

    public function update(ProductRating $productRating, array $ratingData): ProductRating;

    public function delete(ProductRating $productRating): void;
}

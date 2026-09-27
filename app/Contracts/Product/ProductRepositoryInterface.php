<?php

namespace App\Contracts\Product;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function getAll(array $filters): LengthAwarePaginator;

    public function findWithRelationsById(int $productId): ?Product;

    public function findById(int $productId): ?Product;

    public function create(array $productData): Product;

    public function update(Product $product, array $productData): Product;

    public function delete(Product $product): void;
}

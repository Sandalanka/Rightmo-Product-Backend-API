<?php

namespace App\Contracts\Product;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Collection;

interface ProductImageRepositoryInterface
{
    public function createMany(Product $product, array $imagePaths): Collection;

    public function findForProduct(int $productId, int $imageId): ?ProductImage;

    public function update(ProductImage $productImage, string $imagePath): ProductImage;

    public function delete(ProductImage $productImage): void;
}

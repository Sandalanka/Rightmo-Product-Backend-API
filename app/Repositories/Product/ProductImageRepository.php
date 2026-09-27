<?php

namespace App\Repositories\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Contracts\Product\ProductImageRepositoryInterface;
use App\Models\Product;
use App\Models\ProductImage;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ProductImageRepository implements ProductImageRepositoryInterface
{
    /**
     * Summery: Create image rows for a product
     *
     * @param  array
     *
     * @throws Exception
     */
    public function createMany(Product $product, array $imagePaths): Collection
    {
        try {
            return $product->images()->createMany(
                array_map(fn (string $imagePath) => ['image_path' => $imagePath], $imagePaths)
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while creating product images-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Find an image that belongs to the given product
     *
     * @throws Exception
     */
    public function findForProduct(int $productId, int $imageId): ?ProductImage
    {
        try {
            return ProductImage::query()
                ->where('product_id', $productId)
                ->find($imageId);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching a product image-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Update image path
     *
     * @throws Exception
     */
    public function update(ProductImage $productImage, string $imagePath): ProductImage
    {
        try {
            $productImage->image_path = $imagePath;
            $productImage->save();

            return $productImage;

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while updating a product image-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Delete an image row
     *
     * @throws Exception
     */
    public function delete(ProductImage $productImage): void
    {
        try {
            $productImage->delete();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while deleting a product image-(repository): '
            );

            throw $exception;
        }
    }
}

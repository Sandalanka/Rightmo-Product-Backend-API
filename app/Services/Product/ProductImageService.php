<?php

namespace App\Services\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\CacheConstant;
use App\Contracts\Product\ProductImageRepositoryInterface;
use App\Contracts\Product\ProductRepositoryInterface;
use App\Http\Resources\Product\ProductImageResource;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageService
{
    protected ProductRepositoryInterface $productRepository;

    protected ProductImageRepositoryInterface $productImageRepository;

    public function __construct(ProductRepositoryInterface $productRepository,
        ProductImageRepositoryInterface $productImageRepository)
    {
        $this->productRepository = $productRepository;
        $this->productImageRepository = $productImageRepository;
    }

    /**
     * Summery: Upload and add images to a product
     *
     * @param  array<int, UploadedFile>  $images
     * @return array<int, array<string, mixed>>|null
     *
     * @throws Exception
     */
    public function add(int $productId, array $images): ?array
    {
        try {
            $product = $this->productRepository->findById($productId);

            if ($product === null) {
                return null;
            }

            $imagePaths = array_map(fn (UploadedFile $image) => $image->store('products', 'public'), $images);

            $productImages = $this->productImageRepository->createMany($product, $imagePaths);

            $this->flushCacheAfterCommit();

            return ProductImageResource::collection($productImages)->response()->getData(true)['data'];

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while adding product images-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Replace a product image file
     *
     * @return array<string, mixed>|null
     *
     * @throws Exception
     */
    public function update(int $productId, int $imageId, UploadedFile $image): ?array
    {
        try {
            $productImage = $this->productImageRepository->findForProduct($productId, $imageId);

            if ($productImage === null) {
                return null;
            }

            $oldImagePath = $productImage->image_path;

            $this->productImageRepository->update($productImage, $image->store('products', 'public'));

            DB::afterCommit(fn () => Storage::disk('public')->delete($oldImagePath));

            $this->flushCacheAfterCommit();

            return (new ProductImageResource($productImage))->response()->getData(true)['data'];

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while updating a product image-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Delete a product image
     *
     * @throws Exception
     */
    public function delete(int $productId, int $imageId): bool
    {
        try {
            $productImage = $this->productImageRepository->findForProduct($productId, $imageId);

            if ($productImage === null) {
                return false;
            }

            $imagePath = $productImage->image_path;

            $this->productImageRepository->delete($productImage);

            DB::afterCommit(fn () => Storage::disk('public')->delete($imagePath));

            $this->flushCacheAfterCommit();

            return true;

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while deleting a product image-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Invalidate product cache once the surrounding transaction commits
     */
    protected function flushCacheAfterCommit(): void
    {
        DB::afterCommit(fn () => Cache::forever(CacheConstant::PRODUCT_VERSION_KEY, (string) Str::ulid()));
    }
}

<?php

namespace App\Services\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\CacheConstant;
use App\Contracts\Product\ProductImageRepositoryInterface;
use App\Contracts\Product\ProductRepositoryInterface;
use App\Http\Resources\Product\ProductListResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
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
     * Summery: Get paginated products (cached)
     *
     * @param  array
     *
     * @throws Exception
     */
    public function getAll(array $filters): array
    {
        try {
            return Cache::remember($this->listCacheKey($filters), config('cache.ttl.products'),
                fn () => ProductListResource::collection($this->productRepository->getAll($filters))
                    ->response()->getData(true)
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching products-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Get product by id (cached)
     *
     * @return array<string, mixed>|null
     *
     * @throws Exception
     */
    public function getById(int $productId): ?array
    {
        try {
            return Cache::remember($this->itemCacheKey($productId), config('cache.ttl.products'),
                function () use ($productId) {
                    $product = $this->productRepository->findWithRelationsById($productId);

                    return $product ? $this->toArray($product) : null;
                }
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching a product by id-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Create a product with optional images
     *
     * @param  array
     * @param  array
     *
     * @throws Exception
     */
    public function create(array $productData, array $images = []): array
    {
        try {
            $product = $this->productRepository->create($productData);

            if (! empty($images)) {
                $imagePaths = array_map(fn (UploadedFile $image) => $image->store('products', 'public'), $images);

                $this->productImageRepository->createMany($product, $imagePaths);
            }

            $this->flushCacheAfterCommit();

            return $this->toArray($this->productRepository->findWithRelationsById($product->id));

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while creating a product-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Update a product
     *
     * @param  array
     *
     * @throws Exception
     */
    public function update(int $productId, array $productData): ?array
    {
        try {
            $product = $this->productRepository->findById($productId);

            if ($product === null) {
                return null;
            }

            $this->productRepository->update($product, $productData);

            $this->flushCacheAfterCommit();

            return $this->toArray($this->productRepository->findWithRelationsById($product->id));

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while updating a product-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Delete a product and its image files
     *
     * @throws Exception
     */
    public function delete(int $productId): bool
    {
        try {
            $product = $this->productRepository->findWithRelationsById($productId);

            if ($product === null) {
                return false;
            }

            $imagePaths = $product->images->pluck('image_path')->all();

            $this->productRepository->delete($product);

            DB::afterCommit(fn () => Storage::disk('public')->delete($imagePaths));

            $this->flushCacheAfterCommit();

            return true;

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while deleting a product-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Invalidate product cache once the surrounding transaction commits.
     */
    protected function flushCacheAfterCommit(): void
    {
        DB::afterCommit(fn () => Cache::forever(CacheConstant::PRODUCT_VERSION_KEY, (string) Str::ulid()));
    }

    /**
     * Summery: Cache key for a product list page (every filter / sort / page combination gets its own key)
     *
     * @param  array
     */
    protected function listCacheKey(array $filters): string
    {
        ksort($filters);

        return 'products:'.$this->cacheVersion().':list:'.md5(json_encode($filters));
    }

    /**
     * Summery: Cache key for a single product
     */
    protected function itemCacheKey(int $productId): string
    {
        return 'products:'.$this->cacheVersion().':item:'.$productId;
    }

    /**
     * Summery: Current product cache version
     */
    protected function cacheVersion(): string
    {
        return Cache::rememberForever(CacheConstant::PRODUCT_VERSION_KEY, fn () => (string) Str::ulid());
    }

    /**
     * Summery: Convert product to a plain array (cache safe)
     */
    protected function toArray(Product $product): array
    {
        return (new ProductResource($product))->response()->getData(true)['data'];
    }
}

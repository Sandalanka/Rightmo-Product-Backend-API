<?php

namespace App\Services\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\CacheConstant;
use App\Contracts\Product\ProductRatingRepositoryInterface;
use App\Contracts\Product\ProductRepositoryInterface;
use App\Http\Resources\Product\ProductRatingResource;
use App\Models\ProductRating;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductRatingService
{
    protected ProductRepositoryInterface $productRepository;

    protected ProductRatingRepositoryInterface $productRatingRepository;

    public function __construct(ProductRepositoryInterface $productRepository,
        ProductRatingRepositoryInterface $productRatingRepository)
    {
        $this->productRepository = $productRepository;
        $this->productRatingRepository = $productRatingRepository;
    }

    /**
     * Summery: Get paginated ratings of a product
     *
     * @return array{data: array<int, mixed>, links: array<string, mixed>, meta: array<string, mixed>}|null
     *
     * @throws Exception
     */
    public function getForProduct(int $productId, int $perPage): ?array
    {
        try {
            if ($this->productRepository->findById($productId) === null) {
                return null;
            }

            return ProductRatingResource::collection($this->productRatingRepository->getForProduct($productId, $perPage))
                ->response()->getData(true);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching product ratings-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Rate a product (a user may rate the same product several times)
     *
     * @param  array{rating: int, comment?: ?string}  $ratingData
     * @return array<string, mixed>|null
     *
     * @throws Exception
     */
    public function rate(int $productId, int $userId, array $ratingData): ?array
    {
        try {
            if ($this->productRepository->findById($productId) === null) {
                return null;
            }

            $productRating = $this->productRatingRepository->create($productId, $userId, $ratingData);

            $this->flushCacheAfterCommit();

            return $this->toArray($productRating);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while rating a product-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Find a rating that belongs to a product
     *
     * @throws Exception
     */
    public function findForProduct(int $productId, int $ratingId): ?ProductRating
    {
        try {
            return $this->productRatingRepository->findForProduct($productId, $ratingId);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching a product rating-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Update a rating
     *
     * @param  array{rating?: int, comment?: ?string}  $ratingData
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function update(ProductRating $productRating, array $ratingData): array
    {
        try {
            $productRating = $this->productRatingRepository->update($productRating, $ratingData);

            $this->flushCacheAfterCommit();

            return $this->toArray($productRating);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while updating a product rating-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Delete a rating
     *
     * @throws Exception
     */
    public function delete(ProductRating $productRating): void
    {
        try {
            $this->productRatingRepository->delete($productRating);

            $this->flushCacheAfterCommit();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while deleting a product rating-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Convert a rating to its API representation
     *
     * @return array<string, mixed>
     */
    protected function toArray(ProductRating $productRating): array
    {
        return (new ProductRatingResource($productRating->load('user:id,name')))->response()->getData(true)['data'];
    }

    /**
     * Summery: Invalidate product cache once the surrounding transaction commits
     */
    protected function flushCacheAfterCommit(): void
    {
        DB::afterCommit(fn () => Cache::forever(CacheConstant::PRODUCT_VERSION_KEY, (string) Str::ulid()));
    }
}

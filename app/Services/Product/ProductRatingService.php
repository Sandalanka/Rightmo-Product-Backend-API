<?php

namespace App\Services\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\CacheConstant;
use App\Contracts\Product\ProductRatingRepositoryInterface;
use App\Contracts\Product\ProductRepositoryInterface;
use App\Http\Resources\Product\ProductRatingResource;
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
     * Summery: Rate a product (a second rating by the same user updates the first)
     *
     * @param  array{rating: int, comment?: ?string}  $ratingData
     * @return array{rating: array<string, mixed>, created: bool}|null
     *
     * @throws Exception
     */
    public function rate(int $productId, int $userId, array $ratingData): ?array
    {
        try {
            if ($this->productRepository->findById($productId) === null) {
                return null;
            }

            $productRating = $this->productRatingRepository->updateOrCreate($productId, $userId, $ratingData);

            $this->flushCacheAfterCommit();

            return [
                'rating' => (new ProductRatingResource($productRating->load('user:id,name')))->response()->getData(true)['data'],
                'created' => $productRating->wasRecentlyCreated,
            ];

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while rating a product-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Delete the rating a user gave a product
     *
     * @throws Exception
     */
    public function delete(int $productId, int $userId): bool
    {
        try {
            $productRating = $this->productRatingRepository->findForUser($productId, $userId);

            if ($productRating === null) {
                return false;
            }

            $this->productRatingRepository->delete($productRating);

            $this->flushCacheAfterCommit();

            return true;

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while deleting a product rating-(service): '
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

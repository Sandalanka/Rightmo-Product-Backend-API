<?php

namespace App\Repositories\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Contracts\Product\ProductRatingRepositoryInterface;
use App\Models\ProductRating;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRatingRepository implements ProductRatingRepositoryInterface
{
    /**
     * Summery: Get paginated ratings of a product (newest first)
     *
     * @return LengthAwarePaginator<int, ProductRating>
     *
     * @throws Exception
     */
    public function getForProduct(int $productId, int $perPage): LengthAwarePaginator
    {
        try {
            return ProductRating::query()
                ->select(['id', 'product_id', 'user_id', 'rating', 'comment', 'created_at', 'updated_at'])
                ->with('user:id,name')
                ->where('product_id', $productId)
                ->latest()
                ->orderByDesc('id')
                ->paginate($perPage)
                ->withQueryString();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching product ratings-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Create or update the rating a user gave a product
     *
     * @param  array
     *
     * @throws Exception
     */
    public function updateOrCreate(int $productId, int $userId, array $ratingData): ProductRating
    {
        try {
            return ProductRating::updateOrCreate(
                ['product_id' => $productId, 'user_id' => $userId],
                ['rating' => $ratingData['rating'], 'comment' => $ratingData['comment'] ?? null]
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while saving a product rating-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Find the rating a user gave a product
     *
     * @throws Exception
     */
    public function findForUser(int $productId, int $userId): ?ProductRating
    {
        try {
            return ProductRating::query()
                ->where('product_id', $productId)
                ->where('user_id', $userId)
                ->first();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching a product rating-(repository): '
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
            $productRating->delete();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while deleting a product rating-(repository): '
            );

            throw $exception;
        }
    }
}

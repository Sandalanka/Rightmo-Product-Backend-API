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
     * Summery: Create a rating a user gave a product
     *
     * @param  array{rating: int, comment?: ?string}  $ratingData
     *
     * @throws Exception
     */
    public function create(int $productId, int $userId, array $ratingData): ProductRating
    {
        try {
            return ProductRating::create([
                'product_id' => $productId,
                'user_id' => $userId,
                'rating' => $ratingData['rating'],
                'comment' => $ratingData['comment'] ?? null,
            ]);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while creating a product rating-(repository): '
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
            return ProductRating::query()
                ->where('product_id', $productId)
                ->find($ratingId);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching a product rating-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Update a rating
     *
     * @param  array{rating?: int, comment?: ?string}  $ratingData
     *
     * @throws Exception
     */
    public function update(ProductRating $productRating, array $ratingData): ProductRating
    {
        try {
            $productRating->update($ratingData);

            return $productRating;

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while updating a product rating-(repository): '
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

<?php

namespace App\Http\Controllers\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\MessageConstant;
use App\Constants\StatusCodeConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductRatingIndexRequest;
use App\Http\Requests\Product\ProductRatingStoreRequest;
use App\Http\Requests\Product\ProductRatingUpdateRequest;
use App\Models\ProductRating;
use App\Services\Product\ProductRatingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductRatingController extends Controller
{
    protected ProductRatingService $productRatingService;

    public function __construct(ProductRatingService $productRatingService)
    {
        $this->productRatingService = $productRatingService;
    }

    /**
     * Summery: Get paginated ratings of a product
     */
    public function index(ProductRatingIndexRequest $request, int $productId): JsonResponse
    {
        try {
            $ratings = $this->productRatingService->getForProduct($productId, $request->perPage());

            if ($ratings !== null) {

                return $this->successResponse(
                    data: [
                        'ratings' => $ratings['data'],
                        'pagination' => $ratings['meta'],
                        'links' => $ratings['links'],
                    ],
                    message: MessageConstant::PRODUCT_RATINGS_FETCHED
                );
            }

            return $this->errorResponse(
                message: MessageConstant::PRODUCT_NOT_FOUND,
                statusCode: StatusCodeConstant::NOT_FOUND
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception, 'An error occurred while fetching product ratings-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: Rate a product (a user may rate the same product several times)
     *
     * @throws Throwable
     */
    public function store(ProductRatingStoreRequest $request, int $productId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $rating = $this->productRatingService->rate($productId, $request->user()->id, $request->validated());

            DB::commit();

            if ($rating !== null) {

                return $this->successResponse(
                    data: $rating,
                    message: MessageConstant::PRODUCT_RATED,
                    statusCode: StatusCodeConstant::CREATED
                );
            }

            return $this->errorResponse(
                message: MessageConstant::PRODUCT_NOT_FOUND,
                statusCode: StatusCodeConstant::NOT_FOUND
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while rating a product-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: Update one of the authenticated user's ratings
     *
     * @throws Throwable
     */
    public function update(ProductRatingUpdateRequest $request, int $productId, int $ratingId): JsonResponse
    {
        try {
            $productRating = $this->productRatingService->findForProduct($productId, $ratingId);

            $deniedResponse = $this->denyUnlessOwner($productRating, $request);

            if ($deniedResponse !== null) {
                return $deniedResponse;
            }

            DB::beginTransaction();

            $rating = $this->productRatingService->update($productRating, $request->validated());

            DB::commit();

            return $this->successResponse(
                data: $rating,
                message: MessageConstant::PRODUCT_RATING_UPDATED
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while updating a product rating-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: Delete one of the authenticated user's ratings
     *
     * @throws Throwable
     */
    public function destroy(Request $request, int $productId, int $ratingId): JsonResponse
    {
        try {
            $productRating = $this->productRatingService->findForProduct($productId, $ratingId);

            $deniedResponse = $this->denyUnlessOwner($productRating, $request);

            if ($deniedResponse !== null) {
                return $deniedResponse;
            }

            DB::beginTransaction();

            $this->productRatingService->delete($productRating);

            DB::commit();

            return $this->successResponse(
                message: MessageConstant::PRODUCT_RATING_DELETED
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while deleting a product rating-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: 404 when the rating does not belong to the product, 403 when it belongs to another user
     */
    protected function denyUnlessOwner(?ProductRating $productRating, Request $request): ?JsonResponse
    {
        if ($productRating === null) {
            return $this->errorResponse(
                message: MessageConstant::PRODUCT_RATING_NOT_FOUND,
                statusCode: StatusCodeConstant::NOT_FOUND
            );
        }

        if ($productRating->user_id !== $request->user()->id) {
            return $this->errorResponse(
                message: MessageConstant::PRODUCT_RATING_FORBIDDEN,
                statusCode: StatusCodeConstant::FORBIDDEN
            );
        }

        return null;
    }
}

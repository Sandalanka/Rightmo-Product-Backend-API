<?php

namespace App\Http\Controllers\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\MessageConstant;
use App\Constants\StatusCodeConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductRatingIndexRequest;
use App\Http\Requests\Product\ProductRatingStoreRequest;
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
     * Summery: Rate a product (rating again updates the user's existing rating)
     *
     * @throws Throwable
     */
    public function store(ProductRatingStoreRequest $request, int $productId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $result = $this->productRatingService->rate($productId, $request->user()->id, $request->validated());

            DB::commit();

            if ($result !== null) {

                return $this->successResponse(
                    data: $result['rating'],
                    message: $result['created'] ? MessageConstant::PRODUCT_RATED : MessageConstant::PRODUCT_RATING_UPDATED,
                    statusCode: $result['created'] ? StatusCodeConstant::CREATED : StatusCodeConstant::OK
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
     * Summery: Delete the authenticated user's rating of a product
     *
     * @throws Throwable
     */
    public function destroy(Request $request, int $productId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $isDeleted = $this->productRatingService->delete($productId, $request->user()->id);

            DB::commit();

            if ($isDeleted === true) {

                return $this->successResponse(
                    message: MessageConstant::PRODUCT_RATING_DELETED
                );
            }

            return $this->errorResponse(
                message: MessageConstant::PRODUCT_RATING_NOT_FOUND,
                statusCode: StatusCodeConstant::NOT_FOUND
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while deleting a product rating-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }
}

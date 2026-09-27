<?php

namespace App\Http\Controllers\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\MessageConstant;
use App\Constants\StatusCodeConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductImageStoreRequest;
use App\Http\Requests\Product\ProductImageUpdateRequest;
use App\Services\Product\ProductImageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductImageController extends Controller
{
    protected ProductImageService $productImageService;

    public function __construct(ProductImageService $productImageService)
    {
        $this->productImageService = $productImageService;
    }

    /**
     * Summery: Add images to a product
     *
     * @throws Throwable
     */
    public function store(ProductImageStoreRequest $request, int $productId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $images = $this->productImageService->add($productId, $request->file('images'));

            DB::commit();

            if ($images !== null) {

                return $this->successResponse(
                    data: ['images' => $images],
                    message: MessageConstant::PRODUCT_IMAGES_ADDED,
                    statusCode: StatusCodeConstant::CREATED
                );
            }

            return $this->errorResponse(
                message: MessageConstant::PRODUCT_NOT_FOUND,
                statusCode: StatusCodeConstant::NOT_FOUND
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while adding product images-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: Replace a product image
     *
     * @throws Throwable
     */
    public function update(ProductImageUpdateRequest $request, int $productId, int $imageId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $image = $this->productImageService->update($productId, $imageId, $request->file('image'));

            DB::commit();

            if ($image !== null) {

                return $this->successResponse(
                    data: $image,
                    message: MessageConstant::PRODUCT_IMAGE_UPDATED
                );
            }

            return $this->errorResponse(
                message: MessageConstant::PRODUCT_IMAGE_NOT_FOUND,
                statusCode: StatusCodeConstant::NOT_FOUND
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while updating a product image-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: Delete a product image
     *
     * @throws Throwable
     */
    public function destroy(int $productId, int $imageId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $isDeleted = $this->productImageService->delete($productId, $imageId);

            DB::commit();

            if ($isDeleted === true) {

                return $this->successResponse(
                    message: MessageConstant::PRODUCT_IMAGE_DELETED
                );
            }

            return $this->errorResponse(
                message: MessageConstant::PRODUCT_IMAGE_NOT_FOUND,
                statusCode: StatusCodeConstant::NOT_FOUND
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while deleting a product image-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }
}

<?php

namespace App\Http\Controllers\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\MessageConstant;
use App\Constants\StatusCodeConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductIndexRequest;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Services\Product\ProductService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Summery: Get all products (paginated, searchable, filterable, sortable)
     */
    public function index(ProductIndexRequest $request): JsonResponse
    {
        try {
            $products = $this->productService->getAll($request->filters());

            return $this->successResponse(
                data: [
                    'products' => $products['data'],
                    'pagination' => $products['meta'],
                    'links' => $products['links'],
                ],
                message: MessageConstant::PRODUCTS_FETCHED
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception, 'An error occurred while fetching products-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: Get product by id
     */
    public function show(int $productId): JsonResponse
    {
        try {
            $product = $this->productService->getById($productId);

            if ($product !== null) {

                return $this->successResponse(
                    data: $product,
                    message: MessageConstant::PRODUCT_FETCHED
                );
            }

            return $this->errorResponse(
                message: MessageConstant::PRODUCT_NOT_FOUND,
                statusCode: StatusCodeConstant::NOT_FOUND
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception, 'An error occurred while fetching a product-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: Create a product
     *
     * @throws Throwable
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $product = $this->productService->create(
                $request->safe()->except('images'),
                $request->file('images', [])
            );

            DB::commit();

            return $this->successResponse(
                data: $product,
                message: MessageConstant::PRODUCT_CREATED,
                statusCode: StatusCodeConstant::CREATED
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while creating a product-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: Update a product
     *
     * @throws Throwable
     */
    public function update(ProductUpdateRequest $request, int $productId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $product = $this->productService->update($productId, $request->validated());

            DB::commit();

            if ($product !== null) {

                return $this->successResponse(
                    data: $product,
                    message: MessageConstant::PRODUCT_UPDATED
                );
            }

            return $this->errorResponse(
                message: MessageConstant::PRODUCT_NOT_FOUND,
                statusCode: StatusCodeConstant::NOT_FOUND
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while updating a product-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: Delete a product
     *
     * @throws Throwable
     */
    public function destroy(int $productId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $isDeleted = $this->productService->delete($productId);

            DB::commit();

            if ($isDeleted === true) {

                return $this->successResponse(
                    message: MessageConstant::PRODUCT_DELETED
                );
            }

            return $this->errorResponse(
                message: MessageConstant::PRODUCT_NOT_FOUND,
                statusCode: StatusCodeConstant::NOT_FOUND
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while deleting a product-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }
}

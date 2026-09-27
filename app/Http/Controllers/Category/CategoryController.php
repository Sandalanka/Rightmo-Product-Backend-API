<?php

namespace App\Http\Controllers\Category;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\MessageConstant;
use App\Http\Controllers\Controller;
use App\Services\Category\CategoryService;
use Exception;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Summery: Get all categories
     */
    public function index(): JsonResponse
    {
        try {
            $categories = $this->categoryService->getAll();

            return $this->successResponse(
                data: ['categories' => $categories],
                message: MessageConstant::CATEGORIES_FETCHED
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception, 'An error occurred while fetching categories-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }
}

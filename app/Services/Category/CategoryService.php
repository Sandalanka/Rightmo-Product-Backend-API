<?php

namespace App\Services\Category;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\CacheConstant;
use App\Contracts\Category\CategoryRepositoryInterface;
use App\Http\Resources\Category\CategoryResource;
use Exception;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Summery: Get all categories (cached)
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws Exception
     */
    public function getAll(): array
    {
        try {
            return Cache::remember(CacheConstant::CATEGORY_ALL_KEY, config('cache.ttl.categories'),
                fn () => CategoryResource::collection($this->categoryRepository->getAll())
                    ->response()->getData(true)['data']
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching categories-(service): '
            );

            throw $exception;
        }
    }
}

<?php

namespace App\Repositories\Category;

use App\Classes\Common\ApiCatchErrors;
use App\Contracts\Category\CategoryRepositoryInterface;
use App\Models\Category;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Summery: Get all categories
     *
     *
     * @throws Exception
     */
    public function getAll(): Collection
    {
        try {
            return Category::query()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching categories-(repository): '
            );

            throw $exception;
        }
    }
}

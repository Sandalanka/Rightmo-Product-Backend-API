<?php

namespace App\Repositories\Product;

use App\Classes\Common\ApiCatchErrors;
use App\Contracts\Product\ProductRepositoryInterface;
use App\Models\Product;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Summery: Get paginated products with search, filter and sort
     *
     * @param  array
     *
     * @throws Exception
     */
    public function getAll(array $filters): LengthAwarePaginator
    {
        try {
            return Product::query()
                ->select(['id', 'name', 'category_id', 'price'])
                ->with(['category:id,name', 'firstImage'])
                ->withAvg('ratings', 'rating')
                ->when($filters['search'], function (Builder $query, string $search) {
                    $search = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search);

                    $query->whereRaw("name LIKE ? ESCAPE '!'", ['%'.$search.'%']);
                })
                ->when($filters['category_id'], fn (Builder $query, int $categoryId) => $query->where('category_id', $categoryId))
                ->when($filters['min_price'] !== null, fn (Builder $query) => $query->where('price', '>=', $filters['min_price']))
                ->when($filters['max_price'] !== null, fn (Builder $query) => $query->where('price', '<=', $filters['max_price']))
                // "rating" sorts by the average rating (withAvg alias); unrated products (NULL) sort as the lowest
                ->orderBy($filters['sort_by'] === 'rating' ? 'ratings_avg_rating' : $filters['sort_by'], $filters['sort_order'])
                ->orderBy('id', $filters['sort_order'])
                ->paginate(perPage: $filters['per_page'], page: $filters['page'])
                ->withQueryString();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching products-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Find product by id with category and images
     *
     * @throws Exception
     */
    public function findWithRelationsById(int $productId): ?Product
    {
        try {
            return Product::query()
                ->select(['id', 'name', 'description', 'category_id', 'price', 'created_at', 'updated_at'])
                ->with(['category:id,name', 'images:id,product_id,image_path'])
                ->withAvg('ratings', 'rating')
                ->withCount('ratings')
                ->find($productId);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching a product by id-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Find product by id (no relations)
     *
     * @throws Exception
     */
    public function findById(int $productId): ?Product
    {
        try {
            return Product::find($productId);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching a product by id-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Create a product
     *
     * @param  array
     *
     * @throws Exception
     */
    public function create(array $productData): Product
    {
        try {
            return Product::create([
                'name' => $productData['name'],
                'description' => $productData['description'] ?? null,
                'category_id' => $productData['category_id'],
                'price' => $productData['price'],
            ]);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while creating a product-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Update a product
     *
     * @param  array
     *
     * @throws Exception
     */
    public function update(Product $product, array $productData): Product
    {
        try {
            $product->name = $productData['name'] ?? $product->name;
            $product->category_id = $productData['category_id'] ?? $product->category_id;
            $product->price = $productData['price'] ?? $product->price;

            if (array_key_exists('description', $productData)) {
                $product->description = $productData['description'];
            }

            $product->save();

            return $product;

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while updating a product-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Delete a product (images rows are removed by cascade)
     *
     * @throws Exception
     */
    public function delete(Product $product): void
    {
        try {
            $product->delete();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while deleting a product-(repository): '
            );

            throw $exception;
        }
    }
}

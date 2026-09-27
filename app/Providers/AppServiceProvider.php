<?php

namespace App\Providers;

use App\Contracts\Auth\AuthRepositoryInterface;
use App\Contracts\Category\CategoryRepositoryInterface;
use App\Contracts\Product\ProductImageRepositoryInterface;
use App\Contracts\Product\ProductRatingRepositoryInterface;
use App\Contracts\Product\ProductRepositoryInterface;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Product\ProductImageRepository;
use App\Repositories\Product\ProductRatingRepository;
use App\Repositories\Product\ProductRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductImageRepositoryInterface::class, ProductImageRepository::class);
        $this->app->bind(ProductRatingRepositoryInterface::class, ProductRatingRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Throw on lazy loaded relations (N+1) outside production, so missing eager loads are caught early
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}

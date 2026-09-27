<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Product catalogue REST API: authentication, categories, products, product images and product ratings.',
    title: 'Rightmo Product API',
    license: new OA\License(name: 'MIT', url: 'https://opensource.org/licenses/MIT'),
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Sanctum personal access token from /auth/login or /auth/register. Enter only the token (without "Bearer").',
    bearerFormat: 'Token',
    scheme: 'bearer',
)]
#[OA\Tag(name: 'Auth', description: 'Register, login and logout')]
#[OA\Tag(name: 'Categories', description: 'Product categories')]
#[OA\Tag(name: 'Products', description: 'Product CRUD with search, filter, sort and pagination')]
#[OA\Tag(name: 'Product Images', description: 'Add, replace and delete product images')]
#[OA\Tag(name: 'Product Ratings', description: 'Rate products (1 to 5) and list ratings')]
class OpenApi {}

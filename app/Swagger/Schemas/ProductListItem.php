<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductListItem',
    description: 'Product in the list: first image only (use GET /products/{productId} for description, all images, rating count and dates)',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Wireless Headphones'),
        new OA\Property(property: 'price', description: 'Decimal with 2 places, returned as a string', type: 'string', example: '199.99'),
        new OA\Property(property: 'average_rating', description: 'Average rating rounded to 1 decimal, 0 when not rated', type: 'number', format: 'float', example: 4.7),
        new OA\Property(property: 'category_name', type: 'string', example: 'Electronics'),
        new OA\Property(property: 'image_url', description: 'URL of the first image, null when the product has no images', type: 'string', example: 'http://localhost/storage/products/abc123.jpg', nullable: true),
    ],
    type: 'object',
)]
class ProductListItem {}

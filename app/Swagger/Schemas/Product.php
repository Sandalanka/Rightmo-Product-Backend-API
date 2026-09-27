<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Product',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Wireless Headphones'),
        new OA\Property(property: 'description', type: 'string', example: 'Noise cancelling over-ear headphones.', nullable: true),
        new OA\Property(property: 'price', description: 'Decimal with 2 places, returned as a string', type: 'string', example: '199.99'),
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
        new OA\Property(property: 'average_rating', description: 'Average rating rounded to 1 decimal, 0 when not rated', type: 'number', format: 'float', example: 4.7),
        new OA\Property(property: 'ratings_count', type: 'integer', example: 12),
        new OA\Property(property: 'category', ref: '#/components/schemas/Category'),
        new OA\Property(property: 'images', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductImage')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
class Product {}

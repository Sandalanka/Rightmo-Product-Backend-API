<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductRating',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1, example: 5),
        new OA\Property(property: 'comment', type: 'string', example: 'Great sound quality.', nullable: true),
        new OA\Property(
            property: 'user',
            description: 'Public reviewer details',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 3),
                new OA\Property(property: 'name', type: 'string', example: 'Jane Doe'),
            ],
            type: 'object',
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
class ProductRating {}

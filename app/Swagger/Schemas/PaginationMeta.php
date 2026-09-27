<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginationMeta',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'from', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'last_page', type: 'integer', example: 5),
        new OA\Property(property: 'path', type: 'string', example: 'http://localhost/api/v1/products'),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'to', type: 'integer', example: 15, nullable: true),
        new OA\Property(property: 'total', type: 'integer', example: 70),
        new OA\Property(
            property: 'links',
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'url', type: 'string', nullable: true),
                new OA\Property(property: 'label', type: 'string'),
                new OA\Property(property: 'active', type: 'boolean'),
            ], type: 'object'),
        ),
    ],
    type: 'object',
)]
class PaginationMeta {}

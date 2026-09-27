<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginationLinks',
    properties: [
        new OA\Property(property: 'first', type: 'string', example: 'http://localhost/api/v1/products?page=1'),
        new OA\Property(property: 'last', type: 'string', example: 'http://localhost/api/v1/products?page=5'),
        new OA\Property(property: 'prev', type: 'string', example: null, nullable: true),
        new OA\Property(property: 'next', type: 'string', example: 'http://localhost/api/v1/products?page=2', nullable: true),
    ],
    type: 'object',
)]
class PaginationLinks {}

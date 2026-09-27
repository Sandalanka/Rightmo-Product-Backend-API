<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'error'),
        new OA\Property(property: 'message', type: 'string', example: 'Product not found.'),
        new OA\Property(property: 'timestamp', type: 'string', example: '2026-09-26 10:30:00'),
    ],
    type: 'object',
)]
class ErrorResponse {}

<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SuccessResponse',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'success'),
        new OA\Property(property: 'timestamp', type: 'string', example: '2026-09-26 10:30:00'),
        new OA\Property(property: 'message', type: 'string', example: 'Request completed successfully.'),
    ],
    type: 'object',
)]
class SuccessResponse {}

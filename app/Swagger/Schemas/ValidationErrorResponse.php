<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationErrorResponse',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'failed'),
        new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
        new OA\Property(
            property: 'errors',
            description: 'Field name => list of error messages',
            type: 'object',
            example: ['name' => ['The name field is required.']],
            additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string')),
        ),
        new OA\Property(property: 'timestamp', type: 'string', example: '2026-09-26 10:30:00'),
    ],
    type: 'object',
)]
class ValidationErrorResponse {}

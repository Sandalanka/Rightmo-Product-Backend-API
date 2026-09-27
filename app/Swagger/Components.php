<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'Unauthenticated',
    description: 'Missing or invalid Bearer token',
    content: new OA\JsonContent(allOf: [
        new OA\Schema(ref: '#/components/schemas/ErrorResponse'),
        new OA\Schema(properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated. Please login and send a valid Bearer token.'),
        ]),
    ]),
)]
#[OA\Response(
    response: 'ValidationError',
    description: 'Validation failed',
    content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'),
)]
#[OA\Response(
    response: 'ProductNotFound',
    description: 'Product not found',
    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
)]
#[OA\Response(
    response: 'ServerError',
    description: 'Unexpected server error',
    content: new OA\JsonContent(allOf: [
        new OA\Schema(ref: '#/components/schemas/ErrorResponse'),
        new OA\Schema(properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Something went wrong.'),
            new OA\Property(property: 'errors', description: 'Exception message (only when APP_DEBUG is true)', type: 'string'),
        ]),
    ]),
)]
#[OA\Parameter(
    parameter: 'ProductId',
    name: 'productId',
    description: 'Product id',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer', example: 1),
)]
#[OA\Parameter(
    parameter: 'ImageId',
    name: 'imageId',
    description: 'Product image id',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'integer', example: 1),
)]
class Components {}

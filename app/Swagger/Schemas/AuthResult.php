<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthResult',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'token', type: 'string', example: '1|Xy7abc...'),
        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
    ],
    type: 'object',
)]
class AuthResult {}

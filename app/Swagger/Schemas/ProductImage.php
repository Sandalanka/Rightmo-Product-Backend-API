<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductImage',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'image_url', type: 'string', example: 'http://localhost/storage/products/abc123.jpg'),
    ],
    type: 'object',
)]
class ProductImage {}

<?php

namespace App\Swagger\Paths;

use OpenApi\Attributes as OA;

class ProductImageApi
{
    #[OA\Post(
        path: '/products/{productId}/images',
        operationId: 'productImageStore',
        summary: 'Add images to a product',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['images[]'],
                properties: [
                    new OA\Property(property: 'images[]', description: '1 to 10 images (jpg, jpeg, png, webp, max 2MB each)', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                ],
            ),
        )),
        tags: ['Product Images'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/ProductId')],
        responses: [
            new OA\Response(response: 201, description: 'Images added', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Product images added successfully.'),
                    new OA\Property(property: 'data', properties: [
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductImage')),
                    ], type: 'object'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/ProductNotFound', response: 404),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function store(): void {}

    #[OA\Post(
        path: '/products/{productId}/images/{imageId}',
        operationId: 'productImageUpdate',
        description: 'The route is PUT, but PHP cannot read files from a multipart PUT request, so send POST with `_method=PUT` (Laravel method spoofing).',
        summary: 'Replace a product image',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['_method', 'image'],
                properties: [
                    new OA\Property(property: '_method', type: 'string', default: 'PUT', enum: ['PUT']),
                    new OA\Property(property: 'image', description: 'jpg, jpeg, png or webp, max 2MB', type: 'string', format: 'binary'),
                ],
            ),
        )),
        tags: ['Product Images'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/ProductId'),
            new OA\Parameter(ref: '#/components/parameters/ImageId'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Image replaced', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Product image updated successfully.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/ProductImage'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(response: 404, description: 'Image not found for this product', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/ErrorResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'Product image not found.')]),
            ])),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function update(): void {}

    #[OA\Delete(
        path: '/products/{productId}/images/{imageId}',
        operationId: 'productImageDestroy',
        summary: 'Delete a product image',
        security: [['sanctum' => []]],
        tags: ['Product Images'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/ProductId'),
            new OA\Parameter(ref: '#/components/parameters/ImageId'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Image deleted', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'Product image deleted successfully.')]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(response: 404, description: 'Image not found for this product', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/ErrorResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'Product image not found.')]),
            ])),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function destroy(): void {}
}

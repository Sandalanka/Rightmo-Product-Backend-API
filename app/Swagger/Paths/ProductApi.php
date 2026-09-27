<?php

namespace App\Swagger\Paths;

use OpenApi\Attributes as OA;

class ProductApi
{
    #[OA\Get(
        path: '/products',
        operationId: 'productIndex',
        summary: 'List products with average rating and first image (paginated, searchable, filterable, sortable, cached)',
        security: [['sanctum' => []]],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(name: 'search', description: 'Search by product name', in: 'query', schema: new OA\Schema(type: 'string', maxLength: 255)),
            new OA\Parameter(name: 'category_id', description: 'Filter by category', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'min_price', in: 'query', schema: new OA\Schema(type: 'number', minimum: 0)),
            new OA\Parameter(name: 'max_price', description: 'Must be >= min_price', in: 'query', schema: new OA\Schema(type: 'number', minimum: 0)),
            new OA\Parameter(name: 'sort_by', description: 'rating = average rating (unrated products count as lowest)', in: 'query', schema: new OA\Schema(type: 'string', default: 'created_at', enum: ['price', 'rating', 'name', 'created_at'])),
            new OA\Parameter(name: 'sort_order', in: 'query', schema: new OA\Schema(type: 'string', default: 'desc', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15, maximum: 100, minimum: 1)),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Product page', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Products fetched successfully.'),
                    new OA\Property(property: 'data', properties: [
                        new OA\Property(property: 'products', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductListItem')),
                        new OA\Property(property: 'pagination', ref: '#/components/schemas/PaginationMeta'),
                        new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
                    ], type: 'object'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/products/{productId}',
        operationId: 'productShow',
        summary: 'Get a product with category, all images, average rating and rating count (cached)',
        security: [['sanctum' => []]],
        tags: ['Products'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/ProductId')],
        responses: [
            new OA\Response(response: 200, description: 'Product details', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Product fetched successfully.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/Product'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/ProductNotFound', response: 404),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function show(): void {}

    #[OA\Post(
        path: '/products',
        operationId: 'productStore',
        summary: 'Create a product with optional images',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['name', 'category_id', 'price'],
                properties: [
                    new OA\Property(property: 'name', description: 'Must be unique', type: 'string', maxLength: 255, example: 'Wireless Headphones'),
                    new OA\Property(property: 'description', type: 'string', maxLength: 5000, nullable: true),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                    new OA\Property(property: 'price', description: 'Up to 2 decimal places', type: 'number', maximum: 99999999.99, minimum: 0, example: 199.99),
                    new OA\Property(property: 'images[]', description: 'Up to 10 images (jpg, jpeg, png, webp, max 2MB each)', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                ],
            ),
        )),
        tags: ['Products'],
        responses: [
            new OA\Response(response: 201, description: 'Product created', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Product created successfully.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/Product'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function store(): void {}

    #[OA\Put(
        path: '/products/{productId}',
        operationId: 'productUpdate',
        summary: 'Update a product (images are managed with the Product Images endpoints)',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            description: 'Send only the fields to change',
            properties: [
                new OA\Property(property: 'name', description: 'Must be unique', type: 'string', maxLength: 255, example: 'Wireless Headphones Pro'),
                new OA\Property(property: 'description', type: 'string', maxLength: 5000, nullable: true),
                new OA\Property(property: 'category_id', type: 'integer', example: 1),
                new OA\Property(property: 'price', type: 'number', maximum: 99999999.99, minimum: 0, example: 249.99),
            ],
        )),
        tags: ['Products'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/ProductId')],
        responses: [
            new OA\Response(response: 200, description: 'Product updated', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Product updated successfully.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/Product'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/ProductNotFound', response: 404),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function update(): void {}

    #[OA\Delete(
        path: '/products/{productId}',
        operationId: 'productDestroy',
        summary: 'Delete a product with its images and ratings',
        security: [['sanctum' => []]],
        tags: ['Products'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/ProductId')],
        responses: [
            new OA\Response(response: 200, description: 'Product deleted', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'Product deleted successfully.')]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/ProductNotFound', response: 404),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function destroy(): void {}
}

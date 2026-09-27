<?php

namespace App\Swagger\Paths;

use OpenApi\Attributes as OA;

class ProductRatingApi
{
    #[OA\Get(
        path: '/products/{productId}/ratings',
        operationId: 'productRatingIndex',
        summary: 'List ratings of a product (newest first)',
        security: [['sanctum' => []]],
        tags: ['Product Ratings'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/ProductId'),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 10, maximum: 100, minimum: 1)),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Rating page', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Product ratings fetched successfully.'),
                    new OA\Property(property: 'data', properties: [
                        new OA\Property(property: 'ratings', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductRating')),
                        new OA\Property(property: 'pagination', ref: '#/components/schemas/PaginationMeta'),
                        new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
                    ], type: 'object'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/ProductNotFound', response: 404),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/products/{productId}/ratings',
        operationId: 'productRatingStore',
        description: 'A user may rate the same product several times; every call creates a new rating.',
        summary: 'Rate a product',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['rating'],
            properties: [
                new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1, example: 5),
                new OA\Property(property: 'comment', type: 'string', maxLength: 1000, example: 'Great sound quality.', nullable: true),
            ],
        )),
        tags: ['Product Ratings'],
        parameters: [new OA\Parameter(ref: '#/components/parameters/ProductId')],
        responses: [
            new OA\Response(response: 201, description: 'Rating created', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Product rated successfully.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/ProductRating'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/ProductNotFound', response: 404),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function store(): void {}

    #[OA\Put(
        path: '/products/{productId}/ratings/{ratingId}',
        operationId: 'productRatingUpdate',
        description: 'Only the user who wrote the rating can update it. Send only the fields to change.',
        summary: 'Update one of your ratings',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1, example: 4),
                new OA\Property(property: 'comment', type: 'string', maxLength: 1000, example: 'Still great after a month.', nullable: true),
            ],
        )),
        tags: ['Product Ratings'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/ProductId'),
            new OA\Parameter(name: 'ratingId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Rating updated', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Product rating updated successfully.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/ProductRating'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(response: 403, description: 'The rating belongs to another user', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/ErrorResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'You can only change your own ratings.')]),
            ])),
            new OA\Response(response: 404, description: 'The rating does not exist for this product', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/ErrorResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'Product rating not found.')]),
            ])),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function update(): void {}

    #[OA\Delete(
        path: '/products/{productId}/ratings/{ratingId}',
        operationId: 'productRatingDestroy',
        description: 'Only the user who wrote the rating can delete it.',
        summary: 'Delete one of your ratings',
        security: [['sanctum' => []]],
        tags: ['Product Ratings'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/ProductId'),
            new OA\Parameter(name: 'ratingId', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Rating deleted', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'Product rating deleted successfully.')]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(response: 403, description: 'The rating belongs to another user', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/ErrorResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'You can only change your own ratings.')]),
            ])),
            new OA\Response(response: 404, description: 'The rating does not exist for this product', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/ErrorResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'Product rating not found.')]),
            ])),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function destroy(): void {}
}

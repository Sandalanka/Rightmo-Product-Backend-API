<?php

namespace App\Swagger\Paths;

use OpenApi\Attributes as OA;

class CategoryApi
{
    #[OA\Get(
        path: '/categories',
        operationId: 'categoryIndex',
        summary: 'List all categories (sorted by name, cached)',
        security: [['sanctum' => []]],
        tags: ['Categories'],
        responses: [
            new OA\Response(response: 200, description: 'Category list', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Categories fetched successfully.'),
                    new OA\Property(property: 'data', properties: [
                        new OA\Property(property: 'categories', type: 'array', items: new OA\Items(ref: '#/components/schemas/Category')),
                    ], type: 'object'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function index(): void {}
}

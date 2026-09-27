<?php

namespace App\Swagger\Paths;

use OpenApi\Attributes as OA;

class AuthApi
{
    #[OA\Post(
        path: '/auth/register',
        operationId: 'authRegister',
        summary: 'Register a new user',
        security: [],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['name', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'John Doe'),
                new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'john@example.com'),
                new OA\Property(property: 'password', description: 'Min 8 chars with uppercase, lowercase, number and symbol (@$!%*#?&)', type: 'string', format: 'password', example: 'Secret@123'),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'Secret@123'),
            ],
        )),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 201, description: 'User registered', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'User registered successfully.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/AuthResult'),
                ]),
            ])),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function register(): void {}

    #[OA\Post(
        path: '/auth/login',
        operationId: 'authLogin',
        summary: 'Login and get an access token',
        security: [],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'test@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Password@123'),
            ],
        )),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logged in', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'User login successfully.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/AuthResult'),
                ]),
            ])),
            new OA\Response(response: 401, description: 'Wrong email or password', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/ErrorResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'Invalid email or password.')]),
            ])),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function login(): void {}

    #[OA\Post(
        path: '/auth/logout',
        operationId: 'authLogout',
        summary: 'Logout (revoke the current token)',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logged out', content: new OA\JsonContent(allOf: [
                new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
                new OA\Schema(properties: [new OA\Property(property: 'message', type: 'string', example: 'User logout successfully.')]),
            ])),
            new OA\Response(ref: '#/components/responses/Unauthenticated', response: 401),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function logout(): void {}
}

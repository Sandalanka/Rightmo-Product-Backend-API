<?php

namespace Tests\Feature\Auth;

use App\Constants\MessageConstant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UnauthenticatedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function protectedEndpoints(): array
    {
        return [
            'logout' => ['POST', '/api/v1/auth/logout'],
            'categories' => ['GET', '/api/v1/categories'],
            'products' => ['GET', '/api/v1/products'],
            'delete product' => ['DELETE', '/api/v1/products/1'],
            'product ratings' => ['GET', '/api/v1/products/1/ratings'],
        ];
    }

    #[DataProvider('protectedEndpoints')]
    public function test_request_without_accept_header_gets_json_401_not_redirect(string $method, string $uri): void
    {
        // No "Accept: application/json" header, like a browser or a bare Postman request
        $this->call($method, $uri)
            ->assertUnauthorized()
            ->assertExactJson([
                'status' => 'error',
                'message' => MessageConstant::UNAUTHENTICATED,
                'timestamp' => now()->toDateTimeString(),
            ]);
    }

    public function test_invalid_token_gets_json_401(): void
    {
        $this->withToken('1|not-a-real-token')
            ->getJson('/api/v1/products')
            ->assertUnauthorized()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', MessageConstant::UNAUTHENTICATED);
    }
}

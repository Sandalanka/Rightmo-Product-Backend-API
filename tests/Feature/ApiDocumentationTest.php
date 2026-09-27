<?php

namespace Tests\Feature;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use OpenApi\Generator;
use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_every_api_route_is_documented_and_every_documented_path_exists(): void
    {
        $spec = json_decode((new Generator)->generate([app_path('Swagger')])->toJson(), true);

        $documented = [];

        foreach ($spec['paths'] as $path => $operations) {
            foreach ($operations as $method => $operation) {
                if (! in_array($method, ['get', 'post', 'put', 'delete'], true)) {
                    continue;
                }

                // Multipart updates are documented as POST with `_method` (Laravel method spoofing)
                $spoofedMethod = data_get($operation, 'requestBody.content.multipart/form-data.schema.properties._method.default');

                $documented[] = strtoupper($spoofedMethod ?? $method).' '.$spec['servers'][0]['url'].$path;
            }
        }

        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RoutingRoute $route) => str_starts_with($route->uri(), 'api/v1/'))
            ->flatMap(fn (RoutingRoute $route) => collect($route->methods())
                ->reject(fn (string $method) => $method === 'HEAD')
                ->map(fn (string $method) => $method.' /'.$route->uri()))
            ->all();

        $this->assertEqualsCanonicalizing($routes, $documented);
    }

    public function test_documentation_page_is_available(): void
    {
        $this->get('/api/documentation')->assertOk();
    }
}

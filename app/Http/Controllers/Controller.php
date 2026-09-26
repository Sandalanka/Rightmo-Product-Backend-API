<?php

namespace App\Http\Controllers;

use App\Constants\MessageConstant;
use App\Constants\StatusCodeConstant;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

abstract class Controller
{
    /**
     * Summary: Return a success JSON response
     */
    protected function successResponse(array|Collection|Arrayable|JsonResource|null $data = null,
        ?string $message = null,
        int $statusCode = StatusCodeConstant::OK): JsonResponse
    {
        $response = [
            'status' => 'success',
            'timestamp' => now()->toDateTimeString(),
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode, [
            'Access-Control-Allow-Origin' => '*',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Summary: Return an error JSON response
     */
    protected function errorResponse(array|Collection|Arrayable $data = [],
        ?Exception $exception = null,
        string $message = MessageConstant::GENERAL_RESPONSE_ERROR_MESSAGE,
        int $statusCode = StatusCodeConstant::INTERNAL_SERVER_ERROR): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ];

        // Only expose the raw exception message while debugging
        if ($exception !== null && config('app.debug')) {
            $response['errors'] = $exception->getMessage();
        }

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode, [
            'Access-Control-Allow-Origin' => '*',
            'Content-Type' => 'application/json',
        ]);
    }
}

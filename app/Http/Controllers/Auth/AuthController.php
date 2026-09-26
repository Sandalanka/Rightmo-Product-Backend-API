<?php

namespace App\Http\Controllers\Auth;

use App\Classes\Common\ApiCatchErrors;
use App\Constants\MessageConstant;
use App\Constants\StatusCodeConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Services\Auth\AuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Summery: User register
     *
     * @throws Throwable
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $registerResult = $this->authService->register($request->validated());

            DB::commit();

            return $this->successResponse(
                data: new AuthResource($registerResult),
                message: MessageConstant::USER_REGISTERED,
                statusCode: StatusCodeConstant::CREATED
            );

        } catch (Exception $exception) {
            ApiCatchErrors::rollback($exception, 'An error occurred while register an user-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: User login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $loginResult = $this->authService->login($request->validated());

            if ($loginResult !== null) {

                return $this->successResponse(
                    data: new AuthResource($loginResult),
                    message: MessageConstant::USER_LOGIN
                );
            }

            return $this->errorResponse(
                message: MessageConstant::INVALID_CREDENTIALS,
                statusCode: StatusCodeConstant::UNAUTHORIZED
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception, 'An error occurred while login an user-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }

    /**
     * Summery: User logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return $this->successResponse(
                message: MessageConstant::USER_LOGOUT
            );

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception, 'An error occurred while logout an user-(controller): ');

            return $this->errorResponse(
                exception: $exception,
                message: MessageConstant::SOMETHING_WENT_WRONG
            );
        }
    }
}

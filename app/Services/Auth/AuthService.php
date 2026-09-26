<?php

namespace App\Services\Auth;

use App\Classes\Common\ApiCatchErrors;
use App\Contracts\Auth\AuthRepositoryInterface;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Summery: User register
     *
     * @param  array{name: string, email: string, password: string}  $registerData
     * @return array{user: User, token: string}
     *
     * @throws Exception
     */
    public function register(array $registerData): array
    {
        try {
            $user = $this->authRepository->register($registerData);

            $token = $this->authRepository->createToken($user);

            return [
                'user' => $user,
                'token' => $token,
            ];

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while register an user-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: User login
     *
     * @param  array{email: string, password: string}  $loginData
     * @return array{user: User, token: string}|null
     *
     * @throws Exception
     */
    public function login(array $loginData): ?array
    {
        try {
            $user = $this->authRepository->findUserByEmail($loginData['email']);

            if ($user && Hash::check($loginData['password'], $user->password)) {

                $this->authRepository->deleteAllTokens($user);

                $token = $this->authRepository->createToken($user);

                return [
                    'user' => $user,
                    'token' => $token,
                ];
            }

            return null;

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while login an user-(service): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: User logout
     *
     * @throws Exception
     */
    public function logout(User $user): void
    {
        try {
            $this->authRepository->logout($user);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while logout an user-(service): '
            );

            throw $exception;
        }
    }
}

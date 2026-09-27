<?php

namespace App\Repositories\Auth;

use App\Classes\Common\ApiCatchErrors;
use App\Contracts\Auth\AuthRepositoryInterface;
use App\Models\User;
use Exception;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * Summery: User register
     *
     * @param  array{name: string, email: string, password: string}  $registerData
     *
     * @throws Exception
     */
    public function register(array $registerData): User
    {
        try {
            return User::create([
                'name' => $registerData['name'],
                'email' => $registerData['email'],
                'password' => $registerData['password'],
            ]);

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while register an user-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Find user by email
     *
     * @throws Exception
     */
    public function findUserByEmail(string $email): ?User
    {
        try {
            return User::where('email', $email)->first();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while fetching an user by email-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Create a new sanctum personal access token for the user
     *
     * @throws Exception
     */
    public function createToken(User $user): string
    {
        try {
            return $user->createToken('Personal Access Token')->plainTextToken;

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while creating an user token-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: Delete all sanctum tokens of the user
     *
     * @throws Exception
     */
    public function deleteAllTokens(User $user): void
    {
        try {
            $user->tokens()->delete();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while deleting all user tokens-(repository): '
            );

            throw $exception;
        }
    }

    /**
     * Summery: User logout (delete current access token)
     *
     * @throws Exception
     */
    public function logout(User $user): void
    {
        try {
            $user->currentAccessToken()?->delete();

        } catch (Exception $exception) {
            ApiCatchErrors::throw($exception,
                'An error occurred while logout an user-(repository): '
            );

            throw $exception;
        }
    }
}

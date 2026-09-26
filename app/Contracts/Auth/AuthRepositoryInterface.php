<?php

namespace App\Contracts\Auth;

use App\Models\User;

interface AuthRepositoryInterface
{
    /**
     * Summery: User register
     *
     * @param  array{name: string, email: string, password: string}  $registerData
     */
    public function register(array $registerData): User;

    /**
     * Summery: Find user by email
     */
    public function findUserByEmail(string $email): ?User;

    /**
     * Summery: Create a new sanctum personal access token for the user
     */
    public function createToken(User $user): string;

    /**
     * Summery: Delete all sanctum tokens of the user
     */
    public function deleteAllTokens(User $user): void;

    /**
     * Summery: User logout (delete current access token)
     */
    public function logout(User $user): void;
}

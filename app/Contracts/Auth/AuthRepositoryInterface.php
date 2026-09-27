<?php

namespace App\Contracts\Auth;

use App\Models\User;

interface AuthRepositoryInterface
{
    
    public function register(array $registerData): User;

    public function findUserByEmail(string $email): ?User;

    public function createToken(User $user): string;

    public function deleteAllTokens(User $user): void;

    public function logout(User $user): void;
}

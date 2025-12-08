<?php
namespace App\Interfaces;

interface UserServiceInterface {
    public function register(array $data): array;
    public function login(string $email, string $password): array;
    public function updateProfile(int $userId, array $data): array;
    public function getUserById(int $userId): array;
    public function validateRegistrationData(array $data): array;
}
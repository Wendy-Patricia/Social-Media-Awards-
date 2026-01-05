<?php
namespace App\Interfaces;

interface CandidateServiceInterface {
    public function submitCandidature(array $data): array;
    public function validateCandidature(array $data): array;
    public function getUserCandidatures(int $userId): array;
    public function updateCandidatureStatus(int $candidatureId, string $status, int $reviewerId): array;
}
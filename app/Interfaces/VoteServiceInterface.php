<?php
namespace App\Interfaces;

interface VoteServiceInterface {
    public function vote(array $data): array;
    public function validateVote(array $data): array;
    public function getUserVotes(int $userId): array;
    public function getCategoryResults(int $categoryId): array;
}
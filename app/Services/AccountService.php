<?php

namespace App\Services;

use PDO;

class AccountService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllAccounts(): array
    {
        $sql = "SELECT * FROM compte ORDER BY date_creation DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getAccountById(int $id): ?array
    {
        $sql = "SELECT * FROM compte WHERE id_compte = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function updateAccountStatus(int $id, bool $isActive): bool
    {
        $sql = "UPDATE compte SET est_actif = :is_active WHERE id_compte = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':is_active' => $isActive ? 1 : 0,
            ':id' => $id
        ]);
    }

    public function deleteAccount(int $id): bool
    {
        $sql = "DELETE FROM compte WHERE id_compte = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
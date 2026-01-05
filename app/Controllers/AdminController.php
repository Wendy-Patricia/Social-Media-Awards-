<?php

namespace App\Controllers;

use App\Services\CategoryService;
use PDO;

class AdminController
{
    private CategoryService $categoryService;
    private PDO $pdo;

    public function __construct()
    {
        // Cria a conexão PDO diretamente aqui (sem classe Database)
        try {
            $this->pdo = new PDO(
                "mysql:host=localhost;dbname=social_media_awards;charset=utf8",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }

        $this->categoryService = new CategoryService($this->pdo);
    }

    public function getAllCategories(): array
    {
        return $this->categoryService->getAllCategories();
    }

    public function getCategoryById(int $id): ?array
    {
        return $this->categoryService->getCategoryById($id);
    }

    public function createCategory(array $data, ?array $imageFile = null): bool
    {
        return $this->categoryService->createCategory($data, $imageFile);
    }

    public function updateCategory(int $id, array $data, ?array $imageFile = null): bool
    {
        return $this->categoryService->updateCategory($id, $data, $imageFile);
    }

    public function deleteCategory(int $id): bool
    {
        return $this->categoryService->deleteCategory($id);
    }

    public function getEditionsList(): array
    {
        $sql = "SELECT id_edition, nom, annee FROM edition ORDER BY annee DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
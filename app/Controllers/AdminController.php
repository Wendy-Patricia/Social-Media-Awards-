<?php
// app/Controllers/AdminController.php

namespace App\Controllers;

use PDO;
use PDOException;

class AdminController
{
    private $categoryService;
    private $pdo;

    public function __construct()
    {
        // Incluir o autoloader
        require_once __DIR__ . '/../autoload.php';
        
        // Cria a conexão PDO
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

        // Agora podemos instanciar a classe
        $this->categoryService = new \App\Services\CategoryService($this->pdo);
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
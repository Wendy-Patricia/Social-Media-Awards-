<?php
// app/Controllers/AdminController.php

namespace App\Controllers;

use PDO;
use PDOException;
use App\Services\CategoryService;
use App\Services\EditionService;
use App\Services\CandidatureService;

class AdminController
{
    private CategoryService $categoryService;
    private EditionService $editionService;
    private CandidatureService $candidatureService;
    private $pdo;

    public function __construct()
    {

        require_once __DIR__ . '/../autoload.php';
        
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
        $this->categoryService = new CategoryService($this->pdo);
        $this->editionService = new EditionService($this->pdo);
        $this->candidatureService = new CandidatureService($this->pdo);
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

    public function getAllEditions(): array
    {
        return $this->editionService->getAllEditions();
    }

    public function getEditionById(int $id): ?array
    {
        return $this->editionService->getEditionById($id);
    }

    public function createEdition(array $data, ?array $imageFile = null): bool
    {
        return $this->editionService->createEdition($data, $imageFile);
    }

    public function updateEdition(int $id, array $data, ?array $imageFile = null): bool
    {
        return $this->editionService->updateEdition($id, $data, $imageFile);
    }

    public function deleteEdition(int $id): bool
    {
        return $this->editionService->deleteEdition($id);
    }

    public function getAllCandidatures(): array
    {
        return $this->candidatureService->getAllCandidatures();
    }

    public function getCandidatureById(int $id): ?array
    {
        return $this->candidatureService->getCandidatureById($id);
    }

    public function updateCandidatureStatus(int $id, string $statut): bool
    {
        return $this->candidatureService->updateStatus($id, $statut);
    }

    public function deleteCandidature(int $id): bool
    {
        return $this->candidatureService->deleteCandidature($id);
    }
    
}
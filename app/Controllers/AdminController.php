<?php

namespace App\Controllers;

use App\Services\CategoryService;

class AdminController
{
    private CategoryService $categoryService;

    public function __construct()
    {
        // Conexão direta à classe Database no namespace global
        $pdo = \Database::getConnection();
        $this->categoryService = new CategoryService($pdo);
    }

    // Método público para listar todas as categorias
    public function getAllCategories(): array
    {
        return $this->categoryService->getAllCategories();
    }

    // Método público para obter uma categoria por ID
    public function getCategoryById(int $id): ?array
    {
        return $this->categoryService->getCategoryById($id);
    }

    // Método público para criar categoria
    public function createCategory(array $data, ?array $imageFile = null): bool
    {
        return $this->categoryService->createCategory($data, $imageFile);
    }

    // Método público para atualizar categoria
    public function updateCategory(int $id, array $data, ?array $imageFile = null): bool
    {
        return $this->categoryService->updateCategory($id, $data, $imageFile);
    }

    // Método público para apagar categoria
    public function deleteCategory(int $id): bool
    {
        return $this->categoryService->deleteCategory($id);
    }

    // Método público para listar edições (para os selects)
    public function getEditionsList(): array
    {
        $pdo = \Database::getConnection();
        $sql = "SELECT id_edition, nom, annee FROM edition ORDER BY annee DESC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }
}
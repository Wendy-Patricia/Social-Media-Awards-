<?php

namespace App\Controllers;

use App\Services\CategoryService;
use App\Interfaces\AdminCategoryControllerInterface;
use App\Models\Categorie;

/**
 * Contrôleur responsable de la gestion administrative des catégories.
 * 
 * Ce contrôleur regroupe toutes les opérations CRUD liées aux catégories
 * du système des Social Media Awards, en déléguant la logique métier au service dédié.
 */
class AdminCategoryController implements AdminCategoryControllerInterface
{
    private CategoryService $categoryService;

    /**
     * Constructeur du contrôleur.
     *
     * @param CategoryService $categoryService Service gérant les opérations sur les catégories
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Récupère la liste complète de toutes les catégories.
     *
     * @return Categorie[] Tableau contenant toutes les catégories sous forme d'objets Categorie
     */
    public function getAllCategories(): array
    {
        return $this->categoryService->getAllCategories();
    }

    /**
     * Récupère une catégorie spécifique par son identifiant.
     *
     * @param int $id Identifiant unique de la catégorie
     * @return Categorie|null Objet Categorie ou null si non trouvée
     */
    public function getCategoryById(int $id): ?Categorie
    {
        return $this->categoryService->getCategoryById($id);
    }

    /**
     * Crée une nouvelle catégorie.
     *
     * @param array $data Données de la catégorie (nom, description, etc.)
     * @param array|null $imageFile Fichier image uploadé (facultatif)
     * @return bool True en cas de succès, false sinon
     */
    public function createCategory(array $data, ?array $imageFile = null): bool
    {
        return $this->categoryService->createCategory($data, $imageFile);
    }

    /**
     * Met à jour une catégorie existante.
     *
     * @param int $id Identifiant de la catégorie à modifier
     * @param array $data Nouvelles données de la catégorie
     * @param array|null $imageFile Nouvelle image (facultatif)
     * @return bool True en cas de succès, false sinon
     */
    public function updateCategory(int $id, array $data, ?array $imageFile = null): bool
    {
        return $this->categoryService->updateCategory($id, $data, $imageFile);
    }

    /**
     * Supprime une catégorie par son identifiant.
     *
     * @param int $id Identifiant de la catégorie à supprimer
     * @return bool True en cas de succès, false sinon
     */
    public function deleteCategory(int $id): bool
    {
        return $this->categoryService->deleteCategory($id);
    }
}
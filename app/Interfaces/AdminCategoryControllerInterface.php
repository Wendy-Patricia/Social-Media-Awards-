<?php
namespace App\Interfaces;

/**
 * Interface pour le contrôleur administratif des catégories.
 * 
 * Définit les méthodes obligatoires pour la gestion CRUD des catégories.
 */
interface AdminCategoryControllerInterface
{
    /**
     * Récupère la liste complète de toutes les catégories.
     *
     * @return array Tableau contenant toutes les catégories
     */
    public function getAllCategories(): array;

    /**
     * Récupère une catégorie spécifique par son identifiant.
     *
     * @param int $id Identifiant unique de la catégorie
     * @return array|null Données de la catégorie ou null si non trouvée
     */
    public function getCategoryById(int $id): ?array;

    /**
     * Crée une nouvelle catégorie.
     *
     * @param array $data Données de la catégorie (nom, description, etc.)
     * @param array|null $imageFile Fichier image uploadé (facultatif)
     * @return bool True en cas de succès, false sinon
     */
    public function createCategory(array $data, ?array $imageFile = null): bool;

    /**
     * Met à jour une catégorie existante.
     *
     * @param int $id Identifiant de la catégorie à modifier
     * @param array $data Nouvelles données de la catégorie
     * @param array|null $imageFile Nouvelle image (facultatif)
     * @return bool True en cas de succès, false sinon
     */
    public function updateCategory(int $id, array $data, ?array $imageFile = null): bool;

    /**
     * Supprime une catégorie par son identifiant.
     *
     * @param int $id Identifiant de la catégorie à supprimer
     * @return bool True en cas de succès, false sinon
     */
    public function deleteCategory(int $id): bool;
}
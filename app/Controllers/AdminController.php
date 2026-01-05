<?php

namespace App\Controllers;

use App\Services\CategoryService;
use PDO;

/**
 * Contrôleur principal pour le panneau d'administration.
 *
 * Gère actuellement toutes les actions liées aux catégories.
 * Peut être étendu pour d'autres entités administratives à l'avenir.
 *
 * @author  [Ton Nom]
 * @version 1.0
 */
class AdminController
{
    private CategoryService $categoryService;
    private PDO $pdo;

    /**
     * Constructeur avec injection de dépendance.
     *
     * @param PDO $pdo Instance de connexion à la base de données.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->categoryService = new CategoryService($pdo);
    }

    /**
     * Affiche la page principale de gestion des catégories (liste complète).
     *
     * Route suggérée : /admin/categories/manage-categories.php
     */
    public function manageCategories(): void
    {
        $categories = $this->categoryService->getAllCategories();
        $editions = $this->getEditionsList();

        // Passage des variables à la vue
        require_once __DIR__ . '/../../views/admin/categories/manage-categories.php';
    }

    /**
     * Affiche le formulaire de création d'une nouvelle catégorie.
     *
     * Route suggérée : /admin/categories/add-categorie.php
     */
    public function showCreateCategoryForm(): void
    {
        $editions = $this->getEditionsList();

        $error = $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        require_once __DIR__ . '/../../views/admin/categories/add-categorie.php';
    }

    /**
     * Traite la soumission du formulaire de création de catégorie.
     */
    public function createCategory(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Méthode de requête non autorisée.";
            header('Location: add-categorie.php');
            exit;
        }

        $data = [
            'nom'               => trim($_POST['nom'] ?? ''),
            'description'       => trim($_POST['description'] ?? ''),
            'plateforme_cible'  => $_POST['plateforme_cible'] ?? null,
            'date_debut_votes'  => $_POST['date_debut_votes'] ?: null,
            'date_fin_votes'    => $_POST['date_fin_votes'] ?: null,
            'id_edition'        => (int)($_POST['id_edition'] ?? 0),
            'limite_nomines'    => (int)($_POST['limite_nomines'] ?? 10),
        ];

        // Validation minimale
        if (empty($data['nom']) || $data['id_edition'] <= 0) {
            $_SESSION['error'] = "Le nom de la catégorie et l'édition sont obligatoires.";
            header('Location: add-categorie.php');
            exit;
        }

        $imageFile = $_FILES['image'] ?? null;

        $success = $this->categoryService->createCategory($data, $imageFile);

        if ($success) {
            $_SESSION['success'] = "Catégorie créée avec succès.";
            header('Location: manage-categories.php');
        } else {
            $_SESSION['error'] = "Erreur lors de la création de la catégorie.";
            header('Location: add-categorie.php');
        }
        exit;
    }

    /**
     * Affiche le formulaire d'édition d'une catégorie existante.
     *
     * @param int $id Identifiant de la catégorie à modifier.
     */
    public function showEditCategoryForm(int $id): void
    {
        $category = $this->categoryService->getCategoryById($id);

        if (!$category) {
            $_SESSION['error'] = "Catégorie introuvable.";
            header('Location: manage-categories.php');
            exit;
        }

        $editions = $this->getEditionsList();

        $error = $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);

        require_once __DIR__ . '/../../views/admin/categories/edit-categorie.php';
    }

    /**
     * Traite la mise à jour d'une catégorie.
     *
     * @param int $id Identifiant de la catégorie à mettre à jour.
     */
    public function updateCategory(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Méthode de requête non autorisée.";
            header("Location: edit-categorie.php?id=$id");
            exit;
        }

        $data = [
            'nom'               => trim($_POST['nom'] ?? ''),
            'description'       => trim($_POST['description'] ?? ''),
            'plateforme_cible'  => $_POST['plateforme_cible'] ?? null,
            'date_debut_votes'  => $_POST['date_debut_votes'] ?: null,
            'date_fin_votes'    => $_POST['date_fin_votes'] ?: null,
            'id_edition'        => (int)($_POST['id_edition'] ?? 0),
            'limite_nomines'    => (int)($_POST['limite_nomines'] ?? 10),
        ];

        if (empty($data['nom']) || $data['id_edition'] <= 0) {
            $_SESSION['error'] = "Le nom et l'édition sont obligatoires.";
            header("Location: edit-categorie.php?id=$id");
            exit;
        }

        $imageFile = $_FILES['image'] ?? null;

        $success = $this->categoryService->updateCategory($id, $data, $imageFile);

        if ($success) {
            $_SESSION['success'] = "Catégorie mise à jour avec succès.";
            header('Location: manage-categories.php');
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de la catégorie.";
            header("Location: edit-categorie.php?id=$id");
        }
        exit;
    }

    /**
     * Supprime une catégorie de manière définitive.
     *
     * @param int $id Identifiant de la catégorie à supprimer.
     */
    public function deleteCategory(int $id): void
    {
        $success = $this->categoryService->deleteCategory($id);

        if ($success) {
            $_SESSION['success'] = "Catégorie supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Impossible de supprimer la catégorie (contraintes d'intégrité ou erreur serveur).";
        }

        header('Location: manage-categories.php');
        exit;
    }

    /**
     * Récupère la liste de toutes les éditions pour les menus déroulants.
     *
     * Méthode temporaire – à déplacer dans un EditionService dédié plus tard.
     *
     * @return array Tableau associatif des éditions.
     */
    private function getEditionsList(): array
    {
        $sql = "SELECT id_edition, nom, annee FROM edition ORDER BY annee DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
<?php

namespace App\Services;

use App\Models\Category;
use PDO;
use PDOException;

/**
 * Service responsable de la gestion des catégories du système Social Media Awards.
 * 
 * Ce service centralise toutes les opérations CRUD sur la table `categorie` ainsi que
 * les traitements associés (upload d'images, comptage de candidatures et nominations).
 * 
 * @author  Wendy Patricia
 * @version 1.0
 */
class CategoryService
{
    /**
     * Instance PDO pour l'accès à la base de données.
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructeur du service.
     *
     * @param PDO $pdo Instance de connexion PDO injectée (Dependency Injection).
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère la liste complète des catégories avec leurs informations complémentaires.
     *
     * Retourne pour chaque catégorie :
     * - Le nom et l'année de l'édition associée
     * - Le nombre total de candidatures soumises
     * - Le nombre de nominations (finalistes approuvés)
     *
     * @return Category[] Tableau d'objets Category hydratés avec toutes les données.
     */
    public function getAllCategories(): array
    {
        $sql = "
            SELECT 
                c.*,
                e.nom AS edition_nom,
                e.annee AS edition_annee,
                COALESCE(cand.total_candidaturas, 0) AS nb_candidatures,
                COALESCE(nom.total_nominations, 0) AS nb_nominations
            FROM categorie c
            LEFT JOIN edition e ON c.id_edition = e.id_edition
            LEFT JOIN (
                SELECT id_categorie, COUNT(*) AS total_candidaturas
                FROM candidature
                GROUP BY id_categorie
            ) cand ON c.id_categorie = cand.id_categorie
            LEFT JOIN (
                SELECT id_categorie, COUNT(*) AS total_nominations
                FROM nomination
                GROUP BY id_categorie
            ) nom ON c.id_categorie = nom.id_categorie
            ORDER BY e.annee DESC, c.nom ASC
        ";

        $stmt = $this->pdo->query($sql);
        $categories = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = new Category($row);
        }

        return $categories;
    }

    /**
     * Récupère une catégorie spécifique par son identifiant.
     *
     * @param int $id Identifiant unique de la catégorie (id_categorie).
     * @return Category|null Objet Category si trouvé, null sinon.
     */
    public function getCategoryById(int $id): ?Category
    {
        $sql = "
            SELECT 
                c.*,
                e.nom AS edition_nom,
                e.annee AS edition_annee
            FROM categorie c
            LEFT JOIN edition e ON c.id_edition = e.id_edition
            WHERE c.id_categorie = :id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new Category($row) : null;
    }

    /**
     * Crée une nouvelle catégorie dans la base de données.
     *
     * Gère également l'upload de l'image associée si fournie.
     *
     * @param array      $data      Données du formulaire (nom, description, id_edition, etc.).
     * @param array|null $imageFile Tableau $_FILES['image'] ou null si aucune image.
     * @return bool                 true en cas de succès, false en cas d'échec.
     */
    public function createCategory(array $data, ?array $imageFile = null): bool
    {
        $imagePath = null;
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->uploadImage($imageFile);
            if (!$imagePath) {
                return false;
            }
        }

        $sql = "
            INSERT INTO categorie (
                nom, description, image, plateforme_cible, 
                date_debut_votes, date_fin_votes, id_edition, limite_nomines
            ) VALUES (
                :nom, :description, :image, :plateforme_cible,
                :date_debut_votes, :date_fin_votes, :id_edition, :limite_nomines
            )
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nom'              => $data['nom'],
                ':description'      => $data['description'] ?? null,
                ':image'            => $imagePath,
                ':plateforme_cible' => $data['plateforme_cible'] ?? null,
                ':date_debut_votes' => $data['date_debut_votes'] ?? null,
                ':date_fin_votes'   => $data['date_fin_votes'] ?? null,
                ':id_edition'       => $data['id_edition'],
                ':limite_nomines'   => $data['limite_nomines'] ?? 10
            ]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de la catégorie : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour les informations d'une catégorie existante.
     *
     * Gère la mise à jour ou le remplacement de l'image si une nouvelle est fournie.
     *
     * @param int        $id        Identifiant de la catégorie à modifier.
     * @param array      $data      Nouvelles données de la catégorie.
     * @param array|null $imageFile Nouvelle image (facultatif).
     * @return bool                 true en cas de succès, false sinon.
     */
    public function updateCategory(int $id, array $data, ?array $imageFile = null): bool
    {
        $category = $this->getCategoryById($id);
        if (!$category) {
            return false;
        }

        $imagePath = $category->getImage();

        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            // Suppression de l'ancienne image
            if ($imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = $this->uploadImage($imageFile);
            if (!$imagePath) {
                return false;
            }
        }

        $sql = "
            UPDATE categorie SET
                nom = :nom,
                description = :description,
                image = :image,
                plateforme_cible = :plateforme_cible,
                date_debut_votes = :date_debut_votes,
                date_fin_votes = :date_fin_votes,
                id_edition = :id_edition,
                limite_nomines = :limite_nomines
            WHERE id_categorie = :id
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nom'              => $data['nom'],
                ':description'      => $data['description'] ?? null,
                ':image'            => $imagePath,
                ':plateforme_cible' => $data['plateforme_cible'] ?? null,
                ':date_debut_votes' => $data['date_debut_votes'] ?? null,
                ':date_fin_votes'   => $data['date_fin_votes'] ?? null,
                ':id_edition'       => $data['id_edition'],
                ':limite_nomines'   => $data['limite_nomines'] ?? 10,
                ':id'               => $id
            ]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de la catégorie : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime définitivement une catégorie.
     *
     * Supprime également l'image associée du système de fichiers.
     * Attention : La suppression peut échouer si des contraintes d'intégrité référentielle
     * existent (candidatures, nominations, etc.).
     *
     * @param int $id Identifiant de la catégorie à supprimer.
     * @return bool   true si suppression réussie, false sinon.
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->getCategoryById($id);
        if (!$category) {
            return false;
        }

        // Suppression de l'image physique
        $imagePath = $category->getImage();
        if ($imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
            unlink(__DIR__ . '/../../public/' . $imagePath);
        }

        $sql = "DELETE FROM categorie WHERE id_categorie = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de la catégorie (probable contrainte FK) : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gère l'upload et le stockage sécurisé de l'image d'une catégorie.
     *
     * Vérifie le type MIME, la taille et renomme le fichier de manière unique.
     *
     * @param array $file Tableau $_FILES contenant l'image uploadée.
     * @return string|null Chemin relatif de l'image stockée ou null en cas d'échec.
     */
    private function uploadImage(array $file): ?string
    {
        $uploadDir = __DIR__ . '/../../public/uploads/categories/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2 Mo

        if ($file['size'] > $maxSize || !in_array($file['type'], $allowed)) {
            return null;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cat_') . '.' . strtolower($extension);
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return 'uploads/categories/' . $filename;
        }

        return null;
    }
}
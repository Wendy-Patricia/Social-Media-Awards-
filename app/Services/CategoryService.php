<?php

namespace App\Services;

use PDO;
use PDOException;
use App\Models\Categorie;

/**
 * Service de gestion des catégories.
 */
class CategoryService
{
    private $pdo;

    /**
     * Constructeur du service.
     *
     * @param PDO $pdo Connexion à la base de données.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les catégories d'une édition.
     *
     * @param int $editionId ID de l'édition.
     * @return Categorie[] Liste des catégories sous forme d'objets Categorie.
     */
    public function getAllCategoriesByEdition(int $editionId): array
    {
        try {
            $sql = "SELECT * FROM categorie 
                    WHERE id_edition = :edition_id 
                    ORDER BY nom ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':edition_id' => $editionId]);

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $categories = [];
            foreach ($data as $row) {
                $categories[] = new Categorie($row);
            }
            return $categories;
        } catch (PDOException $e) {
            error_log("Erreur récupération catégories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère toutes les catégories.
     *
     * @return Categorie[] Liste des catégories sous forme d'objets Categorie.
     */
    public function getAllCategories(): array
    {
        $sql = "
            SELECT 
                c.*,
                e.nom AS edition_nom,
                e.annee AS edition_annee,
                COALESCE(cand.nb_candidatures, 0) AS nb_candidatures,
                COALESCE(nom.nb_nominations, 0) AS nb_nominations
            FROM categorie c
            LEFT JOIN edition e ON c.id_edition = e.id_edition
            LEFT JOIN (SELECT id_categorie, COUNT(*) AS nb_candidatures FROM candidature GROUP BY id_categorie) cand 
                ON c.id_categorie = cand.id_categorie
            LEFT JOIN (SELECT id_categorie, COUNT(*) AS nb_nominations FROM nomination GROUP BY id_categorie) nom 
                ON c.id_categorie = nom.id_categorie
            ORDER BY c.nom ASC
        ";

        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();
        $categories = [];
        foreach ($data as $row) {
            $categories[] = new Categorie($row);
        }
        return $categories;
    }

    /**
     * Compte les nominations par catégorie.
     *
     * @param int $categoryId ID de la catégorie.
     * @return int Nombre de nominations.
     */
    public function countNominationsByCategory(int $categoryId): int
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM nomination 
                    WHERE id_categorie = :category_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':category_id' => $categoryId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Erreur comptage nominations: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère une catégorie par son ID.
     *
     * @param int $categoryId ID de la catégorie.
     * @return Categorie|null Objet Categorie ou null si non trouvée.
     */
    public function getCategoryById(int $categoryId): ?Categorie
    {
        try {
            $sql = "SELECT * FROM categorie WHERE id_categorie = :category_id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':category_id' => $categoryId]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? new Categorie($data) : null;
        } catch (PDOException $e) {
            error_log("Erreur récupération catégorie: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère toutes les plateformes uniques.
     *
     * @return array Liste des plateformes.
     */
    public function getAllPlatforms(): array
    {
        try {
            $sql = "SELECT DISTINCT plateforme_cible 
                    FROM categorie 
                    WHERE plateforme_cible IS NOT NULL";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Erreur récupération plateformes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crée une nouvelle catégorie.
     *
     * @param array $data Données de la catégorie.
     * @param array|null $imageFile Fichier image.
     * @return bool Succès de l'opération.
     */
    public function createCategory(array $data, ?array $imageFile = null): bool
    {
        try {
            $category = new Categorie($data);
            $imagePath = $this->uploadImage($imageFile);

            $sql = "INSERT INTO categorie (nom, description, image, plateforme_cible, date_debut_votes, date_fin_votes, id_edition, limite_nomines)
                    VALUES (:nom, :description, :image, :plateforme_cible, :date_debut_votes, :date_fin_votes, :id_edition, :limite_nomines)";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nom' => $category->getNom(),
                ':description' => $category->getDescription(),
                ':image' => $imagePath,
                ':plateforme_cible' => $category->getPlateformeCible(),
                ':date_debut_votes' => $category->getDateDebutVotes(),
                ':date_fin_votes' => $category->getDateFinVotes(),
                ':id_edition' => $category->getIdEdition(),
                ':limite_nomines' => $category->getLimiteNomines()
            ]);
        } catch (\InvalidArgumentException $e) {
            error_log("Validation échouée: " . $e->getMessage());
            return false;
        } catch (PDOException $e) {
            error_log("Erreur création catégorie: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour une catégorie.
     *
     * @param int $id ID de la catégorie.
     * @param array $data Nouvelles données.
     * @param array|null $imageFile Nouvelle image.
     * @return bool Succès de l'opération.
     */
    public function updateCategory(int $id, array $data, ?array $imageFile = null): bool
    {
        $category = $this->getCategoryById($id);
        if (!$category) return false;

        $imagePath = $category->getImage();

        if ($imageFile && !empty($imageFile['name'])) {
            $newPath = $this->uploadImage($imageFile);
            if ($newPath && $imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = $newPath;
        }

        try {
            $updatedCategory = new Categorie($data);
            $sql = "UPDATE categorie SET
                    nom = :nom,
                    description = :description,
                    image = :image,
                    plateforme_cible = :plateforme_cible,
                    date_debut_votes = :date_debut_votes,
                    date_fin_votes = :date_fin_votes,
                    id_edition = :id_edition,
                    limite_nomines = :limite_nomines
                    WHERE id_categorie = :id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nom' => $updatedCategory->getNom(),
                ':description' => $updatedCategory->getDescription(),
                ':image' => $imagePath,
                ':plateforme_cible' => $updatedCategory->getPlateformeCible(),
                ':date_debut_votes' => $updatedCategory->getDateDebutVotes(),
                ':date_fin_votes' => $updatedCategory->getDateFinVotes(),
                ':id_edition' => $updatedCategory->getIdEdition(),
                ':limite_nomines' => $updatedCategory->getLimiteNomines(),
                ':id' => $id
            ]);
        } catch (\InvalidArgumentException $e) {
            error_log("Validation échouée: " . $e->getMessage());
            return false;
        } catch (PDOException $e) {
            error_log("Erreur mise à jour catégorie: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une catégorie.
     *
     * @param int $id ID de la catégorie.
     * @return bool Succès de l'opération.
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->getCategoryById($id);
        if ($category && $category->getImage() && file_exists(__DIR__ . '/../../public/' . $category->getImage())) {
            unlink(__DIR__ . '/../../public/' . $category->getImage());
        }

        $sql = "DELETE FROM categorie WHERE id_categorie = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Télécharge une image.
     *
     * @param array|null $file Fichier image.
     * @return string|null Chemin de l'image ou null si échec.
     */
    private function uploadImage(?array $file): ?string
    {
        if (!$file || empty($file['name']) || $file['error'] !== 0) return null;

        $uploadDir = __DIR__ . '/../../public/uploads/categories/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024;

        if ($file['size'] > $maxSize || !in_array($file['type'], $allowed)) return null;

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cat_') . '.' . strtolower($ext);
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/categories/' . $filename;
        }
        return null;
    }
}
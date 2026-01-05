<?php
namespace App\Services;

use App\Models\Category;
use PDO;
use PDOException;

class CategoryService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

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
        $categories = [];
        while ($row = $stmt->fetch()) {
            $categories[] = $row; 
        }
        return $categories;
    }

    public function getCategoryById(int $id): ?array
    {
        $sql = "SELECT c.*, e.nom AS edition_nom FROM categorie c 
                LEFT JOIN edition e ON c.id_edition = e.id_edition 
                WHERE c.id_categorie = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function createCategory(array $data, ?array $imageFile = null): bool
    {
        $imagePath = $this->uploadImage($imageFile);

        $sql = "INSERT INTO categorie 
                (nom, description, image, plateforme_cible, date_debut_votes, date_fin_votes, id_edition, limite_nomines)
                VALUES (:nom, :description, :image, :plateforme_cible, :date_debut_votes, :date_fin_votes, :id_edition, :limite_nomines)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom' => $data['nom'],
            ':description' => $data['description'],
            ':image' => $imagePath,
            ':plateforme_cible' => $data['plateforme_cible'],
            ':date_debut_votes' => $data['date_debut_votes'] ?: null,
            ':date_fin_votes' => $data['date_fin_votes'] ?: null,
            ':id_edition' => $data['id_edition'],
            ':limite_nomines' => $data['limite_nomines']
        ]);
    }

    public function updateCategory(int $id, array $data, ?array $imageFile = null): bool
    {
        $category = $this->getCategoryById($id);
        $imagePath = $category['image'];

        if ($imageFile && !empty($imageFile['name'])) {
            $newPath = $this->uploadImage($imageFile);
            if ($newPath && $imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = $newPath;
        }

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
            ':nom' => $data['nom'],
            ':description' => $data['description'],
            ':image' => $imagePath,
            ':plateforme_cible' => $data['plateforme_cible'],
            ':date_debut_votes' => $data['date_debut_votes'] ?: null,
            ':date_fin_votes' => $data['date_fin_votes'] ?: null,
            ':id_edition' => $data['id_edition'],
            ':limite_nomines' => $data['limite_nomines'],
            ':id' => $id
        ]);
    }

    public function deleteCategory(int $id): bool
    {
        $category = $this->getCategoryById($id);
        if ($category && $category['image'] && file_exists(__DIR__ . '/../../public/' . $category['image'])) {
            unlink(__DIR__ . '/../../public/' . $category['image']);
        }

        $sql = "DELETE FROM categorie WHERE id_categorie = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

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
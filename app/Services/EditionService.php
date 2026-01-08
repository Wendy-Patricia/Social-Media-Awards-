<?php

namespace App\Services;

use PDO;

class EditionService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllEditions(): array
    {
        $sql = "
            SELECT 
                e.*,
                COALESCE(cat.nb_categories, 0) AS nb_categories,
                COALESCE(cand.nb_candidatures, 0) AS nb_candidatures,
                COALESCE(vot.nb_votants, 0) AS nb_votants
            FROM edition e
            LEFT JOIN (SELECT id_edition, COUNT(*) AS nb_categories FROM categorie GROUP BY id_edition) cat 
                ON e.id_edition = cat.id_edition
            LEFT JOIN (SELECT c.id_edition, COUNT(*) AS nb_candidatures 
                       FROM candidature cand JOIN categorie c ON cand.id_categorie = c.id_categorie 
                       GROUP BY c.id_edition) cand 
                ON e.id_edition = cand.id_edition
            LEFT JOIN (SELECT c.id_edition, COUNT(DISTINCT ta.id_compte) AS nb_votants 
                       FROM token_anonyme ta 
                       JOIN categorie c ON ta.id_categorie = c.id_categorie
                       WHERE ta.est_utilise = 1
                       GROUP BY c.id_edition) vot 
                ON e.id_edition = vot.id_edition
            ORDER BY e.annee DESC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getEditionById(int $id): ?array
    {
        $sql = "SELECT * FROM edition WHERE id_edition = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function createEdition(array $data, ?array $imageFile = null): bool
    {
        $checkSql = "SELECT id_edition FROM edition WHERE annee = :annee";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([':annee' => $data['annee']]);

        if ($checkStmt->fetch()) {
            throw new \Exception("Une édition avec l'année {$data['annee']} existe déjà.");
        }
        $imagePath = $this->uploadImage($imageFile);

        $sql = "INSERT INTO edition 
                (annee, nom, description, image, date_debut_candidatures, date_fin_candidatures, 
                 date_debut, date_fin, est_active, theme)
                VALUES (:annee, :nom, :description, :image, :date_debut_candidatures, 
                        :date_fin_candidatures, :date_debut, :date_fin, :est_active, :theme)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':annee' => $data['annee'],
            ':nom' => $data['nom'],
            ':description' => $data['description'] ?: null,
            ':image' => $imagePath,
            ':date_debut_candidatures' => $data['date_debut_candidatures'],
            ':date_fin_candidatures' => $data['date_fin_candidatures'],
            ':date_debut' => $data['date_debut'],
            ':date_fin' => $data['date_fin'],
            ':est_active' => $data['est_active'] ?? 0,
            ':theme' => $data['theme'] ?: null
        ]);
    }

    public function updateEdition(int $id, array $data, ?array $imageFile = null): bool
    {
        $edition = $this->getEditionById($id);
        $imagePath = $edition['image'] ?? null;

        if ($imageFile && !empty($imageFile['name'])) {
            $newPath = $this->uploadImage($imageFile);
            if ($newPath && $imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = $newPath;
        }

        if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            if ($imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = null;
        }

        $sql = "UPDATE edition SET
                annee = :annee,
                nom = :nom,
                description = :description,
                image = :image,
                date_debut_candidatures = :date_debut_candidatures,
                date_fin_candidatures = :date_fin_candidatures,
                date_debut = :date_debut,
                date_fin = :date_fin,
                est_active = :est_active,
                theme = :theme
                WHERE id_edition = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':annee' => $data['annee'],
            ':nom' => $data['nom'],
            ':description' => $data['description'] ?: null,
            ':image' => $imagePath,
            ':date_debut_candidatures' => $data['date_debut_candidatures'],
            ':date_fin_candidatures' => $data['date_fin_candidatures'],
            ':date_debut' => $data['date_debut'],
            ':date_fin' => $data['date_fin'],
            ':est_active' => $data['est_active'] ?? 0,
            ':theme' => $data['theme'] ?: null,
            ':id' => $id
        ]);
    }

    public function deleteEdition(int $id): bool
    {
        $edition = $this->getEditionById($id);
        if ($edition && $edition['image'] && file_exists(__DIR__ . '/../../public/' . $edition['image'])) {
            unlink(__DIR__ . '/../../public/' . $edition['image']);
        }

        $sql = "DELETE FROM edition WHERE id_edition = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    private function uploadImage(?array $file): ?string
    {
        if (!$file || empty($file['name']) || $file['error'] !== 0) return null;

        $uploadDir = __DIR__ . '/../../public/uploads/editions/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;

        if ($file['size'] > $maxSize || !in_array($file['type'], $allowed)) return null;

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('edi_') . '.' . strtolower($ext);
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/editions/' . $filename;
        }
        return null;
    }
}

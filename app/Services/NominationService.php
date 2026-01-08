<?php

namespace App\Services;

use PDO;
use App\Models\Nomination;

class NominationService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllNominations(): array
    {
        $sql = "
            SELECT n.*, 
                   cand.libelle AS candidature_libelle,
                   cat.nom AS categorie_nom,
                   edi.nom AS edition_nom,
                   comp.pseudonyme AS candidat_nom,
                   comp.photo_profil AS candidat_photo
            FROM nomination n
            JOIN candidature cand ON n.id_candidature = cand.id_candidature
            JOIN categorie cat ON n.id_categorie = cat.id_categorie
            JOIN edition edi ON cat.id_edition = edi.id_edition
            LEFT JOIN compte comp ON cand.id_compte = comp.id_compte
            ORDER BY n.date_approbation DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNominationById(int $id): ?array
    {
        $sql = "
            SELECT n.*, 
                   cand.*, 
                   cat.nom AS categorie_nom,
                   edi.nom AS edition_nom,
                   comp.pseudonyme, comp.email AS candidat_email
            FROM nomination n
            JOIN candidature cand ON n.id_candidature = cand.id_candidature
            JOIN categorie cat ON n.id_categorie = cat.id_categorie
            JOIN edition edi ON cat.id_edition = edi.id_edition
            LEFT JOIN compte comp ON cand.id_compte = comp.id_compte
            WHERE n.id_nomination = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createFromCandidature(int $id_candidature, int $id_admin): bool
    {
        $candidature = $this->getCandidatureById($id_candidature);
        if (!$candidature || $candidature['statut'] !== 'Approuvée') {
            return false;
        }

        $imagePath = $this->uploadImage($_FILES['image_file'] ?? null);

        $this->pdo->beginTransaction();
        try {
            $sql = "INSERT INTO nomination 
                    (libelle, plateforme, url_contenu, url_image, argumentaire, 
                     date_approbation, id_candidature, id_categorie, id_compte, id_admin)
                    VALUES (:libelle, :plateforme, :url_contenu, :url_image, :argumentaire,
                            NOW(), :id_candidature, :id_categorie, :id_compte, :id_admin)";

            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                ':libelle' => $candidature['libelle'],
                ':plateforme' => $candidature['plateforme'],
                ':url_contenu' => $candidature['url_contenu'],
                ':url_image' => $imagePath ?: $candidature['image'],
                ':argumentaire' => $candidature['argumentaire'],
                ':id_candidature' => $id_candidature,
                ':id_categorie' => $candidature['id_categorie'],
                ':id_compte' => $candidature['id_compte'],
                ':id_admin' => $id_admin
            ]);

            $this->pdo->commit();
            return $success;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function updateNomination(int $id, array $data, ?array $imageFile = null, int $id_admin): bool
    {
        $nomination = $this->getNominationById($id);
        if (!$nomination) return false;

        $imagePath = $nomination['url_image'];
        if ($imageFile && !empty($imageFile['name'])) {
            $newPath = $this->uploadImage($imageFile);
            if ($newPath && $imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = $newPath;
        }

        if (isset($data['remove_image']) && $data['remove_image'] == '1') {
            if ($imagePath && file_exists(__DIR__ . '/../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../public/' . $imagePath);
            }
            $imagePath = null;
        }

        $sql = "UPDATE nomination SET
                libelle = :libelle,
                plateforme = :plateforme,
                url_contenu = :url_contenu,
                url_image = :url_image,
                argumentaire = :argumentaire,
                id_admin = :id_admin
                WHERE id_nomination = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':libelle' => $data['libelle'],
            ':plateforme' => $data['plateforme'],
            ':url_contenu' => $data['url_contenu'],
            ':url_image' => $imagePath,
            ':argumentaire' => $data['argumentaire'],
            ':id_admin' => $id_admin,
            ':id' => $id
        ]);
    }

    public function deleteNomination(int $id): bool
    {
        $nomination = $this->getNominationById($id);
        if ($nomination && $nomination['url_image'] && file_exists(__DIR__ . '/../../public/' . $nomination['url_image'])) {
            unlink(__DIR__ . '/../../public/' . $nomination['url_image']);
        }

        $sql = "DELETE FROM nomination WHERE id_nomination = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    private function getCandidatureById(int $id): ?array
    {
        $sql = "SELECT * FROM candidature WHERE id_candidature = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function uploadImage(?array $file): ?string
    {
        if (!$file || empty($file['name']) || $file['error'] !== 0) return null;

        $uploadDir = __DIR__ . '/../../public/uploads/nominations/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 3 * 1024 * 1024;

        if ($file['size'] > $maxSize || !in_array($file['type'], $allowed)) return null;

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('nom_') . '.' . strtolower($ext);
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/nominations/' . $filename;
        }
        return null;
    }
}
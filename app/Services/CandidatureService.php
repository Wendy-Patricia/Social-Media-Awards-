<?php
namespace App\Services;

use PDO;

class CandidatureService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllCandidatures(): array
    {
        $sql = "
            SELECT 
                ca.*,
                co.pseudonyme AS candidat_pseudonyme,
                co.email AS candidat_email,
                cat.nom AS categorie_nom,
                e.nom AS edition_nom
            FROM candidature ca
            JOIN compte co ON ca.id_compte = co.id_compte
            JOIN categorie cat ON ca.id_categorie = cat.id_categorie
            JOIN edition e ON cat.id_edition = e.id_edition
            ORDER BY ca.date_soumission DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getCandidatureById(int $id): ?array
    {
        $sql = "
            SELECT 
                ca.*,
                co.pseudonyme AS candidat_pseudonyme,
                co.email AS candidat_email,
                cat.nom AS categorie_nom,
                e.nom AS edition_nom
            FROM candidature ca
            JOIN compte co ON ca.id_compte = co.id_compte
            JOIN categorie cat ON ca.id_categorie = cat.id_categorie
            JOIN edition e ON cat.id_edition = e.id_edition
            WHERE ca.id_candidature = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function updateStatus(int $id, string $statut): bool
    {
        $sql = "UPDATE candidature SET statut = :statut WHERE id_candidature = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['statut' => $statut, 'id' => $id]);
    }

    public function deleteCandidature(int $id): bool
    {
        $candidature = $this->getCandidatureById($id);
        if ($candidature && $candidature['image'] && file_exists(__DIR__ . '/../../public/' . $candidature['image'])) {
            unlink(__DIR__ . '/../../public/' . $candidature['image']);
        }

        $sql = "DELETE FROM candidature WHERE id_candidature = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
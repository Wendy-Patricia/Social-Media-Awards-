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

    public function getCandidatureStats(int $userId): array
    {
        $sql = "SELECT 
            COUNT(CASE WHEN statut = 'En attente' THEN 1 END) as pending,
            COUNT(CASE WHEN statut = 'Approuvée' THEN 1 END) as approved,
            COUNT(CASE WHEN statut = 'Rejetée' THEN 1 END) as rejected,
            COUNT(*) as total
            FROM candidature 
            WHERE id_compte = :userId";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $result = $stmt->fetch();
        
        return $result ?: [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'total' => 0
        ];
    }

    public function getCandidaturesByCategory(int $categoryId): array
    {
        $sql = "SELECT c.*, co.pseudonyme
                FROM candidature c
                JOIN compte co ON c.id_compte = co.id_compte
                WHERE c.id_categorie = :categoryId
                ORDER BY c.date_soumission DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':categoryId' => $categoryId]);
        return $stmt->fetchAll();
    }

    public function getCandidaturesByStatus(string $status): array
    {
        $sql = "SELECT c.*, co.pseudonyme, cat.nom as categorie_nom
                FROM candidature c
                JOIN compte co ON c.id_compte = co.id_compte
                JOIN categorie cat ON c.id_categorie = cat.id_categorie
                WHERE c.statut = :status
                ORDER BY c.date_soumission DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll();
    }

    public function approveCandidature(int $candidatureId, int $adminId): bool
    {
        $sql = "UPDATE candidature 
                SET statut = 'Approuvée' 
                WHERE id_candidature = :id
                AND statut = 'En attente'";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $candidatureId]);
    }

    public function rejectCandidature(int $candidatureId, int $adminId): bool
    {
        $sql = "UPDATE candidature 
                SET statut = 'Rejetée' 
                WHERE id_candidature = :id
                AND statut = 'En attente'";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $candidatureId]);
    }

    /**
     * Verifica se candidato já tem candidatura na categoria
     */
    public function hasCandidatureInCategory(int $userId, int $categoryId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM candidature 
                WHERE id_compte = :userId 
                AND id_categorie = :categoryId
                AND statut != 'Rejetée'";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':userId' => $userId,
            ':categoryId' => $categoryId
        ]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }

    /**
     * Obtém total de candidaturas por edição
     */
    public function getCandidaturesByEdition(int $editionId): array
    {
        $sql = "SELECT c.*, co.pseudonyme, cat.nom as categorie_nom
                FROM candidature c
                JOIN compte co ON c.id_compte = co.id_compte
                JOIN categorie cat ON c.id_categorie = cat.id_categorie
                WHERE cat.id_edition = :editionId
                ORDER BY c.date_soumission DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':editionId' => $editionId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtém estatísticas de candidaturas por edição
     */
    public function getEditionStats(int $editionId): array
    {
        $sql = "SELECT 
                COUNT(CASE WHEN c.statut = 'En attente' THEN 1 END) as pending,
                COUNT(CASE WHEN c.statut = 'Approuvée' THEN 1 END) as approved,
                COUNT(CASE WHEN c.statut = 'Rejetée' THEN 1 END) as rejected,
                COUNT(DISTINCT c.id_compte) as unique_candidates,
                COUNT(*) as total
                FROM candidature c
                JOIN categorie cat ON c.id_categorie = cat.id_categorie
                WHERE cat.id_edition = :editionId";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':editionId' => $editionId]);
        return $stmt->fetch();
    }

}
<?php
namespace App\Services;

use PDO;
use App\Models\Candidature;

/**
 * Service de gestion des candidatures.
 */
class CandidatureService
{
    private PDO $pdo;

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
     * Récupère toutes les candidatures.
     *
     * @return Candidature[] Liste des candidatures sous forme d'objets Candidature.
     */
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
        $data = $stmt->fetchAll();
        $candidatures = [];
        foreach ($data as $row) {
            $candidatures[] = new Candidature($row);
        }
        return $candidatures;
    }

    /**
     * Récupère une candidature par ID.
     *
     * @param int $id ID de la candidature.
     * @return Candidature|null Objet Candidature ou null si non trouvée.
     */
    public function getCandidatureById(int $id): ?Candidature
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
        $data = $stmt->fetch();
        return $data ? new Candidature($data) : null;
    }

    /**
     * Met à jour le statut d'une candidature.
     *
     * @param int $id ID de la candidature.
     * @param string $statut Nouveau statut.
     * @return bool Succès de l'opération.
     */
    public function updateStatus(int $id, string $statut): bool
    {
        $sql = "UPDATE candidature SET statut = :statut WHERE id_candidature = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['statut' => $statut, 'id' => $id]);
    }

    /**
     * Supprime une candidature.
     *
     * @param int $id ID de la candidature.
     * @return bool Succès de l'opération.
     */
    public function deleteCandidature(int $id): bool
    {
        $candidature = $this->getCandidatureById($id);
        if ($candidature && $candidature->getImage() && file_exists(__DIR__ . '/../../public/' . $candidature->getImage())) {
            unlink(__DIR__ . '/../../public/' . $candidature->getImage());
        }

        $sql = "DELETE FROM candidature WHERE id_candidature = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Obtient les statistiques des candidatures pour un utilisateur.
     *
     * @param int $userId ID de l'utilisateur.
     * @return array Statistiques.
     */
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

    /**
     * Récupère les candidatures par catégorie.
     *
     * @param int $categoryId ID de la catégorie.
     * @return Candidature[] Liste des candidatures.
     */
    public function getCandidaturesByCategory(int $categoryId): array
    {
        $sql = "SELECT c.*, co.pseudonyme
                FROM candidature c
                JOIN compte co ON c.id_compte = co.id_compte
                WHERE c.id_categorie = :categoryId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':categoryId' => $categoryId]);
        $data = $stmt->fetchAll();
        $candidatures = [];
        foreach ($data as $row) {
            $candidatures[] = new Candidature($row);
        }
        return $candidatures;
    }

    /**
     * Récupère les candidatures par statut.
     *
     * @param string $status Statut.
     * @return Candidature[] Liste des candidatures.
     */
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
        $data = $stmt->fetchAll();
        $candidatures = [];
        foreach ($data as $row) {
            $candidatures[] = new Candidature($row);
        }
        return $candidatures;
    }

    /**
     * Approuve une candidature.
     *
     * @param int $candidatureId ID de la candidature.
     * @param int $adminId ID de l'admin.
     * @return bool Succès de l'opération.
     */
    public function approveCandidature(int $candidatureId, int $adminId): bool
    {
        $sql = "UPDATE candidature 
                SET statut = 'Approuvée' 
                WHERE id_candidature = :id
                AND statut = 'En attente'";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $candidatureId]);
    }

    /**
     * Rejette une candidature.
     *
     * @param int $candidatureId ID de la candidature.
     * @param int $adminId ID de l'admin.
     * @return bool Succès de l'opération.
     */
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
     * Vérifie si le candidat a déjà une candidature dans la catégorie.
     *
     * @param int $userId ID du candidat.
     * @param int $categoryId ID de la catégorie.
     * @return bool
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
     * Obtient les candidatures par édition.
     *
     * @param int $editionId ID de l'édition.
     * @return Candidature[] Liste des candidatures.
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
        $data = $stmt->fetchAll();
        $candidatures = [];
        foreach ($data as $row) {
            $candidatures[] = new Candidature($row);
        }
        return $candidatures;
    }

    /**
     * Obtient les statistiques par édition.
     *
     * @param int $editionId ID de l'édition.
     * @return array Statistiques.
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


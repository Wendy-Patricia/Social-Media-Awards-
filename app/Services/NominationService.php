<?php
namespace App\Services;

use PDO;
use PDOException;

/**
 * Service de gestion des nominations
 */
class NominationService
{
    private $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Récupère toutes les nominations
     */
    public function getAllNominations(): array
    {
        try {
            $sql = "SELECT n.*, c.nom as categorie_nom 
                    FROM nomination n
                    JOIN categorie c ON n.id_categorie = c.id_categorie
                    ORDER BY n.libelle ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération nominations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère les nominations par catégorie
     */
    public function getNominationsByCategory(int $categoryId): array
    {
        try {
            $sql = "SELECT n.*, c.nom as categorie_nom 
                    FROM nomination n
                    JOIN categorie c ON n.id_categorie = c.id_categorie
                    WHERE n.id_categorie = :category_id
                    ORDER BY n.libelle ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':category_id' => $categoryId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération nominations par catégorie: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Compte les votes pour une nomination
     */
    public function countVotesForNomination(int $nominationId): int
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM vote 
                    WHERE id_nomination = :nomination_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':nomination_id' => $nominationId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("Erreur comptage votes: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Récupère toutes les catégories
     */
    public function getAllCategories(): array
    {
        try {
            $sql = "SELECT id_categorie, nom 
                    FROM categorie 
                    ORDER BY nom ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération catégories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère une catégorie par ID
     */
    public function getCategoryById(int $categoryId): ?array
    {
        try {
            $sql = "SELECT * FROM categorie WHERE id_categorie = :category_id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':category_id' => $categoryId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            
        } catch (PDOException $e) {
            error_log("Erreur récupération catégorie: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Récupère toutes les plateformes uniques
     */
    public function getAllPlatforms(): array
    {
        try {
            $sql = "SELECT DISTINCT plateforme 
                    FROM nomination 
                    WHERE plateforme IS NOT NULL 
                    AND plateforme != ''
                    UNION
                    SELECT DISTINCT plateforme_cible 
                    FROM categorie 
                    WHERE plateforme_cible IS NOT NULL 
                    AND plateforme_cible != ''
                    ORDER BY 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_filter($results);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération plateformes: " . $e->getMessage());
            return [];
        }
    }
}
<?php

namespace App\Services;

use PDO;
use PDOException;

/**
 * Service de gestion des catégories
 */
class CategoryService
{
    private $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Récupère toutes les catégories d'une édition
     */
    public function getAllCategoriesByEdition(int $editionId): array
    {
        try {
            $sql = "SELECT * FROM categorie 
                    WHERE id_edition = :edition_id 
                    ORDER BY nom ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':edition_id' => $editionId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération catégories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Compte les nominations par catégorie
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
     * Récupère une catégorie par son ID
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
            $sql = "SELECT DISTINCT plateforme_cible 
                    FROM categorie 
                    WHERE plateforme_cible IS NOT NULL 
                    AND plateforme_cible != ''
                    ORDER BY plateforme_cible";
            
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

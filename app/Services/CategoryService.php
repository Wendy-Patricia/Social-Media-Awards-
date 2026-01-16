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

<?php
namespace App\Services;

use PDO;
use PDOException;

/**
 * Service de gestion des résultats des Social Media Awards
 * @description Fournit les méthodes pour récupérer les résultats, statistiques et gagnants
 */
class ResultsService
{
    private $pdo;
    
    /**
     * Constructeur avec injection de dépendance PDO
     * @param PDO $pdo Instance de connexion à la base de données
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Récupère l'édition la plus récente active
     * @return array|null Données de l'édition ou null si aucune
     */
    public function getLatestEdition(): ?array
    {
        try {
            $sql = "SELECT * FROM edition 
                    WHERE est_active = 1 
                    ORDER BY annee DESC 
                    LIMIT 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $edition = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $edition ?: $this->getDefaultEdition();
            
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'édition: " . $e->getMessage());
            return $this->getDefaultEdition();
        }
    }
    
    /**
     * Données par défaut si aucune édition n'est trouvée
     * @return array Édition par défaut
     */
    private function getDefaultEdition(): array
    {
        return [
            'id_edition' => 1,
            'annee' => date('Y'),
            'nom' => 'Social Media Awards ' . date('Y'),
            'est_active' => 1
        ];
    }
    
    /**
     * Vérifie si les résultats sont disponibles pour une édition
     * @param int $editionId ID de l'édition
     * @return bool True si des résultats existent
     */
    private function areResultsAvailable(int $editionId): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM resultat WHERE id_edition = :edition_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':edition_id' => $editionId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($result['count'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Erreur vérification résultats: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les grands gagnants (top 3) pour une édition
     * @param int $editionId ID de l'édition
     * @return array Tableau des gagnants avec leurs informations
     */
    public function getGrandWinners(int $editionId): array
    {
        // D'abord essayer depuis la table resultat
        if ($this->areResultsAvailable($editionId)) {
            $winners = $this->getWinnersFromResultTable($editionId);
            if (!empty($winners)) {
                return $winners;
            }
        }
        
        // Sinon, calculer depuis les votes
        return $this->calculateWinnersFromVotes($editionId);
    }
    
    /**
     * Récupère les gagnants depuis la table resultat
     * @param int $editionId ID de l'édition
     * @return array Tableau des gagnants
     */
    private function getWinnersFromResultTable(int $editionId): array
    {
        try {
            // Récupérer les 3 premières nominations avec le meilleur rang
            $sql = "SELECT 
                        r.nombre_votes,
                        r.rang,
                        n.id_nomination,
                        n.libelle AS nom_nomination,
                        n.url_image AS image,
                        c.nom AS categorie,
                        c.plateforme_cible AS plateforme
                    FROM resultat r
                    JOIN nomination n ON r.id_nomination = n.id_nomination
                    JOIN categorie c ON n.id_categorie = c.id_categorie
                    WHERE r.id_edition = :edition_id 
                      AND r.rang <= 3
                    ORDER BY r.rang ASC
                    LIMIT 3";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':edition_id' => $editionId]);
            
            $winners = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Transformer les données pour le format attendu
            foreach ($winners as &$winner) {
                $winner['total_votes'] = $winner['nombre_votes'];
                unset($winner['nombre_votes']);
            }
            
            return $winners;
            
        } catch (PDOException $e) {
            error_log("Erreur récupération depuis resultat: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcule les gagnants depuis les votes (méthode de secours)
     * @param int $editionId ID de l'édition
     * @return array Tableau des gagnants calculés
     */
    private function calculateWinnersFromVotes(int $editionId): array
    {
        try {
            // Version compatible MySQL sans ROW_NUMBER()
            $sql = "SELECT 
                        n.id_nomination,
                        n.libelle AS nom_nomination,
                        n.url_image AS image,
                        c.nom AS categorie,
                        COUNT(v.id_vote) AS total_votes,
                        c.plateforme_cible AS plateforme
                    FROM nomination n
                    JOIN categorie c ON n.id_categorie = c.id_categorie
                    LEFT JOIN vote v ON n.id_nomination = v.id_nomination
                    WHERE c.id_edition = :edition_id
                    GROUP BY n.id_nomination, n.libelle, n.url_image, c.nom, c.plateforme_cible
                    ORDER BY total_votes DESC
                    LIMIT 3";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':edition_id' => $editionId]);
            
            $winners = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ajouter le rang manuellement
            $rank = 1;
            foreach ($winners as &$winner) {
                $winner['rang'] = $rank;
                $rank++;
            }
            
            return $winners;
            
        } catch (PDOException $e) {
            error_log("Erreur calcul gagnants depuis votes: " . $e->getMessage());
            return $this->getDefaultWinners();
        }
    }
    
    /**
     * Gagnants par défaut (pour démonstration)
     * @return array Gagnants par défaut
     */
    private function getDefaultWinners(): array
    {
        return [
            [
                'id_nomination' => 0,
                'nom_nomination' => 'En attente des premiers votes',
                'image' => 'assets/images/default-winner.jpg',
                'categorie' => 'Résultats en préparation',
                'total_votes' => 0,
                'plateforme' => 'all',
                'rang' => 1
            ]
        ];
    }
    
    /**
     * Récupère les résultats par catégorie pour une édition
     * @param int $editionId ID de l'édition
     * @return array Tableau des résultats par catégorie
     */
    public function getResultsByCategory(int $editionId): array
    {
        try {
            // D'abord essayer d'utiliser la vue
            $sql = "SELECT 
                        vrc.categorie AS categorie_nom,
                        vrc.nomination AS nom_nomination,
                        vrc.total_votes AS vote_count,
                        vrc.rang,
                        vrc.edition,
                        c.plateforme_cible AS plateforme,
                        c.id_categorie
                    FROM vue_resultats_categorie vrc
                    JOIN categorie c ON vrc.categorie = c.nom AND c.id_edition = :edition_id
                    WHERE vrc.edition = :edition_id
                    ORDER BY vrc.categorie, vrc.rang";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':edition_id' => $editionId]);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si la vue retourne des résultats, les organiser
            if (!empty($results)) {
                return $this->organizeResultsByCategory($results);
            }
            
            // Sinon, calculer depuis les votes
            return $this->calculateCategoryResultsFromVotes($editionId);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération résultats catégorie: " . $e->getMessage());
            return $this->calculateCategoryResultsFromVotes($editionId);
        }
    }
    
    /**
     * Organise les résultats par catégorie
     * @param array $results Résultats bruts de la vue
     * @return array Résultats organisés par catégorie
     */
    private function organizeResultsByCategory(array $results): array
    {
        $groupedResults = [];
        
        foreach ($results as $result) {
            $categoryName = $result['categorie_nom'];
            
            if (!isset($groupedResults[$categoryName])) {
                $groupedResults[$categoryName] = [
                    'categorie_nom' => $categoryName,
                    'plateforme' => $result['plateforme'],
                    'id_categorie' => $result['id_categorie'],
                    'winners' => []
                ];
            }
            
            // Ajouter le gagnant à la catégorie
            $position = $this->getPositionFromRank($result['rang']);
            $groupedResults[$categoryName]['winners'][] = [
                'nom_nomination' => $result['nom_nomination'],
                'vote_count' => $result['vote_count'],
                'rang' => $result['rang'],
                'position' => $position,
                'medal' => $this->getMedalEmoji($position),
                'vote_percentage' => 0 // Sera calculé plus tard
            ];
        }
        
        // Calculer les totaux et pourcentages
        foreach ($groupedResults as &$category) {
            $category['total_votes_categorie'] = array_sum(array_column($category['winners'], 'vote_count'));
            $category['nb_nominations'] = count($category['winners']);
            
            // Calculer les pourcentages
            foreach ($category['winners'] as &$winner) {
                $winner['vote_percentage'] = $category['total_votes_categorie'] > 0 
                    ? round(($winner['vote_count'] / $category['total_votes_categorie']) * 100, 1)
                    : 0;
            }
        }
        
        return array_values($groupedResults);
    }
    
    /**
     * Calcule les résultats par catégorie depuis les votes
     * @param int $editionId ID de l'édition
     * @return array Résultats calculés
     */
    private function calculateCategoryResultsFromVotes(int $editionId): array
    {
        try {
            // Récupérer toutes les catégories de l'édition
            $sqlCategories = "SELECT id_categorie, nom, plateforme_cible 
                             FROM categorie 
                             WHERE id_edition = :edition_id";
            
            $stmt = $this->pdo->prepare($sqlCategories);
            $stmt->execute([':edition_id' => $editionId]);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $results = [];
            
            foreach ($categories as $category) {
                // Pour chaque catégorie, récupérer les top 3 nominations
                $sqlNominations = "SELECT 
                                    n.id_nomination,
                                    n.libelle AS nom_nomination,
                                    COUNT(v.id_vote) AS vote_count
                                  FROM nomination n
                                  LEFT JOIN vote v ON n.id_nomination = v.id_nomination
                                  WHERE n.id_categorie = :category_id
                                  GROUP BY n.id_nomination, n.libelle
                                  ORDER BY vote_count DESC
                                  LIMIT 3";
                
                $stmt2 = $this->pdo->prepare($sqlNominations);
                $stmt2->execute([':category_id' => $category['id_categorie']]);
                $nominations = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                
                // Ajouter le rang manuellement
                $rank = 1;
                $winners = [];
                foreach ($nominations as $nomination) {
                    $position = $this->getPositionFromRank($rank);
                    $winners[] = [
                        'nom_nomination' => $nomination['nom_nomination'],
                        'vote_count' => $nomination['vote_count'],
                        'rang' => $rank,
                        'position' => $position,
                        'medal' => $this->getMedalEmoji($position),
                        'vote_percentage' => 0
                    ];
                    $rank++;
                }
                
                // Calculer le total des votes pour la catégorie
                $totalVotes = array_sum(array_column($winners, 'vote_count'));
                
                // Ajouter les pourcentages
                foreach ($winners as &$winner) {
                    $winner['vote_percentage'] = $totalVotes > 0 
                        ? round(($winner['vote_count'] / $totalVotes) * 100, 1)
                        : 0;
                }
                
                $results[] = [
                    'categorie_nom' => $category['nom'],
                    'plateforme' => $category['plateforme_cible'],
                    'id_categorie' => $category['id_categorie'],
                    'winners' => $winners,
                    'total_votes_categorie' => $totalVotes,
                    'nb_nominations' => count($winners)
                ];
            }
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Erreur calcul catégories depuis votes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Convertit un rang numérique en position textuelle
     * @param int $rank Rang numérique (1, 2, 3...)
     * @return string Position textuelle
     */
    private function getPositionFromRank(int $rank): string
    {
        $positions = [1 => 'gold', 2 => 'silver', 3 => 'bronze'];
        return $positions[$rank] ?? 'participant';
    }
    
    /**
     * Retourne l'emoji correspondant à la position
     * @param string $position Position (gold, silver, bronze)
     * @return string Emoji correspondant
     */
    private function getMedalEmoji(string $position): string
    {
        $emojis = [
            'gold' => '🥇',
            'silver' => '🥈', 
            'bronze' => '🥉'
        ];
        
        return $emojis[$position] ?? '🏅';
    }
    
     /**
     * Calcule les statistiques globales pour une édition
     * @param int $editionId ID de l'édition
     * @return array Tableau de statistiques
     */
    public function getGlobalStatistics(int $editionId): array
    {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT v.id_vote) AS total_votes,
                        COUNT(DISTINCT c.id_categorie) AS total_categories,
                        COUNT(DISTINCT n.id_nomination) AS total_nominations,
                        COUNT(DISTINCT cp.id_compte) AS total_voters,
                        COALESCE(
                            ROUND(
                                (COUNT(DISTINCT cp.id_compte) * 100.0) / 
                                NULLIF(
                                    (SELECT COUNT(DISTINCT id_compte) 
                                     FROM controle_presence 
                                     WHERE statut_a_vote = 1 
                                       AND id_categorie IN (
                                         SELECT id_categorie FROM categorie WHERE id_edition = :edition_id
                                       )), 
                                    0
                                ), 
                                1
                            ),
                            0
                        ) AS participation_rate
                    FROM categorie c
                    LEFT JOIN nomination n ON c.id_categorie = n.id_categorie
                    LEFT JOIN vote v ON n.id_nomination = v.id_nomination
                    LEFT JOIN controle_presence cp ON cp.id_categorie = c.id_categorie AND cp.statut_a_vote = 1
                    WHERE c.id_edition = :edition_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':edition_id' => $editionId]);
            
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // DEBUG: Log dos dados para verificar
            error_log("DEBUG - Estatísticas para edição $editionId: " . print_r($stats, true));
            
            // Se ainda for 0, tentar cálculo alternativo
            if (($stats['participation_rate'] ?? 0) == 0 && ($stats['total_voters'] ?? 0) > 0) {
                // Cálculo alternativo: (total de votantes / total de contas registadas) * 100
                $sqlAlternative = "SELECT 
                                    COUNT(DISTINCT cp.id_compte) as total_voters,
                                    (SELECT COUNT(DISTINCT id_compte) 
                                     FROM controle_presence 
                                     WHERE id_categorie IN (
                                       SELECT id_categorie FROM categorie WHERE id_edition = :edition_id
                                     )) as total_accounts
                                   FROM controle_presence cp
                                   WHERE cp.statut_a_vote = 1 
                                     AND cp.id_categorie IN (
                                       SELECT id_categorie FROM categorie WHERE id_edition = :edition_id2
                                     )";
                
                $stmt2 = $this->pdo->prepare($sqlAlternative);
                $stmt2->execute([
                    ':edition_id' => $editionId,
                    ':edition_id2' => $editionId
                ]);
                $altStats = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if (($altStats['total_accounts'] ?? 0) > 0) {
                    $participationRate = round(($altStats['total_voters'] / $altStats['total_accounts']) * 100, 1);
                    $stats['participation_rate'] = $participationRate;
                }
            }
            
            // Valeurs par défaut si nulles
            return [
                'total_votes' => $stats['total_votes'] ?? 0,
                'total_categories' => $stats['total_categories'] ?? 0,
                'total_nominations' => $stats['total_nominations'] ?? 0,
                'total_voters' => $stats['total_voters'] ?? 0,
                'participation_rate' => $stats['participation_rate'] ?? 0
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur calcul statistiques: " . $e->getMessage());
            return [
                'total_votes' => 0,
                'total_categories' => 0,
                'total_nominations' => 0,
                'total_voters' => 0,
                'participation_rate' => 0
            ];
        }
    }
    
    /**
     * Récupère la liste des éditions disponibles
     * @return array Liste des éditions
     */
    public function getAvailableEditions(): array
    {
        try {
            $sql = "SELECT id_edition, annee, nom 
                    FROM edition 
                    ORDER BY annee DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $editions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si aucune édition, créer une par défaut
            if (empty($editions)) {
                return [
                    [
                        'id_edition' => 1,
                        'annee' => date('Y'),
                        'nom' => 'Social Media Awards ' . date('Y')
                    ]
                ];
            }
            
            return $editions;
            
        } catch (PDOException $e) {
            error_log("Erreur récupération éditions: " . $e->getMessage());
            return [
                [
                    'id_edition' => 1,
                    'annee' => date('Y'),
                    'nom' => 'Social Media Awards ' . date('Y')
                ]
            ];
        }
    }
    
    /**
     * Met à jour les résultats dans la table resultat
     * @param int $editionId ID de l'édition
     * @return bool Succès de l'opération
     */
    public function updateResultsTable(int $editionId): bool
    {
        try {
            // Version simple compatible MySQL
            $sql = "INSERT INTO resultat (nombre_votes, rang, id_nomination, id_edition)
                    SELECT 
                        vote_counts.vote_count,
                        @row_number := CASE 
                            WHEN @current_category = vote_counts.id_categorie 
                            THEN @row_number + 1 
                            ELSE 1 
                        END AS rang,
                        vote_counts.id_nomination,
                        :edition_id
                    FROM (
                        SELECT 
                            n.id_nomination,
                            n.id_categorie,
                            COUNT(v.id_vote) AS vote_count
                        FROM nomination n
                        LEFT JOIN vote v ON n.id_nomination = v.id_nomination
                        JOIN categorie c ON n.id_categorie = c.id_categorie
                        WHERE c.id_edition = :edition_id2
                        GROUP BY n.id_nomination, n.id_categorie
                    ) AS vote_counts
                    CROSS JOIN (SELECT @row_number := 0, @current_category := 0) AS vars
                    ORDER BY vote_counts.id_categorie, vote_counts.vote_count DESC";
            
            // D'abord supprimer les anciens résultats
            $deleteSql = "DELETE FROM resultat WHERE id_edition = :edition_id";
            $deleteStmt = $this->pdo->prepare($deleteSql);
            $deleteStmt->execute([':edition_id' => $editionId]);
            
            // Puis insérer les nouveaux
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':edition_id' => $editionId,
                ':edition_id2' => $editionId
            ]);
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Erreur mise à jour résultats: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère toutes les éditions avec leurs statistiques
     * @return array Éditions avec statistiques
     */
    public function getAllEditionsWithStats(): array
    {
        try {
            $sql = "SELECT 
                        e.id_edition,
                        e.annee,
                        e.nom,
                        e.est_active,
                        COUNT(DISTINCT c.id_categorie) AS nb_categories,
                        COUNT(DISTINCT n.id_nomination) AS nb_nominations,
                        COUNT(DISTINCT v.id_vote) AS nb_votes
                    FROM edition e
                    LEFT JOIN categorie c ON e.id_edition = c.id_edition
                    LEFT JOIN nomination n ON c.id_categorie = n.id_categorie
                    LEFT JOIN vote v ON n.id_nomination = v.id_nomination
                    GROUP BY e.id_edition, e.annee, e.nom, e.est_active
                    ORDER BY e.annee DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur récupération éditions avec stats: " . $e->getMessage());
            return [];
        }
    }
    // Adicione estes métodos ao seu ResultsService.php existente

/**
 * Vérifie si l'édition est active (votes en cours)
 * @param int $editionId ID de l'édition
 * @return array Informations sur le statut de l'édition
 */
public function getEditionStatus(int $editionId): array
{
    try {
        $sql = "SELECT 
                    e.id_edition,
                    e.annee,
                    e.nom,
                    e.est_active,
                    e.date_debut,
                    e.date_fin,
                    e.date_debut_candidatures,
                    e.date_fin_candidatures,
                    NOW() as now_date
                FROM edition e
                WHERE e.id_edition = :edition_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':edition_id' => $editionId]);
        $edition = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$edition) {
            return [
                'active' => false,
                'message' => 'Édition non trouvée',
                'dates' => null
            ];
        }

        // Usar strtotime em vez de DateTime para evitar problemas de namespace
        $now = strtotime($edition['now_date']);
        $dateDebut = strtotime($edition['date_debut']);
        $dateFin = strtotime($edition['date_fin']);

        // Édition active si: est_active = 1 ET maintenant entre date_debut et date_fin
        $isActive = ($edition['est_active'] == 1 && 
                    $now >= $dateDebut && 
                    $now <= $dateFin);

        // Vérifier si les votes sont terminés
        $votesFinished = ($now > $dateFin);
        
        // Vérifier si c'est avant le début des votes
        $votesNotStarted = ($now < $dateDebut);

        return [
            'active' => $isActive,
            'votes_finished' => $votesFinished,
            'votes_not_started' => $votesNotStarted,
            'status' => $edition['est_active'] ? 'active' : 'inactive',
            'date_debut' => $edition['date_debut'],
            'date_fin' => $edition['date_fin'],
            'date_debut_candidatures' => $edition['date_debut_candidatures'],
            'date_fin_candidatures' => $edition['date_fin_candidatures'],
            'nom' => $edition['nom'],
            'annee' => $edition['annee'],
            'message' => $isActive ? 'Votes en cours' : ($votesFinished ? 'Votes terminés' : ($votesNotStarted ? 'Votes pas encore commencés' : 'Édition inactive'))
        ];
        
    } catch (PDOException $e) {
        error_log("Erreur récupération statut édition: " . $e->getMessage());
        return [
            'active' => false,
            'votes_finished' => false,
            'votes_not_started' => false,
            'message' => 'Erreur système'
        ];
    }
}

/**
 * Récupère uniquement les éditions terminées (pour les résultats)
 * @return array Liste des éditions terminées
 */
public function getFinishedEditions(): array
{
    try {
        $sql = "SELECT id_edition, annee, nom 
                FROM edition 
                WHERE (date_fin < NOW() OR est_active = 0)
                ORDER BY annee DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        $editions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si aucune édition terminée, retourner toutes les éditions
        if (empty($editions)) {
            return $this->getAvailableEditions();
        }
        
        return $editions;
        
    } catch (PDOException $e) {
        error_log("Erreur récupération éditions terminées: " . $e->getMessage());
        return $this->getAvailableEditions();
    }
}
}
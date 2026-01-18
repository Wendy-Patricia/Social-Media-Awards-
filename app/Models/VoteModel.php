<?php
// app/Models/Vote.php

require_once __DIR__ . '/../../config/database.php';

/**
 * Modèle gérant toutes les opérations liées au système de vote
 * - Génération de tokens anonymes
 * - Enregistrement des votes
 * - Vérification des droits de vote
 * - Récupération des statistiques
 */
class Vote
{
    private $db;

    /**
     * Constructeur du modèle Vote
     * Initialise la connexion à la base de données
     */
    public function __construct()
    {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    /**
     * Obtient l'instance de la base de données
     * 
     * @return PDO Instance de connexion à la base de données
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Génère un token anonyme pour un utilisateur dans une catégorie
     * 
     * @param int $userId ID du compte utilisateur
     * @param int $categoryId ID de la catégorie
     * @return string|null Token généré ou null en cas d'erreur
     */
    public function generateToken($userId, $categoryId)
    {
        try {
            $stmt = $this->db->prepare("CALL gerar_token_anonimo(:id_compte, :id_categorie, @token_value)");
            $stmt->execute([
                ':id_compte' => $userId,
                ':id_categorie' => $categoryId
            ]);

            // Obtient le token généré
            $stmt = $this->db->query("SELECT @token_value as token_value");
            $result = $stmt->fetch();

            return $result ? $result['token_value'] : null;
        } catch (Exception $e) {
            error_log("Erreur génération token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Enregistre un vote dans le système
     * 
     * @param string $token Token anonyme de l'utilisateur
     * @param string $encryptedVote Vote chiffré
     * @param int $nominationId ID de la nomination votée
     * @return int|false ID du vote enregistré ou false en cas d'erreur
     */
    public function castVote($token, $encryptedVote, $nominationId)
    {
        try {
            $stmt = $this->db->prepare("CALL processar_voto(:token, :vote_chiffre, :id_nomination, @id_vote)");
            $stmt->execute([
                ':token' => $token,
                ':vote_chiffre' => $encryptedVote,
                ':id_nomination' => $nominationId
            ]);

            // Obtient l'ID du vote enregistré
            $stmt = $this->db->query("SELECT @id_vote as id_vote");
            $result = $stmt->fetch();

            return $result ? $result['id_vote'] : null;
        } catch (Exception $e) {
            error_log("Erreur enregistrement vote: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un utilisateur a déjà voté dans une catégorie
     * 
     * @param int $userId ID du compte utilisateur
     * @param int $categoryId ID de la catégorie
     * @return bool True si l'utilisateur a déjà voté
     */
    public function hasUserVoted($userId, $categoryId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT statut_a_vote 
                FROM CONTROLE_PRESENCE 
                WHERE id_compte = :id_compte 
                AND id_categorie = :id_categorie
            ");
            $stmt->execute([
                ':id_compte' => $userId,
                ':id_categorie' => $categoryId
            ]);

            $result = $stmt->fetch();
            return $result && $result['statut_a_vote'] == 1;
        } catch (Exception $e) {
            error_log("Erreur vérification vote: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtient les catégories disponibles pour le vote
     * Prend en compte les périodes de vote des catégories et de l'édition
     * 
     * @return array Liste des catégories disponibles
     */
    public function getVotingCategories()
    {
        try {
            $now = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
                SELECT c.*, e.annee as edition_year,
                       COUNT(DISTINCT n.id_nomination) as nomination_count
                FROM CATEGORIE c
                JOIN EDITION e ON c.id_edition = e.id_edition
                LEFT JOIN NOMINATION n ON c.id_categorie = n.id_categorie
                WHERE (
                    (c.date_debut_votes IS NULL AND c.date_fin_votes IS NULL AND e.date_debut <= :now AND e.date_fin >= :now)
                    OR
                    (c.date_debut_votes IS NOT NULL AND c.date_fin_votes IS NOT NULL 
                     AND c.date_debut_votes <= :now AND c.date_fin_votes >= :now)
                )
                AND e.est_active = 1
                GROUP BY c.id_categorie
                ORDER BY c.nom ASC
            ");

            $stmt->execute([':now' => $now]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erreur récupération catégories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtient les nominations approuvées pour une catégorie
     * 
     * @param int $categoryId ID de la catégorie
     * @return array Liste des nominations avec le compte de votes
     */
    public function getNominationsForCategory($categoryId)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT n.*, c.pseudonyme as candidate_name,
                   (SELECT COUNT(*) FROM VOTE v WHERE v.id_nomination = n.id_nomination) as vote_count
            FROM NOMINATION n
            JOIN COMPTE c ON n.id_compte = c.id_compte
            WHERE n.id_categorie = :id_categorie
            AND n.date_approbation IS NOT NULL
            ORDER BY n.libelle ASC
        ");

            $stmt->execute([':id_categorie' => $categoryId]);
            $nominations = $stmt->fetchAll();

            return $nominations;
        } catch (Exception $e) {
            error_log("Erreur récupération nominations: " . $e->getMessage());
            return []; // Retourne un tableau vide, PAS de placeholders
        }
    }

    // Méthode privée pour obtenir des placeholders (désactivée)
    private function getNominationPlaceholders($categoryId)
    {
        return []; // Retourne un tableau vide, pas de placeholders
    }

    /**
     * Obtient le certificat de participation d'un utilisateur pour une catégorie
     * 
     * @param int $userId ID du compte utilisateur
     * @param int $categoryId ID de la catégorie
     * @return array|null Certificat de participation ou null
     */
    public function getParticipationCertificate($userId, $categoryId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM CERTIFICAT_PARTICIPATION
                WHERE id_compte = :id_compte 
                AND id_categorie = :id_categorie
                ORDER BY date_emission DESC
                LIMIT 1
            ");

            $stmt->execute([
                ':id_compte' => $userId,
                ':id_categorie' => $categoryId
            ]);

            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erreur récupération certificat: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtient l'historique de votes d'un utilisateur (informations anonymes)
     * 
     * @param int $userId ID du compte utilisateur
     * @return array Historique des votes
     */
    public function getUserVotingHistory($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.nom as category_name,
                    cp.date_controle as vote_date,
                    cp.statut_a_vote as has_voted
                FROM CONTROLE_PRESENCE cp
                JOIN CATEGORIE c ON cp.id_categorie = c.id_categorie
                WHERE cp.id_compte = :id_compte
                ORDER BY cp.date_controle DESC
            ");

            $stmt->execute([':id_compte' => $userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erreur historique votes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si une catégorie est active pour le vote
     * 
     * @param int $categoryId ID de la catégorie
     * @return bool True si la catégorie est active
     */
    public function isCategoryActive($categoryId)
    {
        try {
            $now = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
                SELECT 
                    CASE 
                        WHEN date_debut_votes IS NOT NULL AND date_fin_votes IS NOT NULL THEN
                            :now BETWEEN date_debut_votes AND date_fin_votes
                        ELSE
                            :now BETWEEN e.date_debut AND e.date_fin
                    END as is_active
                FROM CATEGORIE c
                JOIN EDITION e ON c.id_edition = e.id_edition
                WHERE c.id_categorie = :id_categorie
                AND e.est_active = 1
            ");

            $stmt->execute([
                ':id_categorie' => $categoryId,
                ':now' => $now
            ]);

            $result = $stmt->fetch();
            return $result && $result['is_active'] == 1;
        } catch (Exception $e) {
            error_log("Erreur vérification catégorie: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Chiffre un vote (simulation - en production utiliser une méthode plus sécurisée)
     * 
     * @param int $nominationId ID de la nomination
     * @param int $userId ID de l'utilisateur
     * @return string Vote chiffré en base64
     */
    public function encryptVote($nominationId, $userId)
    {
        // En production, utiliser le chiffrement asymétrique
        // Ici, nous utilisons une simulation pour la démonstration
        $data = [
            'nomination_id' => $nominationId,
            'timestamp' => time(),
            'user_hash' => hash('sha256', $userId . 'salt_' . time())
        ];

        return base64_encode(json_encode($data));
    }

    /**
     * Obtient l'ID de la catégorie d'une nomination
     * 
     * @param int $nominationId ID de la nomination
     * @return int|null ID de la catégorie ou null
     */
    public function getCategoryIdFromNomination($nominationId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id_categorie FROM NOMINATION 
                WHERE id_nomination = :id_nomination
            ");

            $stmt->execute([':id_nomination' => $nominationId]);
            $result = $stmt->fetch();

            return $result ? $result['id_categorie'] : null;
        } catch (Exception $e) {
            error_log("Erreur récupération catégorie: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtient les informations d'une catégorie
     * 
     * @param int $categoryId ID de la catégorie
     * @return array|null Informations de la catégorie ou null
     */
    public function getCategoryInfo($categoryId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, e.annee, e.nom as edition_nom
                FROM CATEGORIE c
                JOIN EDITION e ON c.id_edition = e.id_edition
                WHERE c.id_categorie = :id_categorie
            ");

            $stmt->execute([':id_categorie' => $categoryId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erreur récupération info catégorie: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Valide un token anonyme
     * 
     * @param string $token Token à valider
     * @param int $userId ID de l'utilisateur
     * @param int $categoryId ID de la catégorie
     * @return bool True si le token est valide
     */
    public function validateToken($token, $userId, $categoryId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id_token FROM TOKEN_ANONYME 
                WHERE token_value = :token 
                AND id_compte = :user_id 
                AND id_categorie = :category_id
                AND est_utilise = FALSE 
                AND date_expiration > NOW()
            ");

            $stmt->execute([
                ':token' => $token,
                ':user_id' => $userId,
                ':category_id' => $categoryId
            ]);

            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            error_log("Erreur validation token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtient les statistiques de vote
     * 
     * @param int|null $userId ID de l'utilisateur (optionnel)
     * @return array Statistiques de vote
     */
    public function getVotingStatistics($userId = null)
    {
        try {
            $stats = [];

            // Total des catégories
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM CATEGORIE");
            $result = $stmt->fetch();
            $stats['total_categories'] = $result['total'] ?? 0;

            // Total des nominations
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM NOMINATION");
            $result = $stmt->fetch();
            $stats['total_nominations'] = $result['total'] ?? 0;

            // Total des votes
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM VOTE");
            $result = $stmt->fetch();
            $stats['total_votes'] = $result['total'] ?? 0;

            // Statistiques de l'utilisateur si fourni
            if ($userId) {
                // Votes de l'utilisateur
                $stmt = $this->db->prepare("
                    SELECT COUNT(DISTINCT cp.id_categorie) as voted_categories
                    FROM CONTROLE_PRESENCE cp
                    WHERE cp.id_compte = :user_id AND cp.statut_a_vote = 1
                ");
                $stmt->execute([':user_id' => $userId]);
                $result = $stmt->fetch();
                $stats['user_voted_categories'] = $result['voted_categories'] ?? 0;

                // Certificats de l'utilisateur
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as certificates
                    FROM CERTIFICAT_PARTICIPATION
                    WHERE id_compte = :user_id
                ");
                $stmt->execute([':user_id' => $userId]);
                $result = $stmt->fetch();
                $stats['user_certificates'] = $result['certificates'] ?? 0;
            }

            return $stats;
        } catch (Exception $e) {
            error_log("Erreur statistiques vote: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtient le nombre de votes d'un utilisateur
     * 
     * @param int $userId ID du compte utilisateur
     * @return int Nombre de votes
     */
    public function getUserVotesCount($userId)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT v.id_vote) as count
            FROM VOTE v
            JOIN TOKEN_ANONYME ta ON v.id_token = ta.id_token
            WHERE ta.id_compte = :id_compte
        ");

            $stmt->execute([':id_compte' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Erreur getUserVotesCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Vérifie si un utilisateur a voté dans une catégorie spécifique
     * 
     * @param int $userId ID du compte utilisateur
     * @param int $categoryId ID de la catégorie
     * @return bool True si l'utilisateur a voté dans cette catégorie
     */
    public function hasUserVotedInCategory($userId, $categoryId)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM VOTE v
            JOIN TOKEN_ANONYME ta ON v.id_token = ta.id_token
            JOIN NOMINATION n ON v.id_nomination = n.id_nomination
            WHERE ta.id_compte = :id_compte 
            AND n.id_categorie = :id_categorie
        ");

            $stmt->execute([
                ':id_compte' => $userId,
                ':id_categorie' => $categoryId
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['count'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Erreur hasUserVotedInCategory: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un utilisateur a voté dans des catégories actives
     * 
     * @param int $userId ID du compte utilisateur
     * @return bool True si l'utilisateur a voté dans des catégories actives
     */
    public function hasUserVotedInActiveCategories($userId)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM VOTE v
            JOIN TOKEN_ANONYME ta ON v.id_token = ta.id_token
            JOIN NOMINATION n ON v.id_nomination = n.id_nomination
            JOIN CATEGORIE c ON n.id_categorie = c.id_categorie
            WHERE ta.id_compte = :id_compte 
            AND c.date_fin_votes > NOW()
        ");

            $stmt->execute([':id_compte' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ($result['count'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Erreur hasUserVotedInActiveCategories: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtient les votes récents d'un utilisateur
     * 
     * @param int $userId ID du compte utilisateur
     * @param int $limit Nombre maximum de votes à récupérer
     * @return array Liste des votes récents
     */
    public function getUserRecentVotes($userId, $limit = 5)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT 
                v.id_vote,
                v.date_heure_vote,
                n.libelle as nomination_libelle,
                c.nom as category_nom,
                c.id_categorie
            FROM VOTE v
            JOIN TOKEN_ANONYME ta ON v.id_token = ta.id_token
            JOIN NOMINATION n ON v.id_nomination = n.id_nomination
            JOIN CATEGORIE c ON n.id_categorie = c.id_categorie
            WHERE ta.id_compte = :id_compte
            ORDER BY v.date_heure_vote DESC
            LIMIT :limit
        ");

            $stmt->bindValue(':id_compte', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getUserRecentVotes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si un utilisateur peut voter dans une catégorie
     * - Vérifie si la période de vote est ouverte
     * - Vérifie si l'utilisateur n'a pas déjà voté
     * - Vérifie s'il y a des nominations disponibles
     * 
     * @param int $userId ID du compte utilisateur
     * @param int $categoryId ID de la catégorie
     * @return array Résultat de la vérification avec informations détaillées
     */
    public function canVoteInCategory($userId, $categoryId)
    {
        try {
            $now = date('Y-m-d H:i:s');

            $stmt = $this->db->prepare("
            SELECT 
                c.id_categorie,
                c.nom,
                CASE 
                    WHEN c.date_debut_votes IS NOT NULL AND c.date_fin_votes IS NOT NULL THEN
                        :now BETWEEN c.date_debut_votes AND c.date_fin_votes
                    ELSE
                        :now BETWEEN e.date_debut AND e.date_fin
                END as voting_open,
                
                IFNULL(cp.statut_a_vote, 0) as has_voted,
                
                COUNT(DISTINCT n.id_nomination) as nomination_count,
                
                -- DEBUG info
                c.date_debut_votes as cat_start,
                c.date_fin_votes as cat_end,
                e.date_debut as edition_start,
                e.date_fin as edition_end
                
            FROM CATEGORIE c
            JOIN EDITION e ON c.id_edition = e.id_edition
            LEFT JOIN CONTROLE_PRESENCE cp ON (
                cp.id_categorie = c.id_categorie 
                AND cp.id_compte = :user_id
            )
            LEFT JOIN NOMINATION n ON c.id_categorie = n.id_categorie
            WHERE c.id_categorie = :category_id
            AND e.est_active = 1
            GROUP BY c.id_categorie
        ");

            $stmt->execute([
                ':category_id' => $categoryId,
                ':user_id' => $userId,
                ':now' => $now
            ]);

            $result = $stmt->fetch();

            if (!$result) {
                error_log("DEBUG: Categoria $categoryId não encontrada ou edição inativa");
                return [
                    'can_vote' => false,
                    'reason' => 'Categoria não encontrada ou edição inativa',
                    'debug' => ['category_id' => $categoryId, 'user_id' => $userId]
                ];
            }

            $canVote = ($result['voting_open'] == 1)
                && ($result['has_voted'] == 0)
                && ($result['nomination_count'] > 0);

            error_log("DEBUG canVoteInCategory: " . json_encode([
                'category_id' => $categoryId,
                'category_name' => $result['nom'],
                'voting_open' => $result['voting_open'],
                'has_voted' => $result['has_voted'],
                'nomination_count' => $result['nomination_count'],
                'can_vote' => $canVote,
                'dates' => [
                    'category' => ['start' => $result['cat_start'], 'end' => $result['cat_end']],
                    'edition' => ['start' => $result['edition_start'], 'end' => $result['edition_end']],
                    'now' => $now
                ]
            ]));

            return [
                'can_vote' => $canVote,
                'voting_open' => $result['voting_open'] == 1,
                'has_voted' => $result['has_voted'] == 1,
                'nominations_available' => $result['nomination_count'] > 0,
                'nomination_count' => $result['nomination_count'],
                'category_name' => $result['nom']
            ];
        } catch (Exception $e) {
            error_log("Erreur vérification vote: " . $e->getMessage());
            return [
                'can_vote' => false,
                'reason' => 'Erro técnico: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Compte le nombre de catégories dans lesquelles un utilisateur a voté
     * 
     * @param int $userId ID de l'électeur
     * @return int Nombre de catégories votées
     */
    public function getCategoriesVotedCount($userId)
    {
        try {
            $sql = "SELECT COUNT(DISTINCT id_categorie) as count 
                    FROM votes 
                    WHERE id_electeur = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return $result ? $result['count'] : 0;
        } catch (Exception $e) {
            error_log("Erro ao contar categorias votadas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtient les nominations pour une catégorie avec les images des candidats
     * Retourne un placeholder si aucune nomination n'est approuvée
     * 
     * @param int $categoryId ID de la catégorie
     * @return array Liste des nominations avec images
     */
    public function getNominationsForCategoryWithImages($categoryId)
    {
        try {
            $stmt = $this->db->prepare("
            SELECT n.*, c.pseudonyme as candidate_name,
                   co.photo_profil as candidate_image,
                   (SELECT COUNT(*) FROM VOTE v WHERE v.id_nomination = n.id_nomination) as vote_count
            FROM NOMINATION n
            JOIN COMPTE c ON n.id_compte = c.id_compte
            LEFT JOIN COMPTE co ON n.id_compte = co.id_compte
            WHERE n.id_categorie = :id_categorie
            AND n.date_approbation IS NOT NULL
            ORDER BY n.libelle ASC
        ");

            $stmt->execute([':id_categorie' => $categoryId]);
            $nominations = $stmt->fetchAll();

            // Si aucune nomination, créer un placeholder
            if (empty($nominations)) {
                return [
                    [
                        'id_nomination' => 0,
                        'libelle' => 'Aucune nomination approuvée',
                        'candidate_name' => 'Administrateur',
                        'vote_count' => 0,
                        'url_image' => 'assets/images/default-nominee.jpg',
                        'plateforme' => 'all'
                    ]
                ];
            }

            return $nominations;
        } catch (Exception $e) {
            error_log("Erreur récupération nominations: " . $e->getMessage());
            return [];
        }
    }
}
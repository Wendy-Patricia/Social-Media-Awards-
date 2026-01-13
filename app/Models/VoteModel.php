<?php
// app/Models/Vote.php
require_once __DIR__ . '/../../config/database.php';

class Vote {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    public function getDb() {
        return $this->db;
    }

    /**
     * Gerar token anônimo para um usuário em uma categoria
     */
    public function generateToken($userId, $categoryId) {
        try {
            $stmt = $this->db->prepare("CALL gerar_token_anonimo(:id_compte, :id_categorie, @token_value)");
            $stmt->execute([
                ':id_compte' => $userId,
                ':id_categorie' => $categoryId
            ]);
            
            // Obter o token gerado
            $stmt = $this->db->query("SELECT @token_value as token_value");
            $result = $stmt->fetch();
            
            return $result ? $result['token_value'] : null;
            
        } catch (Exception $e) {
            error_log("Erreur génération token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registrar um voto
     */
    public function castVote($token, $encryptedVote, $nominationId) {
        try {
            $stmt = $this->db->prepare("CALL processar_voto(:token, :vote_chiffre, :id_nomination, @id_vote)");
            $stmt->execute([
                ':token' => $token,
                ':vote_chiffre' => $encryptedVote,
                ':id_nomination' => $nominationId
            ]);
            
            // Obter ID do voto registrado
            $stmt = $this->db->query("SELECT @id_vote as id_vote");
            $result = $stmt->fetch();
            
            return $result ? $result['id_vote'] : null;
            
        } catch (Exception $e) {
            error_log("Erreur enregistrement vote: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar se usuário já votou em uma categoria
     */
    public function hasUserVoted($userId, $categoryId) {
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
     * Obter categorias disponíveis para votação
     */
    public function getVotingCategories() {
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
     * Obter nomeações para uma categoria
     */
    public function getNominationsForCategory($categoryId) {
    try {
        // Primeiro verificar se há nominações na tabela nomination
        $sql = "SELECT COUNT(*) as count FROM nomination WHERE id_categorie = :category_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':category_id' => $categoryId]);
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            // Se não houver nominações, criar placeholders
            return $this->getNominationPlaceholders($categoryId);
        }
        
        $stmt = $this->db->prepare("
            SELECT n.*, c.pseudonyme as candidate_name,
                   (SELECT COUNT(*) FROM VOTE v WHERE v.id_nomination = n.id_nomination) as vote_count
            FROM NOMINATION n
            JOIN COMPTE c ON n.id_compte = c.id_compte
            WHERE n.id_categorie = :id_categorie
            ORDER BY n.libelle ASC
        ");
        
        $stmt->execute([':id_categorie' => $categoryId]);
        $nominations = $stmt->fetchAll();
        
        return $nominations;
        
    } catch (Exception $e) {
        error_log("Erreur récupération nominations: " . $e->getMessage());
        return $this->getNominationPlaceholders($categoryId);
    }
}

private function getNominationPlaceholders($categoryId) {
    // Placeholders para teste
    $placeholders = [
        [
            'id_nomination' => 9991,
            'libelle' => 'Candidat Test A',
            'candidate_name' => 'Testeur A',
            'vote_count' => 0,
            'url_image' => 'assets/images/default-nominee.jpg',
            'plateforme' => 'Test'
        ],
        [
            'id_nomination' => 9992,
            'libelle' => 'Candidat Test B',
            'candidate_name' => 'Testeur B',
            'vote_count' => 0,
            'url_image' => 'assets/images/default-nominee.jpg',
            'plateforme' => 'Test'
        ],
        [
            'id_nomination' => 9993,
            'libelle' => 'Candidat Test C',
            'candidate_name' => 'Testeur C',
            'vote_count' => 0,
            'url_image' => 'assets/images/default-nominee.jpg',
            'plateforme' => 'Test'
        ]
    ];
    
    return $placeholders;
}

    /**
     * Obter certificado de participação
     */
    public function getParticipationCertificate($userId, $categoryId) {
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
     * Obter histórico de votos do usuário (apenas informações anônimas)
     */
    public function getUserVotingHistory($userId) {
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
     * Verificar se categoria está ativa para votação
     */
    public function isCategoryActive($categoryId) {
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
     * Criptografar voto (simulação - em produção usar método mais seguro)
     */
    public function encryptVote($nominationId, $userId) {
        // Em produção, usar criptografia assimétrica
        // Aqui usamos uma simulação para demonstração
        $data = [
            'nomination_id' => $nominationId,
            'timestamp' => time(),
            'user_hash' => hash('sha256', $userId . 'salt_' . time())
        ];
        
        return base64_encode(json_encode($data));
    }

    /**
     * Obter ID da categoria de uma nomeação
     */
    public function getCategoryIdFromNomination($nominationId) {
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
     * Obter informações de uma categoria
     */
    public function getCategoryInfo($categoryId) {
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
     * Verificar se token é válido
     */
    public function validateToken($token, $userId, $categoryId) {
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
     * Obter estatísticas de votação
     */
    public function getVotingStatistics($userId = null) {
        try {
            $stats = [];
            
            // Total de categorias
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM CATEGORIE");
            $result = $stmt->fetch();
            $stats['total_categories'] = $result['total'] ?? 0;
            
            // Total de nomeações
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM NOMINATION");
            $result = $stmt->fetch();
            $stats['total_nominations'] = $result['total'] ?? 0;
            
            // Total de votos
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM VOTE");
            $result = $stmt->fetch();
            $stats['total_votes'] = $result['total'] ?? 0;
            
            // Estatísticas do usuário se fornecido
            if ($userId) {
                // Votos do usuário
                $stmt = $this->db->prepare("
                    SELECT COUNT(DISTINCT cp.id_categorie) as voted_categories
                    FROM CONTROLE_PRESENCE cp
                    WHERE cp.id_compte = :user_id AND cp.statut_a_vote = 1
                ");
                $stmt->execute([':user_id' => $userId]);
                $result = $stmt->fetch();
                $stats['user_voted_categories'] = $result['voted_categories'] ?? 0;
                
                // Certificados do usuário
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
    public function getUserVotesCount($userId) {
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

public function hasUserVotedInCategory($userId, $categoryId) {
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

public function hasUserVotedInActiveCategories($userId) {
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

public function getUserRecentVotes($userId, $limit = 5) {
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

public function canVoteInCategory($userId, $categoryId) {
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

public function getNominationsForCategoryWithImages($categoryId) {
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
        
        // Se não houver nominações, criar um placeholder
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
?>
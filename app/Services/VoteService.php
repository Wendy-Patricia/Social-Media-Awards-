<?php
// app/Services/VoteService.php

/**
 * Service de gestion des votes
 * Gère la logique métier liée au processus de vote
 */
class VoteService
{
    private $voteModel;
    private $userModel;

    /**
     * Constructeur du service de vote
     * Initialise les modèles nécessaires
     */
    public function __construct()
    {
        require_once __DIR__ . '/../Models/VoteModel.php';
        require_once __DIR__ . '/../Models/UserModel.php';

        $this->voteModel = new Vote();
        $this->userModel = new User();
    }

    /**
     * Méthode getter pour accéder au modèle de vote
     * 
     * @return Vote Modèle de vote
     */
    public function getVoteModel()
    {
        return $this->voteModel;
    }

    /**
     * Obtenir les catégories disponibles pour l'utilisateur
     * VERSION CORRIGÉE - Affiche toutes les catégories actives
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Catégories disponibles avec informations de vote
     */
    public function getAvailableCategoriesForUser($userId)
    {
        try {
            error_log("=== DEBUG getAvailableCategoriesForUser ===");
            error_log("Utilisateur ID: " . $userId);

            // 1. Obtenir l'édition active
            $activeEdition = $this->getActiveEdition();

            if (!$activeEdition) {
                error_log("DEBUG: Aucune édition active trouvée!");
                return [];
            }

            error_log("DEBUG: Édition active ID: " . $activeEdition['id_edition']);

            // 2. Obtenir TOUTES les catégories de l'édition active
            $categories = $this->getCategoriesForEdition($activeEdition['id_edition']);

            if (empty($categories)) {
                error_log("DEBUG: Aucune catégorie trouvée pour l'édition!");
                return [];
            }

            error_log("DEBUG: Total catégories de l'édition: " . count($categories));

            $available = [];
            $now = date('Y-m-d H:i:s');

            foreach ($categories as $category) {
                $categoryId = $category['id_categorie'] ?? 0;

                if ($categoryId <= 0) {
                    continue;
                }

                error_log("--- Traitement catégorie ID: {$categoryId} ---");

                // 3. Vérifier si elle est dans la période de vote
                $isActive = $this->isCategoryInVotingPeriod($category, $activeEdition);

                if (!$isActive) {
                    error_log("Catégorie {$categoryId} n'est pas active (hors période)");
                    continue;
                }

                // 4. Compter les nominations
                $nominations = $this->voteModel->getNominationsForCategory($categoryId);
                $nominationCount = count($nominations);

                // 5. Vérifier si l'utilisateur a déjà voté
                $hasVoted = $this->voteModel->hasUserVoted($userId, $categoryId);

                // 6. Déterminer si peut voter maintenant
                $canVoteNow = !$hasVoted && $nominationCount > 0;

                // 7. Ajouter les informations
                $category['nomination_count'] = $nominationCount;
                $category['has_voted'] = $hasVoted;
                $category['is_active'] = true;
                $category['can_vote'] = $canVoteNow;
                $category['has_nominations'] = $nominationCount > 0;
                $category['nominations'] = $nominations;

                // 8. Ajouter les dates formatées pour l'affichage
                if ($category['date_debut_votes'] && $category['date_fin_votes']) {
                    $category['vote_start_formatted'] = date('d/m/Y', strtotime($category['date_debut_votes']));
                    $category['vote_end_formatted'] = date('d/m/Y', strtotime($category['date_fin_votes']));
                } else {
                    $category['vote_start_formatted'] = date('d/m/Y', strtotime($activeEdition['date_debut']));
                    $category['vote_end_formatted'] = date('d/m/Y', strtotime($activeEdition['date_fin']));
                }

                $available[] = $category;

                error_log("✓ Catégorie {$categoryId} '{$category['nom']}' ajoutée");
                error_log("  - Nominations: {$nominationCount}");
                error_log("  - Déjà voté: " . ($hasVoted ? 'OUI' : 'NON'));
                error_log("  - Peut voter maintenant: " . ($canVoteNow ? 'OUI' : 'NON'));
                error_log("  - A des nominations: " . ($nominationCount > 0 ? 'OUI' : 'NON'));
            }

            error_log("=== FIN getAvailableCategoriesForUser ===");
            error_log("Catégories disponibles: " . count($available));

            return $available;
        } catch (Exception $e) {
            error_log("ERREUR dans getAvailableCategoriesForUser: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtenir l'édition active
     * 
     * @return array|null Informations de l'édition active ou null
     */
    private function getActiveEdition()
    {
        try {
            $db = $this->voteModel->getDb();

            $stmt = $db->query("
                SELECT * FROM edition 
                WHERE est_active = 1 
                ORDER BY annee DESC 
                LIMIT 1
            ");

            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("ERREUR dans getActiveEdition: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir les catégories pour une édition
     * 
     * @param int $editionId ID de l'édition
     * @return array Liste des catégories de l'édition
     */
    private function getCategoriesForEdition($editionId)
    {
        try {
            $db = $this->voteModel->getDb();

            $stmt = $db->prepare("
                SELECT c.*, e.annee, e.nom as edition_nom
                FROM categorie c
                JOIN edition e ON c.id_edition = e.id_edition
                WHERE c.id_edition = :edition_id
                ORDER BY c.nom ASC
            ");

            $stmt->execute([':edition_id' => $editionId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("ERREUR dans getCategoriesForEdition: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifier si la catégorie est dans la période de vote
     * 
     * @param array $category Informations de la catégorie
     * @param array $edition Informations de l'édition
     * @return bool True si dans la période de vote
     */
    private function isCategoryInVotingPeriod($category, $edition)
    {
        $now = date('Y-m-d H:i:s');

        // Si la catégorie a des dates spécifiques, les utiliser
        if ($category['date_debut_votes'] && $category['date_fin_votes']) {
            $start = $category['date_debut_votes'];
            $end = $category['date_fin_votes'];
            return ($now >= $start && $now <= $end);
        }

        // Sinon, utiliser les dates de l'édition
        if ($edition['date_debut'] && $edition['date_fin']) {
            $start = $edition['date_debut'];
            $end = $edition['date_fin'];
            return ($now >= $start && $now <= $end);
        }

        return false;
    }

    /**
     * Démarrer le processus de vote pour une catégorie
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $categoryId ID de la catégorie
     * @return array Résultat du démarrage du vote
     */
    public function startVotingProcess($userId, $categoryId)
    {
        error_log("=== DEBUG startVotingProcess ===");
        error_log("Utilisateur: {$userId}, Catégorie: {$categoryId}");

        // 1. Vérifier si peut voter
        $canVote = $this->canUserVoteSimple($userId, $categoryId);

        if (!$canVote['can_vote']) {
            error_log("DEBUG: Utilisateur NE PEUT PAS voter. Raison: " . $canVote['reason']);
            return [
                'success' => false,
                'message' => $canVote['reason'],
                'already_voted' => $canVote['already_voted'] ?? false
            ];
        }

        error_log("DEBUG: Utilisateur PEUT voter");

        // 2. Vérifier s'il y a des nominations
        $nominations = $this->voteModel->getNominationsForCategory($categoryId);

        if (empty($nominations)) {
            error_log("DEBUG: Aucune nomination trouvée pour catégorie {$categoryId}");
            return [
                'success' => false,
                'message' => 'Aucune nomination disponible pour cette catégorie'
            ];
        }

        error_log("DEBUG: Trouvées " . count($nominations) . " nominations");

        // 3. Générer le token anonyme
        $token = $this->voteModel->generateToken($userId, $categoryId);

        if (!$token) {
            error_log("DEBUG: Échec de génération du token");
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération du token'
            ];
        }

        error_log("DEBUG: Token généré avec succès: " . substr($token, 0, 20) . "...");

        return [
            'success' => true,
            'token' => $token,
            'nominations' => $nominations,
            'category_id' => $categoryId,
            'nomination_count' => count($nominations),
            'message' => 'Prêt à voter!'
        ];
    }

    /**
     * Vérification SIMPLIFIÉE si l'utilisateur peut voter
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $categoryId ID de la catégorie
     * @return array Résultat de la vérification
     */
    public function canUserVoteSimple($userId, $categoryId)
    {
        try {
            error_log("=== DEBUG canUserVoteSimple ===");
            error_log("Utilisateur: {$userId}, Catégorie: {$categoryId}");

            // 1. Obtenir les informations de la catégorie
            $categoryInfo = $this->voteModel->getCategoryInfo($categoryId);

            if (!$categoryInfo) {
                error_log("DEBUG: Catégorie non trouvée");
                return [
                    'can_vote' => false,
                    'reason' => 'Catégorie non trouvée',
                    'already_voted' => false
                ];
            }

            // 2. Obtenir l'édition active
            $activeEdition = $this->getActiveEdition();

            if (!$activeEdition) {
                error_log("DEBUG: Aucune édition active trouvée");
                return [
                    'can_vote' => false,
                    'reason' => 'Aucune édition active',
                    'already_voted' => false
                ];
            }

            // 3. Vérifier si la catégorie est active (PÉRIODE DE VOTE)
            $isActive = $this->isCategoryInVotingPeriod($categoryInfo, $activeEdition);

            if (!$isActive) {
                error_log("DEBUG: Catégorie n'est pas dans la période de vote");
                return [
                    'can_vote' => false,
                    'reason' => 'Les votes ne sont pas ouverts pour cette catégorie',
                    'already_voted' => false
                ];
            }

            // 4. Vérifier si a déjà voté
            $hasVoted = $this->voteModel->hasUserVoted($userId, $categoryId);

            if ($hasVoted) {
                error_log("DEBUG: Utilisateur a déjà voté dans cette catégorie");
                return [
                    'can_vote' => false,
                    'reason' => 'Vous avez déjà voté dans cette catégorie',
                    'already_voted' => true
                ];
            }

            // 5. Vérifier s'il y a des nominations
            $nominations = $this->voteModel->getNominationsForCategory($categoryId);

            if (empty($nominations)) {
                error_log("DEBUG: Catégorie n'a pas de nominations");
                return [
                    'can_vote' => false,
                    'reason' => 'Aucune nomination disponible',
                    'already_voted' => false
                ];
            }

            error_log("DEBUG: Utilisateur PEUT voter!");
            return [
                'can_vote' => true,
                'reason' => 'Peut voter',
                'already_voted' => false
            ];
        } catch (Exception $e) {
            error_log("ERREUR dans canUserVoteSimple: " . $e->getMessage());
            return [
                'can_vote' => false,
                'reason' => 'Erreur technique: ' . $e->getMessage(),
                'already_voted' => false
            ];
        }
    }

    /**
     * Obtenir le statut de vote de l'utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Statut de vote par catégorie
     */
    public function getUserVotingStatus($userId)
    {
        try {
            error_log("=== DEBUG getUserVotingStatus ===");

            // Obtenir les catégories disponibles
            $categories = $this->getAvailableCategoriesForUser($userId);

            if (empty($categories)) {
                error_log("DEBUG: Aucune catégorie disponible pour le statut");
                return [];
            }

            $status = [];

            foreach ($categories as $category) {
                $categoryId = $category['id_categorie'];
                $hasVoted = $category['has_voted'] ?? false;
                $hasNominations = $category['has_nominations'] ?? false;

                $status[] = [
                    'category_id' => $categoryId,
                    'category_name' => $category['nom'],
                    'has_voted' => $hasVoted,
                    'is_active' => $category['is_active'] ?? true,
                    'nomination_count' => $category['nomination_count'] ?? 0,
                    'has_nominations' => $hasNominations,
                    'can_vote' => !$hasVoted && $hasNominations,
                    'vote_period' => $category['vote_start_formatted'] . ' - ' . $category['vote_end_formatted']
                ];

                error_log("Statut catégorie {$categoryId}: Voté={$hasVoted}, Nominations={$hasNominations}");
            }

            error_log("DEBUG: Statut retourné pour {$userId}: " . count($status) . " catégories");
            return $status;
        } catch (Exception $e) {
            error_log("ERREUR dans getUserVotingStatus: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Traiter le vote
     * 
     * @param string $token Token anonyme
     * @param int $nominationId ID de la nomination
     * @param int $userId ID de l'utilisateur
     * @return array Résultat du traitement du vote
     */
    public function processVote($token, $nominationId, $userId)
    {
        try {
            error_log("=== DEBUG processVote ===");
            error_log("Token: " . substr($token, 0, 20) . "...");
            error_log("Nomination: {$nominationId}, User: {$userId}");

            // 1. Vérifier si le token est toujours valide
            if (!$this->isTokenValid($token)) {
                error_log("DEBUG: Token invalide ou expiré");
                return [
                    'success' => false,
                    'message' => 'Token de vote invalide ou expiré'
                ];
            }

            // 2. Crypter le vote
            $encryptedVote = $this->voteModel->encryptVote($nominationId, $userId);
            error_log("DEBUG: Vote crypté: " . substr($encryptedVote, 0, 50) . "...");

            // 3. Enregistrer le vote via procédure stockée
            $voteId = $this->voteModel->castVote($token, $encryptedVote, $nominationId);

            if ($voteId) {
                error_log("DEBUG: Vote enregistré avec ID: {$voteId}");

                // 4. Essayer d'obtenir le certificat
                try {
                    $categoryId = $this->getCategoryIdFromToken($token);
                    if ($categoryId) {
                        $certificate = $this->voteModel->getParticipationCertificate($userId, $categoryId);
                        error_log("DEBUG: Certificat obtenu: " . ($certificate ? 'OUI' : 'NON'));
                    }
                } catch (Exception $e) {
                    error_log("AVERTISSEMENT: Impossible d'obtenir le certificat: " . $e->getMessage());
                    $certificate = null;
                }

                return [
                    'success' => true,
                    'vote_id' => $voteId,
                    'certificate' => $certificate,
                    'message' => 'Votre vote a été enregistré avec succès!'
                ];
            } else {
                error_log("ERREUR: Échec de l'enregistrement du vote");
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'enregistrement du vote'
                ];
            }
        } catch (Exception $e) {
            error_log("ERREUR CRITIQUE dans processVote: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur technique est survenue: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier si le token est valide
     * 
     * @param string $token Token à vérifier
     * @return bool True si le token est valide
     */
    private function isTokenValid($token)
    {
        try {
            $db = $this->voteModel->getDb();

            $stmt = $db->prepare("
                SELECT id_token FROM TOKEN_ANONYME 
                WHERE token_value = :token 
                AND est_utilise = FALSE 
                AND date_expiration > NOW()
            ");

            $stmt->execute([':token' => $token]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            error_log("ERREUR dans isTokenValid: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper: obtenir l'ID de la catégorie à partir du token
     * 
     * @param string $token Token anonyme
     * @return int|null ID de la catégorie ou null
     */
    private function getCategoryIdFromToken($token)
    {
        try {
            $stmt = $this->voteModel->getDb()->prepare("
                SELECT id_categorie FROM TOKEN_ANONYME 
                WHERE token_value = :token
            ");
            $stmt->execute([':token' => $token]);
            $result = $stmt->fetch();

            return $result ? $result['id_categorie'] : null;
        } catch (Exception $e) {
            error_log("ERREUR dans getCategoryIdFromToken: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifier le statut de vote en temps réel
     * 
     * @param int $userId ID de l'utilisateur
     * @param int|null $categoryId ID de la catégorie (optionnel)
     * @return array Statut de vote
     */
    public function checkVotingStatus($userId, $categoryId = null)
    {
        try {
            if ($categoryId) {
                $canVote = $this->canUserVoteSimple($userId, $categoryId);
                $voteModel = $this->voteModel;
                $hasVoted = $voteModel->hasUserVoted($userId, $categoryId);

                return [
                    'authenticated' => true,
                    'can_vote' => $canVote['can_vote'],
                    'has_voted' => $hasVoted,
                    'already_voted' => $canVote['already_voted'] ?? false,
                    'category_active' => true
                ];
            } else {
                $status = $this->getUserVotingStatus($userId);
                return [
                    'authenticated' => true,
                    'voting_status' => $status
                ];
            }
        } catch (Exception $e) {
            error_log("ERREUR dans checkVotingStatus: " . $e->getMessage());
            return ['authenticated' => false];
        }
    }
}
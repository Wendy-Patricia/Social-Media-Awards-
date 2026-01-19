<?php
/**
 * Contrôleur gérant le processus de vote des utilisateurs
 * - Affiche la page de vote
 * - Gère le démarrage du vote par catégorie
 * - Traite les votes soumis
 * - Génère les certificats de participation
 */
class VoteController {
    private $voteService; // Service de traitement des votes

    /**
     * Constructeur du contrôleur de vote
     * - Initialise le service de vote
     */
    public function __construct() {
        require_once __DIR__ . '/../Services/VoteService.php';
        $this->voteService = new VoteService();
    }

    /**
     * Affiche la page principale de vote
     * - Vérifie l'authentification de l'utilisateur
     * - Récupère le statut de vote de l'utilisateur
     * - Obtient les catégories disponibles pour l'utilisateur
     * 
     * @return array Données pour l'affichage de la page de vote
     */
    public function showVotingPage() {
        // Vérifier si une session est active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Rediriger si l'utilisateur n'est pas authentifié ou n'est pas un électeur
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
            header('Location: /Social-Media-Awards-/login.php');
            exit();
        }

        $userId = $_SESSION['user_id'];
        
        // Obtenir le statut de vote de l'utilisateur
        $votingStatus = $this->voteService->getUserVotingStatus($userId);
        
        // Obtenir les catégories disponibles pour l'utilisateur
        $availableCategories = $this->voteService->getAvailableCategoriesForUser($userId);
        
        return [
            'voting_status' => $votingStatus,
            'available_categories' => $availableCategories,
            'user' => [
                'id' => $userId,
                'pseudonyme' => $_SESSION['user_pseudonyme']
            ]
        ];
    }

    /**
     * Démarre le processus de vote pour une catégorie spécifique - VERSION CORRIGÉE
     * - Vérifie l'authentification et les permissions
     * - Nettoie la session de vote précédente
     * - Vérifie si l'utilisateur peut voter
     * - Initialise un nouveau processus de vote
     * - Stocke les informations dans la session
     * 
     * @return array Résultat de l'initialisation du vote
     */
    public function startCategoryVoting() {
        // Vérifier l'authentification
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
            return ['success' => false, 'message' => 'Non autorisé'];
        }

        // Vérifier que la méthode est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Méthode non autorisée'];
        }

        $userId = $_SESSION['user_id'];
        $categoryId = intval($_POST['category_id'] ?? 0);

        // Validation de l'ID de catégorie
        if ($categoryId <= 0) {
            return ['success' => false, 'message' => 'Catégorie invalide'];
        }
        
        // NETTOYER toute session de vote antérieure AVANT la vérification
        $this->clearVotingSession();
        
        // Vérifier si l'utilisateur peut voter (inclut la vérification de vote existant)
        $canVote = $this->voteService->canUserVoteSimple($userId, $categoryId);
        
        if (!$canVote['can_vote']) {
            return [
                'success' => false, 
                'message' => $canVote['reason'],
                'already_voted' => $canVote['already_voted'] ?? false
            ];
        }

        // Démarrer un nouveau processus de vote
        $result = $this->voteService->startVotingProcess($userId, $categoryId);
        
        if ($result['success']) {
            // Stocker le token et les informations dans la session avec horodatage
            $_SESSION['voting_token'] = $result['token'];
            $_SESSION['voting_category'] = $categoryId;
            $_SESSION['voting_category_name'] = $this->getCategoryName($categoryId);
            $_SESSION['voting_nominations'] = $result['nominations'];
            $_SESSION['voting_started'] = time();
            $_SESSION['voting_expires'] = time() + 3600; // 1 heure
            
            // Journalisation pour débogage
            error_log("DEBUG: Nouvelle session de vote démarrée pour catégorie {$categoryId}");
            error_log("DEBUG: Token: " . substr($result['token'], 0, 20) . "...");
        } else {
            error_log("DEBUG: Échec du démarrage du vote: " . $result['message']);
        }

        return $result;
    }

    /**
     * Traite um vote soumis - VERSION CORRIGÉE
     * - Vérifie l'authentification et les permissions
     * - Effectue plusieurs validations (vote existant, token, session, expiration)
     * - Traite le vote via le service
     * - Nettoie et met à jour la session après vote
     * 
     * @return array Résultat du traitement du vote
     */
    public function castVote() {
        // Vérifier l'authentification
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
            return ['success' => false, 'message' => 'Non autorisé'];
        }

        // Vérifier que la méthode est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Méthode non autorisée'];
        }

        $userId = $_SESSION['user_id'];
        $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $nominationId = intval($_POST['nomination_id'] ?? 0);

        // Validation des IDs
        if ($categoryId <= 0) {
            return ['success' => false, 'message' => 'Catégorie invalide'];
        }
        
        if ($nominationId <= 0) {
            return ['success' => false, 'message' => 'Sélection invalide'];
        }
        
        // PREMIÈRE VÉRIFICATION : L'utilisateur a-t-il déjà voté dans cette catégorie ?
        require_once __DIR__ . '/../Models/VoteModel.php';
        $voteModel = new Vote();
        $hasVoted = $voteModel->hasUserVoted($userId, $categoryId);
        
        if ($hasVoted) {
            $this->clearVotingSession();
            return [
                'success' => false, 
                'message' => 'Vous avez déjà voté dans cette catégorie',
                'already_voted' => true
            ];
        }
        
        // DEUXIÈME VÉRIFICATION : Token et session valides ?
        // Si la session n'a pas le token, recréer la session de vote
        if (!isset($_SESSION['voting_token']) || !isset($_SESSION['voting_category'])) {
            // Essayer de recréer la session de vote si le token est présent en POST
            if (isset($_POST['token']) && !empty($_POST['token'])) {
                // Recréer les variables de session à partir des données POST
                $_SESSION['voting_token'] = $_POST['token'];
                $_SESSION['voting_category'] = $categoryId;
                $_SESSION['voting_started'] = time();
                $_SESSION['voting_expires'] = time() + 3600;
                
                error_log("DEBUG: Session de vote recréée à partir des données POST");
            } else {
                $this->clearVotingSession();
                return [
                    'success' => false, 
                    'message' => 'Session de vote expirée ou invalide. Veuillez recommencer.',
                    'session_expired' => true
                ];
            }
        }
        
        // TROISIÈME VÉRIFICATION : Le token correspond-il à la catégorie ?
        if ($_SESSION['voting_category'] != $categoryId) {
            $this->clearVotingSession();
            return [
                'success' => false, 
                'message' => 'Token de vote invalide pour cette catégorie.',
                'token_mismatch' => true
            ];
        }
        
        // QUATRIÈME VÉRIFICATION : La session a-t-elle expiré ?
        if (isset($_SESSION['voting_expires']) && time() > $_SESSION['voting_expires']) {
            $this->clearVotingSession();
            return [
                'success' => false, 
                'message' => 'Session de vote expirée. Veuillez recommencer.',
                'session_expired' => true
            ];
        }

        $token = $_SESSION['voting_token'];

        // Traiter le vote via le service
        $result = $this->voteService->processVote($token, $nominationId, $userId);
        
        if ($result['success']) {
            // NETTOYER COMPLÈTEMENT la session de vote
            $this->clearVotingSession();
            
            // Nettoyer tout message précédent
            if (isset($_SESSION['vote_success'])) unset($_SESSION['vote_success']);
            if (isset($_SESSION['vote_message'])) unset($_SESSION['vote_message']);
            if (isset($_SESSION['last_vote_details'])) unset($_SESSION['last_vote_details']);
            
            // Stocker UNIQUEMENT le vote actuel
            $_SESSION['last_vote'] = [
                'vote_id' => $result['vote_id'],
                'category_id' => $categoryId,
                'category_name' => $_SESSION['voting_category_name'] ?? '',
                'certificate' => $result['certificate'] ?? null,
                'message' => $result['message'],
                'timestamp' => time(),
                'is_current' => true // Marquer comme vote actuel
            ];
            
            // Marquer qu'un vote a réussi
            $_SESSION['vote_success'] = true;
            $_SESSION['vote_message'] = $result['message'];
            
            error_log("DEBUG: Vote traité avec succès. ID: " . $result['vote_id']);
        } else {
            // En cas d'échec, nettoyer la session
            $this->clearVotingSession();
            error_log("DEBUG: Échec du traitement du vote: " . $result['message']);
        }

        return $result;
    }

    /**
     * Récupère le certificat de participation
     * - Vérifie l'authentification
     * - Cherche d'abord dans la session
     * - Puis dans la base de données
     * 
     * @return array Résultat avec le certificat ou message d'erreur
     */
    public function getCertificate() {
        // Vérifier l'authentification
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
            return ['success' => false, 'message' => 'Non autorisé'];
        }

        $userId = $_SESSION['user_id'];
        $categoryId = intval($_GET['category_id'] ?? 0);

        // Vérifier d'abord le dernier vote dans la session
        if (isset($_SESSION['last_vote']) && $_SESSION['last_vote']['is_current']) {
            return [
                'success' => true,
                'certificate' => $_SESSION['last_vote']['certificate']
            ];
        }

        // Sinon, chercher dans la base de données
        if ($categoryId > 0) {
            $voteModel = $this->voteService->getVoteModel();
            $certificate = $voteModel->getParticipationCertificate($userId, $categoryId);
            
            if ($certificate) {
                return [
                    'success' => true,
                    'certificate' => $certificate
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Aucun certificat trouvé'
        ];
    }

    /**
     * Vérifie le statut de vote en temps réel
     * - Vérifie l'authentification
     * - Appelle le service pour obtenir le statut
     * 
     * @return array Statut d'authentification et de vote
     */
    public function checkVotingStatus() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            return ['authenticated' => false];
        }

        $userId = $_SESSION['user_id'];
        $categoryId = intval($_GET['category_id'] ?? 0);

        return $this->voteService->checkVotingStatus($userId, $categoryId);
    }

    /**
     * Função utilitaire : obtient le nom d'une catégorie
     * 
     * @param int $categoryId ID de la catégorie
     * @return string Nom de la catégorie ou texte par défaut
     */
    private function getCategoryName($categoryId) {
        try {
            $voteModel = $this->voteService->getVoteModel();
            $category = $voteModel->getCategoryInfo($categoryId);
            return $category ? $category['nom'] : 'Catégorie inconnue';
        } catch (Exception $e) {
            error_log("Erreur getCategoryName: " . $e->getMessage());
            return 'Catégorie';
        }
    }
    
    /**
     * Nettoie la session de vote
     * - Supprime toutes les variables de session liées au vote
     * - Marque le dernier vote comme non actuel
     */
    private function clearVotingSession() {
        unset($_SESSION['voting_token']);
        unset($_SESSION['voting_category']);
        unset($_SESSION['voting_category_name']);
        unset($_SESSION['voting_nominations']);
        unset($_SESSION['voting_started']);
        unset($_SESSION['voting_expires']);
        
        // Marquer le dernier vote comme non actuel
        if (isset($_SESSION['last_vote'])) {
            $_SESSION['last_vote']['is_current'] = false;
        }
    }
}
?>
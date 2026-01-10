<?php
// app/Controllers/VoteController.php

class VoteController {
    private $voteService;

    public function __construct() {
        require_once __DIR__ . '/../Services/VoteService.php';
        $this->voteService = new VoteService();
    }

    /**
     * Página principal de votação
     */
    public function showVotingPage() {
        // Verificar autenticação
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
            header('Location: /Social-Media-Awards-/login.php');
            exit();
        }

        $userId = $_SESSION['user_id'];
        
        // Obter status de votação
        $votingStatus = $this->voteService->getUserVotingStatus($userId);
        
        // Obter categorias disponíveis
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
     * Iniciar votação em uma categoria específica
     */
    public function startCategoryVoting() {
        // Verificar autenticação
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
            return ['success' => false, 'message' => 'Non autorisé'];
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Méthode non autorisée'];
        }

        $userId = $_SESSION['user_id'];
        $categoryId = intval($_POST['category_id'] ?? 0);

        if ($categoryId <= 0) {
            return ['success' => false, 'message' => 'Catégorie invalide'];
        }

        // Iniciar processo de votação
        $result = $this->voteService->startVotingProcess($userId, $categoryId);
        
        if ($result['success']) {
            // Armazenar token na sessão
            $_SESSION['voting_token'] = $result['token'];
            $_SESSION['voting_category'] = $categoryId;
        }

        return $result;
    }

    /**
     * Processar voto
     */
    public function castVote() {
        // Verificar autenticação
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
            return ['success' => false, 'message' => 'Non autorisé'];
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'message' => 'Méthode non autorisée'];
        }

        // Verificar se tem token de votação ativo
        if (!isset($_SESSION['voting_token']) || !isset($_SESSION['voting_category'])) {
            return ['success' => false, 'message' => 'Session de vote invalide'];
        }

        $userId = $_SESSION['user_id'];
        $token = $_SESSION['voting_token'];
        $nominationId = intval($_POST['nomination_id'] ?? 0);

        if ($nominationId <= 0) {
            return ['success' => false, 'message' => 'Sélection invalide'];
        }

        // Processar voto
        $result = $this->voteService->processVote($token, $nominationId, $userId);
        
        if ($result['success']) {
            // Limpar sessão de votação
            unset($_SESSION['voting_token']);
            unset($_SESSION['voting_category']);
            
            // Armazenar certificado na sessão
            $_SESSION['last_vote_certificate'] = $result['certificate'];
        }

        return $result;
    }

    /**
     * Obter certificado de participação
     */
    public function getCertificate() {
        // Verificar autenticação
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voter') {
            return ['success' => false, 'message' => 'Non autorisé'];
        }

        $userId = $_SESSION['user_id'];
        $categoryId = intval($_GET['category_id'] ?? 0);

        if ($categoryId <= 0) {
            // Tentar obter da sessão
            if (isset($_SESSION['last_vote_certificate'])) {
                return [
                    'success' => true,
                    'certificate' => $_SESSION['last_vote_certificate']
                ];
            }
            return ['success' => false, 'message' => 'Catégorie invalide'];
        }

        $voteModel = $this->voteService->getVoteModel();
        $certificate = $voteModel->getParticipationCertificate($userId, $categoryId);
        
        if ($certificate) {
            return [
                'success' => true,
                'certificate' => $certificate
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Aucun certificat trouvé'
            ];
        }
    }

    /**
     * Verificar status de voto em tempo real
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

        if ($categoryId > 0) {
            $canVote = $this->voteService->canUserVote($userId, $categoryId);
            $voteModel = $this->voteService->getVoteModel();
            $hasVoted = $voteModel->hasUserVoted($userId, $categoryId);
            
            return [
                'authenticated' => true,
                'can_vote' => $canVote,
                'has_voted' => $hasVoted,
                'category_active' => $voteModel->isCategoryActive($categoryId)
            ];
        } else {
            $status = $this->voteService->getUserVotingStatus($userId);
            return [
                'authenticated' => true,
                'voting_status' => $status
            ];
        }
    }
}
?>
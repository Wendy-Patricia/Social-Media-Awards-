<?php
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
     * Iniciar votação em uma categoria específica - VERSÃO CORRIGIDA
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
        
        // LIMPAR qualquer sessão de votação anterior ANTES de verificar
        $this->clearVotingSession();
        
        // Verificar se pode votar (inclui verificação de já votou)
        $canVote = $this->voteService->canUserVoteSimple($userId, $categoryId);
        
        if (!$canVote['can_vote']) {
            return [
                'success' => false, 
                'message' => $canVote['reason'],
                'already_voted' => $canVote['already_voted'] ?? false
            ];
        }

        // Iniciar novo processo de votação
        $result = $this->voteService->startVotingProcess($userId, $categoryId);
        
        if ($result['success']) {
            // Armazenar token e informações na sessão com timestamp
            $_SESSION['voting_token'] = $result['token'];
            $_SESSION['voting_category'] = $categoryId;
            $_SESSION['voting_category_name'] = $this->getCategoryName($categoryId);
            $_SESSION['voting_nominations'] = $result['nominations'];
            $_SESSION['voting_started'] = time();
            $_SESSION['voting_expires'] = time() + 3600; // 1 hora
            
            error_log("DEBUG: Nova sessão de votação iniciada para categoria {$categoryId}");
            error_log("DEBUG: Token: " . substr($result['token'], 0, 20) . "...");
        } else {
            error_log("DEBUG: Falha ao iniciar votação: " . $result['message']);
        }

        return $result;
    }

    /**
     * Processar voto - VERSÃO CORRIGIDA
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

        $userId = $_SESSION['user_id'];
        $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $nominationId = intval($_POST['nomination_id'] ?? 0);

        if ($categoryId <= 0) {
            return ['success' => false, 'message' => 'Catégorie invalide'];
        }
        
        if ($nominationId <= 0) {
            return ['success' => false, 'message' => 'Sélection invalide'];
        }
        
        // PRIMEIRA VERIFICAÇÃO: Já votou nesta categoria?
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
        
        // SEGUNDA VERIFICAÇÃO: Token e sessão válidos?
        if (!isset($_SESSION['voting_token']) || !isset($_SESSION['voting_category'])) {
            $this->clearVotingSession();
            return [
                'success' => false, 
                'message' => 'Session de vote expirée ou invalide. Veuillez recommencer.',
                'session_expired' => true
            ];
        }
        
        // TERCEIRA VERIFICAÇÃO: Token corresponde à categoria?
        if ($_SESSION['voting_category'] != $categoryId) {
            $this->clearVotingSession();
            return [
                'success' => false, 
                'message' => 'Token de vote invalide pour cette catégorie.',
                'token_mismatch' => true
            ];
        }
        
        // QUARTA VERIFICAÇÃO: Sessão expirou?
        if (isset($_SESSION['voting_expires']) && time() > $_SESSION['voting_expires']) {
            $this->clearVotingSession();
            return [
                'success' => false, 
                'message' => 'Session de vote expirée. Veuillez recommencer.',
                'session_expired' => true
            ];
        }

        $token = $_SESSION['voting_token'];

        // Processar voto
        $result = $this->voteService->processVote($token, $nominationId, $userId);
        
        if ($result['success']) {
            // LIMPAR COMPLETAMENTE a sessão de votação
            $this->clearVotingSession();
            
            // Limpar qualquer mensagem anterior
            if (isset($_SESSION['vote_success'])) unset($_SESSION['vote_success']);
            if (isset($_SESSION['vote_message'])) unset($_SESSION['vote_message']);
            if (isset($_SESSION['last_vote_details'])) unset($_SESSION['last_vote_details']);
            
            // Armazenar APENAS o voto atual
            $_SESSION['last_vote'] = [
                'vote_id' => $result['vote_id'],
                'category_id' => $categoryId,
                'category_name' => $_SESSION['voting_category_name'] ?? '',
                'certificate' => $result['certificate'] ?? null,
                'message' => $result['message'],
                'timestamp' => time(),
                'is_current' => true // Marcar como voto atual
            ];
            
            // Marcar que houve um voto bem-sucedido
            $_SESSION['vote_success'] = true;
            $_SESSION['vote_message'] = $result['message'];
            
            error_log("DEBUG: Voto processado com sucesso. ID: " . $result['vote_id']);
        } else {
            // Se falhou, limpar a sessão
            $this->clearVotingSession();
            error_log("DEBUG: Falha ao processar voto: " . $result['message']);
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

        // Primeiro verificar último voto na sessão
        if (isset($_SESSION['last_vote']) && $_SESSION['last_vote']['is_current']) {
            return [
                'success' => true,
                'certificate' => $_SESSION['last_vote']['certificate']
            ];
        }

        // Se não, buscar no banco
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

        return $this->voteService->checkVotingStatus($userId, $categoryId);
    }

    /**
     * Helper: obter nome da categoria
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
     * Limpar sessão de votação
     */
    private function clearVotingSession() {
        unset($_SESSION['voting_token']);
        unset($_SESSION['voting_category']);
        unset($_SESSION['voting_category_name']);
        unset($_SESSION['voting_nominations']);
        unset($_SESSION['voting_started']);
        unset($_SESSION['voting_expires']);
        
        // Marcar último voto como não atual
        if (isset($_SESSION['last_vote'])) {
            $_SESSION['last_vote']['is_current'] = false;
        }
    }
}
?>
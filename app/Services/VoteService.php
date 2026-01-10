<?php
// app/Services/VoteService.php

class VoteService {
    private $voteModel;
    private $userModel;

    public function __construct() {
        require_once __DIR__ . '/../Models/Vote.php';
        require_once __DIR__ . '/../Models/User.php';
        
        $this->voteModel = new Vote();
        $this->userModel = new User();
    }

    // Método getter para acessar o voteModel
    public function getVoteModel() {
        return $this->voteModel;
    }

    /**
     * Iniciar processo de votação para uma categoria
     */
    public function startVotingProcess($userId, $categoryId) {
        // 1. Verificar se usuário pode votar
        if (!$this->canUserVote($userId, $categoryId)) {
            return [
                'success' => false,
                'message' => 'Vous ne pouvez pas voter dans cette catégorie'
            ];
        }

        // 2. Gerar token anônimo
        $token = $this->voteModel->generateToken($userId, $categoryId);
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération du token'
            ];
        }

        // 3. Obter nomeações da categoria
        $nominations = $this->voteModel->getNominationsForCategory($categoryId);
        
        if (empty($nominations)) {
            return [
                'success' => false,
                'message' => 'Aucune nomination disponible pour cette catégorie'
            ];
        }

        return [
            'success' => true,
            'token' => $token,
            'nominations' => $nominations,
            'category_id' => $categoryId
        ];
    }

    /**
     * Processar voto
     */
    public function processVote($token, $nominationId, $userId) {
        try {
            // 1. Criptografar voto
            $encryptedVote = $this->voteModel->encryptVote($nominationId, $userId);
            
            // 2. Registrar voto via stored procedure
            $voteId = $this->voteModel->castVote($token, $encryptedVote, $nominationId);
            
            if ($voteId) {
                // 3. Obter certificado de participação
                $categoryId = $this->getCategoryIdFromToken($token);
                $certificate = $this->voteModel->getParticipationCertificate($userId, $categoryId);
                
                return [
                    'success' => true,
                    'vote_id' => $voteId,
                    'certificate' => $certificate,
                    'message' => 'Votre vote a été enregistré avec succès!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'enregistrement du vote'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erreur traitement vote: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur technique est survenue'
            ];
        }
    }

    /**
     * Verificar se usuário pode votar
     */
    public function canUserVote($userId, $categoryId) {
        // 1. Verificar se categoria está ativa
        if (!$this->voteModel->isCategoryActive($categoryId)) {
            return false;
        }

        // 2. Verificar se usuário já votou
        if ($this->voteModel->hasUserVoted($userId, $categoryId)) {
            return false;
        }

        return true;
    }

    /**
     * Obter status de votação do usuário
     */
    public function getUserVotingStatus($userId) {
        $categories = $this->voteModel->getVotingCategories();
        $history = $this->voteModel->getUserVotingHistory($userId);
        
        $status = [];
        
        foreach ($categories as $category) {
            $hasVoted = false;
            $voteDate = null;
            
            foreach ($history as $record) {
                if ($record['category_name'] == $category['nom'] && $record['has_voted']) {
                    $hasVoted = true;
                    $voteDate = $record['vote_date'];
                    break;
                }
            }
            
            $status[] = [
                'category_id' => $category['id_categorie'],
                'category_name' => $category['nom'],
                'has_voted' => $hasVoted,
                'vote_date' => $voteDate,
                'is_active' => $this->voteModel->isCategoryActive($category['id_categorie']),
                'nomination_count' => $category['nomination_count']
            ];
        }
        
        return $status;
    }

    /**
     * Obter categorias disponíveis para o usuário
     */
    public function getAvailableCategoriesForUser($userId) {
        $categories = $this->voteModel->getVotingCategories();
        $available = [];
        
        foreach ($categories as $category) {
            if ($this->canUserVote($userId, $category['id_categorie'])) {
                $available[] = $category;
            }
        }
        
        return $available;
    }

    /**
     * Helper: obter ID da categoria a partir do token
     */
    private function getCategoryIdFromToken($token) {
        // Em produção, buscar no banco de dados
        // Aqui simplificamos para demonstração
        try {
            $stmt = $this->voteModel->getDb()->prepare("
                SELECT id_categorie FROM TOKEN_ANONYME 
                WHERE token_value = :token
            ");
            $stmt->execute([':token' => $token]);
            $result = $stmt->fetch();
            
            return $result ? $result['id_categorie'] : null;
            
        } catch (Exception $e) {
            error_log("Erreur récupération catégorie: " . $e->getMessage());
            return null;
        }
    }
}
?>
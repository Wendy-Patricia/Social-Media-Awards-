<?php
// app/Services/VoteService.php - VERSÃO CORRIGIDA

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
        $canVoteResult = $this->canUserVote($userId, $categoryId);
        
        if (!$canVoteResult['can_vote']) {
            $reason = $canVoteResult['reason'] ?? 'Impossible de voter dans cette catégorie';
            return [
                'success' => false,
                'message' => $reason
            ];
        }

        // 2. Verificar se há nominações
        $nominations = $this->voteModel->getNominationsForCategory($categoryId);
        
        if (empty($nominations)) {
            return [
                'success' => false,
                'message' => 'Aucune nomination disponible pour cette catégorie pour le moment'
            ];
        }

        // 3. Gerar token anônimo
        $token = $this->voteModel->generateToken($userId, $categoryId);
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération du token'
            ];
        }

        return [
            'success' => true,
            'token' => $token,
            'nominations' => $nominations,
            'category_id' => $categoryId,
            'nomination_count' => count($nominations)
        ];
    }

    /**
     * Obter categorias disponíveis para o usuário
     * MODIFICADO: Mostra categorias mesmo sem nominações
     */
    public function getAvailableCategoriesForUser($userId) {
        try {
            $now = date('Y-m-d H:i:s');
            
            // Buscar todas as categorias ativas
            $allCategories = $this->voteModel->getVotingCategories();
            
            error_log("DEBUG getAvailableCategoriesForUser - Todas as categorias: " . json_encode($allCategories));
            
            $available = [];
            
            foreach ($allCategories as $category) {
                $categoryId = $category['id_categorie'] ?? 0;
                
                if ($categoryId <= 0) {
                    continue;
                }
                
                // Verificar se usuário pode votar (considerando apenas datas e se já votou)
                $voteCheck = $this->voteModel->canVoteInCategory($userId, $categoryId);
                
                error_log("DEBUG Categoria {$categoryId} ({$category['nom']}) - Resultado: " . json_encode($voteCheck));
                
                // MODIFICAÇÃO AQUI: Mostrar categoria mesmo sem nominações
                if ($voteCheck['can_vote'] || (!$voteCheck['has_voted'] && $voteCheck['voting_open'])) {
                    // Verificar número de nominações
                    $nominations = $this->voteModel->getNominationsForCategory($categoryId);
                    $category['nomination_count'] = count($nominations);
                    $category['has_nominations'] = count($nominations) > 0;
                    $category['can_vote'] = $voteCheck['can_vote'];
                    $category['voting_open'] = $voteCheck['voting_open'];
                    $category['has_voted'] = $voteCheck['has_voted'];
                    
                    $available[] = $category;
                }
            }
            
            error_log("DEBUG getAvailableCategoriesForUser - Disponível para usuário {$userId}: " . count($available));
            
            return $available;
            
        } catch (Exception $e) {
            error_log("Erreur getAvailableCategoriesForUser: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar se usuário pode votar
     */
    public function canUserVote($userId, $categoryId) {
        // Usar a função específica do modelo
        $result = $this->voteModel->canVoteInCategory($userId, $categoryId);
        
        error_log("DEBUG canUserVote - User: {$userId}, Category: {$categoryId}, Result: " . json_encode($result));
        
        return $result;
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
                if (isset($record['category_name']) && $record['category_name'] == $category['nom'] && isset($record['has_voted']) && $record['has_voted']) {
                    $hasVoted = true;
                    $voteDate = $record['vote_date'] ?? null;
                    break;
                }
            }
            
            // Verificar nominações
            $nominations = $this->voteModel->getNominationsForCategory($category['id_categorie']);
            $nominationCount = count($nominations);
            
            // Verificar se categoria está ativa
            $isActive = $this->voteModel->isCategoryActive($category['id_categorie']);
            
            $status[] = [
                'category_id' => $category['id_categorie'],
                'category_name' => $category['nom'],
                'has_voted' => $hasVoted,
                'vote_date' => $voteDate,
                'is_active' => $isActive,
                'nomination_count' => $nominationCount,
                'has_nominations' => $nominationCount > 0
            ];
        }
        
        return $status;
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
                'message' => 'Une erreur technique est survenue: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Helper: obter ID da categoria a partir do token
     */
    private function getCategoryIdFromToken($token) {
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
<?php
// app/Services/VoteService.php - VERSÃO COMPLETA CORRIGIDA

class VoteService {
    private $voteModel;
    private $userModel;

    public function __construct() {
        require_once __DIR__ . '/../Models/VoteModel.php';
        require_once __DIR__ . '/../Models/UserModel.php';
        
        $this->voteModel = new Vote();
        $this->userModel = new User();
    }

    // Método getter para acessar o voteModel
    public function getVoteModel() {
        return $this->voteModel;
    }

    /**
     * Obter categorias disponíveis para o usuário
     * VERSÃO CORRIGIDA - Mostra todas as categorias ativas
     */
    public function getAvailableCategoriesForUser($userId) {
        try {
            error_log("=== DEBUG getAvailableCategoriesForUser ===");
            error_log("Usuário ID: " . $userId);
            
            // 1. Obter edição ativa
            $activeEdition = $this->getActiveEdition();
            
            if (!$activeEdition) {
                error_log("DEBUG: Nenhuma edição ativa encontrada!");
                return [];
            }
            
            error_log("DEBUG: Edição ativa ID: " . $activeEdition['id_edition']);
            
            // 2. Obter TODAS as categorias da edição ativa
            $categories = $this->getCategoriesForEdition($activeEdition['id_edition']);
            
            if (empty($categories)) {
                error_log("DEBUG: Nenhuma categoria encontrada para a edição!");
                return [];
            }
            
            error_log("DEBUG: Total categorias da edição: " . count($categories));
            
            $available = [];
            $now = date('Y-m-d H:i:s');
            
            foreach ($categories as $category) {
                $categoryId = $category['id_categorie'] ?? 0;
                
                if ($categoryId <= 0) {
                    continue;
                }
                
                error_log("--- Processando categoria ID: {$categoryId} ---");
                
                // 3. Verificar se está no período de votação
                $isActive = $this->isCategoryInVotingPeriod($category, $activeEdition);
                
                if (!$isActive) {
                    error_log("Categoria {$categoryId} não está ativa (fora do período)");
                    continue;
                }
                
                // 4. Contar nominações
                $nominations = $this->voteModel->getNominationsForCategory($categoryId);
                $nominationCount = count($nominations);
                
                // 5. Verificar se usuário já votou
                $hasVoted = $this->voteModel->hasUserVoted($userId, $categoryId);
                
                // 6. Determinar se pode votar agora
                $canVoteNow = !$hasVoted && $nominationCount > 0;
                
                // 7. Adicionar informações
                $category['nomination_count'] = $nominationCount;
                $category['has_voted'] = $hasVoted;
                $category['is_active'] = true;
                $category['can_vote'] = $canVoteNow;
                $category['has_nominations'] = $nominationCount > 0;
                $category['nominations'] = $nominations;
                
                // 8. Adicionar datas formatadas para display
                if ($category['date_debut_votes'] && $category['date_fin_votes']) {
                    $category['vote_start_formatted'] = date('d/m/Y', strtotime($category['date_debut_votes']));
                    $category['vote_end_formatted'] = date('d/m/Y', strtotime($category['date_fin_votes']));
                } else {
                    $category['vote_start_formatted'] = date('d/m/Y', strtotime($activeEdition['date_debut']));
                    $category['vote_end_formatted'] = date('d/m/Y', strtotime($activeEdition['date_fin']));
                }
                
                $available[] = $category;
                
                error_log("✓ Categoria {$categoryId} '{$category['nom']}' adicionada");
                error_log("  - Nomeações: {$nominationCount}");
                error_log("  - Já votou: " . ($hasVoted ? 'SIM' : 'NÃO'));
                error_log("  - Pode votar agora: " . ($canVoteNow ? 'SIM' : 'NÃO'));
                error_log("  - Tem nominações: " . ($nominationCount > 0 ? 'SIM' : 'NÃO'));
            }
            
            error_log("=== FIM getAvailableCategoriesForUser ===");
            error_log("Categorias disponíveis: " . count($available));
            
            return $available;
            
        } catch (Exception $e) {
            error_log("ERRO em getAvailableCategoriesForUser: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter edição ativa
     */
    private function getActiveEdition() {
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
            error_log("ERRO em getActiveEdition: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obter categorias para uma edição
     */
    private function getCategoriesForEdition($editionId) {
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
            error_log("ERRO em getCategoriesForEdition: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verificar se categoria está no período de votação
     */
    private function isCategoryInVotingPeriod($category, $edition) {
        $now = date('Y-m-d H:i:s');
        
        // Se a categoria tem datas específicas, usar elas
        if ($category['date_debut_votes'] && $category['date_fin_votes']) {
            $start = $category['date_debut_votes'];
            $end = $category['date_fin_votes'];
            return ($now >= $start && $now <= $end);
        }
        
        // Se não, usar datas da edição
        if ($edition['date_debut'] && $edition['date_fin']) {
            $start = $edition['date_debut'];
            $end = $edition['date_fin'];
            return ($now >= $start && $now <= $end);
        }
        
        return false;
    }

    /**
     * Iniciar processo de votação para uma categoria
     */
    public function startVotingProcess($userId, $categoryId) {
        error_log("=== DEBUG startVotingProcess ===");
        error_log("Usuário: {$userId}, Categoria: {$categoryId}");
        
        // 1. Verificar se pode votar
        $canVote = $this->canUserVoteSimple($userId, $categoryId);
        
        if (!$canVote['can_vote']) {
            error_log("DEBUG: Usuário NÃO pode votar. Razão: " . $canVote['reason']);
            return [
                'success' => false,
                'message' => $canVote['reason'],
                'already_voted' => $canVote['already_voted'] ?? false
            ];
        }
        
        error_log("DEBUG: Usuário PODE votar");

        // 2. Verificar se há nominações
        $nominations = $this->voteModel->getNominationsForCategory($categoryId);
        
        if (empty($nominations)) {
            error_log("DEBUG: Nenhuma nomeação encontrada para categoria {$categoryId}");
            return [
                'success' => false,
                'message' => 'Aucune nomination disponible pour cette catégorie'
            ];
        }
        
        error_log("DEBUG: Encontradas " . count($nominations) . " nomeações");

        // 3. Gerar token anônimo
        $token = $this->voteModel->generateToken($userId, $categoryId);
        
        if (!$token) {
            error_log("DEBUG: Falha ao gerar token");
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération du token'
            ];
        }
        
        error_log("DEBUG: Token gerado com sucesso: " . substr($token, 0, 20) . "...");

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
     * Verificação SIMPLIFICADA se usuário pode votar
     */
    public function canUserVoteSimple($userId, $categoryId) {
        try {
            error_log("=== DEBUG canUserVoteSimple ===");
            error_log("Usuário: {$userId}, Categoria: {$categoryId}");
            
            // 1. Obter informações da categoria
            $categoryInfo = $this->voteModel->getCategoryInfo($categoryId);
            
            if (!$categoryInfo) {
                error_log("DEBUG: Categoria não encontrada");
                return [
                    'can_vote' => false,
                    'reason' => 'Catégorie non trouvée',
                    'already_voted' => false
                ];
            }
            
            // 2. Obter edição ativa
            $activeEdition = $this->getActiveEdition();
            
            if (!$activeEdition) {
                error_log("DEBUG: Nenhuma edição ativa encontrada");
                return [
                    'can_vote' => false,
                    'reason' => 'Aucune édition active',
                    'already_voted' => false
                ];
            }
            
            // 3. Verificar se categoria está ativa (PERÍODO DE VOTAÇÃO)
            $isActive = $this->isCategoryInVotingPeriod($categoryInfo, $activeEdition);
            
            if (!$isActive) {
                error_log("DEBUG: Categoria não está no período de votação");
                return [
                    'can_vote' => false,
                    'reason' => 'Les votes ne sont pas ouverts pour cette catégorie',
                    'already_voted' => false
                ];
            }
            
            // 4. Verificar se já votou
            $hasVoted = $this->voteModel->hasUserVoted($userId, $categoryId);
            
            if ($hasVoted) {
                error_log("DEBUG: Usuário já votou nesta categoria");
                return [
                    'can_vote' => false,
                    'reason' => 'Vous avez déjà voté dans cette catégorie',
                    'already_voted' => true
                ];
            }
            
            // 5. Verificar se tem nominações
            $nominations = $this->voteModel->getNominationsForCategory($categoryId);
            
            if (empty($nominations)) {
                error_log("DEBUG: Categoria não tem nominações");
                return [
                    'can_vote' => false,
                    'reason' => 'Aucune nomination disponible',
                    'already_voted' => false
                ];
            }
            
            error_log("DEBUG: Usuário PODE votar!");
            return [
                'can_vote' => true,
                'reason' => 'Peut voter',
                'already_voted' => false
            ];
            
        } catch (Exception $e) {
            error_log("ERRO em canUserVoteSimple: " . $e->getMessage());
            return [
                'can_vote' => false,
                'reason' => 'Erreur technique: ' . $e->getMessage(),
                'already_voted' => false
            ];
        }
    }

    /**
     * Obter status de votação do usuário
     */
    public function getUserVotingStatus($userId) {
        try {
            error_log("=== DEBUG getUserVotingStatus ===");
            
            // Obter categorias disponíveis
            $categories = $this->getAvailableCategoriesForUser($userId);
            
            if (empty($categories)) {
                error_log("DEBUG: Nenhuma categoria disponível para status");
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
                
                error_log("Status categoria {$categoryId}: Votou={$hasVoted}, Nomeações={$hasNominations}");
            }
            
            error_log("DEBUG: Status retornado para {$userId}: " . count($status) . " categorias");
            return $status;
            
        } catch (Exception $e) {
            error_log("ERRO em getUserVotingStatus: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Processar voto
     */
    public function processVote($token, $nominationId, $userId) {
        try {
            error_log("=== DEBUG processVote ===");
            error_log("Token: " . substr($token, 0, 20) . "...");
            error_log("Nomination: {$nominationId}, User: {$userId}");
            
            // 1. Verificar se token ainda é válido
            if (!$this->isTokenValid($token)) {
                error_log("DEBUG: Token inválido ou expirado");
                return [
                    'success' => false,
                    'message' => 'Token de vote invalide ou expiré'
                ];
            }
            
            // 2. Criptografar voto
            $encryptedVote = $this->voteModel->encryptVote($nominationId, $userId);
            error_log("DEBUG: Voto criptografado: " . substr($encryptedVote, 0, 50) . "...");
            
            // 3. Registrar voto via stored procedure
            $voteId = $this->voteModel->castVote($token, $encryptedVote, $nominationId);
            
            if ($voteId) {
                error_log("DEBUG: Voto registrado com ID: {$voteId}");
                
                // 4. Tentar obter certificado
                try {
                    $categoryId = $this->getCategoryIdFromToken($token);
                    if ($categoryId) {
                        $certificate = $this->voteModel->getParticipationCertificate($userId, $categoryId);
                        error_log("DEBUG: Certificado obtido: " . ($certificate ? 'SIM' : 'NÃO'));
                    }
                } catch (Exception $e) {
                    error_log("AVISO: Não foi possível obter certificado: " . $e->getMessage());
                    $certificate = null;
                }
                
                return [
                    'success' => true,
                    'vote_id' => $voteId,
                    'certificate' => $certificate,
                    'message' => 'Votre vote a été enregistré avec succès!'
                ];
            } else {
                error_log("ERRO: Falha ao registrar voto");
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'enregistrement du vote'
                ];
            }
            
        } catch (Exception $e) {
            error_log("ERRO CRÍTICO em processVote: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur technique est survenue: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar se token é válido
     */
    private function isTokenValid($token) {
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
            error_log("ERRO em isTokenValid: " . $e->getMessage());
            return false;
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
            error_log("ERRO em getCategoryIdFromToken: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verificar status de voto em tempo real
     */
    public function checkVotingStatus($userId, $categoryId = null) {
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
            error_log("ERRO em checkVotingStatus: " . $e->getMessage());
            return ['authenticated' => false];
        }
    }
}
?>
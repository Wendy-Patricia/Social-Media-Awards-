<?php
// config/permissions.php

require_once __DIR__ . '/session.php';

function isLoggedIn(): bool {
    return isAuthenticated();
}

function requireLogin() {
    requireAuth(); // Reutiliza a função existente
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        http_response_code(403);
        die('Accès refusé. Vous devez être administrateur.');
    }
}

/**
 * Verificar se usuário pode votar em uma categoria
 */
function canUserVote($userId, $categoryId) {
    require_once __DIR__ . '/../app/Services/VoteService.php';
    $voteService = new VoteService();
    return $voteService->canUserVote($userId, $categoryId);
}

/**
 * Verificar se categoria está ativa para votação
 */
function isCategoryActive($categoryId) {
    require_once __DIR__ . '/../app/Models/Vote.php';
    $voteModel = new Vote();
    return $voteModel->isCategoryActive($categoryId);
}

/**
 * Redirecionar para página de votação se não puder votar
 */
function requireVotingPermission($userId, $categoryId) {
    if (!canUserVote($userId, $categoryId)) {
        $_SESSION['error'] = 'Vous ne pouvez pas voter dans cette catégorie';
        header('Location: /Social-Media-Awards-/views/user/vote.php');
        exit();
    }
}

/**
 * Verificar token de votação
 */
function validateVotingToken($token, $userId, $categoryId) {
    require_once __DIR__ . '/../app/Models/Vote.php';
    $voteModel = new Vote();
    
    try {
        // Verificar se token é válido
        $stmt = $voteModel->getDb()->prepare("
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
        
    } catch (PDOException $e) {
        error_log("Erreur validation token: " . $e->getMessage());
        return false;
    }
}
?>
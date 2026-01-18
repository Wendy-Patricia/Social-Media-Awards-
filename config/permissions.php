<?php
// config/permissions.php

require_once __DIR__ . '/session.php';

/**
 * Vérifie si l'utilisateur est actuellement connecté
 * 
 * @return bool True si l'utilisateur est connecté, sinon false
 */
function isLoggedIn(): bool
{
    return isAuthenticated(); // Réutilise la fonction existante
}

/**
 * Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
 */
function requireLogin()
{
    requireAuth(); // Réutilise la fonction existante
}

/**
 * Exige que l'utilisateur soit administrateur
 * - Vérifie d'abord l'authentification
 * - Vérifie ensuite le rôle administrateur
 * - Redirige ou affiche une erreur 403 si non autorisé
 */
function requireAdmin()
{
    requireAuth();
    if (!isAdmin()) {
        http_response_code(403);
        die('Accès refusé. Vous devez être administrateur.');
    }
}

/**
 * Vérifie si un utilisateur peut voter dans une catégorie spécifique
 * 
 * @param int $userId ID de l'utilisateur
 * @param int $categoryId ID de la catégorie
 * @return bool True si l'utilisateur peut voter, sinon false
 */
function canUserVote($userId, $categoryId)
{
    require_once __DIR__ . '/../app/Services/VoteService.php';
    $voteService = new VoteService();
    return $voteService->canUserVote($userId, $categoryId);
}

/**
 * Vérifie si une catégorie est active pour le vote
 * 
 * @param int $categoryId ID de la catégorie
 * @return bool True si la catégorie est active, sinon false
 */
function isCategoryActive($categoryId)
{
    require_once __DIR__ . '/../app/Models/Vote.php';
    $voteModel = new Vote();
    return $voteModel->isCategoryActive($categoryId);
}

/**
 * Vérifie les permissions de vote et redirige si nécessaire
 * 
 * @param int $userId ID de l'utilisateur
 * @param int $categoryId ID de la catégorie
 */
function requireVotingPermission($userId, $categoryId)
{
    if (!canUserVote($userId, $categoryId)) {
        $_SESSION['error'] = 'Vous ne pouvez pas voter dans cette catégorie';
        header('Location: /Social-Media-Awards-/views/user/vote.php');
        exit();
    }
}

/**
 * Valide un token de vote anonyme
 * 
 * @param string $token Valeur du token
 * @param int $userId ID de l'utilisateur
 * @param int $categoryId ID de la catégorie
 * @return bool True si le token est valide, sinon false
 */
function validateVotingToken($token, $userId, $categoryId)
{
    require_once __DIR__ . '/../app/Models/Vote.php';
    $voteModel = new Vote();
    
    try {
        // Vérifie si le token existe, n'est pas utilisé et n'est pas expiré
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
<?php
// config/session.php

require_once __DIR__ . '/database.php';

/**
 * Gestion centralisée des sessions utilisateur
 * Fournit des fonctions pour vérifier l'authentification et les rôles
 */

// Démarre la session uniquement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie si l'utilisateur est authentifié
 * 
 * @return bool True si l'utilisateur est connecté, sinon false
 */
function isAuthenticated()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Vérifie si l'utilisateur a le rôle administrateur
 * 
 * @return bool True si l'utilisateur est admin, sinon false
 */
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Récupère le type/role de l'utilisateur connecté
 * 
 * @return string|null Role de l'utilisateur ou null si non connecté
 */
function getUserType()
{
    return $_SESSION['user_role'] ?? null;
}

/**
 * Récupère l'ID de l'utilisateur connecté
 * 
 * @return int|null ID de l'utilisateur ou null si non connecté
 */
function getUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Récupère l'email de l'utilisateur connecté
 * 
 * @return string|null Email de l'utilisateur ou null si non connecté
 */
function getUserEmail()
{
    return $_SESSION['user_email'] ?? null;
}

/**
 * Récupère le pseudonyme de l'utilisateur connecté
 * 
 * @return string|null Pseudonyme de l'utilisateur ou null si non connecté
 */
function getUserPseudo()
{
    return $_SESSION['user_pseudonyme'] ?? null;
}

/**
 * Exige une authentification pour accéder à une page
 * Redirige vers la page de connexion si l'utilisateur n'est pas connecté
 */
function requireAuth()
{
    if (!isAuthenticated()) {
        $loginPath = '/Social-Media-Awards-/views/login.php';
        header("Location: $loginPath");
        exit();
    }
}

/**
 * Exige un rôle spécifique pour accéder à une page
 * - Vérifie d'abord l'authentification
 * - Redirige vers le dashboard approprié si le rôle ne correspond pas
 * 
 * @param string $role Rôle requis (admin, candidate, voter)
 */
function requireRole($role)
{
    requireAuth();
    
    if (getUserType() !== $role) {
        // Redirection vers le dashboard selon le rôle actuel
        $redirect = match(getUserType()) {
            'admin' => '/Social-Media-Awards-/views/admin/dashboard.php',
            'candidate' => '/Social-Media-Awards-/views/candidate/candidate-dashboard.php',
            'voter' => '/Social-Media-Awards-/views/user/user-dashboard.php',
            default => '/Social-Media-Awards-/index.php'
        };
        
        header("Location: $redirect");
        exit();
    }
}
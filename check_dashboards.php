<?php
// check_dashboards.php

/**
 * Contrôleur de redirection vers les tableaux de bord
 * - Vérifie l'état de connexion de l'utilisateur
 * - Détermine le rôle et redirige vers le dashboard approprié
 * - Gère les fallbacks en cas de fichiers manquants
 */
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: /Social-Media-Awards/views/login.php');
    exit;
}

// Détermination du rôle utilisateur
$role = $_SESSION['user_role'] ?? 'guest';

/**
 * Redirection selon le rôle avec gestion des fallbacks
 */
switch ($role) {
    case 'admin':
        // Vérification de l'existence du dashboard administrateur
        if (file_exists(__DIR__ . '/views/admin/dashboard.php')) {
            header('Location: /Social-Media-Awards/views/admin/dashboard.php');
        } else {
            // Fallback vers l'ancien nom de fichier
            header('Location: /Social-Media-Awards/views/admin/admin-dashboard.php');
        }
        break;
        
    case 'candidate':
        // Vérification de l'existence du dashboard candidat
        if (file_exists(__DIR__ . '/views/candidate/candidate-dashboard.php')) {
            header('Location: /Social-Media-Awards/views/candidate/candidate-dashboard.php');
        } else {
            // Affichage d'un message temporaire si le dashboard n'existe pas
            echo "<h1>Tableau de bord Candidat</h1>";
            echo "<p>Le dashboard n'est pas encore configuré.</p>";
            echo "<p><a href='/Social-Media-Awards/logout.php'>Déconnexion</a></p>";
        }
        break;
        
    case 'voter':
        // Vérification de l'existence du dashboard électeur
        if (file_exists(__DIR__ . '/views/user/user-dashboard.php')) {
            header('Location: /Social-Media-Awards/views/user/user-dashboard.php');
        } else {
            // Redirection vers la page d'accueil par défaut
            header('Location: /Social-Media-Awards/index.php');
        }
        break;
        
    default:
        // Redirection pour les rôles non reconnus
        header('Location: /Social-Media-Awards/index.php');
        break;
}

exit;
?>
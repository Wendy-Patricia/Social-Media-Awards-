<?php
// logout.php 
// FICHIER : logout.php
// DESCRIPTION : Script de déconnexion de l'utilisateur
// FONCTIONNALITÉ : Termine la session en cours et redirige vers la page de login
// REMARQUE : Cette fonctionnalité est également présente dans UserController
//            pour une centralisation des opérations d'authentification

session_start();

// Limpar todas as variáveis de sessão
// NETTOYAGE DE LA SESSION : Réinitialisation de toutes les variables de session
$_SESSION = [];

// Destruir o cookie de sessão
// DESTRUCTION DU COOKIE DE SESSION : Suppression du cookie de session côté client
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
// DESTRUCTION DE LA SESSION : Libération de toutes les ressources de session
session_destroy();

// Redirecionar para a página de login
// REDIRECTION : Retour à la page de connexion après déconnexion
header("Location: /Social-Media-Awards-/views/login.php");
exit();
?>
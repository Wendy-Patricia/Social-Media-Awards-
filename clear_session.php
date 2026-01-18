<?php
// clear_session.php

/**
 * Script de nettoyage de session pour débogage
 * - Détruit complètement la session utilisateur
 * - Nettoie les cookies associés
 * - Fournit une interface de retour pour le développeur
 * 
 * @note Ce script est principalement utilisé pendant le développement
 */
session_start();
session_destroy();

/**
 * Nettoyage des cookies de session
 */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/**
 * Affichage de confirmation pour l'utilisateur
 */
echo "<h1>Sessão Limpa!</h1>";
echo "<p>Sua sessão foi destruída. Agora você pode:</p>";
echo "<ul>";
echo "<li><a href='inscription.php'>Criar nova conta</a></li>";
echo "<li><a href='views/login.php'>Fazer login</a></li>";
echo "</ul>";

// Redirection automatique après 3 secondes
header("refresh:3;url=inscription.php");
?>
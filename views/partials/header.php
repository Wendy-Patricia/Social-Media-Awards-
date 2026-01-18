<?php
// Partial : header.php
// En-tête réutilisable pour toutes les pages du site
// Gère l'affichage conditionnel selon l'état de connexion de l'utilisateur

// Inclure la gestion des sessions pour vérifier l'authentification
require_once __DIR__ . '/../../config/session.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Métadonnées pour la compatibilité et l'affichage -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Feuilles de style -->
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/header.css">
    <!-- Icônes Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <!-- En-tête principal -->
    <header class="header">
        <!-- Barre de navigation -->
        <nav class="navbar">
            <!-- Logo et nom du site -->
            <div class="nav-logo">
                <img src="/Social-Media-Awards-/assets/images/logo.png" alt="Logo Social Media Awards" class="logo">
                <div class="logo-text">Social Media Awards</div>
            </div>

            <!-- Menu de navigation principal -->
            <ul class="nav-menu">
                <li><a href="/Social-Media-Awards-/index.php">Accueil</a></li>
                <li><a href="/Social-Media-Awards-/categories.php">Catégories</a></li>
                <li><a href="/Social-Media-Awards-/nominees.php">Nominés</a></li>
                <li><a href="/Social-Media-Awards-/results.php">Résultats</a></li>
                <li><a href="/Social-Media-Awards-/contact.php">Contacts</a></li>
                <li><a href="/Social-Media-Awards-/about.php">À propos</a></li>
            </ul>

            <!-- Section des boutons d'authentification -->
            <div class="nav-buttons">
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                    <!-- Affichage lorsque l'utilisateur est connecté -->
                    <!-- Message de bienvenue avec le pseudonyme de l'utilisateur -->
                    <span>Bien-venue<?php echo htmlspecialchars($_SESSION['user_pseudonyme'] ?? 'Utilizador'); ?>!</span>
                    <!-- Bouton de déconnexion -->
                    <a href="/Social-Media-Awards-/logout.php" class="logout-button">Sortir</a>
                <?php else: ?>
                    <!-- Affichage lorsque l'utilisateur n'est pas connecté -->
                    <!-- Bouton de connexion -->
                    <a href="/Social-Media-Awards-/views/login.php" class="login-button">Connexion</a>
                    <!-- Bouton d'inscription -->
                    <a href="/Social-Media-Awards-/inscription.php" class="signup-button">Inscription</a>
                <?php endif; ?>
            </div>

            <!-- Menu hamburger pour la version mobile -->
            <div class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <!-- Script JavaScript pour la navigation mobile -->
    <script src="/Social-Media-Awards-/assets/js/header.js"></script>
<?php
    require_once __DIR__ . '/../../config/session.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <header class="header">
        <nav class="navbar">
            <div class="nav-logo">
                <img src="/Social-Media-Awards-/assets/images/logo.png" alt="Logo Social Media Awards" class="logo">
                <div class="logo-text">Social Media Awards</div>
            </div>

            <ul class="nav-menu">
                <li><a href="/Social-Media-Awards-/index.php">Accueil</a></li>
                <li><a href="/Social-Media-Awards-/categories.php">Catégories</a></li>
                <li><a href="/Social-Media-Awards-/nominees.php">Nominés</a></li>
                <li><a href="/Social-Media-Awards-/results.php">Résultats</a></li>
                <li><a href="/Social-Media-Awards-/contact.php">Contacts</a></li>
                <li><a href="/Social-Media-Awards-/about.php">À propos</a></li>
            </ul>

            <div class="nav-buttons">
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                    <span>Bien-venue<?php echo htmlspecialchars($_SESSION['user_pseudonyme'] ?? 'Utilizador'); ?>!</span>
                    <a href="/Social-Media-Awards-/logout.php" class="logout-button">Sortir</a>
                <?php else: ?>
                    <a href="/Social-Media-Awards-/views/login.php" class="login-button">Connexion</a>
                    <a href="/Social-Media-Awards-/inscription.php" class="signup-button">Inscription</a>
                <?php endif; ?>
            </div>

            <div class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <script src="/Social-Media-Awards-/assets/js/header.js"></script>
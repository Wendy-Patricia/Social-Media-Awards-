<?php
require_once __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/permissions.php';
requireAdmin();

$page_title = 'Tableau de Bord Administratif';
$is_admin_page = true;

require_once __DIR__ . '/../../views/partials/admin-header.php';
?>

<link rel="stylesheet" href="../../assets/css/admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<main class="admin-main-content dashboard-simple">
    <div class="welcome-container">
        <div class="welcome-card">
            <div class="welcome-icon">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            
            <h1 class="welcome-title">
                Bienvenue, <span class="welcome-name"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Administrateur') ?></span> !
            </h1>
            
            <p class="welcome-message">
                Vous êtes connecté au panneau d'administration du Social Media Awards.
            </p>
            
            <div class="welcome-date">
                <i class="fas fa-calendar-day"></i>
                <span id="currentDateTime"><?= date('l, d F Y - H:i') ?></span>
            </div>
            
            <div class="welcome-actions">
                <a href="editions/gerer-editions.php" class="welcome-action-btn">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Gérer les éditions</span>
                </a>
                
                <a href="editions/ajouter-edition.php" class="welcome-action-btn primary">
                    <i class="fas fa-plus-circle"></i>
                    <span>Créer une édition</span>
                </a>
            </div>
        </div>
        
        <div class="quick-links">
            <h2><i class="fas fa-bolt"></i> Accès Rapide</h2>
            
            <div class="links-grid">
                <a href="categories/gerer-categories.php" class="link-item">
                    <div class="link-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="link-content">
                        <h3>Catégories</h3>
                        <p>Gérer les catégories de prix</p>
                    </div>
                </a>
                
                <a href="candidatures/manage-candidature.php" class="link-item">
                    <div class="link-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="link-content">
                        <h3>Candidatures</h3>
                        <p>Modérer les candidatures</p>
                    </div>
                </a>
                
                <a href="nominations/manage-nominations.php" class="link-item">
                    <div class="link-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="link-content">
                        <h3>Utilisateurs</h3>
                        <p>Gérer les nominees</p>
                    </div>
                </a>
                
            </div>
        </div>
    </div>
</main>

<script src="../../../assets/js/admin-dashboard.js"></script>
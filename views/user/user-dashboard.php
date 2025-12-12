<?php
// app/views/user/dashboard.php
require_once '../../config/session.php';
require_once '../../app/Services/UserService.php';

// Verificar se é votante
requireRole('voter');

$userService = new UserService();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Tableau de Bord - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/votant.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include '../partials/header.php'; ?> 
    
    <main class="dashboard-container">
        <!-- User Info -->
        <section class="user-info-section">
            <div class="user-profile">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="profile-details">
                    <h1>Bonjour, <?php echo htmlspecialchars($_SESSION['user_pseudonyme'] ?? 'Utilisateur'); ?>!</h1>
                    <p>Bienvenue sur votre tableau de bord</p>
                    <div class="user-stats">
                        <span class="stat-item">
                            <i class="fas fa-envelope"></i>
                            <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'Non défini'); ?>
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-user-tag"></i>
                            Électeur
                        </span>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Voting Status -->
        <section class="voting-status">
            <h2><i class="fas fa-vote-yea"></i> Statut de Vote</h2>
            <div class="status-cards">
                <div class="status-card">
                    <div class="status-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="status-content">
                        <h3>Élections Actives</h3>
                        <p>0 élections en cours</p>
                    </div>
                </div>
                
                <div class="status-card">
                    <div class="status-icon">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <div class="status-content">
                        <h3>Votes Émis</h3>
                        <p>0 votes effectués</p>
                    </div>
                </div>
                
                <div class="status-card">
                    <div class="status-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="status-content">
                        <h3>Certificats</h3>
                        <p>0 certificats obtenus</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Available Elections -->
        <section class="available-elections">
            <h2><i class="fas fa-calendar-alt"></i> Élections Disponibles</h2>
            <div class="elections-grid">
                <div class="election-card">
                    <div class="election-header">
                        <h3>Social Media Awards 2024</h3>
                        <span class="election-status status-active">Active</span>
                    </div>
                    <div class="election-body">
                        <p>Votez pour vos créateurs préférés dans toutes les catégories</p>
                        <div class="election-details">
                            <span><i class="fas fa-clock"></i> Clôture: 31/12/2024</span>
                            <span><i class="fas fa-tags"></i> 10 catégories</span>
                        </div>
                    </div>
                    <div class="election-footer">
                        <a href="../categories.php" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Voir Catégories
                        </a>
                    </div>
                </div>
                
                <div class="election-card">
                    <div class="election-header">
                        <h3>Influenceur de l'Année</h3>
                        <span class="election-status status-coming">Bientôt</span>
                    </div>
                    <div class="election-body">
                        <p>Découvrez les nominés pour l'influenceur de l'année</p>
                        <div class="election-details">
                            <span><i class="fas fa-clock"></i> Début: 01/01/2025</span>
                            <span><i class="fas fa-tags"></i> 1 catégorie</span>
                        </div>
                    </div>
                    <div class="election-footer">
                        <button class="btn btn-secondary" disabled>
                            <i class="fas fa-bell"></i> M'informer
                        </button>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Quick Links -->
        <section class="quick-links">
            <h2><i class="fas fa-link"></i> Accès Rapide</h2>
            <div class="links-grid">
                <a href="../categories.php" class="link-card">
                    <i class="fas fa-tags"></i>
                    <span>Catégories de Vote</span>
                </a>
                <a href="../nominees.php" class="link-card">
                    <i class="fas fa-users"></i>
                    <span>Voir Nominés</span>
                </a>
                <a href="../results.php" class="link-card">
                    <i class="fas fa-chart-bar"></i>
                    <span>Résultats</span>
                </a>
                <a href="edit-profile.php" class="link-card">
                    <i class="fas fa-user-edit"></i>
                    <span>Modifier Profil</span>
                </a>
            </div>
        </section>
    </main>
    
    <!-- Footer -->
    <?php include '../partials/footer.php'; ?>
    
    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>
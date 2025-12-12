<?php
// app/views/candidate/dashboard.php
require_once '../../config/session.php';
require_once '../../app/Services/UserService.php';

// Verificar se é candidato
requireRole('candidate');

$userService = new UserService();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Candidat - Social Media Awards</title>
    <link rel="stylesheet" href="../../assets/css/candidate.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="candidate-container">
        <!-- Header -->
        <?php include '../partials/header.php'; ?>
        
        <div class="candidate-content">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="welcome-text">
                    <h1><i class="fas fa-user-tie"></i> Bienvenue Candidat!</h1>
                    <p><?php echo htmlspecialchars($_SESSION['user_pseudonyme'] ?? 'Candidat'); ?>, suivez votre parcours de candidature</p>
                </div>
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>
            
            <!-- Status Cards -->
            <div class="status-cards">
                <div class="status-card <?php echo 'status-pending'; ?>">
                    <div class="status-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="status-info">
                        <h3>Statut Candidature</h3>
                        <div class="status-badge">En Attente</div>
                        <p>Votre candidature est en cours de modération</p>
                    </div>
                </div>
                
                <div class="status-card">
                    <div class="status-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="status-info">
                        <h3>Nominations</h3>
                        <div class="status-number">0</div>
                        <p>Nominations obtenues</p>
                    </div>
                </div>
                
                <div class="status-card">
                    <div class="status-icon">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <div class="status-info">
                        <h3>Votes</h3>
                        <div class="status-number">0</div>
                        <p>Votes reçus</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="candidate-actions">
                <h2><i class="fas fa-rocket"></i> Actions Disponibles</h2>
                <div class="action-grid">
                    <a href="submit-application.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <h3>Soumettre Candidature</h3>
                        <p>Postuler pour une catégorie</p>
                    </a>
                    
                    <a href="edit-profile.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h3>Modifier Profil</h3>
                        <p>Mettre à jour vos informations</p>
                    </a>
                    
                    <a href="candidate-status.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Suivi Candidature</h3>
                        <p>Voir l'avancement de votre candidature</p>
                    </a>
                </div>
            </div>
            
            <!-- My Applications -->
            <div class="applications-section">
                <h2><i class="fas fa-file-alt"></i> Mes Candidatures</h2>
                <div class="applications-list">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune candidature</h3>
                        <p>Vous n'avez pas encore soumis de candidature.</p>
                        <a href="submit-application.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Soumettre une candidature
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <?php include '../partials/footer.php'; ?>
    </div>
    
    <script src="../../assets/js/candidate.js"></script>
</body>
</html>
<?php
// views/user/user-dashboard.php
require_once '../../config/session.php';

// Verificar autenticação e tipo de usuário usando requireRole
requireRole('voter');

// Obter dados do usuário da sessão
$userId = $_SESSION['user_id'] ?? null;
$userPseudonyme = $_SESSION['user_pseudonyme'] ?? 'Électeur';
$userEmail = $_SESSION['user_email'] ?? 'Non défini';

// Obter iniciais para o avatar
$initials = strtoupper(substr($userPseudonyme, 0, 2));

// Dados simulados (serão substituídos por consultas à BD)
$hasVoted = false;
$activeElections = 2;
$votesMade = 0;
$certificates = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Électeur - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/user-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header personnalisé du dashboard -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo-section">
                
                <img src="/Social-Media-Awards-/assets/images/logo.png" alt="Logo Social Media Awards" class="logo-image">
                <h1>Social Media <span class="highlight">Awards</span></h1>
            </div>
            
            <nav class="user-nav">
                <div class="user-info-nav">
                    <div class="avatar-nav"><?php echo $initials; ?></div>
                    <div class="user-details-nav">
                        <span class="user-name-nav"><?php echo htmlspecialchars($userPseudonyme); ?></span>
                        <span class="user-role-nav">Électeur</span>
                    </div>
                </div>
                
                <a href="/Social-Media-Awards-/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </nav>
        </div>
    </header>

    <main class="dashboard-container">
        <div class="dashboard-main">
            <!-- Hero Section - Informações personnelles -->
            <section class="hero-section">
                <div class="hero-content">
                    <div class="hero-avatar">
                        <?php echo $initials; ?>
                    </div>
                    
                    <div class="hero-text">
                        <h1>Bonjour, <?php echo htmlspecialchars($userPseudonyme); ?>!</h1>
                        <p>Bienvenue dans votre espace électeur. Gérez vos votes, consultez les candidats et suivez les résultats en temps réel.</p>
                        
                        <div class="hero-stats">
                            <div class="stat-badge">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($userEmail); ?></span>
                            </div>
                            <div class="stat-badge">
                                <i class="fas fa-vote-yea"></i>
                                <span>Statut: Électeur</span>
                            </div>
                            <div class="stat-badge">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Membre depuis: <?php echo date('m/Y'); ?></span>
                            </div>
                        </div>
                        
                        <div class="hero-actions">
                            <a href="edit-profile.php" class="btn btn-secondary">
                                <i class="fas fa-user-edit"></i>
                                Modifier Profil
                            </a>
                            <a href="/Social-Media-Awards-/categories.php" class="btn btn-outline" style="color: var(--white); border-color: var(--white);">
                                <i class="fas fa-tags"></i>
                                Explorer Catégories
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Seção de Status do Voto -->
            <div class="voting-section">
                <!-- Estado do Voto -->
                <section class="voting-status-card">
                    <div class="status-header">
                        <i class="fas fa-vote-yea"></i>
                        <h2>État de votre vote</h2>
                    </div>
                    
                    <div class="vote-status-container">
                        <div class="vote-indicator <?php echo $hasVoted ? 'has-voted' : 'not-voted'; ?>">
                            <div class="vote-icon <?php echo $hasVoted ? 'has-voted' : 'not-voted'; ?>">
                                <i class="fas <?php echo $hasVoted ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                            </div>
                            <h3><?php echo $hasVoted ? 'Vote Enregistré!' : 'En attente de vote'; ?></h3>
                            <p><?php echo $hasVoted ? 'Merci d\'avoir participé à cette élection.' : 'Période de vote en cours. Exprimez-vous!'; ?></p>
                            
                            <div class="vote-period">
                                <i class="fas fa-clock"></i>
                                <span>Période de vote: 01 Déc - 31 Déc 2025</span>
                            </div>
                        </div>
                        
                        <div class="vote-stats-grid">
                            <div class="stat-card">
                                <div class="stat-card-icon">
                                    <i class="fas fa-vote-yea"></i>
                                </div>
                                <h4>Votes émis</h4>
                                <div class="number"><?php echo $votesMade; ?></div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-card-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <h4>Certificats</h4>
                                <div class="number"><?php echo $certificates; ?></div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-card-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <h4>Élections actives</h4>
                                <div class="number"><?php echo $activeElections; ?></div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-card-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <h4>Prochain vote</h4>
                                <div class="number">01/01</div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Informações Rápidas -->
                <section class="quick-info-cards">
                    <div class="quick-info-card">
                        <i class="fas fa-exclamation-circle" style="color: var(--tertiary);"></i>
                        <div class="quick-info-content">
                            <h4>Important à savoir</h4>
                            <p>Vous pouvez voter une fois par catégorie. Consultez les règles avant de voter.</p>
                        </div>
                    </div>
                    
                    <div class="quick-info-card">
                        <i class="fas fa-shield-alt" style="color: var(--principal);"></i>
                        <div class="quick-info-content">
                            <h4>Vote sécurisé</h4>
                            <p>Notre système garantit l'anonymat et l'intégrité de votre vote.</p>
                        </div>
                    </div>
                    
                    <div class="quick-info-card">
                        <i class="fas fa-question-circle" style="color: var(--secondary);"></i>
                        <div class="quick-info-content">
                            <h4>Besoin d'aide?</h4>
                            <p>Consultez notre FAQ ou contactez notre support pour toute question.</p>
                        </div>
                    </div>
                    
                    <a href="/Social-Media-Awards-/nominees.php" class="btn btn-primary btn-block">
                        <i class="fas fa-users"></i>
                        Voir tous les candidats
                    </a>
                </section>
            </div>

            <!-- Élections Disponíveis -->
            <section class="elections-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        <h2>Élections Disponibles</h2>
                    </div>
                    <a href="/Social-Media-Awards-/index.php" class="btn btn-outline">
                        <i class="fas fa-eye"></i>
                        Voir toutes
                    </a>
                </div>
                
                <div class="elections-grid">
                    <!-- Édition 2024 - Active -->
                    <div class="election-card">
                        <span class="election-badge badge-active">Active</span>
                        <div class="election-header">
                            <h3>Édition 2025</h3>
                            <span class="election-category">Social Media Awards</span>
                        </div>
                        <div class="election-body">
                            <p class="election-description">
                                Votez pour les créateurs les plus influents de l'année dans 12 catégories différentes. 
                                Les résultats seront dévoilés lors de la cérémonie de remise des prix.
                            </p>
                            
                            <div class="election-meta">
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <div class="label">Clôture</div>
                                        <div class="value">31/12/2025 23:59</div>
                                    </div>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-tags"></i>
                                    <div>
                                        <div class="label">Catégories</div>
                                        <div class="value">12</div>
                                    </div>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i>
                                    <div>
                                        <div class="label">Nominés</div>
                                        <div class="value">65+</div>
                                    </div>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-globe"></i>
                                    <div>
                                        <div class="label">Portée</div>
                                        <div class="value">Internationale</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="election-stats">
                                <div class="stat-pill">
                                    <div class="number">25K</div>
                                    <div class="label">Votes</div>
                                </div>
                                <div class="stat-pill">
                                    <div class="number">85%</div>
                                    <div class="label">Participation</div>
                                </div>
                                <div class="stat-pill">
                                    <div class="number">12</div>
                                    <div class="label">Jours restants</div>
                                </div>
                            </div>
                        </div>
                        <div class="election-footer">
                            <a href="/Social-Media-Awards-/categories.php" class="btn btn-primary">
                                <i class="fas fa-vote-yea"></i>
                                Voter maintenant
                            </a>
                            <a href="/Social-Media-Awards-/nominees.php" class="btn btn-outline">
                                <i class="fas fa-list"></i>
                                Voir nominés
                            </a>
                        </div>
                    </div>

                    <!-- Influenceur de l'Année - À venir -->
                    <div class="election-card">
                        <span class="election-badge badge-upcoming">À venir</span>
                        <div class="election-header">
                            <h3>Influenceur de l'Année</h3>
                            <span class="election-category">Trophée spécial</span>
                        </div>
                        <div class="election-body">
                            <p class="election-description">
                                Découvrez les nominés pour le titre prestigieux d'Influenceur de l'Année 2024. 
                                Votez pour le créateur qui a le plus marqué les réseaux sociaux.
                            </p>
                            
                            <div class="election-meta">
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <div class="label">Début</div>
                                        <div class="value">01/01/2026 00:00</div>
                                    </div>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-tags"></i>
                                    <div>
                                        <div class="label">Catégorie unique</div>
                                        <div class="value">1</div>
                                    </div>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i>
                                    <div>
                                        <div class="label">Nominés</div>
                                        <div class="value">15</div>
                                    </div>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-trophy"></i>
                                    <div>
                                        <div class="label">Prix</div>
                                        <div class="value">Trophée Or</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="election-stats">
                                <div class="stat-pill">
                                    <div class="number">0</div>
                                    <div class="label">Votes</div>
                                </div>
                                <div class="stat-pill">
                                    <div class="number">0%</div>
                                    <div class="label">Participation</div>
                                </div>
                                <div class="stat-pill">
                                    <div class="number">20</div>
                                    <div class="label">Jours avant</div>
                                </div>
                            </div>
                        </div>
                        <div class="election-footer">
                            <button class="btn btn-disabled" disabled>
                                <i class="fas fa-bell"></i>
                                Me notifier
                            </button>
                            <a href="/Social-Media-Awards-/nominees.php?category=influenceur" class="btn btn-outline">
                                <i class="fas fa-eye"></i>
                                Voir nominés
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Catégories Populaires -->
            <section class="categories-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-star"></i>
                        <h2>Catégories Populaires</h2>
                    </div>
                    <a href="/Social-Media-Awards-/categories.php" class="btn btn-outline">
                        <i class="fas fa-arrow-right"></i>
                        Toutes les catégories
                    </a>
                </div>
                
                <div class="categories-grid">
                    <!-- Catégorie 1 -->
                    <div class="category-card">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                            <div class="category-title">
                                <h4>Meilleur Photographe</h4>
                                <span class="category-platform">Instagram</span>
                            </div>
                        </div>
                        <p class="category-description">
                            Révélez les talents qui transforment l'ordinaire en extraordinaire 
                            grâce à leur vision photographique unique.
                        </p>
                        <div class="category-stats">
                            <div class="category-stat">
                                <div class="number">25</div>
                                <div class="label">Nominés</div>
                            </div>
                            <div class="category-stat">
                                <div class="number">15K</div>
                                <div class="label">Votes</div>
                            </div>
                        </div>
                        <a href="/Social-Media-Awards-/nominees.php?category=photographe" class="btn btn-outline btn-block category-action">
                            <i class="fas fa-users"></i>
                            Voir nominés
                        </a>
                    </div>

                    <!-- Catégorie 2 -->
                    <div class="category-card">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-gamepad"></i>
                            </div>
                            <div class="category-title">
                                <h4>Meilleur Streamer</h4>
                                <span class="category-platform">Twitch</span>
                            </div>
                        </div>
                        <p class="category-description">
                            Découvrez les streamers qui captivent leur audience avec des 
                            performances exceptionnelles et un contenu engageant.
                        </p>
                        <div class="category-stats">
                            <div class="category-stat">
                                <div class="number">18</div>
                                <div class="label">Nominés</div>
                            </div>
                            <div class="category-stat">
                                <div class="number">12K</div>
                                <div class="label">Votes</div>
                            </div>
                        </div>
                        <a href="/Social-Media-Awards-/nominees.php?category=streamer" class="btn btn-outline btn-block category-action">
                            <i class="fas fa-users"></i>
                            Voir nominés
                        </a>
                    </div>

                    <!-- Catégorie 3 -->
                    <div class="category-card">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-music"></i>
                            </div>
                            <div class="category-title">
                                <h4>Meilleur Musicien</h4>
                                <span class="category-platform">Spotify</span>
                            </div>
                        </div>
                        <p class="category-description">
                            Célébrez les artistes qui innovent et inspirent à travers 
                            leur musique et leur présence sur les réseaux sociaux.
                        </p>
                        <div class="category-stats">
                            <div class="category-stat">
                                <div class="number">22</div>
                                <div class="label">Nominés</div>
                            </div>
                            <div class="category-stat">
                                <div class="number">18K</div>
                                <div class="label">Votes</div>
                            </div>
                        </div>
                        <a href="/Social-Media-Awards-/nominees.php?category=musicien" class="btn btn-outline btn-block category-action">
                            <i class="fas fa-users"></i>
                            Voir nominés
                        </a>
                    </div>
                </div>
            </section>

            <!-- Candidats en Vedette -->
            <section class="candidates-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-crown"></i>
                        <h2>Candidats en Vedette</h2>
                    </div>
                    <a href="/Social-Media-Awards-/nominees.php" class="btn btn-outline">
                        <i class="fas fa-users"></i>
                        Tous les candidats
                    </a>
                </div>
                
                <div class="candidates-grid">
                    <!-- Candidat 1 -->
                    <div class="candidate-card">
                        <div class="candidate-header">
                            <span class="candidate-badge">Top 3</span>
                            <div class="candidate-avatar">
                                <i class="fas fa-camera"></i>
                            </div>
                            <h3>@PhotoPro</h3>
                            <div class="candidate-category">Meilleur Photographe</div>
                            <span class="candidate-platform platform-instagram">
                                <i class="fab fa-instagram"></i> Instagram
                            </span>
                        </div>
                        <div class="candidate-body">
                            <div class="candidate-stats">
                                <div class="candidate-stat">
                                    <span class="number">2.5M</span>
                                    <span class="label">Abonnés</span>
                                </div>
                                <div class="candidate-stat">
                                    <span class="number">145</span>
                                    <span class="label">Nominations</span>
                                </div>
                            </div>
                            <p class="candidate-description">
                                Photographe spécialisé en paysages urbains. Transforme 
                                les villes en œuvres d'art à travers son objectif unique.
                            </p>
                        </div>
                        <div class="candidate-footer">
                            <a href="#" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i>
                                Voir profil
                            </a>
                            <a href="#" class="btn btn-outline btn-sm">
                                <i class="fas fa-vote-yea"></i>
                                Voter
                            </a>
                        </div>
                    </div>

                    <!-- Candidat 2 -->
                    <div class="candidate-card">
                        <div class="candidate-header">
                            <span class="candidate-badge">Révélation</span>
                            <div class="candidate-avatar">
                                <i class="fas fa-gamepad"></i>
                            </div>
                            <h3>@GameMaster</h3>
                            <div class="candidate-category">Meilleur Streamer</div>
                            <span class="candidate-platform platform-twitch">
                                <i class="fab fa-twitch"></i> Twitch
                            </span>
                        </div>
                        <div class="candidate-body">
                            <div class="candidate-stats">
                                <div class="candidate-stat">
                                    <span class="number">1.8M</span>
                                    <span class="label">Abonnés</span>
                                </div>
                                <div class="candidate-stat">
                                    <span class="number">89</span>
                                    <span class="label">Nominations</span>
                                </div>
                            </div>
                            <p class="candidate-description">
                                Streamer passionné de jeux indépendants. Sa communauté 
                                engagée et bienveillante fait de chaque stream un moment unique.
                            </p>
                        </div>
                        <div class="candidate-footer">
                            <a href="#" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i>
                                Voir profil
                            </a>
                            <a href="#" class="btn btn-outline btn-sm">
                                <i class="fas fa-vote-yea"></i>
                                Voter
                            </a>
                        </div>
                    </div>

                    <!-- Candidat 3 -->
                    <div class="candidate-card">
                        <div class="candidate-header">
                            <span class="candidate-badge">Vétéran</span>
                            <div class="candidate-avatar">
                                <i class="fas fa-music"></i>
                            </div>
                            <h3>@SoundWizard</h3>
                            <div class="candidate-category">Meilleur Musicien</div>
                            <span class="candidate-platform platform-spotify">
                                <i class="fab fa-spotify"></i> Spotify
                            </span>
                        </div>
                        <div class="candidate-body">
                            <div class="candidate-stats">
                                <div class="candidate-stat">
                                    <span class="number">3.2M</span>
                                    <span class="label">Abonnés</span>
                                </div>
                                <div class="candidate-stat">
                                    <span class="number">203</span>
                                    <span class="label">Nominations</span>
                                </div>
                            </div>
                            <p class="candidate-description">
                                Producteur et compositeur innovant. Fusionne musique 
                                électronique et traditionnelle pour créer des sonorités uniques.
                            </p>
                        </div>
                        <div class="candidate-footer">
                            <a href="#" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i>
                                Voir profil
                            </a>
                            <a href="#" class="btn btn-outline btn-sm">
                                <i class="fas fa-vote-yea"></i>
                                Voter
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer personnalisé -->
    <footer class="dashboard-footer">
        <div class="footer-content">
            <div class="footer-links">
                <a href="/Social-Media-Awards-/categories.php">Catégories</a>
                <a href="/Social-Media-Awards-/nominees.php">Nominés</a>
                <a href="/Social-Media-Awards-/results.php">Résultats</a>
                <a href="/Social-Media-Awards-/contact.php">Contact</a>
                <a href="/Social-Media-Awards-/about.php">À propos</a>
                <a href="/Social-Media-Awards-/faq.php">FAQ</a>
            </div>
            <div class="copyright">
                &copy; 2024 Social Media Awards. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script src="/Social-Media-Awards-/assets/js/user-dashboard.js"></script>
</body>
</html>
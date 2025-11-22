<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Social Media Awards 2025</title>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="main-content">
        <!--  SECTION HERO BANNER - PLEINE LARGEUR SANS ESPACE-->
        <section class="hero-banner">
            <div class="banner-container">
                <img src="assets/images/banner1.png" alt="Bannière Social Media Awards 2025" class="hero-banner-image">
                <div class="banner-overlay">
                    <div class="banner-text">
                        <h1 class="banner-title">Social Media Awards 2025</h1>
                        <p class="banner-subtitle">Célébrez l'excellence numérique à travers les plateformes sociales</p>
                        <div class="banner-buttons">
                            <a href="nominees.php" class="btn-primary">
                                <i class="fas fa-vote-yea"></i>
                                Commencer à Voter
                            </a>
                            <a href="#edition-info" class="btn-secondary">
                                <i class="fas fa-info-circle"></i>
                                En savoir plus
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--  SECTION HERO PRINCIPALE-->
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h2 class="hero-title">La Plus Grande Célébration du Digital</h2>
                    <p class="hero-description">
                        Les Social Media Awards récompensent l'innovation, la créativité et l'impact des contenus
                        à travers toutes les plateformes sociales. Rejoignez des milliers de passionnés pour
                        célébrer les talents qui façonnent l'univers numérique.
                    </p>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="stat-number">12</div>
                            <div class="stat-label">Catégories</div>
                        </div>
                        <div class="hero-stat">
                            <div class="stat-number">5</div>
                            <div class="stat-label">Plateformes</div>
                        </div>
                        <div class="hero-stat">
                            <div class="stat-number">50K+</div>
                            <div class="stat-label">Votes</div>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="platforms-showcase">
                        <div class="platform-item">
                            <i class="fab fa-tiktok"></i>
                            <span>TikTok</span>
                        </div>
                        <div class="platform-item">
                            <i class="fab fa-instagram"></i>
                            <span>Instagram</span>
                        </div>
                        <div class="platform-item">
                            <i class="fab fa-youtube"></i>
                            <span>YouTube</span>
                        </div>
                        <div class="platform-item">
                            <i class="fab fa-twitter"></i>
                            <span>Twitter</span>
                        </div>
                        <div class="platform-item">
                            <i class="fab fa-facebook"></i>
                            <span>Facebook</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION ÉDITION INFO -->
        <section class="edition-info" id="edition-info">
            <div class="container">
                <h2>Édition 2025</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date">15 Sept - 15 Oct</div>
                        <div class="timeline-content">
                            <h3>Période de Candidatures</h3>
                            <p>Soumettez vos candidatures pour être considéré dans les différentes catégories</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">1 Nov - 30 Nov</div>
                        <div class="timeline-content">
                            <h3>Période de Vote</h3>
                            <p>Votez pour vos créateurs et contenus préférés</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">15 Déc</div>
                        <div class="timeline-content">
                            <h3>Annonce des Résultats</h3>
                            <p>Découvrez les gagnants de chaque catégorie</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION CATÉGORIES POPULAIRES -->
        <section class="categories" id="categories">
            <div class="container">
                <h2>Catégories de Compétition</h2>
                <div class="categories-grid">
                    <!-- Catégorie 1 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>Créateur Révélation de l'Année</h3>
                        <p>Les nouveaux talents qui ont explosé cette année</p>
                        <span class="platform-tag">Multi-Plateformes</span>
                    </div>
                    
                    <!-- Catégorie 2 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-podcast"></i>
                        </div>
                        <h3>Meilleur Podcast en Ligne</h3>
                        <p>Les podcasts les plus engageants et innovants</p>
                        <span class="platform-tag">YouTube/Spotify</span>
                    </div>
                    
                    <!-- Catégorie 3 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3>Campagne Branded Content</h3>
                        <p>Collaborations marques-créateurs les plus créatives</p>
                        <span class="platform-tag">Instagram</span>
                    </div>
                    
                    <!-- Catégorie 4 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-virus"></i>
                        </div>
                        <h3>Mème le plus Virulent</h3>
                        <p>Les mèmes qui ont dominé les réseaux sociaux</p>
                        <span class="platform-tag">Twitter</span>
                    </div>
                    
                    <!-- Catégorie 5 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <h3>Meilleure Série Court-Format</h3>
                        <p>Séries conçues spécifiquement pour les réseaux</p>
                        <span class="platform-tag">TikTok</span>
                    </div>
                    
                    <!-- Catégorie 6 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3>Contenu Éducatif</h3>
                        <p>Créateurs qui rendent le savoir accessible</p>
                        <span class="platform-tag">YouTube</span>
                    </div>
                    
                    <!-- Catégorie 7 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h3>Créateur Culinaire</h3>
                        <p>Les meilleurs contenus gastronomiques en ligne</p>
                        <span class="platform-tag">Instagram</span>
                    </div>
                    
                    <!-- Catégorie 8 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-dumbbell"></i>
                        </div>
                        <h3>Influenceur Sport & Fitness</h3>
                        <p>Contenus inspirants autour du sport et bien-être</p>
                        <span class="platform-tag">YouTube</span>
                    </div>
                    
                    <!-- Catégorie 9 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <h3>Artiste Digital</h3>
                        <p>Créations artistiques et design innovant</p>
                        <span class="platform-tag">Behance/Dribbble</span>
                    </div>
                    
                    <!-- Catégorie 10 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <h3>Streamer de l'Année</h3>
                        <p>Meilleur contenu gaming et live streaming</p>
                        <span class="platform-tag">Twitch</span>
                    </div>
                    
                    <!-- Catégorie 11 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3>Initiative Sociale</h3>
                        <p>Créateurs engagés pour des causes sociales</p>
                        <span class="platform-tag">Multi-Plateformes</span>
                    </div>
                    
                    <!-- Catégorie 12 -->
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-music"></i>
                        </div>
                        <h3>Découverte Musicale</h3>
                        <p>Artistes révélés grâce aux réseaux sociaux</p>
                        <span class="platform-tag">TikTok/SoundCloud</span>
                    </div>
                </div>
            </div>
        </section>

        <!--SECTION PROCESSUS-->
        <section class="process">
            <div class="container">
                <h2>Comment Participer</h2>
                <div class="process-steps">
                    <!-- Étape 1 -->
                    <div class="step">
                        <div class="step-number">1</div>
                        <h3>Créer un Compte</h3>
                        <p>Inscrivez-vous en tant qu'utilisateur pour voter ou en tant que candidat pour soumettre vos créations</p>
                    </div>
                    
                    <!-- Étape 2 -->
                    <div class="step">
                        <div class="step-number">2</div>
                        <h3>Soumettre ou Voter</h3>
                        <p>Pendant les périodes ouvertes, soumettez vos candidatures ou votez pour vos favoris</p>
                    </div>
                    
                    <!-- Étape 3 -->
                    <div class="step">
                        <div class="step-number">3</div>
                        <h3>Résultats</h3>
                        <p>Découvrez les gagnants et célébrez l'excellence numérique</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION STATISTIQUES - PLEINE LARGEUR-->
        <section class="stats">
            <div class="container">
                <div class="stats-grid">
                    <!-- Statistique 1 -->
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Catégories</div>
                    </div>
                    
                    <!-- Statistique 2 -->
                    <div class="stat-item">
                        <div class="stat-number">1000+</div>
                        <div class="stat-label">Candidatures</div>
                    </div>
                    
                    <!-- Statistique 3 -->
                    <div class="stat-item">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Votes</div>
                    </div>
                    
                    <!-- Statistique 4 -->
                    <div class="stat-item">
                        <div class="stat-number">5</div>
                        <div class="stat-label">Plateformes</div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'footer.php'; ?>

    <script src="assets/js/index.js"></script>
</body>
</html>
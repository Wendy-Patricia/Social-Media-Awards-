
<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/permissions.php';
require_once __DIR__ . '/config/database.php';

/**
 * Fonction pour obtenir le lien de vote dynamique selon l'authentification
 * - Si l'utilisateur est authentifié comme électeur : redirige vers la page de vote
 * - Si l'utilisateur est authentifié comme candidat/admin : redirige vers son dashboard
 * - Si l'utilisateur n'est pas authentifié : redirige vers login avec redirection vers vote
 * 
 * @return string URL de destination
 */
function getVoteLink() {
    if (isAuthenticated()) {
        $userType = getUserType();
        if ($userType === 'voter') {
            // Électeur authentifié → page de vote
            return '/Social-Media-Awards-/views/user/Vote.php';
        } elseif ($userType === 'candidate') {
            // Candidat authentifié → dashboard candidat
            return '/Social-Media-Awards-/views/candidate/candidate-dashboard.php';
        } elseif ($userType === 'admin') {
            // Admin authentifié → dashboard admin
            return '/Social-Media-Awards-/views/admin/dashboard.php';
        }
    }
    // Non authentifié → login avec redirection vers page de vote
    return '/Social-Media-Awards-/views/login.php?redirect=' . 
           urlencode('/Social-Media-Awards-/views/user/Vote.php');
}

// Valeurs fixes pour les statistiques d'affichage
$heroStats = [
    'categories' => 12,
    'platforms'  => 5,
    'votes'      => '50K+',
    'candidatures' => 1000
];

$sectionStats = [
    'categories'    => 50,
    'candidatures'  => 1000,
    'votes'         => '50K+',
    'platforms'     => 5
];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Social Media Awards 2026</title>
</head>

<body>
    <?php require_once 'views/partials/header.php'; ?>

    <div class="main-content">
        <!-- BANNIÈRE HERO PRINCIPALE -->
        <section class="hero-banner">
            <div class="banner-container">
                <img src="assets/images/banner1.png" alt="Bannière Social Media Awards 2025" class="hero-banner-image">
                <div class="banner-overlay">
                    <div class="banner-text">
                        <h1 class="banner-title">Social Media Awards 2026</h1>
                        <p class="banner-subtitle">Célébrez l'excellence numérique à travers les plateformes sociales</p>
                        <div class="banner-buttons">
                            <!-- BOUTON "COMMENCER À VOTER" AVEC LIEN DYNAMIQUE -->
                            <!-- Utilise la fonction getVoteLink() pour déterminer la destination -->
                            <a href="<?php echo getVoteLink(); ?>" class="btn-primary">
                                <i class="fas fa-vote-yea"></i>
                                Commencer à Voter
                            </a>
                            <a href="about.php" class="btn-secondary">
                                <i class="fas fa-info-circle"></i>
                                En savoir plus
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION HERO AVEC STATISTIQUES -->
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
                            <div class="stat-number"><?php echo $heroStats['categories']; ?></div>
                            <div class="stat-label">Catégories</div>
                        </div>
                        <div class="hero-stat">
                            <div class="stat-number"><?php echo $heroStats['platforms']; ?></div>
                            <div class="stat-label">Plateformes</div>
                        </div>
                        <div class="hero-stat">
                            <div class="stat-number"><?php echo $heroStats['votes']; ?></div>
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
                            <i class="fas fa-instagram"></i>
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

        <!-- SECTION STATISTIQUES DÉTAILLÉES -->
        <section class="stats">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $sectionStats['categories']; ?>+</div>
                        <div class="stat-label">Catégories</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $sectionStats['candidatures']; ?>+</div>
                        <div class="stat-label">Candidatures</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $sectionStats['votes']; ?>+</div>
                        <div class="stat-label">Votes</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $sectionStats['platforms']; ?></div>
                        <div class="stat-label">Plateformes</div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'views/partials/footer.php'; ?>
    <script src="assets/js/index.js"></script>
</body>
</html>

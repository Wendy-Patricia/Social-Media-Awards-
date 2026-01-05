<?php
// Adicionar apenas estas 4 linhas no TOPO do arquivo
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Services/StatisticsService.php';

$statsService = new StatisticsService();
$heroStats = $statsService->getHomePageStats();
$sectionStats = $statsService->getStatsSection();
?>
<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/permissions.php';
require_once __DIR__ . '/config/database.php';
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
    <title>Social Media Awards 2025</title>
</head>

<body>
    <?php require_once 'views/partials/header.php'; ?>

    <div class="main-content">
        <!-- SECTION HERO BANNER - PLEINE LARGEUR SANS ESPACE-->
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
                            <a href="about.php" class="btn-secondary">
                                <i class="fas fa-info-circle"></i>
                                En savoir plus
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>


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
                            <!-- APENAS ESTA LINHA MUDA - o número fica dinâmico -->
                            <div class="stat-number"><?php echo $heroStats['categories']; ?></div>
                            <div class="stat-label">Catégories</div>
                        </div>
                        <div class="hero-stat">
                            <!-- APENAS ESTA LINHA MUDA -->
                            <div class="stat-number"><?php echo $heroStats['platforms']; ?></div>
                            <div class="stat-label">Plateformes</div>
                        </div>
                        <div class="hero-stat">
                            <!-- APENAS ESTA LINHA MUDA -->
                            <div class="stat-number"><?php echo $heroStats['votes']; ?></div>
                            <div class="stat-label">Votes</div>
                        </div>
                    </div>
                </div>
                <!-- TODO O RESTO PERMANECE IGUAL -->
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

        <!-- ... TODO O MEIO DO ARQUIVO PERMANECE EXATAMENTE IGUAL ... -->

        <!-- APENAS ATUALIZAR A SEÇÃO DE STATS -->
        <section class="stats">
            <div class="container">
                <div class="stats-grid">
                    <!-- APENAS OS NÚMEROS MUDAM -->
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
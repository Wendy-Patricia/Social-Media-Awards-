<?php
/**
 * PAGE : about.php
 * DESCRIPTION : Page "À Propos" présentant la mission, l'équipe et les statistiques des Social Media Awards
 * RESPONSABILITÉS :
 * - Afficher les informations sur l'événement
 * - Présenter l'équipe organisatrice
 * - Montrer les statistiques clés
 * - Maintenir une structure HTML sémantique et accessible
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- CONFIGURATION DE L'ENCODAGE ET DE LA RESPONSIVITÉ -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CHARGEMENT DES FEUILLES DE STYLE -->
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/about.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- DÉFINITION DU TITRE DE LA PAGE -->
    <title>À Propos - Social Media Awards 2025</title>
</head>
<body>
    <!-- INCLUSION DE L'EN-TÊTE COMMUNE -->
    <?php require_once 'views/partials/header.php'; ?>

    <!-- SECTION PRINCIPALE DU CONTENU -->
    <div class="main-content">
        <!-- SECTION HÉRO - INTRODUCTION PRINCIPALE -->
        <section class="about-hero">
            <div class="container">
                <h1>À Propos des Social Media Awards</h1>
                <p>Découvrez notre mission, notre vision et l'équipe derrière la plus grande célébration du digital</p>
            </div>
        </section>

        <!-- SECTION MISSION - OBJECTIFS ET VALEURS -->
        <section class="mission-section">
            <div class="container">
                <div class="mission-grid">
                    <!-- CONTENU TEXTUEL DE LA MISSION -->
                    <div class="mission-content">
                        <h2>Notre Mission</h2>
                        <p>Les Social Media Awards ont été créés pour célébrer l'excellence, l'innovation et la créativité dans l'univers des médias sociaux. Nous croyons que chaque créateur mérite d'être reconnu pour son travail et son impact.</p>
                        
                        <!-- STATISTIQUES DE L'ÉVÉNEMENT -->
                        <div class="mission-stats">
                            <div class="stat">
                                <div class="stat-number">3</div>
                                <div class="stat-label">Éditions</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number">500+</div>
                                <div class="stat-label">Créateurs Récompensés</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number">1M+</div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- VISUELS REPRÉSENTANT LES VALEURS -->
                    <div class="mission-visual">
                        <div class="visual-card">
                            <i class="fas fa-trophy"></i>
                            <h3>Reconnaissance</h3>
                            <p>Valoriser le travail des créateurs</p>
                        </div>
                        <div class="visual-card">
                            <i class="fas fa-users"></i>
                            <h3>Communauté</h3>
                            <p>Rassembler les passionnés du digital</p>
                        </div>
                        <div class="visual-card">
                            <i class="fas fa-rocket"></i>
                            <h3>Innovation</h3>
                            <p>Encourager la créativité</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION ÉQUIPE - PRÉSENTATION DES ORGANISATEURS -->
        <section class="team-section">
            <div class="container">
                <h2>Notre Équipe</h2>
                <div class="team-grid">
                    <!-- MEMBRE DE L'ÉQUIPE 1 -->
                    <div class="team-member">
                        <div class="member-photo">
                            <img src="assets/images/team/member1.jpg" alt="Team Member">
                        </div>
                        <h3>Wendy Mechisso</h3>
                        <p class="member-role">Administrateur</p>
                        <p class="member-bio">Passionné de digital avec 10 ans d'expérience dans les médias sociaux</p>
                    </div>
                    
                    <!-- MEMBRE DE L'ÉQUIPE 2 -->
                    <div class="team-member">
                        <div class="member-photo">
                            <img src="assets/images/team/member2.jpg" alt="Team Member">
                        </div>
                        <h3>Eunice Ligeiro</h3>
                        <p class="member-role">Administrateur</p>
                        <p class="member-bio">Expert en stratégie digitale et animation de communautés en ligne.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- INCLUSION DU PIED DE PAGE COMMUN -->
    <?php include 'views/partials/footer.php'; ?>
</body>
</html>
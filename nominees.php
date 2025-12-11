<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/nominees.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Nominés - Social Media Awards 2025</title>
</head>
<body>
    <?php require_once 'views/partials/header.php'; ?>

    <div class="main-content">
        <!--SECTION HERO  -->
        <section class="nominees-hero">
            <div class="hero-container">
                <h1>Nos Nominés 2025</h1>
                <p>Découvrez les talents exceptionnels sélectionnés pour cette édition des Social Media Awards</p>
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un nominé..." id="searchInput">
                    </div>
                    <select id="categoryFilter">
                        <option value="">Toutes les catégories</option>
                        <option value="revelation">Créateur Révélation</option>
                        <option value="podcast">Podcast en Ligne</option>
                        <option value="branded">Branded Content</option>
                    </select>
                    <select id="platformFilter">
                        <option value="">Toutes les plateformes</option>
                        <option value="tiktok">TikTok</option>
                        <option value="instagram">Instagram</option>
                        <option value="youtube">YouTube</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- GRILLE DES NOMINÉS -->
        <section class="nominees-section">
            <div class="container">
                <div class="nominees-grid">
                    <!-- Nominé 1 -->
                    <div class="nominee-card" data-category="revelation" data-platform="tiktok">
                        <div class="nominee-image">
                            <img src="assets/images/Nominees/nominee1.jpg" alt="Nominee Name">
                            <div class="platform-badge tiktok">
                                <i class="fab fa-tiktok"></i>
                            </div>
                        </div>
                        <div class="nominee-info">
                            <h3>@CreateurTikTok</h3>
                            <p class="nominee-category">Créateur Révélation de l'Année</p>
                            <p class="nominee-description">Contenu humoristique et créatif qui a conquis TikTok</p>
                            <div class="nominee-stats">
                                <div class="stat">
                                    <i class="fas fa-heart"></i>
                                    <span>2.5M</span>
                                </div>
                                <div class="stat">
                                    <i class="fas fa-share"></i>
                                    <span>150K</span>
                                </div>
                            </div>
                            <button class="btn-vote">Voter</button>
                        </div>
                    </div>

                    <!-- Nominé 2 -->
                    <div class="nominee-card" data-category="podcast" data-platform="youtube">
                        <div class="nominee-image">
                            <img src="assets/images/Nominees/nominee2.jpg" alt="Nominee Name">
                            <div class="platform-badge youtube">
                                <i class="fab fa-youtube"></i>
                            </div>
                        </div>
                        <div class="nominee-info">
                            <h3>TechTalk Podcast</h3>
                            <p class="nominee-category">Meilleur Podcast en Ligne</p>
                            <p class="nominee-description">Analyse approfondie des tendances tech et digitales</p>
                            <div class="nominee-stats">
                                <div class="stat">
                                    <i class="fas fa-play-circle"></i>
                                    <span>1.2M</span>
                                </div>
                                <div class="stat">
                                    <i class="fas fa-comment"></i>
                                    <span>45K</span>
                                </div>
                            </div>
                            <button class="btn-vote">Voter</button>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

    <?php include 'views/partials/footer.php'; ?>
    <script src="assets/js/nominees.js"></script>
</body>
</html>
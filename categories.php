<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/categories.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Catégories - Social Media Awards 2025</title>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="main-content">

        <section class="categories-hero">
            <div class="hero-container">
                <h1>Catégories de Compétition</h1>
                <p>Découvrez les 12 catégories qui célèbrent l'excellence à travers toutes les plateformes sociales</p>
                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Catégories</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">5</div>
                        <div class="stat-label">Plateformes</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Nominés</div>
                    </div>
                </div>
            </div>
        </section>


        <section class="categories-section">
            <div class="container">

                <div class="categories-filter">
                    <button class="filter-btn active" data-filter="all">Toutes</button>
                    <button class="filter-btn" data-filter="tiktok">TikTok</button>
                    <button class="filter-btn" data-filter="instagram">Instagram</button>
                    <button class="filter-btn" data-filter="youtube">YouTube</button>
                    <button class="filter-btn" data-filter="twitter">Twitter</button>
                    <button class="filter-btn" data-filter="facebook">Facebook</button>
                </div>

                <div class="categories-grid">

                    <div class="category-card" data-platform="tiktok,instagram,youtube">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="platform-tags">
                                <span class="platform-tag tiktok">TikTok</span>
                                <span class="platform-tag instagram">Instagram</span>
                                <span class="platform-tag youtube">YouTube</span>
                            </div>
                        </div>
                        <h3>Créateur Révélation de l'Année</h3>
                        <p>Les nouveaux talents qui ont marqué l'année par leur croissance exceptionnelle et leur contenu innovant</p>
                        <div class="category-stats">
                            <div class="stat">
                                <div class="stat-number">25</div>
                                <div class="stat-label">Nominés</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number">15K</div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                        <button class="btn-view-nominees">Voir les Nominés</button>
                    </div>


                    <div class="category-card" data-platform="youtube,spotify">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-podcast"></i>
                            </div>
                            <div class="platform-tags">
                                <span class="platform-tag youtube">YouTube</span>
                                <span class="platform-tag spotify">Spotify</span>
                            </div>
                        </div>
                        <h3>Meilleur Podcast en Ligne</h3>
                        <p>Les podcasts les plus engageants, innovants et influents de l'année</p>
                        <div class="category-stats">
                            <div class="stat">
                                <div class="stat-number">18</div>
                                <div class="stat-label">Nominés</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number">12K</div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                        <button class="btn-view-nominees">Voir les Nominés</button>
                    </div>


                    <div class="category-card" data-platform="instagram">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div class="platform-tags">
                                <span class="platform-tag instagram">Instagram</span>
                            </div>
                        </div>
                        <h3>Campagne Branded Content</h3>
                        <p>Collaborations marques-créateurs les plus créatives et impactantes</p>
                        <div class="category-stats">
                            <div class="stat">
                                <div class="stat-number">15</div>
                                <div class="stat-label">Nominés</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number">8K</div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                        <button class="btn-view-nominees">Voir les Nominés</button>
                    </div>


                    <div class="category-card" data-platform="tiktok,instagram">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-fire"></i>
                            </div>
                            <div class="platform-tags">
                                <span class="platform-tag tiktok">TikTok</span>
                                <span class="platform-tag instagram">Instagram</span>
                            </div>
                        </div>
                        <h3>Meilleur Challenge Viral</h3>
                        <p>Les défis les plus créatifs et engageants qui ont conquis les réseaux sociaux</p>
                        <div class="category-stats">
                            <div class="stat">
                                <div class="stat-number">22</div>
                                <div class="stat-label">Nominés</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number">20K</div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                        <button class="btn-view-nominees">Voir les Nominés</button>
                    </div>


                    <div class="category-card" data-platform="youtube,twitch">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-broadcast-tower"></i>
                            </div>
                            <div class="platform-tags">
                                <span class="platform-tag youtube">YouTube</span>
                                <span class="platform-tag twitch">Twitch</span>
                            </div>
                        </div>
                        <h3>Meilleur Live Stream</h3>
                        <p>Les diffusions en direct les plus interactives et mémorables de l'année</p>
                        <div class="category-stats">
                            <div class="stat">
                                <div class="stat-number">16</div>
                                <div class="stat-label">Nominés</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number">14K</div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                        <button class="btn-view-nominees">Voir les Nominés</button>
                    </div>


                    <div class="category-card" data-platform="youtube,tiktok,instagram">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="platform-tags">
                                <span class="platform-tag youtube">YouTube</span>
                                <span class="platform-tag tiktok">TikTok</span>
                                <span class="platform-tag instagram">Instagram</span>
                            </div>
                        </div>
                        <h3>Meilleur Contenu Éducatif</h3>
                        <p>Contenus qui enseignent, informent et rendent le savoir accessible à tous</p>
                        <div class="category-stats">
                            <div class="stat">
                                <div class="stat-number">20</div>
                                <div class="stat-label">Nominés</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number">18K</div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                        <button class="btn-view-nominees">Voir les Nominés</button>
                    </div>


                    <div class="category-card" data-platform="instagram,youtube,tiktok">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="platform-tags">
                                <span class="platform-tag instagram">Instagram</span>
                                <span class="platform-tag youtube">YouTube</span>
                                <span class="platform-tag tiktok">TikTok</span>
                            </div>
                        </div>
                        <h3>Meilleur Influenceur Lifestyle</h3>
                        <p>Créateurs qui inspirent par leur style de vie, leurs conseils et leur authenticité</p>
                        <div class="category-stats">
                            <div class="stat">
                                <div class="stat-number">24</div>
                                <div class="stat-label">Nominés</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number">22K</div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                        <button class="btn-view-nominees">Voir les Nominés</button>
                    </div>
                    
                </div>
            </div>
        </section>
    </div>

    <?php include 'footer.php'; ?>
    <script src="assets/js/categories.js"></script>
</body>
</html>
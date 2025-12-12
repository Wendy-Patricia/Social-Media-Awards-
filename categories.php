<?php
// Adicionar no início (apenas estas 4 linhas)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Services/CategoryService.php';

$categoryService = new CategoryService();
$pageStats = $categoryService->getCategoryPageStats();
$categories = $categoryService->getCategoriesWithDynamicStats();
?>
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
    <?php require_once 'views/partials/header.php'; ?>

    <div class="main-content">
        <section class="categories-hero">
            <div class="hero-container">
                <h1>Catégories de Compétition</h1>
                <p>Découvrez les <?php echo $pageStats['categories']; ?> catégories qui célèbrent l'excellence à travers toutes les plateformes sociales</p>
                <div class="hero-stats">
                    <div class="stat">
                        <!-- NÚMERO DINÂMICO -->
                        <div class="stat-number"><?php echo $pageStats['categories']; ?></div>
                        <div class="stat-label">Catégories</div>
                    </div>
                    <div class="stat">
                        <!-- NÚMERO DINÂMICO -->
                        <div class="stat-number"><?php echo $pageStats['platforms']; ?></div>
                        <div class="stat-label">Plateformes</div>
                    </div>
                    <div class="stat">
                        <!-- NÚMERO DINÂMICO -->
                        <div class="stat-number"><?php echo $pageStats['nominees']; ?></div>
                        <div class="stat-label">Nominés</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="categories-section">
            <div class="container">
                <!-- FILTROS MANTIDOS IGUAIS -->
                <div class="categories-filter">
                    <button class="filter-btn active" data-filter="all">Toutes</button>
                    <button class="filter-btn" data-filter="tiktok">TikTok</button>
                    <button class="filter-btn" data-filter="instagram">Instagram</button>
                    <button class="filter-btn" data-filter="youtube">YouTube</button>
                    <button class="filter-btn" data-filter="twitter">Twitter</button>
                    <button class="filter-btn" data-filter="facebook">Facebook</button>
                </div>

                <div class="categories-grid">
                    <!-- CATEGORIA 1 (estrutura mantida, números dinâmicos) -->
                    <div class="category-card" data-platform="tiktok instagram youtube">
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
                                <!-- NÚMERO DINÂMICO -->
                                <div class="stat-number"><?php echo $categories[0]['nominees']; ?></div>
                                <div class="stat-label">Nominés</div>
                            </div>
                            <div class="stat">
                                <!-- NÚMERO DINÂMICO -->
                                <div class="stat-number"><?php echo $categories[0]['votes']; ?></div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                        <button class="btn-view-nominees">Voir les Nominés</button>
                    </div>

                    <!-- CATEGORIA 2 (estrutura mantida, números dinâmicos) -->
                    <div class="category-card" data-platform="youtube spotify">
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
                                <!-- NÚMERO DINÂMICO -->
                                <div class="stat-number"><?php echo $categories[1]['nominees']; ?></div>
                                <div class="stat-label">Nominés</div>
                            </div>
                            <div class="stat">
                                <!-- NÚMERO DINÂMICO -->
                                <div class="stat-number"><?php echo $categories[1]['votes']; ?></div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                        <button class="btn-view-nominees">Voir les Nominés</button>
                    </div>

                    <!-- REPETIR PARA AS OUTRAS CATEGORIAS, USANDO $categories[2], $categories[3], etc. -->
                    <!-- Como você tem muitas categorias, vou mostrar só as primeiras como exemplo -->
                    
                </div>
            </div>
        </section>
    </div>

    <?php include 'views/partials/footer.php'; ?>
    <script src="assets/js/categories.js"></script>
</body>
</html>
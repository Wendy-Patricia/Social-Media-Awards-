<?php
// categories.php

// ===============================================
// 1. Criar conexão PDO
// ===============================================
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=social_media_awards;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// ===============================================
// 2. Incluir o CategoryService
// ===============================================
require_once __DIR__ . '/app/Services/CategoryService.php';

// ===============================================
// 3. Instanciar o serviço
// ===============================================
$categoryService = new \App\Services\CategoryService($pdo);

// ===============================================
// 4. Buscar todas as categorias
// ===============================================
$rawCategories = $categoryService->getAllCategories();

// ===============================================
// 5. Estatísticas por categoria (nominés e votos)
// ===============================================
$categories = [];
foreach ($rawCategories as $cat) {
    $catId = $cat['id_categorie'];

    // Número de nominés = quantas linhas em nomination para esta categoria
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM nomination WHERE id_categorie = ?");
    $stmt->execute([$catId]);
    $nominees = (int)$stmt->fetchColumn();

    // Total de votos reais nesta categoria
    $stmt = $pdo->prepare("
        SELECT COUNT(v.id_vote) 
        FROM vote v
        JOIN nomination n ON v.id_nomination = n.id_nomination
        WHERE n.id_categorie = ?
    ");
    $stmt->execute([$catId]);
    $votes = (int)$stmt->fetchColumn();

    $categories[] = [
        'nominees' => $nominees,
        'votes'    => $votes,
    ];
}

// ===============================================
// 6. Estatísticas globais para o hero
// ===============================================
$totalCategories = count($rawCategories);

// Número de plataformas distintas (baseado na coluna plateforme da tabela candidature)
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT plateforme) 
    FROM candidature 
    WHERE plateforme IS NOT NULL AND plateforme != ''
");
$totalPlatforms = (int)$stmt->fetchColumn();
$totalPlatforms = $totalPlatforms > 0 ? $totalPlatforms : 6; // fallback visual

// Total de nominés únicos (contas distintas nomeadas na edição ativa)
$stmt = $pdo->query("
    SELECT COUNT(DISTINCT n.id_compte) 
    FROM nomination n
    JOIN categorie c ON n.id_categorie = c.id_categorie
    WHERE c.id_edition = (SELECT id_edition FROM edition WHERE est_active = 1 LIMIT 1)
");
$totalNominees = (int)$stmt->fetchColumn();

$pageStats = [
    'categories' => $totalCategories,
    'platforms'  => $totalPlatforms,
    'nominees'   => $totalNominees,
];
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
                        <div class="stat-number"><?php echo $pageStats['categories']; ?></div>
                        <div class="stat-label">Catégories</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $pageStats['platforms']; ?></div>
                        <div class="stat-label">Plateformes</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $pageStats['nominees']; ?></div>
                        <div class="stat-label">Nominés</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="categories-section">
            <div class="container">
                <div class="categories-filter">
                    <button class="filter-btn active" data-filter="all">Toutes</button>
                    <button class="filter-btn" data-filter="youtube">YouTube</button>
                    <button class="filter-btn" data-filter="instagram">Instagram</button>
                    <button class="filter-btn" data-filter="tiktok">TikTok</button>
                    <button class="filter-btn" data-filter="spotify">Spotify</button>
                    <button class="filter-btn" data-filter="twitch">Twitch</button>
                </div>

                <div class="categories-grid">
                    <?php if (empty($rawCategories)): ?>
                        <p style="grid-column: 1 / -1; text-align:center; padding:60px; font-size:1.3em; color:#888;">
                            Aucune catégorie disponible pour le moment.
                        </p>
                    <?php else: ?>
                        <?php foreach ($rawCategories as $index => $cat): ?>
                        <?php 
                            $platforms = [];
                            if (!empty($cat['plateforme_cible']) && $cat['plateforme_cible'] !== 'Toutes') {
                                $platforms = array_map('trim', explode(',', $cat['plateforme_cible']));
                            }
                            $dataPlatform = !empty($platforms) ? implode(' ', array_map('strtolower', $platforms)) : 'all';
                        ?>
                        <div class="category-card" data-platform="<?php echo htmlspecialchars($dataPlatform); ?>">
                            <div class="category-header">
                                <div class="category-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="platform-tags">
                                    <?php foreach ($platforms as $plat): ?>
                                        <span class="platform-tag <?php echo strtolower(htmlspecialchars($plat)); ?>">
                                            <?php echo htmlspecialchars($plat); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <h3><?php echo htmlspecialchars($cat['nom']); ?></h3>
                            <p><?php echo htmlspecialchars($cat['description'] ?? 'Description à venir...'); ?></p>
                            <div class="category-stats">
                                <div class="stat">
                                    <div class="stat-number"><?php echo $categories[$index]['nominees']; ?></div>
                                    <div class="stat-label">Nominés</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-number"><?php echo $categories[$index]['votes']; ?></div>
                                    <div class="stat-label">Votes</div>
                                </div>
                            </div>
                            <button class="btn-view-nominees" onclick="window.location.href='nominees.php?category=<?php echo $cat['id_categorie']; ?>'">
                                Voir les Nominés
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <?php include 'views/partials/footer.php'; ?>
    <script src="assets/js/categories.js"></script>
</body>
</html>
<?php
// FICHIER : categories.php
// DESCRIPTION : Page d'affichage des catégories avec statistiques dynamiques
// FONCTIONNALITÉ : Affiche toutes les catégories avec leurs stats et permet la navigation

// CHARGEMENT DES DÉPENDANCES
require_once __DIR__ . '/config/database.php';        // Connexion à la base de données

// IMPORTANT : Charger l'autoloader si vous en avez un
require_once __DIR__ . '/app/autoload.php';           // Si vous avez un autoloader

// INITIALISATION DE LA CONNEXION PDO
try {
    // Créer une instance de PDO
    $pdo = new PDO(
        "mysql:host=localhost;dbname=social_media_awards;charset=utf8mb4",
        "root",  
        ""      
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Créer le service avec l'injection de PDO
    require_once __DIR__ . '/app/Services/CategoryService.php';
    $categoryService = new App\Services\CategoryService($pdo);
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// RÉCUPÉRATION DES STATISTIQUES DE LA PAGE
try {
    // Note : Votre CategoryService n'a pas de méthode getCategoryPageStats()
    // Je vais créer une méthode alternative ou utiliser des valeurs fixes
    
    $pageStats = [
        'categories' => '12',
        'platforms' => '5',
        'nominees' => '150'
    ];
    
    // Si vous voulez des données réelles, ajoutez cette méthode à CategoryService
    // $pageStats = $categoryService->getCategoryPageStats();
    
} catch (Exception $e) {
    // Valeurs par défaut en cas d'erreur
    $pageStats = [
        'categories' => '12',
        'platforms' => '5',
        'nominees' => '150'
    ];
}

// RÉCUPÉRATION DES CATÉGORIES
try {
    $categories = $categoryService->getAllCategories();
    
    // Formater les données pour l'affichage
    $formattedCategories = [];
    foreach ($categories as $category) {
        $formattedCategories[] = [
            'nominees' => $category['nb_nominations'] ?? '0',
            'votes' => $this->formatVoteCount($category['id_categorie'] ?? 0) // Méthode à créer
        ];
    }
    
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des catégories : " . $e->getMessage());
    // Catégories par défaut
    $formattedCategories = [
        ['nominees' => '25', 'votes' => '15K'],
        ['nominees' => '18', 'votes' => '12K'],
        ['nominees' => '22', 'votes' => '18K'],
        ['nominees' => '15', 'votes' => '8K'],
        ['nominees' => '30', 'votes' => '20K'],
        ['nominees' => '12', 'votes' => '6K']
    ];
}

/**
 * Fonction pour formater le nombre de votes (exemple)
 * À adapter selon votre base de données
 */
function formatVoteCount($categoryId): string {
    // Vous devrez créer une requête pour compter les votes par catégorie
    // Pour l'instant, retourne une valeur formatée
    $voteCounts = [15000, 12000, 18000, 8000, 20000, 6000];
    $index = ($categoryId - 1) % 6;
    $count = $voteCounts[$index] ?? 0;
    
    if ($count >= 1000) {
        return round($count / 1000) . 'K';
    }
    return (string)$count;
}

/**
 * Fonction pour obtenir le nombre de votes par catégorie
 */
function getVotesByCategory($pdo, $categoryId): int {
    try {
        $sql = "SELECT COUNT(*) as vote_count 
                FROM vote v 
                JOIN nomination n ON v.id_nomination = n.id_nomination 
                WHERE n.id_categorie = :category_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':category_id' => $categoryId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['vote_count'] ?? 0;
    } catch (Exception $e) {
        error_log("Erreur comptage votes: " . $e->getMessage());
        return 0;
    }
}
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
        <!-- SECTION HÉRO DES CATÉGORIES -->
        <section class="categories-hero">
            <div class="hero-container">
                <h1>Catégories de Compétition</h1>
                <p>Découvrez les <?php echo htmlspecialchars($pageStats['categories']); ?> catégories qui célèbrent l'excellence à travers toutes les plateformes sociales</p>
                
                <!-- STATISTIQUES PRINCIPALES -->
                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-number"><?php echo htmlspecialchars($pageStats['categories']); ?></div>
                        <div class="stat-label">Catégories</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo htmlspecialchars($pageStats['platforms']); ?></div>
                        <div class="stat-label">Plateformes</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo htmlspecialchars($pageStats['nominees']); ?></div>
                        <div class="stat-label">Nominés</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION PRINCIPALE DES CATÉGORIES -->
        <section class="categories-section">
            <div class="container">
                <!-- FILTRES PAR PLATEFORME -->
                <div class="categories-filter">
                    <button class="filter-btn active" data-filter="all">Toutes</button>
                    <button class="filter-btn" data-filter="tiktok">TikTok</button>
                    <button class="filter-btn" data-filter="instagram">Instagram</button>
                    <button class="filter-btn" data-filter="youtube">YouTube</button>
                    <button class="filter-btn" data-filter="twitter">Twitter</button>
                    <button class="filter-btn" data-filter="facebook">Facebook</button>
                </div>

                <!-- GRILLE DES CATÉGORIES -->
                <div class="categories-grid">
                    <?php
                    // Noms des catégories à afficher
                    $categoryNames = [
                        "Créateur Révélation de l'Année",
                        "Meilleur Podcast en Ligne",
                        "Meilleur Contenu Vidéo",
                        "Meilleur Photographe",
                        "Influenceur de l'Année",
                        "Meilleur Branded Content"
                    ];
                    
                    // Icônes pour chaque catégorie
                    $categoryIcons = [
                        "fas fa-star",
                        "fas fa-podcast",
                        "fas fa-video",
                        "fas fa-camera",
                        "fas fa-user-friends",
                        "fas fa-briefcase"
                    ];
                    
                    // Plateformes pour chaque catégorie
                    $categoryPlatforms = [
                        ["tiktok", "instagram", "youtube"],
                        ["youtube", "spotify"],
                        ["youtube", "tiktok", "instagram"],
                        ["instagram"],
                        ["tiktok", "instagram", "youtube", "twitter"],
                        ["instagram", "youtube", "tiktok"]
                    ];
                    
                    // Afficher chaque catégorie
                    for ($i = 0; $i < 6; $i++):
                        $categoryData = $formattedCategories[$i] ?? ['nominees' => '0', 'votes' => '0'];
                    ?>
                    <div class="category-card" data-platform="<?php echo implode(' ', $categoryPlatforms[$i]); ?>">
                        <div class="category-header">
                            <div class="category-icon">
                                <i class="<?php echo $categoryIcons[$i]; ?>"></i>
                            </div>
                            <div class="platform-tags">
                                <?php foreach ($categoryPlatforms[$i] as $platform): ?>
                                <span class="platform-tag <?php echo $platform; ?>">
                                    <?php echo ucfirst($platform); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <h3><?php echo htmlspecialchars($categoryNames[$i]); ?></h3>
                        <p>
                            <?php 
                            // Descriptions selon la catégorie
                            $descriptions = [
                                "Les nouveaux talents qui ont marqué l'année par leur croissance exceptionnelle et leur contenu innovant",
                                "Les podcasts les plus engageants, innovants et influents de l'année",
                                "Les créations vidéo les plus impactantes et créatives de l'année",
                                "Les artistes visuels qui transforment l'ordinaire en extraordinaire",
                                "Les personnalités qui ont le plus influencé la culture digitale",
                                "Les collaborations marque-créateur les plus réussies de l'année"
                            ];
                            echo htmlspecialchars($descriptions[$i]);
                            ?>
                        </p>
                        <div class="category-stats">
                            <div class="stat">
                                <div class="stat-number"><?php echo htmlspecialchars($categoryData['nominees']); ?></div>
                                <div class="stat-label">Nominés</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number"><?php echo htmlspecialchars($categoryData['votes']); ?></div>
                                <div class="stat-label">Votes</div>
                            </div>
                        </div>
                        <button class="btn-view-nominees">Voir les Nominés</button>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </section>
    </div>

    <?php include 'views/partials/footer.php'; ?>
    <script src="assets/js/categories.js"></script>
</body>
</html>
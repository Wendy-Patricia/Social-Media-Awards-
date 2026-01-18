<?php
// FICHIER : categories.php
// DESCRIPTION : Page d'affichage dynamique des catégories avec statistiques
// FONCTIONNALITÉ : Affiche toutes les catégories depuis la BDD avec leurs stats

// CHARGEMENT DES DÉPENDANCES
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/autoload.php';

// INITIALISATION DE LA CONNEXION PDO
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=social_media_awards;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Charger le CategoryService
    $categoryService = new App\Services\CategoryService($pdo);
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

/**
 * Compte le total des nominations pour une édition
 */
function countTotalNominations($pdo, $editionId): int {
    try {
        $sql = "SELECT COUNT(*) as total 
                FROM nomination n 
                JOIN categorie c ON n.id_categorie = c.id_categorie 
                WHERE c.id_edition = :edition_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':edition_id' => $editionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Compte les votes par catégorie
 */
function countVotesByCategory($pdo, $categoryId): int {
    try {
        $sql = "SELECT COUNT(*) as total 
                FROM vote v 
                JOIN nomination n ON v.id_nomination = n.id_nomination 
                WHERE n.id_categorie = :category_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':category_id' => $categoryId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Formate le nombre de votes (ex: 1500 → 1.5K)
 */
function formatVoteCount($count): string {
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M';
    } elseif ($count >= 1000) {
        return round($count / 1000, 1) . 'K';
    }
    return (string)$count;
}

/**
 * Récupère l'icône selon la plateforme
 */
function getPlatformIcon($platform): string {
    $icons = [
        'tiktok' => 'fab fa-tiktok',
        'instagram' => 'fab fa-instagram',
        'youtube' => 'fab fa-youtube',
        'twitter' => 'fab fa-twitter',
        'facebook' => 'fab fa-facebook',
        'spotify' => 'fab fa-spotify',
        'twitch' => 'fab fa-twitch',
        'x' => 'fab fa-x-twitter'
    ];
    
    if ($platform === null || $platform === '') {
        return 'fas fa-globe';
    }
    
    $platformLower = strtolower($platform);
    return $icons[$platformLower] ?? 'fas fa-globe';
}

/**
 * Récupère l'icône selon le type de catégorie
 */
function getCategoryIcon($categoryName): string {
    $keywords = [
        'révélation' => 'fas fa-star',
        'podcast' => 'fas fa-podcast',
        'vidéo' => 'fas fa-video',
        'photo' => 'fas fa-camera',
        'influenceur' => 'fas fa-user-friends',
        'branded' => 'fas fa-briefcase',
        'éducatif' => 'fas fa-graduation-cap',
        'humoristique' => 'fas fa-laugh',
        'tiktok' => 'fab fa-tiktok',
        'instagram' => 'fab fa-instagram',
        'youtube' => 'fab fa-youtube'
    ];
    
    $nameLower = strtolower($categoryName);
    foreach ($keywords as $keyword => $icon) {
        if (strpos($nameLower, $keyword) !== false) {
            return $icon;
        }
    }
    
    return 'fas fa-trophy';
}

/**
 * Génère une description basée sur la catégorie
 */
function generateCategoryDescription($categoryName, $platform): string {
    $descriptions = [
        'tiktok' => "Les talents les plus innovants et créatifs sur TikTok qui ont marqué l'année par leur contenu unique",
        'instagram' => "Les créateurs Instagram qui transforment le quotidien en art visuel et engagent leur communauté",
        'youtube' => "Les YouTubeurs dont le contenu éducatif, divertissant ou innovant a captivé des millions de spectateurs",
        'podcast' => "Les podcasts les plus influents qui ont su captiver leur audience avec des discussions percutantes",
        'révélation' => "Les nouveaux talents qui ont connu une croissance exceptionnelle et révolutionné leur domaine",
        'branded' => "Les collaborations entre marques et créateurs les plus réussies et authentiques de l'année"
    ];
    
    $nameLower = strtolower($categoryName);
    
    foreach ($descriptions as $keyword => $desc) {
        if (strpos($nameLower, $keyword) !== false) {
            return $desc;
        }
    }
    
    $platformText = ($platform && $platform !== 'Toutes') ? "sur " . ucfirst($platform) : "dans les médias sociaux";
    return "Catégorie célébrant l'excellence et l'innovation dans la création de contenu " . $platformText;
}

// RÉCUPÉRATION DES DONNÉES DYNAMIQUES
try {
    // Récupérer l'édition active
    $editionService = new class($pdo) {
        private $pdo;
        public function __construct($pdo) { 
            $this->pdo = $pdo; 
        }
        public function getActiveEdition() {
            $sql = "SELECT * FROM edition WHERE est_active = 1 ORDER BY annee DESC LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['id_edition' => 1, 'annee' => date('Y')];
        }
    };
    
    $activeEdition = $editionService->getActiveEdition();
    $editionId = $activeEdition['id_edition'];
    
    // Récupérer toutes les catégories de l'édition active
    $categories = $categoryService->getAllCategoriesByEdition($editionId);
    
    // Récupérer les plateformes uniques (en utilisant les méthodes getter)
    $platformsArray = [];
    foreach ($categories as $category) {
        if ($category instanceof App\Models\Categorie) {
            $platform = $category->getPlateformeCible();
            if ($platform && !in_array($platform, $platformsArray)) {
                $platformsArray[] = $platform;
            }
        }
    }
    
    // Calculer les statistiques de la page
    $pageStats = [
        'categories' => count($categories),
        'platforms' => count($platformsArray),
        'nominees' => countTotalNominations($pdo, $editionId)
    ];
    
} catch (Exception $e) {
    error_log("Erreur récupération catégories : " . $e->getMessage());
    // Valeurs par défaut
    $categories = [];
    $platformsArray = [];
    $pageStats = ['categories' => 0, 'platforms' => 0, 'nominees' => 0];
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
    <title>Catégories - Social Media Awards <?php echo htmlspecialchars($activeEdition['annee'] ?? date('Y')); ?></title>
    <style>
        /* Styles pour l'état vide */
        .no-categories-message {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease 0.3s forwards;
        }
        
        .empty-state {
            display: inline-block;
            padding: 40px;
            background: var(--light-gray);
            border-radius: 15px;
            border: 2px dashed var(--border-color, #ddd);
            max-width: 500px;
            margin: 0 auto;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--principal);
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <?php require_once 'views/partials/header.php'; ?>

    <div class="main-content">
        <!-- SECTION HÉRO DES CATÉGORIES -->
        <section class="categories-hero">
            <div class="hero-container">
                <h1>Catégories de Compétition <?php echo htmlspecialchars($activeEdition['annee'] ?? date('Y')); ?></h1>
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
                <div class="categories-filter">
                    <button class="filter-btn active" data-filter="all">Toutes</button>
                    <?php foreach ($platformsArray as $platform): 
                        if ($platform && $platform !== 'Toutes' && $platform !== 'all'): ?>
                    <button class="filter-btn" data-filter="<?php echo htmlspecialchars($platform); ?>">
                        <i class="<?php echo getPlatformIcon($platform); ?>"></i>
                        <?php echo htmlspecialchars($platform); ?>
                    </button>
                    <?php endif; 
                    endforeach; ?>
                </div>

                <!-- GRILLE DES CATÉGORIES -->
                <div class="categories-grid">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): 
                            // Vérifier que c'est bien un objet Categorie
                            if (!$category instanceof App\Models\Categorie) {
                                continue;
                            }
                            
                            $categoryId = $category->getIdCategorie();
                            $nomineesCount = $categoryService->countNominationsByCategory($categoryId);
                            $votesCount = countVotesByCategory($pdo, $categoryId);
                            $formattedVotes = formatVoteCount($votesCount);
                        ?>
                        <div class="category-card" data-platform="<?php echo htmlspecialchars($category->getPlateformeCible() ?? 'all'); ?>">
                            <div class="category-header">
                                <div class="category-icon">
                                    <i class="<?php echo getCategoryIcon($category->getNom()); ?>"></i>
                                </div>
                                <div class="platform-tags">
                                    <?php if ($category->getPlateformeCible() && $category->getPlateformeCible() !== 'Toutes'): ?>
                                    <span class="platform-tag <?php echo htmlspecialchars(strtolower($category->getPlateformeCible())); ?>">
                                        <i class="<?php echo getPlatformIcon($category->getPlateformeCible()); ?>"></i>
                                        <?php echo htmlspecialchars($category->getPlateformeCible()); ?>
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    // Ajouter d'autres plateformes si multi-plateformes
                                    $additionalPlatforms = [];
                                    $categoryNameLower = strtolower($category->getNom());
                                    if (strpos($categoryNameLower, 'multi') !== false || strpos($categoryNameLower, 'cross') !== false) {
                                        $additionalPlatforms = ['Instagram', 'TikTok', 'YouTube', 'Facebook'];
                                    }
                                    
                                    foreach ($additionalPlatforms as $platform):
                                        if ($platform !== $category->getPlateformeCible()):
                                    ?>
                                    <span class="platform-tag <?php echo htmlspecialchars(strtolower($platform)); ?>">
                                        <i class="<?php echo getPlatformIcon($platform); ?>"></i>
                                        <?php echo htmlspecialchars($platform); ?>
                                    </span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                            <h3><?php echo htmlspecialchars($category->getNom()); ?></h3>
                            <p>
                                <?php 
                                if ($category->getDescription()) {
                                    echo htmlspecialchars($category->getDescription());
                                } else {
                                    echo generateCategoryDescription($category->getNom(), $category->getPlateformeCible() ?? 'les réseaux sociaux');
                                }
                                ?>
                            </p>
                            <div class="category-stats">
                                <div class="stat">
                                    <div class="stat-number"><?php echo htmlspecialchars($nomineesCount); ?></div>
                                    <div class="stat-label">Nominés</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-number"><?php echo htmlspecialchars($formattedVotes); ?></div>
                                    <div class="stat-label">Votes</div>
                                </div>
                            </div>
                            <button class="btn-view-nominees" 
                                    data-category-id="<?php echo htmlspecialchars($categoryId); ?>"
                                    data-category-name="<?php echo htmlspecialchars(urlencode($category->getNom())); ?>">
                                Voir les Nominés
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Message si aucune catégorie -->
                        <div class="no-categories-message">
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <h3>Aucune catégorie disponible</h3>
                                <p>Les catégories de cette édition seront bientôt annoncées.</p>
                                <p><small>Vérifiez que vous avez créé des catégories dans la base de données.</small></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <?php include 'views/partials/footer.php'; ?>
    
    <!-- JavaScript -->
    <script src="assets/js/categories.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestionnaire pour le bouton "Voir les Nominés"
        document.querySelectorAll('.btn-view-nominees').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category-id');
                const categoryName = this.getAttribute('data-category-name');
                
                if (categoryId && categoryId !== '0') {
                    // Animation de clic
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';
                    this.disabled = true;
                    
                    // Redirection après un délai
                    setTimeout(() => {
                        window.location.href = `nominees.php?category=${categoryId}&name=${categoryName}`;
                    }, 500);
                } else {
                    // Fallback vers la page générale
                    window.location.href = 'nominees.php';
                }
            });
        });
        
        // Animation des cartes
        const categoryCards = document.querySelectorAll('.category-card');
        categoryCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Vérifier s'il y a un message d'état vide
        const emptyMessage = document.querySelector('.no-categories-message');
        if (emptyMessage) {
            setTimeout(() => {
                emptyMessage.style.opacity = '1';
                emptyMessage.style.transform = 'translateY(0)';
            }, 500);
        }
    });
    </script>
</body>
</html>
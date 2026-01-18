<?php
/**
 * PAGE : categories.php
 * DESCRIPTION : Page d'affichage dynamique des catégories de compétition avec statistiques
 * RESPONSABILITÉS :
 * - Afficher toutes les catégories depuis la base de données
 * - Filtrer les catégories par plateforme
 * - Calculer et afficher les statistiques (nominations, votes)
 * - Gérer les états vides et les erreurs
 * - Fournir une interface utilisateur interactive
 */

// CHARGEMENT DES DÉPENDANCES POUR LA BASE DE DONNÉES ET L'AUTOLOAD
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/autoload.php';

/**
 * INITIALISATION DE LA CONNEXION À LA BASE DE DONNÉES
 * Établit la connexion PDO et configure le mode d'erreur
 * @throws PDOException Si la connexion échoue
 */
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=social_media_awards;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // INSTANCIATION DU SERVICE DES CATÉGORIES
    $categoryService = new App\Services\CategoryService($pdo);
    
} catch (PDOException $e) {
    // GESTION DES ERREURS DE CONNEXION
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

/**
 * Compte le total des nominations pour une édition spécifique
 * 
 * @param PDO $pdo Instance de connexion à la base de données
 * @param int $editionId Identifiant de l'édition
 * @return int Nombre total de nominations
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
        // RETOURNE 0 EN CAS D'ERREUR
        return 0;
    }
}

/**
 * Compte les votes pour une catégorie spécifique
 * 
 * @param PDO $pdo Instance de connexion à la base de données
 * @param int $categoryId Identifiant de la catégorie
 * @return int Nombre de votes pour la catégorie
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
        // RETOURNE 0 EN CAS D'ERREUR
        return 0;
    }
}

/**
 * Formate un nombre de votes pour un affichage lisible
 * Convertit les grands nombres en format abrégé (K, M)
 * 
 * @param int $count Nombre de votes à formater
 * @return string Nombre formaté
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
 * Récupère l'icône FontAwesome correspondant à une plateforme
 * 
 * @param string|null $platform Nom de la plateforme
 * @return string Classe CSS de l'icône
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
    
    // UTILISE UNE ICÔNE GLOBALE SI AUCUNE PLATEFORME N'EST SPÉCIFIÉE
    if ($platform === null || $platform === '') {
        return 'fas fa-globe';
    }
    
    $platformLower = strtolower($platform);
    return $icons[$platformLower] ?? 'fas fa-globe';
}

/**
 * Détermine l'icône appropriée selon le nom de la catégorie
 * Utilise des mots-clés pour associer des icônes spécifiques
 * 
 * @param string $categoryName Nom de la catégorie
 * @return string Classe CSS de l'icône
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
    
    // ICÔNE PAR DÉFAUT POUR LES CATÉGORIES NON RECONNUES
    return 'fas fa-trophy';
}

/**
 * Génère une description automatique basée sur le nom de la catégorie et la plateforme
 * 
 * @param string $categoryName Nom de la catégorie
 * @param string $platform Plateforme cible
 * @return string Description générée
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
    
    // DESCRIPTION GÉNÉRIQUE POUR LES CATÉGORIES NON RECONNUES
    $platformText = ($platform && $platform !== 'Toutes') ? "sur " . ucfirst($platform) : "dans les médias sociaux";
    return "Catégorie célébrant l'excellence et l'innovation dans la création de contenu " . $platformText;
}

/**
 * RÉCUPÉRATION ET TRAITEMENT DES DONNÉES DYNAMIQUES
 * Cette section gère la récupération des catégories, plateformes et statistiques
 */
try {
    // SERVICE POUR RÉCUPÉRER L'ÉDITION ACTIVE
    $editionService = new class($pdo) {
        private $pdo;
        
        /**
         * Constructeur du service d'édition
         * @param PDO $pdo Instance de connexion à la base de données
         */
        public function __construct($pdo) { 
            $this->pdo = $pdo; 
        }
        
        /**
         * Récupère l'édition active actuelle
         * @return array Informations de l'édition active
         */
        public function getActiveEdition() {
            $sql = "SELECT * FROM edition WHERE est_active = 1 ORDER BY annee DESC LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['id_edition' => 1, 'annee' => date('Y')];
        }
    };
    
    // RÉCUPÉRATION DE L'ÉDITION ACTIVE
    $activeEdition = $editionService->getActiveEdition();
    $editionId = $activeEdition['id_edition'];
    
    // RÉCUPÉRATION DE TOUTES LES CATÉGORIES DE L'ÉDITION ACTIVE
    $categories = $categoryService->getAllCategoriesByEdition($editionId);
    
    // EXTRACTION DES PLATEFORMES UNIQUES POUR LES FILTRES
    $platformsArray = [];
    foreach ($categories as $category) {
        if ($category instanceof App\Models\Categorie) {
            $platform = $category->getPlateformeCible();
            if ($platform && !in_array($platform, $platformsArray)) {
                $platformsArray[] = $platform;
            }
        }
    }
    
    // CALCUL DES STATISTIQUES GLOBALES DE LA PAGE
    $pageStats = [
        'categories' => count($categories),
        'platforms' => count($platformsArray),
        'nominees' => countTotalNominations($pdo, $editionId)
    ];
    
} catch (Exception $e) {
    /**
     * GESTION DES ERREURS DE RÉCUPÉRATION DES DONNÉES
     * En cas d'erreur, initialise les variables avec des valeurs par défaut
     */
    error_log("Erreur récupération catégories : " . $e->getMessage());
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
    
    <!-- CHARGEMENT DES FEUILLES DE STYLE -->
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/categories.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- TITRE DYNAMIQUE INCLUANT L'ANNÉE DE L'ÉDITION -->
    <title>Catégories - Social Media Awards <?php echo htmlspecialchars($activeEdition['annee'] ?? date('Y')); ?></title>
    
    <!-- STYLES POUR L'ÉTAT VIDE (QUAND AUCUNE CATÉGORIE N'EST DISPONIBLE) -->
    <style>
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
        
        /* ANIMATION D'APPARITION PROGRESSIVE */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- INCLUSION DE L'EN-TÊTE COMMUNE -->
    <?php require_once 'views/partials/header.php'; ?>

    <!-- CONTENU PRINCIPAL DE LA PAGE -->
    <div class="main-content">
        <!-- SECTION HÉRO - INTRODUCTION AVEC STATISTIQUES -->
        <section class="categories-hero">
            <div class="hero-container">
                <h1>Catégories de Compétition <?php echo htmlspecialchars($activeEdition['annee'] ?? date('Y')); ?></h1>
                <p>Découvrez les <?php echo htmlspecialchars($pageStats['categories']); ?> catégories qui célèbrent l'excellence à travers toutes les plateformes sociales</p>
                
                <!-- AFFICHAGE DES STATISTIQUES PRINCIPALES -->
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

        <!-- SECTION PRINCIPALE D'AFFICHAGE DES CATÉGORIES -->
        <section class="categories-section">
            <div class="container">
                <!-- BARRE DE FILTRAGE PAR PLATEFORME -->
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
                        <!-- BOUCLE D'AFFICHAGE DE CHAQUE CATÉGORIE -->
                        <?php foreach ($categories as $category): 
                            // VÉRIFICATION QUE L'OBJET EST BIEN UNE INSTANCE DE CATEGORIE
                            if (!$category instanceof App\Models\Categorie) {
                                continue;
                            }
                            
                            // CALCUL DES STATISTIQUES SPÉCIFIQUES À LA CATÉGORIE
                            $categoryId = $category->getIdCategorie();
                            $nomineesCount = $categoryService->countNominationsByCategory($categoryId);
                            $votesCount = countVotesByCategory($pdo, $categoryId);
                            $formattedVotes = formatVoteCount($votesCount);
                        ?>
                        <div class="category-card" data-platform="<?php echo htmlspecialchars($category->getPlateformeCible() ?? 'all'); ?>">
                            <!-- EN-TÊTE DE LA CARTE AVEC ICÔNE ET TAGS DE PLATEFORME -->
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
                                    /**
                                     * AJOUT DE PLATEFORMES SUPPLÉMENTAIRES POUR LES CATÉGORIES MULTI-PLATEFORMES
                                     * Détecte les catégories contenant "multi" ou "cross" dans leur nom
                                     */
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
                            
                            <!-- NOM ET DESCRIPTION DE LA CATÉGORIE -->
                            <h3><?php echo htmlspecialchars($category->getNom()); ?></h3>
                            <p>
                                <?php 
                                /**
                                 * AFFICHAGE DE LA DESCRIPTION
                                 * Utilise la description existante ou génère une description automatique
                                 */
                                if ($category->getDescription()) {
                                    echo htmlspecialchars($category->getDescription());
                                } else {
                                    echo generateCategoryDescription($category->getNom(), $category->getPlateformeCible() ?? 'les réseaux sociaux');
                                }
                                ?>
                            </p>
                            
                            <!-- STATISTIQUES DE LA CATÉGORIE (NOMINÉS ET VOTES) -->
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
                            
                            <!-- BOUTON POUR ACCÉDER AUX NOMINÉS DE LA CATÉGORIE -->
                            <button class="btn-view-nominees" 
                                    data-category-id="<?php echo htmlspecialchars($categoryId); ?>"
                                    data-category-name="<?php echo htmlspecialchars(urlencode($category->getNom())); ?>">
                                Voir les Nominés
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- MESSAGE D'ÉTAT VIDE - AFFICHÉ QUAND AUCUNE CATÉGORIE N'EST DISPONIBLE -->
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

    <!-- INCLUSION DU PIED DE PAGE COMMUN -->
    <?php include 'views/partials/footer.php'; ?>
    
    <!-- CHARGEMENT DU JAVASCRIPT SPÉCIFIQUE À LA PAGE -->
    <script src="assets/js/categories.js"></script>
    
    <!-- SCRIPT D'INTERACTIVITÉ EN LIGNE -->
    <script>
    /**
     * GESTIONNAIRE D'ÉVÉNEMENTS AU CHARGEMENT DE LA PAGE
     * Configure les interactions utilisateur et les animations
     */
    document.addEventListener('DOMContentLoaded', function() {
        /**
         * GESTION DES CLICS SUR LES BOUTONS "VOIR LES NOMINÉS"
         * Redirige vers la page des nominés avec les paramètres appropriés
         */
        document.querySelectorAll('.btn-view-nominees').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category-id');
                const categoryName = this.getAttribute('data-category-name');
                
                if (categoryId && categoryId !== '0') {
                    // ANIMATION DE CHARGEMENT SUR LE BOUTON
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';
                    this.disabled = true;
                    
                    // REDIRECTION APRÈS UN DÉLAI POUR PERMETTRE L'ANIMATION
                    setTimeout(() => {
                        window.location.href = `nominees.php?category=${categoryId}&name=${categoryName}`;
                    }, 500);
                } else {
                    // REDIRECTION VERS LA PAGE GÉNÉRALE DES NOMINÉS EN CAS D'ERREUR
                    window.location.href = 'nominees.php';
                }
            });
        });
        
        /**
         * ANIMATION D'APPARITION PROGRESSIVE DES CARTES DE CATÉGORIES
         * Applique un effet de fondu et de translation verticale
         */
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
        
        /**
         * GESTION DE L'ANIMATION DU MESSAGE D'ÉTAT VIDE
         * S'assure que le message apparaît avec une animation
         */
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
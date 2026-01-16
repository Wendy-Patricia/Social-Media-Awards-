<?php
// FICHIER : nominees.php
// DESCRIPTION : Page affichant les nominés avec données dynamiques
// FONCTIONNALITÉ : Affiche les nominés depuis la BDD avec système de vote intelligent

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/permissions.php';
require_once __DIR__ . '/config/database.php';

// INITIALISATION
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=social_media_awards;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// RÉCUPÉRATION DES DONNÉES
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$categoryName = isset($_GET['name']) ? urldecode($_GET['name']) : '';

try {
    // Service pour les nominés
    require_once __DIR__ . '/app/Services/NominationService.php';
    $nominationService = new App\Services\NominationService($pdo);
    
    // Récupérer les nominés selon le filtre
    if ($categoryFilter > 0) {
        $nominees = $nominationService->getNominationsByCategory($categoryFilter);
        $currentCategory = $nominationService->getCategoryById($categoryFilter);
        $categoryName = $currentCategory['nom'] ?? $categoryName;
    } else {
        $nominees = $nominationService->getAllNominations();
        $currentCategory = null;
    }
    
    // Récupérer toutes les catégories pour le filtre
    $allCategories = $nominationService->getAllCategories();
    
    // Récupérer les plateformes uniques
    $platforms = $nominationService->getAllPlatforms();
    
} catch (Exception $e) {
    error_log("Erreur récupération nominés: " . $e->getMessage());
    $nominees = [];
    $allCategories = [];
    $platforms = [];
    $currentCategory = null;
}

/**
 * Obtient le lien du bouton de vote selon l'état d'authentification
 */
function getVoteButtonLink($nominationId = 0, $categoryId = 0) {
    if (isAuthenticated()) {
        $userType = getUserType();
        if ($userType === 'voter') {
            // Électeur authentifié → page de vote
            $link = '/Social-Media-Awards-/views/user/Vote.php';
            $params = [];
            if ($nominationId > 0) $params[] = 'nomination=' . $nominationId;
            if ($categoryId > 0) $params[] = 'category=' . $categoryId;
            
            if (!empty($params)) {
                $link .= '?' . implode('&', $params);
            }
            return $link;
        } else {
            // Non-électeur → action désactivée
            return 'javascript:void(0)';
        }
    } else {
        // Non authentifié → login avec redirection
        $redirect = '/Social-Media-Awards-/views/user/Vote.php';
        $params = [];
        if ($nominationId > 0) $params[] = 'nomination=' . $nominationId;
        if ($categoryId > 0) $params[] = 'category=' . $categoryId;
        
        if (!empty($params)) {
            $redirect .= '?' . implode('&', $params);
        }
        
        return '/Social-Media-Awards-/views/login.php?redirect=' . urlencode($redirect);
    }
}

/**
 * Vérifie si l'utilisateur peut voter
 */
function canVote() {
    return isAuthenticated() && getUserType() === 'voter';
}

/**
 * Texte du bouton selon l'état
 */
function getVoteButtonText() {
    if (!isAuthenticated()) {
        return 'Connectez-vous pour voter';
    } elseif (getUserType() !== 'voter') {
        return 'Non éligible pour voter';
    } else {
        return 'Voter';
    }
}

/**
 * Classe CSS du bouton
 */
function getVoteButtonClass() {
    if (!isAuthenticated() || getUserType() !== 'voter') {
        return 'btn-vote btn-disabled';
    } else {
        return 'btn-vote';
    }
}

/**
 * Formate les statistiques (ex: 1500 → 1.5K)
 */
function formatStatNumber($number): string {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return (string)$number;
}

/**
 * Récupère l'icône de plateforme
 */
function getPlatformIcon($platform): string {
    $icons = [
        'tiktok' => 'fab fa-tiktok',
        'instagram' => 'fab fa-instagram',
        'youtube' => 'fab fa-youtube',
        'twitter' => 'fab fa-twitter',
        'facebook' => 'fab fa-facebook',
        'spotify' => 'fab fa-spotify'
    ];
    return $icons[strtolower($platform)] ?? 'fas fa-globe';
}

/**
 * Génère une description basée sur la catégorie et la plateforme
 */
function generateNomineeDescription($categoryName, $platform, $nomineeName): string {
    $descriptions = [
        'tiktok' => "Créateur TikTok innovant connu pour $nomineeName",
        'instagram' => "Influenceur Instagram avec un contenu visuel unique pour $nomineeName",
        'youtube' => "YouTubeur produisant du contenu de qualité sur $nomineeName",
        'podcast' => "Podcast engagant et informatif par $nomineeName",
        'révélation' => "Nouveau talent en pleine ascension : $nomineeName"
    ];
    
    $nameLower = strtolower($categoryName);
    
    foreach ($descriptions as $keyword => $desc) {
        if (strpos($nameLower, $keyword) !== false) {
            return $desc;
        }
    }
    
    return "Candidat exceptionnel dans la catégorie $categoryName";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/nominees.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Nominés - Social Media Awards</title>
    <style>
        /* Styles additionnels */
        .nominees-hero h1 {
            margin-bottom: 10px;
        }
        
        .category-badge {
            display: inline-block;
            background: var(--principal);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            margin: 10px 0;
            font-size: 0.9rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .nominee-stats {
            display: flex;
            gap: 15px;
            margin: 15px 0;
        }
        
        .nominee-stat {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gray);
        }
        
        .nominee-stat i {
            color: var(--principal);
        }
        
        .nominee-stat .stat-number {
            font-weight: bold;
            color: var(--dark);
        }
    </style>
</head>
<body>
    <?php require_once 'views/partials/header.php'; ?>

    <div class="main-content">
        <!-- SECTION HERO DES NOMINÉS -->
        <section class="nominees-hero">
            <div class="hero-container">
                <h1>
                    <?php if ($categoryName): ?>
                    Nominés : <?php echo htmlspecialchars($categoryName); ?>
                    <?php else: ?>
                    Nos Nominés
                    <?php endif; ?>
                </h1>
                
                <p>
                    <?php if ($categoryName): ?>
                    Découvrez les talents nominés dans cette catégorie
                    <?php else: ?>
                    Découvrez tous les talents exceptionnels sélectionnés
                    <?php endif; ?>
                </p>
                
                <?php if ($categoryName): ?>
                <div class="category-badge">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($categoryName); ?>
                </div>
                <?php endif; ?>
                
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un nominé..." id="searchInput">
                    </div>
                    
                    <select id="categoryFilter">
                        <option value="0">Toutes les catégories</option>
                        <?php foreach ($allCategories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['id_categorie']); ?>" 
                                <?php echo ($categoryFilter == $cat['id_categorie']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select id="platformFilter">
                        <option value="">Toutes les plateformes</option>
                        <?php foreach ($platforms as $platform): ?>
                        <option value="<?php echo htmlspecialchars($platform); ?>">
                            <?php echo ucfirst(htmlspecialchars($platform)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>

        <!-- GRILLE DES NOMINÉS -->
        <section class="nominees-section">
            <div class="container">
                <?php if (!empty($nominees)): ?>
                <div class="nominees-grid">
                    <?php foreach ($nominees as $nominee): 
                        $votesCount = $nominationService->countVotesForNomination($nominee['id_nomination']);
                        $categoryInfo = $nominationService->getCategoryById($nominee['id_categorie']);
                    ?>
                    <div class="nominee-card" 
                         data-category="<?php echo htmlspecialchars($categoryInfo['nom'] ?? ''); ?>"
                         data-platform="<?php echo htmlspecialchars($nominee['plateforme'] ?? ''); ?>"
                         data-name="<?php echo htmlspecialchars(strtolower($nominee['libelle'])); ?>">
                        
                        <div class="nominee-image">
                            <?php if (!empty($nominee['url_image'])): ?>
                            <img src="<?php echo htmlspecialchars($nominee['url_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($nominee['libelle']); ?>"
                                 onerror="this.src='assets/images/default-nominee.jpg'">
                            <?php else: ?>
                            <img src="assets/images/default-nominee.jpg" 
                                 alt="<?php echo htmlspecialchars($nominee['libelle']); ?>">
                            <?php endif; ?>
                            
                            <?php if (!empty($nominee['plateforme'])): ?>
                            <div class="platform-badge <?php echo htmlspecialchars($nominee['plateforme']); ?>">
                                <i class="<?php echo getPlatformIcon($nominee['plateforme']); ?>"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="nominee-info">
                            <h3><?php echo htmlspecialchars($nominee['libelle']); ?></h3>
                            
                            <?php if (!empty($categoryInfo)): ?>
                            <p class="nominee-category">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($categoryInfo['nom']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <p class="nominee-description">
                                <?php 
                                if (!empty($nominee['argumentaire'])) {
                                    echo htmlspecialchars(substr($nominee['argumentaire'], 0, 120)) . '...';
                                } else {
                                    echo generateNomineeDescription(
                                        $categoryInfo['nom'] ?? 'Catégorie',
                                        $nominee['plateforme'] ?? 'plateforme',
                                        $nominee['libelle']
                                    );
                                }
                                ?>
                            </p>
                            
                            <div class="nominee-stats">
                                <div class="nominee-stat">
                                    <i class="fas fa-heart"></i>
                                    <span class="stat-number"><?php echo formatStatNumber($votesCount); ?></span>
                                    <span class="stat-label">Votes</span>
                                </div>
                                
                                <?php if (!empty($nominee['plateforme'])): ?>
                                <div class="nominee-stat">
                                    <i class="<?php echo getPlatformIcon($nominee['plateforme']); ?>"></i>
                                    <span class="stat-label"><?php echo ucfirst(htmlspecialchars($nominee['plateforme'])); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- BOUTON DE VOTE ADAPTATIF -->
                            <a href="<?php echo getVoteButtonLink($nominee['id_nomination'], $nominee['id_categorie']); ?>" 
                               class="<?php echo getVoteButtonClass(); ?>"
                               onclick="return handleVoteClick(this, event, '<?php echo htmlspecialchars($nominee['libelle']); ?>')">
                                <i class="fas fa-vote-yea"></i>
                                <?php echo getVoteButtonText(); ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>Aucun nominé disponible</h3>
                    <p>
                        <?php if ($categoryFilter > 0): ?>
                        Aucun nominé dans cette catégorie pour le moment.
                        <?php else: ?>
                        Les nominations pour cette édition seront bientôt annoncées.
                        <?php endif; ?>
                    </p>
                    <?php if ($categoryFilter > 0): ?>
                    <a href="nominees.php" class="btn-view-nominees" style="margin-top: 20px;">
                        Voir tous les nominés
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <?php include 'views/partials/footer.php'; ?>
    
    <!-- JavaScript mis à jour -->
    <script src="assets/js/nominees.js"></script>
    
    <script>
    /**
     * Gère le clic sur un bouton de vote
     */
    function handleVoteClick(link, event, nomineeName) {
        const isDisabled = link.classList.contains('btn-disabled');
        
        if (isDisabled) {
            event.preventDefault();
            
            <?php if (!isAuthenticated()): ?>
                // UTILISATEUR NON AUTHENTIFIÉ
                if (confirm('Vous devez être connecté pour voter. Voulez-vous vous connecter maintenant?')) {
                    const currentPage = window.location.pathname + window.location.search;
                    window.location.href = '/Social-Media-Awards-/views/login.php?redirect=' + encodeURIComponent(currentPage);
                }
            <?php else: ?>
                // UTILISATEUR AUTHENTIFIÉ MAIS NON-ÉLECTEUR
                alert('Seuls les électeurs peuvent voter. Votre compte n\'a pas les permissions nécessaires.');
            <?php endif; ?>
            
            return false;
        }
        
        // Confirmation pour les électeurs
        if (nomineeName) {
            return confirm(`Confirmez-vous votre vote pour "${nomineeName}" ?`);
        }
        
        return true;
    }
    
    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du filtre par catégorie
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', function() {
                const categoryId = this.value;
                if (categoryId > 0) {
                    window.location.href = `nominees.php?category=${categoryId}`;
                } else {
                    window.location.href = 'nominees.php';
                }
            });
        }
        
        // Animation des cartes
        const nomineeCards = document.querySelectorAll('.nominee-card');
        nomineeCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Recherche en temps réel
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const cards = document.querySelectorAll('.nominee-card');
                
                cards.forEach(card => {
                    const nomineeName = card.querySelector('h3').textContent.toLowerCase();
                    if (nomineeName.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
        
        // Filtre par plateforme
        const platformFilter = document.getElementById('platformFilter');
        if (platformFilter) {
            platformFilter.addEventListener('change', function() {
                const platform = this.value;
                const cards = document.querySelectorAll('.nominee-card');
                
                cards.forEach(card => {
                    const cardPlatform = card.getAttribute('data-platform');
                    if (!platform || cardPlatform === platform) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });
    </script>
</body>
</html>
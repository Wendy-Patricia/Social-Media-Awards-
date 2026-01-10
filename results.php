<?php
// FICHIER : results.php  
// DESCRIPTION : Page affichant les résultats dynamiques des Social Media Awards
// FONCTIONNALITÉ : Affiche les gagnants, résultats par catégorie et statistiques en temps réel

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
    
    // Créer le service ResultsService
    require_once __DIR__ . '/app/Services/ResultsService.php';
    $resultsService = new App\Services\ResultsService($pdo);
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// RÉCUPÉRATION DES DONNÉES DYNAMIQUES
try {
    // Récupérer l'édition la plus récente
    $latestEdition = $resultsService->getLatestEdition();
    $editionId = $latestEdition['id_edition'] ?? 1;
    $editionYear = $latestEdition['annee'] ?? date('Y');
    
    // Récupérer les grands gagnants
    $grandWinners = $resultsService->getGrandWinners($editionId);
    
    // Récupérer les résultats par catégorie
    $categoryResults = $resultsService->getResultsByCategory($editionId);
    
    // Récupérer les statistiques globales
    $globalStats = $resultsService->getGlobalStatistics($editionId);
    
    // Récupérer la liste des éditions disponibles
    $availableEditions = $resultsService->getAvailableEditions();
    
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des résultats : " . $e->getMessage());
    // Valeurs par défaut en cas d'erreur
    $latestEdition = ['annee' => date('Y'), 'nom' => 'Social Media Awards'];
    $grandWinners = [];
    $categoryResults = [];
    $globalStats = [
        'total_votes' => 0,
        'total_categories' => 0,
        'total_nominations' => 0,
        'participation_rate' => 0
    ];
    $availableEditions = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/results.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Résultats - Social Media Awards <?php echo htmlspecialchars($editionYear); ?></title>
    
    <!-- Styles additionnels pour améliorations visuelles -->
    <style>
        /* Animation pour les cartes de résultats */
        .result-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Indicateur de chargement */
        .loading-indicator {
            display: none;
            text-align: center;
            padding: 20px;
            color: var(--principal);
        }
        
        .loading-indicator.active {
            display: block;
        }
        
        /* Amélioration de la hiérarchie visuelle */
        .winner-item {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .winner-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Badge pour nouvelles catégories */
        .new-badge {
            background: var(--secondary-pink);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
            margin-left: 8px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <?php require_once 'views/partials/header.php'; ?>

    <div class="main-content">
        <!-- SECTION HERO DES RÉSULTATS -->
        <section class="results-hero">
            <div class="container">
                <h1>Résultats <?php echo htmlspecialchars($editionYear); ?></h1>
                <p>Découvrez les gagnants de cette édition des Social Media Awards</p>
                
                <div class="edition-selector">
                    <select id="editionSelect" aria-label="Sélectionner une édition">
                        <?php foreach ($availableEditions as $edition): ?>
                        <option value="<?php echo htmlspecialchars($edition['id_edition']); ?>" 
                                <?php echo ($edition['id_edition'] == $editionId) ? 'selected' : ''; ?>>
                            Édition <?php echo htmlspecialchars($edition['annee']); ?> - <?php echo htmlspecialchars($edition['nom']); ?>
                        </option>
                        <?php endforeach; ?>
                        <?php if (empty($availableEditions)): ?>
                        <option value="1" selected>Édition <?php echo htmlspecialchars($editionYear); ?></option>
                        <?php endif; ?>
                    </select>
                    
                    <div class="vote-stats">
                        <div class="stat">
                            <div class="stat-number"><?php echo number_format($globalStats['total_votes']); ?></div>
                            <div class="stat-label">Votes Totaux</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number"><?php echo htmlspecialchars($globalStats['participation_rate']); ?>%</div>
                            <div class="stat-label">Participation</div>
                        </div>
                    </div>
                </div>
                
                <!-- Indicateur de chargement -->
                <div class="loading-indicator" id="loadingIndicator">
                    <i class="fas fa-spinner fa-spin"></i> Chargement des données...
                </div>
            </div>
        </section>

        <!-- SECTION GRANDS GAGNANTS -->
        <section class="winners-overview">
            <div class="container">
                <h2>Les Grands Gagnants</h2>
                
                <?php if (!empty($grandWinners)): ?>
                <div class="winners-grid">
                    <!-- Grand Gagnant (1er) -->
                    <?php if (isset($grandWinners[0])): ?>
                    <div class="grand-winner">
                        <div class="winner-crown">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="winner-image">
                            <img src="<?php echo htmlspecialchars($grandWinners[0]['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($grandWinners[0]['nom_nomination']); ?>"
                                 onerror="this.src='assets/images/default-winner.jpg'">
                        </div>
                        <div class="winner-info">
                            <h3><?php echo htmlspecialchars($grandWinners[0]['nom_nomination']); ?></h3>
                            <p class="winner-category"><?php echo htmlspecialchars($grandWinners[0]['categorie']); ?></p>
                            <div class="winner-stats">
                                <div class="stat">
                                    <div class="stat-number"><?php echo number_format($grandWinners[0]['total_votes']); ?></div>
                                    <div class="stat-label">Votes</div>
                                </div>
                                <div class="stat">
                                    <div class="stat-number">
                                        <?php 
                                        // Calculer le pourcentage si nous avons le total des votes
                                        $percentage = $globalStats['total_votes'] > 0 
                                            ? round(($grandWinners[0]['total_votes'] / $globalStats['total_votes']) * 100, 1)
                                            : 0;
                                        echo $percentage;
                                        ?>%
                                    </div>
                                    <div class="stat-label">Part des votes</div>
                                </div>
                            </div>
                            <?php if (!empty($grandWinners[0]['plateforme'])): ?>
                            <div class="platform-badge <?php echo htmlspecialchars($grandWinners[0]['plateforme']); ?>">
                                <i class="fab fa-<?php echo htmlspecialchars($grandWinners[0]['plateforme']); ?>"></i>
                                <?php echo ucfirst(htmlspecialchars($grandWinners[0]['plateforme'])); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Top des gagnants (2ème et 3ème) -->
                    <div class="top-winners">
                        <?php for ($i = 1; $i <= 2; $i++): ?>
                            <?php if (isset($grandWinners[$i])): ?>
                            <div class="top-winner">
                                <div class="winner-rank"><?php echo $i + 1; ?></div>
                                <div class="winner-image">
                                    <img src="<?php echo htmlspecialchars($grandWinners[$i]['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($grandWinners[$i]['nom_nomination']); ?>"
                                         onerror="this.src='assets/images/default-winner.jpg'">
                                </div>
                                <div class="winner-info">
                                    <h4><?php echo htmlspecialchars($grandWinners[$i]['nom_nomination']); ?></h4>
                                    <p><?php echo htmlspecialchars($grandWinners[$i]['categorie']); ?></p>
                                    <div class="votes"><?php echo number_format($grandWinners[$i]['total_votes']); ?> votes</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-trophy" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                    <h3 style="color: var(--gray);">En attente des premiers votes</h3>
                    <p>Les résultats seront disponibles après la période de vote.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- SECTION RÉSULTATS PAR CATÉGORIE -->
        <section class="category-results">
            <div class="container">
                <h2>Résultats par Catégorie</h2>
                
                <!-- Filtres par plateforme -->
                <div class="results-tabs">
                    <button class="tab-btn active" data-tab="all">Toutes</button>
                    <button class="tab-btn" data-tab="tiktok">TikTok</button>
                    <button class="tab-btn" data-tab="instagram">Instagram</button>
                    <button class="tab-btn" data-tab="youtube">YouTube</button>
                    <button class="tab-btn" data-tab="twitter">Twitter</button>
                    <button class="tab-btn" data-tab="facebook">Facebook</button>
                </div>

                <?php if (!empty($categoryResults)): ?>
                <div class="results-grid">
                    <?php foreach ($categoryResults as $category): ?>
                    <div class="result-card" data-platform="<?php echo htmlspecialchars($category['plateforme'] ?? 'all'); ?>">
                        <div class="category-header">
                            <h3><?php echo htmlspecialchars($category['categorie_nom']); ?></h3>
                            <?php if (!empty($category['plateforme'])): ?>
                            <span class="platform-tag <?php echo htmlspecialchars($category['plateforme']); ?>">
                                <?php echo ucfirst(htmlspecialchars($category['plateforme'])); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($category['winners'])): ?>
                        <div class="winners-list">
                            <?php foreach ($category['winners'] as $winner): ?>
                            <div class="winner-item <?php echo htmlspecialchars($winner['position']); ?>">
                                <div class="winner-rank"><?php echo htmlspecialchars($winner['medal']); ?></div>
                                <div class="winner-details">
                                    <h4><?php echo htmlspecialchars($winner['nom_nomination']); ?></h4>
                                    <p><?php echo number_format($winner['vote_count']); ?> votes</p>
                                </div>
                                <div class="vote-percentage"><?php echo htmlspecialchars($winner['vote_percentage'] ?? 0); ?>%</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="no-winners">
                            <p>Aucun vote enregistré pour cette catégorie.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="category-stats">
                            <div class="stat">
                                <span>Total votes:</span>
                                <strong><?php echo number_format($category['total_votes_categorie'] ?? 0); ?></strong>
                            </div>
                            <div class="stat">
                                <span>Nominations:</span>
                                <strong><?php echo htmlspecialchars($category['nb_nominations'] ?? 0); ?></strong>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-categories">
                    <i class="fas fa-folder-open" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
                    <h3 style="color: var(--gray);">Catégories en préparation</h3>
                    <p>Les catégories de cette édition seront bientôt disponibles.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- SECTION STATISTIQUES GLOBALES -->
        <section class="statistics-section">
            <div class="container">
                <h2>Statistiques Globales</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($globalStats['total_voters']); ?></div>
                            <div class="stat-label">Électeurs Actifs</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo htmlspecialchars($globalStats['total_categories']); ?></div>
                            <div class="stat-label">Catégories</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo htmlspecialchars($globalStats['total_nominations']); ?></div>
                            <div class="stat-label">Nominations</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo htmlspecialchars($globalStats['participation_rate']); ?>%</div>
                            <div class="stat-label">Taux de Participation</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'views/partials/footer.php'; ?>
    
    <!-- JavaScript amélioré -->
    <script src="assets/js/results.js"></script>
    
    <!-- Script pour le changement d'édition -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editionSelect = document.getElementById('editionSelect');
        const loadingIndicator = document.getElementById('loadingIndicator');
        
        if (editionSelect) {
            editionSelect.addEventListener('change', function() {
                const editionId = this.value;
                
                // Afficher l'indicateur de chargement
                loadingIndicator.classList.add('active');
                
                // Rediriger vers la même page avec le paramètre d'édition
                // (Dans une version future, cela pourrait être une requête AJAX)
                window.location.href = `results.php?edition=${editionId}`;
            });
        }
        
        // Animation pour les cartes au défilement
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observer les cartes de résultats
        document.querySelectorAll('.result-card, .stat-card, .top-winner').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    });
    </script>
</body>
</html>
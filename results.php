
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
    // Récupérer la dernière édition terminée par défaut
    $latestEdition = $resultsService->getLatestEdition();
    $editionId = $latestEdition['id_edition'] ?? 1;
    $editionYear = $latestEdition['annee'] ?? date('Y');
    
    // Vérifier le paramètre GET pour l'édition
    if (isset($_GET['edition']) && is_numeric($_GET['edition'])) {
        $requestedEditionId = (int)$_GET['edition'];
        $availableEditions = $resultsService->getFinishedEditions();
        
        // Vérifier si l'édition demandée est valide et terminée
        $isValid = false;
        foreach ($availableEditions as $edition) {
            if ($edition['id_edition'] == $requestedEditionId) {
                $isValid = true;
                $editionId = $requestedEditionId;
                // Récupérer les infos complètes de cette édition
                $sql = "SELECT * FROM edition WHERE id_edition = :editionId";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':editionId' => $editionId]);
                $latestEdition = $stmt->fetch(PDO::FETCH_ASSOC);
                $editionYear = $latestEdition['annee'];
                break;
            }
        }
        
        if (!$isValid) {
            // Si l'édition demandée n'est pas valide, utiliser la dernière terminée
            $latestEdition = $resultsService->getLatestEdition();
            $editionId = $latestEdition['id_edition'];
            $editionYear = $latestEdition['annee'];
        }
    }
    
    // Vérifier si l'édition est active (votes en cours)
    $editionStatus = $resultsService->getEditionStatus($editionId);
    $isEditionActive = $editionStatus['active'];
    $votesFinished = $editionStatus['votes_finished'];
    
    // Si l'édition est active ou si les votes ne sont pas encore commencés, 
    // on ne montre PAS les résultats
    if ($isEditionActive || !$votesFinished) {
        $grandWinners = [];
        $categoryResults = [];
        $globalStats = [
            'total_votes' => 0,
            'total_categories' => 0,
            'total_nominations' => 0,
            'participation_rate' => 0,
            'total_voters' => 0
        ];
        
        $votePeriodMessage = $isEditionActive 
            ? "Les votes sont en cours jusqu'au " . date('d/m/Y à H:i', strtotime($editionStatus['date_fin']))
            : "Les votes ne sont pas encore commencés";
        
    } else {
        // Édition terminée, on récupère tous les résultats
        $grandWinners = $resultsService->getGrandWinners($editionId);
        $categoryResults = $resultsService->getResultsByCategory($editionId);
        $globalStats = $resultsService->getGlobalStatistics($editionId);
        $votePeriodMessage = "Période de vote terminée";
    }
    
    // Récupérer uniquement les éditions terminées (pour les résultats)
    $availableEditions = $resultsService->getFinishedEditions();
    
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des résultats : " . $e->getMessage());
    // Valeurs par défaut en cas d'erreur
    $isEditionActive = false;
    $votesFinished = true;
    $latestEdition = ['annee' => date('Y'), 'nom' => 'Social Media Awards'];
    $grandWinners = [];
    $categoryResults = [];
    $globalStats = [
        'total_votes' => 0,
        'total_categories' => 0,
        'total_nominations' => 0,
        'participation_rate' => 0,
        'total_voters' => 0
    ];
    $availableEditions = [];
    $votePeriodMessage = "Statut indéterminé";
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
    <title>
        <?php if ($isEditionActive): ?>Votes en cours
        <?php elseif (!$votesFinished): ?>Votes à venir
        <?php else: ?>Résultats <?php echo htmlspecialchars($editionYear); ?>
        <?php endif; ?> - Social Media Awards
    </title>
    

</head>
<body class="<?php 
    echo $isEditionActive ? 'vote-active' : '';
    echo (!$votesFinished && !$isEditionActive) ? 'vote-not-started-page' : 'vote-ended';
?>">
    <?php require_once 'views/partials/header.php'; ?>

    <div class="main-content">
        <!-- SECTION HERO DES RÉSULTATS -->
        <section class="results-hero">
            <div class="global-container">
                <h1>
                    <?php if ($isEditionActive): ?>
                        <i class="fas fa-vote-yea"></i> Votes en cours
                    <?php elseif (!$votesFinished && !$isEditionActive): ?>
                        <i class="fas fa-clock"></i> Votes à venir
                    <?php else: ?>
                        <i class="fas fa-trophy"></i> Résultats <?php echo htmlspecialchars($editionYear); ?>
                    <?php endif; ?>
                </h1>
                
                <p>
                    <?php if ($isEditionActive): ?>
                        Les votes sont ouverts. Revenez après le <?php echo date('d/m/Y', strtotime($editionStatus['date_fin'])); ?> pour découvrir les résultats.
                    <?php elseif (!$votesFinished && !$isEditionActive): ?>
                        Les votes commenceront le <?php echo date('d/m/Y', strtotime($editionStatus['date_debut'])); ?>.
                    <?php else: ?>
                        Découvrez les gagnants de cette édition des Social Media Awards
                    <?php endif; ?>
                </p>
                
                <div class="edition-selector">
                    <select id="editionSelect" aria-label="Sélectionner une édition">
                        <?php if (!empty($availableEditions)): ?>
                            <?php foreach ($availableEditions as $edition): ?>
                                <?php 
                                $editionStat = $resultsService->getEditionStatus($edition['id_edition']);
                                $isActiveEdition = $editionStat['active'];
                                $isFinished = $editionStat['votes_finished'];
                                ?>
                            <option value="<?php echo htmlspecialchars($edition['id_edition']); ?>" 
                                    <?php echo ($edition['id_edition'] == $editionId) ? 'selected' : ''; ?>
                                    data-active="<?php echo $isActiveEdition ? '1' : '0'; ?>"
                                    data-finished="<?php echo $isFinished ? '1' : '0'; ?>">
                                Édition <?php echo htmlspecialchars($edition['annee']); ?> - 
                                <?php echo htmlspecialchars($edition['nom']); ?>
                                <?php if ($isActiveEdition): ?> (Votes en cours)<?php endif; ?>
                                <?php if (!$isFinished && !$isActiveEdition): ?> (À venir)<?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="<?php echo htmlspecialchars($editionId); ?>" selected>
                                Édition <?php echo htmlspecialchars($editionYear); ?>
                            </option>
                        <?php endif; ?>
                    </select>
                    
                    <?php if (!$isEditionActive && $votesFinished): ?>
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
                    <?php endif; ?>
                </div>
                
                <!-- Message si vote en cours -->
                <?php if ($isEditionActive): ?>
                <div class="vote-in-progress" id="voteInProgress">
                    <i class="fas fa-hourglass-half"></i>
                    <h2>Les votes sont en cours !</h2>
                    <p>Les résultats seront disponibles après la période de vote.</p>
                    <div class="countdown" id="countdownTimer">
                        Fin des votes le <?php echo date('d/m/Y à H:i', strtotime($editionStatus['date_fin'])); ?>
                    </div>
                </div>
                <?php elseif (!$votesFinished && !$isEditionActive): ?>
                <div class="vote-not-started" id="voteNotStarted">
                    <i class="fas fa-calendar-alt"></i>
                    <h2>Les votes n'ont pas encore commencé</h2>
                    <p>Les votes débuteront le <?php echo date('d/m/Y à H:i', strtotime($editionStatus['date_debut'])); ?></p>
                    <p>Revenez à cette date pour participer !</p>
                </div>
                <?php endif; ?>
                
                
            </div>
        </section>

        <!-- SECTION RÉSULTATS PAR CATÉGORIE (seulement si vote terminé) -->
        <?php if (!$isEditionActive && $votesFinished): ?>
        <section class="category-results">
            <div class="global-container">
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
                    <h3 style="color: var(--gray);">Aucune catégorie avec résultats</h3>
                    <p>Aucun vote n'a été enregistré pour les catégories de cette édition.</p>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- SECTION STATISTIQUES GLOBALES (toujours visible, mais avec données différentes) -->
        <section class="statistics-section">
            <div class="global-container">
                <h2>Statistiques</h2>
                <div class="stats-grid">
                    <?php if ($isEditionActive): ?>
                    <!-- Statistiques pendant le vote -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-vote-yea"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">Votes ouverts</div>
                            <div class="stat-label">Jusqu'au <?php echo date('d/m/Y', strtotime($editionStatus['date_fin'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">En cours</div>
                            <div class="stat-label">Période de vote active</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">Participez !</div>
                            <div class="stat-label">Votez pour vos favoris</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">Édition <?php echo htmlspecialchars($editionYear); ?></div>
                            <div class="stat-label">Social Media Awards</div>
                        </div>
                    </div>
                    <?php elseif (!$votesFinished && !$isEditionActive): ?>
                    <!-- Statistiques avant le vote -->
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">À venir</div>
                            <div class="stat-label">Début le <?php echo date('d/m/Y', strtotime($editionStatus['date_debut'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-hourglass-start"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">Préparez-vous</div>
                            <div class="stat-label">Votes bientôt ouverts</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">Édition <?php echo htmlspecialchars($editionYear); ?></div>
                            <div class="stat-label">Social Media Awards</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">Informations</div>
                            <div class="stat-label">Consultez les catégories</div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Statistiques après le vote -->
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
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <?php include 'views/partials/footer.php'; ?>
    
    <!-- JavaScript -->
    <script src="assets/js/results.js"></script>
    
    <!-- Script pour gérer l'affichage selon le statut -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editionSelect = document.getElementById('editionSelect');
        
        // Adapter le JavaScript pour gérer les différentes éditions
        if (editionSelect) {
            editionSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const isActive = selectedOption.getAttribute('data-active') === '1';
                const isFinished = selectedOption.getAttribute('data-finished') === '1';
                
                // Afficher un message selon le statut
                if (isActive) {
                    if (confirm("Cette édition est encore en cours de vote. Voulez-vous quand même voir la page?")) {
                        window.location.href = `results.php?edition=${this.value}`;
                    } else {
                        // Revenir à l'option précédente
                        this.value = '<?php echo $editionId; ?>';
                    }
                } else if (!isFinished && !isActive) {
                    if (confirm("Les votes de cette édition n'ont pas encore commencé. Voulez-vous quand même voir la page?")) {
                        window.location.href = `results.php?edition=${this.value}`;
                    } else {
                        this.value = '<?php echo $editionId; ?>';
                    }
                } else {
                    window.location.href = `results.php?edition=${this.value}`;
                }
            });
        }
        
        // Compteur à rebours si vote en cours
        <?php if ($isEditionActive && isset($editionStatus['date_fin'])): ?>
        function updateCountdown() {
            const endDate = new Date("<?php echo $editionStatus['date_fin']; ?>".replace(' ', 'T'));
            const now = new Date();
            const distance = endDate - now;
            
            if (distance < 0) {
                // Le vote est terminé, recharger la page
                window.location.reload();
                return;
            }
            
            // Calculer jours, heures, minutes, secondes
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Mettre à jour l'affichage
            const countdownElement = document.getElementById('countdownTimer');
            if (countdownElement) {
                if (days > 0) {
                    countdownElement.innerHTML = 
                        `Fin dans : ${days}j ${hours}h ${minutes}m ${seconds}s`;
                } else {
                    countdownElement.innerHTML = 
                        `Fin dans : ${hours}h ${minutes}m ${seconds}s`;
                }
            }
        }
        
        // Mettre à jour toutes les secondes
        updateCountdown();
        setInterval(updateCountdown, 1000);
        <?php endif; ?>
        
        // Désactiver les filtres si vote en cours
        const isVoteActive = document.body.classList.contains('vote-active');
        const isVoteNotStarted = document.body.classList.contains('vote-not-started-page');
        
        if (isVoteActive || isVoteNotStarted) {
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(button => {
                button.style.pointerEvents = 'none';
                button.style.opacity = '0.5';
            });
        }
    });
    </script>
</body>
</html>

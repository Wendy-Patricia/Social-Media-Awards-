<?php
// FICHIER : results.php  
// DESCRIPTION : Page affichant les résultats dynamiques des Social Media Awards
// POLITIQUE : NE JAMAIS montrer les résultats avant la fin officielle des votes

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/autoload.php';

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=social_media_awards;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $resultsService = new App\Services\ResultsService($pdo);
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

try {
    // 1. Obter TODAS as edições disponíveis
    $allEditions = $resultsService->getAvailableEditions();
    
    // 2. Determinar qual edição mostrar (parâmetro GET ou última terminada)
    if (isset($_GET['edition']) && is_numeric($_GET['edition'])) {
        $editionId = (int)$_GET['edition'];
    } else {
        // Por padrão, buscar a última edição TERMINADA
        $finishedEditions = $resultsService->getFinishedEditions();
        if (!empty($finishedEditions)) {
            $editionId = $finishedEditions[0]['id_edition']; // Primeira = mais recente
        } else {
            $editionId = 1; // Fallback
        }
    }
    
    // 3. Obter informações COMPLETAS da edição
    $sqlEdition = "SELECT * FROM edition WHERE id_edition = :edition_id";
    $stmtEdition = $pdo->prepare($sqlEdition);
    $stmtEdition->execute([':edition_id' => $editionId]);
    $edition = $stmtEdition->fetch(PDO::FETCH_ASSOC);
    
    if (!$edition) {
        throw new Exception("Édition non trouvée: $editionId");
    }
    
    $editionYear = $edition['annee'] ?? date('Y');
    $editionName = $edition['nom'] ?? "Social Media Awards";
    $dateDebut = $edition['date_debut'] ?? null;
    $dateFin = $edition['date_fin'] ?? null;
    
    // DEBUG: Log des informations
    error_log("RESULTS PAGE - Édition ID: $editionId");
    error_log("RESULTS PAGE - Date début: $dateDebut");
    error_log("RESULTS PAGE - Date fin: $dateFin");
    error_log("RESULTS PAGE - Est active: " . ($edition['est_active'] ? 'Oui' : 'Non'));
    
    // 4. DÉTERMINER LE STATUT EXACT (LOGIQUE CORRIGÉE)
    $now = date('Y-m-d H:i:s');
    $status = 'unknown';
    
    // Conditions prioritaires:
    if ($dateFin && $now > $dateFin) {
        // 1. La date de fin est passée → Votes TERMINÉS
        $status = 'voting_finished';
        error_log("RESULTS PAGE - Statut: voting_finished (date fin dépassée)");
    } elseif ($edition['est_active'] == 1 && $dateDebut && $now >= $dateDebut && (!$dateFin || $now <= $dateFin)) {
        // 2. Édition active ET maintenant entre début et fin → Votes EN COURS
        $status = 'voting_active';
        error_log("RESULTS PAGE - Statut: voting_active (dans période)");
    } elseif ($edition['est_active'] == 1 && $dateDebut && $now < $dateDebut) {
        // 3. Édition active mais avant début → Pas encore commencé
        $status = 'voting_not_started';
        error_log("RESULTS PAGE - Statut: voting_not_started (avant début)");
    } elseif ($edition['est_active'] == 0) {
        // 4. Édition inactive
        $status = 'edition_inactive';
        error_log("RESULTS PAGE - Statut: edition_inactive");
    } else {
        // 5. Autres cas (dates manquantes, etc.)
        $status = 'unknown';
        error_log("RESULTS PAGE - Statut: unknown");
    }
    
    // 5. POLITIQUE: NE JAMAIS montrer les résultats si les votes sont encore possibles
    $canShowResults = false;
    $showWarning = false;
    
    if ($status === 'voting_finished') {
        // Votes terminés → on peut montrer les résultats
        $canShowResults = true;
        error_log("RESULTS PAGE - Résultats AUTORISÉS (votes terminés)");
    } elseif ($status === 'voting_active') {
        // Votes en cours → CACHE les résultats
        $canShowResults = false;
        $showWarning = true;
        error_log("RESULTS PAGE - Résultats INTERDITS (votes en cours)");
    } else {
        // Autres cas → pas de résultats
        $canShowResults = false;
        error_log("RESULTS PAGE - Résultats INTERDITS (statut: $status)");
    }
    
    // 6. Charger les données selon les permissions
    if ($canShowResults) {
        // Charger les résultats (seulement si autorisé)
        $grandWinners = $resultsService->getGrandWinners($editionId);
        $categoryResults = $resultsService->getResultsByCategory($editionId);
        $globalStats = $resultsService->getGlobalStatistics($editionId);
        
        error_log("RESULTS PAGE - Données chargées: " . 
                 count($grandWinners) . " gagnants, " . 
                 count($categoryResults) . " catégories");
    } else {
        // Ne PAS charger les résultats
        $grandWinners = [];
        $categoryResults = [];
        $globalStats = [
            'total_votes' => 0,
            'total_categories' => 0,
            'total_nominations' => 0,
            'participation_rate' => 0,
            'total_voters' => 0
        ];
        
        error_log("RESULTS PAGE - Données NON chargées (non autorisé)");
    }
    
    // 7. Obtenir la liste des éditions (filtrées par statut pour le dropdown)
    $availableEditions = $resultsService->getAvailableEditions();
    
    // Ajouter le statut à chaque édition pour le dropdown
    foreach ($availableEditions as &$ed) {
        $edStatus = $resultsService->getEditionStatus($ed['id_edition']);
        $ed['is_active'] = $edStatus['active'];
        $ed['is_finished'] = $edStatus['votes_finished'];
        $ed['not_started'] = $edStatus['votes_not_started'];
    }
    
} catch (Exception $e) {
    error_log("Erreur results.php: " . $e->getMessage());
    $status = 'error';
    $editionYear = date('Y');
    $dateDebut = null;
    $dateFin = null;
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
    $canShowResults = false;
    $showWarning = false;
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
        <?php 
        switch ($status) {
            case 'voting_active': echo 'Votes en cours'; break;
            case 'voting_not_started': echo 'Votes à venir'; break;
            case 'voting_finished': echo "Résultats $editionYear"; break;
            case 'edition_inactive': echo "Édition $editionYear"; break;
            default: echo "Social Media Awards";
        }
        ?>
    </title>
    
    <style>
        /* Styles pour la sécurité des résultats */
        .security-warning {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.2);
            border-left: 5px solid #c0392b;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(255, 107, 107, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
        }
        
        .security-warning i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .voting-period-box {
            background: linear-gradient(135deg, #4FBDAB, #3da895);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        
        .vote-countdown {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .results-locked {
            text-align: center;
            padding: 50px 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 30px 0;
            border: 2px dashed #dee2e6;
        }
        
        .results-locked i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        /* Styles pour les résultats quand disponibles */
        .winner-card {
            transition: transform 0.3s ease;
        }
        
        .winner-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="status-<?php echo $status; ?>">

<?php require_once 'views/partials/header.php'; ?>

<div class="main-content">
    <!-- SECTION HERO -->
    <section class="results-hero">
        <div class="global-container">
            <h1>
                <?php 
                switch ($status) {
                    case 'voting_active': 
                        echo '<i class="fas fa-vote-yea"></i> Votes en cours';
                        break;
                    case 'voting_not_started': 
                        echo '<i class="fas fa-clock"></i> Votes à venir';
                        break;
                    case 'voting_finished': 
                        echo '<i class="fas fa-trophy"></i> Résultats ' . htmlspecialchars($editionYear);
                        break;
                    case 'edition_inactive':
                        echo '<i class="fas fa-info-circle"></i> Édition ' . htmlspecialchars($editionYear);
                        break;
                    default:
                        echo '<i class="fas fa-trophy"></i> Social Media Awards';
                }
                ?>
            </h1>
            
            <p class="subtitle">
                <?php 
                switch ($status) {
                    case 'voting_active':
                        echo 'Les votes sont actuellement ouverts. Les résultats seront disponibles après la clôture.';
                        break;
                    case 'voting_not_started':
                        echo 'Les votes commenceront bientôt. Préparez-vous à participer!';
                        break;
                    case 'voting_finished':
                        echo 'Découvrez les gagnants officiels de cette édition';
                        break;
                    case 'edition_inactive':
                        echo 'Cette édition est actuellement inactive';
                        break;
                    default:
                        echo 'Informations sur les Social Media Awards';
                }
                ?>
            </p>
            
            <!-- Dates importantes -->
            <?php if ($dateDebut || $dateFin): ?>
            <div class="edition-dates">
                <div class="date-card">
                    <i class="fas fa-calendar-plus"></i>
                    <div>
                        <small>Début</small>
                        <div><?php echo $dateDebut ? date('d/m/Y à H:i', strtotime($dateDebut)) : 'Non définie'; ?></div>
                    </div>
                </div>
                
                <div class="date-card">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <small>Fin</small>
                        <div><?php echo $dateFin ? date('d/m/Y à H:i', strtotime($dateFin)) : 'Non définie'; ?></div>
                    </div>
                </div>
                
                <div class="date-card">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <small>Statut</small>
                        <div class="status-badge status-<?php echo $status; ?>">
                            <?php 
                            switch ($status) {
                                case 'voting_active': echo 'En cours'; break;
                                case 'voting_not_started': echo 'À venir'; break;
                                case 'voting_finished': echo 'Terminé'; break;
                                case 'edition_inactive': echo 'Inactive'; break;
                                default: echo 'Inconnu';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- AVERTISSEMENT SI VOTES EN COURS -->
            <?php if ($showWarning): ?>
            <div class="security-warning" id="securityWarning">
                <i class="fas fa-lock"></i>
                <h2>Résultats temporairement indisponibles</h2>
                <p>Pour garantir l'intégrité du vote, les résultats ne sont pas accessibles pendant la période de vote.</p>
                <p>Ils seront dévoilés automatiquement après la clôture des votes.</p>
                
                <?php if ($dateFin): ?>
                <div class="vote-countdown">
                    <i class="fas fa-hourglass-half"></i>
                    Fin des votes: <?php echo date('d/m/Y à H:i', strtotime($dateFin)); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Sélecteur d'édition -->
            <div class="edition-selector">
                <label for="editionSelect">
                    <i class="fas fa-calendar-alt"></i> Choisir une édition:
                </label>
                <select id="editionSelect">
                    <?php foreach ($availableEditions as $ed): 
                        $isCurrent = ($ed['id_edition'] == $editionId);
                        $isFinished = $ed['is_finished'] ?? false;
                        $isActive = $ed['is_active'] ?? false;
                        $notStarted = $ed['not_started'] ?? false;
                    ?>
                    <option value="<?php echo $ed['id_edition']; ?>" 
                            <?php echo $isCurrent ? 'selected' : ''; ?>
                            data-status="<?php echo $isActive ? 'active' : ($isFinished ? 'finished' : ($notStarted ? 'not_started' : 'inactive')); ?>">
                        <?php echo htmlspecialchars($ed['annee']); ?> - <?php echo htmlspecialchars($ed['nom']); ?>
                        <?php if ($isActive): ?> (Votes en cours)<?php endif; ?>
                        <?php if ($isFinished): ?> (Terminée)<?php endif; ?>
                        <?php if ($notStarted): ?> (À venir)<?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="button" id="changeEditionBtn" class="btn-small">
                    <i class="fas fa-sync-alt"></i> Voir
                </button>
            </div>
        </div>
    </section>

    <!-- SECTION AVANT LES RÉSULTATS (quand votes en cours) -->
    <?php if (!$canShowResults && $status === 'voting_active'): ?>
    <section class="before-results">
        <div class="global-container">
            <div class="results-locked">
                <i class="fas fa-lock fa-4x"></i>
                <h2>Les résultats sont sécurisés</h2>
                <p>Pour maintenir l'équité et la transparence du processus, les résultats ne sont pas accessibles pendant la période de vote.</p>
                
                <div class="call-to-action">
                    <h3>Vous voulez participer?</h3>
                    <p>Votez pour vos favoris avant la fin de la période!</p>
                    <a href="/Social-Media-Awards-/views/user/Vote.php" class="btn-primary">
                        <i class="fas fa-vote-yea"></i> Voter maintenant
                    </a>
                </div>
                
                <?php if ($dateFin): ?>
                <div class="countdown-container">
                    <h4>Temps restant pour voter:</h4>
                    <div id="liveCountdown" class="live-countdown">
                        <span id="countdownDays">--</span> jours
                        <span id="countdownHours">--</span> heures
                        <span id="countdownMinutes">--</span> minutes
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Statistiques "safe" (sans révéler les gagnants) -->
            <div class="safe-stats">
                <h3><i class="fas fa-chart-line"></i> Participation en temps réel</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($globalStats['total_voters']); ?></div>
                            <div class="stat-label">Participants</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $globalStats['total_categories']; ?></div>
                            <div class="stat-label">Catégories</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $globalStats['total_nominations']; ?></div>
                            <div class="stat-label">Candidats</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">En cours</div>
                            <div class="stat-label">Votes actifs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SECTION RÉSULTATS (SEULEMENT SI AUTORISÉ) -->
    <?php if ($canShowResults): ?>
    <section class="results-section" id="officialResults">
        <div class="global-container">
            <div class="results-header">
                <h2><i class="fas fa-medal"></i> Palmarès officiel - Édition <?php echo htmlspecialchars($editionYear); ?></h2>
                <p class="results-announcement">
                    <i class="fas fa-bullhorn"></i> 
                    Les résultats ont été officiellement validés après la clôture des votes.
                </p>
                <div class="results-timestamp">
                    <i class="fas fa-calendar-check"></i>
                    Date de publication: <?php echo date('d/m/Y à H:i'); ?>
                </div>
            </div>
            
            <!-- Grands gagnants -->
            <?php if (!empty($grandWinners)): ?>
            <div class="winners-showcase">
                <h3><i class="fas fa-crown"></i> Grands Gagnants</h3>
                <div class="winners-grid">
                    <?php foreach ($grandWinners as $winner): 
                        $rank = $winner['rang'] ?? 1;
                        $rankClass = ($rank == 1) ? 'gold' : (($rank == 2) ? 'silver' : 'bronze');
                    ?>
                    <div class="winner-card <?php echo $rankClass; ?>">
                        <div class="winner-rank rank-<?php echo $rank; ?>">
                            <?php if ($rank == 1): ?>
                                <i class="fas fa-crown"></i>
                            <?php else: ?>
                                <?php echo $rank; ?><sup><?php echo ($rank == 1) ? 'ère' : 'ème'; ?></sup>
                            <?php endif; ?>
                        </div>
                        <div class="winner-image">
                            <?php if (!empty($winner['image'])): ?>
                                <img src="<?php echo htmlspecialchars($winner['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($winner['nom_nomination']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="default-winner-img">
                                    <i class="fas fa-trophy"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="winner-info">
                            <h4><?php echo htmlspecialchars($winner['nom_nomination']); ?></h4>
                            <p class="winner-category">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($winner['categorie'] ?? 'Catégorie'); ?>
                            </p>
                            <div class="winner-stats">
                                <span class="stat-votes">
                                    <i class="fas fa-vote-yea"></i>
                                    <?php echo number_format($winner['total_votes'] ?? 0); ?> votes
                                </span>
                                <?php if (!empty($winner['plateforme'])): ?>
                                <span class="stat-platform">
                                    <i class="fab fa-<?php echo strtolower($winner['plateforme']); ?>"></i>
                                    <?php echo htmlspecialchars($winner['plateforme']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="no-results">
                <i class="fas fa-info-circle"></i>
                <h3>Aucun gagnant enregistré</h3>
                <p>Il n'y a pas encore de résultats disponibles pour cette édition.</p>
            </div>
            <?php endif; ?>
            
            <!-- Résultats par catégorie -->
            <?php if (!empty($categoryResults)): ?>
            <div class="category-results">
                <h3><i class="fas fa-list-ol"></i> Résultats par Catégorie</h3>
                
                <!-- Filtres de plateforme -->
                <div class="category-filters">
                    <button class="filter-btn active" data-filter="all">Toutes</button>
                    <?php 
                    // Obter plateformes únicas
                    $platforms = [];
                    foreach ($categoryResults as $cat) {
                        if (!empty($cat['plateforme'])) {
                            $platforms[$cat['plateforme']] = true;
                        }
                    }
                    ksort($platforms);
                    ?>
                    <?php foreach (array_keys($platforms) as $platform): ?>
                    <button class="filter-btn" data-filter="<?php echo strtolower($platform); ?>">
                        <?php echo htmlspecialchars($platform); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                
                <div class="categories-grid">
                    <?php foreach ($categoryResults as $category): ?>
                    <div class="category-result-card" 
                         data-platform="<?php echo strtolower($category['plateforme'] ?? 'all'); ?>">
                        <div class="category-header">
                            <h4><?php echo htmlspecialchars($category['categorie_nom']); ?></h4>
                            <?php if (!empty($category['plateforme'])): ?>
                            <span class="platform-badge <?php echo strtolower($category['plateforme']); ?>">
                                <i class="fab fa-<?php echo strtolower($category['plateforme']); ?>"></i>
                                <?php echo htmlspecialchars($category['plateforme']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($category['winners'])): ?>
                        <div class="category-winners">
                            <?php foreach ($category['winners'] as $winner): ?>
                            <div class="category-winner position-<?php echo $winner['position']; ?>">
                                <span class="winner-medal"><?php echo $winner['medal']; ?></span>
                                <span class="winner-name">
                                    <?php echo htmlspecialchars($winner['nom_nomination']); ?>
                                </span>
                                <div class="winner-details">
                                    <span class="winner-votes">
                                        <i class="fas fa-chart-bar"></i>
                                        <?php echo number_format($winner['vote_count']); ?> votes
                                    </span>
                                    <span class="winner-percentage">
                                        <?php echo $winner['vote_percentage']; ?>%
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="no-winners">Aucun vote enregistré dans cette catégorie</p>
                        <?php endif; ?>
                        
                        <div class="category-total">
                            <i class="fas fa-calculator"></i>
                            Total: <?php echo number_format($category['total_votes_categorie'] ?? 0); ?> votes
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Statistiques détaillées -->
            <div class="detailed-stats">
                <h3><i class="fas fa-chart-pie"></i> Statistiques détaillées</h3>
                <div class="stats-grid detailed">
                    <div class="stat-card large">
                        <div class="stat-icon">
                            <i class="fas fa-vote-yea"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($globalStats['total_votes']); ?></div>
                            <div class="stat-label">Votes Totaux</div>
                            <div class="stat-desc">Nombre total de votes enregistrés</div>
                        </div>
                    </div>
                    
                    <div class="stat-card large">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($globalStats['total_voters']); ?></div>
                            <div class="stat-label">Électeurs</div>
                            <div class="stat-desc">Participants uniques</div>
                        </div>
                    </div>
                    
                    <div class="stat-card large">
                        <div class="stat-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $globalStats['participation_rate']; ?>%</div>
                            <div class="stat-label">Taux de Participation</div>
                            <div class="stat-desc">Pourcentage d'électeurs actifs</div>
                        </div>
                    </div>
                    
                    <div class="stat-card large">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $globalStats['total_categories']; ?></div>
                            <div class="stat-label">Catégories</div>
                            <div class="stat-desc">Nombre de catégories actives</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php elseif ($status === 'voting_not_started'): ?>
    <!-- Message pour votes pas encore commencés -->
    <section class="not-started-section">
        <div class="global-container">
            <div class="coming-soon">
                <i class="fas fa-hourglass-start fa-4x"></i>
                <h2>Les votes n'ont pas encore commencé</h2>
                <p>L'édition <?php echo htmlspecialchars($editionYear); ?> est en préparation.</p>
                
                <?php if ($dateDebut): ?>
                <div class="start-date">
                    <i class="fas fa-calendar-day"></i>
                    <h3>Date d'ouverture des votes:</h3>
                    <div class="date-display">
                        <?php echo date('d/m/Y à H:i', strtotime($dateDebut)); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="preparation-info">
                    <h3><i class="fas fa-info-circle"></i> En attendant...</h3>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Les catégories sont en cours de finalisation</li>
                        <li><i class="fas fa-check-circle"></i> Les candidats sont en sélection</li>
                        <li><i class="fas fa-check-circle"></i> Le système de vote est en test</li>
                        <li><i class="fas fa-check-circle"></i> Tout sera prêt pour la date d'ouverture!</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- SECTION STATISTIQUES GÉNÉRALES (toujours visible) -->
    <section class="general-statistics">
        <div class="global-container">
            <h2><i class="fas fa-chart-line"></i> Vue d'ensemble</h2>
            <div class="stats-grid">
                <?php if ($status === 'voting_active'): ?>
                <!-- Pendant les votes -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-hourglass-half"></i>
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
                        <div class="stat-number">Participez!</div>
                        <div class="stat-label">Votez pour vos favoris</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Sécurisé</div>
                        <div class="stat-label">Vote anonyme et transparent</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Édition <?php echo $editionYear; ?></div>
                        <div class="stat-label">Social Media Awards</div>
                    </div>
                </div>
                
                <?php elseif ($status === 'voting_finished'): ?>
                <!-- Après les votes -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Terminé</div>
                        <div class="stat-label">Votes clôturés</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($globalStats['total_voters']); ?></div>
                        <div class="stat-label">Participants</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-vote-yea"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format($globalStats['total_votes']); ?></div>
                        <div class="stat-label">Votes</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $globalStats['participation_rate']; ?>%</div>
                        <div class="stat-label">Participation</div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Avant les votes -->
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">À venir</div>
                        <div class="stat-label">Bientôt</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Préparez-vous</div>
                        <div class="stat-label">Votez bientôt</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Informations</div>
                        <div class="stat-label">À suivre</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Édition <?php echo $editionYear; ?></div>
                        <div class="stat-label">Social Media Awards</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php include 'views/partials/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du sélecteur d'édition
    const editionSelect = document.getElementById('editionSelect');
    const changeBtn = document.getElementById('changeEditionBtn');
    
    if (editionSelect && changeBtn) {
        changeBtn.addEventListener('click', function() {
            const selectedValue = editionSelect.value;
            const selectedOption = editionSelect.options[editionSelect.selectedIndex];
            const status = selectedOption.getAttribute('data-status');
            
            // Avertissement selon le statut
            if (status === 'active') {
                if (!confirm("Cette édition est en cours de vote. Les résultats ne seront visibles qu'après la clôture. Voulez-vous continuer?")) {
                    return;
                }
            } else if (status === 'not_started') {
                if (!confirm("Les votes de cette édition n'ont pas encore commencé. Voulez-vous continuer?")) {
                    return;
                }
            }
            
            window.location.href = 'results.php?edition=' + selectedValue;
        });
        
        editionSelect.addEventListener('change', function() {
            changeBtn.style.display = 'inline-block';
        });
    }
    
    // Filtres des catégories (si résultats disponibles)
    const filterButtons = document.querySelectorAll('.filter-btn');
    if (filterButtons.length > 0) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Retirer la classe active de tous les boutons
                filterButtons.forEach(b => b.classList.remove('active'));
                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');
                
                const filterValue = this.getAttribute('data-filter');
                const cards = document.querySelectorAll('.category-result-card');
                
                cards.forEach(card => {
                    const cardPlatform = card.getAttribute('data-platform');
                    if (filterValue === 'all' || cardPlatform === filterValue) {
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 10);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(10px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }
    
    // Compteur à rebours pour les votes en cours
    <?php if ($status === 'voting_active' && $dateFin): ?>
    function updateLiveCountdown() {
        const endDate = new Date("<?php echo $dateFin; ?>".replace(' ', 'T'));
        const now = new Date();
        const distance = endDate - now;
        
        if (distance < 0) {
            // Temps écoulé, recharger la page
            window.location.reload();
            return;
        }
        
        // Calculer le temps restant
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        // Mettre à jour l'affichage
        const daysEl = document.getElementById('countdownDays');
        const hoursEl = document.getElementById('countdownHours');
        const minutesEl = document.getElementById('countdownMinutes');
        
        if (daysEl) daysEl.textContent = days;
        if (hoursEl) hoursEl.textContent = hours.toString().padStart(2, '0');
        if (minutesEl) minutesEl.textContent = minutes.toString().padStart(2, '0');
        
        // Changer la couleur si moins de 24h
        if (days === 0 && hours < 24) {
            const container = document.querySelector('.live-countdown');
            if (container) {
                container.style.color = '#ff6b6b';
                container.style.fontWeight = 'bold';
            }
        }
    }
    
    updateLiveCountdown();
    const countdownInterval = setInterval(updateLiveCountdown, 1000);
    <?php endif; ?>
    
    // Animation des cartes de résultats
    const resultCards = document.querySelectorAll('.winner-card, .category-result-card');
    resultCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Message de sécurité en surbrillance
    const securityWarning = document.getElementById('securityWarning');
    if (securityWarning) {
        setInterval(() => {
            securityWarning.style.transform = securityWarning.style.transform === 'scale(1.02)' ? 'scale(1)' : 'scale(1.02)';
        }, 2000);
    }
});
</script>
</body>
</html>
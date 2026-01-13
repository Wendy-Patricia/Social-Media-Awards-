<?php
// views/candidate/candidate-dashboard.php
session_start();

// Verificar se o usuário está logado como candidato
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: /Social-Media-Awards/views/login.php');
    exit;
}

// Incluir configurações
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatService;
use App\Services\CategoryService;
use App\Services\EditionService;

// Inicializar conexão
$pdo = Database::getInstance()->getConnection();

// Inicializar serviços
$candidatService = new CandidatService($pdo);
$categoryService = new CategoryService($pdo);
$editionService = new EditionService($pdo);

// Verificar estado do candidato
$userId = $_SESSION['user_id'];
$isNominee = $candidatService->isNominee($userId);
$nominations = [];
$votingStatus = 'not_started';

if ($isNominee) {
    $nominations = $candidatService->getActiveNominations($userId);
    if (!empty($nominations)) {
        $votingStatus = $candidatService->getVotingStatus($nominations[0]);
    }
}

// Obter estatísticas
$stats = $candidatService->getCandidatStats($userId);

// Obter candidaturas recentes
$candidatures = $candidatService->getUserCandidatures($userId);
$recentCandidatures = array_slice($candidatures, 0, 5);

// Verificar edições ativas para candidaturas
$activeEditions = [];
try {
    $sql = "SELECT e.* FROM edition e
            WHERE e.est_active = 1 
            AND e.date_fin_candidatures >= CURDATE()
            ORDER BY e.date_fin_candidatures ASC
            LIMIT 2";
    $stmt = $pdo->query($sql);
    $activeEditions = $stmt->fetchAll();
} catch (Exception $e) {
    // Silenciar erro se a tabela não existir ainda
    $activeEditions = [];
}

// Determinar classe CSS para status
function getStatusClass($status) {
    return match($status) {
        'in_progress' => 'status-active',
        'ended' => 'status-ended',
        default => 'status-pending'
    };
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Social Media Awards</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/candidat.css">

</head>
<body class="bg-light">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/Social-Media-Awards-/index.php">
                <i class="fas fa-trophy me-2"></i>Social Media Awards
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="/Social-Media-Awards-/views/candidate/candidate-dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                    <span class="navbar-text me-3">
                        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['user_pseudonyme'] ?? 'Candidat') ?>
                    </span>
                    <a class="nav-link" href="/Social-Media-Awards-/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="sidebar-container">
                    <?php include __DIR__ . '/../partials/sidebar-candidat.php'; ?>
                </div>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-9">
                <div class="main-content fade-in">
                    <!-- Cabeçalho -->
                    <div class="page-header mb-4">
                        <h1 class="page-title">
                            <?php if ($isNominee): ?>
                            <i class="fas fa-trophy me-2"></i>Tableau de bord Nominé
                            <?php else: ?>
                            <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord Candidat
                            <?php endif; ?>
                        </h1>
                        <p class="page-subtitle">Bonjour, <?= htmlspecialchars($_SESSION['user_pseudonyme'] ?? 'Candidat') ?> !</p>
                    </div>
                    
                    <!-- Alertas -->
                    <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); endif; ?>
                    
                    <?php if ($isNominee): ?>
                    <!-- ====================== -->
                    <!-- DASHBOARD DO NOMEADO -->
                    <!-- ====================== -->
                    
                    <!-- Hero para nomeados -->
                    <div class="nominee-hero mb-4">
                        <div class="nominee-content">
                            <h2>Félicitations ! Vous êtes officiellement nominé(e)</h2>
                            <p>Votre contenu a été sélectionné par notre jury. Vous participez maintenant à la phase de votes.</p>
                        </div>
                        <div class="nominee-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                    
                    <!-- Nomeações -->
                    <?php foreach ($nominations as $nomination): 
                        $status = $votingStatus;
                        $statusClass = getStatusClass($status);
                        $startDate = new DateTime($nomination['date_debut_votes'] ?? $nomination['date_debut']);
                        $endDate = new DateTime($nomination['date_fin_votes'] ?? $nomination['date_fin']);
                        $now = new DateTime();
                    ?>
                    <div class="main-card nomination-card mb-4">
                        <div class="card-header">
                            <div class="nomination-header">
                                <h5 class="nomination-title">
                                    <i class="fas fa-vote-yea me-2"></i>
                                    Votre nomination
                                </h5>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= $status == 'in_progress' ? 'Votes en cours' : 
                                       ($status == 'ended' ? 'Votes terminés' : 'Votes non commencés') ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="nomination-details">
                                <div>
                                    <h5 class="mb-3"><?= htmlspecialchars($nomination['libelle'] ?? 'Titre non disponible') ?></h5>
                                    <p class="text-muted mb-4">
                                        <?php if (isset($nomination['categorie_nom'])): ?>
                                        <strong>Catégorie:</strong> <?= htmlspecialchars($nomination['categorie_nom']) ?><br>
                                        <?php endif; ?>
                                        <?php if (isset($nomination['edition_nom'])): ?>
                                        <strong>Édition:</strong> <?= htmlspecialchars($nomination['edition_nom']) ?><br>
                                        <?php endif; ?>
                                        <?php if (isset($nomination['plateforme'])): ?>
                                        <strong>Plateforme:</strong> <?= htmlspecialchars($nomination['plateforme']) ?>
                                        <?php endif; ?>
                                    </p>
                                    
                                    <!-- Datas importantes -->
                                    <?php if (isset($nomination['date_debut_votes']) || isset($nomination['date_debut'])): ?>
                                    <div class="dates-list">
                                        <h6><i class="far fa-calendar-alt me-2"></i>Dates importantes</h6>
                                        <ul class="list-unstyled">
                                            <li>
                                                <i class="fas fa-play-circle"></i>
                                                <strong>Début des votes:</strong> 
                                                <?= $startDate->format('d/m/Y H:i') ?>
                                            </li>
                                            <li>
                                                <i class="fas fa-flag-checkered"></i>
                                                <strong>Fin des votes:</strong> 
                                                <?= $endDate->format('d/m/Y H:i') ?>
                                            </li>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <?php if ($status == 'in_progress'): 
                                        $interval = $now->diff($endDate);
                                    ?>
                                    <div class="countdown-container">
                                        <h6 class="countdown-title">Temps restant</h6>
                                        <div class="countdown-timer" data-countdown="<?= $endDate->format('c') ?>">
                                            <div class="countdown-unit">
                                                <span class="countdown-number countdown-days"><?= $interval->days ?></span>
                                                <span class="countdown-label">jours</span>
                                            </div>
                                            <div class="countdown-unit">
                                                <span class="countdown-number countdown-hours"><?= str_pad($interval->h, 2, '0', STR_PAD_LEFT) ?></span>
                                                <span class="countdown-label">heures</span>
                                            </div>
                                            <div class="countdown-unit">
                                                <span class="countdown-number countdown-minutes"><?= str_pad($interval->i, 2, '0', STR_PAD_LEFT) ?></span>
                                                <span class="countdown-label">min</span>
                                            </div>
                                            <div class="countdown-unit">
                                                <span class="countdown-number countdown-seconds"><?= str_pad($interval->s, 2, '0', STR_PAD_LEFT) ?></span>
                                                <span class="countdown-label">sec</span>
                                            </div>
                                        </div>
                                        <p class="small text-muted mt-2">avant la fin des votes</p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Ações -->
                                    <div class="action-buttons mt-4">
                                        <?php if (isset($nomination['id_nomination'])): ?>
                                        <a href="nominee-profile.php?nomination=<?= $nomination['id_nomination'] ?>" 
                                           class="btn btn-action btn-outline-primary">
                                            <i class="fas fa-id-badge me-1"></i> Voir mon profil public
                                        </a>
                                        <?php endif; ?>
                                        <a href="share-nomination.php" 
                                           class="btn btn-action btn-outline-success">
                                            <i class="fas fa-share-alt me-1"></i> Partager ma nomination
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Cartões de ação -->
                    <div class="features-grid mb-4">
                        <div class="feature-card hover-lift">
                            <div class="feature-icon">
                                <i class="fas fa-id-badge"></i>
                            </div>
                            <h5>Profil Public</h5>
                            <p>Votre profil visible par les votants</p>
                            <a href="nominee-profile.php" class="btn btn-primary">Voir mon profil</a>
                        </div>
                        
                        <div class="feature-card hover-lift">
                            <div class="feature-icon">
                                <i class="fas fa-share-alt"></i>
                            </div>
                            <h5>Partager</h5>
                            <p>Partagez votre nomination sur les réseaux</p>
                            <a href="share-nomination.php" class="btn btn-success">Partager</a>
                        </div>
                        
                        <div class="feature-card hover-lift">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h5>Statut des votes</h5>
                            <p>Suivez l'avancement des votes</p>
                            <a href="status-votes.php" class="btn btn-info">Voir le statut</a>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <!-- ====================== -->
                    <!-- DASHBOARD DO CANDIDATO -->
                    <!-- ====================== -->
                    
                    <!-- Estatísticas -->
                    <div class="stats-grid mb-4">
                        <div class="stat-card primary hover-lift">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h6>Candidatures</h6>
                                    <div class="stat-number" data-count="<?= $stats['total'] ?? 0 ?>">0</div>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card warning hover-lift">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h6>En attente</h6>
                                    <div class="stat-number" data-count="<?= $stats['pending'] ?? 0 ?>">0</div>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card success hover-lift">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h6>Approuvées</h6>
                                    <div class="stat-number" data-count="<?= $stats['approved'] ?? 0 ?>">0</div>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card danger hover-lift">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h6>Rejetées</h6>
                                    <div class="stat-number" data-count="<?= $stats['rejected'] ?? 0 ?>">0</div>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Candidaturas recentes -->
                    <div class="main-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>
                                Vos candidatures récentes
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentCandidatures)): ?>
                            <div class="text-center py-4">
                                <div class="feature-icon mb-3">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h5>Vous n'avez pas encore soumis de candidature</h5>
                                <p class="text-muted mb-4">
                                    Commencez par soumettre votre première candidature pour participer aux Social Media Awards.
                                </p>
                                <a href="soumettre-candidature.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i> Soumettre une candidature
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="table-container">
                                <table class="table table-candidatures">
                                    <thead>
                                        <tr>
                                            <th>Titre</th>
                                            <th>Catégorie</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentCandidatures as $cand): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars(substr($cand['libelle'], 0, 40)) ?><?= strlen($cand['libelle']) > 40 ? '...' : '' ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($cand['plateforme'] ?? '') ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($cand['categorie_nom'] ?? 'N/A') ?></td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($cand['date_soumission'])) ?><br>
                                                <small class="text-muted"><?= date('H:i', strtotime($cand['date_soumission'])) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?= $cand['statut'] == 'Approuvée' ? 'bg-success' : 
                                                       ($cand['statut'] == 'Rejetée' ? 'bg-danger' : 'bg-warning') ?>">
                                                    <?= $cand['statut'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="candidature-details.php?id=<?= $cand['id_candidature'] ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Voir les détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="mes-candidatures.php" class="btn btn-outline-secondary">
                                    Voir toutes les candidatures
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Appels à candidatures -->
                    <?php if (!empty($activeEditions)): ?>
                    <div class="main-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bullhorn me-2"></i>
                                Appels à candidatures ouverts
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="features-grid">
                                <?php foreach ($activeEditions as $edition): 
                                    $dateFin = new DateTime($edition['date_fin_candidatures']);
                                    $now = new DateTime();
                                    $interval = $now->diff($dateFin);
                                ?>
                                <div class="feature-card hover-lift">
                                    <div class="feature-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <h5><?= htmlspecialchars($edition['nom']) ?></h5>
                                    <p class="mb-3">
                                        <small class="text-muted">
                                            <i class="far fa-calendar me-1"></i>
                                            Clôture: <?= $dateFin->format('d/m/Y H:i') ?>
                                        </small>
                                    </p>
                                    <div class="progress mb-3" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?= min(100, max(10, 100 - ($interval->days * 100 / 30))) ?>%">
                                        </div>
                                    </div>
                                    <a href="soumettre-candidature.php?edition=<?= $edition['id_edition'] ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> Soumettre
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Call to Action -->
                    <div class="cta-section mt-4">
                        <div class="cta-content">
                            <h3>Prêt à participer ?</h3>
                            <p>Soumettez votre meilleur contenu et tentez de remporter les Social Media Awards !</p>
                            <a href="soumettre-candidature.php" class="btn btn-cta">
                                <i class="fas fa-rocket me-2"></i> Soumettre ma candidature
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

   <!-- Após o código existente no candidate-dashboard.php -->
<?php if ($isNominee): ?>
<!-- ====================== -->
<!-- DASHBOARD DO NOMEADO -->
<!-- ====================== -->

<!-- Hero para nomeados -->
<div class="nominee-hero mb-4">
    <div class="nominee-content">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="nominee-badge">
                <i class="fas fa-trophy me-2"></i>NOMINÉ(E) OFFICIEL
            </span>
            <?php if ($votingStatus == 'in_progress'): ?>
            <span class="badge bg-success pulse">
                <i class="fas fa-vote-yea me-1"></i> VOTES EN COURS
            </span>
            <?php endif; ?>
        </div>
        <h2>Félicitations ! Vous êtes officiellement nominé(e)</h2>
        <p>Votre contenu a été sélectionné par notre jury. Vous participez maintenant à la phase de votes.</p>
        
        <!-- Dicas para promoção -->
        <div class="mt-4">
            <h6 class="text-white mb-2">
                <i class="fas fa-lightbulb me-2"></i>Conseils pour promouvoir votre nomination
            </h6>
            <ul class="nominee-tips">
                <li>Partagez votre profil public sur vos réseaux sociaux</li>
                <li>Utilisez les hashtags officiels #SocialMediaAwards2025</li>
                <li>Créez des stories pour engager votre communauté</li>
                <li>Merci vos followers pour leur soutien !</li>
            </ul>
        </div>
    </div>
    <div class="nominee-icon">
        <i class="fas fa-trophy"></i>
    </div>
</div>

<!-- Nomeações ativas -->
<?php foreach ($nominations as $nomination): 
    $status = $votingStatus;
    $statusClass = getStatusClass($status);
    $startDate = new DateTime($nomination['date_debut_votes'] ?? $nomination['date_debut']);
    $endDate = new DateTime($nomination['date_fin_votes'] ?? $nomination['date_fin']);
    $now = new DateTime();
?>
<div class="main-card nomination-card mb-4">
    <div class="card-header">
        <div class="nomination-header">
            <h5 class="nomination-title">
                <i class="fas fa-medal me-2"></i>
                Votre nomination officielle
            </h5>
            <span class="status-badge <?= $statusClass ?>">
                <?= $status == 'in_progress' ? 'Votes en cours' : 
                   ($status == 'ended' ? 'Votes terminés' : 'Votes non commencés') ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="nomination-details">
            <div>
                <h5 class="mb-3"><?= htmlspecialchars($nomination['libelle'] ?? 'Titre non disponible') ?></h5>
                <p class="text-muted mb-4">
                    <?php if (isset($nomination['categorie_nom'])): ?>
                    <strong><i class="fas fa-tag me-1"></i>Catégorie:</strong> 
                    <?= htmlspecialchars($nomination['categorie_nom']) ?><br>
                    <?php endif; ?>
                    <?php if (isset($nomination['edition_nom'])): ?>
                    <strong><i class="fas fa-calendar-alt me-1"></i>Édition:</strong> 
                    <?= htmlspecialchars($nomination['edition_nom']) ?><br>
                    <?php endif; ?>
                    <?php if (isset($nomination['plateforme'])): ?>
                    <strong><i class="fas fa-share-alt me-1"></i>Plateforme:</strong> 
                    <?= htmlspecialchars($nomination['plateforme']) ?>
                    <?php endif; ?>
                </p>
                
                <!-- Datas importantes -->
                <?php if (isset($nomination['date_debut_votes']) || isset($nomination['date_debut'])): ?>
                <div class="dates-list">
                    <h6><i class="far fa-calendar-alt me-2"></i>Dates importantes</h6>
                    <ul class="list-unstyled">
                        <li>
                            <i class="fas fa-play-circle text-primary"></i>
                            <strong>Début des votes:</strong> 
                            <?= $startDate->format('d/m/Y H:i') ?>
                        </li>
                        <li>
                            <i class="fas fa-flag-checkered text-success"></i>
                            <strong>Fin des votes:</strong> 
                            <?= $endDate->format('d/m/Y H:i') ?>
                        </li>
                        <li>
                            <i class="fas fa-chart-bar text-info"></i>
                            <strong>Publication des résultats:</strong> 
                            <?= date('d/m/Y', strtotime('+3 days', $endDate->getTimestamp())) ?>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <?php if ($status == 'in_progress'): 
                    $interval = $now->diff($endDate);
                ?>
                <div class="countdown-container">
                    <h6 class="countdown-title">Temps restant pour voter</h6>
                    <div class="countdown-timer" data-countdown="<?= $endDate->format('c') ?>">
                        <div class="countdown-unit">
                            <span class="countdown-number countdown-days"><?= $interval->days ?></span>
                            <span class="countdown-label">jours</span>
                        </div>
                        <div class="countdown-unit">
                            <span class="countdown-number countdown-hours"><?= str_pad($interval->h, 2, '0', STR_PAD_LEFT) ?></span>
                            <span class="countdown-label">heures</span>
                        </div>
                        <div class="countdown-unit">
                            <span class="countdown-number countdown-minutes"><?= str_pad($interval->i, 2, '0', STR_PAD_LEFT) ?></span>
                            <span class="countdown-label">min</span>
                        </div>
                        <div class="countdown-unit">
                            <span class="countdown-number countdown-seconds"><?= str_pad($interval->s, 2, '0', STR_PAD_LEFT) ?></span>
                            <span class="countdown-label">sec</span>
                        </div>
                    </div>
                    <p class="small text-muted mt-2">avant la fin des votes</p>
                    
                    <!-- Lembrete de regras -->
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Le règlement interdit l'achat de votes ou l'utilisation de bots.</small>
                    </div>
                </div>
                <?php elseif ($status == 'ended'): ?>
                <div class="status-ended-container">
                    <div class="alert alert-secondary">
                        <h6><i class="fas fa-clock me-2"></i>Votes terminés</h6>
                        <p class="mb-0">Les votes sont clos. Les résultats seront annoncés prochainement.</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Botões de ação -->
                <div class="action-buttons mt-4">
                    <?php if (isset($nomination['id_nomination'])): ?>
                    <a href="nominee-profile.php?nomination=<?= $nomination['id_nomination'] ?>" 
                       class="btn btn-action btn-primary mb-2">
                        <i class="fas fa-id-badge me-1"></i> Voir mon profil public
                    </a>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2">
                        <a href="share-nomination.php" 
                           class="btn btn-action btn-success flex-fill">
                            <i class="fas fa-share-alt me-1"></i> Partager ma nomination
                        </a>
                        <a href="status-votes.php" 
                           class="btn btn-action btn-info flex-fill">
                            <i class="fas fa-chart-line me-1"></i> Statut des votes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Cartões de ação para nomeados -->
<div class="features-grid mb-4">
    <div class="feature-card hover-lift">
        <div class="feature-icon">
            <i class="fas fa-id-badge"></i>
        </div>
        <h5>Profil Public</h5>
        <p>Votre profil visible par les votants et vos fans</p>
        <a href="nominee-profile.php" class="btn btn-primary">
            <i class="fas fa-eye me-1"></i> Voir mon profil
        </a>
    </div>
    
    <div class="feature-card hover-lift">
        <div class="feature-icon">
            <i class="fas fa-share-alt"></i>
        </div>
        <h5>Kit Promotionnel</h5>
        <p>Outils et liens pour promouvoir votre nomination</p>
        <a href="share-nomination.php" class="btn btn-success">
            <i class="fas fa-rocket me-1"></i> Promouvoir
        </a>
    </div>
    
    <div class="feature-card hover-lift">
        <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <h5>Transparence</h5>
        <p>Suivez l'état des votes (sans résultats détaillés)</p>
        <a href="status-votes.php" class="btn btn-info">
            <i class="fas fa-chart-bar me-1"></i> Voir le statut
        </a>
    </div>
    
    <div class="feature-card hover-lift">
        <div class="feature-icon">
            <i class="fas fa-file-contract"></i>
        </div>
        <h5>Règlement</h5>
        <p>Consultez les règles importantes pour les nominés</p>
        <a href="reglement.php" class="btn btn-warning">
            <i class="fas fa-book me-1"></i> Consulter
        </a>
    </div>
</div>

<!-- Aviso importante -->
<div class="alert alert-warning">
    <div class="d-flex align-items-start">
        <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
        <div>
            <h6 class="mb-2">Important pour tous les nominés</h6>
            <ul class="mb-0">
                <li>❌ <strong>Ne jamais</strong> tenter d'acheter des votes ou utiliser des bots</li>
                <li>❌ <strong>Ne jamais</strong> partager des informations sensibles sur les votes</li>
                <li>✅ <strong>Toujours</strong> respecter le règlement et les délais</li>
                <li>✅ <strong>Utiliser</strong> uniquement les outils officiels de promotion</li>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?> 

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <h5>Social Media Awards</h5>
                    <p>Célébrons la créativité numérique ensemble.</p>
                </div>
                <div>
                    <h5>Liens utiles</h5>
                    <ul class="footer-links">
                        <li><a href="/Social-Media-Awards-/views/candidate/reglement.php">Règlement</a></li>
                        <li><a href="/Social-Media-Awards-/contact.php">Contact</a></li>
                        <li><a href="/Social-Media-Awards-/about.php">À propos</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Assistance</h5>
                    <ul class="footer-links">
                        <li><a href="mailto:support@socialmediaawards.fr">support@socialmediaawards.fr</a></li>
                        <li><a href="tel:+33123456789">+33 1 23 45 67 89</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="mb-0">&copy; <?= date('Y') ?> Social Media Awards. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Scripts personalizados -->
    <script src="/Social-Media-Awards-/assets/js/candidat.js"></script>
    
    <script>
    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Animar contadores de estatísticas
        const statNumbers = document.querySelectorAll('.stat-number[data-count]');
        statNumbers.forEach(element => {
            const target = parseInt(element.getAttribute('data-count'));
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString('fr-FR');
            }, 16);
        });
        
        // Contador regressivo
        function updateCountdown(endDate) {
            const now = new Date().getTime();
            const distance = endDate - now;
            
            if (distance < 0) return;
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            const elements = {
                days: document.querySelector('.countdown-days'),
                hours: document.querySelector('.countdown-hours'),
                minutes: document.querySelector('.countdown-minutes'),
                seconds: document.querySelector('.countdown-seconds')
            };
            
            if (elements.days) elements.days.textContent = days;
            if (elements.hours) elements.hours.textContent = hours.toString().padStart(2, '0');
            if (elements.minutes) elements.minutes.textContent = minutes.toString().padStart(2, '0');
            if (elements.seconds) elements.seconds.textContent = seconds.toString().padStart(2, '0');
        }
        
        // Inicializar contador se existir
        const countdownElement = document.querySelector('[data-countdown]');
        if (countdownElement) {
            const endDate = new Date(countdownElement.getAttribute('data-countdown')).getTime();
            setInterval(() => updateCountdown(endDate), 1000);
        }
    });
    </script>
</body>
</html>
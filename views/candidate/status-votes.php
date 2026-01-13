<?php
// views/candidate/status-votes.php
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

// Verificar se é nomeado
$userId = $_SESSION['user_id'];
$isNominee = $candidatService->isNominee($userId);

if (!$isNominee) {
    $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
    header('Location: candidate-dashboard.php');
    exit;
}

// Obter nomeações ativas
$nominations = $candidatService->getActiveNominations($userId);
if (empty($nominations)) {
    $_SESSION['error'] = "Aucune nomination active trouvée.";
    header('Location: candidate-dashboard.php');
    exit;
}

// Processar cada nomeação
$nominationData = [];
foreach ($nominations as $nomination) {
    $votingStatus = $candidatService->getVotingStatus($nomination);
    
    // Obter datas
    $startDate = new DateTime($nomination['date_debut_votes'] ?? $nomination['date_debut']);
    $endDate = new DateTime($nomination['date_fin_votes'] ?? $nomination['date_fin']);
    $now = new DateTime();
    
    // Calcular progresso
    $totalDuration = $endDate->getTimestamp() - $startDate->getTimestamp();
    $elapsedDuration = $now->getTimestamp() - $startDate->getTimestamp();
    $progress = $totalDuration > 0 ? min(100, max(0, ($elapsedDuration / $totalDuration) * 100)) : 0;
    
    $nominationData[] = [
        'nomination' => $nomination,
        'status' => $votingStatus,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'progress' => $progress,
        'now' => $now
    ];
}

// Determinar status geral
$overallStatus = 'not_started';
foreach ($nominationData as $data) {
    if ($data['status'] == 'in_progress') {
        $overallStatus = 'in_progress';
        break;
    } elseif ($data['status'] == 'ended') {
        $overallStatus = 'ended';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statut des Votes - Social Media Awards</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/Social-Media-Awards/assets/css/candidat.css">
    
    <style>
    .voting-status-card {
        background: white;
        border-radius: var(--border-radius-xl);
        padding: var(--spacing-xl);
        border: none;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }
    
    .voting-status-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
    }
    
    .status-in-progress::before { background: linear-gradient(90deg, var(--success), #28D193); }
    .status-ended::before { background: linear-gradient(90deg, var(--gray), #8e9aaf); }
    .status-not-started::before { background: linear-gradient(90deg, var(--warning), #FFB347); }
    
    .progress-container {
        background: var(--light-gray);
        border-radius: 10px;
        height: 10px;
        overflow: hidden;
        margin: var(--spacing-md) 0;
    }
    
    .progress-bar-animated {
        height: 100%;
        background: linear-gradient(90deg, var(--principal), var(--principal-dark));
        border-radius: 10px;
        position: relative;
        overflow: hidden;
    }
    
    .progress-bar-animated::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
    
    .time-remaining {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--principal-dark), var(--principal));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .transparency-card {
        background: linear-gradient(135deg, rgba(79, 189, 171, 0.05), rgba(79, 189, 171, 0.02));
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-lg);
        border: 2px solid rgba(79, 189, 171, 0.1);
    }
    
    .phase-indicator {
        display: flex;
        justify-content: space-between;
        margin: var(--spacing-xl) 0;
        position: relative;
    }
    
    .phase-indicator::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--border-color);
        z-index: 1;
    }
    
    .phase-step {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }
    
    .phase-dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--border-color);
        margin: 0 auto 10px;
        position: relative;
        z-index: 3;
    }
    
    .phase-step.active .phase-dot {
        background: var(--principal);
        box-shadow: 0 0 0 4px rgba(79, 189, 171, 0.2);
    }
    
    .phase-step.completed .phase-dot {
        background: var(--success);
    }
    
    .info-bubble {
        background: var(--white);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        position: relative;
    }
    
    .info-bubble::before {
        content: '';
        position: absolute;
        top: -8px;
        left: 20px;
        width: 15px;
        height: 15px;
        background: var(--white);
        border-left: 1px solid var(--border-color);
        border-top: 1px solid var(--border-color);
        transform: rotate(45deg);
    }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/Social-Media-Awards/index.php">
                <i class="fas fa-trophy me-2"></i>Social Media Awards
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="candidate-dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                </a>
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['user_pseudonyme'] ?? 'Nominé') ?>
                </span>
                <a class="nav-link" href="/Social-Media-Awards/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
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
                <!-- Cabeçalho -->
                <div class="page-header mb-4">
                    <h1 class="page-title">
                        <i class="fas fa-chart-line me-2"></i>Statut des Votes
                    </h1>
                    <p class="page-subtitle">Transparence et suivi de la phase de votes</p>
                </div>
                
                <!-- Status geral -->
                <div class="voting-status-card mb-4 <?= 'status-' . str_replace('_', '-', $overallStatus) ?>">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-3">
                                <?php if ($overallStatus == 'in_progress'): ?>
                                <i class="fas fa-vote-yea text-success me-2"></i>
                                Votes en cours
                                <?php elseif ($overallStatus == 'ended'): ?>
                                <i class="fas fa-flag-checkered text-secondary me-2"></i>
                                Votes terminés
                                <?php else: ?>
                                <i class="fas fa-clock text-warning me-2"></i>
                                Votes à venir
                                <?php endif; ?>
                            </h3>
                            
                            <p class="mb-4">
                                <?php if ($overallStatus == 'in_progress'): ?>
                                La phase de votes est actuellement en cours. Encouragez votre communauté à voter !
                                <?php elseif ($overallStatus == 'ended'): ?>
                                La phase de votes est terminée. Les résultats seront annoncés prochainement.
                                <?php else: ?>
                                La phase de votes n'a pas encore commencé. Préparez votre stratégie de promotion.
                                <?php endif; ?>
                            </p>
                            
                            <!-- Progresso -->
                            <?php if ($overallStatus == 'in_progress'): 
                                $firstNomination = $nominationData[0];
                            ?>
                            <div class="progress-container">
                                <div class="progress-bar-animated" 
                                     style="width: <?= $firstNomination['progress'] ?>%">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span>Début : <?= $firstNomination['startDate']->format('d/m H:i') ?></span>
                                <span><?= round($firstNomination['progress']) ?>%</span>
                                <span>Fin : <?= $firstNomination['endDate']->format('d/m H:i') ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4 text-center">
                            <?php if ($overallStatus == 'in_progress'): 
                                $firstNomination = $nominationData[0];
                                $interval = $firstNomination['now']->diff($firstNomination['endDate']);
                            ?>
                            <div class="time-remaining">
                                <?= $interval->days ?>j
                            </div>
                            <p class="text-muted mb-0">avant la fin des votes</p>
                            <?php elseif ($overallStatus == 'ended'): ?>
                            <div class="text-secondary">
                                <i class="fas fa-flag-checkered fa-4x"></i>
                            </div>
                            <p class="text-muted mb-0">Votes terminés</p>
                            <?php else: ?>
                            <div class="text-warning">
                                <i class="fas fa-clock fa-4x"></i>
                            </div>
                            <p class="text-muted mb-0">À venir</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Todas as nomeações -->
                <?php foreach ($nominationData as $data): 
                    $nomination = $data['nomination'];
                    $status = $data['status'];
                    $startDate = $data['startDate'];
                    $endDate = $data['endDate'];
                    $now = $data['now'];
                ?>
                <div class="main-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-medal me-2"></i>
                            <?= htmlspecialchars($nomination['libelle']) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Informações da nomeação -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="info-bubble">
                                            <small class="text-muted d-block">Catégorie</small>
                                            <strong><?= htmlspecialchars($nomination['categorie_nom']) ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-bubble">
                                            <small class="text-muted d-block">Édition</small>
                                            <strong><?= htmlspecialchars($nomination['edition_nom']) ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-bubble">
                                            <small class="text-muted d-block">Plateforme</small>
                                            <strong><?= htmlspecialchars($nomination['plateforme']) ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Status detalhado -->
                                <div class="alert 
                                    <?= $status == 'in_progress' ? 'alert-success' : 
                                       ($status == 'ended' ? 'alert-secondary' : 'alert-warning') ?>">
                                    <div class="d-flex align-items-center">
                                        <i class="fas 
                                            <?= $status == 'in_progress' ? 'fa-vote-yea' : 
                                               ($status == 'ended' ? 'fa-flag-checkered' : 'fa-clock') ?> 
                                            fa-2x me-3">
                                        </i>
                                        <div>
                                            <h6 class="mb-1">
                                                <?= $status == 'in_progress' ? 'Votes en cours' : 
                                                   ($status == 'ended' ? 'Votes terminés' : 'Votes non commencés') ?>
                                            </h6>
                                            <p class="mb-0">
                                                <?php if ($status == 'in_progress'): ?>
                                                La communauté vote actuellement pour cette catégorie.
                                                <?php elseif ($status == 'ended'): ?>
                                                Les votes sont clos pour cette catégorie.
                                                <?php else: ?>
                                                Les votes débuteront le <?= $startDate->format('d/m/Y à H:i') ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Fases do processo -->
                                <div class="phase-indicator">
                                    <div class="phase-step <?= $status != 'not_started' ? 'completed' : '' ?>">
                                        <div class="phase-dot"></div>
                                        <small>Préparation</small>
                                    </div>
                                    <div class="phase-step <?= $status == 'in_progress' || $status == 'ended' ? 'completed' : '' ?>">
                                        <div class="phase-dot <?= $status == 'in_progress' ? 'active' : '' ?>"></div>
                                        <small>Votes</small>
                                    </div>
                                    <div class="phase-step <?= $status == 'ended' ? 'completed' : '' ?>">
                                        <div class="phase-dot <?= $status == 'ended' ? 'active' : '' ?>"></div>
                                        <small>Résultats</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- Timer ou datas -->
                                <div class="transparency-card">
                                    <?php if ($status == 'in_progress'): 
                                        $interval = $now->diff($endDate);
                                    ?>
                                    <h6 class="text-center mb-3">
                                        <i class="fas fa-hourglass-half me-2"></i>
                                        Temps restant
                                    </h6>
                                    <div class="text-center mb-3">
                                        <div class="display-4">
                                            <?= $interval->days ?>
                                        </div>
                                        <small class="text-muted">jours</small>
                                    </div>
                                    <div class="text-center">
                                        <?= str_pad($interval->h, 2, '0', STR_PAD_LEFT) ?>h 
                                        <?= str_pad($interval->i, 2, '0', STR_PAD_LEFT) ?>m
                                    </div>
                                    <p class="text-center small text-muted mt-2">
                                        Fin : <?= $endDate->format('d/m H:i') ?>
                                    </p>
                                    <?php else: ?>
                                    <h6 class="text-center mb-3">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Dates importantes
                                    </h6>
                                    <ul class="list-unstyled small">
                                        <li class="mb-2">
                                            <i class="fas fa-play-circle text-primary me-2"></i>
                                            <strong>Début :</strong><br>
                                            <?= $startDate->format('d/m/Y H:i') ?>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-flag-checkered text-success me-2"></i>
                                            <strong>Fin :</strong><br>
                                            <?= $endDate->format('d/m/Y H:i') ?>
                                        </li>
                                        <li>
                                            <i class="fas fa-chart-bar text-info me-2"></i>
                                            <strong>Résultats :</strong><br>
                                            <?= date('d/m/Y', strtotime('+3 days', $endDate->getTimestamp())) ?>
                                        </li>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Ações -->
                                <div class="mt-3">
                                    <?php if ($status == 'in_progress'): ?>
                                    <a href="share-nomination.php" class="btn btn-success w-100 mb-2">
                                        <i class="fas fa-share-alt me-2"></i> Promouvoir
                                    </a>
                                    <?php endif; ?>
                                    <a href="nominee-profile.php?nomination=<?= $nomination['id_nomination'] ?>" 
                                       class="btn btn-primary w-100">
                                        <i class="fas fa-eye me-2"></i> Voir le profil
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Transparência -->
                        <div class="transparency-card mt-4">
                            <h6><i class="fas fa-shield-alt me-2"></i>Transparence du processus</h6>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-lock fa-2x text-primary mb-2"></i>
                                        <h6>Votes sécurisés</h6>
                                        <small class="text-muted">Système crypté et anonyme</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-balance-scale fa-2x text-success mb-2"></i>
                                        <h6>Équité garantie</h6>
                                        <small class="text-muted">Pas d'influence extérieure</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-user-shield fa-2x text-warning mb-2"></i>
                                        <h6>Contrôle anti-fraude</h6>
                                        <small class="text-muted">Détection automatique</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Informações importantes -->
                <div class="main-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Informations importantes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-check-circle me-2"></i>Ce que vous pouvez savoir</h6>
                                    <ul class="mb-0">
                                        <li>Statut général des votes</li>
                                        <li>Dates de début et fin</li>
                                        <li>Temps restant (si en cours)</li>
                                        <li>Prochaines étapes</li>
                                        <li>Règles de transparence</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-ban me-2"></i>Ce que vous ne pouvez pas savoir</h6>
                                    <ul class="mb-0">
                                        <li>Nombre de votes reçus</li>
                                        <li>Classement en temps réel</li>
                                        <li>Pourcentages exacts</li>
                                        <li>Comparaison avec autres</li>
                                        <li>Résultats avant annonce</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ -->
                        <div class="mt-4">
                            <h6 class="mb-3">Questions fréquentes</h6>
                            <div class="accordion" id="faqAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#faq1">
                                            Pourquoi ne vois-je pas mon nombre de votes ?
                                        </button>
                                    </h2>
                                    <div id="faq1" class="accordion-collapse collapse" 
                                         data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Pour garantir l'équité et éviter toute manipulation, les résultats ne sont pas affichés avant la fin officielle des votes.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#faq2">
                                            Quand seront annoncés les résultats ?
                                        </button>
                                    </h2>
                                    <div id="faq2" class="accordion-collapse collapse" 
                                         data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Les résultats seront officiellement annoncés 3 jours après la fin des votes, après vérification et validation par notre équipe.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#faq3">
                                            Comment puis-je promouvoir ma nomination ?
                                        </button>
                                    </h2>
                                    <div id="faq3" class="accordion-collapse collapse" 
                                         data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Utilisez le <a href="share-nomination.php">kit promotionnel</a> pour partager votre lien public sur vos réseaux sociaux. N'oubliez pas d'utiliser les hashtags officiels !
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contato -->
                        <div class="text-center mt-4">
                            <p class="text-muted">
                                Des questions ? Contactez notre équipe :
                                <a href="mailto:support-votes@socialmediaawards.fr" class="text-primary">
                                    support-votes@socialmediaawards.fr
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <h5>Social Media Awards</h5>
                    <p>Célébrons la créativité numérique ensemble.</p>
                </div>
                <div>
                    <h5>Transparence</h5>
                    <ul class="footer-links">
                        <li><a href="reglement.php">Règlement des votes</a></li>
                        <li><a href="mailto:transparence@socialmediaawards.fr">Demande d'information</a></li>
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
    
    <script>
    // Atualizar contadores em tempo real
    function updateAllCountdowns() {
        document.querySelectorAll('.countdown-timer').forEach(timer => {
            const endDate = new Date(timer.getAttribute('data-countdown')).getTime();
            const now = new Date().getTime();
            const distance = endDate - now;
            
            if (distance < 0) return;
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            const dayEl = timer.querySelector('.countdown-days');
            const hourEl = timer.querySelector('.countdown-hours');
            const minEl = timer.querySelector('.countdown-minutes');
            const secEl = timer.querySelector('.countdown-seconds');
            
            if (dayEl) dayEl.textContent = days;
            if (hourEl) hourEl.textContent = hours.toString().padStart(2, '0');
            if (minEl) minEl.textContent = minutes.toString().padStart(2, '0');
            if (secEl) secEl.textContent = seconds.toString().padStart(2, '0');
        });
    }
    
    // Atualizar a cada segundo
    setInterval(updateAllCountdowns, 1000);
    updateAllCountdowns();
    </script>
</body>
</html>
<?php
// views/candidate/nominee-profile.php
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

// Obter nomeação específica ou a primeira
$nominationId = $_GET['nomination'] ?? null;
if ($nominationId) {
    $nomination = array_filter($nominations, function($nom) use ($nominationId) {
        return $nom['id_nomination'] == $nominationId;
    });
    $nomination = reset($nomination);
} else {
    $nomination = $nominations[0];
}

if (!$nomination) {
    $_SESSION['error'] = "Nomination non trouvée.";
    header('Location: candidate-dashboard.php');
    exit;
}

// Gerar URL pública
$publicProfileUrl = "https://" . $_SERVER['HTTP_HOST'] . "/Social-Media-Awards/nominee.php?id=" . $nomination['id_nomination'];

// Obter estatísticas básicas (sem detalhes)
$votingStatus = $candidatService->getVotingStatus($nomination);
$statusClass = '';
if ($votingStatus == 'in_progress') $statusClass = 'status-active';
elseif ($votingStatus == 'ended') $statusClass = 'status-ended';
else $statusClass = 'status-pending';

// Verificar se pode editar perfil
$canEditProfile = $candidatService->canEditProfile($userId);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil Public - Social Media Awards</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/Social-Media-Awards/assets/css/candidat.css">
    
    <style>
    .profile-header {
        background: linear-gradient(135deg, var(--principal), var(--principal-dark));
        color: white;
        border-radius: var(--border-radius-xl);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
        position: relative;
        overflow: hidden;
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="none"/><path d="M20,20 L80,20 L80,80 L20,80 Z" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>');
        opacity: 0.3;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: var(--shadow-lg);
        object-fit: cover;
    }
    
    .social-links {
        display: flex;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-md);
    }
    
    .social-link {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .social-link:hover {
        transform: translateY(-3px);
    }
    
    .social-instagram { background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D); }
    .social-tiktok { background: linear-gradient(45deg, #000000, #25F4EE, #FE2C55); }
    .social-youtube { background: #FF0000; }
    .social-twitter { background: #1DA1F2; }
    .social-facebook { background: #1877F2; }
    
    .nomination-card-public {
        border-left: 6px solid var(--tertiary);
        border: none;
        box-shadow: var(--shadow-xl);
    }
    
    .copy-link-container {
        background: linear-gradient(135deg, rgba(79, 189, 171, 0.1), rgba(79, 189, 171, 0.05));
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-md);
        border: 2px solid var(--principal-light);
    }
    
    .url-display {
        background: white;
        border-radius: var(--border-radius-md);
        padding: var(--spacing-sm) var(--spacing-md);
        font-family: monospace;
        border: 1px solid var(--border-color);
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .nominee-rules {
        background: linear-gradient(135deg, rgba(255, 213, 128, 0.1), rgba(255, 213, 128, 0.05));
        border-left: 4px solid var(--warning);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-lg);
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
            <!-- Sidebar do nomeado -->
            <div class="col-md-3">
                <div class="sidebar-container">
                    <div class="sidebar-card mb-4">
                        <div class="card-body">
                            <div class="sidebar-title">
                                <i class="fas fa-trophy"></i>
                                MENU NOMINÉ
                            </div>
                            <ul class="nav-candidat">
                                <li>
                                    <a class="nav-link" href="candidate-dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i>
                                        <span>Tableau de bord</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link active" href="nominee-profile.php">
                                        <i class="fas fa-id-badge"></i>
                                        <span>Mon profil public</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link" href="share-nomination.php">
                                        <i class="fas fa-share-alt"></i>
                                        <span>Partager ma nomination</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link" href="status-votes.php">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Statut des votes</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link" href="reglement.php">
                                        <i class="fas fa-file-contract"></i>
                                        <span>Règlement</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link" href="/Social-Media-Awards/logout.php">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Déconnexion</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Badge de status -->
                    <div class="status-badge-card text-center">
                        <span class="nominee-badge">
                            <i class="fas fa-medal"></i> NOMINÉ
                        </span>
                        <h5 class="mt-3">Profil Public</h5>
                        <p class="text-muted small">Version visible par les votants</p>
                        
                        <?php if (!$canEditProfile): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-lock"></i>
                            <small>Modification désactivée pendant les votes</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-9">
                <!-- Cabeçalho -->
                <div class="page-header mb-4">
                    <h1 class="page-title">
                        <i class="fas fa-id-badge me-2"></i>Mon Profil Public
                    </h1>
                    <p class="page-subtitle">Votre profil visible par les votants</p>
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
                
                <!-- Perfil -->
                <div class="profile-header mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <?php if ($nomination['photo_profil']): ?>
                            <img src="/Social-Media-Awards/public/<?= htmlspecialchars($nomination['photo_profil']) ?>" 
                                 class="profile-avatar mb-3">
                            <?php else: ?>
                            <div class="profile-avatar mb-3 d-flex align-items-center justify-content-center bg-white text-primary">
                                <i class="fas fa-user fa-3x"></i>
                            </div>
                            <?php endif; ?>
                            
                            <div class="social-links justify-content-center">
                                <?php if ($nomination['url_instagram']): ?>
                                <a href="<?= htmlspecialchars($nomination['url_instagram']) ?>" 
                                   class="social-link social-instagram" target="_blank">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($nomination['url_tiktok']): ?>
                                <a href="<?= htmlspecialchars($nomination['url_tiktok']) ?>" 
                                   class="social-link social-tiktok" target="_blank">
                                    <i class="fab fa-tiktok"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($nomination['url_youtube']): ?>
                                <a href="<?= htmlspecialchars($nomination['url_youtube']) ?>" 
                                   class="social-link social-youtube" target="_blank">
                                    <i class="fab fa-youtube"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($nomination['url_twitter']): ?>
                                <a href="<?= htmlspecialchars($nomination['url_twitter']) ?>" 
                                   class="social-link social-twitter" target="_blank">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h2 class="mb-2"><?= htmlspecialchars($nomination['pseudonyme'] ?? 'Nominé') ?></h2>
                                    <p class="mb-4"><?= htmlspecialchars($nomination['bio'] ?? 'Candidat aux Social Media Awards') ?></p>
                                </div>
                                
                                <div class="text-end">
                                    <span class="badge bg-warning text-dark fs-6">
                                        <i class="fas fa-medal me-1"></i> NOMINÉ
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Status da votação -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="alert <?= $statusClass == 'status-active' ? 'alert-success' : 'alert-secondary' ?>">
                                        <h6 class="mb-1">
                                            <i class="fas fa-vote-yea me-2"></i>
                                            <?= $votingStatus == 'in_progress' ? 'Votes en cours' : 
                                               ($votingStatus == 'ended' ? 'Votes terminés' : 'Votes à venir') ?>
                                        </h6>
                                        <?php if ($votingStatus == 'in_progress'): ?>
                                        <small class="d-block">Encouragez votre communauté à voter !</small>
                                        <?php elseif ($votingStatus == 'ended'): ?>
                                        <small class="d-block">Résultats bientôt disponibles</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="copy-link-container">
                                        <h6><i class="fas fa-link me-2"></i>Lien public du profil</h6>
                                        <div class="input-group mt-2">
                                            <input type="text" class="form-control url-display" 
                                                   value="<?= htmlspecialchars($publicProfileUrl) ?>" 
                                                   id="profile-url" readonly>
                                            <button class="btn btn-primary" onclick="copyProfileUrl()">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Partagez ce lien pour que vos fans puissent voter
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Conteúdo da nomeação -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="main-card nomination-card-public mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-video me-2"></i>
                                    Ma nomination
                                </h5>
                            </div>
                            <div class="card-body">
                                <h4 class="mb-4"><?= htmlspecialchars($nomination['libelle']) ?></h4>
                                
                                <!-- Categoria e plataforma -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                                <i class="fas fa-tag text-primary"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Catégorie</small>
                                                <strong><?= htmlspecialchars($nomination['categorie_nom']) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                                                <i class="fab fa-<?= strtolower($nomination['plateforme']) ?> text-success"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Plateforme</small>
                                                <strong><?= htmlspecialchars($nomination['plateforme']) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Argumentaire -->
                                <div class="mb-4">
                                    <h6><i class="fas fa-comment me-2"></i>Mon argumentaire</h6>
                                    <div class="bg-light p-4 rounded mt-2">
                                        <?= nl2br(htmlspecialchars($nomination['argumentaire'])) ?>
                                    </div>
                                </div>
                                
                                <!-- Link do conteúdo -->
                                <div class="mb-4">
                                    <h6><i class="fas fa-external-link-alt me-2"></i>Voir mon contenu</h6>
                                    <a href="<?= htmlspecialchars($nomination['url_contenu']) ?>" 
                                       target="_blank" class="btn btn-outline-primary">
                                        <i class="fas fa-play-circle me-2"></i>
                                        Voir sur <?= htmlspecialchars($nomination['plateforme']) ?>
                                    </a>
                                </div>
                                
                                <!-- Imagem (se existir) -->
                                <?php if ($nomination['url_image']): ?>
                                <div class="mt-4">
                                    <h6><i class="fas fa-image me-2"></i>Image de présentation</h6>
                                    <img src="/Social-Media-Awards/public/<?= htmlspecialchars($nomination['url_image']) ?>" 
                                         class="img-fluid rounded mt-2" 
                                         alt="<?= htmlspecialchars($nomination['libelle']) ?>">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Regras importantes -->
                        <div class="main-card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Important
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="nominee-rules">
                                    <h6 class="mb-3">Règles pour les nominés</h6>
                                    <ul class="small mb-0">
                                        <li class="mb-2">✅ Partagez votre lien public</li>
                                        <li class="mb-2">✅ Utilisez les hashtags officiels</li>
                                        <li class="mb-2">❌ N'achetez pas de votes</li>
                                        <li class="mb-2">❌ N'utilisez pas de bots</li>
                                        <li>❌ Ne partagez pas d'informations sensibles</li>
                                    </ul>
                                </div>
                                
                                <!-- Botões de ação -->
                                <div class="mt-4">
                                    <a href="share-nomination.php" class="btn btn-success w-100 mb-2">
                                        <i class="fas fa-share-alt me-2"></i> Kit promotionnel
                                    </a>
                                    <a href="status-votes.php" class="btn btn-info w-100 mb-2">
                                        <i class="fas fa-chart-line me-2"></i> Statut des votes
                                    </a>
                                    <a href="reglement.php" class="btn btn-warning w-100">
                                        <i class="fas fa-book me-2"></i> Règlement complet
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Atualizar perfil -->
                        <?php if ($canEditProfile): ?>
                        <div class="main-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>
                                    Modifier mon profil
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted">
                                    Vous pouvez modifier votre profil tant que les votes ne sont pas commencés.
                                </p>
                                <a href="edit-profile.php" class="btn btn-primary w-100">
                                    <i class="fas fa-pencil-alt me-2"></i> Modifier le profil
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
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
                    <h5>Support Nominés</h5>
                    <ul class="footer-links">
                        <li><a href="mailto:nominations@socialmediaawards.fr">nominations@socialmediaawards.fr</a></li>
                        <li><a href="tel:+33123456789">+33 1 23 45 67 89</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Suivez-nous</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fab fa-instagram me-2"></i>Instagram</a></li>
                        <li><a href="#"><i class="fab fa-tiktok me-2"></i>TikTok</a></li>
                        <li><a href="#"><i class="fab fa-twitter me-2"></i>Twitter</a></li>
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
    <script src="/Social-Media-Awards/assets/js/candidat.js"></script>
    
    <script>
    function copyProfileUrl() {
        const urlInput = document.getElementById('profile-url');
        urlInput.select();
        urlInput.setSelectionRange(0, 99999);
        
        navigator.clipboard.writeText(urlInput.value).then(() => {
            const button = event.target.closest('button');
            const original = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.add('btn-success');
            
            setTimeout(() => {
                button.innerHTML = original;
                button.classList.remove('btn-success');
            }, 2000);
        });
    }
    
    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
</body>
</html>
<?php
// Limpar session messages
unset($_SESSION['success']);
unset($_SESSION['error']);
?>
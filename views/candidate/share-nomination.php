<?php
// views/candidate/share-nomination.php
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

$nomination = $nominations[0];
$publicProfileUrl = "https://" . $_SERVER['HTTP_HOST'] . "/Social-Media-Awards/nominee.php?id=" . $nomination['id_nomination'];

// Textos pré-gerados
$shareTexts = [
    'twitter' => "Votez pour moi aux Social Media Awards 2025 🏆\n\nJe suis nominé dans la catégorie \"" . $nomination['categorie_nom'] . "\" !\n\n" . $publicProfileUrl . "\n\n#SocialMediaAwards2025 #VotezPourMoi",
    'instagram' => "🏆 JE SUIS NOMINÉ ! 🏆\n\nJe participe aux Social Media Awards 2025 dans la catégorie \"" . $nomination['categorie_nom'] . "\" !\n\nAidez-moi à gagner en votant pour moi via le lien dans ma bio ⬆️\n\nMerci pour votre soutien ! ❤️\n\n" . $publicProfileUrl . "\n\n#SocialMediaAwards2025 #Nomination #Vote",
    'tiktok' => "Je suis nominé aux Social Media Awards 2025 ! 🏆\nVotez pour moi via le lien dans ma bio !\n\n#SocialMediaAwards2025 #Nomination #VotezPourMoi",
    'whatsapp' => "Salut ! Je suis nominé aux Social Media Awards 2025 dans la catégorie \"" . $nomination['categorie_nom'] . "\" ! 🏆\n\nPeux-tu voter pour moi ? Ça me ferait super plaisir !\n\n" . $publicProfileUrl . "\n\nMerci beaucoup ! 🙏",
    'email' => "Bonjour,\n\nJe suis ravi de vous annoncer que je suis nominé aux Social Media Awards 2025 dans la catégorie \"" . $nomination['categorie_nom'] . "\" ! 🏆\n\nVotre soutien serait très précieux pour moi. Pouvez-vous prendre quelques secondes pour voter ?\n\nLien pour voter : " . $publicProfileUrl . "\n\nMerci infiniment pour votre aide !\n\nCordialement,\n" . ($nomination['pseudonyme'] ?? 'Votre nominé')
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kit Promotionnel - Social Media Awards</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/candidat.css">
    
    <style>
    .share-platform-card {
        background: white;
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-xl);
        border: none;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .share-platform-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }
    
    .share-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: var(--spacing-md);
        color: white;
    }
    
    .share-text-preview {
        background: var(--light-gray);
        border-radius: var(--border-radius-md);
        padding: var(--spacing-md);
        max-height: 200px;
        overflow-y: auto;
        font-size: 0.875rem;
        text-align: left;
        white-space: pre-wrap;
    }
    
    .share-stats {
        background: linear-gradient(135deg, var(--principal), var(--principal-dark));
        color: white;
        border-radius: var(--border-radius-xl);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
    }
    
    .hashtag-container {
        background: white;
        border-radius: var(--border-radius-lg);
        padding: var(--spacing-lg);
        border: 2px solid var(--border-color);
    }
    
    .hashtag {
        display: inline-block;
        background: linear-gradient(135deg, rgba(79, 189, 171, 0.1), rgba(79, 189, 171, 0.05));
        color: var(--principal-dark);
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: 50px;
        margin: 3px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .hashtag:hover {
        background: linear-gradient(135deg, var(--principal-light), var(--principal));
        color: white;
        transform: translateY(-2px);
    }
    
    .copy-btn {
        position: relative;
        overflow: hidden;
    }
    
    .copy-btn.copied::after {
        content: 'Copié !';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--success);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeOut 2s forwards;
    }
    
    @keyframes fadeOut {
        0% { opacity: 1; }
        70% { opacity: 1; }
        100% { opacity: 0; }
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
                        <i class="fas fa-share-alt me-2"></i>Kit Promotionnel
                    </h1>
                    <p class="page-subtitle">Outils pour promouvoir votre nomination</p>
                </div>
                
                <!-- Status da votação -->
                <?php 
                $votingStatus = $candidatService->getVotingStatus($nomination);
                $statusClass = '';
                if ($votingStatus == 'in_progress') $statusClass = 'alert-success';
                elseif ($votingStatus == 'ended') $statusClass = 'alert-secondary';
                else $statusClass = 'alert-warning';
                ?>
                
                <div class="alert <?= $statusClass ?> d-flex align-items-center">
                    <i class="fas fa-bullhorn fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-1">
                            <?= $votingStatus == 'in_progress' ? '🎉 C\'est le moment de promouvoir !' : 
                               ($votingStatus == 'ended' ? '⏰ Les votes sont terminés' : '⏳ Les votes commencent bientôt') ?>
                        </h5>
                        <p class="mb-0">
                            <?php if ($votingStatus == 'in_progress'): ?>
                            Partagez votre nomination avec votre communauté pour maximiser vos chances !
                            <?php elseif ($votingStatus == 'ended'): ?>
                            Merci pour votre participation. Les résultats seront annoncés prochainement.
                            <?php else: ?>
                            Préparez votre stratégie de promotion pour quand les votes commenceront.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <!-- Link público -->
                <div class="main-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-link me-2"></i>
                            Lien public de votre profil
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="text" class="form-control" 
                                           value="<?= htmlspecialchars($publicProfileUrl) ?>" 
                                           id="public-url" readonly>
                                    <button class="btn btn-primary copy-btn" 
                                            onclick="copyToClipboard('public-url', this)">
                                        <i class="fas fa-copy"></i> Copier
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Partagez ce lien partout : bio Instagram/TikTok, stories, emails...
                                </small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="qr-code-placeholder bg-light p-3 rounded">
                                    <i class="fas fa-qrcode fa-3x text-muted"></i>
                                    <small class="d-block mt-2">QR Code (bientôt disponible)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hashtags officiels -->
                <div class="main-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-hashtag me-2"></i>
                            Hashtags officiels
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="hashtag-container">
                            <div class="mb-3">
                                <h6>Hashtags principaux :</h6>
                                <div class="mb-3">
                                    <span class="hashtag" onclick="copyHashtag('#SocialMediaAwards2025')">#SocialMediaAwards2025</span>
                                    <span class="hashtag" onclick="copyHashtag('#VotezPourMoi')">#VotezPourMoi</span>
                                    <span class="hashtag" onclick="copyHashtag('#Nomination')">#Nomination</span>
                                </div>
                                
                                <h6>Par plateforme :</h6>
                                <div class="mb-3">
                                    <span class="hashtag" onclick="copyHashtag('#TikTokAwards')">#TikTokAwards</span>
                                    <span class="hashtag" onclick="copyHashtag('#InstaAwards')">#InstaAwards</span>
                                    <span class="hashtag" onclick="copyHashtag('#YouTubeAwards')">#YouTubeAwards</span>
                                </div>
                                
                                <h6>Votre catégorie :</h6>
                                <div>
                                    <?php 
                                    $categoryHashtag = '#' . str_replace(' ', '', $nomination['categorie_nom']);
                                    ?>
                                    <span class="hashtag" onclick="copyHashtag('<?= $categoryHashtag ?>')">
                                        <?= $categoryHashtag ?>
                                    </span>
                                </div>
                            </div>
                            
                            <button class="btn btn-outline-primary" onclick="copyAllHashtags()">
                                <i class="fas fa-copy me-2"></i> Copier tous les hashtags
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Textos pré-gerados -->
                <div class="main-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-comment-alt me-2"></i>
                            Textes pré-rédigés
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <?php foreach ($shareTexts as $platform => $text): 
                                $platformNames = [
                                    'twitter' => 'Twitter / X',
                                    'instagram' => 'Instagram',
                                    'tiktok' => 'TikTok',
                                    'whatsapp' => 'WhatsApp',
                                    'email' => 'Email'
                                ];
                                $platformIcons = [
                                    'twitter' => 'fab fa-twitter',
                                    'instagram' => 'fab fa-instagram',
                                    'tiktok' => 'fab fa-tiktok',
                                    'whatsapp' => 'fab fa-whatsapp',
                                    'email' => 'fas fa-envelope'
                                ];
                                $platformColors = [
                                    'twitter' => '#1DA1F2',
                                    'instagram' => 'linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D)',
                                    'tiktok' => 'linear-gradient(45deg, #000000, #25F4EE, #FE2C55)',
                                    'whatsapp' => '#25D366',
                                    'email' => '#EA4335'
                                ];
                            ?>
                            <div class="col-md-6">
                                <div class="share-platform-card">
                                    <div class="share-icon" style="background: <?= $platformColors[$platform] ?>">
                                        <i class="<?= $platformIcons[$platform] ?>"></i>
                                    </div>
                                    <h5><?= $platformNames[$platform] ?></h5>
                                    
                                    <div class="share-text-preview w-100 mb-3">
                                        <?= htmlspecialchars(substr($text, 0, 200)) ?>...
                                    </div>
                                    
                                    <div class="mt-auto w-100">
                                        <button class="btn btn-primary w-100 mb-2 copy-btn" 
                                                onclick="copyShareText('<?= $platform ?>', this)">
                                            <i class="fas fa-copy me-2"></i> Copier le texte
                                        </button>
                                        
                                        <?php if ($platform == 'twitter'): ?>
                                        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($text) ?>" 
                                           target="_blank" class="btn btn-outline-primary w-100">
                                            <i class="fab fa-twitter me-2"></i> Poster sur Twitter
                                        </a>
                                        <?php elseif ($platform == 'whatsapp'): ?>
                                        <a href="https://wa.me/?text=<?= urlencode($text) ?>" 
                                           target="_blank" class="btn btn-outline-success w-100">
                                            <i class="fab fa-whatsapp me-2"></i> Partager sur WhatsApp
                                        </a>
                                        <?php elseif ($platform == 'email'): ?>
                                        <a href="mailto:?subject=Je suis nominé aux Social Media Awards 2025&body=<?= urlencode($text) ?>" 
                                           class="btn btn-outline-danger w-100">
                                            <i class="fas fa-paper-plane me-2"></i> Envoyer par email
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <input type="hidden" id="text-<?= $platform ?>" value="<?= htmlspecialchars($text) ?>">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Dicas de promoção -->
                <div class="main-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            Conseils de promotion
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-check-circle me-2"></i>À faire</h6>
                                    <ul class="mb-0">
                                        <li>Partagez dans vos stories Instagram/TikTok</li>
                                        <li>Créez une vidéo de remerciement</li>
                                        <li>Utilisez les hashtags officiels</li>
                                        <li>Remerciez vos supporters</li>
                                        <li>Mettez le lien dans votre bio</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-ban me-2"></i>À éviter</h6>
                                    <ul class="mb-0">
                                        <li>Ne spammez pas vos followers</li>
                                        <li>N'achetez pas de votes</li>
                                        <li>N'utilisez pas de bots</li>
                                        <li>Ne promettez pas de récompenses</li>
                                        <li>Ne trichez pas !</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Timeline de promoção -->
                        <div class="mt-4">
                            <h6 class="mb-3">Calendrier suggéré :</h6>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-badge bg-primary">J-3</div>
                                    <div class="timeline-content">
                                        <strong>Annonce de la nomination</strong>
                                        <p>Partagez la nouvelle avec votre communauté</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-badge bg-success">J-1</div>
                                    <div class="timeline-content">
                                        <strong>Rappel avant les votes</strong>
                                        <p>Préparez votre audience pour le début des votes</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-badge bg-info">Jour J</div>
                                    <div class="timeline-content">
                                        <strong>Début des votes</strong>
                                        <p>Postez le lien de vote partout</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-badge bg-warning">Mi-parcours</div>
                                    <div class="timeline-content">
                                        <strong>Rappel mi-parcours</strong>
                                        <p>Relancez vos supporters</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-badge bg-danger">Dernier jour</div>
                                    <div class="timeline-content">
                                        <strong>Dernier appel</strong>
                                        <p>Dernière chance pour voter</p>
                                    </div>
                                </div>
                            </div>
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
                    <h5>Support Promotion</h5>
                    <ul class="footer-links">
                        <li><a href="mailto:promotion@socialmediaawards.fr">promotion@socialmediaawards.fr</a></li>
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
    
    <script>
    function copyToClipboard(elementId, button) {
        const element = document.getElementById(elementId);
        element.select();
        element.setSelectionRange(0, 99999);
        
        navigator.clipboard.writeText(element.value).then(() => {
            showCopyFeedback(button);
        });
    }
    
    function copyShareText(platform, button) {
        const text = document.getElementById('text-' + platform).value;
        navigator.clipboard.writeText(text).then(() => {
            showCopyFeedback(button);
        });
    }
    
    function copyHashtag(hashtag) {
        navigator.clipboard.writeText(hashtag).then(() => {
            showToast('Hashtag copié : ' + hashtag);
        });
    }
    
    function copyAllHashtags() {
        const hashtags = [
            '#SocialMediaAwards2025',
            '#VotezPourMoi', 
            '#Nomination',
            '#TikTokAwards',
            '#InstaAwards',
            '#YouTubeAwards',
            '#<?= str_replace(' ', '', $nomination['categorie_nom']) ?>'
        ].join(' ');
        
        navigator.clipboard.writeText(hashtags).then(() => {
            showToast('Tous les hashtags ont été copiés');
        });
    }
    
    function showCopyFeedback(button) {
        button.classList.add('copied');
        setTimeout(() => {
            button.classList.remove('copied');
        }, 2000);
    }
    
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <div class="toast-body">
                <i class="fas fa-check-circle me-2 text-success"></i>
                ${message}
            </div>
        `;
        
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 1rem 1.5rem;
            animation: slideInRight 0.3s ease-out;
            z-index: 9999;
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Estilos para animações
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--principal-light);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .timeline-badge {
            position: absolute;
            left: -30px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .timeline-content {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid var(--border-color);
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>
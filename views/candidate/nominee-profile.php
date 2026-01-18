<?php
// views/candidate/nominee-profile.php
session_start();

// Verificar se o usuário está logado como candidato
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: /Social-Media-Awards-/views/login.php');
    exit;
}

// Incluir configurações
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatService;
use App\Services\CategoryService;
use App\Services\EditionService;

$pdo = Database::getInstance()->getConnection();

$candidatService = new CandidatService($pdo);
$categoryService = new CategoryService($pdo);
$editionService = new EditionService($pdo);

$userId = $_SESSION['user_id'];
$isNominee = $candidatService->isNominee($userId);

if (!$isNominee) {
    $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
    header('Location: candidate-dashboard.php');
    exit;
}

$nominations = $candidatService->getActiveNominations($userId);
if (empty($nominations)) {
    $_SESSION['error'] = "Aucune nomination active trouvée.";
    header('Location: candidate-dashboard.php');
    exit;
}

$nominationId = $_GET['nomination'] ?? null;
if ($nominationId) {
    $nomination = array_filter($nominations, function ($nom) use ($nominationId) {
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

$nomination['url_instagram'] = $nomination['url_instagram'] ?? null;
$nomination['url_tiktok'] = $nomination['url_tiktok'] ?? null;
$nomination['url_youtube'] = $nomination['url_youtube'] ?? null;
$nomination['url_twitter'] = $nomination['url_twitter'] ?? null;
$nomination['bio'] = $nomination['bio'] ?? 'Candidat aux Social Media Awards';
$nomination['photo_profil'] = $nomination['photo_profil'] ?? null;
$nomination['url_image'] = $nomination['url_image'] ?? null;

$baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$baseUrl .= "://" . $_SERVER['HTTP_HOST'];
$publicProfileUrl = $baseUrl . "/Social-Media-Awards-/nominee.php?id=" . $nomination['id_nomination'];

$votingStatus = $candidatService->getVotingStatus($nomination);
$statusClass = '';
if ($votingStatus == 'in_progress') $statusClass = 'status-active';
elseif ($votingStatus == 'ended') $statusClass = 'status-ended';
else $statusClass = 'status-pending';

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

    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/candidat.css">

    <style>
        .navbar-dark {
            background: linear-gradient(135deg, #4FBDAB, #45a999) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .navbar-nav .nav-link {
            color: white !important;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #FFD700 !important;
            transform: translateY(-2px);
        }

        .navbar-text {
            color: white !important;
            font-weight: 500;
        }

        /* Sidebar */
        .sidebar-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .sidebar-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }

        .sidebar-title {
            color: #4FBDAB;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-candidat {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-candidat li {
            margin-bottom: 8px;
        }

        .nav-candidat .nav-link {
            color: #333;
            padding: 10px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .nav-candidat .nav-link:hover {
            background: linear-gradient(135deg, rgba(79, 189, 171, 0.1), rgba(79, 189, 171, 0.05));
            color: #4FBDAB;
            transform: translateX(5px);
        }

        .nav-candidat .nav-link.active {
            background: linear-gradient(135deg, #4FBDAB, #45a999);
            color: white;
            font-weight: 600;
        }

        .status-badge-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 20px;
            border: 2px solid #4FBDAB;
        }

        .nominee-badge {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #333;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            display: inline-block;
        }

        /* Conteúdo principal */
        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            color: #4FBDAB;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .profile-header {
            background: linear-gradient(135deg, #4FBDAB, #45a999);
            color: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(79, 189, 171, 0.2);
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
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            object-fit: cover;
            background: white;
        }

        .social-links {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            justify-content: center;
            flex-wrap: wrap;
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
            font-size: 1.2rem;
        }

        .social-link:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .social-instagram {
            background: linear-gradient(45deg, #405DE6, #5851DB, #833AB4, #C13584, #E1306C, #FD1D1D);
        }

        .social-tiktok {
            background: linear-gradient(45deg, #000000, #25F4EE, #FE2C55);
        }

        .social-youtube {
            background: #FF0000;
        }

        .social-twitter {
            background: #1DA1F2;
        }

        .social-facebook {
            background: #1877F2;
        }

        /* Cartões */
        .main-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .main-card .card-header {
            background: linear-gradient(135deg, #4FBDAB, #45a999);
            color: white;
            padding: 20px;
            border-bottom: none;
            font-weight: 600;
        }

        .main-card .card-body {
            padding: 30px;
        }

        .nomination-card-public {
            border-left: 6px solid #4FBDAB;
        }

        .copy-link-container {
            background: linear-gradient(135deg, rgba(79, 189, 171, 0.1), rgba(79, 189, 171, 0.05));
            border-radius: 12px;
            padding: 20px;
            border: 2px solid rgba(79, 189, 171, 0.3);
        }

        .url-display {
            background: white;
            border-radius: 8px;
            padding: 10px 15px;
            font-family: 'Courier New', monospace;
            border: 1px solid #dee2e6;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.9rem;
        }

        .nominee-rules {
            background: linear-gradient(135deg, rgba(255, 213, 128, 0.1), rgba(255, 213, 128, 0.05));
            border-left: 4px solid #FFC107;
            border-radius: 10px;
            padding: 20px;
        }

        .nominee-rules h6 {
            color: #856404;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .nominee-rules ul {
            padding-left: 20px;
            margin-bottom: 0;
        }

        .nominee-rules li {
            margin-bottom: 8px;
            color: #333;
        }

        /* Botões */
        .btn-primary {
            background: linear-gradient(135deg, #4FBDAB, #45a999);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #45a999, #3a8f7f);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(79, 189, 171, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #218838);
            border: none;
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            border: none;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            border: none;
            color: #212529;
        }

        .btn-outline-primary {
            border: 2px solid #4FBDAB;
            color: #4FBDAB;
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: #4FBDAB;
            color: white;
        }

        /* Badges */
        .badge-warning {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #333;
            font-weight: 600;
            padding: 8px 16px;
            font-size: 1rem;
        }

        .badge-success {
            background: linear-gradient(135deg, #28a745, #218838);
        }

        .badge-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
        }

        /* Alerts */
        .alert-success {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #721c24;
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
            border: 1px solid rgba(255, 193, 7, 0.3);
            color: #856404;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #343a40, #212529);
            color: white;
            padding: 40px 0 20px;
            margin-top: 40px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .footer h5 {
            color: #4FBDAB;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #4FBDAB;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #495057;
            color: #adb5bd;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .profile-header {
                padding: 20px;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
            }

            .main-card .card-body {
                padding: 20px;
            }

            .footer-content {
                flex-direction: column;
                gap: 20px;
            }
        }

        /* Animações */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-header,
        .main-card {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/Social-Media-Awards-/index.php">
                <img src="/Social-Media-Awards-/assets/images/logo.png" alt="Logo" class="me-2" width="30" height="30">
                Social Media Awards
            </a>

            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="candidate-dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                </a>
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i> <?= htmlspecialchars($_SESSION['user_pseudonyme'] ?? 'Nominé') ?>
                </span>
                <a class="nav-link" href="/Social-Media-Awards-/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
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
                                    <a class="nav-link" href="../../results.php">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Resultat</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link" href="reglement.php">
                                        <i class="fas fa-file-contract"></i>
                                        <span>Règlement</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link" href="/Social-Media-Awards-/logout.php">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Déconnexion</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

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

            <div class="col-md-9">
                <div class="page-header mb-4">
                    <h1 class="page-title">
                        <i class="fas fa-id-badge me-2"></i>Mon Profil Public
                    </h1>
                    <p class="page-subtitle">Votre profil visible par les votants</p>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php unset($_SESSION['success']);
                endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php unset($_SESSION['error']);
                endif; ?>

                <div class="profile-header mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <?php if ($nomination['photo_profil']): ?>
                                <img src="/Social-Media-Awards-/public/<?= htmlspecialchars($nomination['photo_profil']) ?>"
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
                                    <p class="mb-4"><?= htmlspecialchars($nomination['bio']) ?></p>
                                </div>

                                <div class="text-end">
                                    <span class="badge bg-warning text-dark fs-6">
                                        <i class="fas fa-medal me-1"></i> NOMINÉ
                                    </span>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="alert <?= $votingStatus == 'in_progress' ? 'alert-success' : ($votingStatus == 'ended' ? 'alert-secondary' : 'alert-warning') ?>">
                                        <h6 class="mb-1">
                                            <i class="fas fa-vote-yea me-2"></i>
                                            <?= $votingStatus == 'in_progress' ? 'Votes en cours' : ($votingStatus == 'ended' ? 'Votes terminés' : 'Votes à venir') ?>
                                        </h6>
                                        <?php if ($votingStatus == 'in_progress'): ?>
                                            <small class="d-block">Encouragez votre communauté à voter !</small>
                                        <?php elseif ($votingStatus == 'ended'): ?>
                                            <small class="d-block">Résultats bientôt disponibles</small>
                                        <?php else: ?>
                                            <small class="d-block">Les votes commenceront bientôt</small>
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
                                                <?php
                                                $platformIcon = match (strtolower($nomination['plateforme'])) {
                                                    'tiktok' => 'fab fa-tiktok',
                                                    'instagram' => 'fab fa-instagram',
                                                    'youtube' => 'fab fa-youtube',
                                                    'x' => 'fab fa-x-twitter',
                                                    'facebook' => 'fab fa-facebook',
                                                    'twitch' => 'fab fa-twitch',
                                                    default => 'fas fa-globe'
                                                };
                                                ?>
                                                <i class="<?= $platformIcon ?> text-success"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Plateforme</small>
                                                <strong><?= htmlspecialchars($nomination['plateforme']) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6><i class="fas fa-comment me-2"></i>Mon argumentaire</h6>
                                    <div class="bg-light p-4 rounded mt-2">
                                        <?= nl2br(htmlspecialchars($nomination['argumentaire'])) ?>
                                    </div>
                                </div>

                                <?php if ($nomination['url_image']): ?>
                                    <div class="mt-4">
                                        <h6><i class="fas fa-image me-2"></i>Image de présentation</h6>
                                        <img src="/Social-Media-Awards-/public/<?= htmlspecialchars($nomination['url_image']) ?>"
                                            class="img-fluid rounded mt-2"
                                            alt="<?= htmlspecialchars($nomination['libelle']) ?>"
                                            style="max-height: 300px; object-fit: cover;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">

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
                                    <a href="perfil-candidat.php" class="btn btn-primary w-100">
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
            tooltipTriggerList.map(function(tooltipTriggerEl) {
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
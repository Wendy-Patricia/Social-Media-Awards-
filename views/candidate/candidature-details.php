<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: /Social-Media-Awards/views/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatureService;
use App\Services\CategoryService;

$pdo = Database::getInstance()->getConnection();
$candidatureService = new CandidatureService($pdo);
$categoryService = new CategoryService($pdo);

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = "Candidature non trouvée.";
    header('Location: mes-candidatures.php');
    exit;
}

$candidature = $candidatureService->getCandidatureById((int)$id);

if (!$candidature || $candidature->getIdCompte() !== $_SESSION['user_id']) {
    $_SESSION['error'] = "Candidature non trouvée ou non autorisée.";
    header('Location: mes-candidatures.php');
    exit;
}

// Obter informações da categoria
$category = $categoryService->getCategoryById($candidature->getIdCategorie());
$edition = null;
if ($category) {
    // Obter informações da edição
    $stmt = $pdo->prepare("
        SELECT e.* 
        FROM edition e
        JOIN categorie c ON e.id_edition = c.id_edition
        WHERE c.id_categorie = :id
    ");
    $stmt->execute([':id' => $candidature->getIdCategorie()]);
    $edition = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Formatar data
$dateSoumission = date('d/m/Y à H:i', strtotime($candidature->getDateSoumission()));

// Status colors
$statusColors = [
    'En attente' => 'warning',
    'Approuvée' => 'success',
    'Rejetée' => 'danger',
    'En cours' => 'info'
];

$statusClass = $statusColors[$candidature->getStatut()] ?? 'secondary';

// Platform icons
$platformIcons = [
    'TikTok' => 'fab fa-tiktok',
    'Instagram' => 'fab fa-instagram',
    'YouTube' => 'fab fa-youtube',
    'X' => 'fab fa-x-twitter',
    'Facebook' => 'fab fa-facebook',
    'Twitch' => 'fab fa-twitch'
];

$platformIcon = $platformIcons[$candidature->getPlateforme()] ?? 'fas fa-globe';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Candidature - Social Media Awards</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/candidat.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="candidate-dashboard.php">
                <i class="fas fa-trophy"></i>
                Social Media Awards
            </a>
            <div class="navbar-nav ms-auto">
                <a href="mes-candidatures.php" class="nav-link">
                    <i class="fas fa-arrow-left"></i> Retour aux candidatures
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-file-alt"></i> Détails de la Candidature
                        </h1>
                        <p class="page-subtitle">Informations complètes sur votre candidature</p>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="soumettre-candidature.php?edit=<?= $id ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <button class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="row">
                <!-- Left Column - Candidature Details -->
                <div class="col-lg-8">
                    <!-- Candidature Header Card -->
                    <div class="main-card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle text-primary"></i> 
                                        Informations Générales
                                    </h5>
                                </div>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <i class="fas fa-circle"></i> <?= htmlspecialchars($candidature->getStatut()) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <label><i class="fas fa-heading"></i> Titre</label>
                                        <p class="detail-value"><?= htmlspecialchars($candidature->getLibelle()) ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <label><i class="<?= $platformIcon ?>"></i> Plateforme</label>
                                        <p class="detail-value"><?= htmlspecialchars($candidature->getPlateforme()) ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <label><i class="fas fa-link"></i> URL du Contenu</label>
                                        <p class="detail-value">
                                            <a href="<?= htmlspecialchars($candidature->getUrlContenu()) ?>" 
                                               target="_blank" 
                                               class="text-primary text-decoration-none">
                                                <i class="fas fa-external-link-alt"></i> Voir le contenu
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <label><i class="fas fa-calendar"></i> Date de Soumission</label>
                                        <p class="detail-value"><?= $dateSoumission ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Argumentaire Card -->
                    <div class="main-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-comment-dots text-primary"></i> Argumentaire
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="argumentaire-content">
                                <?= nl2br(htmlspecialchars($candidature->getArgumentaire())) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Image Preview Card -->
                    <?php if (!empty($candidature->getImage())): ?>
                    <div class="main-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-image text-primary"></i> Image de Présentation
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="image-preview-detail">
                                <img src="/Social-Media-Awards-/public/<?= htmlspecialchars($candidature->getImage()) ?>" 
                                     alt="Image de la candidature" 
                                     class="img-fluid rounded shadow">
                                <div class="mt-3">
                                    <a href="/Social-Media-Awards-/public/<?= htmlspecialchars($candidature->getImage()) ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-expand"></i> Voir en grand
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column - Context & Actions -->
                <div class="col-lg-4">
                    <!-- Edition & Category Card -->
                    <div class="sidebar-card mb-4">
                        <div class="card-body">
                            <h6 class="sidebar-title">
                                <i class="fas fa-layer-group"></i> Contexte
                            </h6>
                            <div class="context-info">
                                <?php if ($edition): ?>
                                <div class="context-item mb-3">
                                    <label><i class="fas fa-calendar-alt"></i> Édition</label>
                                    <p class="context-value"><?= htmlspecialchars($edition['nom']) ?>
                                        <?php if (isset($edition['annee'])): ?>
                                            <span class="text-muted">(<?= htmlspecialchars($edition['annee']) ?>)</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($category): ?>
                                <div class="context-item">
                                    <label><i class="fas fa-tag"></i> Catégorie</label>
                                    <p class="context-value"><?= htmlspecialchars($category->getNom()) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline Card -->
                    <div class="sidebar-card mb-4">
                        <div class="card-body">
                            <h6 class="sidebar-title">
                                <i class="fas fa-history"></i> Historique du Statut
                            </h6>
                            <div class="status-timeline">
                                <div class="timeline-item completed">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <h6>Soumission</h6>
                                        <p class="text-muted small"><?= $dateSoumission ?></p>
                                    </div>
                                </div>
                                <div class="timeline-item <?= $candidature->getStatut() !== 'En attente' ? 'completed' : 'current' ?>">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <h6>Évaluation</h6>
                                        <p class="text-muted small">
                                            <?php if ($candidature->getStatut() === 'En attente'): ?>
                                                En cours d'examen
                                            <?php else: ?>
                                                Terminé le <?= date('d/m/Y') ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="timeline-item <?= $candidature->getStatut() !== 'En attente' ? 'completed' : '' ?>">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-content">
                                        <h6>Décision</h6>
                                        <p class="text-muted small"><?= htmlspecialchars($candidature->getStatut()) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="sidebar-card">
                        <div class="card-body">
                            <h6 class="sidebar-title">
                                <i class="fas fa-cogs"></i> Actions
                            </h6>
                            <div class="action-buttons-vertical">
                                <a href="soumettre-candidature.php?edit=<?= $id ?>" 
                                   class="btn-action btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <a href="mes-candidatures.php" 
                                   class="btn-action btn-outline-secondary w-100 mb-2">
                                    <i class="fas fa-list"></i> Retour à la liste
                                </a>
                                <button onclick="window.print()" 
                                        class="btn-action btn-outline-info w-100 mb-2">
                                    <i class="fas fa-print"></i> Imprimer
                                </button>
                                <button onclick="shareCandidature()" 
                                        class="btn-action btn-outline-success w-100">
                                    <i class="fas fa-share-alt"></i> Partager
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container-fluid">
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Social Media Awards. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function shareCandidature() {
            const title = "<?= addslashes($candidature->getLibelle()) ?>";
            const text = "Découvrez ma candidature aux Social Media Awards !";
            const url = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: text,
                    url: url
                });
            } else {
                // Fallback pour les navigateurs qui ne supportent pas l'API Web Share
                navigator.clipboard.writeText(url).then(() => {
                    alert("Lien copié dans le presse-papier !");
                });
            }
        }
        
        // Animation pour les éléments de timeline
        document.addEventListener('DOMContentLoaded', function() {
            const timelineItems = document.querySelectorAll('.timeline-item');
            timelineItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.2}s`;
            });
        });
    </script>
</body>
</html>
<?php
require_once __DIR__ . '/../../../app/autoload.php';
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../views/partials/admin-header.php';

// Check if nomination ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de nomination invalide.";
    header('Location: manage-nominations.php');
    exit;
}

$nominationId = (int)$_GET['id'];
$pdo = Database::getInstance()->getConnection();
$nominationService = new App\Services\NominationService($pdo);

// Get nomination details
$nomination = $nominationService->getNominationById($nominationId);
if (!$nomination) {
    $_SESSION['error'] = "Nomination non trouvée.";
    header('Location: manage-nominations.php');
    exit;
}

// Get category name
$categoryService = new App\Services\CategoryService($pdo);
$category = $categoryService->getCategoryById($nomination->getIdCategorie());

// Count votes for this nomination
$voteCount = $nominationService->countVotesForNomination($nominationId);

// Get related data if needed
// For example: get user who submitted the nomination
$submittedBy = null;
if ($nomination->getIdCompte()) {
    $stmt = $pdo->prepare("SELECT pseudonyme, email FROM compte WHERE id_compte = ?");
    $stmt->execute([$nomination->getIdCompte()]);
    $submittedBy = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get admin who approved
$approvedBy = null;
if ($nomination->getIdAdmin()) {
    $stmt = $pdo->prepare("SELECT pseudonyme FROM compte WHERE id_compte = ?");
    $stmt->execute([$nomination->getIdAdmin()]);
    $approvedBy = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get platform icon
$platform = strtolower($nomination->getPlateforme());
$icon = match ($platform) {
    'tiktok' => 'fa-tiktok',
    'instagram' => 'fa-instagram',
    'youtube' => 'fa-youtube',
    'facebook' => 'fa-facebook',
    'x', 'twitter' => 'fa-x-twitter',
    'twitch' => 'fa-twitch',
    'spotify' => 'fa-spotify',
    default => 'fa-globe'
};

// Format dates
$approvalDate = $nomination->getDateApprobation() 
    ? date('d/m/Y H:i', strtotime($nomination->getDateApprobation()))
    : 'Non approuvé';

// Get image URL with fallback
$imageUrl = $nomination->getUrlImage() 
    ? '/Social-Media-Awards-/' . $nomination->getUrlImage()
    : '/Social-Media-Awards-/assets/images/default-nomination.png';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voir Nomination - Admin</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/admin-nominations.css">
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/admin-common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/brands.min.css">
</head>

<body>
    <div class="admin-container">
        <div class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1><i class="fas fa-eye"></i> Détails de la Nomination</h1>
                    <nav class="breadcrumb">
                        <a href="dashboard.php">Tableau de bord</a> > 
                        <a href="manage-nominations.php">Nominations</a> > 
                        <span>Détails</span>
                    </nav>
                </div>
                <div class="header-actions">
                    <a href="manage-nominations.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <a href="edit-nomination.php?id=<?php echo $nominationId; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                </div>
            </header>

            <main class="admin-content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['success']); 
                        unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['error']); 
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="detail-grid">
                    <!-- Main Information Card -->
                    <div class="card card-main">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> Informations principales</h3>
                        </div>
                        <div class="card-body">
                            <div class="detail-main">
                                <div class="detail-image">
                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                         alt="<?php echo htmlspecialchars($nomination->getLibelle()); ?>"
                                         class="nomination-image"
                                         onerror="this.src='/Social-Media-Awards-/assets/images/default-nomination.png'">
                                </div>
                                <div class="detail-info">
                                    <h2 class="nomination-title"><?php echo htmlspecialchars($nomination->getLibelle()); ?></h2>
                                    
                                    <div class="detail-meta">
                                        <div class="meta-item">
                                            <span class="meta-label"><i class="fas fa-layer-group"></i> Catégorie:</span>
                                            <span class="meta-value">
                                                <span class="badge badge-teal">
                                                    <?php 
                                                    // CORRECTION ICI : utiliser les méthodes getter de l'objet Categorie
                                                    if ($category instanceof \App\Models\Categorie) {
                                                        echo htmlspecialchars($category->getNom());
                                                    } else {
                                                        echo 'Catégorie #' . $nomination->getIdCategorie();
                                                    }
                                                    ?>
                                                </span>
                                            </span>
                                        </div>
                                        
                                        <div class="meta-item">
                                            <span class="meta-label"><i class="fas fa-thumbs-up"></i> Votes:</span>
                                            <span class="meta-value">
                                                <span class="badge badge-blue">
                                                    <i class="fas fa-vote-yea"></i> <?php echo number_format($voteCount); ?> votes
                                                </span>
                                            </span>
                                        </div>
                                        
                                        <div class="meta-item">
                                            <span class="meta-label"><i class="fas fa-calendar-check"></i> Date d'approbation:</span>
                                            <span class="meta-value"><?php echo $approvalDate; ?></span>
                                        </div>
                                        
                                        <div class="meta-item">
                                            <span class="meta-label"><i class="fas fa-user-check"></i> Approuvé par:</span>
                                            <span class="meta-value">
                                                <?php if ($approvedBy): ?>
                                                    <?php echo htmlspecialchars($approvedBy['pseudonyme']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Inconnu</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Platform & Content Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-desktop"></i> Plateforme & Contenu</h3>
                        </div>
                        <div class="card-body">
                            <div class="detail-section">
                                <div class="platform-display">
                                    <span class="platform-badge large">
                                        <i class="fab <?php echo $icon; ?>"></i>
                                        <?php echo htmlspecialchars($nomination->getPlateforme()); ?>
                                    </span>
                                </div>
                                
                                <div class="content-link">
                                    <h4><i class="fas fa-link"></i> Lien du contenu:</h4>
                                    <a href="<?php echo htmlspecialchars($nomination->getUrlContenu()); ?>" 
                                       target="_blank" 
                                       class="content-url">
                                        <i class="fas fa-external-link-alt"></i>
                                        <?php echo htmlspecialchars($nomination->getUrlContenu()); ?>
                                    </a>
                                </div>
                                
                                <?php if ($submittedBy): ?>
                                <div class="submitter-info">
                                    <h4><i class="fas fa-user-plus"></i> Soumis par:</h4>
                                    <div class="user-card">
                                        <i class="fas fa-user-circle user-icon"></i>
                                        <div class="user-details">
                                            <strong><?php echo htmlspecialchars($submittedBy['pseudonyme']); ?></strong>
                                            <div class="text-muted small"><?php echo htmlspecialchars($submittedBy['email']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Argumentation Card -->
                    <div class="card card-argument">
                        <div class="card-header">
                            <h3><i class="fas fa-comment-dots"></i> Argumentaire</h3>
                        </div>
                        <div class="card-body">
                            <div class="argument-content">
                                <?php echo nl2br(htmlspecialchars($nomination->getArgumentaire())); ?>
                            </div>
                            <div class="argument-stats">
                                <span class="text-muted">
                                    <i class="fas fa-text-height"></i> 
                                    <?php echo strlen($nomination->getArgumentaire()); ?> caractères
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-cogs"></i> Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="action-buttons-grid">
                                <a href="edit-nomination.php?id=<?php echo $nominationId; ?>" class="btn btn-primary btn-block">
                                    <i class="fas fa-edit"></i> Modifier cette nomination
                                </a>
                                
                                <a href="#" class="btn btn-info btn-block" onclick="previewNomination()">
                                    <i class="fas fa-eye"></i> Voir en mode public
                                </a>
                                
                                <form method="POST" action="delete-nomination.php" class="delete-form" onsubmit="return confirmDelete()">
                                    <input type="hidden" name="id" value="<?php echo $nominationId; ?>">
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash"></i> Supprimer cette nomination
                                    </button>
                                </form>
                                
                                <a href="manage-nominations.php" class="btn btn-secondary btn-block">
                                    <i class="fas fa-list"></i> Retour à la liste
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical Details (for debugging/advanced users) -->
                <div class="card card-technical">
                    <div class="card-header">
                        <h3><i class="fas fa-code"></i> Informations techniques</h3>
                    </div>
                    <div class="card-body">
                        <div class="technical-grid">
                            <div class="tech-item">
                                <span class="tech-label">ID Nomination:</span>
                                <span class="tech-value"><?php echo $nomination->getIdNomination(); ?></span>
                            </div>
                            <div class="tech-item">
                                <span class="tech-label">ID Candidature:</span>
                                <span class="tech-value"><?php echo $nomination->getIdCandidature(); ?></span>
                            </div>
                            <div class="tech-item">
                                <span class="tech-label">ID Catégorie:</span>
                                <span class="tech-value"><?php echo $nomination->getIdCategorie(); ?></span>
                            </div>
                            <div class="tech-item">
                                <span class="tech-label">ID Compte:</span>
                                <span class="tech-value"><?php echo $nomination->getIdCompte() ?? 'N/A'; ?></span>
                            </div>
                            <div class="tech-item">
                                <span class="tech-label">ID Admin:</span>
                                <span class="tech-value"><?php echo $nomination->getIdAdmin() ?? 'N/A'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        function confirmDelete() {
            return confirm('Êtes-vous sûr de vouloir supprimer cette nomination ? Cette action est irréversible.');
        }

        // Add ripple effect to buttons
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const x = e.clientX - e.target.getBoundingClientRect().left;
                    const y = e.clientY - e.target.getBoundingClientRect().top;

                    const ripple = document.createElement('span');
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');

                    this.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
    </script>
</body>
</html>

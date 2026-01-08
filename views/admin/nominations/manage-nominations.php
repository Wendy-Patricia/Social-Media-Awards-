<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/Services/NominationService.php';
require_once __DIR__ . '/../../../app/Services/CategoryService.php';
require_once __DIR__ . '/../../../views/partials/admin-header.php';

$pdo = Database::getInstance()->getConnection();
$nominationService = new App\Services\NominationService($pdo);
$nominations = $nominationService->getAllNominations();

$stats = [
    'total' => count($nominations),
    'total_votes' => 0 // preencher se houver tabela de votos
];

$categoryService = new App\Services\CategoryService($pdo);
$categories = $categoryService->getAllCategories();
$platforms = ['TikTok', 'Instagram', 'YouTube', 'Facebook', 'X', 'Twitch', 'Spotify'];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Nominations - Admin</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/admin-nominations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/brands.min.css">
</head>

<body>

    <div class="admin-container">
        <div class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1><i class="fas fa-award"></i> Gestion des Nominations</h1>
                    <nav class="breadcrumb">
                        <a href="dashboard.php">Tableau de bord</a> > <span>Nominations</span>
                    </nav>
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

    
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon bg-teal">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Nominations</h3>
                            <div class="stat-number"><?php echo $stats['total']; ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bg-blue">
                            <i class="fas fa-vote-yea"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Votes Total</h3>
                            <div class="stat-number"><?php echo number_format($stats['total_votes']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Nominations Table -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Liste des Nominations</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($nominations)): ?>
                            <div class="empty-state">
                                <i class="fas fa-award"></i>
                                <h4>Aucune nomination trouvée</h4>
                                <p>Commencez par créer une nouvelle nomination</p>
                                <a href="create-nomination.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Créer une nomination
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Candidat</th>
                                            <th>Titre</th>
                                            <th>Catégorie</th>
                                            <th>Plateforme</th>
                                            <th>Date Approbation</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($nominations as $index => $n): ?>
                                            <tr style="--row-index: <?php echo $index; ?>">
                                                <td>
                                                    <div class="user-info">
                                                        <?php
                                                        $avatar = $n['candidat_photo'] ?? $n['url_image'] ?? 'assets/images/default-avatar.png';
                                                        ?>
                                                        <img src="/Social-Media-Awards-/<?php echo htmlspecialchars($avatar); ?>"
                                                            alt="<?php echo htmlspecialchars($n['candidat_nom'] ?? 'Anonyme'); ?>"
                                                            class="user-avatar">
                                                        <div class="user-details">
                                                            <strong><?php echo htmlspecialchars($n['candidat_nom'] ?? 'Anonyme'); ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($n['libelle']); ?></strong>
                                                    <div class="text-muted small">
                                                        <?php echo htmlspecialchars(substr($n['argumentaire'] ?? '', 0, 80)); ?>...
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-teal">
                                                        <?php echo htmlspecialchars($n['categorie_nom'] ?? 'Non catégorisé'); ?>
                                                    </span>
                                                    <div class="text-muted small">
                                                        <?php echo htmlspecialchars($n['edition_nom'] ?? ''); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $platform = strtolower($n['plateforme']);
                                                    $icon = match ($platform) {
                                                        'tiktok' => 'fa-tiktok',
                                                        'instagram' => 'fa-instagram',
                                                        'youtube' => 'fa-youtube',
                                                        'facebook' => 'fa-facebook',
                                                        'x', 'twitter' => 'fa-x-twitter',
                                                        'twitch' => 'fa-twitch',
                                                        default => 'fa-globe'
                                                    };
                                                    ?>
                                                    <span class="platform-badge">
                                                        <i class="fab <?php echo $icon; ?>"></i>
                                                        <?php echo htmlspecialchars($n['plateforme']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo date('d/m/Y', strtotime($n['date_approbation'])); ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="edit-nomination.php?id=<?php echo $n['id_nomination']; ?>"
                                                            class="btn-icon btn-edit" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="view-nomination.php?id=<?php echo $n['id_nomination']; ?>"
                                                            class="btn-icon btn-view" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <form method="POST" action="delete-nomination.php"
                                                            onsubmit="return confirmDelete()">
                                                            <input type="hidden" name="id" value="<?php echo $n['id_nomination']; ?>">
                                                            <button type="submit" class="btn-icon btn-delete" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de confirmação -->
    <div class="confirm-modal" id="confirmModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmation</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                Êtes-vous sûr de vouloir supprimer cette nomination ?
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="modalCancel">Annuler</button>
                <button class="btn btn-danger" id="modalConfirm">Supprimer</button>
            </div>
        </div>
    </div>

    <script>
        // Menu mobile
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Confirmação de exclusão
        function confirmDelete() {
            return confirm('Êtes-vous sûr de vouloir supprimer cette nomination ?');
        }

        // Fechar modal ao clicar fora
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('confirmModal');
            const modalClose = document.getElementById('modalClose');
            const modalCancel = document.getElementById('modalCancel');

            if (modal) {
                [modal, modalClose, modalCancel].forEach(element => {
                    if (element) {
                        element.addEventListener('click', function(e) {
                            if (e.target === modal || e.target === modalClose || e.target === modalCancel) {
                                modal.classList.remove('active');
                            }
                        });
                    }
                });
            }

            document.querySelectorAll('.btn, .btn-icon').forEach(button => {
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
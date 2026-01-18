<?php
require_once __DIR__ . '/../../../app/autoload.php';
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();
require_once __DIR__ . '/../../../config/bootstrap-admin.php';

$editionService->updateAllEditionStatus();
$editions = $editionService->getAllEditions();

require_once __DIR__ . '/../../../views/partials/admin-header.php';
?>

<link rel="stylesheet" href="../../../assets/css/admin-editions.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="admin-main-content admin-editions-page">
    <div class="admin-page-header">
        <div class="page-title">
            <h1><i class="fas fa-calendar-alt"></i> Gestion des Éditions</h1>
            <p><?= count($editions) ?> édition(s)</p>
        </div>
        <a href="ajouter-edition.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle édition
        </a>
    </div>

    <div class="admin-content">
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Opération réussie !</div>
        <?php endif; ?>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Une édition est active entre le début des candidatures et la fin des votes.
        </div>

        <?php if (empty($editions)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times fa-4x"></i>
            <h3>Aucune édition trouvée</h3>
            <p>Créez votre première édition pour commencer.</p>
            <a href="ajouter-edition.php" class="btn btn-primary">Nouvelle édition</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ÉDITION</th>
                        <th>PÉRIODE</th>
                        <th>STATUT</th>
                        <th>CATÉGORIES</th>
                        <th>CANDIDATURES</th>
                        <th>VOTANTS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($editions as $e): ?>
                    <?php
                    $statusText = $e->getEstActive() ? 'Active' : 'Terminée';
                    $statusClass = $e->getEstActive() ? 'active' : 'finished';
                    ?>
                    <tr>
                        <td>
                            <div class="edition-name">
                                <?= htmlspecialchars($e->getNom()) ?>
                                <small><?= $e->getAnnee() ?></small>
                            </div>
                        </td>
                        <td>
                            Du <?= date('d/m/Y', strtotime($e->getDateDebutCandidatures())) ?> 
                            au <?= date('d/m/Y', strtotime($e->getDateFin())) ?>
                        </td>
                        <td>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                        </td>
                        <td><strong><?= $e->getNbCategories() ?></strong></td>
                        <td><strong><?= $e->getNbCandidatures() ?></strong></td>
                        <td><strong><?= $e->getNbVotants() ?></strong></td>
                        <td class="actions-cell">
                            <a href="modifier-edition.php?id=<?= $e->getIdEdition() ?>" class="action-btn edit" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" onclick="confirmDelete(<?= $e->getIdEdition() ?>, '<?= addslashes($e->getNom()) ?>')" class="action-btn delete" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="../../../assets/js/admin-editions.js"></script>

<?php if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $editionController->deleteEdition($id);
    exit;
} ?>
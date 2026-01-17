<?php
require_once __DIR__ . '/../../../app/autoload.php';
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();
require_once __DIR__ . '/../../../config/bootstrap-admin.php';

$editions = $editionController->getAllEditions();

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
            <strong>Système d'activation automatique:</strong> Une édition est active si la date actuelle est comprise
            entre sa date de début et sa date de fin. Une seule édition peut être active à la fois.
        </div>

        <?php if (empty($editions)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar"></i>
            <h3>Aucune édition</h3>
            <a href="ajouter-edition.php" class="btn btn-primary">Créer la première édition</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="enhanced-table">
                <thead>
                    <tr>
                        <th>Édition</th>
                        <th>Période</th>
                        <th>Statut</th>
                        <th>Catégories</th>
                        <th>Candidatures</th>
                        <th>Votants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($editions as $e):
                            $editionModel = new \App\Models\Edition($e);
                            $status = $editionModel->getStatus();
                            $statusClass = $status === 'active' ? 'status-active' : ($status === 'upcoming' ? 'status-upcoming' : 'status-completed');
                            $statusText = $status === 'active' ? 'Active' : ($status === 'upcoming' ? 'À venir' : 'Terminée');
                        ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($e['nom']) ?></strong><br>
                            <small><?= $e['annee'] ?></small>
                        </td>
                        <td>
                            Du <?= date('d/m/Y', strtotime($e['date_debut'])) ?><br>
                            au <?= date('d/m/Y', strtotime($e['date_fin'])) ?>
                        </td>
                        <td>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                        </td>
                        <td><strong><?= $e['nb_categories'] ?></strong></td>
                        <td><strong><?= $e['nb_candidatures'] ?></strong></td>
                        <td><strong><?= $e['nb_votants'] ?></strong></td>
                        <td class="actions-cell">
                            <a href="modifier-edition.php?id=<?= $e['id_edition'] ?>" class="action-btn edit"
                                title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" onclick="confirmDelete(<?= $e['id_edition'] ?>, '<?= addslashes($e['nom']) ?>')"
                                class="action-btn delete" title="Supprimer">
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
    header("Location: gerer-editions.php?success=1");
    exit;
} ?>
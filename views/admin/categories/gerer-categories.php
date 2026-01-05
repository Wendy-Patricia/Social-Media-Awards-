<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();

require_once __DIR__ . '/../../../app/Controllers/AdminController.php';

$controller = new App\Controllers\AdminController();

// Agora usa os métodos públicos
$categories = $controller->getAllCategories();
$editions = $controller->getEditionsList();

require_once __DIR__ . '/../../../views/partials/admin-header.php';
?>

<link rel="stylesheet" href="../../../assets/css/admin-categorie.css">

<div class="admin-page-header">
    <div class="page-title">
        <h1><i class="fas fa-tags"></i> Gestion des Catégories</h1>
    </div>
    <a href="ajouter-categorie.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvelle Catégorie
    </a>
</div>

<div class="admin-content">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Opération réussie !</div>
    <?php endif; ?>

    <?php if (empty($categories)): ?>
        <div class="empty-state">
            <p>Aucune catégorie pour le moment.</p>
            <a href="ajouter-categorie.php" class="btn btn-primary">Créer la première catégorie</a>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Plateforme</th>
                    <th>Édition</th>
                    <th>Candidatures / Nominés</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cat['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($cat['plateforme_cible']) ?></td>
                        <td><?= htmlspecialchars($cat['edition_nom'] ?? 'Non définie') ?></td>
                        <td><?= $cat['nb_candidatures'] ?> / <?= $cat['nb_nominations'] ?></td>
                        <td class="actions">
                            <a href="modifier-categorie.php?id=<?= $cat['id_categorie'] ?>" class="btn-icon" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="gerer-categories.php?delete=<?= $cat['id_categorie'] ?>"
                                onclick="return confirm('Supprimer cette catégorie ? Cette action est irréversible.');"
                                class="btn-icon btn-danger" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php
    // Traitement suppression directe
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        if ($controller->deleteCategory($id)) {
            header("Location: gerer-categories.php?success=1");
            exit;
        } else {
            echo "<div class='alert alert-error'>Erreur lors de la suppression.</div>";
        }
    }
    ?>
</div>

<?php require_once __DIR__ . '/../../../views/partials/admin-footer.php'; ?>
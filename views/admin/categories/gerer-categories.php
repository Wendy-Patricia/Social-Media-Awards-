<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();

require_once __DIR__ . '/../../../app/Controllers/AdminController.php';

$controller = new App\Controllers\AdminController();
$categories = $controller->getAllCategories();
$editions = $controller->getEditionsList();

require_once __DIR__ . '/../../../views/partials/admin-header.php';
?>

<link rel="stylesheet" href="../../../assets/css/admin-categories.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="admin-main-content admin-categories-page">
    <div class="admin-page-header">
        <div class="page-title">
            <h1><i class="fas fa-tags"></i> Gestion des Catégories</h1>
            <p><?= count($categories) ?> catégorie(s) au total</p>
        </div>
        <a href="ajouter-categorie.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus-circle"></i> Nouvelle Catégorie
        </a>
    </div>

    <div class="admin-content">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Opération réussie !
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> Erreur lors de l'opération.
            </div>
        <?php endif; ?>

        <!-- Estatísticas -->
        <?php if (!empty($categories)): ?>
        <div class="stats-summary">
            <div class="stat-item total">
                <span class="stat-number"><?= count($categories) ?></span>
                <span class="stat-label">Total Catégories</span>
            </div>
            <?php
            $now = time();
            $active = array_filter($categories, function($cat) use ($now) {
                $end = strtotime($cat['date_fin_votes'] ?? '');
                $start = strtotime($cat['date_debut_votes'] ?? '');
                return $end && $now <= $end && $now >= $start;
            });
            
            $upcoming = array_filter($categories, function($cat) use ($now) {
                $start = strtotime($cat['date_debut_votes'] ?? '');
                return $start && $now < $start;
            });
            
            $ended = array_filter($categories, function($cat) use ($now) {
                $end = strtotime($cat['date_fin_votes'] ?? '');
                return $end && $now > $end;
            });
            ?>
            <div class="stat-item active">
                <span class="stat-number"><?= count($active) ?></span>
                <span class="stat-label">Actives</span>
            </div>
            <div class="stat-item upcoming">
                <span class="stat-number"><?= count($upcoming) ?></span>
                <span class="stat-label">À venir</span>
            </div>
            <div class="stat-item ended">
                <span class="stat-number"><?= count($ended) ?></span>
                <span class="stat-label">Terminées</span>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-controls">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher une catégorie...">
            </div>
            <div class="filters">
                <select class="filter-select" id="platformFilter">
                    <option value="">Toutes les plateformes</option>
                    <option value="Toutes">Toutes</option>
                    <option value="TikTok">TikTok</option>
                    <option value="Instagram">Instagram</option>
                    <option value="YouTube">YouTube</option>
                    <option value="Twitch">Twitch</option>
                    <option value="Facebook">Facebook</option>
                    <option value="X">X (Twitter)</option>
                    <option value="Autre">Autre</option>
                </select>
                <select class="filter-select" id="editionFilter">
                    <option value="">Toutes les éditions</option>
                    <?php foreach ($editions as $edition): ?>
                        <option value="<?= $edition['id_edition'] ?>"><?= htmlspecialchars($edition['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="filter-select" id="statusFilter">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actives</option>
                    <option value="upcoming">À venir</option>
                    <option value="ended">Terminées</option>
                </select>
            </div>
        </div>

        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h3>Aucune catégorie pour le moment</h3>
                <p>Commencez par créer votre première catégorie.</p>
                <a href="ajouter-categorie.php" class="btn btn-primary" style="margin-top: 15px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-plus"></i> Créer la première catégorie
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="enhanced-table" id="categoriesTable">
                    <thead>
                        <tr>
                            <th width="30%">
                                <i class="fas fa-heading"></i> Catégorie
                            </th>
                            <th width="15%">
                                <i class="fas fa-globe"></i> Plateforme
                            </th>
                            <th width="15%">
                                <i class="fas fa-calendar-alt"></i> Édition
                            </th>
                            <th width="15%">
                                <i class="fas fa-users"></i> Participation
                            </th>
                            <th width="15%">
                                <i class="fas fa-vote-yea"></i> Statut Votes
                            </th>
                            <th width="10%">
                                <i class="fas fa-cog"></i> Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): 
                            $now = time();
                            $start = strtotime($cat['date_debut_votes'] ?? '');
                            $end = strtotime($cat['date_fin_votes'] ?? '');
                            
                            $status = '';
                            if ($end && $now > $end) {
                                $status = 'ended';
                                $status_text = 'Terminé';
                            } elseif ($start && $now < $start) {
                                $status = 'upcoming';
                                $status_text = 'À venir';
                            } elseif ($end && $now <= $end && $now >= $start) {
                                $status = 'active';
                                $status_text = 'En cours';
                            } else {
                                $status = '';
                                $status_text = 'Non défini';
                            }
                            
                            $platform_class = 'platform-' . strtolower(str_replace([' ', '-'], '', $cat['plateforme_cible']));
                            $percentage = $cat['limite_nomines'] > 0 ? 
                                min(100, ($cat['nb_nominations'] / $cat['limite_nomines']) * 100) : 0;
                        ?>
                        <tr data-id="<?= $cat['id_categorie'] ?>"
                            data-platform="<?= htmlspecialchars($cat['plateforme_cible']) ?>"
                            data-edition="<?= $cat['id_edition'] ?>"
                            data-status="<?= $status ?>"
                            data-candidatures="<?= $cat['nb_candidatures'] ?>"
                            data-nominations="<?= $cat['nb_nominations'] ?>"
                            data-limite="<?= $cat['limite_nomines'] ?>"
                            data-debut="<?= $cat['date_debut_votes'] ?>"
                            data-fin="<?= $cat['date_fin_votes'] ?>">
                            <td>
                                <div class="category-name">
                                    <?php if ($cat['image']): ?>
                                        <img src="../../../public/<?= htmlspecialchars($cat['image']) ?>" 
                                             alt="<?= htmlspecialchars($cat['nom']) ?>" 
                                             class="category-image">
                                    <?php else: ?>
                                        <div class="category-image" style="background: #f0f2f5; display: flex; align-items: center; justify-content: center; color: #7f8c8d;">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="category-info">
                                        <h4><?= htmlspecialchars($cat['nom']) ?></h4>
                                        <?php if ($cat['description']): ?>
                                            <div class="description" title="<?= htmlspecialchars($cat['description']) ?>">
                                                <?= htmlspecialchars(substr($cat['description'], 0, 60)) ?>...
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="platform-badge <?= $platform_class ?>">
                                    <?= htmlspecialchars($cat['plateforme_cible']) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($cat['edition_nom'] ?? 'Non définie') ?></strong>
                                <?php if ($cat['edition_annee'] ?? ''): ?>
                                    <br><small style="color: #7f8c8d;"><?= $cat['edition_annee'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="numbers-cell">
                                <div style="font-weight: 600; color: var(--dark-color);">
                                    <?= $cat['nb_candidatures'] ?> candidatures
                                </div>
                                <div style="font-size: 13px; color: var(--success-color);">
                                    <?= $cat['nb_nominations'] ?> nominés
                                </div>
                                <?php if ($cat['limite_nomines'] > 0): ?>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $percentage ?>%;"></div>
                                </div>
                                <small style="color: var(--secondary-color);">
                                    <?= $cat['nb_nominations'] ?>/<?= $cat['limite_nomines'] ?> places
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cat['date_debut_votes'] && $cat['date_fin_votes']): ?>
                                    <div style="font-size: 13px;">
                                        <div>Début: <?= date('d/m/Y', strtotime($cat['date_debut_votes'])) ?></div>
                                        <div>Fin: <?= date('d/m/Y', strtotime($cat['date_fin_votes'])) ?></div>
                                    </div>
                                    <span class="vote-status status-<?= $status ?>">
                                        <?= $status_text ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--secondary-color); font-style: italic;">
                                        Dates non définies
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <a href="modifier-categorie.php?id=<?= $cat['id_categorie'] ?>" 
                                   class="action-btn edit" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" 
                                   onclick="return confirmDelete(<?= $cat['id_categorie'] ?>, '<?= addslashes($cat['nom']) ?>')"
                                   class="action-btn delete" 
                                   title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="#" 
                                   class="action-btn view" 
                                   title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="pagination">
                <a href="#" class="page-link disabled"><i class="fas fa-chevron-left"></i></a>
                <a href="#" class="page-link active">1</a>
                <a href="#" class="page-link">2</a>
                <a href="#" class="page-link">3</a>
                <a href="#" class="page-link"><i class="fas fa-chevron-right"></i></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../../../assets/js/admin-categories.js"></script>

<?php
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($controller->deleteCategory($id)) {
        echo '<script>window.location.href = "gerer-categories.php?success=1";</script>';
        exit;
    } else {
        echo '<script>window.location.href = "gerer-categories.php?error=1";</script>';
        exit;
    }
}
?>

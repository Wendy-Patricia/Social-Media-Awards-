<?php

// Buscar categorias do banco de dados
$categories = [];
$editions = [];


require_once __DIR__ . '/../../partials/admin-header.php'; 
?>

<link rel="stylesheet" href="../../../assets/css/admin-categorie.css">

<div class="admin-page-header">
    <div class="page-title">
        <h1><i class="fas fa-tags"></i> Gestion des Catégories</h1>
        <nav class="breadcrumb">
            <a href="/Social-Media-Awards-/views/admin/dashboard.php">Tableau de bord</a> &gt;
            <span>Catégories</span>
        </nav>
    </div>
    <a href="/Social-Media-Awards-/views/admin/categories/add-categorie.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nouvelle Catégorie
    </a>
</div>

<div class="admin-content">
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) || isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Catégorie supprimée avec succès.
        </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon" style="background: #4FBDAB;">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-info">
                <h3>Total Catégories</h3>
                <div class="stat-number"><?php echo count($categories); ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #FFD166;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-info">
                <h3>Édition <?php echo date('Y'); ?></h3>
                <div class="stat-number">
                    <?php
                    $current_edition_count = 0;
                    foreach ($categories as $cat) {
                        if (isset($cat['annee']) && $cat['annee'] == date('Y')) {
                            $current_edition_count++;
                        }
                    }
                    echo $current_edition_count;
                    ?>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #FF5A79;">
                <i class="fas fa-file-signature"></i>
            </div>
            <div class="stat-info">
                <h3>Candidatures Total</h3>
                <div class="stat-number">
                    <?php
                    $total_candidatures = 0;
                    foreach ($categories as $cat) {
                        $total_candidatures += $cat['nb_candidatures'] ?? 0;
                    }
                    echo $total_candidatures;
                    ?>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #6c757d;">
                <i class="fas fa-award"></i>
            </div>
            <div class="stat-info">
                <h3>Nominations Total</h3>
                <div class="stat-number">
                    <?php
                    $total_nominations = 0;
                    foreach ($categories as $cat) {
                        $total_nominations += $cat['nb_nominations'] ?? 0;
                    }
                    echo $total_nominations;
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre de filtres -->
    <div class="filters-bar">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Rechercher une catégorie...">
            <button class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-controls">
            <select id="editionFilter" class="form-control">
                <option value="">Toutes les éditions</option>
                <?php foreach ($editions as $edition): ?>
                    <option value="<?php echo $edition['id_edition']; ?>">
                        <?php echo htmlspecialchars($edition['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="platformFilter" class="form-control">
                <option value="">Toutes les plateformes</option>
                <option value="TikTok">TikTok</option>
                <option value="Instagram">Instagram</option>
                <option value="YouTube">YouTube</option>
                <option value="X">X (Twitter)</option>
                <option value="Facebook">Facebook</option>
                <option value="Toutes">Toutes</option>
            </select>
        </div>
    </div>

    <!-- Tableau des catégories -->
    <div class="category-table">
        <?php if (empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-tags"></i>
                <h3>Aucune catégorie trouvée</h3>
                <p>Il n'y a pas encore de catégories créées.</p>
                <a href="categorie_create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Créer une catégorie
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="categoriesTable">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Édition</th>
                            <th>Plateforme</th>
                            <th>Candidatures</th>
                            <th>Nominations</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesBody">
                        <?php foreach ($categories as $categorie):
                            // Déterminer la classe CSS para a plataforma
                            $platform_class = 'platform-toutes';
                            switch ($categorie['plateforme_cible'] ?? 'Toutes') {
                                case 'TikTok':
                                    $platform_class = 'platform-tiktok';
                                    break;
                                case 'Instagram':
                                    $platform_class = 'platform-instagram';
                                    break;
                                case 'YouTube':
                                    $platform_class = 'platform-youtube';
                                    break;
                                case 'X':
                                    $platform_class = 'platform-x';
                                    break;
                                case 'Facebook':
                                    $platform_class = 'platform-facebook';
                                    break;
                                default:
                                    $platform_class = 'platform-toutes';
                            }

                            // Déterminer le statut
                            $current_date = date('Y-m-d H:i:s');
                            $status = 'inactive';
                            $status_text = 'Inactive';
                            $date_fin_votes = $categorie['date_fin_votes'] ?? null;

                            if (!empty($date_fin_votes)) {
                                if ($current_date <= $date_fin_votes) {
                                    $status = 'active';
                                    $status_text = 'Active';
                                }
                            }
                        ?>
                            <tr data-edition="<?php echo $categorie['id_edition']; ?>"
                                data-platform="<?php echo htmlspecialchars($categorie['plateforme_cible'] ?? 'Toutes'); ?>"
                                data-name="<?php echo htmlspecialchars(strtolower($categorie['nom'])); ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($categorie['nom']); ?></strong>
                                    <div class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
                                        <?php echo htmlspecialchars(substr($categorie['description'] ?? '', 0, 100)); ?>...
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?php echo htmlspecialchars($categorie['edition_nom'] ?? 'Non attribué'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="platform-tag <?php echo $platform_class; ?>">
                                        <?php if (($categorie['plateforme_cible'] ?? '') == 'TikTok'): ?>
                                            <i class="fab fa-tiktok"></i>
                                        <?php elseif (($categorie['plateforme_cible'] ?? '') == 'Instagram'): ?>
                                            <i class="fab fa-instagram"></i>
                                        <?php elseif (($categorie['plateforme_cible'] ?? '') == 'YouTube'): ?>
                                            <i class="fab fa-youtube"></i>
                                        <?php elseif (($categorie['plateforme_cible'] ?? '') == 'X'): ?>
                                            <i class="fab fa-twitter"></i>
                                        <?php elseif (($categorie['plateforme_cible'] ?? '') == 'Facebook'): ?>
                                            <i class="fab fa-facebook"></i>
                                        <?php else: ?>
                                            <i class="fas fa-globe"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($categorie['plateforme_cible'] ?? 'Toutes'); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo $categorie['nb_candidatures'] ?? 0; ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo $categorie['nb_nominations'] ?? 0; ?></strong>
                                </td>
                                <td>
                                    <span class="status-<?php echo $status; ?>">
                                        <i class="fas fa-circle"></i> <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="categorie_edit.php?id=<?php echo $categorie['id_categorie']; ?>"
                                            class="btn-icon btn-edit" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="categorie_view.php?id=<?php echo $categorie['id_categorie']; ?>"
                                            class="btn-icon btn-view" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button"
                                            class="btn-icon btn-delete"
                                            title="Supprimer"
                                            data-id="<?php echo $categorie['id_categorie']; ?>"
                                            data-name="<?php echo htmlspecialchars($categorie['nom']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Modal de confirmation de suppression -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmer la suppression</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer la catégorie <strong id="deleteCategoryName"></strong> ?</p>
            <p class="text-danger"><i class="fas fa-exclamation-circle"></i> Cette action est irréversible. Toutes les données associées seront perdues.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelDeleteBtn">
                <i class="fas fa-times"></i> Annuler
            </button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                <i class="fas fa-trash"></i> Supprimer
            </a>
        </div>
    </div>
</div>
 <script src="../../../assets/js/admin-categorie.js"></script>
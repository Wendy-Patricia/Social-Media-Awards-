<?php
// manage-editions.php
require_once __DIR__ . '/../../partials/admin-header.php';

// Simulação de dados (substituir por consulta ao banco de dados)
$editions = [
    [
        'id_edition' => 1,
        'nom' => 'Social Media Awards 2024',
        'annee' => 2024,
        'description' => 'Primeira edição dos prêmios de mídia social',
        'date_debut' => '2024-01-01',
        'date_fin' => '2024-12-31',
        'date_fin_votes' => '2024-11-30',
        'status' => 'active',
        'nombre_categories' => 15,
        'nombre_candidatures' => 325,
        'nombre_votants' => 2500
    ],
    [
        'id_edition' => 2,
        'nom' => 'Social Media Awards 2025',
        'annee' => 2025,
        'description' => 'Segunda edição - Inovações em conteúdo digital',
        'date_debut' => '2025-01-01',
        'date_fin' => '2025-12-31',
        'date_fin_votes' => '2025-11-30',
        'status' => 'upcoming',
        'nombre_categories' => 0,
        'nombre_candidatures' => 0,
        'nombre_votants' => 0
    ],
    [
        'id_edition' => 3,
        'nom' => 'Social Media Awards 2023',
        'annee' => 2023,
        'description' => 'Edição inaugural dos prêmios',
        'date_debut' => '2023-01-01',
        'date_fin' => '2023-12-31',
        'date_fin_votes' => '2023-11-30',
        'status' => 'completed',
        'nombre_categories' => 12,
        'nombre_candidatures' => 280,
        'nombre_votants' => 1800
    ]
];

$edition_stats = [
    'total' => count($editions),
    'active' => count(array_filter($editions, fn($e) => $e['status'] === 'active')),
    'upcoming' => count(array_filter($editions, fn($e) => $e['status'] === 'upcoming')),
    'completed' => count(array_filter($editions, fn($e) => $e['status'] === 'completed')),
    'total_categories' => array_sum(array_column($editions, 'nombre_categories')),
    'total_candidatures' => array_sum(array_column($editions, 'nombre_candidatures')),
    'total_votants' => array_sum(array_column($editions, 'nombre_votants'))
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Éditions - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/admin-editions.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <!-- Header da página -->
    <div class="admin-page-header">
        <div class="page-title">
            <h1><i class="fas fa-calendar-alt"></i> Gestion des Éditions</h1>
            <nav class="breadcrumb">
                <a href="dashboard.php">Tableau de bord</a> &gt;
                <span>Éditions</span>
            </nav>
        </div>
        <a href="add-edition.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Édition
        </a>
    </div>

    <div class="admin-content">
        <!-- Cartões de estatísticas -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4FBDAB, #45a999);">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Éditions</h3>
                    <div class="stat-number"><?php echo $edition_stats['total']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #218838);">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>Éditions Actives</h3>
                    <div class="stat-number"><?php echo $edition_stats['active']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>Éditions à Venir</h3>
                    <div class="stat-number"><?php echo $edition_stats['upcoming']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #6c757d, #5a6268);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>Éditions Terminées</h3>
                    <div class="stat-number"><?php echo $edition_stats['completed']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #FFD166, #E6BC5E);">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-info">
                    <h3>Catégories Total</h3>
                    <div class="stat-number"><?php echo $edition_stats['total_categories']; ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #FF5A79, #E54A68);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Votants Total</h3>
                    <div class="stat-number"><?php echo number_format($edition_stats['total_votants']); ?></div>
                </div>
            </div>
        </div>

        <!-- Filtros e busca -->
        <div class="filters-bar">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Rechercher une édition...">
                <button class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <div class="filter-controls">
                <select id="statusFilter" class="form-control">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actives</option>
                    <option value="upcoming">À venir</option>
                    <option value="completed">Terminées</option>
                </select>

                <select id="yearFilter" class="form-control">
                    <option value="">Toutes les années</option>
                    <option value="2023">2023</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                </select>
            </div>
        </div>

        <!-- Tabela de edições -->
        <div class="editions-table">
            <?php if (empty($editions)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Aucune édition trouvée</h3>
                    <p>Il n'y a pas encore d'éditions créées.</p>
                    <a href="add-edition.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer une édition
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="editionsTable">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Année</th>
                                <th>Période</th>
                                <th>Statut</th>
                                <th>Catégories</th>
                                <th>Candidatures</th>
                                <th>Votants</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($editions as $edition): ?>
                                <tr
                                    data-status="<?php echo htmlspecialchars($edition['status']); ?>"
                                    data-year="<?php echo htmlspecialchars($edition['annee']); ?>"
                                    data-name="<?php echo htmlspecialchars(strtolower($edition['nom'])); ?>">
                                    <!-- NOM -->
                                    <td>
                                        <strong><?php echo htmlspecialchars($edition['nom']); ?></strong>
                                        <div class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
                                            <?php echo htmlspecialchars(substr($edition['description'], 0, 50)); ?>...
                                        </div>
                                    </td>

                                    <!-- ANNÉE -->
                                    <td>
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($edition['annee']); ?></span>
                                    </td>

                                    <!-- PERÍODO -->
                                    <td>
                                        <small><i class="fas fa-play"></i> <?php echo date('d/m/Y', strtotime($edition['date_debut'])); ?></small><br>
                                        <small><i class="fas fa-flag-checkered"></i> <?php echo date('d/m/Y', strtotime($edition['date_fin'])); ?></small>
                                    </td>

                                    <!-- STATUT -->
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        switch ($edition['status']) {
                                            case 'active':
                                                $status_class = 'status-active';
                                                $status_text = 'Active';
                                                break;
                                            case 'upcoming':
                                                $status_class = 'status-upcoming';
                                                $status_text = 'À venir';
                                                break;
                                            case 'completed':
                                                $status_class = 'status-completed';
                                                $status_text = 'Terminée';
                                                break;
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <i class="fas fa-circle"></i>
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>

                                    <!-- CATÉGORIES -->
                                    <td><strong><?php echo $edition['nombre_categories']; ?></strong></td>

                                    <!-- CANDIDATURES -->
                                    <td><strong><?php echo $edition['nombre_candidatures']; ?></strong></td>

                                    <!-- VOTANTS -->
                                    <td><strong><?php echo number_format($edition['nombre_votants']); ?></strong></td>

                                    <!-- ACTIONS -->
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit-edition.php?id=<?php echo $edition['id_edition']; ?>" class="btn-icon btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="view-edition.php?id=<?php echo $edition['id_edition']; ?>" class="btn-icon btn-view">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn-icon btn-toggle-status"
                                                data-id="<?php echo $edition['id_edition']; ?>"
                                                data-status="<?php echo $edition['status']; ?>">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                            <button class="btn-icon btn-delete"
                                                data-id="<?php echo $edition['id_edition']; ?>"
                                                data-name="<?php echo htmlspecialchars($edition['nom']); ?>">
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
                <p>Êtes-vous sûr de vouloir supprimer l'édition <strong id="deleteEditionName"></strong> ?</p>
                <p class="text-danger"><i class="fas fa-exclamation-circle"></i> Cette action est irréversible. Toutes les catégories et données associées seront perdues.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelDeleteBtn">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de changement de statut -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exchange-alt"></i> Changer le statut</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Changer le statut de l'édition <strong id="statusEditionName"></strong></p>
                <div class="status-options">
                    <label class="status-option">
                        <input type="radio" name="newStatus" value="active" id="statusActive">
                        <span class="status-badge status-active">
                            <i class="fas fa-circle"></i> Active
                        </span>
                    </label>
                    <label class="status-option">
                        <input type="radio" name="newStatus" value="upcoming" id="statusUpcoming">
                        <span class="status-badge status-upcoming">
                            <i class="fas fa-circle"></i> À venir
                        </span>
                    </label>
                    <label class="status-option">
                        <input type="radio" name="newStatus" value="completed" id="statusCompleted">
                        <span class="status-badge status-completed">
                            <i class="fas fa-circle"></i> Terminée
                        </span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelStatusBtn">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button id="confirmStatusBtn" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </div>
    </div>

    <script src="/Social-Media-Awards-/assets/js/admin-editions.js"></script>
</body>

</html>
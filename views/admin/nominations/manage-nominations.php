<?php
// manage-nominations.php
require_once __DIR__ . '/../../partials/admin-header.php';

// Simulação de dados (substituir por consulta ao banco de dados)
$nominations = [
    [
        'id_nomination' => 1,
        'titre' => 'Meilleur Créateur Tech TikTok 2024',
        'candidat_nom' => 'TechExplained',
        'candidat_username' => '@techexplained',
        'plateforme' => 'TikTok',
        'categorie_nom' => 'Créateur Tech de l\'Année',
        'edition_nom' => 'Social Media Awards 2024',
        'date_soumission' => '2024-03-15 14:30:00',
        'date_approbation' => '2024-03-20 10:15:00',
        'statut' => 'approved',
        'votes' => 1250,
        'argumentation' => 'Contenu éducatif de haute qualité expliquant les technologies complexes de manière accessible.',
        'image_url' => 'https://via.placeholder.com/150'
    ],
    [
        'id_nomination' => 2,
        'titre' => 'Influenceuse Beauté Révélation',
        'candidat_nom' => 'BeautyByMarie',
        'candidat_username' => '@beautybymarie',
        'plateforme' => 'Instagram',
        'categorie_nom' => 'Révélation Beauté',
        'edition_nom' => 'Social Media Awards 2024',
        'date_soumission' => '2024-03-10 09:45:00',
        'date_approbation' => null,
        'statut' => 'pending',
        'votes' => 0,
        'argumentation' => 'Approche unique des tutoriels maquillage avec des produits accessibles.',
        'image_url' => 'https://via.placeholder.com/150'
    ],
    [
        'id_nomination' => 3,
        'titre' => 'Meilleur Podcast Gaming',
        'candidat_nom' => 'GameTalk',
        'candidat_username' => '@gametalk',
        'plateforme' => 'YouTube',
        'categorie_nom' => 'Podcast Gaming',
        'edition_nom' => 'Social Media Awards 2024',
        'date_soumission' => '2024-03-05 16:20:00',
        'date_approbation' => '2024-03-08 11:30:00',
        'statut' => 'approved',
        'votes' => 890,
        'argumentation' => 'Analyses approfondies de l\'industrie du jeu vidéo avec des invités prestigieux.',
        'image_url' => 'https://via.placeholder.com/150'
    ],
    [
        'id_nomination' => 4,
        'titre' => 'Comédien Web Série Humoristique',
        'candidat_nom' => 'LaughFactory',
        'candidat_username' => '@laughfactory',
        'plateforme' => 'Facebook',
        'categorie_nom' => 'Humour Web',
        'edition_nom' => 'Social Media Awards 2024',
        'date_soumission' => '2024-03-12 13:15:00',
        'date_approbation' => null,
        'statut' => 'rejected',
        'votes' => 0,
        'argumentation' => 'Contenu ne respectant pas les critères d\'éligibilité de la catégorie.',
        'image_url' => 'https://via.placeholder.com/150'
    ]
];

// Estatísticas
$stats = [
    'total' => count($nominations),
    'approved' => count(array_filter($nominations, fn($n) => $n['statut'] === 'approved')),
    'pending' => count(array_filter($nominations, fn($n) => $n['statut'] === 'pending')),
    'rejected' => count(array_filter($nominations, fn($n) => $n['statut'] === 'rejected')),
    'total_votes' => array_sum(array_column($nominations, 'votes'))
];

// Categorias para filtro
$categories = [
    ['id' => 1, 'nom' => 'Créateur Tech de l\'Année'],
    ['id' => 2, 'nom' => 'Révélation Beauté'],
    ['id' => 3, 'nom' => 'Podcast Gaming'],
    ['id' => 4, 'nom' => 'Humour Web']
];

// Plataformas para filtro
$platforms = ['TikTok', 'Instagram', 'YouTube', 'Facebook', 'X', 'Twitch'];
?>

<link rel="stylesheet" href="/Social-Media-Awards-/assets/css/admin-nominations.css">


<div class="admin-page-header">
    <div class="page-title">
        <h1><i class="fas fa-award"></i> Gestion des Nominations</h1>
        <nav class="breadcrumb">
            <a href="dashboard.php">Tableau de bord</a> &gt;
            <span>Nominations</span>
        </nav>
    </div>
    <div class="header-actions">
        <a href="approve-nominations.php" class="btn btn-secondary">
            <i class="fas fa-check-double"></i> Approuver en Masse
        </a>
        <a href="create-nomination.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Nomination
        </a>
    </div>
</div>

<div class="admin-content">
    <!-- Cartões de estatísticas -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4FBDAB, #45a999);">
                <i class="fas fa-award"></i>
            </div>
            <div class="stat-info">
                <h3>Total Nominations</h3>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #218838);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3>Approuvées</h3>
                <div class="stat-number"><?php echo $stats['approved']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #FFD166, #E6BC5E);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3>En Attente</h3>
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #FF5A79, #E54A68);">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h3>Rejetées</h3>
                <div class="stat-number"><?php echo $stats['rejected']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                <i class="fas fa-vote-yea"></i>
            </div>
            <div class="stat-info">
                <h3>Votes Total</h3>
                <div class="stat-number"><?php echo number_format($stats['total_votes']); ?></div>
            </div>
        </div>
    </div>

    <!-- Filtros e busca -->
    <div class="filters-bar">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Rechercher une nomination, candidat...">
            <button class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <div class="filter-controls">
            <select id="statusFilter" class="form-control">
                <option value="">Tous les statuts</option>
                <option value="approved">Approuvées</option>
                <option value="pending">En attente</option>
                <option value="rejected">Rejetées</option>
            </select>

            <select id="categoryFilter" class="form-control">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="platformFilter" class="form-control">
                <option value="">Toutes les plateformes</option>
                <?php foreach ($platforms as $platform): ?>
                    <option value="<?php echo htmlspecialchars($platform); ?>">
                        <?php echo htmlspecialchars($platform); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Tabela de nomeações -->
    <div class="nominations-table">
        <?php if (empty($nominations)): ?>
            <div class="empty-state">
                <i class="fas fa-award"></i>
                <h3>Aucune nomination trouvée</h3>
                <p>Il n'y a pas encore de nominations créées.</p>
                <a href="create-nomination.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Créer une nomination
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table id="nominationsTable">
                    <thead>
                        <tr>
                            <th>Candidat</th>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Plateforme</th>
                            <th>Statut</th>
                            <th>Votes</th>
                            <th>Date Soumission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nominations as $nomination):
                            // Classes de status
                            $status_class = '';
                            $status_icon = '';
                            $status_text = '';

                            switch ($nomination['statut']) {
                                case 'approved':
                                    $status_class = 'status-approved';
                                    $status_icon = 'check-circle';
                                    $status_text = 'Approuvée';
                                    break;
                                case 'pending':
                                    $status_class = 'status-pending';
                                    $status_icon = 'clock';
                                    $status_text = 'En attente';
                                    break;
                                case 'rejected':
                                    $status_class = 'status-rejected';
                                    $status_icon = 'times-circle';
                                    $status_text = 'Rejetée';
                                    break;
                            }

                            // Classe da plataforma
                            $platform_class = 'platform-' . strtolower($nomination['plateforme']);
                        ?>
                            <tr data-status="<?php echo $nomination['statut']; ?>"
                                data-category="<?php echo htmlspecialchars($nomination['categorie_nom']); ?>"
                                data-platform="<?php echo htmlspecialchars($nomination['plateforme']); ?>"
                                data-candidate="<?php echo htmlspecialchars(strtolower($nomination['candidat_nom'])); ?>">

                                <td>
                                    <div class="candidate-info">
                                        <div class="candidate-avatar">
                                            <img src="<?php echo htmlspecialchars($nomination['image_url']); ?>"
                                                alt="<?php echo htmlspecialchars($nomination['candidat_nom']); ?>">
                                        </div>
                                        <div class="candidate-details">
                                            <strong><?php echo htmlspecialchars($nomination['candidat_nom']); ?></strong>
                                            <div class="text-muted">
                                                <?php echo htmlspecialchars($nomination['candidat_username']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <strong><?php echo htmlspecialchars($nomination['titre']); ?></strong>
                                    <div class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
                                        <?php echo htmlspecialchars(substr($nomination['argumentation'] ?? '', 0, 80)); ?>...
                                    </div>
                                </td>

                                <td>
                                    <span class="badge badge-category">
                                        <?php echo htmlspecialchars($nomination['categorie_nom']); ?>
                                    </span>
                                    <div class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
                                        <?php echo htmlspecialchars($nomination['edition_nom']); ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="platform-tag <?php echo $platform_class; ?>">
                                        <?php if ($nomination['plateforme'] == 'TikTok'): ?>
                                            <i class="fab fa-tiktok"></i>
                                        <?php elseif ($nomination['plateforme'] == 'Instagram'): ?>
                                            <i class="fab fa-instagram"></i>
                                        <?php elseif ($nomination['plateforme'] == 'YouTube'): ?>
                                            <i class="fab fa-youtube"></i>
                                        <?php elseif ($nomination['plateforme'] == 'Facebook'): ?>
                                            <i class="fab fa-facebook"></i>
                                        <?php elseif ($nomination['plateforme'] == 'X'): ?>
                                            <i class="fab fa-twitter"></i>
                                        <?php else: ?>
                                            <i class="fas fa-globe"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($nomination['plateforme']); ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <i class="fas fa-<?php echo $status_icon; ?>"></i>
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="vote-count">
                                        <strong><?php echo number_format($nomination['votes']); ?></strong>
                                        <div class="text-muted" style="font-size: 0.85rem;">votes</div>
                                    </div>
                                </td>

                                <td>
                                    <div class="date-info">
                                        <div><i class="fas fa-paper-plane"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($nomination['date_soumission'])); ?>
                                        </div>
                                        <?php if ($nomination['date_approbation']): ?>
                                            <div><i class="fas fa-check"></i>
                                                <?php echo date('d/m/Y', strtotime($nomination['date_approbation'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <?php if ($nomination['statut'] == 'pending'): ?>
                                            <button type="button"
                                                class="btn-icon btn-approve"
                                                title="Approuver"
                                                data-id="<?php echo $nomination['id_nomination']; ?>"
                                                data-title="<?php echo htmlspecialchars($nomination['titre']); ?>">
                                                <i class="fas fa-check"></i>
                                            </button>

                                            <button type="button"
                                                class="btn-icon btn-reject"
                                                title="Rejeter"
                                                data-id="<?php echo $nomination['id_nomination']; ?>"
                                                data-title="<?php echo htmlspecialchars($nomination['titre']); ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>

                                        <a href="edit-nomination.php?id=<?php echo $nomination['id_nomination']; ?>"
                                            class="btn-icon btn-edit" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <a href="view-nomination.php?id=<?php echo $nomination['id_nomination']; ?>"
                                            class="btn-icon btn-view" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <button type="button"
                                            class="btn-icon btn-delete"
                                            title="Supprimer"
                                            data-id="<?php echo $nomination['id_nomination']; ?>"
                                            data-title="<?php echo htmlspecialchars($nomination['titre']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="pagination">
                <span class="page-info">Affichage de 1 à <?php echo count($nominations); ?> sur 50 résultats</span>
                <div class="page-controls">
                    <button class="page-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- Modal d'approbation -->
<div id="approveModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-check-circle"></i> Approuver la Nomination</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Approuver la nomination <strong id="approveNominationTitle"></strong> ?</p>
            <div class="form-group">
                <label for="approvalNotes">Notes (optionnel)</label>
                <textarea id="approvalNotes" rows="3" placeholder="Ajoutez des notes internes sur cette approbation..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelApproveBtn">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button id="confirmApproveBtn" class="btn btn-success">
                <i class="fas fa-check"></i> Approuver
            </button>
        </div>
    </div>
</div>

<!-- Modal de rejet -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-times-circle"></i> Rejeter la Nomination</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Rejeter la nomination <strong id="rejectNominationTitle"></strong> ?</p>
            <div class="form-group">
                <label for="rejectReason">Motif du rejet *</label>
                <select id="rejectReason" required>
                    <option value="">Sélectionner un motif</option>
                    <option value="criteria">Ne respecte pas les critères</option>
                    <option value="quality">Qualité insuffisante</option>
                    <option value="duplicate">Doublon</option>
                    <option value="inappropriate">Contenu inapproprié</option>
                    <option value="other">Autre</option>
                </select>
            </div>
            <div class="form-group">
                <label for="rejectNotes">Détails (optionnel)</label>
                <textarea id="rejectNotes" rows="3" placeholder="Détails supplémentaires sur le rejet..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelRejectBtn">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button id="confirmRejectBtn" class="btn btn-danger">
                <i class="fas fa-times"></i> Rejeter
            </button>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmer la suppression</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer la nomination <strong id="deleteNominationTitle"></strong> ?</p>
            <p class="text-danger"><i class="fas fa-exclamation-circle"></i> Cette action est irréversible.</p>
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

<script src="/Social-Media-Awards-/assets/js/admin-nominations.js"></script>
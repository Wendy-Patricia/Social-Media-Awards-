<?php
// manage-applications.php
require_once __DIR__ . '/../../partials/admin-header.php';

// Simulação de dados (substituir por consulta ao banco de dados)
$applications = [
    [
        'id_candidature' => 1,
        'titre' => 'Créateur Tech Innovant 2024',
        'candidat_nom' => 'TechInnovator',
        'candidat_email' => 'contact@techinnovator.com',
        'candidat_telephone' => '+33 6 12 34 56 78',
        'plateforme' => 'TikTok',
        'categorie_nom' => 'Créateur Tech de l\'Année',
        'edition_nom' => 'Social Media Awards 2024',
        'date_soumission' => '2024-03-15 14:30:00',
        'date_modification' => '2024-03-16 09:15:00',
        'statut' => 'pending',
        'type_candidature' => 'auto',
        'source' => 'Formulaire en ligne',
        'niveau_urgence' => 'normal',
        'score_qualite' => 85,
        'notes_internes' => 'Candidat très prometteur, contenu de haute qualité',
        'pieces_jointes' => 3
    ],
    [
        'id_candidature' => 2,
        'titre' => 'Influenceuse Beauté Débutante',
        'candidat_nom' => 'BeautyNewcomer',
        'candidat_email' => 'hello@beautynewcomer.com',
        'candidat_telephone' => '+33 6 23 45 67 89',
        'plateforme' => 'Instagram',
        'categorie_nom' => 'Révélation Beauté',
        'edition_nom' => 'Social Media Awards 2024',
        'date_soumission' => '2024-03-14 11:20:00',
        'date_modification' => '2024-03-15 16:45:00',
        'statut' => 'review',
        'type_candidature' => 'auto',
        'source' => 'Formulaire en ligne',
        'niveau_urgence' => 'low',
        'score_qualite' => 65,
        'notes_internes' => 'Contenu correct mais manque d\'originalité',
        'pieces_jointes' => 2
    ],
    [
        'id_candidature' => 3,
        'titre' => 'Podcast Gaming Éducatif',
        'candidat_nom' => 'GameEduPod',
        'candidat_email' => 'info@gameedupod.com',
        'candidat_telephone' => '+33 6 34 56 78 90',
        'plateforme' => 'YouTube',
        'categorie_nom' => 'Podcast Gaming',
        'edition_nom' => 'Social Media Awards 2024',
        'date_soumission' => '2024-03-12 09:45:00',
        'date_modification' => '2024-03-14 14:30:00',
        'statut' => 'approved',
        'type_candidature' => 'manual',
        'source' => 'Ajout manuel',
        'niveau_urgence' => 'high',
        'score_qualite' => 92,
        'notes_internes' => 'Excellente production, valeur éducative importante',
        'pieces_jointes' => 5
    ],
    [
        'id_candidature' => 4,
        'titre' => 'Comédien Web Série Humoristique',
        'candidat_nom' => 'WebComedian',
        'candidat_email' => 'contact@webcomedian.com',
        'candidat_telephone' => '+33 6 45 67 89 01',
        'plateforme' => 'Facebook',
        'categorie_nom' => 'Humour Web',
        'edition_nom' => 'Social Media Awards 2024',
        'date_soumission' => '2024-03-10 16:15:00',
        'date_modification' => '2024-03-11 10:30:00',
        'statut' => 'rejected',
        'type_candidature' => 'auto',
        'source' => 'Formulaire en ligne',
        'niveau_urgence' => 'normal',
        'score_qualite' => 45,
        'notes_internes' => 'Contenu inapproprié, ne respecte pas les critères',
        'pieces_jointes' => 1
    ],
    [
        'id_candidature' => 5,
        'titre' => 'Expert Finance TikTok',
        'candidat_nom' => 'FinanceExpert',
        'candidat_email' => 'expert@financeexpert.com',
        'candidat_telephone' => '+33 6 56 78 90 12',
        'plateforme' => 'TikTok',
        'categorie_nom' => 'Éducation Financière',
        'edition_nom' => 'Social Media Awards 2024',
        'date_soumission' => '2024-03-18 13:20:00',
        'date_modification' => null,
        'statut' => 'pending',
        'type_candidature' => 'auto',
        'source' => 'Formulaire en ligne',
        'niveau_urgence' => 'normal',
        'score_qualite' => 78,
        'notes_internes' => '',
        'pieces_jointes' => 4
    ]
];

// Estatísticas
$stats = [
    'total' => count($applications),
    'pending' => count(array_filter($applications, fn($a) => $a['statut'] === 'pending')),
    'review' => count(array_filter($applications, fn($a) => $a['statut'] === 'review')),
    'approved' => count(array_filter($applications, fn($a) => $a['statut'] === 'approved')),
    'rejected' => count(array_filter($applications, fn($a) => $a['statut'] === 'rejected')),
    'auto' => count(array_filter($applications, fn($a) => $a['type_candidature'] === 'auto')),
    'manual' => count(array_filter($applications, fn($a) => $a['type_candidature'] === 'manual')),
    'total_pieces' => array_sum(array_column($applications, 'pieces_jointes'))
];

// Categorias para filtro
$categories = [
    ['id' => 1, 'nom' => 'Créateur Tech de l\'Année'],
    ['id' => 2, 'nom' => 'Révélation Beauté'],
    ['id' => 3, 'nom' => 'Podcast Gaming'],
    ['id' => 4, 'nom' => 'Humour Web'],
    ['id' => 5, 'nom' => 'Éducation Financière']
];

// Plataformas para filtro
$platforms = ['TikTok', 'Instagram', 'YouTube', 'Facebook', 'X', 'Twitch', 'LinkedIn'];

// Status para filtro
$statuses = [
    ['value' => 'pending', 'label' => 'En attente'],
    ['value' => 'review', 'label' => 'En analyse'],
    ['value' => 'approved', 'label' => 'Approuvée'],
    ['value' => 'rejected', 'label' => 'Rejetée']
];
?>

<link rel="stylesheet" href="/Social-Media-Awards-/assets/css/admin-candidatures.css">

<div class="admin-page-header">
    <div class="page-title">
        <h1><i class="fas fa-file-alt"></i> Gestion des Candidatures</h1>
        <nav class="breadcrumb">
            <a href="dashboard.php">Tableau de bord</a> &gt;
            <span>Candidatures</span>
        </nav>
    </div>
    <div class="header-actions">
        <a href="export-applications.php" class="btn btn-secondary">
            <i class="fas fa-file-export"></i> Exporter
        </a>
        <a href="add-application.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter Manuellement
        </a>
    </div>
</div>

<div class="admin-content">
    <!-- Cartões de estatísticas -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4FBDAB, #45a999);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-info">
                <h3>Total Candidatures</h3>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
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
            <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8, #138496);">
                <i class="fas fa-search"></i>
            </div>
            <div class="stat-info">
                <h3>En Analyse</h3>
                <div class="stat-number"><?php echo $stats['review']; ?></div>
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
            <div class="stat-icon" style="background: linear-gradient(135deg, #FF5A79, #E54A68);">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h3>Rejetées</h3>
                <div class="stat-number"><?php echo $stats['rejected']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #6c757d, #5a6268);">
                <i class="fas fa-paperclip"></i>
            </div>
            <div class="stat-info">
                <h3>Pièces Jointes</h3>
                <div class="stat-number"><?php echo $stats['total_pieces']; ?></div>
            </div>
        </div>
    </div>

    <!-- Filtros avançados -->
    <div class="advanced-filters">
        <div class="filters-header">
            <h3><i class="fas fa-filter"></i> Filtres Avancés</h3>
            <button class="btn btn-link" id="toggleFilters">
                <i class="fas fa-chevron-down"></i> Afficher/Masquer
            </button>
        </div>

        <div class="filters-content" id="filtersContent">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="dateRange">Période</label>
                    <div class="date-range-inputs">
                        <input type="date" id="dateStart" class="form-control" placeholder="Date début">
                        <span>à</span>
                        <input type="date" id="dateEnd" class="form-control" placeholder="Date fin">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="statusFilter">Statut</label>
                    <select id="statusFilter" class="form-control" multiple>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status['value']; ?>">
                                <?php echo $status['label']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="categoryFilter">Catégorie</label>
                    <select id="categoryFilter" class="form-control" multiple>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="platformFilter">Plateforme</label>
                    <select id="platformFilter" class="form-control" multiple>
                        <?php foreach ($platforms as $platform): ?>
                            <option value="<?php echo htmlspecialchars($platform); ?>">
                                <?php echo htmlspecialchars($platform); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="urgencyFilter">Niveau d'Urgence</label>
                    <select id="urgencyFilter" class="form-control">
                        <option value="">Tous</option>
                        <option value="low">Faible</option>
                        <option value="normal">Normal</option>
                        <option value="high">Élevé</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="qualityFilter">Score de Qualité</label>
                    <div class="range-inputs">
                        <input type="number" id="qualityMin" class="form-control" min="0" max="100" placeholder="Min">
                        <span>à</span>
                        <input type="number" id="qualityMax" class="form-control" min="0" max="100" placeholder="Max">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="typeFilter">Type de Candidature</label>
                    <select id="typeFilter" class="form-control">
                        <option value="">Tous</option>
                        <option value="auto">Auto-soumission</option>
                        <option value="manual">Manuelle</option>
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <button class="btn btn-secondary" id="resetFilters">
                    <i class="fas fa-redo"></i> Réinitialiser
                </button>
                <button class="btn btn-primary" id="applyFilters">
                    <i class="fas fa-check"></i> Appliquer les Filtres
                </button>
            </div>
        </div>
    </div>

    <!-- Barre de recherche rapide -->
    <div class="quick-search-bar">
        <div class="search-box">
            <input type="text" id="quickSearch" placeholder="Rechercher par titre, candidat, email...">
            <button class="btn btn-primary">
                <i class="fas fa-search"></i> Rechercher
            </button>
        </div>

        <div class="view-controls">
            <span class="view-label">Affichage:</span>
            <div class="view-options">
                <button class="view-option active" data-view="table">
                    <i class="fas fa-table"></i> Tableau
                </button>
                <button class="view-option" data-view="cards">
                    <i class="fas fa-th-large"></i> Cartes
                </button>
            </div>
        </div>
    </div>

    <!-- Tabela de candidaturas -->
    <div class="applications-container">
        <div class="applications-header">
            <div class="results-info">
                <span id="resultsCount"><?php echo count($applications); ?> candidatures trouvées</span>
                <div class="sort-controls">
                    <select id="sortBy" class="form-control">
                        <option value="date_desc">Date (récentes d'abord)</option>
                        <option value="date_asc">Date (anciennes d'abord)</option>
                        <option value="title_asc">Titre (A-Z)</option>
                        <option value="title_desc">Titre (Z-A)</option>
                        <option value="score_desc">Score (haut d'abord)</option>
                        <option value="score_asc">Score (bas d'abord)</option>
                    </select>
                </div>
            </div>

            <div class="bulk-actions">
                <select id="bulkAction" class="form-control">
                    <option value="">Actions groupées...</option>
                    <option value="set_review">Mettre en analyse</option>
                    <option value="set_approved">Approuver</option>
                    <option value="set_rejected">Rejeter</option>
                    <option value="export">Exporter la sélection</option>
                    <option value="delete">Supprimer</option>
                </select>
                <button class="btn btn-secondary" id="applyBulkAction" disabled>
                    <i class="fas fa-play"></i> Appliquer
                </button>
            </div>
        </div>

        <!-- Vue tableau -->
        <div class="applications-table-view active" id="tableView">
            <div class="table-responsive">
                <table id="applicationsTable">
                    <thead>
                        <tr>
                            <th class="select-column">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Titre / Candidat</th>
                            <th>Catégorie / Édition</th>
                            <th>Plateforme</th>
                            <th>Statut</th>
                            <th>Date Soumission</th>
                            <th>Score</th>
                            <th>Pièces</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $application):
                            // Classes de status
                            $status_class = '';
                            $status_icon = '';
                            $status_text = '';

                            switch ($application['statut']) {
                                case 'pending':
                                    $status_class = 'status-pending';
                                    $status_icon = 'clock';
                                    $status_text = 'En attente';
                                    break;
                                case 'review':
                                    $status_class = 'status-review';
                                    $status_icon = 'search';
                                    $status_text = 'En analyse';
                                    break;
                                case 'approved':
                                    $status_class = 'status-approved';
                                    $status_icon = 'check-circle';
                                    $status_text = 'Approuvée';
                                    break;
                                case 'rejected':
                                    $status_class = 'status-rejected';
                                    $status_icon = 'times-circle';
                                    $status_text = 'Rejetée';
                                    break;
                            }

                            // Classe da plataforma
                            $platform_class = 'platform-' . strtolower($application['plateforme']);

                            // Niveau d'urgence
                            $urgency_class = '';
                            $urgency_text = '';
                            switch ($application['niveau_urgence']) {
                                case 'low':
                                    $urgency_class = 'urgency-low';
                                    $urgency_text = 'Faible';
                                    break;
                                case 'normal':
                                    $urgency_class = 'urgency-normal';
                                    $urgency_text = 'Normal';
                                    break;
                                case 'high':
                                    $urgency_class = 'urgency-high';
                                    $urgency_text = 'Élevé';
                                    break;
                            }

                            // Score de qualité
                            $score_class = '';
                            if ($application['score_qualite'] >= 80) {
                                $score_class = 'score-high';
                            } elseif ($application['score_qualite'] >= 60) {
                                $score_class = 'score-medium';
                            } else {
                                $score_class = 'score-low';
                            }
                        ?>
                            <tr data-id="<?php echo $application['id_candidature']; ?>"
                                data-status="<?php echo $application['statut']; ?>"
                                data-category="<?php echo htmlspecialchars($application['categorie_nom']); ?>"
                                data-platform="<?php echo htmlspecialchars($application['plateforme']); ?>"
                                data-urgency="<?php echo $application['niveau_urgence']; ?>"
                                data-score="<?php echo $application['score_qualite']; ?>"
                                data-date="<?php echo strtotime($application['date_soumission']); ?>"
                                data-title="<?php echo htmlspecialchars(strtolower($application['titre'])); ?>"
                                data-candidate="<?php echo htmlspecialchars(strtolower($application['candidat_nom'])); ?>">

                                <td class="select-column">
                                    <input type="checkbox" class="select-row"
                                        value="<?php echo $application['id_candidature']; ?>">
                                </td>

                                <td>
                                    <div class="application-title">
                                        <strong><?php echo htmlspecialchars($application['titre']); ?></strong>
                                        <div class="candidate-info">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars($application['candidat_nom']); ?></span>
                                            <span class="text-muted">•</span>
                                            <span class="text-muted"><?php echo htmlspecialchars($application['candidat_email']); ?></span>
                                        </div>
                                        <div class="application-meta">
                                            <span class="badge badge-source">
                                                <?php echo $application['type_candidature'] == 'auto' ? 'Auto' : 'Manuelle'; ?>
                                            </span>
                                            <?php if ($application['niveau_urgence'] == 'high'): ?>
                                                <span class="badge badge-urgency-high">
                                                    <i class="fas fa-exclamation-triangle"></i> Urgent
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="category-info">
                                        <span class="badge badge-category">
                                            <?php echo htmlspecialchars($application['categorie_nom']); ?>
                                        </span>
                                        <div class="text-muted" style="font-size: 0.85rem; margin-top: 5px;">
                                            <?php echo htmlspecialchars($application['edition_nom']); ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="platform-tag <?php echo $platform_class; ?>">
                                        <?php if ($application['plateforme'] == 'TikTok'): ?>
                                            <i class="fab fa-tiktok"></i>
                                        <?php elseif ($application['plateforme'] == 'Instagram'): ?>
                                            <i class="fab fa-instagram"></i>
                                        <?php elseif ($application['plateforme'] == 'YouTube'): ?>
                                            <i class="fab fa-youtube"></i>
                                        <?php elseif ($application['plateforme'] == 'Facebook'): ?>
                                            <i class="fab fa-facebook"></i>
                                        <?php elseif ($application['plateforme'] == 'X'): ?>
                                            <i class="fab fa-twitter"></i>
                                        <?php else: ?>
                                            <i class="fas fa-globe"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($application['plateforme']); ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <i class="fas fa-<?php echo $status_icon; ?>"></i>
                                        <?php echo $status_text; ?>
                                    </span>
                                    <?php if ($application['statut'] == 'review'): ?>
                                        <div class="text-muted" style="font-size: 0.8rem; margin-top: 3px;">
                                            <i class="fas fa-history"></i> En cours
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="date-info">
                                        <div class="date-main">
                                            <?php echo date('d/m/Y', strtotime($application['date_soumission'])); ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.8rem;">
                                            <?php echo date('H:i', strtotime($application['date_soumission'])); ?>
                                        </div>
                                        <?php if ($application['date_modification']): ?>
                                            <div class="text-muted" style="font-size: 0.75rem; margin-top: 3px;">
                                                <i class="fas fa-edit"></i> Modifiée
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="score-indicator <?php echo $score_class; ?>">
                                        <div class="score-value">
                                            <?php echo $application['score_qualite']; ?>%
                                        </div>
                                        <div class="score-bar">
                                            <div class="score-fill" style="width: <?php echo $application['score_qualite']; ?>%"></div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="attachments-count">
                                        <span class="badge badge-attachments">
                                            <i class="fas fa-paperclip"></i>
                                            <?php echo $application['pieces_jointes']; ?>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <a href="view-application.php?id=<?php echo $application['id_candidature']; ?>"
                                            class="btn-icon btn-view" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <?php if ($application['statut'] == 'pending' || $application['statut'] == 'review'): ?>
                                            <button type="button"
                                                class="btn-icon btn-review"
                                                title="Mettre en analyse"
                                                data-id="<?php echo $application['id_candidature']; ?>"
                                                data-title="<?php echo htmlspecialchars($application['titre']); ?>">
                                                <i class="fas fa-search"></i>
                                            </button>

                                            <button type="button"
                                                class="btn-icon btn-approve"
                                                title="Approuver"
                                                data-id="<?php echo $application['id_candidature']; ?>"
                                                data-title="<?php echo htmlspecialchars($application['titre']); ?>">
                                                <i class="fas fa-check"></i>
                                            </button>

                                            <button type="button"
                                                class="btn-icon btn-reject"
                                                title="Rejeter"
                                                data-id="<?php echo $application['id_candidature']; ?>"
                                                data-title="<?php echo htmlspecialchars($application['titre']); ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>

                                        <a href="edit-application.php?id=<?php echo $application['id_candidature']; ?>"
                                            class="btn-icon btn-edit" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <button type="button"
                                            class="btn-icon btn-delete"
                                            title="Supprimer"
                                            data-id="<?php echo $application['id_candidature']; ?>"
                                            data-title="<?php echo htmlspecialchars($application['titre']); ?>">
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
                <div class="page-info">
                    Affichage de 1 à <?php echo count($applications); ?> sur 50 résultats
                </div>
                <div class="page-controls">
                    <button class="page-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">4</button>
                    <button class="page-btn">5</button>
                    <button class="page-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Vue cartes -->
        <div class="applications-cards-view" id="cardsView">
            <div class="cards-grid">
                <?php foreach ($applications as $application):
                    // Classes de status
                    $status_class = '';
                    $status_text = '';

                    switch ($application['statut']) {
                        case 'pending':
                            $status_class = 'status-pending';
                            $status_text = 'En attente';
                            break;
                        case 'review':
                            $status_class = 'status-review';
                            $status_text = 'En analyse';
                            break;
                        case 'approved':
                            $status_class = 'status-approved';
                            $status_text = 'Approuvée';
                            break;
                        case 'rejected':
                            $status_class = 'status-rejected';
                            $status_text = 'Rejetée';
                            break;
                    }

                    // Score de qualité
                    $score_class = '';
                    if ($application['score_qualite'] >= 80) {
                        $score_class = 'score-high';
                    } elseif ($application['score_qualite'] >= 60) {
                        $score_class = 'score-medium';
                    } else {
                        $score_class = 'score-low';
                    }
                ?>
                    <div class="application-card" data-id="<?php echo $application['id_candidature']; ?>">
                        <div class="card-header <?php echo $status_class; ?>">
                            <div class="card-status">
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <i class="fas fa-circle"></i> <?php echo $status_text; ?>
                                </span>
                                <span class="card-id">#<?php echo $application['id_candidature']; ?></span>
                            </div>
                            <input type="checkbox" class="card-select"
                                value="<?php echo $application['id_candidature']; ?>">
                        </div>

                        <div class="card-body">
                            <h4 class="card-title"><?php echo htmlspecialchars($application['titre']); ?></h4>

                            <div class="card-candidate">
                                <i class="fas fa-user"></i>
                                <strong><?php echo htmlspecialchars($application['candidat_nom']); ?></strong>
                            </div>

                            <div class="card-email">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($application['candidat_email']); ?></span>
                            </div>

                            <div class="card-details">
                                <div class="detail-item">
                                    <i class="fas fa-tags"></i>
                                    <span><?php echo htmlspecialchars($application['categorie_nom']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-globe"></i>
                                    <span><?php echo htmlspecialchars($application['plateforme']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d/m/Y', strtotime($application['date_soumission'])); ?></span>
                                </div>
                            </div>

                            <div class="card-stats">
                                <div class="stat-item">
                                    <div class="stat-value <?php echo $score_class; ?>">
                                        <?php echo $application['score_qualite']; ?>%
                                    </div>
                                    <div class="stat-label">Score</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value">
                                        <i class="fas fa-paperclip"></i> <?php echo $application['pieces_jointes']; ?>
                                    </div>
                                    <div class="stat-label">Pièces</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value">
                                        <?php echo $application['type_candidature'] == 'auto' ? 'Auto' : 'Manuelle'; ?>
                                    </div>
                                    <div class="stat-label">Type</div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="card-actions">
                                <a href="view-application.php?id=<?php echo $application['id_candidature']; ?>"
                                    class="btn-icon btn-view" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <?php if ($application['statut'] == 'pending' || $application['statut'] == 'review'): ?>
                                    <button type="button"
                                        class="btn-icon btn-review"
                                        title="Mettre en analyse"
                                        data-id="<?php echo $application['id_candidature']; ?>">
                                        <i class="fas fa-search"></i>
                                    </button>

                                    <button type="button"
                                        class="btn-icon btn-approve"
                                        title="Approuver"
                                        data-id="<?php echo $application['id_candidature']; ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>

                                <a href="edit-application.php?id=<?php echo $application['id_candidature']; ?>"
                                    class="btn-icon btn-edit" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>


<!-- Modal de changement de statut -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exchange-alt"></i> Changer le Statut</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p id="statusModalText">Changer le statut de la candidature :</p>
            <div class="status-options">
                <div class="status-option" data-status="review">
                    <span class="status-badge status-review">
                        <i class="fas fa-search"></i> En analyse
                    </span>
                    <p class="status-description">Placer la candidature en cours d'analyse</p>
                </div>
                <div class="status-option" data-status="approved">
                    <span class="status-badge status-approved">
                        <i class="fas fa-check-circle"></i> Approuvée
                    </span>
                    <p class="status-description">Approuver et créer une nomination</p>
                </div>
                <div class="status-option" data-status="rejected">
                    <span class="status-badge status-rejected">
                        <i class="fas fa-times-circle"></i> Rejetée
                    </span>
                    <p class="status-description">Rejeter la candidature</p>
                </div>
            </div>

            <div class="form-group" id="rejectionReasonGroup" style="display: none;">
                <label for="rejectionReason">Motif du rejet *</label>
                <select id="rejectionReason" required>
                    <option value="">Sélectionner un motif</option>
                    <option value="criteria">Ne respecte pas les critères</option>
                    <option value="quality">Qualité insuffisante</option>
                    <option value="incomplete">Dossier incomplet</option>
                    <option value="duplicate">Doublon</option>
                    <option value="inappropriate">Contenu inapproprié</option>
                    <option value="other">Autre</option>
                </select>
            </div>

            <div class="form-group">
                <label for="statusNotes">Notes (optionnel)</label>
                <textarea id="statusNotes" rows="3" placeholder="Ajoutez des notes internes..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelStatusBtn">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button id="confirmStatusBtn" class="btn btn-primary">
                <i class="fas fa-save"></i> Confirmer
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
            <p>Êtes-vous sûr de vouloir supprimer la candidature <strong id="deleteApplicationTitle"></strong> ?</p>
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

<!-- Modal d'approbation en masse -->
<div id="bulkModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-users"></i> Action Groupée</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p id="bulkModalText">Appliquer l'action à <span id="selectedCount">0</span> candidature(s) sélectionnée(s) :</p>
            <div class="form-group">
                <label for="bulkStatus">Nouveau statut</label>
                <select id="bulkStatus" class="form-control">
                    <option value="review">Mettre en analyse</option>
                    <option value="approved">Approuver</option>
                    <option value="rejected">Rejeter</option>
                </select>
            </div>
            <div class="form-group">
                <label for="bulkNotes">Notes (optionnel)</label>
                <textarea id="bulkNotes" rows="3" placeholder="Notes à appliquer à toutes les candidatures..."></textarea>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Cette action sera appliquée à toutes les candidatures sélectionnées.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelBulkBtn">
                <i class="fas fa-times"></i> Annuler
            </button>
            <button id="confirmBulkBtn" class="btn btn-primary">
                <i class="fas fa-check"></i> Confirmer
            </button>
        </div>
    </div>
</div>

<script src="/Social-Media-Awards-/assets/js/admin-candidatures.js"></script>
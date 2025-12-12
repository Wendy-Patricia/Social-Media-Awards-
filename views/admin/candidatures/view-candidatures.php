<?php
// view-application.php
require_once __DIR__ . '/../../partials/admin-header.php';

// ID da candidatura da URL
$application_id = $_GET['id'] ?? 0;

// Dados simulados (substituir por consulta ao banco de dados)
$application = [
    'id_candidature' => $application_id,
    'titre' => 'Créateur Tech Innovant 2024',
    'description' => 'Candidature pour le prix du créateur tech le plus innovant de l\'année 2024.',
    'candidat_nom' => 'TechInnovator',
    'candidat_email' => 'contact@techinnovator.com',
    'candidat_telephone' => '+33 6 12 34 56 78',
    'candidat_entreprise' => 'Tech Innovations SAS',
    'candidat_position' => 'Fondateur & Créateur de Contenu',
    'candidat_pays' => 'France',
    'candidat_ville' => 'Paris',
    'plateforme' => 'TikTok',
    'lien_plateforme' => 'https://tiktok.com/@techinnovator',
    'abonnes' => '1,200,000',
    'engagement_rate' => '8.5%',
    'categorie_nom' => 'Créateur Tech de l\'Année',
    'id_categorie' => 1,
    'edition_nom' => 'Social Media Awards 2024',
    'id_edition' => 1,
    'date_soumission' => '2024-03-15 14:30:00',
    'date_modification' => '2024-03-16 09:15:00',
    'statut' => 'review',
    'type_candidature' => 'auto',
    'source' => 'Formulaire en ligne',
    'niveau_urgence' => 'normal',
    'score_qualite' => 85,
    'argumentation' => 'Je crée du contenu éducatif de haute qualité qui explique les technologies complexes de manière accessible. Avec plus de 500 vidéos publiées et une communauté de 1,2M d\'abonnés, j\'ai réussi à démocratiser l\'accès aux connaissances technologiques.',
    'realisations' => '• 1,2M abonnés sur TikTok
• 50M de vues mensuelles
• 500+ vidéos éducatives
• Collaboration avec Microsoft et Google
• Prix du "Meilleur Contenu Éducatif 2023"',
    'objectifs' => 'Continuer à créer du contenu éducatif de qualité et étendre ma communauté à 2M d\'abonnés d\'ici fin 2024.',
    'notes_internes' => 'Candidat très prometteur, contenu de haute qualité et engagement communautaire exceptionnel.',
    'assignee_id' => 2,
    'assignee_nom' => 'Marie Dubois',
    'date_revue' => '2024-03-16 10:00:00',
    'date_decision' => null
];

// Pièces jointes
$attachments = [
    [
        'id' => 1,
        'nom' => 'portfolio.pdf',
        'type' => 'PDF',
        'taille' => '2.4 MB',
        'date_upload' => '2024-03-15 14:25:00'
    ],
    [
        'id' => 2,
        'nom' => 'statistiques_tiktok.xlsx',
        'type' => 'Excel',
        'taille' => '1.8 MB',
        'date_upload' => '2024-03-15 14:26:00'
    ],
    [
        'id' => 3,
        'nom' => 'video_presentation.mp4',
        'type' => 'Vidéo',
        'taille' => '15.2 MB',
        'date_upload' => '2024-03-15 14:28:00'
    ]
];

// Historique des modifications
$history = [
    [
        'date' => '2024-03-16 09:15:00',
        'action' => 'Mise en analyse',
        'utilisateur' => 'Marie Dubois',
        'details' => 'La candidature a été placée en cours d\'analyse.'
    ],
    [
        'date' => '2024-03-15 14:30:00',
        'action' => 'Soumission',
        'utilisateur' => 'Auto-soumission',
        'details' => 'Candidature soumise via le formulaire en ligne.'
    ]
];

// Status atual
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

<link rel="stylesheet" href="/Social-Media-Awards-/assets/css/admin-candidatures.css">

<div class="admin-page-header">
    <div class="page-title">
        <h1><i class="fas fa-eye"></i> Détails de la Candidature</h1>
        <nav class="breadcrumb">
            <a href="dashboard.php">Tableau de bord</a> &gt;
            <a href="manage-applications.php">Candidatures</a> &gt;
            <span>#<?php echo $application_id; ?></span>
        </nav>
    </div>
    <div class="header-actions">
        <a href="manage-applications.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <a href="edit-application.php?id=<?php echo $application_id; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Modifier
        </a>
    </div>
</div>

<div class="admin-content">
    <!-- Cabeçalho da candidatura -->
    <div class="application-header">
        <div class="header-main">
            <h2><?php echo htmlspecialchars($application['titre']); ?></h2>
            <div class="header-meta">
                <span class="badge badge-id">#<?php echo $application_id; ?></span>
                <span class="status-badge <?php echo $status_class; ?>">
                    <i class="fas fa-circle"></i> <?php echo $status_text; ?>
                </span>
                <span class="badge badge-source">
                    <?php echo $application['type_candidature'] == 'auto' ? 'Auto-soumission' : 'Manuelle'; ?>
                </span>
                <span class="text-muted">
                    Soumise le <?php echo date('d/m/Y à H:i', strtotime($application['date_soumission'])); ?>
                </span>
            </div>
        </div>

        <div class="header-actions">
            <?php if ($application['statut'] == 'pending' || $application['statut'] == 'review'): ?>
                <button class="btn btn-review" id="reviewBtn">
                    <i class="fas fa-search"></i> Mettre en analyse
                </button>
                <button class="btn btn-approve" id="approveBtn">
                    <i class="fas fa-check"></i> Approuver
                </button>
                <button class="btn btn-reject" id="rejectBtn">
                    <i class="fas fa-times"></i> Rejeter
                </button>
            <?php endif; ?>

            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i> Plus
                </button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-print"></i> Imprimer
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-download"></i> Télécharger PDF
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-share"></i> Partager
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item text-danger" id="deleteBtn">
                        <i class="fas fa-trash"></i> Supprimer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações principais -->
    <div class="info-grid">
        <!-- Coluna esquerda -->
        <div class="info-column">
            <!-- Informações do candidato -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> Informations du Candidat</h3>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <label><i class="fas fa-user-tag"></i> Nom complet</label>
                        <p><?php echo htmlspecialchars($application['candidat_nom']); ?></p>
                    </div>

                    <div class="info-item">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <p>
                            <a href="mailto:<?php echo htmlspecialchars($application['candidat_email']); ?>">
                                <?php echo htmlspecialchars($application['candidat_email']); ?>
                            </a>
                        </p>
                    </div>

                    <div class="info-item">
                        <label><i class="fas fa-phone"></i> Téléphone</label>
                        <p>
                            <a href="tel:<?php echo htmlspecialchars($application['candidat_telephone']); ?>">
                                <?php echo htmlspecialchars($application['candidat_telephone']); ?>
                            </a>
                        </p>
                    </div>

                    <?php if ($application['candidat_entreprise']): ?>
                        <div class="info-item">
                            <label><i class="fas fa-building"></i> Entreprise</label>
                            <p><?php echo htmlspecialchars($application['candidat_entreprise']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($application['candidat_position']): ?>
                        <div class="info-item">
                            <label><i class="fas fa-briefcase"></i> Position</label>
                            <p><?php echo htmlspecialchars($application['candidat_position']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="info-item">
                        <label><i class="fas fa-map-marker-alt"></i> Localisation</label>
                        <p><?php echo htmlspecialchars($application['candidat_ville']); ?>, <?php echo htmlspecialchars($application['candidat_pays']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Plateforme et statistiques -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Plateforme & Statistiques</h3>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <label><i class="fas fa-globe"></i> Plateforme principale</label>
                        <p>
                            <span class="platform-tag platform-<?php echo strtolower($application['plateforme']); ?>">
                                <?php if ($application['plateforme'] == 'TikTok'): ?>
                                    <i class="fab fa-tiktok"></i>
                                <?php elseif ($application['plateforme'] == 'Instagram'): ?>
                                    <i class="fab fa-instagram"></i>
                                <?php elseif ($application['plateforme'] == 'YouTube'): ?>
                                    <i class="fab fa-youtube"></i>
                                <?php elseif ($application['plateforme'] == 'Facebook'): ?>
                                    <i class="fab fa-facebook"></i>
                                <?php else: ?>
                                    <i class="fas fa-globe"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($application['plateforme']); ?>
                            </span>
                        </p>
                    </div>

                    <div class="info-item">
                        <label><i class="fas fa-link"></i> Lien du profil</label>
                        <p>
                            <a href="<?php echo htmlspecialchars($application['lien_plateforme']); ?>" target="_blank" class="text-link">
                                <?php echo htmlspecialchars($application['lien_plateforme']); ?>
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </p>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $application['abonnes']; ?></div>
                            <div class="stat-label">Abonnés</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $application['engagement_rate']; ?></div>
                            <div class="stat-label">Taux d'engagement</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pièces jointes -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-paperclip"></i> Pièces Jointes (<?php echo count($attachments); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($attachments)): ?>
                        <p class="text-muted">Aucune pièce jointe</p>
                    <?php else: ?>
                        <div class="attachments-list">
                            <?php foreach ($attachments as $attachment): ?>
                                <div class="attachment-item">
                                    <div class="attachment-icon">
                                        <?php if ($attachment['type'] == 'PDF'): ?>
                                            <i class="fas fa-file-pdf"></i>
                                        <?php elseif ($attachment['type'] == 'Excel'): ?>
                                            <i class="fas fa-file-excel"></i>
                                        <?php elseif ($attachment['type'] == 'Vidéo'): ?>
                                            <i class="fas fa-file-video"></i>
                                        <?php else: ?>
                                            <i class="fas fa-file"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="attachment-info">
                                        <div class="attachment-name"><?php echo htmlspecialchars($attachment['nom']); ?></div>
                                        <div class="attachment-meta">
                                            <span><?php echo $attachment['type']; ?></span>
                                            <span>•</span>
                                            <span><?php echo $attachment['taille']; ?></span>
                                            <span>•</span>
                                            <span><?php echo date('d/m/Y', strtotime($attachment['date_upload'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="attachment-actions">
                                        <a href="#" class="btn-icon btn-download" title="Télécharger">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="#" class="btn-icon btn-preview" title="Prévisualiser">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Coluna direita -->
        <div class="info-column">
            <!-- Informações da candidatura -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Informations de la Candidature</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid-small">
                        <div class="info-item">
                            <label><i class="fas fa-tags"></i> Catégorie</label>
                            <p>
                                <span class="badge badge-category">
                                    <?php echo htmlspecialchars($application['categorie_nom']); ?>
                                </span>
                            </p>
                        </div>

                        <div class="info-item">
                            <label><i class="fas fa-calendar-alt"></i> Édition</label>
                            <p><?php echo htmlspecialchars($application['edition_nom']); ?></p>
                        </div>

                        <div class="info-item">
                            <label><i class="fas fa-signal"></i> Niveau d'urgence</label>
                            <p>
                                <span class="badge badge-urgency-<?php echo $application['niveau_urgence']; ?>">
                                    <?php
                                    echo $application['niveau_urgence'] == 'high' ? 'Élevé' : ($application['niveau_urgence'] == 'normal' ? 'Normal' : 'Faible');
                                    ?>
                                </span>
                            </p>
                        </div>

                        <div class="info-item">
                            <label><i class="fas fa-chart-bar"></i> Score de qualité</label>
                            <div class="score-indicator <?php echo $score_class; ?>">
                                <div class="score-value">
                                    <?php echo $application['score_qualite']; ?>%
                                </div>
                                <div class="score-bar">
                                    <div class="score-fill" style="width: <?php echo $application['score_qualite']; ?>%"></div>
                                </div>
                            </div>
                        </div>

                        <?php if ($application['assignee_nom']): ?>
                            <div class="info-item">
                                <label><i class="fas fa-user-check"></i> Assignée à</label>
                                <p>
                                    <i class="fas fa-user-circle"></i>
                                    <?php echo htmlspecialchars($application['assignee_nom']); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if ($application['date_revue']): ?>
                            <div class="info-item">
                                <label><i class="fas fa-search"></i> Date de revue</label>
                                <p><?php echo date('d/m/Y H:i', strtotime($application['date_revue'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Argumentation et contenu -->
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-file-alt"></i> Argumentation</h3>
                </div>
                <div class="card-body">
                    <div class="content-section">
                        <h4>Description</h4>
                        <p><?php echo nl2br(htmlspecialchars($application['description'])); ?></p>
                    </div>

                    <div class="content-section">
                        <h4>Argumentation principale</h4>
                        <p><?php echo nl2br(htmlspecialchars($application['argumentation'])); ?></p>
                    </div>

                    <?php if ($application['realisations']): ?>
                        <div class="content-section">
                            <h4>Réalisations</h4>
                            <div class="realisations-list">
                                <?php echo nl2br(htmlspecialchars($application['realisations'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($application['objectifs']): ?>
                        <div class="content-section">
                            <h4>Objectifs</h4>
                            <p><?php echo nl2br(htmlspecialchars($application['objectifs'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes internes -->
            <?php if ($application['notes_internes']): ?>
                <div class="info-card card-notes">
                    <div class="card-header">
                        <h3><i class="fas fa-sticky-note"></i> Notes Internes</h3>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($application['notes_internes'])); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Historique -->
    <div class="history-section">
        <div class="section-header">
            <h3><i class="fas fa-history"></i> Historique des Modifications</h3>
        </div>
        <div class="timeline">
            <?php foreach ($history as $item): ?>
                <div class="timeline-item">
                    <div class="timeline-date">
                        <?php echo date('d/m/Y H:i', strtotime($item['date'])); ?>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h4><?php echo $item['action']; ?></h4>
                            <span class="timeline-user">
                                <i class="fas fa-user"></i> <?php echo $item['utilisateur']; ?>
                            </span>
                        </div>
                        <?php if ($item['details']): ?>
                            <p><?php echo $item['details']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<!-- Modals (reutilizados do manage-applications.php) -->
<?php include 'modals/status-modal.php'; ?>
<?php include 'modals/delete-modal.php'; ?>

<script src="/Social-Media-Awards-/assets/js/admin-applications.js"></script>
<script>
    // Scripts específicos para view-application.php
    document.addEventListener('DOMContentLoaded', function() {
        // Botões de ação
        const reviewBtn = document.getElementById('reviewBtn');
        const approveBtn = document.getElementById('approveBtn');
        const rejectBtn = document.getElementById('rejectBtn');
        const deleteBtn = document.getElementById('deleteBtn');

        if (reviewBtn) {
            reviewBtn.addEventListener('click', function() {
                showStatusModal('review', '<?php echo htmlspecialchars($application["titre"]); ?>');
            });
        }

        if (approveBtn) {
            approveBtn.addEventListener('click', function() {
                showStatusModal('approved', '<?php echo htmlspecialchars($application["titre"]); ?>');
            });
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', function() {
                showStatusModal('rejected', '<?php echo htmlspecialchars($application["titre"]); ?>');
            });
        }

        if (deleteBtn) {
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                showDeleteModal('<?php echo htmlspecialchars($application["titre"]); ?>', <?php echo $application_id; ?>);
            });
        }

        // Funções para mostrar modals (definidas no arquivo principal)
        function showStatusModal(action, title) {
            // Esta função seria definida no admin-applications.js
            console.log('Ação:', action, 'Título:', title);
            // Implementar abertura do modal
        }

        function showDeleteModal(title, id) {
            // Esta função seria definida no admin-applications.js
            console.log('Excluir:', title, 'ID:', id);
            // Implementar abertura do modal
        }

        // Download de arquivos
        const downloadButtons = document.querySelectorAll('.btn-download');
        downloadButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                // Implementar download
                console.log('Download do arquivo');
            });
        });

        // Pré-visualização de arquivos
        const previewButtons = document.querySelectorAll('.btn-preview');
        previewButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                // Implementar pré-visualização
                console.log('Pré-visualizar arquivo');
            });
        });
    });
</script>
<?php
require_once __DIR__ . '/../../../app/autoload.php';
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();
require_once __DIR__ . '/../../../config/bootstrap-admin.php';

require_once __DIR__ . '/../../../app/Controllers/AdminController.php';

$controller = new App\Controllers\AdminController();
$candidatures = $controller->getAllCandidatures();

$stats = [
    'total' => count($candidatures),
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
];

foreach ($candidatures as $c) {
    switch ($c['statut']) {
        case 'En attente': $stats['pending']++; break;
        case 'Approuvée': $stats['approved']++; break;
        case 'Rejetée': $stats['rejected']++; break;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $controller->deleteCandidature($id);
        header("Location: manage-candidatures.php?success=1");
        exit;
    }
}

if (isset($_GET['success'])) {
    $successMsg = match($_GET['success']) {
        '1' => 'Candidatura suprimida com sucesso.',
        '2' => 'Candidatura aprovada e transformada em nomeação.',
        '3' => 'Candidatura rejeitada.',
        default => 'Ação realizada com sucesso.'
    };
}

if (isset($_GET['error'])) {
    $errorMsg = match($_GET['error']) {
        '1' => 'Erro: Método inválido.',
        '2' => 'Erro: Candidatura não encontrada.',
        '3' => 'Erro: Candidatura já processada.',
        '4' => 'Erro: Falha na transação.',
        default => 'Ocorreu um erro.'
    };
}

require_once __DIR__ . '/../../../views/partials/admin-header.php';
?>

<link rel="stylesheet" href="../../../assets/css/admin-candidatures.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="admin-main-content admin-candidatures-page">
    <div class="admin-page-header">
        <div class="page-title">
            <h1><i class="fas fa-file-alt"></i> Gestion des Candidatures</h1>
            <p><?= $stats['total'] ?> candidature(s) trouvée(s)</p>
        </div>
        
        <?php if (isset($successMsg)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $successMsg ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($errorMsg)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= $errorMsg ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="admin-content">
        <div class="stats-cards">
            <div class="stat-card total">
                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <h3>Total</h3>
                </div>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['pending'] ?></div>
                    <h3>En attente</h3>
                </div>
            </div>
            <div class="stat-card approved">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['approved'] ?></div>
                    <h3>Approuvées</h3>
                </div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['rejected'] ?></div>
                    <h3>Rejetées</h3>
                </div>
            </div>
        </div>

        <div class="table-controls">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher par nom, email, plateforme...">
            </div>
            <div class="filter-options">
                <select id="statusFilter">
                    <option value="">Tous les statuts</option>
                    <option value="En attente">En attente</option>
                    <option value="Approuvée">Approuvées</option>
                    <option value="Rejetée">Rejetées</option>
                </select>
                <select id="categoryFilter">
                    <option value="">Toutes catégories</option>
                    <?php
                    $categories = $controller->getAllCategories();
                    foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['nom']) ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="enhanced-table" id="candidaturesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Candidature</th>
                        <th>Candidat</th>
                        <th>Plateforme</th>
                        <th>Catégorie</th>
                        <th>Soumission</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($candidatures)): ?>
                    <tr>
                        <td colspan="8" class="text-center">Aucune candidature trouvée</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($candidatures as $c): ?>
                    <tr data-status="<?= $c['statut'] ?>" data-category="<?= htmlspecialchars($c['categorie_nom']) ?>">
                        <td class="id-column">#<?= $c['id_candidature'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($c['libelle']) ?></strong><br>
                            <small class="text-muted">Édition: <?= htmlspecialchars($c['edition_nom']) ?></small>
                        </td>
                        <td>
                            <div class="candidate-info">
                                <div class="candidate-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($c['candidat_pseudonyme']) ?></strong><br>
                                    <small><?= htmlspecialchars($c['candidat_email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="platform-badge platform-<?= strtolower($c['plateforme']) ?>">
                                <i class="fab fa-<?= strtolower($c['plateforme']) == 'instagram' ? 'instagram' : 
                                                  (strtolower($c['plateforme']) == 'tiktok' ? 'tiktok' : 
                                                  (strtolower($c['plateforme']) == 'youtube' ? 'youtube' : 'globe')) ?>"></i>
                                <?= htmlspecialchars($c['plateforme']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="category-tag"><?= htmlspecialchars($c['categorie_nom']) ?></span>
                        </td>
                        <td>
                            <div class="date-info">
                                <div><?= date('d/m/Y', strtotime($c['date_soumission'])) ?></div>
                                <small><?= date('H:i', strtotime($c['date_soumission'])) ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $c['statut'])) ?>">
                                <i class="fas fa-circle"></i>
                                <?= $c['statut'] ?>
                            </span>
                        </td>
                        <td class="actions-cell">
                            <div class="action-buttons">
                                <a href="view-candidature.php?id=<?= $c['id_candidature'] ?>" class="action-btn view" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($c['statut'] === 'En attente'): ?>
                                <button class="action-btn process" onclick="openProcessModal(<?= $c['id_candidature'] ?>, '<?= addslashes($c['libelle']) ?>', 'approve')" title="Approuver">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="action-btn reject" onclick="openProcessModal(<?= $c['id_candidature'] ?>, '<?= addslashes($c['libelle']) ?>', 'reject')" title="Rejeter">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php else: ?>
                                <button class="action-btn status" disabled title="Déjà traité">
                                    <i class="fas fa-check-double"></i>
                                </button>
                                <?php endif; ?>
                                <button onclick="openDeleteModal(<?= $c['id_candidature'] ?>, '<?= addslashes($c['libelle']) ?>')" class="action-btn delete" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="processModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-cogs"></i> Traiter la Candidature</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="processModalTitle"></p>
            <form id="processForm" action="process-candidature.php" method="POST">
                <input type="hidden" id="processId" name="id">
                <input type="hidden" id="processAction" name="action">
                
                <div class="form-group">
                    <label for="processComment">Commentaire (optionnel):</label>
                    <textarea id="processComment" name="comment" class="form-control" rows="4" placeholder="Ajoutez un commentaire pour le candidat..."></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <small>Ce commentaire sera visible par le candidat.</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <button class="btn btn-success" id="confirmProcessBtn">
                <i class="fas fa-check"></i> Confirmer
            </button>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-trash"></i> Confirmer la Suppression</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="deleteModalText"></p>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Attention:</strong> Cette action est irréversible.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal">Annuler</button>
            <a href="#" id="confirmDeleteLink" class="btn btn-danger">
                <i class="fas fa-trash"></i> Supprimer
            </a>
        </div>
    </div>
</div>

<script src="../../../assets/js/admin-candidatures.js"></script>
<script>
function openProcessModal(id, libelle, action) {
    const modal = document.getElementById('processModal');
    const title = document.getElementById('processModalTitle');
    const processId = document.getElementById('processId');
    const processAction = document.getElementById('processAction');
    
    processId.value = id;
    processAction.value = action;
    
    if (action === 'approve') {
        title.innerHTML = `Approuver la candidature: <strong>"${libelle}"</strong>`;
        document.getElementById('confirmProcessBtn').className = 'btn btn-success';
        document.getElementById('confirmProcessBtn').innerHTML = '<i class="fas fa-check"></i> Approuver';
    } else {
        title.innerHTML = `Rejeter la candidature: <strong>"${libelle}"</strong>`;
        document.getElementById('confirmProcessBtn').className = 'btn btn-danger';
        document.getElementById('confirmProcessBtn').innerHTML = '<i class="fas fa-times"></i> Rejeter';
    }
    
    modal.style.display = 'flex';
}

function openDeleteModal(id, libelle) {
    const modal = document.getElementById('deleteModal');
    const text = document.getElementById('deleteModalText');
    const link = document.getElementById('confirmDeleteLink');
    
    text.innerHTML = `Êtes-vous sûr de vouloir supprimer définitivement la candidature <strong>"${libelle}"</strong> ?`;
    link.href = `manage-candidatures.php?delete=${id}`;
    
    modal.style.display = 'flex';
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmProcessBtn').addEventListener('click', function() {
        document.getElementById('processForm').submit();
    });
    
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        });
    });
});function openProcessModal(id, libelle, action) {
    const modal = document.getElementById('processModal');
    const title = document.getElementById('processModalTitle');
    const processId = document.getElementById('processId');
    const processAction = document.getElementById('processAction');
    
    processId.value = id;
    processAction.value = action;
    
    if (action === 'approve') {
        title.innerHTML = `Approuver la candidature: <strong>"${libelle}"</strong>`;
        document.getElementById('confirmProcessBtn').className = 'btn btn-success';
        document.getElementById('confirmProcessBtn').innerHTML = '<i class="fas fa-check"></i> Approuver';
    } else {
        title.innerHTML = `Rejeter la candidature: <strong>"${libelle}"</strong>`;
        document.getElementById('confirmProcessBtn').className = 'btn btn-danger';
        document.getElementById('confirmProcessBtn').innerHTML = '<i class="fas fa-times"></i> Rejeter';
    }
    
    modal.style.display = 'flex';
}

function openDeleteModal(id, libelle) {
    const modal = document.getElementById('deleteModal');
    const text = document.getElementById('deleteModalText');
    const link = document.getElementById('confirmDeleteLink');
    
    text.innerHTML = `Êtes-vous sûr de vouloir supprimer définitivement la candidature <strong>"${libelle}"</strong> ?`;
    link.href = `manage-candidatures.php?delete=${id}`;
    
    modal.style.display = 'flex';
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmProcessBtn').addEventListener('click', function() {
        document.getElementById('processForm').submit();
    });
    
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
        });
    });
});
</script>
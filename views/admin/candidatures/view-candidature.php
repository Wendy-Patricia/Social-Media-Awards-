<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: manage-candidatures.php");
    exit;
}

require_once __DIR__ . '/../../../app/Controllers/AdminController.php';

$controller = new App\Controllers\AdminController();
$candidature = $controller->getCandidatureById($id);

if (!$candidature) {
    header("Location: manage-candidatures.php");
    exit;
}

if (isset($_GET['success'])) {
    $successMsg = match($_GET['success']) {
        '1' => 'Candidatura processada com sucesso.',
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

<div class="admin-main-content admin-candidature-detail-page">
    <div class="admin-page-header">
        <div class="page-title">
            <h1><i class="fas fa-eye"></i> Détails de la Candidature #<?= $id ?></h1>
            <p><?= htmlspecialchars($candidature['libelle']) ?></p>
        </div>
        <div class="header-actions">
            <a href="manage-candidatures.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
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
        <div class="detail-grid">
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Informations Générales</h3>
                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $candidature['statut'])) ?>">
                        <i class="fas fa-circle"></i> <?= $candidature['statut'] ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <strong><i class="fas fa-user"></i> Candidat :</strong>
                        <div class="info-content">
                            <span class="candidate-name"><?= htmlspecialchars($candidature['candidat_pseudonyme']) ?></span>
                            <span class="candidate-email"><?= htmlspecialchars($candidature['candidat_email']) ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <strong><i class="fas fa-globe"></i> Plateforme :</strong>
                        <span class="platform-badge platform-<?= strtolower($candidature['plateforme']) ?>">
                            <?= htmlspecialchars($candidature['plateforme']) ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <strong><i class="fas fa-link"></i> Lien :</strong>
                        <a href="<?= htmlspecialchars($candidature['url_contenu']) ?>" target="_blank" class="content-link">
                            <i class="fas fa-external-link-alt"></i> Ouvrir le contenu
                        </a>
                    </div>
                    
                    <div class="info-item">
                        <strong><i class="fas fa-tag"></i> Catégorie :</strong>
                        <span class="category-tag"><?= htmlspecialchars($candidature['categorie_nom']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <strong><i class="fas fa-trophy"></i> Édition :</strong>
                        <?= htmlspecialchars($candidature['edition_nom']) ?>
                    </div>
                    
                    <div class="info-item">
                        <strong><i class="fas fa-calendar-alt"></i> Soumission :</strong>
                        <?= date('d/m/Y à H:i', strtotime($candidature['date_soumission'])) ?>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-comment-alt"></i> Argumentaire</h3>
                </div>
                <div class="card-body">
                    <div class="argumentaire-content">
                        <?= nl2br(htmlspecialchars($candidature['argumentaire'])) ?>
                    </div>
                </div>
            </div>

            <?php if ($candidature['image']): ?>
            <div class="info-card full-width">
                <div class="card-header">
                    <h3><i class="fas fa-image"></i> Image soumise</h3>
                </div>
                <div class="card-body text-center">
                    <div class="image-container">
                        <img src="../../../public/<?= htmlspecialchars($candidature['image']) ?>" 
                             alt="Image candidature" 
                             class="submitted-image"
                             onerror="this.src='../../../assets/images/default-image.jpg'">
                        <div class="image-actions">
                            <a href="../../../public/<?= htmlspecialchars($candidature['image']) ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-expand"></i> Agrandir
                            </a>
                            <button onclick="downloadImage('<?= htmlspecialchars($candidature['image']) ?>')" 
                                    class="btn btn-sm btn-secondary">
                                <i class="fas fa-download"></i> Télécharger
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <?php if ($candidature['statut'] === 'En attente'): ?>
            <button class="btn btn-success" onclick="openProcessModal(<?= $id ?>, '<?= addslashes($candidature['libelle']) ?>', 'approve')">
                <i class="fas fa-check"></i> Approuver
            </button>
            <button class="btn btn-danger" onclick="openProcessModal(<?= $id ?>, '<?= addslashes($candidature['libelle']) ?>', 'reject')">
                <i class="fas fa-times"></i> Rejeter
            </button>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Cette candidature a déjà été traitée.
            </div>
            <?php endif; ?>
            
            <button onclick="openDeleteModal(<?= $id ?>, '<?= addslashes($candidature['libelle']) ?>')" class="btn btn-outline-danger">
                <i class="fas fa-trash"></i> Supprimer
            </button>
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
function downloadImage(imagePath) {
    const link = document.createElement('a');
    link.href = '../../../public/' + imagePath;
    link.download = 'candidature-' + <?= $id ?> + '-' + new Date().getTime() + '.jpg';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

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
});
</script>
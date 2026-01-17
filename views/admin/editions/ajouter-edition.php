<?php
require_once __DIR__ . '/../../../app/autoload.php';
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();
require_once __DIR__ . '/../../../config/bootstrap-admin.php';

$error = '';
$formData = [
    'annee' => $_POST['annee'] ?? date('Y') + 1,
    'nom' => $_POST['nom'] ?? '',
    'description' => $_POST['description'] ?? '',
    'theme' => $_POST['theme'] ?? '',
    'date_debut_candidatures' => $_POST['date_debut_candidatures'] ?? '',
    'date_fin_candidatures' => $_POST['date_fin_candidatures'] ?? '',
    'date_debut' => $_POST['date_debut'] ?? '',
    'date_fin' => $_POST['date_fin'] ?? ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'annee' => (int)($_POST['annee'] ?? date('Y')),
        'nom' => trim($_POST['nom'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'theme' => trim($_POST['theme'] ?? ''),
        'date_debut_candidatures' => $_POST['date_debut_candidatures'] ?? '',
        'date_fin_candidatures' => $_POST['date_fin_candidatures'] ?? '',
        'date_debut' => $_POST['date_debut'] ?? '',
        'date_fin' => $_POST['date_fin'] ?? ''
    ];

    $validationErrors = [];

    if ($data['annee'] < 2000 || $data['annee'] > 2100) {
        $validationErrors[] = "L'année doit être entre 2000 et 2100.";
    }
    if (empty($data['nom'])) {
        $validationErrors[] = "Le nom de l'édition est requis.";
    }
    if (
        empty($data['date_debut_candidatures']) || empty($data['date_fin_candidatures']) ||
        empty($data['date_debut']) || empty($data['date_fin'])
    ) {
        $validationErrors[] = "Toutes les dates sont obligatoires.";
    } else {
        if (
            strtotime($data['date_debut_candidatures']) >= strtotime($data['date_fin_candidatures']) ||
            strtotime($data['date_fin_candidatures']) >= strtotime($data['date_debut']) ||
            strtotime($data['date_debut']) >= strtotime($data['date_fin'])
        ) {
            $validationErrors[] = "Les dates doivent être dans l'ordre logique: Début candidatures < Fin candidatures < Début édition < Fin édition.";
        }
        
        // Vérifier s'il y a déjà une édition active pendant cette période
        $now = date('Y-m-d H:i:s');
        $debut = $data['date_debut'];
        $fin = $data['date_fin'];
        
        // Vérifier si les dates chevauchent une édition existante
        $checkOverlapSql = "SELECT COUNT(*) as count FROM edition 
                           WHERE (date_debut <= :fin AND date_fin >= :debut)
                           OR (date_debut >= :debut2 AND date_fin <= :fin2)";
        $checkStmt = $pdo->prepare($checkOverlapSql);
        $checkStmt->execute([
            ':debut' => $debut,
            ':fin' => $fin,
            ':debut2' => $debut,
            ':fin2' => $fin
        ]);
        $overlapCount = $checkStmt->fetch()['count'];
        
        if ($overlapCount > 0) {
            $validationErrors[] = "Une édition existe déjà pendant cette période. Les éditions ne peuvent pas se chevaucher.";
        }
    }

    if (empty($validationErrors)) {
        $image = $_FILES['image'] ?? null;
        try {
            if ($editionController->createEdition($data, $image)) {
                header("Location: gerer-editions.php?success=1");
                exit;
            } else {
                $error = "Erreur lors de la création de l'édition.";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = implode("<br>", $validationErrors);
    }
}

$existingYears = [];
try {
    $sql = "SELECT annee FROM edition ORDER BY annee DESC";
    $stmt = $pdo->query($sql);
    $existingYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
}

require_once __DIR__ . '/../../../views/partials/admin-header.php';
?>

<link rel="stylesheet" href="../../../assets/css/admin-add-edition.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="admin-main-content admin-edition-form-page">
    <div class="admin-page-header">
        <div class="page-title">
            <h1><i class="fas fa-plus-circle"></i> Créer une nouvelle édition</h1>
            <p>Ajoutez une nouvelle édition aux Social Media Awards</p>
        </div>
        <a href="gerer-editions.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($existingYears)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Années déjà utilisées: <strong><?= implode(', ', $existingYears) ?></strong>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Note importante:</strong> Une édition est automatiquement active si la date actuelle est comprise entre sa date de début et sa date de fin. Les éditions ne peuvent pas se chevaucher.
        </div>

        <form method="POST" enctype="multipart/form-data" class="edition-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="nom" class="form-label required">Nom de l'édition</label>
                    <input type="text" name="nom" id="nom" class="form-control" required maxlength="100"
                        value="<?= htmlspecialchars($formData['nom']) ?>" placeholder="Ex: Social Media Awards <?= date('Y') + 1 ?>">
                </div>
                <div class="form-group">
                    <label for="annee" class="form-label required">Année</label>
                    <input type="number" name="annee" id="annee" class="form-control" required min="2000" max="2100"
                        value="<?= htmlspecialchars($formData['annee']) ?>">
                    <small class="form-text">Choisissez une année qui n'est pas déjà utilisée</small>
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($formData['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="theme" class="form-label">Thème</label>
                <input type="text" name="theme" id="theme" class="form-control"
                    value="<?= htmlspecialchars($formData['theme']) ?>" placeholder="Ex: L'innovation digitale">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">Période des candidatures</label>
                    <div class="date-picker-group">
                        <input type="datetime-local" name="date_debut_candidatures" required
                            value="<?= htmlspecialchars($formData['date_debut_candidatures']) ?>">
                        <span class="date-separator">à</span>
                        <input type="datetime-local" name="date_fin_candidatures" required
                            value="<?= htmlspecialchars($formData['date_fin_candidatures']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label required">Période de l'édition (votes)</label>
                    <div class="date-picker-group">
                        <input type="datetime-local" name="date_debut" required
                            value="<?= htmlspecialchars($formData['date_debut']) ?>">
                        <span class="date-separator">à</span>
                        <input type="datetime-local" name="date_fin" required
                            value="<?= htmlspecialchars($formData['date_fin']) ?>">
                    </div>
                    <small class="form-text">L'édition sera active automatiquement pendant cette période</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Bannière de l'édition (optionnel)</label>
                <div class="file-upload">
                    <input type="file" name="image" id="image" accept="image/*">
                    <div class="file-upload-content">
                        <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                        <div class="file-upload-text">Cliquez ou glissez-déposez une image</div>
                        <div class="file-upload-hint">JPG, PNG, GIF – max 5MB</div>
                    </div>
                </div>
                <div id="filePreview" class="file-preview"></div>
            </div>

            <div class="form-actions">
                <a href="gerer-editions.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Créer l'édition
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../../../assets/js/admin-add-edition.js"></script>
<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();

require_once __DIR__ . '/../../../app/Controllers/AdminController.php';

$controller = new App\Controllers\AdminController();

$error = '';
$formData = [
    'annee' => $_POST['annee'] ?? date('Y'),
    'nom' => $_POST['nom'] ?? '',
    'description' => $_POST['description'] ?? '',
    'theme' => $_POST['theme'] ?? '',
    'date_debut_candidatures' => $_POST['date_debut_candidatures'] ?? '',
    'date_fin_candidatures' => $_POST['date_fin_candidatures'] ?? '',
    'date_debut' => $_POST['date_debut'] ?? '',
    'date_fin' => $_POST['date_fin'] ?? '',
    'est_active' => $_POST['est_active'] ?? '0'
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
        'date_fin' => $_POST['date_fin'] ?? '',
        'est_active' => isset($_POST['est_active']) ? 1 : 0
    ];

    $validationErrors = [];

    if ($data['annee'] < 2000 || $data['annee'] > 2100) {
        $validationErrors[] = "L'année doit être entre 2000 et 2100.";
    }
    if (empty($data['nom'])) {
        $validationErrors[] = "Le nom de l'édition est requis.";
    }
    if (empty($data['date_debut_candidatures']) || empty($data['date_fin_candidatures']) ||
        empty($data['date_debut']) || empty($data['date_fin'])) {
        $validationErrors[] = "Toutes les dates sont obligatoires.";
    } else {
        if (strtotime($data['date_debut_candidatures']) >= strtotime($data['date_fin_candidatures']) ||
            strtotime($data['date_fin_candidatures']) >= strtotime($data['date_debut']) ||
            strtotime($data['date_debut']) >= strtotime($data['date_fin'])) {
            $validationErrors[] = "Les dates doivent être dans l'ordre logique.";
        }
    }

    if (empty($validationErrors)) {
        $image = $_FILES['image'] ?? null;
        if ($controller->createEdition($data, $image)) {
            header("Location: gerer-editions.php?success=1");
            exit;
        } else {
            $error = "Erreur lors de la création de l'édition.";
        }
    } else {
        $error = implode("<br>", $validationErrors);
    }
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
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="edition-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="nom" class="form-label required">Nom de l'édition</label>
                    <input type="text" name="nom" id="nom" class="form-control" required maxlength="100"
                           value="<?= htmlspecialchars($formData['nom']) ?>">
                </div>
                <div class="form-group">
                    <label for="annee" class="form-label required">Année</label>
                    <input type="number" name="annee" id="annee" class="form-control" required min="2000" max="2100"
                           value="<?= htmlspecialchars($formData['annee']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="4"><?= htmlspecialchars($formData['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="theme" class="form-label">Thème</label>
                <input type="text" name="theme" id="theme" class="form-control"
                       value="<?= htmlspecialchars($formData['theme']) ?>">
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
                </div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="est_active" value="1" <?= $formData['est_active'] ? 'checked' : '' ?>>
                    Édition active (en cours)
                </label>
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
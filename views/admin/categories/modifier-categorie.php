<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: gerer-categories.php");
    exit;
}

require_once __DIR__ . '/../../../app/Controllers/AdminController.php';

$controller = new App\Controllers\AdminController();
$category = $controller->getCategoryById($id);
$editions = $controller->getEditionsList();

if (!$category) {
    header("Location: gerer-categories.php");
    exit;
}

$error = '';
$success = '';
$formData = [
    'nom' => $_POST['nom'] ?? $category['nom'],
    'description' => $_POST['description'] ?? $category['description'],
    'plateforme_cible' => $_POST['plateforme_cible'] ?? $category['plateforme_cible'],
    'date_debut_votes' => $_POST['date_debut_votes'] ?? $category['date_debut_votes'],
    'date_fin_votes' => $_POST['date_fin_votes'] ?? $category['date_fin_votes'],
    'id_edition' => $_POST['id_edition'] ?? $category['id_edition'],
    'limite_nomines' => $_POST['limite_nomines'] ?? $category['limite_nomines']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => trim($_POST['nom'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'plateforme_cible' => $_POST['plateforme_cible'] ?? 'Toutes',
        'date_debut_votes' => !empty($_POST['date_debut_votes']) ? $_POST['date_debut_votes'] : null,
        'date_fin_votes' => !empty($_POST['date_fin_votes']) ? $_POST['date_fin_votes'] : null,
        'id_edition' => (int)($_POST['id_edition'] ?? 0),
        'limite_nomines' => (int)($_POST['limite_nomines'] ?? 10)
    ];

    // Validation
    $validationErrors = [];
    
    if (empty($data['nom'])) {
        $validationErrors[] = "Le nom de la catégorie est requis.";
    } elseif (strlen($data['nom']) > 100) {
        $validationErrors[] = "Le nom ne doit pas dépasser 100 caractères.";
    }
    
    if (empty($data['description'])) {
        $validationErrors[] = "La description est requise.";
    } elseif (strlen($data['description']) > 2000) {
        $validationErrors[] = "La description ne doit pas dépasser 2000 caractères.";
    }
    
    if ($data['id_edition'] <= 0) {
        $validationErrors[] = "Veuillez sélectionner une édition.";
    }
    
    if (!empty($data['date_debut_votes']) && !empty($data['date_fin_votes'])) {
        $debut = strtotime($data['date_debut_votes']);
        $fin = strtotime($data['date_fin_votes']);
        
        if ($debut >= $fin) {
            $validationErrors[] = "La date de début doit être avant la date de fin.";
        }
    }
    
    if (empty($validationErrors)) {
        $image = $_FILES['image'] ?? null;
        
        if ($controller->updateCategory($id, $data, $image)) {
            header("Location: gerer-categories.php?success=1");
            exit;
        } else {
            $error = "Une erreur est survenue lors de la mise à jour de la catégorie.";
        }
    } else {
        $error = implode("<br>", $validationErrors);
    }
}

require_once __DIR__ . '/../../../views/partials/admin-header.php';
?>

<link rel="stylesheet" href="../../../assets/css/admin-add-categorie.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="admin-main-content admin-category-form-page">
    <div class="admin-page-header">
        <div class="page-title">
            <h1><i class="fas fa-edit"></i> Modifier la catégorie</h1>
            <p>Mettez à jour les informations de "<?= htmlspecialchars($category['nom']) ?>"</p>
        </div>
        <div class="header-actions">
            <a href="gerer-categories.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            <button id="deleteBtn" 
                    class="btn btn-danger"
                    data-category-id="<?= $id ?>"
                    data-category-name="<?= htmlspecialchars($category['nom']) ?>">
                <i class="fas fa-trash"></i> Supprimer
            </button>
        </div>
    </div>

    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Current Image Preview -->
        <?php if ($category['image']): ?>
        <div class="current-image-section" style="margin-bottom: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: var(--border-radius);">
            <h3 style="margin-bottom: 1rem; color: var(--dark-color); font-size: 1.1rem;">
                <i class="fas fa-image"></i> Image actuelle
            </h3>
            <div id="currentImageContainer" style="text-align: center;">
                <img src="../../../public/<?= htmlspecialchars($category['image']) ?>" 
                     alt="<?= htmlspecialchars($category['nom']) ?>" 
                     style="max-width: 100%; max-height: 300px; border-radius: var(--border-radius); border: 2px solid #e2e8f0;">
                <div style="margin-top: 0.5rem; color: #64748b; font-size: 0.875rem;">
                    Chemin: <?= htmlspecialchars($category['image']) ?>
                </div>
                <div style="margin-top: 0.25rem;">
                    <label style="display: inline-flex; align-items: center; gap: 0.5rem; color: #64748b; font-size: 0.875rem; cursor: pointer;">
                        <input type="checkbox" name="remove_image" value="1">
                        <span>Supprimer cette image</span>
                    </label>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="categoryForm" class="category-form">
            <div class="form-group">
                <label for="nom" class="form-label required">
                    <i class="fas fa-heading"></i> Nom de la catégorie
                </label>
                <input type="text" 
                       id="nom" 
                       name="nom" 
                       class="form-control" 
                       required 
                       value="<?= htmlspecialchars($formData['nom']) ?>"
                       placeholder="Ex: Meilleur Créateur de Contenu"
                       maxlength="100">
                <small class="char-counter"><?= strlen($formData['nom']) ?> / 100 caractères</small>
            </div>

            <div class="form-group">
                <label for="description" class="form-label required">
                    <i class="fas fa-align-left"></i> Description
                </label>
                <textarea id="description" 
                          name="description" 
                          class="form-control" 
                          required 
                          rows="5"
                          placeholder="Décrivez cette catégorie en détail..."
                          maxlength="2000"><?= htmlspecialchars($formData['description']) ?></textarea>
                <div id="charCounter" class="char-counter"><?= strlen($formData['description']) ?> / 2000 caractères</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="id_edition" class="form-label required">
                        <i class="fas fa-calendar-star"></i> Édition
                    </label>
                    <select id="id_edition" name="id_edition" class="form-control" required>
                        <option value="">Sélectionner une édition</option>
                        <?php foreach ($editions as $e): ?>
                            <option value="<?= $e['id_edition'] ?>" 
                                <?= ($formData['id_edition'] == $e['id_edition']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['nom']) ?> (<?= $e['annee'] ?? '' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-mobile-screen"></i> Plateforme cible
                    </label>
                    <input type="hidden" id="plateforme_cible" name="plateforme_cible" value="<?= htmlspecialchars($formData['plateforme_cible']) ?>">
                    
                    <div class="platform-options">
                        <div class="platform-option" data-value="Toutes">
                            <i class="fas fa-globe platform-icon"></i>
                            <span>Toutes</span>
                        </div>
                        <div class="platform-option" data-value="TikTok">
                            <i class="fab fa-tiktok platform-icon platform-tiktok"></i>
                            <span>TikTok</span>
                        </div>
                        <div class="platform-option" data-value="Instagram">
                            <i class="fab fa-instagram platform-icon platform-instagram"></i>
                            <span>Instagram</span>
                        </div>
                        <div class="platform-option" data-value="YouTube">
                            <i class="fab fa-youtube platform-icon platform-youtube"></i>
                            <span>YouTube</span>
                        </div>
                        <div class="platform-option" data-value="Facebook">
                            <i class="fab fa-facebook platform-icon platform-facebook"></i>
                            <span>Facebook</span>
                        </div>
                        <div class="platform-option" data-value="X">
                            <i class="fab fa-x-twitter platform-icon platform-x"></i>
                            <span>X (Twitter)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="limite_nomines" class="form-label">
                        <i class="fas fa-users"></i> Limite de nominés
                    </label>
                    <input type="number" 
                           id="limite_nomines" 
                           name="limite_nomines" 
                           class="form-control" 
                           min="1" 
                           max="50" 
                           value="<?= htmlspecialchars($formData['limite_nomines']) ?>"
                           placeholder="Nombre maximum de nominés">
                    <small>Entre 1 et 50 nominés maximum</small>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-calendar-days"></i> Période de votes
                    </label>
                    <div class="date-picker-group">
                        <input type="datetime-local" 
                               id="date_debut_votes" 
                               name="date_debut_votes" 
                               class="form-control" 
                               value="<?= !empty($formData['date_debut_votes']) ? str_replace(' ', 'T', $formData['date_debut_votes']) : '' ?>">
                        <span class="date-separator">à</span>
                        <input type="datetime-local" 
                               id="date_fin_votes" 
                               name="date_fin_votes" 
                               class="form-control" 
                               value="<?= !empty($formData['date_fin_votes']) ? str_replace(' ', 'T', $formData['date_fin_votes']) : '' ?>">
                    </div>
                    <small>Optionnel - Laisser vide si pas de période spécifique</small>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-image"></i> Nouvelle image (optionnel)
                </label>
                <div class="file-upload">
                    <input type="file" 
                           id="image" 
                           name="image" 
                           accept="image/*">
                    <div class="file-upload-content">
                        <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                        <div class="file-upload-text">Cliquez ou glissez-déposez une nouvelle image</div>
                        <div class="file-upload-hint">JPG, PNG ou GIF (max. 2MB)</div>
                    </div>
                </div>
                <div id="filePreview" class="file-preview">
                    <img id="previewImage" src="" alt="Aperçu de la nouvelle image">
                </div>
                <small>Laisser vide pour conserver l'image actuelle</small>
            </div>

            <div class="form-actions">
                <a href="gerer-categories.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
                <button type="submit" id="submitBtn" class="btn btn-primary">
                    <i class="fas fa-save"></i> Sauvegarder les modifications
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../../../assets/js/admin-categorie-edit.js"></script>
<script>
// Set initial platform selection
document.addEventListener('DOMContentLoaded', function() {
    const platformValue = "<?= htmlspecialchars($formData['plateforme_cible']) ?>";
    document.querySelectorAll('.platform-option').forEach(option => {
        if (option.getAttribute('data-value') === platformValue) {
            option.classList.add('selected');
        }
    });
});
</script>
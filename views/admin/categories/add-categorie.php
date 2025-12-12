<?php
require_once __DIR__ . '/../../partials/admin-header.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une Catégorie - Admin Panel</title>
    <link rel="stylesheet" href="../../../assets/css/admin-add-categorie.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="admin-container">
        <main class="admin-main">
            <div class="admin-page-header">
                <div class="page-title">
                    <h1><i class="fas fa-plus-circle"></i> Créer une Catégorie</h1>
                    <nav class="breadcrumb">
                        <a href="/Social-Media-Awards-/views/admin/dashboard.php">Tableau de bord</a> &gt;
                        <a href="/Social-Media-Awards-/views/admin/categories/manage-categories.php">Catégories</a> &gt;
                        <span>Créer</span>
                    </nav>
                </div>
            </div>

            <div class="admin-content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-info-circle"></i> Informations de la Catégorie</h2>
                        <p class="card-subtitle">Les champs marqués d'un * sont obligatoires</p>
                    </div>

                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="category-form" id="createCategoryForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nom">
                                        <i class="fas fa-tag"></i> Nom de la Catégorie *
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" id="nom" name="nom" required
                                        value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>"
                                        placeholder="ex: Créateur Révélation de l'Année">
                                    <small>Nom unique qui apparaîtra sur le site (max. 100 caractères)</small>
                                </div>

                                <div class="form-group">
                                    <label for="id_edition">
                                        <i class="fas fa-calendar-alt"></i> Édition *
                                        <span class="required">*</span>
                                    </label>
                                    <select id="id_edition" name="id_edition" required>
                                        <option value="">Sélectionner une édition</option>
                                        <?php foreach ($editions as $edition): ?>
                                            <option value="<?php echo $edition['id_edition']; ?>"
                                                <?php echo (isset($_POST['id_edition']) && $_POST['id_edition'] == $edition['id_edition']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($edition['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small>Édition à laquelle cette catégorie appartient</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <textarea id="description" name="description" rows="4"
                                    placeholder="Décrivez cette catégorie, ses critères d'éligibilité, etc..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                <small>Description détaillée qui sera visible par les utilisateurs</small>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="plateforme_cible">
                                        <i class="fas fa-globe"></i> Plateforme Cible
                                    </label>
                                    <select id="plateforme_cible" name="plateforme_cible">
                                        <option value="Toutes">Toutes les plateformes</option>
                                        <option value="TikTok" <?php echo (isset($_POST['plateforme_cible']) && $_POST['plateforme_cible'] == 'TikTok') ? 'selected' : ''; ?>>TikTok</option>
                                        <option value="Instagram" <?php echo (isset($_POST['plateforme_cible']) && $_POST['plateforme_cible'] == 'Instagram') ? 'selected' : ''; ?>>Instagram</option>
                                        <option value="YouTube" <?php echo (isset($_POST['plateforme_cible']) && $_POST['plateforme_cible'] == 'YouTube') ? 'selected' : ''; ?>>YouTube</option>
                                        <option value="X" <?php echo (isset($_POST['plateforme_cible']) && $_POST['plateforme_cible'] == 'X') ? 'selected' : ''; ?>>X (Twitter)</option>
                                        <option value="Facebook" <?php echo (isset($_POST['plateforme_cible']) && $_POST['plateforme_cible'] == 'Facebook') ? 'selected' : ''; ?>>Facebook</option>
                                        <option value="Autre" <?php echo (isset($_POST['plateforme_cible']) && $_POST['plateforme_cible'] == 'Autre') ? 'selected' : ''; ?>>Autre</option>
                                    </select>
                                    <small>Plateforme principale concernée par cette catégorie</small>
                                </div>

                                <div class="form-group">
                                    <label for="image">
                                        <i class="fas fa-image"></i> Image de la Catégorie
                                    </label>
                                    <div class="file-upload">
                                        <input type="file" id="image" name="image" accept="image/*">
                                        <label for="image" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Choisir une image</span>
                                        </label>
                                    </div>
                                    <small>Format: JPG, PNG, GIF | Max: 2MB | Taille recommandée: 400x300px</small>
                                    <div id="image-preview"></div>
                                </div>
                            </div>

                            <!-- NOVO: Campo para limite de nomeados -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="limite_nomines">
                                        <i class="fas fa-users"></i> Limite de Nommés
                                        <span class="required">*</span>
                                    </label>
                                    <div class="number-input-container">
                                        <input type="number" id="limite_nomines" name="limite_nomines" 
                                               min="1" max="50" step="1" required
                                               value="<?php echo isset($_POST['limite_nomines']) ? htmlspecialchars($_POST['limite_nomines']) : '10'; ?>"
                                               placeholder="Nombre maximum de nommés">
                                        <div class="number-input-buttons">
                                            <button type="button" class="number-btn minus" onclick="decrementValue('limite_nomines')">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <button type="button" class="number-btn plus" onclick="incrementValue('limite_nomines')">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Créer la Catégorie
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Réinitialiser
                                </button>
                                <a href="categories_manage.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Aide et conseils -->
                <div class="card card-help">
                    <div class="card-header">
                        <h3><i class="fas fa-lightbulb"></i> Conseils pour créer une catégorie</h3>
                    </div>
                    <div class="card-body">
                        <ul class="tips-list">
                            <li><strong>Nom clair :</strong> Utilisez un nom descriptif qui sera compris par tous les utilisateurs</li>
                            <li><strong>Description complète :</strong> Expliquez les critères d'éligibilité et ce qui sera évalué</li>
                            <li><strong>Plateforme précise :</strong> Si la catégorie est spécifique à une plateforme, sélectionnez-la</li>
                            <li><strong>Image représentative :</strong> Choisissez une image qui représente bien la catégorie</li>
                            <li><strong>Limite de nommés :</strong> Définissez un nombre réaliste en fonction de la popularité de la catégorie</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../../assets/js/admin-add-categorie.js"></script>

</body>

</html>
<?php
// add-edition.php
require_once __DIR__ . '/../../partials/admin-header.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Édition - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/admin-editions.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="admin-page-header">
        <div class="page-title">
            <h1><i class="fas fa-plus-circle"></i> Créer une Nouvelle Édition</h1>
            <nav class="breadcrumb">
                <a href="dashboard.php">Tableau de bord</a> &gt;
                <a href="manage-editions.php">Éditions</a> &gt;
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
                <h2><i class="fas fa-info-circle"></i> Informations de l'Édition</h2>
                <p class="card-subtitle">Les champs marqués d'un * sont obligatoires</p>
            </div>

            <div class="card-body">
                <form method="POST" action="process-edition.php" class="edition-form" id="createEditionForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">
                                <i class="fas fa-signature"></i> Nom de l'Édition *
                                <span class="required">*</span>
                            </label>
                            <input type="text" id="nom" name="nom" required
                                value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>"
                                placeholder="ex: Social Media Awards 2024">
                            <small>Nom unique qui identifiera cette édition</small>
                        </div>

                        <div class="form-group">
                            <label for="annee">
                                <i class="fas fa-calendar"></i> Année *
                                <span class="required">*</span>
                            </label>
                            <div class="number-input-container">
                                <input type="number" id="annee" name="annee"
                                    min="2020" max="2030" step="1" required
                                    value="<?php echo isset($_POST['annee']) ? htmlspecialchars($_POST['annee']) : date('Y'); ?>"
                                    placeholder="Année de l'édition">
                                <div class="number-input-buttons">
                                    <button type="button" class="number-btn minus" onclick="decrementValue('annee')">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button type="button" class="number-btn plus" onclick="incrementValue('annee')">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <small>Année de référence pour cette édition</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left"></i> Description
                        </label>
                        <textarea id="description" name="description" rows="4"
                            placeholder="Décrivez cette édition, ses objectifs, thème spécial, etc..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <small>Description qui sera visible par les utilisateurs</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_debut">
                                <i class="fas fa-play"></i> Date de début *
                                <span class="required">*</span>
                            </label>
                            <input type="date" id="date_debut" name="date_debut" required
                                value="<?php echo isset($_POST['date_debut']) ? htmlspecialchars($_POST['date_debut']) : ''; ?>">
                            <small>Date à laquelle l'édition commence</small>
                        </div>

                        <div class="form-group">
                            <label for="date_fin">
                                <i class="fas fa-flag-checkered"></i> Date de fin *
                                <span class="required">*</span>
                            </label>
                            <input type="date" id="date_fin" name="date_fin" required
                                value="<?php echo isset($_POST['date_fin']) ? htmlspecialchars($_POST['date_fin']) : ''; ?>">
                            <small>Date à laquelle l'édition se termine</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_fin_votes">
                                <i class="fas fa-vote-yea"></i> Date limite des votes
                            </label>
                            <input type="date" id="date_fin_votes" name="date_fin_votes"
                                value="<?php echo isset($_POST['date_fin_votes']) ? htmlspecialchars($_POST['date_fin_votes']) : ''; ?>">
                            <small>Date limite pour les votes (optionnel)</small>
                        </div>

                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-toggle-on"></i> Statut initial
                            </label>
                            <select id="status" name="status">
                                <option value="upcoming" <?php echo (isset($_POST['status']) && $_POST['status'] == 'upcoming') ? 'selected' : 'selected'; ?>>À venir</option>
                                <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="completed" <?php echo (isset($_POST['status']) && $_POST['status'] == 'completed') ? 'selected' : ''; ?>>Terminée</option>
                            </select>
                            <small>Statut initial de l'édition</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">
                            <i class="fas fa-image"></i> Bannière de l'édition
                        </label>
                        <div class="file-upload">
                            <input type="file" id="image" name="image" accept="image/*">
                            <label for="image" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choisir une bannière</span>
                            </label>
                        </div>
                        <small>Format: JPG, PNG | Max: 5MB | Taille recommandée: 1200x400px</small>
                        <div id="image-preview"></div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Créer l'Édition
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                        <a href="manage-editions.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Aide et conseils -->
        <div class="card card-help">
            <div class="card-header">
                <h3><i class="fas fa-lightbulb"></i> Conseils pour créer une édition</h3>
            </div>
            <div class="card-body">
                <ul class="tips-list">
                    <li><strong>Nom distinctif :</strong> Utilisez un nom qui identifiera clairement l'édition</li>
                    <li><strong>Dates réalistes :</strong> Définissez des dates qui permettent suffisamment de temps pour chaque phase</li>
                    <li><strong>Description complète :</strong> Expliquez les objectifs et le thème de l'édition</li>
                    <li><strong>Statut approprié :</strong> Choisissez "À venir" pour les éditions futures, "Active" pour les éditions en cours</li>
                    <li><strong>Date limite des votes :</strong> Définissez une date qui laisse du temps pour le dépouillement</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="/Social-Media-Awards-/assets/js/admin-editions.js"></script>
    <script>
        // Fonctions pour increment/decrement
        function incrementValue(id) {
            const input = document.getElementById(id);
            input.value = parseInt(input.value) + 1;
        }

        function decrementValue(id) {
            const input = document.getElementById(id);
            const currentValue = parseInt(input.value);
            if (currentValue > parseInt(input.min)) {
                input.value = currentValue - 1;
            }
        }
    </script>
</body>

</html>
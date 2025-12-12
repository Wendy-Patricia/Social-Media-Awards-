<?php
// edit-nomination.php
require_once __DIR__ . '/../../partials/admin-header.php';

// ID da nomeação da URL
$nomination_id = $_GET['id'] ?? 0;

// Dados simulados (substituir por consulta ao banco de dados)
$nomination = [
    'id_nomination' => $nomination_id,
    'titre' => 'Meilleur Créateur Tech TikTok 2024',
    'candidat_nom' => 'TechExplained',
    'candidat_username' => '@techexplained',
    'candidat_email' => 'contact@techexplained.com',
    'candidat_phone' => '+33 6 12 34 56 78',
    'plateforme' => 'TikTok',
    'id_categorie' => 1,
    'id_edition' => 1,
    'argumentation' => 'Contenu éducatif de haute qualité expliquant les technologies complexes de manière accessible. Une pédagogie exceptionnelle avec plus de 500 vidéos publiées.',
    'image_url' => 'https://via.placeholder.com/400x300',
    'lien_contenu' => 'https://tiktok.com/@techexplained',
    'statistiques' => '1.2M abonnés, 50M vues mensuelles',
    'date_soumission' => '2024-03-15 14:30:00',
    'statut' => 'approved'
];

// Dados para os selects
$categories = [
    ['id_categorie' => 1, 'nom' => 'Créateur Tech de l\'Année'],
    ['id_categorie' => 2, 'nom' => 'Révélation Beauté'],
    ['id_categorie' => 3, 'nom' => 'Podcast Gaming'],
    ['id_categorie' => 4, 'nom' => 'Humour Web']
];

$editions = [
    ['id_edition' => 1, 'nom' => 'Social Media Awards 2024'],
    ['id_edition' => 2, 'nom' => 'Social Media Awards 2023']
];

$platforms = ['TikTok', 'Instagram', 'YouTube', 'Facebook', 'X', 'Twitch', 'LinkedIn'];
?>

<link rel="stylesheet" href="/Social-Media-Awards-/assets/css/admin-nominations.css">

        <div class="admin-page-header">
            <div class="page-title">
                <h1><i class="fas fa-edit"></i> Éditer une Nomination</h1>
                <nav class="breadcrumb">
                    <a href="dashboard.php">Tableau de bord</a> &gt;
                    <a href="manage-nominations.php">Nominations</a> &gt;
                    <span>Éditer #<?php echo $nomination_id; ?></span>
                </nav>
            </div>
            <div class="header-actions">
                <a href="manage-nominations.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
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
                    <h2><i class="fas fa-info-circle"></i> Informations de la Nomination</h2>
                    <p class="card-subtitle">ID: #<?php echo $nomination_id; ?> | Statut: 
                        <span class="status-badge <?php echo $nomination['statut'] == 'approved' ? 'status-approved' : 'status-pending'; ?>">
                            <i class="fas fa-<?php echo $nomination['statut'] == 'approved' ? 'check-circle' : 'clock'; ?>"></i>
                            <?php echo $nomination['statut'] == 'approved' ? 'Approuvée' : 'En attente'; ?>
                        </span>
                    </p>
                </div>

                <div class="card-body">
                    <form method="POST" action="update-nomination.php" class="nomination-form" id="editNominationForm" enctype="multipart/form-data">
                        <input type="hidden" name="id_nomination" value="<?php echo $nomination_id; ?>">
                        
                        <div class="form-section">
                            <h3><i class="fas fa-user"></i> Informations du Candidat</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="candidat_nom">
                                        <i class="fas fa-user-tag"></i> Nom du Candidat *
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" id="candidat_nom" name="candidat_nom" required
                                        value="<?php echo htmlspecialchars($nomination['candidat_nom']); ?>"
                                        placeholder="ex: TechExplained">
                                </div>
                                
                                <div class="form-group">
                                    <label for="candidat_username">
                                        <i class="fas fa-at"></i> Nom d'utilisateur *
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" id="candidat_username" name="candidat_username" required
                                        value="<?php echo htmlspecialchars($nomination['candidat_username']); ?>"
                                        placeholder="ex: @techexplained">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="candidat_email">
                                        <i class="fas fa-envelope"></i> Email
                                    </label>
                                    <input type="email" id="candidat_email" name="candidat_email"
                                        value="<?php echo htmlspecialchars($nomination['candidat_email'] ?? ''); ?>"
                                        placeholder="ex: contact@example.com">
                                </div>
                                
                                <div class="form-group">
                                    <label for="candidat_phone">
                                        <i class="fas fa-phone"></i> Téléphone
                                    </label>
                                    <input type="tel" id="candidat_phone" name="candidat_phone"
                                        value="<?php echo htmlspecialchars($nomination['candidat_phone'] ?? ''); ?>"
                                        placeholder="ex: +33 6 12 34 56 78">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-trophy"></i> Détails de la Nomination</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="titre">
                                        <i class="fas fa-heading"></i> Titre de la Nomination *
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" id="titre" name="titre" required
                                        value="<?php echo htmlspecialchars($nomination['titre']); ?>"
                                        placeholder="ex: Meilleur Créateur Tech TikTok 2024">
                                </div>
                                
                                <div class="form-group">
                                    <label for="id_categorie">
                                        <i class="fas fa-tags"></i> Catégorie *
                                        <span class="required">*</span>
                                    </label>
                                    <select id="id_categorie" name="id_categorie" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id_categorie']; ?>"
                                                <?php echo ($nomination['id_categorie'] == $category['id_categorie']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="id_edition">
                                        <i class="fas fa-calendar-alt"></i> Édition *
                                        <span class="required">*</span>
                                    </label>
                                    <select id="id_edition" name="id_edition" required>
                                        <option value="">Sélectionner une édition</option>
                                        <?php foreach ($editions as $edition): ?>
                                            <option value="<?php echo $edition['id_edition']; ?>"
                                                <?php echo ($nomination['id_edition'] == $edition['id_edition']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($edition['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="plateforme">
                                        <i class="fas fa-globe"></i> Plateforme *
                                        <span class="required">*</span>
                                    </label>
                                    <select id="plateforme" name="plateforme" required>
                                        <option value="">Sélectionner une plateforme</option>
                                        <?php foreach ($platforms as $platform): ?>
                                            <option value="<?php echo htmlspecialchars($platform); ?>"
                                                <?php echo ($nomination['plateforme'] == $platform) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($platform); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="argumentation">
                                    <i class="fas fa-file-alt"></i> Argumentation *
                                    <span class="required">*</span>
                                </label>
                                <textarea id="argumentation" name="argumentation" rows="5" required
                                    placeholder="Décrivez pourquoi ce candidat mérite cette nomination..."><?php echo htmlspecialchars($nomination['argumentation']); ?></textarea>
                                <small>Minimum 200 caractères. Cette description sera visible par les votants.</small>
                                <div class="char-count">
                                    <span id="charCount"><?php echo strlen($nomination['argumentation']); ?></span> / 2000 caractères
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="lien_contenu">
                                    <i class="fas fa-link"></i> Lien vers le contenu *
                                    <span class="required">*</span>
                                </label>
                                <input type="url" id="lien_contenu" name="lien_contenu" required
                                    value="<?php echo htmlspecialchars($nomination['lien_contenu']); ?>"
                                    placeholder="ex: https://tiktok.com/@techexplained">
                                <small>Lien vers le profil ou contenu principal du candidat.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="statistiques">
                                    <i class="fas fa-chart-bar"></i> Statistiques (optionnel)
                                </label>
                                <textarea id="statistiques" name="statistiques" rows="3"
                                    placeholder="Ex: 1.2M abonnés, 50M vues mensuelles, 500 vidéos..."><?php echo htmlspecialchars($nomination['statistiques'] ?? ''); ?></textarea>
                                <small>Statistiques pertinentes pour évaluer le candidat.</small>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-image"></i> Médias</h3>
                            
                            <div class="form-group">
                                <label for="image_url">
                                    <i class="fas fa-image"></i> Image du Candidat
                                </label>
                                
                                <div class="image-preview-container">
                                    <?php if (!empty($nomination['image_url'])): ?>
                                        <div class="current-image">
                                            <img src="<?php echo htmlspecialchars($nomination['image_url']); ?>" 
                                                 alt="Image actuelle">
                                            <div class="image-info">
                                                <p>Image actuelle</p>
                                                <button type="button" class="btn btn-sm btn-secondary" id="removeImageBtn">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="file-upload">
                                        <input type="file" id="image_file" name="image_file" accept="image/*">
                                        <label for="image_file" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span><?php echo empty($nomination['image_url']) ? 'Choisir une image' : 'Remplacer l\'image'; ?></span>
                                        </label>
                                        <input type="hidden" id="current_image" name="current_image" 
                                               value="<?php echo htmlspecialchars($nomination['image_url']); ?>">
                                    </div>
                                    <small>Format: JPG, PNG, GIF | Max: 2MB | Taille recommandée: 400x300px</small>
                                    <div id="newImagePreview"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3><i class="fas fa-cog"></i> Paramètres</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="statut">
                                        <i class="fas fa-toggle-on"></i> Statut
                                    </label>
                                    <select id="statut" name="statut">
                                        <option value="pending" <?php echo ($nomination['statut'] == 'pending') ? 'selected' : ''; ?>>En attente</option>
                                        <option value="approved" <?php echo ($nomination['statut'] == 'approved') ? 'selected' : ''; ?>>Approuvée</option>
                                        <option value="rejected" <?php echo ($nomination['statut'] == 'rejected') ? 'selected' : ''; ?>>Rejetée</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="ordre">
                                        <i class="fas fa-sort-numeric-down"></i> Ordre d'affichage
                                    </label>
                                    <input type="number" id="ordre" name="ordre" min="1" max="100"
                                        value="1" placeholder="Position dans la liste">
                                    <small>Détermine l'ordre d'affichage dans la catégorie (1 = premier)</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="notes_internes">
                                    <i class="fas fa-sticky-note"></i> Notes Internes
                                </label>
                                <textarea id="notes_internes" name="notes_internes" rows="3"
                                    placeholder="Notes pour usage interne de l'administration..."></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Réinitialiser
                            </button>
                            <a href="manage-nominations.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Aide et conseils -->
            <div class="card card-help">
                <div class="card-header">
                    <h3><i class="fas fa-lightbulb"></i> Conseils pour éditer une nomination</h3>
                </div>
                <div class="card-body">
                    <ul class="tips-list">
                        <li><strong>Titre clair :</strong> Un titre descriptif qui reflète l'essence de la nomination</li>
                        <li><strong>Argumentation complète :</strong> Détaillez les réalisations et mérites du candidat</li>
                        <li><strong>Statistiques pertinentes :</strong> Incluez des données quantifiables si disponibles</li>
                        <li><strong>Image de qualité :</strong> Choisissez une image qui représente bien le candidat</li>
                        <li><strong>Vérifiez les liens :</strong> Assurez-vous que les liens vers le contenu fonctionnent</li>
                    </ul>
                </div>
            </div>
        </div>


<script src="/Social-Media-Awards-/assets/js/admin-nominations.js"></script>
<script>
    // Compteur de caractères pour l'argumentation
    const argumentation = document.getElementById('argumentation');
    const charCount = document.getElementById('charCount');
    
    if (argumentation && charCount) {
        argumentation.addEventListener('input', function() {
            charCount.textContent = this.value.length;
            
            if (this.value.length < 200) {
                charCount.style.color = '#FF5A79';
            } else if (this.value.length < 1000) {
                charCount.style.color = '#FFD166';
            } else {
                charCount.style.color = '#4FBDAB';
            }
        });
        
        // Déclencheur initial
        argumentation.dispatchEvent(new Event('input'));
    }
    
    // Prévisualisation de l'image
    const imageFile = document.getElementById('image_file');
    const newImagePreview = document.getElementById('newImagePreview');
    const removeImageBtn = document.getElementById('removeImageBtn');
    const currentImageInput = document.getElementById('current_image');
    
    if (imageFile) {
        imageFile.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    newImagePreview.innerHTML = `
                        <div class="new-image-preview">
                            <img src="${e.target.result}" alt="Nouvelle image">
                            <p>${file.name} (${(file.size / 1024).toFixed(2)} KB)</p>
                        </div>
                    `;
                }
                
                reader.readAsDataURL(file);
            } else {
                newImagePreview.innerHTML = '';
            }
        });
    }
    
    // Supprimer l'image actuelle
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', function() {
            if (confirm('Supprimer l\'image actuelle ?')) {
                const currentImage = document.querySelector('.current-image');
                if (currentImage) {
                    currentImage.remove();
                }
                currentImageInput.value = '';
            }
        });
    }
</script>
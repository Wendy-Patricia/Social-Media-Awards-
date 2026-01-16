<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: /Social-Media-Awards/views/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatService;
use App\Services\EditionService;
use App\Services\CandidatureService;
use App\Services\CategoryService;
use App\Controllers\CandidatController;

$pdo = Database::getInstance()->getConnection();

$candidatService    = new CandidatService($pdo);
$editionService     = new EditionService($pdo);
$categoryService = new CategoryService($pdo);
$candidatureService = new CandidatureService($pdo);

$controller = new CandidatController(
    $candidatService,
    $categoryService,
    $editionService
);

$editId = $_GET['edit'] ?? null;
$candidatureData = [];
$editionIdFromCandidature = null;

if ($editId && is_numeric($editId)) {
    $candidatureData = $candidatureService->getCandidatureById((int)$editId);

    if (!$candidatureData || $candidatureData['id_compte'] !== $_SESSION['user_id']) {
        $_SESSION['error'] = "Candidature non trouvée ou non autorisée.";
        header('Location: mes-candidatures.php');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id_edition 
        FROM categorie 
        WHERE id_categorie = :id
    ");
    $stmt->execute([':id' => $candidatureData['id_categorie']]);
    $editionIdFromCandidature = $stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'libelle'      => trim($_POST['libelle'] ?? ''),
        'plateforme'   => $_POST['plateforme'] ?? '',
        'url_contenu'  => trim($_POST['url_contenu'] ?? ''),
        'argumentaire' => trim($_POST['argumentaire'] ?? ''),
        'id_compte'    => $_SESSION['user_id'],
        'id_categorie' => (int)($_POST['id_categorie'] ?? 0),
    ];

    // Verificação de campos obrigatórios
    $requiredFields = [
        'libelle' => "Le titre est obligatoire.",
        'plateforme' => "La plateforme est obligatoire.",
        'url_contenu' => "L'URL du contenu est obligatoire.",
        'argumentaire' => "L'argumentaire est obligatoire.",
        'id_categorie' => "La catégorie est obligatoire."
    ];
    
    foreach ($requiredFields as $field => $message) {
        if (empty($data[$field])) {
            $_SESSION['error'] = $message;
            break;
        }
    }
    
    // Verificar imagem
    $hasImage = !empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0;
    if (!$hasImage && empty($candidatureData['image'])) {
        $_SESSION['error'] = "L'image est obligatoire. Veuillez télécharger une image pour votre candidature.";
    }

    if (!isset($_SESSION['error'])) {
        // Processar imagem
        $imagePath = null;
        if ($hasImage) {
            $uploadDir = __DIR__ . '/../../public/uploads/candidatures/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $type = mime_content_type($_FILES['image']['tmp_name']);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($type, $allowedTypes)) {
                $_SESSION['error'] = "Format d'image non supporté. Utilisez JPG, PNG, GIF ou WebP.";
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $_SESSION['error'] = "L'image est trop volumineuse. Taille maximale: 5MB.";
            } else {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('cand_') . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir.$filename)) {
                    $imagePath = 'uploads/candidatures/'.$filename;
                    $data['image'] = $imagePath;
                } else {
                    $_SESSION['error'] = "Erreur lors du téléchargement de l'image.";
                }
            }
        } elseif ($editId && !empty($candidatureData['image'])) {
            // Manter imagem existente se estiver editando
            $data['image'] = $candidatureData['image'];
        }

        if (!isset($_SESSION['error'])) {
            if ($editId) {
                // Modificar candidatura existente
                $result = $candidatService->updateCandidature($editId, $data, $data['id_compte']);
            } else {
                // Criar nova candidatura
                $result = $candidatService->createCandidature($data, $data['id_compte']);
            }

            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                header('Location: mes-candidatures.php');
                exit;
            } else {
                $_SESSION['error'] = $result['error'];
            }
        }
    }
}

$activeEditions = array_filter(
    $editionService->getAllEditions(),
    fn($e) => (int)$e['est_active'] === 1
);

if (!$activeEditions) {
    $_SESSION['error'] = "Aucune édition active.";
    header('Location: candidate-dashboard.php');
    exit;
}

$categoriesByEdition = [];

foreach ($activeEditions as $ed) {
    $stmt = $pdo->prepare("
        SELECT id_categorie, nom
        FROM categorie
        WHERE id_edition = :id
        ORDER BY nom
    ");
    $stmt->execute([':id' => $ed['id_edition']]);
    $categoriesByEdition[$ed['id_edition']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get current plateforme if editing
$currentPlateforme = $candidatureData['plateforme'] ?? '';

// Obter plataformas já utilizadas nas categorias (para exibição visual)
$usedPlatformsByCategory = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT id_categorie, plateforme 
        FROM candidature 
        WHERE id_compte = :user_id 
        AND statut != 'Rejetée'
    ");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $existingCandidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($existingCandidatures as $cand) {
        if (!isset($usedPlatformsByCategory[$cand['id_categorie']])) {
            $usedPlatformsByCategory[$cand['id_categorie']] = [];
        }
        $usedPlatformsByCategory[$cand['id_categorie']][] = $cand['plateforme'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editId ? 'Modifier la Candidature' : 'Nouvelle Candidature' ?> - Social Media Awards</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/candidat.css">
    
    <style>
        .plateforme-icon {
            font-size: 1.2em;
            margin-right: 8px;
        }
        
        .tiktok { color: #000000; }
        .instagram { color: #E4405F; }
        .youtube { color: #FF0000; }
        .x { color: #000000; }
        .facebook { color: #1877F2; }
        .twitch { color: #9146FF; }
        
        .plateforme-badge[data-platform="TikTok"]:hover { border-color: #000000; }
        .plateforme-badge[data-platform="Instagram"]:hover { border-color: #E4405F; }
        .plateforme-badge[data-platform="YouTube"]:hover { border-color: #FF0000; }
        .plateforme-badge[data-platform="X"]:hover { border-color: #000000; }
        .plateforme-badge[data-platform="Facebook"]:hover { border-color: #1877F2; }
        .plateforme-badge[data-platform="Twitch"]:hover { border-color: #9146FF; }
        
        .file-upload-required {
            border-color: #dc3545 !important;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.05), rgba(220, 53, 69, 0.02)) !important;
        }
        
        .file-upload-required .file-icon {
            color: #e35d6a !important;
        }
        
        .file-upload-valid {
            border-color: #28a745 !important;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.02)) !important;
        }
        
        .file-upload-valid .file-icon {
            color: #28a745 !important;
        }
        
        .validation-error-file {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .plateforme-badge {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .plateforme-badge.active {
            border-color: #4FBDAB !important;
            background: rgba(79, 189, 171, 0.1);
        }
        
        .platform-used-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .platform-disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .platform-disabled:hover {
            border-color: #ddd !important;
        }
        
        .platform-info {
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .category-platform-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            font-size: 0.85rem;
        }
        
        .platform-badge-used {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin: 2px;
        }
        
        .platform-badge-available {
            display: inline-block;
            background: #17a2b8;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            margin: 2px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="candidate-dashboard.php">
                <i class="fas fa-trophy"></i>
                Social Media Awards
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="candidature-form-container">
            
            <!-- Form Header -->
            <div class="form-header-candidature">
                <h1>
                    <i class="fas fa-file-import"></i>
                    <?= $editId ? 'Modifier la Candidature' : 'Nouvelle Candidature' ?>
                </h1>
                <p>Remplissez soigneusement tous les champs pour soumettre votre candidature aux Social Media Awards</p>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Règle importante :</strong> Vous pouvez soumettre une candidature par plateforme dans chaque catégorie.
                    Par exemple : une candidature TikTok et une autre Instagram dans la même catégorie.
                </div>
            </div>
            
            <!-- Info Alert -->
            <div class="alert-info-candidature">
                <h4><i class="fas fa-info-circle"></i> Informations importantes</h4>
                <p>Tous les champs sont obligatoires, y compris l'image de présentation.</p>
                <ul>
                    <li>L'image doit être de bonne qualité et représentative du contenu</li>
                    <li>Formats acceptés: JPG, PNG, GIF, WebP</li>
                    <li>Taille maximale: 5MB</li>
                    <li>Le contenu doit être original et publié au cours des 12 derniers mois</li>
                    <li>L'argumentaire doit détailler pourquoi votre contenu mérite de gagner (min. 200 caractères)</li>
                </ul>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="validation-message success">
                    <i class="fas fa-check-circle"></i>
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="validation-message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Main Form Card -->
            <div class="form-card-candidature">
                <form method="post" enctype="multipart/form-data" id="candidatureForm">
                    
                    <!-- Section 1: Édition et Catégorie -->
                    <div class="form-section">
                        <h3><i class="fas fa-layer-group"></i> Sélection de l'Édition et Catégorie</h3>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label-candidature">
                                    <i class="fas fa-calendar-alt"></i> Édition
                                    <span class="required">*</span>
                                </label>
                                <select id="edition" class="form-select-candidature" onchange="loadCategories()" required>
                                    <option value="">Choisir une édition active</option>
                                    <?php foreach ($activeEditions as $e): ?>
                                        <option value="<?= $e['id_edition'] ?>" 
                                            <?= ($editionIdFromCandidature == $e['id_edition']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($e['nom']) ?>
                                            <?php if (isset($e['annee'])): ?>
                                                (<?= htmlspecialchars($e['annee']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="help-text">Sélectionnez l'édition à laquelle vous souhaitez participer</span>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="categorie" class="form-label-candidature">
                                    <i class="fas fa-tag"></i> Catégorie
                                    <span class="required">*</span>
                                </label>
                                <select name="id_categorie" id="categorie" class="form-select-candidature" 
                                        onchange="updatePlatformAvailability()" required>
                                    <option value="">Sélectionnez d'abord une édition</option>
                                </select>
                                <span class="help-text">Choisissez la catégorie qui correspond le mieux à votre contenu</span>
                                
                                <!-- Informação sobre plataformas já usadas nesta categoria -->
                                <div id="categoryPlatformInfo" class="category-platform-info" style="display: none;">
                                    <i class="fas fa-info-circle text-info"></i>
                                    <span id="platformInfoText"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 2: Informations de Base -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Informations de Base</h3>
                        
                        <div class="row g-4">
                            <div class="col-12">
                                <label for="libelle" class="form-label-candidature">
                                    <i class="fas fa-heading"></i> Titre de la Candidature
                                    <span class="required">*</span>
                                </label>
                                <input type="text" id="libelle" name="libelle" 
                                    class="form-control-candidature" 
                                    value="<?= htmlspecialchars($candidatureData['libelle'] ?? '') ?>"
                                    placeholder="Ex: Ma meilleure vidéo TikTok de l'année"
                                    required>
                                <div class="char-counter" id="libelleCounter">0/100 caractères</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 3: Plateforme et Contenu -->
                    <div class="form-section">
                        <h3><i class="fas fa-share-alt"></i> Plateforme et Contenu</h3>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label-candidature">
                                    <i class="fas fa-globe"></i> Plateforme
                                    <span class="required">*</span>
                                </label>
                                <div class="plateforme-badges" id="platformBadges">
                                    <?php 
                                    $platforms = [
                                        'TikTok' => ['icon' => 'fab fa-tiktok tiktok', 'color' => '#000000'],
                                        'Instagram' => ['icon' => 'fab fa-instagram instagram', 'color' => '#E4405F'],
                                        'YouTube' => ['icon' => 'fab fa-youtube youtube', 'color' => '#FF0000'],
                                        'X' => ['icon' => 'fab fa-x-twitter x', 'color' => '#000000'],
                                        'Facebook' => ['icon' => 'fab fa-facebook facebook', 'color' => '#1877F2'],
                                        'Twitch' => ['icon' => 'fab fa-twitch twitch', 'color' => '#9146FF']
                                    ];
                                    
                                    foreach ($platforms as $platform => $platformData): 
                                        $isCurrent = ($currentPlateforme === $platform);
                                    ?>
                                        <div class="plateforme-badge" 
                                             data-platform="<?= $platform ?>"
                                             data-color="<?= $platformData['color'] ?>"
                                             onclick="selectPlatform('<?= $platform ?>')"
                                             id="platform-badge-<?= $platform ?>"
                                             style="border-color: <?= $isCurrent ? $platformData['color'] : '#ddd' ?>;">
                                            <i class="<?= $platformData['icon'] ?>"></i>
                                            <?= $platform ?>
                                            <span class="platform-used-badge" id="used-badge-<?= $platform ?>" style="display: none;">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="plateforme" id="plateformeInput" 
                                    value="<?= htmlspecialchars($currentPlateforme) ?>" required>
                                
                                <!-- Informação sobre plataforma selecionada -->
                                <div id="platformSelectionInfo" class="platform-info" style="display: none;">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span id="platformInfoMessage"></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="url_contenu" class="form-label-candidature">
                                    <i class="fas fa-link"></i> URL du Contenu
                                    <span class="required">*</span>
                                </label>
                                <input type="url" id="url_contenu" name="url_contenu" 
                                    class="form-control-candidature" 
                                    value="<?= htmlspecialchars($candidatureData['url_contenu'] ?? '') ?>"
                                    placeholder="https://..."
                                    required>
                                <span class="help-text">Lien direct vers votre publication (vidéo, post, etc.)</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 4: Argumentaire -->
                    <div class="form-section">
                        <h3><i class="fas fa-comment-dots"></i> Argumentaire</h3>
                        
                        <div class="row g-4">
                            <div class="col-12">
                                <label for="argumentaire" class="form-label-candidature">
                                    <i class="fas fa-edit"></i> Pourquoi méritez-vous de gagner ?
                                    <span class="required">*</span>
                                </label>
                                <textarea id="argumentaire" name="argumentaire" 
                                    class="form-control-candidature form-textarea-candidature"
                                    rows="6"
                                    placeholder="Décrivez pourquoi votre contenu est exceptionnel, son impact, son originalité..."
                                    required><?= htmlspecialchars($candidatureData['argumentaire'] ?? '') ?></textarea>
                                <div class="char-counter" id="argumentaireCounter">0/2000 caractères</div>
                                <span class="help-text">Convainquez le jury en détaillant les points forts de votre contenu (min. 200 caractères)</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 5: Image (OBRIGATÓRIA) -->
                    <div class="form-section">
                        <h3><i class="fas fa-image"></i> Image de Présentation
                            <span class="required-file-indicator">
                                <i class="fas fa-exclamation-circle"></i> Obligatoire
                            </span>
                        </h3>
                        
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label-candidature">
                                    <i class="fas fa-upload"></i> Image du Contenu
                                    <span class="required">*</span>
                                </label>
                                
                                <!-- Mensagem de erro para imagem -->
                                <div class="validation-error-file" id="imageError" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span id="imageErrorMessage"></span>
                                </div>
                                
                                <!-- Área de upload -->
                                <div class="file-upload-candidature file-upload-required" id="fileUploadArea">
                                    <div class="file-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <h5>Cliquez ou glissez-déposez votre image ici</h5>
                                    <p class="text-muted">L'image est obligatoire pour soumettre la candidature</p>
                                    <p class="file-size-info">Formats acceptés: JPG, PNG, GIF, WebP (max 5MB)</p>
                                    <input type="file" name="image" id="imageInput" 
                                        class="file-input-candidature" 
                                        accept="image/*"
                                        required>
                                </div>
                                
                                <!-- Informações do arquivo -->
                                <div class="file-info-candidature" id="fileInfo" style="display: none;">
                                    <p><i class="fas fa-file-image"></i> <span id="fileName"></span> <span id="fileSize"></span></p>
                                </div>
                                
                                <!-- Preview da imagem -->
                                <div class="image-preview-container" id="imagePreviewContainer" style="display: none;">
                                    <img src="" alt="Aperçu" class="image-preview" id="imagePreview">
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                                        <i class="fas fa-trash"></i> Supprimer l'image
                                    </button>
                                </div>
                                
                                <!-- Imagem atual (se estiver editando) -->
                                <?php if (!empty($candidatureData['image'])): ?>
                                    <div class="mt-3" id="currentImageContainer">
                                        <p class="form-label-candidature">
                                            <i class="fas fa-image"></i> Image actuelle:
                                        </p>
                                        <div class="position-relative d-inline-block">
                                            <img src="/Social-Media-Awards/public/<?= htmlspecialchars($candidatureData['image']) ?>" 
                                                alt="Image actuelle" 
                                                class="image-preview"
                                                style="max-width: 200px;">
                                            <div class="mt-2">
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Image chargée
                                                </span>
                                            </div>
                                        </div>
                                        <p class="text-muted small mt-2">
                                            Cette image sera conservée. Vous pouvez la remplacer en téléchargeant une nouvelle image ci-dessus.
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions-candidature">
                        <a href="mes-candidatures.php" class="btn-cancel-candidature">
                            <i class="fas fa-arrow-left"></i> Retour aux candidatures
                        </a>
                        
                        <button type="submit" class="btn-submit-candidature" id="submitButton" disabled>
                            <i class="fas fa-paper-plane"></i>
                            <?= $editId ? 'Mettre à jour la Candidature' : 'Soumettre la Candidature' ?>
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container-fluid">
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Social Media Awards. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dados
        const categoriesByEdition = <?= json_encode($categoriesByEdition) ?>;
        const currentCategoryId = <?= $candidatureData['id_categorie'] ?? 'null' ?>;
        const usedPlatformsByCategory = <?= json_encode($usedPlatformsByCategory) ?>;
        let hasValidImage = <?= !empty($candidatureData['image']) ? 'true' : 'false' ?>;
        let currentSelectedCategory = null;
        let currentSelectedPlatform = null;
        
        // Elementos
        const imageInput = document.getElementById('imageInput');
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const imageError = document.getElementById('imageError');
        const imageErrorMessage = document.getElementById('imageErrorMessage');
        const submitButton = document.getElementById('submitButton');
        const currentImageContainer = document.getElementById('currentImageContainer');
        const categoryPlatformInfo = document.getElementById('categoryPlatformInfo');
        const platformInfoText = document.getElementById('platformInfoText');
        const platformSelectionInfo = document.getElementById('platformSelectionInfo');
        const platformInfoMessage = document.getElementById('platformInfoMessage');
        
        // Carregar categorias quando edição é selecionada
        function loadCategories() {
            const editionSelect = document.getElementById('edition');
            const categorySelect = document.getElementById('categorie');
            const editionId = editionSelect.value;
            
            categorySelect.innerHTML = '<option value="">Sélectionner une catégorie</option>';
            
            if (categoriesByEdition[editionId]) {
                categoriesByEdition[editionId].forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id_categorie;
                    option.textContent = category.nom;
                    
                    if (currentCategoryId && currentCategoryId == category.id_categorie) {
                        option.selected = true;
                        currentSelectedCategory = category.id_categorie;
                    }
                    
                    categorySelect.appendChild(option);
                });
            }
            
            // Atualizar disponibilidade de plataformas
            updatePlatformAvailability();
            checkFormValidity();
        }
        
        // Atualizar disponibilidade das plataformas com base na categoria selecionada
        function updatePlatformAvailability() {
            const categorySelect = document.getElementById('categorie');
            const categoryId = categorySelect.value;
            currentSelectedCategory = categoryId;
            
            // Esconder info anterior
            categoryPlatformInfo.style.display = 'none';
            platformSelectionInfo.style.display = 'none';
            
            // Resetar todas as plataformas
            document.querySelectorAll('.plateforme-badge').forEach(badge => {
                badge.classList.remove('platform-disabled');
                badge.style.opacity = '1';
                badge.style.cursor = 'pointer';
                
                const usedBadge = badge.querySelector('.platform-used-badge');
                if (usedBadge) {
                    usedBadge.style.display = 'none';
                }
            });
            
            if (!categoryId) {
                // Desabilitar todas as plataformas se não houver categoria selecionada
                document.querySelectorAll('.plateforme-badge').forEach(badge => {
                    badge.classList.add('platform-disabled');
                    badge.style.opacity = '0.6';
                    badge.style.cursor = 'not-allowed';
                });
                return;
            }
            
            // Verificar quais plataformas já foram usadas nesta categoria
            const usedPlatforms = usedPlatformsByCategory[categoryId] || [];
            
            if (usedPlatforms.length > 0) {
                // Mostrar info sobre plataformas usadas
                categoryPlatformInfo.style.display = 'block';
                
                const platformNames = usedPlatforms.map(p => 
                    `<span class="platform-badge-used">${p}</span>`
                ).join(', ');
                
                const availablePlatforms = ['TikTok', 'Instagram', 'YouTube', 'X', 'Facebook', 'Twitch']
                    .filter(p => !usedPlatforms.includes(p))
                    .map(p => `<span class="platform-badge-available">${p}</span>`)
                    .join(', ');
                
                platformInfoText.innerHTML = `
                    <strong>Plateformes déjà utilisées :</strong> ${platformNames}<br>
                    <strong>Plateformes disponibles :</strong> ${availablePlatforms}
                `;
                
                // Marcar plataformas já usadas
                usedPlatforms.forEach(platform => {
                    const badge = document.getElementById(`platform-badge-${platform}`);
                    if (badge) {
                        badge.classList.add('platform-disabled');
                        badge.style.opacity = '0.6';
                        badge.style.cursor = 'not-allowed';
                        
                        const usedBadge = badge.querySelector('.platform-used-badge');
                        if (usedBadge) {
                            usedBadge.style.display = 'flex';
                        }
                    }
                });
            } else {
                // Se não tem plataformas usadas, todas estão disponíveis
                platformInfoText.innerHTML = `
                    <strong>Toutes les plateformes sont disponibles pour cette catégorie.</strong>
                `;
                categoryPlatformInfo.style.display = 'block';
            }
            
            // Verificar se a plataforma atual já está selecionada e é válida
            const currentPlatform = document.getElementById('plateformeInput').value;
            if (currentPlatform && usedPlatforms.includes(currentPlatform)) {
                // Plataforma já usada, limpar seleção
                document.getElementById('plateformeInput').value = '';
                document.querySelectorAll('.plateforme-badge').forEach(badge => {
                    badge.classList.remove('active');
                });
                
                // Mostrar mensagem de erro
                showPlatformError(`Vous avez déjà une candidature pour ${currentPlatform} dans cette catégorie.`);
            } else if (currentPlatform) {
                // Plataforma válida, manter seleção
                selectPlatform(currentPlatform);
            }
            
            checkFormValidity();
        }
        
        // Selecionar plataforma
        function selectPlatform(platform) {
            const categoryId = currentSelectedCategory;
            
            if (!categoryId) {
                alert('Veuillez d\'abord sélectionner une catégorie.');
                return;
            }
            
            // Verificar se plataforma já foi usada nesta categoria
            const usedPlatforms = usedPlatformsByCategory[categoryId] || [];
            if (usedPlatforms.includes(platform)) {
                showPlatformError(`Vous avez déjà une candidature pour ${platform} dans cette catégorie.`);
                return;
            }
            
            // Limpar erros anteriores
            hidePlatformError();
            
            // Atualizar valor do campo escondido
            document.getElementById('plateformeInput').value = platform;
            currentSelectedPlatform = platform;
            
            // Atualizar visual dos badges
            document.querySelectorAll('.plateforme-badge').forEach(badge => {
                badge.classList.remove('active');
                const platformColor = badge.getAttribute('data-color');
                badge.style.borderColor = '#ddd';
                
                if (badge.getAttribute('data-platform') === platform) {
                    badge.classList.add('active');
                    badge.style.borderColor = platformColor;
                }
            });
            
            // Mostrar informação da seleção
            platformSelectionInfo.style.display = 'block';
            platformInfoMessage.innerHTML = `
                <strong>${platform}</strong> sélectionné. Cette plateforme est disponible pour cette catégorie.
            `;
            
            checkFormValidity();
        }
        
        // Mostrar erro de plataforma
        function showPlatformError(message) {
            platformSelectionInfo.style.display = 'block';
            platformSelectionInfo.className = 'platform-info';
            platformSelectionInfo.classList.add('text-danger');
            platformInfoMessage.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        }
        
        // Esconder erro de plataforma
        function hidePlatformError() {
            platformSelectionInfo.classList.remove('text-danger');
        }
        
        // Inicializar seleção de plataforma se estiver editando
        <?php if ($currentPlateforme): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Aguardar carregamento das categorias
                setTimeout(() => {
                    if (currentSelectedCategory) {
                        selectPlatform('<?= $currentPlateforme ?>');
                    }
                }, 500);
            });
        <?php endif; ?>
        
        // Contadores de caracteres
        document.getElementById('libelle').addEventListener('input', function() {
            const counter = document.getElementById('libelleCounter');
            const length = this.value.length;
            counter.textContent = `${length}/100 caractères`;
            
            if (length > 90) {
                counter.classList.add('warning');
            } else {
                counter.classList.remove('warning');
            }
            
            checkFormValidity();
        });
        
        document.getElementById('argumentaire').addEventListener('input', function() {
            const counter = document.getElementById('argumentaireCounter');
            const length = this.value.length;
            counter.textContent = `${length}/2000 caractères`;
            
            if (length > 1800) {
                counter.classList.add('warning');
            } else if (length > 1950) {
                counter.classList.add('danger');
                counter.classList.remove('warning');
            } else {
                counter.classList.remove('warning', 'danger');
            }
            
            checkFormValidity();
        });
        
        // Validar imagem
        function validateImage(file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            // Validar tipo
            if (!allowedTypes.includes(file.type)) {
                showImageError("Format d'image non supporté. Utilisez JPG, PNG, GIF ou WebP.");
                return false;
            }
            
            // Validar tamanho
            if (file.size > maxSize) {
                showImageError("L'image est trop volumineuse. Taille maximale: 5MB.");
                return false;
            }
            
            // Validar dimensões (opcional)
            const img = new Image();
            img.src = URL.createObjectURL(file);
            img.onload = function() {
                if (this.width < 100 || this.height < 100) {
                    showImageError("L'image est trop petite. Dimensions minimales: 100x100px.");
                    return false;
                }
                URL.revokeObjectURL(this.src);
            };
            
            hideImageError();
            return true;
        }
        
        function showImageError(message) {
            imageErrorMessage.textContent = message;
            imageError.style.display = 'flex';
            fileUploadArea.classList.remove('file-upload-valid');
            fileUploadArea.classList.add('file-upload-required');
            hasValidImage = false;
            checkFormValidity();
        }
        
        function hideImageError() {
            imageError.style.display = 'none';
            fileUploadArea.classList.remove('file-upload-required');
            fileUploadArea.classList.add('file-upload-valid');
            hasValidImage = true;
            checkFormValidity();
        }
        
        // Manipulação de upload de arquivo
        fileUploadArea.addEventListener('click', () => imageInput.click());
        
        imageInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const file = this.files[0];
                
                if (validateImage(file)) {
                    fileName.textContent = file.name;
                    fileSize.textContent = `(${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                    fileInfo.style.display = 'block';
                    
                    // Esconder imagem atual se estiver editando
                    if (currentImageContainer) {
                        currentImageContainer.style.display = 'none';
                    }
                    
                    // Mostrar preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreviewContainer.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
        
        // Remover imagem
        function removeImage() {
            imageInput.value = '';
            fileInfo.style.display = 'none';
            imagePreviewContainer.style.display = 'none';
            imagePreview.src = '';
            hasValidImage = false;
            
            // Mostrar imagem atual novamente se estiver editando
            if (currentImageContainer) {
                currentImageContainer.style.display = 'block';
                hasValidImage = true;
            }
            
            fileUploadArea.classList.remove('file-upload-valid');
            fileUploadArea.classList.add('file-upload-required');
            hideImageError();
            checkFormValidity();
        }
        
        // Drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            fileUploadArea.classList.add('drag-over');
        }
        
        function unhighlight() {
            fileUploadArea.classList.remove('drag-over');
        }
        
        fileUploadArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                imageInput.files = files;
                const event = new Event('change');
                imageInput.dispatchEvent(event);
            }
        }
        
        // Verificar duplicação via AJAX
        async function checkDuplicateCandidature() {
            const categoryId = document.getElementById('categorie').value;
            const platform = document.getElementById('plateformeInput').value;
            
            if (!categoryId || !platform) {
                return false;
            }
            
            try {
                const response = await fetch(`check-candidature-duplicate.php?category_id=${categoryId}&platform=${encodeURIComponent(platform)}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const result = await response.json();
                return result.has_duplicate;
            } catch (error) {
                console.error('Erreur de vérification:', error);
                return false;
            }
        }
        
        // Validar formulário completo
        async function checkFormValidity() {
            const libelle = document.getElementById('libelle').value.trim();
            const argumentaire = document.getElementById('argumentaire').value.trim();
            const platform = document.getElementById('plateformeInput').value;
            const category = document.getElementById('categorie').value;
            const url = document.getElementById('url_contenu').value.trim();
            const edition = document.getElementById('edition').value;
            
            // Verificar duplicação
            let hasDuplicate = false;
            if (category && platform) {
                hasDuplicate = await checkDuplicateCandidature();
            }
            
            // Atualizar mensagem de erro se houver duplicação
            const duplicateError = document.getElementById('duplicate-error');
            if (hasDuplicate && !duplicateError) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'duplicate-error';
                errorDiv.className = 'alert alert-warning alert-dismissible fade show';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention :</strong> Vous avez déjà une candidature dans cette catégorie pour la plateforme ${platform}.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                const form = document.getElementById('candidatureForm');
                form.insertBefore(errorDiv, form.firstChild);
            } else if (!hasDuplicate && duplicateError) {
                duplicateError.remove();
            }
            
            // Verificar se todos os campos obrigatórios estão preenchidos
            const isFormValid = libelle && 
                               argumentaire.length >= 200 && 
                               platform && 
                               category && 
                               url && 
                               edition && 
                               hasValidImage &&
                               !hasDuplicate;
            
            // Habilitar/desabilitar botão de submit
            submitButton.disabled = !isFormValid;
            
            // Atualizar aparência do botão
            if (hasDuplicate) {
                submitButton.innerHTML = `<i class="fas fa-ban me-2"></i> Déjà candidaté pour ${platform}`;
                submitButton.classList.remove('btn-primary');
                submitButton.classList.add('btn-secondary');
            } else {
                submitButton.innerHTML = editId 
                    ? '<i class="fas fa-paper-plane me-2"></i> Mettre à jour la Candidature'
                    : '<i class="fas fa-paper-plane me-2"></i> Soumettre la Candidature';
                
                if (isFormValid) {
                    submitButton.classList.remove('btn-secondary');
                    submitButton.classList.add('btn-primary');
                } else {
                    submitButton.classList.remove('btn-primary');
                    submitButton.classList.add('btn-secondary');
                }
            }
        }
        
        // Validar em tempo real
        document.querySelectorAll('#candidatureForm input, #candidatureForm select, #candidatureForm textarea').forEach(element => {
            element.addEventListener('input', checkFormValidity);
            element.addEventListener('change', checkFormValidity);
        });
        
        // Validação no envio do formulário
        document.getElementById('candidatureForm').addEventListener('submit', async function(e) {
            // Verificar imagem
            if (!hasValidImage) {
                e.preventDefault();
                showImageError("Veuillez télécharger une image valide pour votre candidature.");
                fileUploadArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            
            // Verificar argumentaire
            const argumentaire = document.getElementById('argumentaire').value.trim();
            if (argumentaire.length < 200) {
                e.preventDefault();
                alert("L'argumentaire doit contenir au moins 200 caractères.");
                document.getElementById('argumentaire').focus();
                return false;
            }
            
            // Verificar duplicação final
            const categoryId = document.getElementById('categorie').value;
            const platform = document.getElementById('plateformeInput').value;
            
            if (categoryId && platform) {
                const hasDuplicate = await checkDuplicateCandidature();
                if (hasDuplicate) {
                    e.preventDefault();
                    alert(`Vous avez déjà une candidature dans cette catégorie pour la plateforme ${platform}.`);
                    return false;
                }
            }
            
            return true;
        });
        
        // Carregar categorias se edição estiver pré-selecionada
        <?php if ($editionIdFromCandidature): ?>
            document.addEventListener('DOMContentLoaded', function() {
                loadCategories();
                // Se tiver imagem atual (modo edição), considera válido
                if (<?= !empty($candidatureData['image']) ? 'true' : 'false' ?>) {
                    checkFormValidity();
                }
            });
        <?php endif; ?>
        
        // Inicializar contadores de caracteres
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('libelle').dispatchEvent(new Event('input'));
            document.getElementById('argumentaire').dispatchEvent(new Event('input'));
            
            // Verificar validade inicial
            setTimeout(checkFormValidity, 100);
            
            // Inicializar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>
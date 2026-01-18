<?php
require_once __DIR__ . '/../../config/session.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: /Social-Media-Awards-/login.php');
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
use App\Models\Edition;

$pdo = Database::getInstance()->getConnection();

$candidatService    = new CandidatService($pdo);
$editionService     = new EditionService($pdo);
$categoryService    = new CategoryService($pdo);
$candidatureService = new CandidatureService($pdo);

$controller = new CandidatController(
    $candidatService,
    $categoryService,
    $editionService
);

// Verificar se estamos editando
$editId = $_GET['edit'] ?? null;
$candidatureData = null;
$editionIdFromCandidature = null;

if ($editId && is_numeric($editId)) {
    // Usar o método getCandidature do CandidatService
    $candidatureData = $candidatService->getCandidature((int)$editId, $_SESSION['user_id']);

    if (!$candidatureData) {
        $_SESSION['error'] = "Candidature non trouvée ou non autorisée.";
        header('Location: mes-candidatures.php');
        exit;
    }

    // Obter ID da edição da categoria
    $stmt = $pdo->prepare("
        SELECT id_edition 
        FROM categorie 
        WHERE id_categorie = :id
    ");
    $stmt->execute([':id' => $candidatureData->getIdCategorie()]);
    $editionIdFromCandidature = $stmt->fetchColumn();
}

// Processar submissão do formulário via controller
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Chamar o método do controller para processar a submissão
    // Nota: O controller deve redirecionar após o processamento
    $controller->soumettreCandidature();
    // Se chegamos aqui, significa que há erros ou estamos no modo GET
}

// Obter todas as edições ativas - CORREÇÃO: usar objetos Edition corretamente
$allEditions = $editionService->getAllEditions();
$activeEditions = [];
foreach ($allEditions as $edition) {
    if ($edition->getEstActive()) {
        $activeEditions[] = $edition;
    }
}
// Preparar categorias por edição para JavaScript
$categoriesByEdition = [];

foreach ($allEditions as $edition) {
    $stmt = $pdo->prepare("
        SELECT c.id_categorie, c.nom, c.plateforme_cible, c.description
        FROM categorie c
        WHERE c.id_edition = :id
        AND EXISTS (
            SELECT 1 FROM edition e 
            WHERE e.id_edition = c.id_edition 
            AND e.date_fin_candidatures >= NOW()
        )
        ORDER BY c.nom
    ");
    $stmt->execute([':id' => $edition->getIdEdition()]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($categories)) {
        $categoriesByEdition[$edition->getIdEdition()] = $categories;
    }
}

// Obter plataforma atual se estiver editando
$currentPlateforme = $candidatureData ? $candidatureData->getPlateforme() : '';

// Obter plataformas já utilizadas nas categorias
$usedPlatformsByCategory = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT id_categorie, plateforme 
        FROM candidature 
        WHERE id_compte = :user_id 
        AND statut != 'Rejetée'
        AND statut != 'Refusée'
        AND id_candidature != :edit_id
    ");
    
    $params = [':user_id' => $_SESSION['user_id']];
    $types = [PDO::PARAM_INT];
    
    if ($editId) {
        $params[':edit_id'] = $editId;
        $types[] = PDO::PARAM_INT;
    } else {
        // Se não estiver editando, excluir todas as candidaturas
        $stmt = $pdo->prepare("
            SELECT id_categorie, plateforme 
            FROM candidature 
            WHERE id_compte = :user_id 
            AND statut != 'Rejetée'
            AND statut != 'Refusée'
        ");
    }
    
    $stmt->execute($params);
    $existingCandidatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($existingCandidatures as $cand) {
        if (!isset($usedPlatformsByCategory[$cand['id_categorie']])) {
            $usedPlatformsByCategory[$cand['id_categorie']] = [];
        }
        if (!in_array($cand['plateforme'], $usedPlatformsByCategory[$cand['id_categorie']])) {
            $usedPlatformsByCategory[$cand['id_categorie']][] = $cand['plateforme'];
        }
    }
}

// Estabelecer edição padrão
$defaultEditionId = null;
if ($editionIdFromCandidature && isset($categoriesByEdition[$editionIdFromCandidature])) {
    $defaultEditionId = $editionIdFromCandidature;
} elseif (!empty($activeEditions)) {
    $defaultEditionId = $activeEditions[0]->getIdEdition();
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
        
        .plateforme-badge {
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
            padding: 10px 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            margin: 5px;
            background: white;
        }
        
        .plateforme-badge:hover:not(.platform-disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .plateforme-badge.active {
            border-color: #4FBDAB !important;
            background: rgba(79, 189, 171, 0.1);
        }
        
        .file-upload-required {
            border-color: #dc3545 !important;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.05), rgba(220, 53, 69, 0.02)) !important;
        }
        
        .file-upload-valid {
            border-color: #28a745 !important;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.02)) !important;
        }
        
        .validation-error-file {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            border-color: #dee2e6 !important;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .platform-info {
            font-size: 0.85rem;
            margin-top: 5px;
            padding: 10px;
            border-radius: 5px;
            background: #f8f9fa;
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
        
        .category-info {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 0.9rem;
        }
        
        .category-info h6 {
            margin-bottom: 5px;
            color: #0d6efd;
        }
        
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-section h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4FBDAB;
        }
        
        .form-control-candidature, .form-select-candidature {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control-candidature:focus, .form-select-candidature:focus {
            border-color: #4FBDAB;
            box-shadow: 0 0 0 0.2rem rgba(79, 189, 171, 0.25);
        }
        
        .form-textarea-candidature {
            min-height: 150px;
            resize: vertical;
        }
        
        .char-counter {
            text-align: right;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .char-counter.warning {
            color: #ffc107;
        }
        
        .char-counter.danger {
            color: #dc3545;
        }
        
        .required {
            color: #dc3545;
        }
        
        .help-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .file-upload-candidature {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .file-upload-candidature:hover {
            border-color: #4FBDAB;
            background: rgba(79, 189, 171, 0.05);
        }
        
        .file-icon {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .image-preview {
            max-width: 300px;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
            border: 2px solid #dee2e6;
        }
        
        .btn-submit-candidature {
            background: linear-gradient(135deg, #4FBDAB, #3A9E8D);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-submit-candidature:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 189, 171, 0.3);
        }
        
        .btn-submit-candidature:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .edition-date-info {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="candidate-dashboard.php">
                <i class="fas fa-trophy"></i>
                Social Media Awards
            </a>
            <div class="navbar-nav">
                <a class="nav-link" href="candidate-dashboard.php">
                    <i class="fas fa-home"></i> Tableau de bord
                </a>
                <a class="nav-link" href="mes-candidatures.php">
                    <i class="fas fa-list"></i> Mes candidatures
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Form Header -->
                <div class="mb-4">
                    <h1 class="h2">
                        <i class="fas fa-file-import"></i>
                        <?= $editId ? 'Modifier la Candidature' : 'Nouvelle Candidature' ?>
                    </h1>
                    <p class="text-muted">Remplissez soigneusement tous les champs pour soumettre votre candidature</p>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Info Alert -->
                <div class="alert alert-info mb-4">
                    <h5 class="alert-heading">
                        <i class="fas fa-info-circle"></i> Informations importantes
                    </h5>
                    <div class="row">
                        <div class="col-md-8">
                            <ul class="mb-0">
                                <li>Tous les champs sont obligatoires, y compris l'image</li>
                                <li>Vous pouvez soumettre une candidature par plateforme dans chaque catégorie</li>
                                <li>L'image doit être de bonne qualité (max 5MB)</li>
                                <li>L'argumentaire doit contenir au moins 200 caractères</li>
                                <li>Le contenu doit être original et publié récemment</li>
                            </ul>
                        </div>
                        <div class="col-md-4 text-end">
                            <strong>Règles :</strong>
                            <div class="small mt-2">
                                <span class="badge bg-success me-1">✓ Plateforme disponible</span>
                                <span class="badge bg-secondary me-1">✗ Déjà utilisé</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Form -->
                <div class="card shadow">
                    <div class="card-body p-4">
                        <form method="post" enctype="multipart/form-data" id="candidatureForm">
                            <?php if ($editId): ?>
                                <input type="hidden" name="id_candidature" value="<?= $editId ?>">
                            <?php endif; ?>
                            
                            <!-- Section 1: Édition et Catégorie -->
                            <div class="form-section mb-4">
                                <h3><i class="fas fa-layer-group"></i> Sélection de l'Édition et Catégorie</h3>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="edition" class="form-label fw-bold">
                                            <i class="fas fa-calendar-alt"></i> Édition
                                            <span class="required">*</span>
                                        </label>
                                        <select id="edition" class="form-select" required>
                                            <option value="">Choisir une édition</option>
                                            <?php foreach ($activeEditions as $edition): 
                                                $dateFin = $edition->getDateFinCandidatures();
                                                $dateFinFormatted = date('d/m/Y', strtotime($dateFin));
                                                $isSelected = ($defaultEditionId == $edition->getIdEdition());
                                            ?>
                                                <option value="<?= $edition->getIdEdition() ?>" 
                                                    <?= $isSelected ? 'selected' : '' ?>
                                                    data-date-fin="<?= htmlspecialchars($dateFin) ?>">
                                                    <?= htmlspecialchars($edition->getNom()) ?>
                                                    <?php if ($edition->getAnnee()): ?>
                                                        (<?= htmlspecialchars($edition->getAnnee()) ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="help-text">Sélectionnez l'édition à laquelle vous souhaitez participer</div>
                                        <?php if (!empty($activeEditions)): ?>
                                            <div class="edition-date-info">
                                                Date limite de candidature: <?= date('d/m/Y', strtotime($activeEditions[0]->getDateFinCandidatures())) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="categorie" class="form-label fw-bold">
                                            <i class="fas fa-tag"></i> Catégorie
                                            <span class="required">*</span>
                                        </label>
                                        <select name="id_categorie" id="categorie" class="form-select" required>
                                            <option value="">Choisir une catégorie</option>
                                        </select>
                                        <div class="help-text">Choisissez la catégorie qui correspond le mieux à votre contenu</div>
                                        
                                        <!-- Détails de la catégorie -->
                                        <div id="categoryDetails" class="category-info mt-2" style="display: none;">
                                            <h6>Détails de la catégorie:</h6>
                                            <div id="categoryDescription" class="small"></div>
                                            <div id="categoryPlatform" class="small mt-1"></div>
                                        </div>
                                        
                                        <!-- Informations sur les plateformes -->
                                        <div id="categoryPlatformInfo" class="category-platform-info mt-2" style="display: none;">
                                            <i class="fas fa-info-circle text-info"></i>
                                            <span id="platformInfoText" class="ms-1"></span>
                                        </div>
                                        
                                        <!-- Loading indicator -->
                                        <div id="loadingCategories" class="text-muted small mt-2" style="display: none;">
                                            <i class="fas fa-spinner fa-spin"></i> Chargement des catégories...
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 2: Informations de Base -->
                            <div class="form-section mb-4">
                                <h3><i class="fas fa-info-circle"></i> Informations de Base</h3>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="libelle" class="form-label fw-bold">
                                            <i class="fas fa-heading"></i> Titre de la Candidature
                                            <span class="required">*</span>
                                        </label>
                                        <input type="text" id="libelle" name="libelle" 
                                            class="form-control" 
                                            value="<?= $candidatureData ? htmlspecialchars($candidatureData->getLibelle()) : '' ?>"
                                            placeholder="Ex: Ma meilleure vidéo TikTok de l'année"
                                            required
                                            maxlength="255">
                                        <div class="char-counter" id="libelleCounter">0/255 caractères</div>
                                        <div class="help-text">Titre attractif qui résume votre candidature</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 3: Plateforme et Contenu -->
                            <div class="form-section mb-4">
                                <h3><i class="fas fa-share-alt"></i> Plateforme et Contenu</h3>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-globe"></i> Plateforme
                                            <span class="required">*</span>
                                        </label>
                                        <div class="mb-3" id="platformBadges">
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
                                                     id="platform-badge-<?= $platform ?>"
                                                     style="<?= $isCurrent ? 'border-color: ' . $platformData['color'] . ';' : '' ?>">
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
                                        
                                        <!-- Information sur la plateforme sélectionnée -->
                                        <div id="platformSelectionInfo" class="platform-info" style="display: none;">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span id="platformInfoMessage" class="ms-1"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="url_contenu" class="form-label fw-bold">
                                            <i class="fas fa-link"></i> URL du Contenu
                                            <span class="required">*</span>
                                        </label>
                                        <input type="url" id="url_contenu" name="url_contenu" 
                                            class="form-control" 
                                            value="<?= $candidatureData ? htmlspecialchars($candidatureData->getUrlContenu()) : '' ?>"
                                            placeholder="https://..."
                                            required>
                                        <div class="help-text">Lien direct vers votre publication (vidéo, post, etc.)</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 4: Argumentaire -->
                            <div class="form-section mb-4">
                                <h3><i class="fas fa-comment-dots"></i> Argumentaire</h3>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="argumentaire" class="form-label fw-bold">
                                            <i class="fas fa-edit"></i> Pourquoi méritez-vous de gagner ?
                                            <span class="required">*</span>
                                        </label>
                                        <textarea id="argumentaire" name="argumentaire" 
                                            class="form-control"
                                            rows="6"
                                            placeholder="Décrivez pourquoi votre contenu est exceptionnel (min. 200 caractères)..."
                                            required><?= $candidatureData ? htmlspecialchars($candidatureData->getArgumentaire()) : '' ?></textarea>
                                        <div class="char-counter" id="argumentaireCounter">0/2000 caractères</div>
                                        <div class="help-text">Convainquez le jury en détaillant les points forts de votre contenu</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 5: Image -->
                            <div class="form-section mb-4">
                                <h3>
                                    <i class="fas fa-image"></i> Image de Présentation
                                    <span class="required">*</span>
                                </h3>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <!-- Message d'erreur -->
                                        <div class="alert alert-danger" id="imageError" style="display: none;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <span id="imageErrorMessage"></span>
                                        </div>
                                        
                                        <!-- Zone de téléchargement -->
                                        <div class="file-upload-candidature" id="fileUploadArea">
                                            <div class="mb-3">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-muted"></i>
                                            </div>
                                            <h5>Cliquez ou glissez-déposez votre image ici</h5>
                                            <p class="text-muted">L'image est obligatoire pour soumettre la candidature</p>
                                            <p class="text-muted small">Formats acceptés: JPG, PNG, GIF, WebP (max 5MB)</p>
                                            <input type="file" name="image" id="imageInput" 
                                                class="d-none" 
                                                accept="image/*"
                                                <?= !$candidatureData ? 'required' : '' ?>>
                                        </div>
                                        
                                        <!-- Information du fichier -->
                                        <div class="alert alert-info" id="fileInfo" style="display: none;">
                                            <i class="fas fa-file-image"></i>
                                            <span id="fileName" class="fw-bold ms-2"></span>
                                            <span id="fileSize" class="text-muted ms-2"></span>
                                        </div>
                                        
                                        <!-- Aperçu de l'image -->
                                        <div class="text-center" id="imagePreviewContainer" style="display: none;">
                                            <img src="" alt="Aperçu" class="image-preview" id="imagePreview">
                                            <br>
                                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                                                <i class="fas fa-trash"></i> Supprimer l'image
                                            </button>
                                        </div>
                                        
                                        <!-- Image actuelle (mode édition) -->
                                        <?php if ($candidatureData && $candidatureData->getImage()): ?>
                                            <div class="alert alert-success mt-3" id="currentImageContainer">
                                                <p class="fw-bold mb-2">
                                                    <i class="fas fa-image"></i> Image actuelle:
                                                </p>
                                                <img src="/Social-Media-Awards-/public/<?= htmlspecialchars($candidatureData->getImage()) ?>" 
                                                    alt="Image actuelle" 
                                                    class="image-preview mb-2">
                                                <p class="text-muted small mb-0">
                                                    Cette image sera conservée. Vous pouvez la remplacer en téléchargeant une nouvelle image ci-dessus.
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions du formulaire -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="mes-candidatures.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </a>
                                
                                <button type="submit" class="btn btn-submit-candidature" id="submitButton" <?= $editId ? '' : 'disabled' ?>>
                                    <i class="fas fa-paper-plane"></i>
                                    <?= $editId ? 'Mettre à jour' : 'Soumettre la candidature' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="text-muted mb-0">
                &copy; <?= date('Y') ?> Social Media Awards. Tous droits réservés.
            </p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Données initiales
        const categoriesByEdition = <?= json_encode($categoriesByEdition) ?>;
        const usedPlatformsByCategory = <?= json_encode($usedPlatformsByCategory) ?>;
        const currentCategoryId = <?= $candidatureData ? $candidatureData->getIdCategorie() : 'null' ?>;
        const currentPlatform = "<?= addslashes($currentPlateforme) ?>";
        const isEditMode = <?= $editId ? 'true' : 'false' ?>;
        
        let hasValidImage = <?= ($candidatureData && $candidatureData->getImage()) ? 'true' : 'false' ?>;
        let currentSelectedCategory = null;
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Carregar categorias para a edição padrão
            const defaultEditionId = <?= $defaultEditionId ?: 'null' ?>;
            if (defaultEditionId) {
                loadCategoriesForEdition(defaultEditionId);
            }
            
            // Événements
            document.getElementById('edition').addEventListener('change', function() {
                loadCategoriesForEdition(this.value);
            });
            
            document.getElementById('categorie').addEventListener('change', function() {
                updateCategoryDetails(this.value);
                updatePlatformAvailability();
                checkFormValidity();
            });
            
            // Événements pour les badges de plateforme
            document.querySelectorAll('.plateforme-badge').forEach(badge => {
                badge.addEventListener('click', function() {
                    if (!this.classList.contains('platform-disabled')) {
                        selectPlatform(this.getAttribute('data-platform'));
                    }
                });
            });
            
            // Initialiser les compteurs de caractères
            initCharacterCounters();
            
            // Initialiser la validation d'image
            initImageValidation();
            
            // Initialiser la plateforme si en mode édition
            if (isEditMode && currentPlatform) {
                // Attendre que as categorias sejam carregadas
                setTimeout(() => {
                    selectPlatform(currentPlatform);
                }, 500);
            }
            
            // Vérifier validade initial
            setTimeout(checkFormValidity, 100);
        });
        
        function loadCategoriesForEdition(editionId) {
            const categorySelect = document.getElementById('categorie');
            categorySelect.innerHTML = '<option value="">Choisir une catégorie</option>';
            categorySelect.disabled = true;
            
            document.getElementById('categoryDetails').style.display = 'none';
            document.getElementById('categoryPlatformInfo').style.display = 'none';
            
            if (!editionId) {
                categorySelect.disabled = false;
                resetPlatformSelection();
                return;
            }
            
            // Afficher le chargement
            document.getElementById('loadingCategories').style.display = 'block';
            
            // Simuler un délai pour l'affichage
            setTimeout(() => {
                if (categoriesByEdition[editionId]) {
                    populateCategories(categoriesByEdition[editionId], editionId);
                } else {
                    categorySelect.innerHTML = '<option value="">Aucune catégorie disponible</option>';
                }
                document.getElementById('loadingCategories').style.display = 'none';
            }, 300);
        }
        
        function populateCategories(categories, editionId) {
            const categorySelect = document.getElementById('categorie');
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id_categorie;
                option.textContent = category.nom;
                option.dataset.description = category.description || '';
                option.dataset.plateformeCible = category.plateforme_cible || 'Toutes';
                option.dataset.editionId = editionId;
                
                if (currentCategoryId && currentCategoryId == category.id_categorie) {
                    option.selected = true;
                    currentSelectedCategory = category.id_categorie;
                }
                
                categorySelect.appendChild(option);
            });
            
            categorySelect.disabled = false;
            
            // Si une catégorie est présélectionnée
            if (currentCategoryId) {
                updateCategoryDetails(currentCategoryId);
                updatePlatformAvailability();
            }
            
            checkFormValidity();
        }
        
        function updateCategoryDetails(categoryId) {
            currentSelectedCategory = categoryId;
            const categoryDetails = document.getElementById('categoryDetails');
            
            if (!categoryId) {
                categoryDetails.style.display = 'none';
                return;
            }
            
            const selectedOption = document.querySelector(`#categorie option[value="${categoryId}"]`);
            if (selectedOption) {
                categoryDetails.style.display = 'block';
                
                // Description
                const description = selectedOption.dataset.description;
                document.getElementById('categoryDescription').textContent = 
                    description || 'Aucune description disponible';
                
                // Plateforme cible
                const plateformeCible = selectedOption.dataset.plateformeCible;
                document.getElementById('categoryPlatform').innerHTML = 
                    `<strong>Plateforme cible:</strong> ${plateformeCible}`;
            } else {
                categoryDetails.style.display = 'none';
            }
        }
        
        function resetPlatformSelection() {
            document.getElementById('plateformeInput').value = '';
            document.querySelectorAll('.plateforme-badge').forEach(badge => {
                badge.classList.remove('active');
                badge.style.borderColor = '#dee2e6';
            });
            document.getElementById('platformSelectionInfo').style.display = 'none';
        }
        
        function selectPlatform(platform) {
            if (!currentSelectedCategory) {
                alert('Veuillez d\'abord sélectionner une catégorie.');
                return;
            }
            
            // Vérifier si la plateforme est déjà utilisée
            const usedPlatforms = usedPlatformsByCategory[currentSelectedCategory] || [];
            if (usedPlatforms.includes(platform) && !isEditMode) {
                showPlatformError(`Vous avez déjà une candidature pour ${platform} dans cette catégorie.`);
                return;
            }
            
            // Mettre à jour l'input caché
            document.getElementById('plateformeInput').value = platform;
            
            // Mettre à jour l'interface
            document.querySelectorAll('.plateforme-badge').forEach(badge => {
                badge.classList.remove('active');
                if (badge.getAttribute('data-platform') === platform) {
                    badge.classList.add('active');
                }
            });
            
            // Afficher la confirmation
            const platformInfo = document.getElementById('platformSelectionInfo');
            platformInfo.style.display = 'block';
            platformInfo.className = 'platform-info';
            document.getElementById('platformInfoMessage').textContent = 
                `${platform} sélectionné`;
            
            checkFormValidity();
        }
        
        function updatePlatformAvailability() {
            const categoryId = document.getElementById('categorie').value;
            const platformInfo = document.getElementById('categoryPlatformInfo');
            
            // Réinitialiser toutes les plateformes
            document.querySelectorAll('.plateforme-badge').forEach(badge => {
                badge.classList.remove('platform-disabled');
                badge.style.opacity = '1';
                badge.style.cursor = 'pointer';
                
                const usedBadge = badge.querySelector('.platform-used-badge');
                if (usedBadge) usedBadge.style.display = 'none';
            });
            
            if (!categoryId) {
                document.querySelectorAll('.plateforme-badge').forEach(badge => {
                    badge.classList.add('platform-disabled');
                });
                platformInfo.style.display = 'none';
                return;
            }
            
            const usedPlatforms = usedPlatformsByCategory[categoryId] || [];
            
            if (usedPlatforms.length > 0) {
                platformInfo.style.display = 'block';
                
                const usedText = usedPlatforms.map(p => 
                    `<span class="platform-badge-used">${p}</span>`
                ).join(', ');
                
                const availablePlatforms = ['TikTok', 'Instagram', 'YouTube', 'X', 'Facebook', 'Twitch']
                    .filter(p => !usedPlatforms.includes(p));
                
                const availableText = availablePlatforms.map(p => 
                    `<span class="platform-badge-available">${p}</span>`
                ).join(', ');
                
                document.getElementById('platformInfoText').innerHTML = 
                    `<strong>Plateformes déjà utilisées :</strong> ${usedText}<br>
                     <strong>Plateformes disponibles :</strong> ${availableText}`;
                
                // Désactiver les plateformes déjà utilisées
                usedPlatforms.forEach(platform => {
                    const badge = document.getElementById(`platform-badge-${platform}`);
                    if (badge && !isEditMode) {
                        badge.classList.add('platform-disabled');
                        const usedBadge = badge.querySelector('.platform-used-badge');
                        if (usedBadge) usedBadge.style.display = 'flex';
                    }
                });
            } else {
                platformInfo.style.display = 'block';
                document.getElementById('platformInfoText').innerHTML = 
                    `<strong>Toutes les plateformes sont disponibles pour cette catégorie</strong>`;
            }
            
            checkFormValidity();
        }
        
        function showPlatformError(message) {
            const platformInfo = document.getElementById('platformSelectionInfo');
            platformInfo.style.display = 'block';
            platformInfo.className = 'platform-info bg-danger text-white';
            document.getElementById('platformInfoMessage').innerHTML = 
                `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        }
        
        function initCharacterCounters() {
            const libelleInput = document.getElementById('libelle');
            const argumentaireInput = document.getElementById('argumentaire');
            
            libelleInput.addEventListener('input', function() {
                const counter = document.getElementById('libelleCounter');
                const length = this.value.length;
                counter.textContent = `${length}/255 caractères`;
                counter.className = length > 240 ? 'char-counter warning' : 'char-counter';
                checkFormValidity();
            });
            
            argumentaireInput.addEventListener('input', function() {
                const counter = document.getElementById('argumentaireCounter');
                const length = this.value.length;
                counter.textContent = `${length}/2000 caractères`;
                
                if (length < 200) {
                    counter.className = 'char-counter danger';
                } else if (length > 1900) {
                    counter.className = 'char-counter warning';
                } else {
                    counter.className = 'char-counter';
                }
                checkFormValidity();
            });
            
            // Déclencher les événements initiaux
            libelleInput.dispatchEvent(new Event('input'));
            argumentaireInput.dispatchEvent(new Event('input'));
        }
        
        function initImageValidation() {
            const uploadArea = document.getElementById('fileUploadArea');
            const imageInput = document.getElementById('imageInput');
            
            uploadArea.addEventListener('click', () => imageInput.click());
            
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '#4FBDAB';
                uploadArea.style.backgroundColor = 'rgba(79, 189, 171, 0.05)';
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.style.borderColor = '#dee2e6';
                uploadArea.style.backgroundColor = '#f8f9fa';
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '#dee2e6';
                uploadArea.style.backgroundColor = '#f8f9fa';
                
                if (e.dataTransfer.files.length) {
                    imageInput.files = e.dataTransfer.files;
                    imageInput.dispatchEvent(new Event('change'));
                }
            });
            
            imageInput.addEventListener('change', function() {
                if (this.files.length) {
                    const file = this.files[0];
                    validateImage(file);
                }
            });
        }
        
        function validateImage(file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            const errorDiv = document.getElementById('imageError');
            const uploadArea = document.getElementById('fileUploadArea');
            
            // Réinitialiser
            errorDiv.style.display = 'none';
            uploadArea.classList.remove('file-upload-required', 'file-upload-valid');
            
            // Vérifier le type
            if (!allowedTypes.includes(file.type)) {
                showImageError('Format non supporté. Utilisez JPG, PNG, GIF ou WebP.');
                return false;
            }
            
            // Vérifier la taille
            if (file.size > maxSize) {
                showImageError('Fichier trop volumineux. Taille maximale: 5MB.');
                return false;
            }
            
            // Afficher les informations du fichier
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = 
                `(${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            document.getElementById('fileInfo').style.display = 'block';
            
            // Masquer l'image actuelle si en mode édition
            const currentImageContainer = document.getElementById('currentImageContainer');
            if (currentImageContainer) {
                currentImageContainer.style.display = 'none';
            }
            
            // Afficher l'aperçu
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
                document.getElementById('imagePreviewContainer').style.display = 'block';
            };
            reader.readAsDataURL(file);
            
            uploadArea.classList.add('file-upload-valid');
            hasValidImage = true;
            checkFormValidity();
            return true;
        }
        
        function showImageError(message) {
            document.getElementById('imageErrorMessage').textContent = message;
            document.getElementById('imageError').style.display = 'block';
            document.getElementById('fileUploadArea').classList.add('file-upload-required');
            hasValidImage = false;
            checkFormValidity();
        }
        
        function removeImage() {
            document.getElementById('imageInput').value = '';
            document.getElementById('fileInfo').style.display = 'none';
            document.getElementById('imagePreviewContainer').style.display = 'none';
            document.getElementById('fileUploadArea').classList.remove('file-upload-valid');
            
            // Réafficher l'image actuelle si en mode édition
            const currentImageContainer = document.getElementById('currentImageContainer');
            if (currentImageContainer) {
                currentImageContainer.style.display = 'block';
                hasValidImage = true;
            } else {
                hasValidImage = false;
            }
            
            checkFormValidity();
        }
        
        function checkFormValidity() {
            const libelle = document.getElementById('libelle').value.trim();
            const argumentaire = document.getElementById('argumentaire').value.trim();
            const platform = document.getElementById('plateformeInput').value;
            const category = document.getElementById('categorie').value;
            const url = document.getElementById('url_contenu').value.trim();
            const edition = document.getElementById('edition').value;
            
            // Vérifier tous les champs obligatoires
            const isFormValid = libelle && 
                               argumentaire.length >= 200 && 
                               platform && 
                               category && 
                               url && 
                               edition && 
                               hasValidImage;
            
            // Activer/désactiver le bouton de soumission
            const submitButton = document.getElementById('submitButton');
            submitButton.disabled = !isFormValid;
            
            // Atualizar aparência do botão
            if (isFormValid) {
                submitButton.classList.remove('btn-secondary');
                submitButton.classList.add('btn-primary');
            } else {
                submitButton.classList.remove('btn-primary');
                submitButton.classList.add('btn-secondary');
            }
        }
        
        // Validation à la soumission
        document.getElementById('candidatureForm').addEventListener('submit', async function(e) {
            // Vérifier l'argumentaire
            const argumentaire = document.getElementById('argumentaire').value.trim();
            if (argumentaire.length < 200) {
                e.preventDefault();
                alert("L'argumentaire doit contenir au moins 200 caractères.");
                document.getElementById('argumentaire').focus();
                return false;
            }
            
            // Vérifier l'image (sauf en mode édition avec image existante)
            if (!hasValidImage) {
                e.preventDefault();
                showImageError("Veuillez télécharger une image valide pour votre candidature.");
                return false;
            }
            
            // Vérifier si la plateforme est disponible
            const categoryId = document.getElementById('categorie').value;
            const platform = document.getElementById('plateformeInput').value;
            
            if (categoryId && platform) {
                const usedPlatforms = usedPlatformsByCategory[categoryId] || [];
                if (usedPlatforms.includes(platform) && !isEditMode) {
                    e.preventDefault();
                    alert(`Vous avez déjà une candidature dans cette catégorie pour la plateforme ${platform}.`);
                    return false;
                }
            }
            
            return true;
        });
    </script>
</body>
</html>
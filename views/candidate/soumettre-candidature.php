<?php
// views/candidate/soumettre-candidature.php
session_start();

// Verificar se o usuário está logado como candidato
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: /Social-Media-Awards-/views/login.php');
    exit;
}

// Incluir configurações
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatService;
use App\Services\CategoryService;
use App\Services\EditionService;

// Inicializar conexão
$pdo = Database::getInstance()->getConnection();

// Inicializar serviços
$candidatService = new CandidatService($pdo);
$categoryService = new CategoryService($pdo);
$editionService = new EditionService($pdo);

// Verificar se pode submeter candidaturas
$userId = $_SESSION['user_id'];
$isNominee = $candidatService->isNominee($userId);
if ($isNominee) {
    $canEdit = $candidatService->canEditProfile($userId);
    if (!$canEdit) {
        $_SESSION['error'] = "Vous ne pouvez pas soumettre de candidature pendant les votes.";
        header('Location: candidate-dashboard.php');
        exit;
    }
}

// Obter categorias disponíveis
$categories = [];
try {
    $sql = "SELECT c.*, e.nom as edition_nom 
            FROM categorie c
            JOIN edition e ON c.id_edition = e.id_edition
            WHERE e.est_active = 1
            AND e.date_fin_candidatures >= CURDATE()
            ORDER BY e.annee DESC, c.nom ASC";
    $stmt = $pdo->query($sql);
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    // Se não conseguir obter categorias, usar array vazio
    $categories = [];
}

// Processar submissão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'libelle' => trim($_POST['libelle'] ?? ''),
        'plateforme' => $_POST['plateforme'] ?? '',
        'url_contenu' => trim($_POST['url_contenu'] ?? ''),
        'argumentaire' => trim($_POST['argumentaire'] ?? ''),
        'id_categorie' => $_POST['id_categorie'] ?? 0
    ];

    // Upload da imagem
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imagePath = $candidatService->uploadImage($_FILES['image']);
        if ($imagePath) {
            $data['image'] = $imagePath;
        }
    }

    // Validação
    $errors = [];
    if (empty($data['libelle'])) $errors[] = "Le titre est obligatoire.";
    if (empty($data['url_contenu'])) $errors[] = "L'URL du contenu est obligatoire.";
    if (empty($data['argumentaire'])) $errors[] = "L'argumentaire est obligatoire.";
    if (empty($data['id_categorie'])) $errors[] = "La catégorie est obligatoire.";
    
    if (empty($errors)) {
        $success = false;
        
        if (isset($_POST['id_candidature']) && !empty($_POST['id_candidature'])) {
            // Atualização
            $success = $candidatService->updateCandidature(
                $_POST['id_candidature'],
                $data,
                $userId
            );
            $message = $success ? "Candidature mise à jour avec succès." : "Erreur lors de la mise à jour.";
        } else {
            // Criação
            $success = $candidatService->createCandidature($data, $userId);
            $message = $success ? "Candidature soumise avec succès." : "Erreur lors de la soumission.";
        }

        if ($success) {
            $_SESSION['success'] = $message;
            header('Location: mes-candidatures.php');
            exit;
        } else {
            $_SESSION['error'] = $message;
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

// Se for edição, obter dados da candidatura
$candidature = null;
if (isset($_GET['edit'])) {
    $candidature = $candidatService->getCandidature($_GET['edit'], $userId);
    if (!$candidature) {
        $_SESSION['error'] = "Candidature non trouvée.";
        header('Location: mes-candidatures.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($candidature) ? 'Modifier' : 'Soumettre' ?> une candidature - Social Media Awards</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/candidat.css">
    
    <style>
    .form-candidature .form-label.required:after {
        content: " *";
        color: #dc3545;
    }
    .img-preview {
        max-width: 200px;
        max-height: 200px;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 10px;
    }
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/Social-Media-Awards-/index.php">
                <i class="fas fa-trophy me-2"></i>Social Media Awards
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="candidate-dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i> Tableau de bord
                </a>
                <a class="nav-link" href="mes-candidatures.php">
                    <i class="fas fa-file-alt me-1"></i> Mes candidatures
                </a>
                <span class="navbar-text me-3">
                    <?= htmlspecialchars($_SESSION['user_pseudonyme'] ?? 'Candidat') ?>
                </span>
                <a class="nav-link" href="/Social-Media-Awards-/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <?php include __DIR__ . '/../partials/sidebar-candidat.php'; ?>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="col-md-9">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2">
                            <i class="fas fa-paper-plane me-2"></i>
                            <?= isset($candidature) ? 'Modifier ma candidature' : 'Soumettre une candidature' ?>
                        </h1>
                        <p class="text-muted mb-0">
                            <?= isset($candidature) ? 
                                'Modifiez votre candidature existante' : 
                                'Soumettez votre contenu pour participer aux Social Media Awards' ?>
                        </p>
                    </div>
                    <div>
                        <a href="mes-candidatures.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour aux candidatures
                        </a>
                    </div>
                </div>
                
                <!-- Alertas -->
                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); endif; ?>
                
                <!-- Formulário -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data" class="form-candidature" id="form-candidature">
                            <?php if (isset($candidature)): ?>
                            <input type="hidden" name="id_candidature" value="<?= $candidature['id_candidature'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Titre -->
                                    <div class="mb-3">
                                        <label for="libelle" class="form-label required">Titre de votre candidature</label>
                                        <input type="text" class="form-control" id="libelle" name="libelle" 
                                               value="<?= htmlspecialchars($candidature['libelle'] ?? '') ?>" 
                                               placeholder="Ex: Ma meilleure vidéo TikTok sur le climat" required>
                                        <div class="form-text">
                                            Donnez un titre clair et accrocheur à votre contenu (max 255 caractères)
                                        </div>
                                    </div>
                                    
                                    <!-- URL du contenu -->
                                    <div class="mb-3">
                                        <label for="url_contenu" class="form-label required">URL de votre contenu</label>
                                        <input type="url" class="form-control" id="url_contenu" name="url_contenu" 
                                               value="<?= htmlspecialchars($candidature['url_contenu'] ?? '') ?>" 
                                               placeholder="https://www.tiktok.com/@votrecompte/video/..." required>
                                        <div class="form-text">
                                            Lien direct vers votre contenu (vidéo, post, story, etc.)
                                        </div>
                                    </div>
                                    
                                    <!-- Plateforme -->
                                    <div class="mb-3">
                                        <label for="plateforme" class="form-label required">Plateforme</label>
                                        <select class="form-select" id="plateforme" name="plateforme" required>
                                            <option value="">Sélectionnez une plateforme</option>
                                            <option value="TikTok" <?= ($candidature['plateforme'] ?? '') == 'TikTok' ? 'selected' : '' ?>>TikTok</option>
                                            <option value="Instagram" <?= ($candidature['plateforme'] ?? '') == 'Instagram' ? 'selected' : '' ?>>Instagram</option>
                                            <option value="YouTube" <?= ($candidature['plateforme'] ?? '') == 'YouTube' ? 'selected' : '' ?>>YouTube</option>
                                            <option value="Facebook" <?= ($candidature['plateforme'] ?? '') == 'Facebook' ? 'selected' : '' ?>>Facebook</option>
                                            <option value="X" <?= ($candidature['plateforme'] ?? '') == 'X' ? 'selected' : '' ?>>X (Twitter)</option>
                                            <option value="Twitch" <?= ($candidature['plateforme'] ?? '') == 'Twitch' ? 'selected' : '' ?>>Twitch</option>
                                            <option value="Spotify" <?= ($candidature['plateforme'] ?? '') == 'Spotify' ? 'selected' : '' ?>>Spotify</option>
                                            <option value="Autre" <?= ($candidature['plateforme'] ?? '') == 'Autre' ? 'selected' : '' ?>>Autre</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Catégorie -->
                                    <div class="mb-3">
                                        <label for="id_categorie" class="form-label required">Catégorie</label>
                                        <select class="form-select" id="id_categorie" name="id_categorie" required>
                                            <option value="">Sélectionnez une catégorie</option>
                                            <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id_categorie'] ?>" 
                                                <?= ($candidature['id_categorie'] ?? '') == $category['id_categorie'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['nom']) ?> 
                                                (<?= htmlspecialchars($category['edition_nom']) ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (empty($categories)): ?>
                                        <div class="alert alert-warning mt-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Aucune catégorie n'est actuellement ouverte aux candidatures.
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Argumentaire -->
                                    <div class="mb-3">
                                        <label for="argumentaire" class="form-label required">Argumentaire</label>
                                        <textarea class="form-control" id="argumentaire" name="argumentaire" 
                                                  rows="6" required
                                                  placeholder="Expliquez pourquoi votre contenu mérite de gagner..."><?= htmlspecialchars($candidature['argumentaire'] ?? '') ?></textarea>
                                        <div class="form-text">
                                            Minimum 100 caractères. Expliquez la valeur de votre contenu, son originalité, son impact...
                                        </div>
                                        <div class="mt-2">
                                            <small>Nombre de caractères: <span id="char-count">0</span></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <!-- Image -->
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-image me-2"></i> Image de présentation
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="image" class="form-label">Image (optionnelle)</label>
                                                <input type="file" class="form-control" id="image" name="image" 
                                                       accept="image/jpeg,image/png,image/gif,image/webp">
                                                <div class="form-text">
                                                    Formats acceptés: JPG, PNG, GIF, WebP (max 2MB)
                                                </div>
                                            </div>
                                            
                                            <!-- Preview da imagem -->
                                            <div class="text-center">
                                                <?php if (isset($candidature['image']) && $candidature['image']): ?>
                                                <img src="/Social-Media-Awards-/public/<?= htmlspecialchars($candidature['image']) ?>" 
                                                     id="image-preview" class="img-preview mb-3">
                                                <div class="mb-3">
                                                    <input type="checkbox" id="remove_image" name="remove_image" value="1">
                                                    <label for="remove_image" class="form-check-label">Supprimer cette image</label>
                                                </div>
                                                <?php else: ?>
                                                <img src="" id="image-preview" class="img-preview mb-3" style="display: none;">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Informations importantes -->
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-info-circle me-2"></i> Informations importantes
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled small">
                                                <li class="mb-2">
                                                    <i class="fas fa-check-circle text-success me-1"></i>
                                                    Tous les champs marqués d'un * sont obligatoires
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-clock text-warning me-1"></i>
                                                    Le traitement prend 3-5 jours ouvrés
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-edit text-info me-1"></i>
                                                    Vous pourrez modifier tant que le statut est "En attente"
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-ban text-danger me-1"></i>
                                                    Pas de contenu haineux ou inapproprié
                                                </li>
                                                <li>
                                                    <i class="fas fa-copyright text-primary me-1"></i>
                                                    Le contenu doit être original
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botões -->
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    <?= isset($candidature) ? 'Mettre à jour' : 'Soumettre ma candidature' ?>
                                </button>
                                <a href="mes-candidatures.php" class="btn btn-outline-secondary ms-2">
                                    Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Social Media Awards</h5>
                    <p class="mb-0">Célébrons la créativité numérique ensemble.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/Social-Media-Awards-/views/candidate/reglement.php" class="text-white me-3">Règlement</a>
                    <a href="/Social-Media-Awards-/contact.php" class="text-white me-3">Contact</a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Scripts personalizados -->
    <script src="/Social-Media-Awards-/assets/js/candidat.js"></script>
    
    <script>
    // Contador de caracteres
    const textarea = document.getElementById('argumentaire');
    const charCount = document.getElementById('char-count');
    
    if (textarea && charCount) {
        // Atualizar contador
        function updateCharCount() {
            charCount.textContent = textarea.value.length;
            if (textarea.value.length < 100) {
                charCount.style.color = '#dc3545';
            } else {
                charCount.style.color = '#198754';
            }
        }
        
        // Inicializar
        updateCharCount();
        
        // Atualizar ao digitar
        textarea.addEventListener('input', updateCharCount);
    }
    
    // Preview da imagem
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Validação do formulário
    const form = document.getElementById('form-candidature');
    if (form) {
        form.addEventListener('submit', function(event) {
            const argumentaire = document.getElementById('argumentaire');
            if (argumentaire && argumentaire.value.length < 100) {
                event.preventDefault();
                alert('L\'argumentaire doit faire au moins 100 caractères.');
                argumentaire.focus();
                return false;
            }
        });
    }
    </script>
</body>
</html>
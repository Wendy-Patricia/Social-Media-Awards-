<?php
// views/user/edit-profile.php - VERSION AVEC TÉLÉCHARGEMENT DE PHOTOS
// Page d'édition du profil utilisateur pour les électeurs
// Permet de modifier les informations personnelles et la photo de profil

// Inclure la gestion des sessions et le modèle utilisateur
require_once '../../config/session.php';
require_once '../../app/Models/UserModel.php';

// Vérifier l'authentification et le rôle
requireRole('voter');

// Initialiser les variables et récupérer les données utilisateur
$userId = $_SESSION['user_id'];
$userModel = new User();

// Récupérer les données de l'utilisateur depuis la base de données
$userData = $userModel->getUserById($userId);

// Fallback aux données de session si l'utilisateur n'est pas trouvé en base
if (!$userData) {
    $userData = [
        'id_compte' => $userId,
        'pseudonyme' => $_SESSION['user_pseudonyme'],
        'email' => $_SESSION['user_email'],
        'date_naissance' => '1990-01-01',
        'pays' => 'France',
        'genre' => '',
        'photo_profil' => null,
        'role' => 'voter'
    ];
}

// Initialiser les messages
$errors = [];
$success = '';

// Définir le répertoire de téléchargement des photos
$upload_dir = 'C:/wamp64/www/Social-Media-Awards-/assets/images/profiles/';
$web_path = '/Social-Media-Awards-/assets/images/profiles/';

// Créer le répertoire s'il n'existe pas
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        $errors['general'] = 'Erreur de configuration: Impossible de créer le dossier de téléchargement';
    }
}

// Traiter le formulaire lorsqu'il est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données du formulaire
    $pseudonyme = trim($_POST['pseudonyme'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $pays = $_POST['pays'] ?? '';
    $genre = $_POST['genre'] ?? '';
    
    // Validation du pseudonyme
    if (empty($pseudonyme)) {
        $errors['pseudonyme'] = 'Le pseudonyme est requis';
    } elseif (strlen($pseudonyme) < 3) {
        $errors['pseudonyme'] = 'Le pseudonyme doit contenir au moins 3 caractères';
    } elseif ($userModel->isPseudonymeTaken($pseudonyme, $userId)) {
        $errors['pseudonyme'] = 'Ce pseudonyme est déjà utilisé';
    }
    
    // Validation de l'email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email invalide';
    } elseif ($userModel->isEmailTaken($email, $userId)) {
        $errors['email'] = 'Cet email est déjà utilisé';
    }
    
    // Validation de la date de naissance
    if (empty($date_naissance)) {
        $errors['date_naissance'] = 'La date de naissance est requise';
    } elseif (strtotime($date_naissance) > strtotime('-13 years')) {
        $errors['date_naissance'] = 'Vous devez avoir au moins 13 ans';
    }
    
    // Validation du pays
    if (empty($pays)) {
        $errors['pays'] = 'Le pays est requis';
    }
    
    // Gestion du téléchargement de la photo de profil
    $photo_profil = $userData['photo_profil'] ?? null;
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        // Validation du type de fichier
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['photo_profil']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            // Vérification de la taille (max 5MB)
            if ($_FILES['photo_profil']['size'] <= 5 * 1024 * 1024) {
                // Générer un nom unique pour le fichier
                $file_extension = strtolower(pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION));
                $new_filename = 'profile_' . $userId . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                // Validation de l'extension réelle
                $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($file_extension, $valid_extensions)) {
                    $errors['photo_profil'] = 'Extension de fichier non autorisée (JPG, PNG, GIF seulement)';
                } else {
                    // Déplacer le fichier vers le répertoire
                    if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $upload_path)) {
                        // Supprimer l'ancienne photo si elle existe
                        if ($userData['photo_profil'] && file_exists($upload_dir . $userData['photo_profil'])) {
                            unlink($upload_dir . $userData['photo_profil']);
                        }
                        
                        $photo_profil = $new_filename;
                    } else {
                        $errors['photo_profil'] = 'Erreur lors du téléchargement de l\'image';
                    }
                }
            } else {
                $errors['photo_profil'] = 'L\'image est trop volumineuse (max 5MB)';
            }
        } else {
            $errors['photo_profil'] = 'Type de fichier non autorisé (JPEG, PNG, GIF seulement)';
        }
    } elseif ($_FILES['photo_profil']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Gestion des erreurs de téléchargement
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par le serveur',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire',
            UPLOAD_ERR_PARTIAL => 'Le téléchargement a été interrompu',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture sur le disque',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté le téléchargement'
        ];
        
        $error_code = $_FILES['photo_profil']['error'];
        $errors['photo_profil'] = $upload_errors[$error_code] ?? 'Erreur inconnue lors du téléchargement';
    }
    
    // Si aucune erreur, mettre à jour les données
    if (empty($errors)) {
        $updateData = [
            'pseudonyme' => $pseudonyme,
            'email' => $email,
            'date_naissance' => $date_naissance,
            'pays' => $pays,
            'genre' => $genre
        ];
        
        // Ajouter la photo de profil si elle a été téléchargée
        if ($photo_profil) {
            $updateData['photo_profil'] = $photo_profil;
        }
        
        // Mettre à jour le profil dans la base de données
        $result = $userModel->updateUserProfile($userId, $updateData);
        
        if ($result) {
            // Mettre à jour les données de session
            $_SESSION['user_pseudonyme'] = $pseudonyme;
            $_SESSION['user_email'] = $email;
            
            // Mettre à jour les données locales pour l'affichage
            $userData['pseudonyme'] = $pseudonyme;
            $userData['email'] = $email;
            $userData['date_naissance'] = $date_naissance;
            $userData['pays'] = $pays;
            $userData['genre'] = $genre;
            $userData['photo_profil'] = $photo_profil;
            
            $success = 'Profil mis à jour avec succès!';
        } else {
            $errors['general'] = 'Une erreur est survenue lors de la mise à jour du profil';
        }
    }
}

// Générer les initiales pour l'avatar
$initials = strtoupper(substr($userData['pseudonyme'], 0, 2));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Profil - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/edit-profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Styles spécifiques pour la gestion des avatars et photos */
        .avatar-large.has-photo {
            background: none !important;
            border: none;
            position: relative;
        }
        
        .avatar-large.has-photo .avatar-initials {
            display: none;
        }
        
        .avatar-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--principal);
            display: block;
        }
        
        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--principal);
        }
        
        /* Styles pour l'upload de fichiers */
        .file-upload {
            position: relative;
            border: 2px dashed var(--gray-light);
            border-radius: var(--radius-md);
            padding: var(--space-lg);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--light);
        }
        
        .file-upload:hover {
            border-color: var(--principal);
            background: rgba(79, 189, 171, 0.05);
        }
        
        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        /* Affichage de la photo actuelle */
        .current-photo {
            margin-top: var(--space-md);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .current-photo img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--principal);
        }
        
        .remove-photo {
            color: var(--danger);
            cursor: pointer;
            font-size: 0.8rem;
            margin-left: var(--space-sm);
        }
        
        .remove-photo:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Header personnalisé -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="/Social-Media-Awards-/assets/images/logo.png" alt="Social Media Awards" class="logo-image">
                <h1>Social Media <span class="highlight">Awards</span></h1>
            </div>
            
            <nav class="user-nav">
                <div class="user-info-nav">
                    <div class="avatar-nav">
                        <?php 
                        $currentPhoto = $userData['photo_profil'] ?? null;
                        if ($currentPhoto && file_exists($upload_dir . $currentPhoto)): 
                        ?>
                            <img src="<?php echo $web_path . htmlspecialchars($currentPhoto); ?>" 
                                 alt="<?php echo htmlspecialchars($userData['pseudonyme']); ?>" 
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <?php echo $initials; ?>
                        <?php endif; ?>
                    </div>
                    <div class="user-details-nav">
                        <span class="user-name-nav"><?php echo htmlspecialchars($userData['pseudonyme']); ?></span>
                        <span class="user-role-nav">Électeur</span>
                    </div>
                </div>
                
                <a href="user-dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </nav>
        </div>
    </header>

    <main class="edit-profile-container">
        <div class="edit-profile-main">
            <!-- Fil d'Ariane (breadcrumb) -->
            <nav class="breadcrumb">
                <ul>
                    <li><a href="user-dashboard.php">Tableau de bord</a></li>
                    <li class="separator"><i class="fas fa-chevron-right"></i></li>
                    <li class="current">Modifier le profil</li>
                </ul>
            </nav>

            <!-- Grille principale -->
            <div class="profile-grid">
                <!-- Barre latérale -->
                <aside class="profile-sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-avatar">
                            <div class="avatar-large <?php echo ($userData['photo_profil'] ?? null) ? 'has-photo' : ''; ?>" id="avatarContainer">
                                <?php if ($userData['photo_profil'] ?? null): 
                                    $photoPath = $upload_dir . $userData['photo_profil'];
                                    if (file_exists($photoPath)): ?>
                                        <img src="<?php echo $web_path . htmlspecialchars($userData['photo_profil']); ?>" 
                                             alt="<?php echo htmlspecialchars($userData['pseudonyme']); ?>" 
                                             class="avatar-img">
                                    <?php else: ?>
                                        <div class="avatar-initials"><?php echo $initials; ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="avatar-initials"><?php echo $initials; ?></div>
                                <?php endif; ?>
                                <div class="avatar-edit">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                            <h3><?php echo htmlspecialchars($userData['pseudonyme']); ?></h3>
                            <p>Électeur depuis <?php echo date('m/Y'); ?></p>
                        </div>
                        
                        <!-- Menu de navigation latéral -->
                        <nav class="sidebar-menu">
                            <a href="#informations" class="menu-item active">
                                <i class="fas fa-user"></i>
                                Informations personnelles
                            </a>
                            <a href="#securite" class="menu-item">
                                <i class="fas fa-shield-alt"></i>
                                Sécurité du compte
                            </a>
                        </nav>
                    </div>
                    
                    <!-- Statistiques utilisateur -->
                    <div class="sidebar-card">
                        <h4 style="margin-bottom: var(--space-md); color: var(--dark);">
                            <i class="fas fa-info-circle" style="color: var(--principal);"></i>
                            Statistiques
                        </h4>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: var(--space-sm);">
                            <li style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 0.9rem; color: var(--gray);">Votes émis</span>
                                <span style="font-weight: 600; color: var(--principal);">0</span>
                            </li>
                            <li style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 0.9rem; color: var(--gray);">Inscrit depuis</span>
                                <span style="font-weight: 600; color: var(--principal);"><?php echo date('m/Y'); ?></span>
                            </li>
                            <li style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 0.9rem; color: var(--gray);">Dernière connexion</span>
                                <span style="font-weight: 600; color: var(--principal);">Aujourd'hui</span>
                            </li>
                        </ul>
                    </div>
                </aside>

                <!-- Section d'édition principale -->
                <section class="edit-section">
                    <div class="section-header">
                        <h2><i class="fas fa-user-edit"></i> Modifier votre profil</h2>
                        <p>Mettez à jour vos informations personnelles et gérez vos préférences.</p>
                    </div>

                    <!-- Messages de succès/erreur -->
                    <?php if ($success): ?>
                        <div style="background: rgba(50, 213, 131, 0.1); border: 2px solid var(--success); border-radius: var(--radius-md); padding: var(--space-md); margin-bottom: var(--space-lg); display: flex; align-items: center; gap: var(--space-sm);">
                            <i class="fas fa-check-circle" style="color: var(--success); font-size: 1.2rem;"></i>
                            <span style="color: var(--success); font-weight: 500;"><?php echo htmlspecialchars($success); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($errors['general'])): ?>
                        <div style="background: rgba(255, 107, 107, 0.1); border: 2px solid var(--danger); border-radius: var(--radius-md); padding: var(--space-md); margin-bottom: var(--space-lg); display: flex; align-items: center; gap: var(--space-sm);">
                            <i class="fas fa-exclamation-circle" style="color: var(--danger); font-size: 1.2rem;"></i>
                            <span style="color: var(--danger); font-weight: 500;"><?php echo htmlspecialchars($errors['general']); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire d'édition du profil -->
                    <form method="POST" action="" class="edit-form" id="editProfileForm" enctype="multipart/form-data">
                        <div class="form-grid">
                            <!-- Champ pseudonyme -->
                            <div class="form-group">
                                <label for="pseudonyme" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Pseudonyme <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       id="pseudonyme" 
                                       name="pseudonyme" 
                                       class="form-control <?php echo isset($errors['pseudonyme']) ? 'error' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($userData['pseudonyme']); ?>"
                                       required
                                       minlength="3"
                                       maxlength="50"
                                       placeholder="Votre nom public">
                                <?php if (isset($errors['pseudonyme'])): ?>
                                    <small class="form-text error"><?php echo htmlspecialchars($errors['pseudonyme']); ?></small>
                                <?php else: ?>
                                    <small class="form-text">Votre nom public visible par tous (3-50 caractères)</small>
                                <?php endif; ?>
                            </div>

                            <!-- Champ email -->
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Adresse email <span class="required">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control <?php echo isset($errors['email']) ? 'error' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($userData['email']); ?>"
                                       required
                                       placeholder="exemple@email.com">
                                <?php if (isset($errors['email'])): ?>
                                    <small class="form-text error"><?php echo htmlspecialchars($errors['email']); ?></small>
                                <?php else: ?>
                                    <small class="form-text">Nous ne partagerons jamais votre email</small>
                                <?php endif; ?>
                            </div>

                            <!-- Champ date de naissance -->
                            <div class="form-group">
                                <label for="date_naissance" class="form-label">
                                    <i class="fas fa-birthday-cake"></i>
                                    Date de naissance <span class="required">*</span>
                                </label>
                                <input type="date" 
                                       id="date_naissance" 
                                       name="date_naissance" 
                                       class="form-control <?php echo isset($errors['date_naissance']) ? 'error' : ''; ?>" 
                                       value="<?php echo htmlspecialchars($userData['date_naissance']); ?>"
                                       required
                                       max="<?php echo date('Y-m-d', strtotime('-13 years')); ?>">
                                <?php if (isset($errors['date_naissance'])): ?>
                                    <small class="form-text error"><?php echo htmlspecialchars($errors['date_naissance']); ?></small>
                                <?php else: ?>
                                    <small class="form-text">Vous devez avoir au moins 13 ans</small>
                                <?php endif; ?>
                            </div>

                            <!-- Champ pays -->
                            <div class="form-group">
                                <label for="pays" class="form-label">
                                    <i class="fas fa-globe"></i>
                                    Pays <span class="required">*</span>
                                </label>
                                <select id="pays" 
                                        name="pays" 
                                        class="form-control <?php echo isset($errors['pays']) ? 'error' : ''; ?>" 
                                        required>
                                    <option value="">Sélectionnez votre pays</option>
                                    <option value="France" <?php echo ($userData['pays'] ?? '') == 'France' ? 'selected' : ''; ?>>France</option>
                                    <option value="Belgique" <?php echo ($userData['pays'] ?? '') == 'Belgique' ? 'selected' : ''; ?>>Belgique</option>
                                    <option value="Suisse" <?php echo ($userData['pays'] ?? '') == 'Suisse' ? 'selected' : ''; ?>>Suisse</option>
                                    <option value="Canada" <?php echo ($userData['pays'] ?? '') == 'Canada' ? 'selected' : ''; ?>>Canada</option>
                                    <option value="Luxembourg" <?php echo ($userData['pays'] ?? '') == 'Luxembourg' ? 'selected' : ''; ?>>Luxembourg</option>
                                    <option value="Autre" <?php echo ($userData['pays'] ?? '') == 'Autre' ? 'selected' : ''; ?>>Autre</option>
                                </select>
                                <?php if (isset($errors['pays'])): ?>
                                    <small class="form-text error"><?php echo htmlspecialchars($errors['pays']); ?></small>
                                <?php endif; ?>
                            </div>

                            <!-- Champ genre -->
                            <div class="form-group full-width">
                                <label class="form-label">
                                    <i class="fas fa-venus-mars"></i>
                                    Genre
                                </label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="genre" value="Homme" <?php echo ($userData['genre'] ?? '') == 'Homme' ? 'checked' : ''; ?>>
                                        <span>Homme</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="genre" value="Femme" <?php echo ($userData['genre'] ?? '') == 'Femme' ? 'checked' : ''; ?>>
                                        <span>Femme</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="genre" value="Autre" <?php echo ($userData['genre'] ?? '') == 'Autre' ? 'checked' : ''; ?>>
                                        <span>Autre</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="genre" value="" <?php echo empty($userData['genre'] ?? '') ? 'checked' : ''; ?>>
                                        <span>Préfère ne pas dire</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Champ photo de profil -->
                            <div class="form-group full-width">
                                <label class="form-label">
                                    <i class="fas fa-camera"></i>
                                    Photo de profil
                                </label>
                                <?php if (isset($errors['photo_profil'])): ?>
                                    <div style="color: var(--danger); margin-bottom: var(--space-sm);">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo htmlspecialchars($errors['photo_profil']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Zone de téléchargement de fichier -->
                                <div class="file-upload">
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--principal); margin-bottom: var(--space-sm);"></i>
                                    <h4 style="color: var(--dark); margin-bottom: var(--space-xs);">Changer de photo</h4>
                                    <p style="color: var(--gray); margin-bottom: var(--space-md);">
                                        Cliquez pour télécharger ou glissez-déposez une image
                                    </p>
                                    <small style="color: var(--gray); display: block;">
                                        PNG, JPG ou GIF jusqu'à 5MB
                                    </small>
                                    <input type="file" id="photo_profil" name="photo_profil" accept="image/*">
                                </div>
                                
                                <!-- Affichage de la photo actuelle -->
                                <?php if ($userData['photo_profil'] && file_exists($upload_dir . $userData['photo_profil'])): ?>
                                    <div class="current-photo">
                                        <img src="<?php echo $web_path . htmlspecialchars($userData['photo_profil']); ?>" 
                                             alt="Photo actuelle">
                                        <div>
                                            <strong>Photo actuelle:</strong> <?php echo htmlspecialchars($userData['photo_profil']); ?>
                                            <br>
                                            <span class="remove-photo" onclick="removePhoto()">
                                                <i class="fas fa-trash"></i> Supprimer cette photo
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Prévisualisation de la nouvelle photo -->
                                <div id="photoPreview" style="margin-top: var(--space-md); display: none;">
                                    <strong>Nouvelle photo sélectionnée:</strong>
                                    <div style="margin-top: var(--space-sm);">
                                        <img id="previewImage" src="" alt="Preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid var(--principal);">
                                        <div style="margin-top: var(--space-xs); font-size: 0.9rem; color: var(--gray);" id="fileInfo"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Sécurité -->
                        <div class="security-section" id="securite">
                            <h3 style="color: var(--dark); margin-bottom: var(--space-lg); display: flex; align-items: center; gap: var(--space-md);">
                                <i class="fas fa-shield-alt" style="color: var(--principal);"></i>
                                Sécurité du compte
                            </h3>
                            
                            <!-- Cartes de sécurité -->
                            <div class="security-grid">
                                <div class="security-card">
                                    <i class="fas fa-lock"></i>
                                    <div class="security-content">
                                        <h4>Mot de passe</h4>
                                        <p>Mettez à jour votre mot de passe régulièrement</p>
                                    </div>
                                </div>
                                
                                <div class="security-card">
                                    <i class="fas fa-user-shield"></i>
                                    <div class="security-content">
                                        <h4>Authentification</h4>
                                        <p>Activez l'authentification à deux facteurs</p>
                                    </div>
                                </div>
                                
                                <div class="security-card">
                                    <i class="fas fa-history"></i>
                                    <div class="security-content">
                                        <h4>Activité récente</h4>
                                        <p>Consultez les connexions récentes à votre compte</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions de sécurité -->
                            <div style="margin-top: var(--space-lg); display: flex; gap: var(--space-md);">
                                <a href="change-password.php" class="btn btn-outline">
                                    <i class="fas fa-key"></i>
                                    Changer le mot de passe
                                </a>
                            </div>
                        </div>

                        <!-- Actions du formulaire -->
                        <div class="form-actions">
                            <div class="btn-left-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Enregistrer les modifications
                                </button>
                                <a href="user-dashboard.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i>
                                    Annuler
                                </a>
                            </div>
                            
                            <div class="btn-right-group">
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <i class="fas fa-trash-alt"></i>
                                    Supprimer le compte
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <!-- Footer du tableau de bord -->
    <footer class="dashboard-footer">
        <div class="footer-content">
            <div class="footer-links">
                <a href="../categories.php">Catégories</a>
                <a href="../nominees.php">Nominés</a>
                <a href="../results.php">Résultats</a>
                <a href="../contact.php">Contact</a>
                <a href="../about.php">À propos</a>
                <a href="../faq.php">FAQ</a>
            </div>
            <div class="copyright">
                &copy; 2024 Social Media Awards. Tous droits réservés.
            </div>
        </div>
    </footer>

    <!-- Modal pour changer la photo de profil -->
    <div class="avatar-modal" id="avatarModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-camera"></i> Changer la photo de profil</h3>
            </div>
            <div class="modal-body">
                <p style="color: var(--gray); margin-bottom: var(--space-lg);">
                    Choisissez une méthode pour mettre à jour votre photo de profil
                </p>
                
                <div class="modal-options">
                    <div class="avatar-option" onclick="document.getElementById('photo_profil').click();">
                        <i class="fas fa-upload"></i>
                        <span>Télécharger</span>
                    </div>
                    
                    <div class="avatar-option" onclick="generateAvatar();">
                        <i class="fas fa-palette"></i>
                        <span>Générer un avatar</span>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: var(--space-lg);">
                    <p style="color: var(--gray); font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i>
                        Votre photo sera visible par tous les utilisateurs
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAvatarModal();">
                    Annuler
                </button>
                <button type="button" class="btn btn-primary" onclick="saveAvatar();">
                    Enregistrer
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript pour la gestion de l'interface utilisateur -->
    <script>
    // Gestion de la modal d'avatar
    document.querySelector('.avatar-large').addEventListener('click', function() {
        document.getElementById('avatarModal').style.display = 'flex';
    });
    
    function closeAvatarModal() {
        document.getElementById('avatarModal').style.display = 'none';
    }
    
    function generateAvatar() {
        // Générer un avatar avec des couleurs aléatoires
        const colors = [
            'linear-gradient(135deg, #4FBDAB, #3da895)',
            'linear-gradient(135deg, #FF5A79, #ff3d5e)',
            'linear-gradient(135deg, #FFD166, #ffc145)',
            'linear-gradient(135deg, #32D583, #2bc174)'
        ];
        
        const randomColor = colors[Math.floor(Math.random() * colors.length)];
        document.querySelector('.avatar-large').style.background = randomColor;
        document.querySelector('.avatar-large').classList.remove('has-photo');
        document.querySelector('.avatar-large').innerHTML = '<div class="avatar-initials"><?php echo $initials; ?></div><div class="avatar-edit"><i class="fas fa-camera"></i></div>';
        
        // Afficher un message de confirmation
        alert('Avatar généré avec succès! Cliquez sur "Enregistrer" pour confirmer.');
    }
    
    function saveAvatar() {
        // TODO: Implémenter l'enregistrement de l'avatar
        alert('Photo de profil mise à jour!');
        closeAvatarModal();
    }
    
    // Gestion du téléchargement de fichier
    document.getElementById('photo_profil').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const photoPreview = document.getElementById('photoPreview');
        const previewImage = document.getElementById('previewImage');
        const fileInfo = document.getElementById('fileInfo');
        const avatarContainer = document.getElementById('avatarContainer');
        
        if (file) {
            // Valider la taille (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Le fichier est trop volumineux. Maximum 5MB.');
                e.target.value = '';
                return;
            }
            
            // Valider le type de fichier
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Type de fichier non autorisé. Formats acceptés: JPEG, PNG, GIF.');
                e.target.value = '';
                return;
            }
            
            // Prévisualisation
            const reader = new FileReader();
            reader.onload = function(event) {
                // Afficher la prévisualisation
                previewImage.src = event.target.result;
                fileInfo.textContent = file.name + ' (' + Math.round(file.size / 1024) + 'KB)';
                photoPreview.style.display = 'block';
                
                // Mettre à jour l'avatar dans la barre latérale
                avatarContainer.innerHTML = '<img src="' + event.target.result + '" class="avatar-preview"><div class="avatar-edit"><i class="fas fa-camera"></i></div>';
                avatarContainer.classList.add('has-photo');
            };
            reader.readAsDataURL(file);
        } else {
            photoPreview.style.display = 'none';
        }
    });
    
    // Fonction pour supprimer la photo
    function removePhoto() {
        if (confirm('Êtes-vous sûr de vouloir supprimer votre photo de profil?')) {
            // Ajouter un champ hidden pour indiquer la suppression de la photo
            const form = document.getElementById('editProfileForm');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'remove_photo';
            input.value = '1';
            form.appendChild(input);
            
            // Soumettre le formulaire
            form.submit();
        }
    }
    
    // Confirmation de suppression de compte
    function confirmDelete() {
        if (confirm('Êtes-vous sûr de vouloir supprimer votre compte?\n\nCette action est irréversible et supprimera toutes vos données.')) {
            window.location.href = 'delete-account.php';
        }
    }
    
    // Validation côté client
    document.getElementById('editProfileForm').addEventListener('submit', function(e) {
        const pseudonyme = document.getElementById('pseudonyme').value.trim();
        const email = document.getElementById('email').value.trim();
        const dateNaissance = document.getElementById('date_naissance').value;
        
        let isValid = true;
        let errorMessage = '';
        
        // Validation du pseudonyme
        if (pseudonyme.length < 3) {
            isValid = false;
            errorMessage += '• Le pseudonyme doit contenir au moins 3 caractères\n';
        }
        
        // Validation de l'email
        if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            isValid = false;
            errorMessage += '• Veuillez entrer une adresse email valide\n';
        }
        
        // Validation de la date de naissance
        if (!dateNaissance) {
            isValid = false;
            errorMessage += '• La date de naissance est requise\n';
        } else {
            const birthDate = new Date(dateNaissance);
            const minDate = new Date();
            minDate.setFullYear(minDate.getFullYear() - 13);
            
            if (birthDate > minDate) {
                isValid = false;
                errorMessage += '• Vous devez avoir au moins 13 ans\n';
            }
        }
        
        // Validation du fichier (s'il est sélectionné)
        const fileInput = document.getElementById('photo_profil');
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            if (file.size > 5 * 1024 * 1024) {
                isValid = false;
                errorMessage += '• L\'image est trop volumineuse (max 5MB)\n';
            }
            
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                isValid = false;
                errorMessage += '• Type de fichier non autorisé (JPEG, PNG, GIF seulement)\n';
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs suivantes:\n\n' + errorMessage);
        }
    });
    
    // Navigation dans la barre latérale
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    // Retirer la classe active de tous les liens
                    document.querySelectorAll('.sidebar-menu a').forEach(item => {
                        item.classList.remove('active');
                    });
                    
                    // Ajouter la classe active au lien cliqué
                    this.classList.add('active');
                    
                    // Faire défiler jusqu'à la section
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Fermer la modal en cliquant à l'extérieur
    document.getElementById('avatarModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAvatarModal();
        }
    });
    
    // Gestion du drag and drop pour le téléchargement de fichier
    const fileUpload = document.querySelector('.file-upload');
    const fileInput = document.getElementById('photo_profil');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileUpload.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight(e) {
        fileUpload.style.borderColor = 'var(--principal)';
        fileUpload.style.backgroundColor = 'rgba(79, 189, 171, 0.1)';
    }
    
    function unhighlight(e) {
        fileUpload.style.borderColor = 'var(--gray-light)';
        fileUpload.style.backgroundColor = 'var(--light)';
    }
    
    // Gestion des événements de drag and drop
    ['dragenter', 'dragover'].forEach(eventName => {
        fileUpload.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        fileUpload.addEventListener(eventName, unhighlight, false);
    });
    
    fileUpload.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });
    </script>
</body>
</html>
<?php
// views/user/edit-profile.php - VERSÃO ATUALIZADA
require_once '../../config/session.php';
require_once '../../app/Models/User.php';

// Verificar autenticação
requireRole('voter');

// Obter dados do usuário
$userId = $_SESSION['user_id'];
$userModel = new User();

// Buscar dados do usuário no banco de dados
$userData = $userModel->getUserById($userId);

// Se não encontrar dados, usar dados da sessão como fallback
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

// Inicializar mensagens de erro/sucesso
$errors = [];
$success = '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e processar dados
    $pseudonyme = trim($_POST['pseudonyme'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $pays = $_POST['pays'] ?? '';
    $genre = $_POST['genre'] ?? '';
    
    // Validações
    if (empty($pseudonyme)) {
        $errors['pseudonyme'] = 'Le pseudonyme est requis';
    } elseif (strlen($pseudonyme) < 3) {
        $errors['pseudonyme'] = 'Le pseudonyme doit contenir au moins 3 caractères';
    } elseif ($userModel->isPseudonymeTaken($pseudonyme, $userId)) {
        $errors['pseudonyme'] = 'Ce pseudonyme est déjà utilisé';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email invalide';
    } elseif ($userModel->isEmailTaken($email, $userId)) {
        $errors['email'] = 'Cet email est déjà utilisé';
    }
    
    if (empty($date_naissance)) {
        $errors['date_naissance'] = 'La date de naissance est requise';
    } elseif (strtotime($date_naissance) > strtotime('-13 years')) {
        $errors['date_naissance'] = 'Vous devez avoir au moins 13 ans';
    }
    
    if (empty($pays)) {
        $errors['pays'] = 'Le pays est requis';
    }
    
    // Gerenciar upload de foto (simplificado por enquanto)
    $photo_profil = $userData['photo_profil'] ?? null;
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK) {
        // Validar o arquivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['photo_profil']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            // Verificar tamanho (max 5MB)
            if ($_FILES['photo_profil']['size'] <= 5 * 1024 * 1024) {
                // Aqui você salvaria o arquivo em um diretório
                // Por enquanto, apenas armazenamos o nome
                $photo_profil = $_FILES['photo_profil']['name'];
            } else {
                $errors['photo_profil'] = 'L\'image est trop volumineuse (max 5MB)';
            }
        } else {
            $errors['photo_profil'] = 'Type de fichier non autorisé (JPEG, PNG, GIF seulement)';
        }
    }
    
    // Se não há erros, atualizar os dados
    if (empty($errors)) {
        $updateData = [
            'pseudonyme' => $pseudonyme,
            'email' => $email,
            'date_naissance' => $date_naissance,
            'pays' => $pays,
            'genre' => $genre
        ];
        
        if ($photo_profil) {
            $updateData['photo_profil'] = $photo_profil;
        }
        
        // Atualizar no banco de dados
        $result = $userModel->updateUserProfile($userId, $updateData);
        
        if ($result) {
            // Atualizar dados na sessão
            $_SESSION['user_pseudonyme'] = $pseudonyme;
            $_SESSION['user_email'] = $email;
            
            // Atualizar dados locais para exibição
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

// Obter iniciais para o avatar
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
                    <div class="avatar-nav"><?php echo $initials; ?></div>
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
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <ul>
                    <li><a href="user-dashboard.php">Tableau de bord</a></li>
                    <li class="separator"><i class="fas fa-chevron-right"></i></li>
                    <li class="current">Modifier le profil</li>
                </ul>
            </nav>

            <!-- Grille principale -->
            <div class="profile-grid">
                <!-- Sidebar de navigation -->
                <aside class="profile-sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-avatar">
                            <div class="avatar-large" id="avatarContainer">
                                <?php echo $initials; ?>
                                <div class="avatar-edit">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                            <h3><?php echo htmlspecialchars($userData['pseudonyme']); ?></h3>
                            <p>Électeur depuis <?php echo date('m/Y'); ?></p>
                        </div>
                        
                        <nav class="sidebar-menu">
                            <a href="#informations" class="menu-item active">
                                <i class="fas fa-user"></i>
                                Informations personnelles
                            </a>
                            <a href="#securite" class="menu-item">
                                <i class="fas fa-shield-alt"></i>
                                Sécurité du compte
                            </a>
                            <a href="#preferences" class="menu-item">
                                <i class="fas fa-cog"></i>
                                Préférences
                            </a>
                            <a href="#notifications" class="menu-item">
                                <i class="fas fa-bell"></i>
                                Notifications
                            </a>
                        </nav>
                    </div>
                    
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

                    <!-- Formulaire d'édition -->
                    <form method="POST" action="" class="edit-form" id="editProfileForm" enctype="multipart/form-data">
                        <div class="form-grid">
                            <!-- Pseudonyme -->
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

                            <!-- Email -->
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

                            <!-- Date de naissance -->
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

                            <!-- Pays -->
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

                            <!-- Genre -->
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

                            <!-- Photo de profil -->
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
                                <div class="file-upload">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <h4 style="color: var(--dark); margin-bottom: var(--space-xs);">Changer de photo</h4>
                                    <p style="color: var(--gray); margin-bottom: var(--space-md);">
                                        Cliquez pour télécharger ou glissez-déposez une image
                                    </p>
                                    <small style="color: var(--gray); display: block;">
                                        PNG, JPG ou GIF jusqu'à 5MB
                                    </small>
                                    <input type="file" id="photo_profil" name="photo_profil" accept="image/*">
                                </div>
                                <?php if ($userData['photo_profil']): ?>
                                    <small class="form-text" style="margin-top: var(--space-sm);">
                                        <i class="fas fa-image"></i>
                                        Photo actuelle: <?php echo htmlspecialchars($userData['photo_profil']); ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Section Sécurité -->
                        <div class="security-section" id="securite">
                            <h3 style="color: var(--dark); margin-bottom: var(--space-lg); display: flex; align-items: center; gap: var(--space-md);">
                                <i class="fas fa-shield-alt" style="color: var(--principal);"></i>
                                Sécurité du compte
                            </h3>
                            
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
                            
                            <div style="margin-top: var(--space-lg); display: flex; gap: var(--space-md);">
                                <a href="change-password.php" class="btn btn-outline">
                                    <i class="fas fa-key"></i>
                                    Changer le mot de passe
                                </a>
                                <a href="security-logs.php" class="btn btn-outline">
                                    <i class="fas fa-history"></i>
                                    Voir l'activité
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

    <!-- Footer -->
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
    <div class="avatar-modal" id="avatarModal">
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

    <script>
    // Gestion de la modal d'avatar
    document.querySelector('.avatar-large').addEventListener('click', function() {
        document.getElementById('avatarModal').style.display = 'flex';
    });
    
    function closeAvatarModal() {
        document.getElementById('avatarModal').style.display = 'none';
    }
    
    function generateAvatar() {
        const colors = [
            'linear-gradient(135deg, #4FBDAB, #3da895)',
            'linear-gradient(135deg, #FF5A79, #ff3d5e)',
            'linear-gradient(135deg, #FFD166, #ffc145)',
            'linear-gradient(135deg, #32D583, #2bc174)'
        ];
        
        const randomColor = colors[Math.floor(Math.random() * colors.length)];
        document.querySelector('.avatar-large').style.background = randomColor;
        
        // Afficher un message
        alert('Avatar généré avec succès! Cliquez sur "Enregistrer" pour confirmer.');
    }
    
    function saveAvatar() {
        // Aqui você implementaria o salvamento do avatar
        alert('Photo de profil mise à jour!');
        closeAvatarModal();
    }
    
    // Gestion du téléchargement de fichier
    document.getElementById('photo_profil').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Valider tamanho (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('Le fichier est trop volumineux. Maximum 5MB.');
                e.target.value = '';
                return;
            }
            
            // Validar tipo
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Type de fichier non autorisé. Formats acceptés: JPEG, PNG, GIF.');
                e.target.value = '';
                return;
            }
            
            // Prévisualização (opcional)
            const reader = new FileReader();
            reader.onload = function(event) {
                // Aqui você poderia mostrar uma pré-visualização
                // Por enquanto, apenas um alerta
                alert('Image sélectionnée: ' + file.name + '\nTaille: ' + Math.round(file.size / 1024) + 'KB');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Confirmação de exclusão de conta
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
        
        if (pseudonyme.length < 3) {
            isValid = false;
            errorMessage += '• Le pseudonyme doit contenir au moins 3 caractères\n';
        }
        
        if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            isValid = false;
            errorMessage += '• Veuillez entrer une adresse email valide\n';
        }
        
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
        
        // Validar arquivo (se selecionado)
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
    
    // Navigation dans la sidebar
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
    </script>
</body>
</html>
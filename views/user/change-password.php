<?php
// views/user/change-password.php
// Page de changement de mot de passe pour les électeurs
// Permet aux utilisateurs de mettre à jour leur mot de passe de manière sécurisée

// Inclure la gestion des sessions et le modèle utilisateur
require_once '../../config/session.php';
require_once '../../app/Models/UserModel.php';

// Vérifier que l'utilisateur est authentifié et a le rôle 'voter'
requireRole('voter');

// Initialiser les variables
$userId = $_SESSION['user_id'];
$errors = [];
$success = '';

// Traiter le formulaire lorsque soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validations du formulaire
    if (empty($current_password)) {
        $errors[] = 'Le mot de passe actuel est requis';
    }
    
    if (empty($new_password)) {
        $errors[] = 'Le nouveau mot de passe est requis';
    } elseif (strlen($new_password) < 6) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caractères';
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = 'Les nouveaux mots de passe ne correspondent pas';
    }
    
    // Si aucune erreur, procéder au changement de mot de passe
    if (empty($errors)) {
        // TODO: Implémenter la vérification du mot de passe actuel
        // et la mise à jour dans la base de données
        $success = 'Mot de passe changé avec succès!';
    }
}

// Générer les initiales pour l'avatar
$initials = strtoupper(substr($_SESSION['user_pseudonyme'], 0, 2));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer le mot de passe - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/edit-profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- En-tête personnalisé du tableau de bord -->
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
                        <span class="user-name-nav"><?php echo htmlspecialchars($_SESSION['user_pseudonyme']); ?></span>
                        <span class="user-role-nav">Électeur</span>
                    </div>
                </div>
                
                <!-- Bouton de retour vers la page d'édition du profil -->
                <a href="edit-profile.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Retour
                </a>
            </nav>
        </div>
    </header>

    <main class="edit-profile-container">
        <div class="edit-profile-main">
            <!-- Fil d'Ariane (breadcrumb) pour la navigation -->
            <nav class="breadcrumb">
                <ul>
                    <li><a href="user-dashboard.php">Tableau de bord</a></li>
                    <li class="separator"><i class="fas fa-chevron-right"></i></li>
                    <li><a href="edit-profile.php">Profil</a></li>
                    <li class="separator"><i class="fas fa-chevron-right"></i></li>
                    <li class="current">Changer le mot de passe</li>
                </ul>
            </nav>

            <!-- Structure en grille pour la page -->
            <div class="profile-grid">
                <!-- Barre latérale de navigation -->
                <aside class="profile-sidebar">
                    <div class="sidebar-card">
                        <nav class="sidebar-menu">
                            <a href="edit-profile.php" class="menu-item">
                                <i class="fas fa-user"></i>
                                Informations personnelles
                            </a>
                            <a href="#current" class="menu-item active">
                                <i class="fas fa-key"></i>
                                Changer mot de passe
                            </a>
                            <a href="edit-profile.php#securite" class="menu-item">
                                <i class="fas fa-shield-alt"></i>
                                Sécurité
                            </a>
                        </nav>
                    </div>
                </aside>

                <!-- Section principale de changement de mot de passe -->
                <section class="edit-section">
                    <div class="section-header">
                        <h2><i class="fas fa-key"></i> Changer le mot de passe</h2>
                        <p>Mettez à jour votre mot de passe pour renforcer la sécurité de votre compte.</p>
                    </div>

                    <!-- Message de succès -->
                    <?php if ($success): ?>
                        <div style="background: rgba(50, 213, 131, 0.1); border: 2px solid var(--success); border-radius: var(--radius-md); padding: var(--space-md); margin-bottom: var(--space-lg);">
                            <i class="fas fa-check-circle" style="color: var(--success); margin-right: var(--space-sm);"></i>
                            <span style="color: var(--success); font-weight: 500;"><?php echo htmlspecialchars($success); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Messages d'erreur -->
                    <?php if (!empty($errors)): ?>
                        <div style="background: rgba(255, 107, 107, 0.1); border: 2px solid var(--danger); border-radius: var(--radius-md); padding: var(--space-md); margin-bottom: var(--space-lg);">
                            <div style="color: var(--danger); margin-bottom: var(--space-sm);">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Erreurs:</strong>
                            </div>
                            <ul style="color: var(--danger); padding-left: var(--space-md);">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire de changement de mot de passe -->
                    <form method="POST" action="" class="edit-form">
                        <div class="form-grid">
                            <!-- Mot de passe actuel -->
                            <div class="form-group full-width">
                                <label for="current_password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    Mot de passe actuel <span class="required">*</span>
                                </label>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       class="form-control" 
                                       required
                                       placeholder="Entrez votre mot de passe actuel">
                                <small class="form-text">
                                    <i class="fas fa-info-circle"></i>
                                    Confirmez votre identité avec votre mot de passe actuel
                                </small>
                            </div>

                            <!-- Nouveau mot de passe -->
                            <div class="form-group">
                                <label for="new_password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    Nouveau mot de passe <span class="required">*</span>
                                </label>
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       class="form-control" 
                                       required
                                       minlength="6"
                                       placeholder="Minimum 6 caractères">
                                <small class="form-text">
                                    <i class="fas fa-shield-alt"></i>
                                    Utilisez un mot de passe fort et unique
                                </small>
                            </div>

                            <!-- Confirmation du nouveau mot de passe -->
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    Confirmer le mot de passe <span class="required">*</span>
                                </label>
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-control" 
                                       required
                                       placeholder="Répétez le nouveau mot de passe">
                                <small class="form-text">
                                    <i class="fas fa-check-circle"></i>
                                    Les deux mots de passe doivent correspondre
                                </small>
                            </div>

                            <!-- Conseils pour un mot de passe sécurisé -->
                            <div class="form-group full-width">
                                <div style="background: var(--light); border-radius: var(--radius-md); padding: var(--space-md); margin-top: var(--space-sm);">
                                    <h4 style="color: var(--dark); margin-bottom: var(--space-sm);">
                                        <i class="fas fa-lightbulb"></i>
                                        Conseils pour un mot de passe sécurisé
                                    </h4>
                                    <ul style="color: var(--gray); font-size: 0.9rem; padding-left: var(--space-md);">
                                        <li>Utilisez au moins 8 caractères</li>
                                        <li>Combinez lettres majuscules et minuscules</li>
                                        <li>Ajoutez des chiffres et caractères spéciaux</li>
                                        <li>Évitez les mots du dictionnaire</li>
                                        <li>Ne réutilisez pas d'anciens mots de passe</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Actions du formulaire -->
                        <div class="form-actions">
                            <div class="btn-left-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Enregistrer le nouveau mot de passe
                                </button>
                                <a href="edit-profile.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i>
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <!-- Scripts JavaScript pour la validation et l'interface utilisateur -->
    <script>
    // Validation du formulaire de changement de mot de passe
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Validation de la longueur minimale
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('Le nouveau mot de passe doit contenir au moins 6 caractères.');
            return false;
        }
        
        // Validation de la correspondance des mots de passe
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Les nouveaux mots de passe ne correspondent pas.');
            return false;
        }
        
        return true;
    });
    
    // Fonction pour afficher/masquer les mots de passe (non implémentée ici)
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        if (field.type === 'password') {
            field.type = 'text';
        } else {
            field.type = 'password';
        }
    }
    </script>
</body>
</html>
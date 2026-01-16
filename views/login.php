<?php
// FICHIER : login.php
// DESCRIPTION : Page de connexion avec support de redirection
// FONCTIONNALITÉ : Gère l'authentification et redirige vers la page demandée

error_reporting(E_ALL); 
ini_set('display_errors', 1);

require_once '../app/Controllers/UserController.php';
require_once '../config/session.php';

// VÉRIFICATION : SI DÉJÀ AUTHENTIFIÉ, REDIRECTION IMMÉDIATE
if (isAuthenticated()) {
    $redirect = match(getUserType()) {
        'admin' => '../views/admin/dashboard.php',
        'candidate' => '../views/candidate/candidate-dashboard.php',
        'voter' => '../views/user/user-dashboard.php',
        default => '../index.php'
    };
    
    // PRIORITÉ AU PARAMÈTRE DE REDIRECTION DE L'URL
    if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
        $redirect = urldecode($_GET['redirect']);
    }
    
    header("Location: $redirect");
    exit();
}

// INITIALISATION DU CONTRÔLEUR
$controller = new UserController();

// RÉCUPÉRATION DU PARAMÈTRE DE REDIRECTION
$redirectUrl = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '';

// TRAITEMENT DU FORMULAIRE DE CONNEXION
$result = $controller->handleLogin();

// GESTION DE LA CONNEXION RÉUSSIE
if (isset($result['success']) && $result['success']) {
    // Redirection selon la priorité :
    // 1. Paramètre redirect de l'URL (si présent)
    // 2. Dashboard par défaut selon le rôle
    if (!empty($redirectUrl)) {
        header("Location: $redirectUrl");
    } else {
        $redirect = match($_SESSION['user_role'] ?? '') {
            'admin' => '../views/admin/dashboard.php',
            'candidate' => '../views/candidate/candidate-dashboard.php',
            'voter' => '../views/user/user-dashboard.php', // CORRIGIDO AQUI TAMBÉM
            default => '../index.php'
        };
        header("Location: $redirect");
    }
    exit();
}

// RÉCUPÉRATION DES MESSAGES D'ERREUR
$error = $result['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Social Media Awards</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'partials/header.php'; ?> 
    
    <div class="login-container">
        <!-- EN-TÊTE DE LA PAGE -->
        <div class="login-header">
            <h1>Connexion</h1>
            <p>Accédez à votre compte Social Media Awards</p>
        </div>
        
        <!-- CARTE DE CONNEXION -->
        <div class="login-card">
            <?php if($error): ?>
                <!-- AFFICHAGE DES ERREURS -->
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- FORMULAIRE DE CONNEXION -->
            <form method="POST" action="">
                <!-- CHAMP CACHÉ POUR LA REDIRECTION -->
                <!-- Permet de transmettre la destination après connexion -->
                <?php if ($redirectUrl): ?>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectUrl); ?>">
                <?php endif; ?>
                
                <!-- CHAMP EMAIL -->
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Adresse email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="votre@email.com" 
                           required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <!-- CHAMP MOT DE PASSE -->
                <div class="form-group">
                    <label for="mot_de_passe">
                        <i class="fas fa-lock"></i> Mot de passe
                    </label>
                    <input type="password" 
                           id="mot_de_passe" 
                           name="mot_de_passe" 
                           placeholder="Votre mot de passe" 
                           required>
                </div>
                
                
                <!-- BOUTON DE SOUMISSION -->
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>
            
            <!-- PIED DE PAGE DU FORMULAIRE -->
            <div class="login-footer">
                <p>Pas encore de compte ? 
                   <a href="../inscription.php" class="register-link">S'inscrire</a>
                </p>
                <p>
                    <a href="../index.php">
                        <i class="fas fa-arrow-left"></i> Retour à l'accueil
                    </a>
                </p>
            </div>
        </div>
        
        <!-- SECTION SÉCURITÉ -->
        <div class="login-security">
            <h3><i class="fas fa-shield-alt"></i> Sécurité</h3>
            <ul>
                <li><i class="fas fa-check-circle"></i> Connexion sécurisée</li>
                <li><i class="fas fa-check-circle"></i> Données chiffrées</li>
                <li><i class="fas fa-check-circle"></i> Sessions protégées</li>
            </ul>
        </div>
    </div>
    
    <script src="../assets/js/login.js"></script>
</body>
</html>
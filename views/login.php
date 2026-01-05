
<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
// views/login.php - SEM 2FA
require_once '../app/Controllers/UserController.php';
require_once '../config/session.php';

// Se já estiver autenticado, redirecionar
if (isAuthenticated()) {
    $redirect = match(getUserType()) {
        'admin' => '../views/admin/dashboard.php',
        'candidate' => '../views/candidate/candidate-dashboard.php',
        'voter' => '../views/user/user-dashboard.php',
        default => '../index.php'
    };
    header("Location: $redirect");
    exit();
}

$controller = new UserController();
$result = $controller->handleLogin();

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
        <div class="login-header">
            <h1>Connexion</h1>
            <p>Accédez à votre compte Social Media Awards</p>
        </div>
        
        <div class="login-card">
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
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
                
                <div class="form-options">
                    <label class="checkbox">
                        <input type="checkbox" name="remember"> Se souvenir de moi
                    </label>
                    <a href="/forgot-password.php" class="forgot-password">Mot de passe oublié ?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>
            
            <div class="login-footer">
                <p>Pas encore de compte ? <a href="../inscription.php" class="register-link">S'inscrire</a></p>
                <p><a href="../index.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a></p>
            </div>
        </div>
        
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

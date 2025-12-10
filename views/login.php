<?php
require_once '../config/session.php';
require_once '../app/Services/UserService.php';

// Redirection si déjà connecté
if (isAuthenticated()) {
    $userType = getUserType();
    switch($userType) {
        case 'admin':
            header('Location: /admin/dashboard.php');
            break;
        case 'candidate':
            header('Location: /candidate/dashboard.php');
            break;
        case 'voter':
            header('Location: /user/dashboard.php');
            break;
        default:
            header('Location: /index.php');
    }
    exit();
}

$userService = new UserService();
$error = '';
$requires2FA = false;
$tempEmail = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['mot_de_passe'];
    $code2fa = $_POST['code_verification'] ?? '';
    
    $result = $userService->login($email, $password, $code2fa);
    
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit();
    } else {
        if (isset($result['requires_2fa'])) {
            $requires2FA = true;
            $tempEmail = $result['email'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Social Media Awards</title>
    <link rel="stylesheet" href="/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="/assets/images/logo.png" alt="Logo Social Media Awards" class="login-logo">
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
            
            <?php if($requires2FA): ?>
                <div class="alert alert-info">
                    <i class="fas fa-shield-alt"></i>
                    Authentification à deux facteurs requise
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php if($requires2FA): ?>
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($tempEmail); ?>">
                    <input type="hidden" name="mot_de_passe" value="<?php echo htmlspecialchars($_POST['mot_de_passe'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label for="code_verification">
                            <i class="fas fa-key"></i> Code de vérification
                        </label>
                        <input type="text" 
                               id="code_verification" 
                               name="code_verification" 
                               placeholder="Entrez le code 2FA" 
                               required
                               maxlength="6"
                               pattern="[0-9]{6}"
                               title="6 chiffres requis">
                        <small>Veuillez entrer le code à 6 chiffres envoyé à votre email</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-check-circle"></i> Vérifier le code
                    </button>
                    
                <?php else: ?>
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
                <?php endif; ?>
            </form>
            
            <div class="login-footer">
                <p>Pas encore de compte ? <a href="/inscription.php" class="register-link">S'inscrire</a></p>
                <p><a href="/index.php"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a></p>
            </div>
        </div>
        
        <div class="login-security">
            <h3><i class="fas fa-shield-alt"></i> Sécurité</h3>
            <ul>
                <li><i class="fas fa-check-circle"></i> Connexion sécurisée HTTPS</li>
                <li><i class="fas fa-check-circle"></i> Authentification 2FA optionnelle</li>
                <li><i class="fas fa-check-circle"></i> Données chiffrées</li>
            </ul>
        </div>
    </div>
    
    <script src="/assets/js/login.js"></script>
</body>
</html>
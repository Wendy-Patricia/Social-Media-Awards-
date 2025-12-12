
<?php
// inscription.php
require_once 'app/Controllers/UserController.php';
require_once 'config/session.php';

// Se já estiver autenticado, redirecionar
if (isAuthenticated()) {
    $redirect = match(getUserType()) {
        'admin' => '/Social-Media-Awards-/admin/admin-dashboard.php',
        'candidate' => '/Social-Media-Awards-/candidate/candidate-dashboard.php',
        'voter' => '/Social-Media-Awards-/user/user-dashboard.php',
        default => 'index.php'
    };
    header("Location: $redirect");
    exit();
}

$controller = new UserController();
$result = $controller->handleRegistration();

// Se registro for bem-sucedido, já será redirecionado pelo controller
// Se chegou aqui, é porque houve erro ou é a primeira carga da página
$errors = $result['errors'] ?? [];
$data = $result['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/base.css">
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/inscription.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <main class="inscription-container">
        <div class="container">
            <div class="inscription-header">
                <h1><i class="fas fa-user-plus"></i> Créer un Compte</h1>
                <p>Rejoignez la communauté Social Media Awards</p>
            </div>
            
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <h3>Erreurs de validation</h3>
                        <ul>
                            <?php foreach($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="inscription-card">
                <form method="POST" action="" class="inscription-form" id="inscriptionForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pseudonyme" class="form-label">
                                <i class="fas fa-user"></i> Pseudonyme *
                            </label>
                            <input type="text" 
                                   id="pseudonyme" 
                                   name="pseudonyme" 
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($data['pseudonyme'] ?? ''); ?>"
                                   required
                                   minlength="3"
                                   maxlength="50"
                                   placeholder="Votre nom public">
                            <small class="form-text">Votre nom public (3-50 caractères)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Adresse email *
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                                   required
                                   placeholder="exemple@email.com">
                            <small class="form-text">Nous ne partagerons jamais votre email</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mot_de_passe" class="form-label">
                                <i class="fas fa-lock"></i> Mot de passe *
                            </label>
                            <input type="password" 
                                   id="mot_de_passe" 
                                   name="mot_de_passe" 
                                   class="form-control"
                                   required
                                   minlength="6"
                                   placeholder="••••••">
                            <small class="form-text">Minimum 6 caractères</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_mot_de_passe" class="form-label">
                                <i class="fas fa-lock"></i> Confirmer le mot de passe *
                            </label>
                            <input type="password" 
                                   id="confirm_mot_de_passe" 
                                   name="confirm_mot_de_passe" 
                                   class="form-control"
                                   required
                                   placeholder="••••••">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type_user" class="form-label">
                                <i class="fas fa-user-tag"></i> Type de compte *
                            </label>
                            <select id="type_user" name="type_user" class="form-control" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="voter" <?php echo ($data['type_user'] ?? '') == 'voter' ? 'selected' : ''; ?>>
                                    Électeur - Je veux voter pour mes favoris
                                </option>
                                <option value="candidate" <?php echo ($data['type_user'] ?? '') == 'candidate' ? 'selected' : ''; ?>>
                                    Candidat - Je veux participer aux élections
                                </option>
                            </select>
                            <small class="form-text">Vous pourrez modifier ce choix plus tard</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_naissance" class="form-label">
                                <i class="fas fa-birthday-cake"></i> Date de naissance *
                            </label>
                            <input type="date" 
                                   id="date_naissance" 
                                   name="date_naissance" 
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($data['date_naissance'] ?? ''); ?>"
                                   required
                                   max="<?php echo date('Y-m-d', strtotime('-13 years')); ?>">
                            <small class="form-text">Vous devez avoir au moins 13 ans</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pays" class="form-label">
                                <i class="fas fa-globe"></i> Pays *
                            </label>
                            <select id="pays" name="pays" class="form-control" required>
                                <option value="">Sélectionnez votre pays</option>
                                <option value="France" <?php echo ($data['pays'] ?? '') == 'France' ? 'selected' : ''; ?>>France</option>
                                <option value="Belgique" <?php echo ($data['pays'] ?? '') == 'Belgique' ? 'selected' : ''; ?>>Belgique</option>
                                <option value="Suisse" <?php echo ($data['pays'] ?? '') == 'Suisse' ? 'selected' : ''; ?>>Suisse</option>
                                <option value="Canada" <?php echo ($data['pays'] ?? '') == 'Canada' ? 'selected' : ''; ?>>Canada</option>
                                <option value="Autre" <?php echo ($data['pays'] ?? '') == 'Autre' ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-venus-mars"></i> Genre
                            </label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="genre" value="Homme" <?php echo ($data['genre'] ?? '') == 'Homme' ? 'checked' : ''; ?>>
                                    <span>Homme</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="genre" value="Femme" <?php echo ($data['genre'] ?? '') == 'Femme' ? 'checked' : ''; ?>>
                                    <span>Femme</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="genre" value="Autre" <?php echo ($data['genre'] ?? '') == 'Autre' ? 'checked' : ''; ?>>
                                    <span>Autre</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="genre" value="" <?php echo empty($data['genre'] ?? '') ? 'checked' : ''; ?>>
                                    <span>Préfère ne pas dire</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group terms-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" id="terms" required>
                            <span>J'accepte les <a href="#terms" class="terms-link">conditions d'utilisation</a> et la <a href="#privacy" class="privacy-link">politique de confidentialité</a> *</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <i class="fas fa-user-plus"></i> Créer mon compte
                    </button>
                </form>
                
                <div class="inscription-footer">
                    <p>Déjà un compte ? <a href="views/login.php" class="login-link">Connectez-vous ici</a></p>
                    <p><a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a></p>
                </div>
            </div>
        </div>
    </main>
    
    <script>
    document.getElementById('inscriptionForm').addEventListener('submit', function(e) {
        const password = document.getElementById('mot_de_passe').value;
        const confirmPassword = document.getElementById('confirm_mot_de_passe').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            return false;
        }
        
        const terms = document.getElementById('terms');
        if (!terms.checked) {
            e.preventDefault();
            alert('Vous devez accepter les conditions d\'utilisation.');
            return false;
        }
        
        return true;
    });
    </script>
</body>
</html>

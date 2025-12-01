<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = htmlspecialchars($_POST['nome'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $type_user = $_POST['type_user'] ?? '';
    
    $erros = [];
    
    // Validations
    if (empty($nome)) {
        $erros[] = "Le nom est obligatoire";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email invalide";
    }
    
    if (strlen($senha) < 6) {
        $erros[] = "Le mot de passe doit contenir au moins 6 caractères";
    }
    
    if ($senha !== $confirmar_senha) {
        $erros[] = "Les mots de passe ne correspondent pas";
    }
    
    if (empty($type_user) || !in_array($type_user, ['electeur', 'candidat', 'admin'])) {
        $erros[] = "Veuillez sélectionner un type d'utilisateur valide";
    }
    
    // Si pas d'erreurs, traite l'inscription
    if (empty($erros)) {
        // Ici vous ajouteriez la logique pour sauvegarder dans la base de données
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Exemple de sauvegarde (adapter selon votre base de données)
        // $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
        // Exécuter la requête...
        
        // Démarrer la session et sauvegarder les infos utilisateur
        session_start();
        $_SESSION['user_id'] = 1; // ID de l'utilisateur créé
        $_SESSION['user_nome'] = $nome;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_tipo'] = $type_user;
        
        // Redirection selon le type d'utilisateur
        switch($type_user) {
            case 'electeur':
                header("Location: dashboard_electeur.php");
                exit();
            case 'candidat':
                header("Location: dashboard_candidat.php");
                exit();
            case 'admin':
                header("Location: dashboard_admin.php");
                exit();
            default:
                header("Location: index.php");
                exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Créer un compte</title>
    <link rel="stylesheet" href="assets/css/inscription.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="header">
            <h1>Créer un Compte</h1>
            <p>Remplissez vos informations pour vous inscrire</p>
        </div>
        
        <div class="form-container">
            <?php if (!empty($erros)): ?>
                <div class="alert alert-error">
                    <?php foreach ($erros as $erro): ?>
                        <div><?php echo $erro; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nome">Nom Complet</label>
                    <input type="text" id="nome" name="nome" 
                           value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="type_user">Type d'utilisateur</label>
                    <select id="type_user" name="type_user" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="electeur" <?php echo (isset($_POST['type_user']) && $_POST['type_user'] == 'electeur') ? 'selected' : ''; ?>>Électeur</option>
                        <option value="candidat" <?php echo (isset($_POST['type_user']) && $_POST['type_user'] == 'candidat') ? 'selected' : ''; ?>>Candidat</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="senha">Mot de passe</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmer le mot de passe</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                </div>
                
                <button type="submit" class="btn-submit">Créer un compte</button>
            </form>
            
            <div class="footer-text">
                Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a>
            </div>
        </div>
    </div>
</body>

</html>
<?php
$userId = $_GET['id'] ?? 1;
?>

<?php
require_once 'partials/header.php';
require_once 'partials/sidebar.php';
?>

<main class="main-content">
    <header class="admin-header">
        <div class="header-left">
            <h1>Modifier l'Électeur #<?php echo str_pad($userId, 3, '0', STR_PAD_LEFT); ?></h1>
            <p class="subtitle">Mettez à jour les informations de l'électeur</p>
        </div>
        <div class="header-actions">
            <a href="manage-users.php" class="btn">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </header>
    
    <div class="form-container">
        <form id="editUserForm" action="/api/users/<?php echo $userId; ?>" method="POST">
            <input type="hidden" name="_method" value="PUT">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Nom Complet *</label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="Jean Dupont <?php echo $userId; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="jean.dupont<?php echo $userId; ?>@email.com">
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Nouveau Mot de Passe (laisser vide pour ne pas changer)</label>
                <input type="password" id="password" name="password" 
                       placeholder="Laisser vide pour ne pas modifier">
            </div>
            
            <div class="form-group">
                <label for="status">Statut du Compte</label>
                <select id="status" name="status">
                    <option value="active" selected>Actif</option>
                    <option value="inactive">Inactif</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="last_login">Dernière Connexion</label>
                <input type="text" id="last_login" readonly 
                       value="<?php echo date('d/m/Y H:i', strtotime('-'.rand(1,24).' hours')); ?>">
            </div>
            
            <div class="form-group">
                <label for="created_at">Date d'Inscription</label>
                <input type="text" id="created_at" readonly 
                       value="<?php echo date('d/m/Y', strtotime('-'.rand(1,30).' days')); ?>">
            </div>
            
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3">Électeur actif depuis l'inscription.</textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
                <a href="manage-users.php" class="btn">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</main>

<?php require_once 'partials/footer.php'; ?>
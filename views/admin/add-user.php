<?php
require_once 'partials/header.php';
require_once 'partials/sidebar.php';
?>

<main class="main-content">
    <header class="admin-header">
        <div class="header-left">
            <h1>Ajouter un Nouvel Électeur</h1>
            <p class="subtitle">Remplissez les informations du nouvel électeur</p>
        </div>
        <div class="header-actions">
            <a href="manage-users.php" class="btn">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </header>
    
    <div class="form-container">
        <form id="addUserForm" action="/api/users" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Nom Complet *</label>
                    <input type="text" id="full_name" name="full_name" required 
                           placeholder="Ex: Jean Dupont">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Ex: jean.dupont@email.com">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mot de Passe *</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Minimum 8 caractères">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le Mot de Passe *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="status">Statut du Compte</label>
                <select id="status" name="status">
                    <option value="active" selected>Actif</option>
                    <option value="inactive">Inactif</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3" 
                          placeholder="Notes concernant cet électeur..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer l'Électeur
                </button>
                <button type="reset" class="btn">
                    <i class="fas fa-undo"></i> Réinitialiser
                </button>
            </div>
        </form>
    </div>
</main>

<?php require_once 'partials/footer.php'; ?>
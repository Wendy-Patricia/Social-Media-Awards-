<?php
$candidateId = $_GET['id'] ?? 1;
?>

<?php
require_once 'partials/header.php';
require_once 'partials/sidebar.php';
?>

<main class="main-content">
    <header class="admin-header">
        <div class="header-left">
            <h1>Modifier le Candidat #<?php echo str_pad($candidateId, 3, '0', STR_PAD_LEFT); ?></h1>
            <p class="subtitle">Mettez à jour les informations du candidat</p>
        </div>
        <div class="header-actions">
            <a href="manage-candidates.php" class="btn">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </header>
    
    <div class="form-container">
        <form id="editCandidateForm" action="/api/candidates/<?php echo $candidateId; ?>" method="POST">
            <input type="hidden" name="_method" value="PUT">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Nom Complet *</label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="Marie Martin <?php echo $candidateId; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="marie.martin<?php echo $candidateId; ?>@email.com">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category">Catégorie *</label>
                    <select id="category" name="category" required>
                        <option value="influencer" selected>Influenceur de l'Année</option>
                        <option value="content">Meilleur Contenu</option>
                        <option value="campaign">Meilleure Campagne</option>
                        <option value="revelation">Révélation de l'Année</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="platform">Plateforme Principale *</label>
                    <select id="platform" name="platform" required>
                        <option value="instagram" selected>Instagram</option>
                        <option value="tiktok">TikTok</option>
                        <option value="youtube">YouTube</option>
                        <option value="twitter">Twitter</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="social_links">Liens des Réseaux Sociaux</label>
                <textarea id="social_links" name="social_links" rows="4">
{
    "instagram": "https://instagram.com/marie.martin",
    "youtube": "https://youtube.com/@mariemartin",
    "tiktok": "https://tiktok.com/@mariemartin"
}
                </textarea>
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="6" required>
Candidat talentueux avec une grande communauté sur les réseaux sociaux. Créateur de contenu innovant et engageant qui a marqué l'année 2024 par son authenticité et sa créativité.
                </textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="followers_count">Nombre d'Abonnés</label>
                    <input type="number" id="followers_count" name="followers_count" 
                           value="<?php echo rand(10000, 1000000); ?>">
                </div>
                
                <div class="form-group">
                    <label for="engagement_rate">Taux d'Engagement (%)</label>
                    <input type="number" id="engagement_rate" name="engagement_rate" step="0.1"
                           value="<?php echo rand(1, 10) + (rand(0, 9) / 10); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status">
                    <option value="pending">En attente</option>
                    <option value="approved" selected>Approuvé</option>
                    <option value="rejected">Rejeté</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="created_at">Date d'Inscription</label>
                <input type="text" id="created_at" readonly 
                       value="<?php echo date('d/m/Y', strtotime('-'.rand(1,30).' days')); ?>">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
                <a href="manage-candidates.php" class="btn">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</main>

<?php require_once 'partials/footer.php'; ?>
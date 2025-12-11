<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/add-candidate.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Social Media Awards 2025</title>
</head>

<main class="main-content">
    <header class="admin-header">
        <div class="header-left">
            <h1>Ajouter un Nouveau Candidat</h1>
            <p class="subtitle">Remplissez les informations du candidat</p>
        </div>
        <div class="header-actions">
            <a href="manage-candidates.php" class="btn">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </header>
    
    <div class="form-container">
        <form id="addCandidateForm" action="/api/candidates" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Nom Complet *</label>
                    <input type="text" id="full_name" name="full_name" required 
                           placeholder="Ex: Marie Martin">
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Ex: marie.martin@email.com">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category">Catégorie *</label>
                    <select id="category" name="category" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="influencer">Influenceur de l'Année</option>
                        <option value="content">Meilleur Contenu</option>
                        <option value="campaign">Meilleure Campagne</option>
                        <option value="revelation">Révélation de l'Année</option>
                        <option value="podcast">Meilleur Podcast</option>
                        <option value="challenge">Meilleur Challenge Viral</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="platform">Plateforme Principale *</label>
                    <select id="platform" name="platform" required>
                        <option value="">Sélectionner une plateforme</option>
                        <option value="instagram">Instagram</option>
                        <option value="tiktok">TikTok</option>
                        <option value="youtube">YouTube</option>
                        <option value="twitter">Twitter</option>
                        <option value="facebook">Facebook</option>
                        <option value="multi">Multi-plateforme</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="social_links">Liens des Réseaux Sociaux (JSON)</label>
                <textarea id="social_links" name="social_links" rows="4" 
                          placeholder='{"instagram": "https://...", "youtube": "https://..."}'>
                </textarea>
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="6" required 
                          placeholder="Description détaillée du candidat..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="followers_count">Nombre d'Abonnés</label>
                <input type="number" id="followers_count" name="followers_count" 
                       placeholder="Ex: 100000">
            </div>
            
            <div class="form-group">
                <label for="status">Statut Initial</label>
                <select id="status" name="status">
                    <option value="pending" selected>En attente</option>
                    <option value="approved">Approuvé</option>
                    <option value="rejected">Rejeté</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer le Candidat
                </button>
                <button type="reset" class="btn">
                    <i class="fas fa-undo"></i> Réinitialiser
                </button>
            </div>
        </form>
    </div>
</main>

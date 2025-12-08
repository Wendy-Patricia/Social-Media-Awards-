<?php
require_once 'partials/header.php';
require_once 'partials/sidebar.php';
?>

<main class="main-content">
    <header class="admin-header">
        <div class="header-left">
            <h1>Gestion des Candidats</h1>
            <p class="subtitle">Liste de tous les candidats enregistrés</p>
        </div>
        <div class="header-actions">
            <a href="add-candidate.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Ajouter un Candidat
            </a>
        </div>
    </header>
    
    <div class="table-container">
        <div class="table-header">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchCandidates" placeholder="Rechercher des candidats...">
            </div>
            <div class="table-actions">
                <select id="filterCategory">
                    <option value="">Toutes les catégories</option>
                    <option value="influencer">Influenceur</option>
                    <option value="content">Contenu</option>
                    <option value="campaign">Campagne</option>
                    <option value="revelation">Révélation</option>
                </select>
                <select id="filterStatus">
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="approved">Approuvé</option>
                    <option value="rejected">Rejeté</option>
                </select>
            </div>
        </div>
        
        <table class="data-table" id="candidatesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Candidat</th>
                    <th>Catégorie</th>
                    <th>Plateforme</th>
                    <th>Followers</th>
                    <th>Statut Candidature</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $categories = ['Influenceur', 'Contenu', 'Campagne', 'Révélation'];
                $platforms = ['Instagram', 'TikTok', 'YouTube', 'Twitter', 'Multi-plateforme'];
                $statuses = ['pending', 'approved', 'rejected'];
                
                for($i = 1; $i <= 15; $i++): 
                $category = $categories[array_rand($categories)];
                $platform = $platforms[array_rand($platforms)];
                $status = $statuses[array_rand($statuses)];
                $statusText = [
                    'pending' => 'En attente',
                    'approved' => 'Approuvé', 
                    'rejected' => 'Rejeté'
                ][$status];
                ?>
                <tr>
                    <td>#<?php echo str_pad($i, 3, '0', STR_PAD_LEFT); ?></td>
                    <td>
                        <div class="candidate-info">
                            <strong>Marie Martin <?php echo $i; ?></strong>
                            <small>marie.martin<?php echo $i; ?>@email.com</small>
                        </div>
                    </td>
                    <td><?php echo $category; ?></td>
                    <td><?php echo $platform; ?></td>
                    <td><?php echo number_format(rand(1000, 1000000)); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $status; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime('-'.rand(1,30).' days')); ?></td>
                    <td class="action-buttons">
                        <a href="edit-candidate.php?id=<?php echo $i; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-danger" data-id="<?php echo $i; ?>" data-type="candidate">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        
        <div class="table-footer">
            <div class="pagination">
                <button class="btn btn-sm"><i class="fas fa-chevron-left"></i></button>
                <span class="page-info">Page 1 sur 3</span>
                <button class="btn btn-sm"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="items-info">Affichage de 15 sur 86 candidats</div>
        </div>
    </div>
</main>

<?php require_once 'partials/footer.php'; ?>
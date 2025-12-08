<?php
require_once 'partials/header.php';
require_once 'partials/sidebar.php';
?>

<main class="main-content">
    <header class="admin-header">
        <div class="header-left">
            <h1>Modération des Candidatures</h1>
            <p class="subtitle">Approuvez ou rejetez les candidatures soumises</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-secondary" data-export="csv" data-type="candidacies">
                <i class="fas fa-download"></i> Exporter
            </button>
        </div>
    </header>
    
    <div class="table-container">
        <div class="table-header">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchCandidacies" placeholder="Rechercher des candidatures...">
            </div>
            <div class="table-actions">
                <select id="filterCandidacyStatus">
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="reviewed">En revue</option>
                    <option value="approved">Approuvées</option>
                    <option value="rejected">Rejetées</option>
                </select>
                <select id="filterCandidacyCategory">
                    <option value="">Toutes catégories</option>
                    <option value="influencer">Influenceur</option>
                    <option value="content">Contenu</option>
                    <option value="campaign">Campagne</option>
                </select>
            </div>
        </div>
        
        <table class="data-table" id="candidaciesTable">
            <thead>
                <tr>
                    <th>Candidat</th>
                    <th>Catégorie</th>
                    <th>Date Soumission</th>
                    <th>Plateforme</th>
                    <th>Statut</th>
                    <th>Complétude</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php for($i = 1; $i <= 12; $i++): 
                $statuses = ['pending', 'reviewed', 'approved', 'rejected'];
                $status = $statuses[array_rand($statuses)];
                $statusText = [
                    'pending' => 'En attente',
                    'reviewed' => 'En revue',
                    'approved' => 'Approuvé',
                    'rejected' => 'Rejeté'
                ][$status];
                $completeness = rand(50, 100);
                $completenessClass = $completeness >= 80 ? 'complete' : 'incomplete';
                $completenessText = $completeness >= 80 ? 'Complet' : 'Incomplet';
                ?>
                <tr>
                    <td>
                        <div class="candidate-info">
                            <strong>Pierre Lefevre <?php echo $i; ?></strong>
                            <br><small>pierre.lefevre<?php echo $i; ?>@email.com</small>
                        </div>
                    </td>
                    <td>Influenceur de l'Année</td>
                    <td><?php echo date('d/m/Y H:i', strtotime('-'.rand(1,72).' hours')); ?></td>
                    <td>Instagram</td>
                    <td>
                        <span class="status-badge status-<?php echo $status; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo $completenessClass; ?>">
                            <?php echo $completenessText; ?> (<?php echo $completeness; ?>%)
                        </span>
                    </td>
                    <td class="action-buttons">
                        <button class="btn btn-sm btn-info" onclick="viewCandidacy(<?php echo $i; ?>)">
                            <i class="fas fa-eye"></i> Voir
                        </button>
                        
                        <?php if($status == 'pending' || $status == 'reviewed'): ?>
                        <button class="btn btn-sm btn-success" data-status="approved" 
                                data-id="<?php echo $i; ?>" data-type="candidacy">
                            <i class="fas fa-check"></i> Approuver
                        </button>
                        <button class="btn btn-sm btn-danger" data-status="rejected" 
                                data-id="<?php echo $i; ?>" data-type="candidacy">
                            <i class="fas fa-times"></i> Rejeter
                        </button>
                        <?php endif; ?>
                        
                        <?php if($status == 'approved'): ?>
                        <button class="btn btn-sm btn-warning" data-status="pending" 
                                data-id="<?php echo $i; ?>" data-type="candidacy">
                            <i class="fas fa-undo"></i> Réouvrir
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        
        <div class="table-footer">
            <div class="stats-summary">
                <div class="stat-item">
                    <span class="stat-label">Total :</span>
                    <span class="stat-value">86</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">En attente :</span>
                    <span class="stat-value">12</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Approuvées :</span>
                    <span class="stat-value">62</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Rejetées :</span>
                    <span class="stat-value">12</span>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function viewCandidacy(id) {
    // Ouvrir une modale ou rediriger vers la page de détail
    window.location.href = `/candidacy-detail.php?id=${id}`;
}
</script>

<style>
.stats-summary {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.stat-item {
    text-align: center;
    padding: 10px;
}

.stat-label {
    display: block;
    color: var(--gray);
    font-size: 0.9rem;
}

.stat-value {
    display: block;
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--dark);
}

.tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.tag {
    background: var(--light-gray);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    color: var(--dark);
}

.candidate-info small {
    display: block;
    color: var(--gray);
    font-size: 0.85rem;
    margin-top: 2px;
}
</style>

<?php require_once 'partials/footer.php'; ?>
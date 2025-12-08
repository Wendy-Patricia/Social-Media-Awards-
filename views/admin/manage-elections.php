<?php
require_once 'partials/header.php';
require_once 'partials/sidebar.php';
?>

<main class="main-content">
    <header class="admin-header">
        <div class="header-left">
            <h1>Gestion des Élections</h1>
            <p class="subtitle">Créez, ouvrez et fermez les élections</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showElectionForm()">
                <i class="fas fa-plus"></i> Créer une Élection
            </button>
        </div>
    </header>
    
    <div id="electionForm" style="display: none;" class="form-container">
        <h3>Créer une Nouvelle Élection</h3>
        <form id="createElectionForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="election_name">Nom de l'Élection *</label>
                    <input type="text" id="election_name" name="name" required 
                           placeholder="Ex: Social Media Awards 2024">
                </div>
                
                <div class="form-group">
                    <label for="election_year">Année *</label>
                    <input type="number" id="election_year" name="year" required 
                           min="2024" max="2030" value="2024">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="candidature_start">Début des Candidatures *</label>
                    <input type="datetime-local" id="candidature_start" name="candidature_start" required>
                </div>
                
                <div class="form-group">
                    <label for="candidature_end">Fin des Candidatures *</label>
                    <input type="datetime-local" id="candidature_end" name="candidature_end" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="voting_start">Début des Votes *</label>
                    <input type="datetime-local" id="voting_start" name="voting_start" required>
                </div>
                
                <div class="form-group">
                    <label for="voting_end">Fin des Votes *</label>
                    <input type="datetime-local" id="voting_end" name="voting_end" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="categories">Catégories (séparées par des virgules)</label>
                <textarea id="categories" name="categories" rows="3"
                          placeholder="Influenceur, Contenu, Campagne, Révélation, Podcast..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Créer l'Élection
                </button>
                <button type="button" class="btn" onclick="hideElectionForm()">
                    Annuler
                </button>
            </div>
        </form>
    </div>
    
    <div class="table-container">
        <div class="table-header">
            <h3>Élections Existantes</h3>
            <div class="table-actions">
                <select id="filterElectionStatus">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actives</option>
                    <option value="upcoming">À venir</option>
                    <option value="closed">Terminées</option>
                </select>
            </div>
        </div>
        
        <table class="data-table" id="electionsTable">
            <thead>
                <tr>
                    <th>Élection</th>
                    <th>Période Candidatures</th>
                    <th>Période Votes</th>
                    <th>Catégories</th>
                    <th>Statut</th>
                    <th>Candidats</th>
                    <th>Votes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php for($i = 1; $i <= 5; $i++): 
                $statuses = ['active', 'upcoming', 'closed'];
                $status = $statuses[array_rand($statuses)];
                $statusText = [
                    'active' => 'Active',
                    'upcoming' => 'À venir', 
                    'closed' => 'Terminée'
                ][$status];
                ?>
                <tr>
                    <td>
                        <strong>Social Media Awards 202<?php echo $i + 3; ?></strong>
                        <br><small>Édition <?php echo 2020 + $i; ?></small>
                    </td>
                    <td>
                        01/01/2024 - 31/01/2024
                    </td>
                    <td>
                        01/02/2024 - 28/02/2024
                    </td>
                    <td>
                        <span class="tags">
                            <span class="tag">Influenceur</span>
                            <span class="tag">Contenu</span>
                            <span class="tag">Campagne</span>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo $status; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                    <td><?php echo rand(20, 100); ?></td>
                    <td><?php echo number_format(rand(1000, 10000)); ?></td>
                    <td class="action-buttons">
                        <?php if($status == 'upcoming'): ?>
                        <button class="btn btn-sm btn-success" data-status="active" 
                                data-id="<?php echo $i; ?>" data-type="election">
                            <i class="fas fa-play"></i> Ouvrir
                        </button>
                        <?php elseif($status == 'active'): ?>
                        <button class="btn btn-sm btn-warning" data-status="closed" 
                                data-id="<?php echo $i; ?>" data-type="election">
                            <i class="fas fa-stop"></i> Fermer
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-danger" data-id="<?php echo $i; ?>" data-type="election">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function showElectionForm() {
    document.getElementById('electionForm').style.display = 'block';
}

function hideElectionForm() {
    document.getElementById('electionForm').style.display = 'none';
}
</script>

<?php require_once 'partials/footer.php'; ?>
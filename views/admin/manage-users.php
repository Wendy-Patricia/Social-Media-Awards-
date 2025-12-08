<?php
require_once 'partials/header.php';
require_once 'partials/sidebar.php';
?>

<main class="main-content">
    <header class="admin-header">
        <div class="header-left">
            <h1>Gestion des Électeurs</h1>
            <p class="subtitle">Liste de tous les électeurs enregistrés dans le système</p>
        </div>
        <div class="header-actions">
            <a href="add-user.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Ajouter un Électeur
            </a>
            <button class="btn btn-secondary" data-export="csv" data-type="users">
                <i class="fas fa-download"></i> Exporter CSV
            </button>
        </div>
    </header>
    
    <div class="table-container">
        <div class="table-header">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchUsers" placeholder="Rechercher des électeurs...">
            </div>
            <div class="table-actions">
                <select id="filterStatus">
                    <option value="">Tous les statuts</option>
                    <option value="active">Actifs</option>
                    <option value="inactive">Inactifs</option>
                </select>
            </div>
        </div>
        
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Date d'Inscription</th>
                    <th>Dernière Connexion</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php for($i = 1; $i <= 15; $i++): ?>
                <?php
                $status = rand(0,1) ? 'active' : 'inactive';
                $statusText = $status == 'active' ? 'Actif' : 'Inactif';
                ?>
                <tr>
                    <td>#<?php echo str_pad($i, 3, '0', STR_PAD_LEFT); ?></td>
                    <td>Jean Dupont <?php echo $i; ?></td>
                    <td>jean.dupont<?php echo $i; ?>@email.com</td>
                    <td><?php echo date('d/m/Y', strtotime('-'.rand(1,30).' days')); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime('-'.rand(1,24).' hours')); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $status; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                    <td class="action-buttons">
                        <a href="edit-user.php?id=<?php echo $i; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <button class="btn btn-sm btn-danger" data-id="<?php echo $i; ?>" data-type="user">
                            <i class="fas fa-trash"></i> Supprimer
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
            <div class="items-info">Affichage de 15 sur 1,245 électeurs</div>
        </div>
    </div>
</main>

<?php require_once 'partials/footer.php'; ?>
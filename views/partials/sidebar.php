<aside class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-crown"></i> SMA Admin</h2>
        <p>Social Media Awards</p>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Tableau de Bord
                </a>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Gestion des Électeurs</span>
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-users.php' ? 'active' : ''; ?>">
                        <a href="manage-users.php">
                            <i class="fas fa-users"></i> Liste des Électeurs
                        </a>
                    </li>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'add-user.php' ? 'active' : ''; ?>">
                        <a href="add-user.php">
                            <i class="fas fa-user-plus"></i> Ajouter un Électeur
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Gestion des Candidats</span>
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-candidates.php' ? 'active' : ''; ?>">
                        <a href="manage-candidates.php">
                            <i class="fas fa-user-tie"></i> Liste des Candidats
                        </a>
                    </li>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'add-candidate.php' ? 'active' : ''; ?>">
                        <a href="add-candidate.php">
                            <i class="fas fa-user-plus"></i> Ajouter un Candidat
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Élections</span>
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-elections.php' ? 'active' : ''; ?>">
                        <a href="manage-elections.php">
                            <i class="fas fa-vote-yea"></i> Gérer les Élections
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Modération</span>
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'moderate-candidacies.php' ? 'active' : ''; ?>">
                        <a href="moderate-candidacies.php">
                            <i class="fas fa-gavel"></i> Candidatures
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="logout">
                <a href="/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </li>
        </ul>
    </nav>
</aside>
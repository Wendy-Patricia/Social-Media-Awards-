<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-crown"></i> Social Media Awards</h2>
        <p>Admin Panel</p>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="/Social-Media-Awards-/views/admin/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Tableau de Bord
                </a>
            </li>

            <li class="nav-section">
                <span class="section-title">Gestion des Editions</span>
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'gerer-editions.php' ? 'active' : ''; ?>">
                        <a href="/Social-Media-Awards-/views/admin/editions/gerer-editions.php">
                            <i class="fas fa-users"></i> Liste des Editions
                        </a>
                    </li>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'ajouter-edition.php' ? 'active' : ''; ?>">
                        <a href="/Social-Media-Awards-/views/admin/editions/ajouter-edition.php">
                            <i class="fas fa-user-plus"></i> Ajouter une Edition
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-section">
                <span class="section-title">Gestion des Catégories</span>
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'gerer-categories.php' ? 'active' : ''; ?>">
                        <a href="/Social-Media-Awards-/views/admin/categories/gerer-categories.php">
                            <i class="fas fa-tags"></i> Liste des catégories
                        </a>
                    </li>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'ajouter-category.php' ? 'active' : ''; ?>">
                        <a href="/Social-Media-Awards-/views/admin/categories/ajouter-categorie.php">
                            <i class="fas fa-plus-circle"></i> Ajouter une catégorie
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-section">
                <span class="section-title">Gestion des Nominations</span>
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-nominations.php' ? 'active' : ''; ?>">
                        <a href="/Social-Media-Awards-/views/admin/nominations/manage-nominations.php">
                            <i class="fas fa-user-tie"></i> Liste des Nomination
                        </a>
                    </li>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'edit-nomination.php' ? 'active' : ''; ?>">
                        <a href="/Social-Media-Awards-/views/admin/nominations/edit-nomination.php">
                            <i class="fas fa-user-plus"></i> Editer un Nominee
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
                <span class="section-title">Gestion des Candidatures</span>
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-applications.php' ? 'active' : ''; ?>">
                        <a href="/Social-Media-Awards-/views/admin/candidatures/manage-candidatures.php">
                            <i class="fas fa-file-alt"></i> Toutes les Candidatures
                        </a>
                    </li>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'view-candidatures.php' ? 'active' : ''; ?>">
                        <a href="/Social-Media-Awards-/views/admin/candidatures/view-candidatures.php/">
                            <i class="fas fa-plus-circle"></i> Visualizer les Candidatures
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
                <a href="/Social-Media-Awards-/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </li>
        </ul>
    </nav>
</aside>
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
                <span class="section-title">Gestion des Candidatures</span>
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-application.php' ? 'active' : ''; ?>">
                        <a href="/Social-Media-Awards-/views/admin/candidatures/manage-candidature.php">
                             <i class="fas fa-gavel"></i> Candidatures
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
                </ul>
            </li>


            <li class="nav-section">
                <span class="section-title">Resultats</span>
                <ul>
                    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'active' : ''; ?>">
                        <a href="/Social-Media-Awards-/results.php">
                            <i class="fas fa-vote-yea"></i> Resultats
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
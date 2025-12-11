<link rel="stylesheet" href="../../assets/css/admin-header.css">

<header class="admin-header">
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="header-left">
        <h1><?php echo $page_title ?? 'Tableau de Bord Administratif'; ?></h1>
        <p class="subtitle">Social Media Awards Administration</p>
    </div>
    
    <div class="admin-profile">
        <img src="../assets/images/admin-avatar.png" alt="Administrateur">
        <span>Administrateur</span>
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</header>

<!-- Overlay para mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

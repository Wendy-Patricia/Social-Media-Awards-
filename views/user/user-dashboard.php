<?php
// views/user/user-dashboard.php
require_once '../../config/session.php';
require_once __DIR__ . '/../../app/Models/User.php';
require_once __DIR__ . '/../../app/Models/Vote.php';
require_once __DIR__ . '/../../app/Models/CategoryModel.php';

// Verificar autenticação e tipo de usuário usando requireRole
requireRole('voter');

// Obter dados do usuário da sessão
$userId = $_SESSION['user_id'] ?? null;
$userPseudonyme = $_SESSION['user_pseudonyme'] ?? 'Électeur';
$userEmail = $_SESSION['user_email'] ?? 'Non défini';

// Instanciar modelos
$userModel = new User();
$voteModel = new Vote();
$categoryModel = new CategoryModel();

// Obter dados reais do banco de dados
$userData = $userModel->getUserById($userId);

// Estatísticas do usuário
$votesCount = $voteModel->getUserVotesCount($userId);
$activeElections = $categoryModel->getActiveCategoriesCount();
$categories = $categoryModel->getAllCategoriesWithNominations();
$availableCategories = $categoryModel->getVotingCategoriesForUser($userId);

// Verificar se o usuário já votou em categorias ativas
$hasVotedInActiveCategories = $voteModel->hasUserVotedInActiveCategories($userId);

// Obter iniciais para o avatar
$initials = strtoupper(substr($userPseudonyme, 0, 2));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Électeur - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/user-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="/Social-Media-Awards-/assets/images/logo.png" alt="Logo Social Media Awards" class="logo-image">
                <h1>Social Media <span class="highlight">Awards</span></h1>
            </div>
            
            <nav class="user-nav">
                <div class="user-info-nav">
                    <div class="avatar-nav"><?php echo $initials; ?></div>
                    <div class="user-details-nav">
                        <span class="user-name-nav"><?php echo htmlspecialchars($userPseudonyme); ?></span>
                        <span class="user-role-nav">Électeur</span>
                    </div>
                </div>
                
                <a href="/Social-Media-Awards-/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </nav>
        </div>
    </header>

    <main class="dashboard-container">
        <!-- Sidebar de Navegação -->
        <aside class="dashboard-sidebar">
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Tableau de Bord</span>
                </a>
                <a href="/Social-Media-Awards-/views/user/Vote.php" class="nav-item">
                    <i class="fas fa-vote-yea"></i>
                    <span>Voter Maintenant</span>
                </a>
                <a href="/Social-Media-Awards-/categories.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Catégories</span>
                </a>
                <a href="/Social-Media-Awards-/nominees.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Candidats</span>
                </a>
                <a href="/Social-Media-Awards-/views/user/edit-profile.php" class="nav-item">
                    <i class="fas fa-user-edit"></i>
                    <span>Mon Profil</span>
                </a>
                <a href="/Social-Media-Awards-/results.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Résultats</span>
                </a>
            </nav>
        </aside>

        <div class="dashboard-main">
            <!-- Hero Section -->
            <section class="hero-section">
                <div class="hero-content">
                    <div class="hero-text">
                        <h1>Bonjour, <?php echo htmlspecialchars($userPseudonyme); ?>!</h1>
                        <p>Votre espace personnel pour participer aux Social Media Awards.</p>
                        <div class="hero-actions">
                            <a href="/Social-Media-Awards-/views/user/Vote.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-vote-yea"></i>
                                Commencer à Voter
                            </a>
                            <a href="/Social-Media-Awards-/categories.php" class="btn btn-outline btn-lg">
                                <i class="fas fa-search"></i>
                                Explorer Catégories
                            </a>
                        </div>
                    </div>
                    <div class="hero-stats">
                        <div class="stat-card">
                            <i class="fas fa-vote-yea"></i>
                            <div class="stat-content">
                                <h3><?php echo $votesCount; ?></h3>
                                <p>Votes émis</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-trophy"></i>
                            <div class="stat-content">
                                <h3><?php echo $activeElections; ?></h3>
                                <p>Élections actives</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-medal"></i>
                            <div class="stat-content">
                                <h3><?php echo count($availableCategories); ?></h3>
                                <p>Catégories disponibles</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section de Votação -->
            <section class="voting-section">
                <div class="section-header">
                    <h2><i class="fas fa-vote-yea"></i> Votre État de Vote</h2>
                </div>
                
                <div class="voting-grid">
                    <!-- Status de Voto -->
                    <div class="status-card <?php echo $hasVotedInActiveCategories ? 'voted' : 'not-voted'; ?>">
                        <div class="status-icon">
                            <i class="fas <?php echo $hasVotedInActiveCategories ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                        </div>
                        <div class="status-content">
                            <h3><?php echo $hasVotedInActiveCategories ? 'Participation en cours' : 'Prêt à voter'; ?></h3>
                            <p><?php echo $hasVotedInActiveCategories ? 
                                'Vous avez déjà voté dans certaines catégories. Continuez!' : 
                                'Aucun vote émis. Commencez maintenant!'; ?></p>
                        </div>
                        <a href="/Social-Media-Awards-/views/user/Vote.php" class="btn btn-primary">
                            <?php echo $hasVotedInActiveCategories ? 'Continuer à Voter' : 'Voter Maintenant'; ?>
                        </a>
                    </div>

                    <!-- Progresso do Voto -->
                    <div class="progress-card">
                        <h3>Votre Progression</h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(100, ($votesCount / max(1, count($availableCategories))) * 100); ?>%"></div>
                        </div>
                        <div class="progress-stats">
                            <span><?php echo $votesCount; ?> / <?php echo count($availableCategories); ?> catégories</span>
                            <span><?php echo round(($votesCount / max(1, count($availableCategories))) * 100); ?>%</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Catégories Disponíveis pour Votação -->
            <section class="categories-section">
                <div class="section-header">
                    <h2><i class="fas fa-star"></i> Catégories à Voter</h2>
                    <a href="/Social-Media-Awards-/categories.php" class="btn btn-outline">
                        Voir toutes
                    </a>
                </div>
                
                <div class="categories-grid">
                    <?php if (!empty($availableCategories)): ?>
                        <?php foreach ($availableCategories as $category): 
                            $hasVoted = $voteModel->hasUserVotedInCategory($userId, $category['id_categorie']);
                        ?>
                            <div class="category-card <?php echo $hasVoted ? 'voted' : ''; ?>">
                                <div class="category-header">
                                    <div class="category-icon">
                                        <?php 
                                        $icons = [
                                            'Photographe' => 'fa-camera',
                                            'Streamer' => 'fa-gamepad',
                                            'Musicien' => 'fa-music',
                                            'Youtuber' => 'fa-youtube',
                                            'Instagram' => 'fa-instagram',
                                            'TikTok' => 'fa-tiktok'
                                        ];
                                        $icon = $icons[$category['plateforme_cible']] ?? 'fa-tag';
                                        ?>
                                        <i class="fas <?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="category-info">
                                        <h4><?php echo htmlspecialchars($category['nom']); ?></h4>
                                        <span class="platform"><?php echo $category['plateforme_cible']; ?></span>
                                    </div>
                                    <?php if ($hasVoted): ?>
                                        <span class="vote-badge voted"><i class="fas fa-check"></i> Voté</span>
                                    <?php else: ?>
                                        <span class="vote-badge available"><i class="fas fa-vote-yea"></i> Disponible</span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="category-desc"><?php echo htmlspecialchars(substr($category['description'], 0, 100)) . '...'; ?></p>
                                
                                <div class="category-stats">
                                    <div class="stat">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $category['nomination_count'] ?? 0; ?> candidats</span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-clock"></i>
                                        <span>Fin: <?php echo date('d/m/Y', strtotime($category['date_fin_votes'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="category-actions">
                                    <?php if (!$hasVoted): ?>
                                        <a href="/Social-Media-Awards-/views/user/Vote.php?category=<?php echo $category['id_categorie']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-vote-yea"></i>
                                            Voter
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-disabled btn-sm" disabled>
                                            <i class="fas fa-check"></i>
                                            Déjà voté
                                        </button>
                                    <?php endif; ?>
                                    <a href="/Social-Media-Awards-/nominees.php?category=<?php echo $category['id_categorie']; ?>" class="btn btn-outline btn-sm">
                                        Voir candidats
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>Aucune catégorie disponible</h3>
                            <p>Il n'y a pas d'élections actives pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Élections Actives -->
            <section class="elections-section">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-alt"></i> Élections Actives</h2>
                </div>
                
                <div class="elections-list">
                    <?php
                    // Agrupar categorias por edição
                    $editions = [];
                    foreach ($categories as $category) {
                        if (!isset($editions[$category['edition_nom']])) {
                            $editions[$category['edition_nom']] = [
                                'nom' => $category['edition_nom'],
                                'annee' => $category['edition_annee'],
                                'categories' => [],
                                'date_fin' => $category['date_fin']
                            ];
                        }
                        $editions[$category['edition_nom']]['categories'][] = $category;
                    }
                    ?>
                    
                    <?php foreach ($editions as $edition): ?>
                        <div class="election-card">
                            <div class="election-header">
                                <h3><?php echo htmlspecialchars($edition['nom']); ?> <?php echo $edition['annee']; ?></h3>
                                <span class="election-status active">En cours</span>
                            </div>
                            
                            <div class="election-body">
                                <p class="election-desc">
                                    <?php echo count($edition['categories']); ?> catégories disponibles
                                </p>
                                
                                <div class="election-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-hourglass-end"></i>
                                        <span>Clôture: <?php echo date('d/m/Y', strtotime($edition['date_fin'])); ?></span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo array_sum(array_column($edition['categories'], 'nomination_count')); ?> candidats</span>
                                    </div>
                                </div>
                                
                                <div class="election-categories">
                                    <?php foreach (array_slice($edition['categories'], 0, 3) as $category): ?>
                                        <span class="category-tag"><?php echo $category['nom']; ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($edition['categories']) > 3): ?>
                                        <span class="category-tag more">+<?php echo count($edition['categories']) - 3; ?> autres</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="election-footer">
                                <a href="/Social-Media-Awards-/views/user/Vote.php?edition=<?php echo $edition['annee']; ?>" class="btn btn-primary">
                                    <i class="fas fa-play"></i>
                                    Participer
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Derniers Votes -->
            <section class="recent-votes">
                <div class="section-header">
                    <h2><i class="fas fa-history"></i> Vos Derniers Votes</h2>
                </div>
                
                <div class="votes-list">
                    <?php
                    $recentVotes = $voteModel->getUserRecentVotes($userId, 3);
                    if (!empty($recentVotes)):
                        foreach ($recentVotes as $vote):
                    ?>
                        <div class="vote-item">
                            <div class="vote-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="vote-info">
                                <h4><?php echo htmlspecialchars($vote['category_nom']); ?></h4>
                                <p class="vote-detail">
                                    Vote pour: <strong><?php echo htmlspecialchars($vote['nomination_libelle']); ?></strong>
                                </p>
                                <span class="vote-date">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($vote['date_heure_vote'])); ?>
                                </span>
                            </div>
                            <div class="vote-actions">
                                <a href="/Social-Media-Awards-/nominees.php?category=<?php echo $vote['id_categorie']; ?>" class="btn btn-outline btn-sm">
                                    Voir catégorie
                                </a>
                            </div>
                        </div>
                    <?php
                        endforeach;
                    else:
                    ?>
                        <div class="empty-state">
                            <i class="fas fa-vote-yea"></i>
                            <h3>Aucun vote enregistré</h3>
                            <p>Commencez à voter pour voir votre historique ici.</p>
                            <a href="/Social-Media-Awards-/views/user/Vote.php" class="btn btn-primary mt-2">
                                Voter maintenant
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <div class="footer-content">
            <div class="footer-links">
                <a href="/Social-Media-Awards-/categories.php">Catégories</a>
                <a href="/Social-Media-Awards-/nominees.php">Candidats</a>
                <a href="/Social-Media-Awards-/results.php">Résultats</a>
                <a href="/Social-Media-Awards-/contact.php">Contact</a>
                <a href="/Social-Media-Awards-/about.php">À propos</a>
                <a href="/Social-Media-Awards-/faq.php">FAQ</a>
            </div>
            <div class="copyright">
                &copy; 2024 Social Media Awards. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script src="/Social-Media-Awards-/assets/js/user-dashboard.js"></script>
</body>
</html>
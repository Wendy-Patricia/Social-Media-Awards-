<?php
// views/user/user-dashboard.php
require_once '../../config/session.php';
require_once __DIR__ . '/../../app/Models/UserModel.php';
require_once __DIR__ . '/../../app/Models/VoteModel.php';
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
$activeElections = $categoryModel->getActiveCategoriesCount() ?? 0;
$categories = $categoryModel->getAllCategoriesWithNominations() ?? [];
$availableCategories = $categoryModel->getVotingCategoriesForUser($userId) ?? [];

// Verificar se o usuário já votou em categorias ativas
$hasVotedInActiveCategories = $voteModel->hasUserVotedInActiveCategories($userId) ?? false;

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
    <style>
        /* Estilos para evitar erros visuais */
        .progress-fill {
            width: <?php echo min(100, max(0, ($votesCount / max(1, count($availableCategories))) * 100)); ?>% !important;
        }

        .category-card {
            transition: all 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            background: #f9f9f9;
            border-radius: 10px;
            margin: 20px 0;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 15px;
        }

        .mt-2 {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="/Social-Media-Awards-/assets/images/logo.png" alt="Logo Social Media Awards" class="logo-image" onerror="this.style.display='none'">
                <h1>Social Media <span class="highlight">Awards</span></h1>
            </div>

            <nav class="user-nav">
                <div class="user-info-nav">
                    <div class="avatar-nav"><?php echo htmlspecialchars($initials); ?></div>
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
                                <h3><?php echo htmlspecialchars($votesCount); ?></h3>
                                <p>Votes émis</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-trophy"></i>
                            <div class="stat-content">
                                <h3><?php echo htmlspecialchars($activeElections); ?></h3>
                                <p>Élections actives</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-medal"></i>
                            <div class="stat-content">
                                <h3><?php echo htmlspecialchars(count($availableCategories)); ?></h3>
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
                            <div class="progress-fill"></div>
                        </div>
                        <div class="progress-stats">
                            <span><?php echo htmlspecialchars($votesCount); ?> / <?php echo htmlspecialchars(count($availableCategories)); ?> catégories</span>
                            <span><?php echo round(($votesCount / max(1, count($availableCategories))) * 100); ?>%</span>
                        </div>
                    </div>
                </div>
            </section>


            <!-- Catégories Disponíveis pour Votação -->
            <section class="categories-section">
                <div class="section-header">
                    <h2><i class="fas fa-star"></i> Catégories à Voter</h2>
                    <a href="/Social-Media-Awards-/views/user/Vote.php" class="btn btn-outline">
                        Voir toutes
                    </a>
                </div>

                <div class="categories-grid">
                    <?php if (!empty($availableCategories)): ?>
                        <?php foreach ($availableCategories as $category):
                            // CORREÇÃO: Usar métodos corretos
                            $hasVoted = $voteModel->hasUserVotedInCategory($userId, $category['id_categorie']);
                            $categoryId = $category['id_categorie'] ?? 0;
                            $categoryName = $category['nom'] ?? 'Catégorie sans nom';
                            $platform = $category['plateforme_cible'] ?? 'Général';
                            $description = $category['description'] ?? '';
                            $nominationCount = $category['nomination_count'] ?? 0;
                            $dateFin = $category['date_fin_votes'] ?? '';

                            // CORREÇÃO: Determinar status corretamente
                            $canVote = !$hasVoted && $nominationCount > 0;
                            $isActive = true; // Já são categorias ativas

                            // DEBUG para verificar valores
                            error_log("Dashboard - Categoria {$categoryId}:");
                            error_log("  - Nome: {$categoryName}");
                            error_log("  - HasVoted: " . ($hasVoted ? 'true' : 'false'));
                            error_log("  - NominationCount: {$nominationCount}");
                            error_log("  - CanVote: " . ($canVote ? 'true' : 'false'));
                            error_log("  - IsActive: " . ($isActive ? 'true' : 'false'));
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
                                            'TikTok' => 'fa-tiktok',
                                            'YouTube' => 'fa-youtube',
                                            'Twitter' => 'fa-twitter',
                                            'Facebook' => 'fa-facebook',
                                            'Twitch' => 'fa-twitch',
                                            'Spotify' => 'fa-spotify'
                                        ];
                                        $icon = $icons[$platform] ?? 'fa-tag';
                                        ?>
                                        <i class="fas <?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="category-info">
                                        <h4><?php echo htmlspecialchars($categoryName); ?></h4>
                                        <span class="platform"><?php echo htmlspecialchars($platform); ?></span>
                                    </div>
                                    <?php if ($hasVoted): ?>
                                        <span class="vote-badge voted"><i class="fas fa-check"></i> Voté</span>
                                    <?php elseif ($canVote): ?>
                                        <span class="vote-badge available"><i class="fas fa-vote-yea"></i> Disponible</span>
                                    <?php elseif ($nominationCount == 0): ?>
                                        <span class="vote-badge no-nominations"><i class="fas fa-users-slash"></i> Pas de nominés</span>
                                    <?php else: ?>
                                        <span class="vote-badge inactive"><i class="fas fa-info-circle"></i> Indisponible</span>
                                    <?php endif; ?>
                                </div>

                                <p class="category-desc">
                                    <?php echo htmlspecialchars(mb_strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description); ?>
                                </p>

                                <div class="category-stats">
                                    <div class="stat">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo htmlspecialchars($nominationCount); ?> candidats</span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-clock"></i>
                                        <span>Fin: <?php echo $dateFin ? date('d/m/Y', strtotime($dateFin)) : 'Non définie'; ?></span>
                                    </div>
                                </div>

                                <div class="category-actions">
                                    <?php if ($canVote && $categoryId > 0): ?>
                                        <a href="/Social-Media-Awards-/views/user/Vote.php?category_id=<?php echo $categoryId; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-vote-yea"></i>
                                            Voter maintenant
                                        </a>
                                    <?php elseif ($hasVoted): ?>
                                        <button class="btn btn-success btn-sm" disabled>
                                            <i class="fas fa-check"></i>
                                            Déjà voté
                                        </button>
                                    <?php elseif ($nominationCount == 0): ?>
                                        <button class="btn btn-warning btn-sm" disabled>
                                            <i class="fas fa-users-slash"></i>
                                            Pas de nominés
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-disabled btn-sm" disabled>
                                            <i class="fas fa-clock"></i>
                                            Indisponible
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($categoryId > 0): ?>
                                        <a href="/Social-Media-Awards-/nominees.php?category=<?php echo $categoryId; ?>" class="btn btn-outline btn-sm">
                                            Voir candidats
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>Aucune catégorie disponible</h3>
                            <p>Il n'y a pas d'élections actives pour le moment.</p>
                            <p><small>Vérifiez si des catégories ont été créées avec des dates de vote valides.</small></p>
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
                        if (isset($category['edition_nom']) && isset($category['edition_annee'])) {
                            $editionKey = $category['edition_nom'] . '_' . $category['edition_annee'];
                            if (!isset($editions[$editionKey])) {
                                $editions[$editionKey] = [
                                    'nom' => $category['edition_nom'] ?? 'Édition',
                                    'annee' => $category['edition_annee'] ?? date('Y'),
                                    'categories' => [],
                                    'date_fin' => $category['date_fin'] ?? ''
                                ];
                            }
                            $editions[$editionKey]['categories'][] = $category;
                        }
                    }
                    ?>

                    <?php if (!empty($editions)): ?>
                        <?php foreach ($editions as $editionKey => $edition): ?>
                            <div class="election-card">
                                <div class="election-header">
                                    <h3><?php echo htmlspecialchars($edition['nom']); ?> <?php echo htmlspecialchars($edition['annee']); ?></h3>
                                    <span class="election-status active">En cours</span>
                                </div>

                                <div class="election-body">
                                    <p class="election-desc">
                                        <?php echo htmlspecialchars(count($edition['categories'])); ?> catégories disponibles
                                    </p>

                                    <div class="election-stats">
                                        <div class="stat-item">
                                            <i class="fas fa-hourglass-end"></i>
                                            <span>Clôture: <?php echo $edition['date_fin'] ? date('d/m/Y', strtotime($edition['date_fin'])) : 'Non définie'; ?></span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-users"></i>
                                            <span>
                                                <?php
                                                $totalCandidates = 0;
                                                foreach ($edition['categories'] as $cat) {
                                                    $totalCandidates += $cat['nomination_count'] ?? 0;
                                                }
                                                echo htmlspecialchars($totalCandidates);
                                                ?>
                                                candidats
                                            </span>
                                        </div>
                                    </div>

                                    <div class="election-categories">
                                        <?php foreach (array_slice($edition['categories'], 0, 3) as $category): ?>
                                            <span class="category-tag"><?php echo htmlspecialchars($category['nom'] ?? 'Catégorie'); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($edition['categories']) > 3): ?>
                                            <span class="category-tag more">+<?php echo count($edition['categories']) - 3; ?> autres</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="election-footer">
                                    <a href="/Social-Media-Awards-/views/user/Vote.php" class="btn btn-primary">
                                        <i class="fas fa-play"></i>
                                        Participer
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-alt"></i>
                            <h3>Aucune élection active</h3>
                            <p>Aucune édition avec des catégories actives n'a été trouvée.</p>
                        </div>
                    <?php endif; ?>
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
                            $categoryId = $vote['id_categorie'] ?? 0;
                            $categoryName = $vote['category_nom'] ?? 'Catégorie inconnue';
                            $nominationName = $vote['nomination_libelle'] ?? 'Nomination inconnue';
                            $voteDate = $vote['date_heure_vote'] ?? '';
                    ?>
                            <div class="vote-item">
                                <div class="vote-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="vote-info">
                                    <h4><?php echo htmlspecialchars($categoryName); ?></h4>
                                    <p class="vote-detail">
                                        Vote pour: <strong><?php echo htmlspecialchars($nominationName); ?></strong>
                                    </p>
                                    <span class="vote-date">
                                        <i class="fas fa-clock"></i>
                                        <?php echo $voteDate ? date('d/m/Y H:i', strtotime($voteDate)) : 'Date inconnue'; ?>
                                    </span>
                                </div>
                                <?php if ($categoryId > 0): ?>
                                    <div class="vote-actions">
                                        <a href="/Social-Media-Awards-/nominees.php?category=<?php echo $categoryId; ?>" class="btn btn-outline btn-sm">
                                            Voir catégorie
                                        </a>
                                    </div>
                                <?php endif; ?>
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
                &copy; <?php echo date('Y'); ?> Social Media Awards. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script src="/Social-Media-Awards-/assets/js/user-dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animações para as cartas
            const cards = document.querySelectorAll('.category-card, .election-card, .vote-item');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Efeito hover nas cartas
            document.querySelectorAll('.category-card:not(.voted) .btn-primary').forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                });

                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            // Verificação de sessão
            setInterval(() => {
                fetch('/Social-Media-Awards-/views/check_session.php')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.authenticated) {
                            window.location.href = '/Social-Media-Awards-/login.php';
                        }
                    })
                    .catch(() => {
                        console.log('Erreur de vérification de session');
                    });
            }, 300000); // 5 minutes
        });
    </script>
</body>

</html>
<?php
// views/user/Vote.php - VERSÃO ATUALIZADA COM CHARTE GRAPHIQUE
require_once '../../config/session.php';

// Verificar autenticação
requireRole('voter');

// Inicializar controlador
require_once '../../app/Controllers/VoteController.php';
$voteController = new VoteController();

// Obter dados para a página
$pageData = $voteController->showVotingPage();

// Verificar se está em processo de votação específico
$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$viewResults = isset($_GET['view']) && $_GET['view'] === 'results';
$nominations = [];
$currentCategory = null;

if ($categoryId && $categoryId > 0) {
    // Obter nomeações para esta categoria
    require_once '../../app/Models/VoteModel.php';
    $voteModel = new Vote();
    $nominations = $voteModel->getNominationsForCategory($categoryId);
    $currentCategory = $voteModel->getCategoryInfo($categoryId);

    // Se não encontrar categoria, redirecionar
    if (!$currentCategory) {
        header('Location: vote.php?error=invalid_category');
        exit();
    }
}

// Processar voto se formulário enviado
$error = null;
$success = false;
$successMessage = null;
$alreadyVoted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'start_voting' && isset($_POST['category_id'])) {
        $result = $voteController->startCategoryVoting();

        if ($result['success']) {
            // Redirecionar para a página de votação
            header('Location: vote.php?category_id=' . $_POST['category_id']);
            exit();
        } else {
            $error = $result['message'];
            $alreadyVoted = $result['already_voted'] ?? false;
        }
    } elseif ($_POST['action'] === 'cast_vote' && isset($_POST['nomination_id'])) {
        $result = $voteController->castVote();

        if ($result['success']) {
            $_SESSION['vote_success'] = true;
            $_SESSION['vote_message'] = $result['message'];
            $_SESSION['last_vote_details'] = [
                'vote_id' => $result['vote_id'] ?? null,
                'category_id' => $_POST['category_id'] ?? null
            ];
            header('Location: vote.php?success=1&category_id=' . ($_POST['category_id'] ?? ''));
            exit();
        } else {
            $error = $result['message'];
            $alreadyVoted = $result['already_voted'] ?? false;

            // Se já votou, redirecionar para evitar mensagem de sucesso antiga
            if ($alreadyVoted) {
                header('Location: vote.php?error=already_voted&category_id=' . ($_POST['category_id'] ?? ''));
                exit();
            }
        }
    }
}

// Verificar se há mensagem de sucesso
if (isset($_GET['success']) || isset($_SESSION['vote_success'])) {
    $success = true;
    $successMessage = $_SESSION['vote_message'] ?? 'Votre vote a été enregistré avec succès!';

    // Limpar mensagens da sessão
    if (isset($_SESSION['vote_success'])) {
        unset($_SESSION['vote_success']);
        unset($_SESSION['vote_message']);
    }
}

// Verificar erros da URL
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_category':
            $error = 'Catégorie invalide ou non trouvée.';
            break;
        case 'already_voted':
            $error = 'Vous avez déjà voté dans cette catégorie.';
            $alreadyVoted = true;
            break;
        case 'voting_closed':
            $error = 'Les votes sont fermés pour cette catégorie.';
            break;
        default:
            $error = 'Une erreur est survenue.';
    }
}

$initials = strtoupper(substr($_SESSION['user_pseudonyme'], 0, 2));
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Voter - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/user-dashboard.css">
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/vote.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Apenas o estilo necessário para o progresso */
        .progress-fill {
            width: <?php
                    $votedCount = 0;
                    $totalAvailable = 0;
                    foreach ($pageData['available_categories'] as $cat) {
                        if ($cat['has_voted']) $votedCount++;
                        if ($cat['can_vote']) $totalAvailable++;
                    }
                    $percentage = $totalAvailable > 0 ? min(100, max(0, ($votedCount / $totalAvailable) * 100)) : 0;
                    echo $percentage;
                    ?>% !important;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="/Social-Media-Awards-/assets/images/logo.png" alt="Social Media Awards" class="logo-image">
                <h1>Social Media <span class="highlight">Awards</span></h1>
            </div>

            <nav class="user-nav">
                <div class="user-info-nav">
                    <div class="avatar-nav"><?php echo $initials; ?></div>
                    <div class="user-details-nav">
                        <span class="user-name-nav"><?php echo htmlspecialchars($_SESSION['user_pseudonyme']); ?></span>
                        <span class="user-role-nav">Électeur</span>
                    </div>
                </div>

                <a href="user-dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Retour au dashboard
                </a>
            </nav>
        </div>
    </header>

    <main class="dashboard-container">
        <div class="dashboard-main">
            <!-- Mensagens -->
            <?php if ($success && $successMessage): ?>
                <div class="voting-alert alert-success">
                    <i class="fas fa-check-circle fa-2x"></i>
                    <div>
                        <strong>Succès!</strong>
                        <p><?php echo htmlspecialchars($successMessage); ?></p>
                        <?php if (isset($_SESSION['last_vote_details']) && $_SESSION['last_vote_details']['vote_id']): ?>
                            <p class="vote-details">
                                <i class="fas fa-fingerprint"></i>
                                ID de vote: <code><?php echo $_SESSION['last_vote_details']['vote_id']; ?></code>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <?php if ($alreadyVoted): ?>
                    <div class="already-voted-alert">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                        <div>
                            <strong>Attention</strong>
                            <p><?php echo htmlspecialchars($error); ?></p>
                            <p><small>Vous ne pouvez voter qu'une seule fois par catégorie.</small></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="voting-alert alert-error">
                        <i class="fas fa-exclamation-circle fa-2x"></i>
                        <div>
                            <strong>Erreur</strong>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Seção Principal -->
            <div class="vote-page-container">
                <?php if (!$categoryId): ?>
                    <!-- Visão geral das categorias -->
                    <section class="categories-overview">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-vote-yea"></i>
                                <h2>Voter dans les catégories</h2>
                            </div>
                            <p>Sélectionnez une catégorie pour commencer à voter</p>
                        </div>

                        <?php if (empty($pageData['available_categories'])): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h3>Aucune catégorie disponible pour le moment</h3>
                                <p>Les votes ne sont pas encore ouverts ou vous avez déjà voté dans toutes les catégories.</p>
                                <div class="empty-state-actions">
                                    <a href="user-dashboard.php" class="btn btn-primary">
                                        <i class="fas fa-home"></i>
                                        Retour au tableau de bord
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="categories-grid">
                                <?php foreach ($pageData['available_categories'] as $category):
                                    $status = null;
                                    foreach ($pageData['voting_status'] as $s) {
                                        if ($s['category_id'] == $category['id_categorie']) {
                                            $status = $s;
                                            break;
                                        }
                                    }

                                    // Correção IMPORTANTE: usar nomination_count direto da categoria, não do status
                                    $actualNominationCount = $category['nomination_count'] ?? 0;
                                    $hasNominations = $actualNominationCount > 0;
                                    $canVote = $status && !$status['has_voted'] && $status['is_active'] && $hasNominations;
                                ?>
                                    <div class="category-card <?php echo $status && $status['has_voted'] ? 'voted' : ''; ?>"
                                        data-start-date="<?php echo $category['date_debut_votes'] ?? ''; ?>"
                                        data-end-date="<?php echo $category['date_fin_votes'] ?? ''; ?>"
                                        data-nominations="<?php echo $category['nomination_count'] ?? 0; ?>">
                                        <div class="category-header">
                                            <div class="category-icon">
                                                <i class="fas fa-trophy"></i>
                                            </div>
                                            <div class="category-badge">
                                                <?php if ($status && $status['has_voted']): ?>
                                                    <span class="badge voted-badge">
                                                        <i class="fas fa-check-circle"></i>
                                                        Voté
                                                    </span>
                                                <?php elseif ($canVote): ?>
                                                    <span class="badge active-badge">
                                                        <i class="fas fa-vote-yea"></i>
                                                        Disponible
                                                    </span>
                                                <?php elseif (!$hasNominations): ?>
                                                    <span class="badge no-nominations-badge">
                                                        <i class="fas fa-users-slash"></i>
                                                        Pas de nominés
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge inactive-badge">
                                                        <i class="fas fa-info-circle"></i>
                                                        <?php echo $status['has_voted'] ? 'Déjà voté' : 'Indisponible'; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="category-body">
                                            <h3><?php echo htmlspecialchars($category['nom']); ?></h3>

                                            <div class="category-meta">
                                                <div class="meta-item">
                                                    <i class="fas fa-calendar"></i>
                                                    <span>
                                                        <?php if ($category['date_debut_votes'] && $category['date_fin_votes']): ?>
                                                            <?php echo date('d/m/Y', strtotime($category['date_debut_votes'])); ?> -
                                                            <?php echo date('d/m/Y', strtotime($category['date_fin_votes'])); ?>
                                                        <?php elseif (isset($category['vote_start_formatted'])): ?>
                                                            <?php echo $category['vote_start_formatted']; ?> -
                                                            <?php echo $category['vote_end_formatted']; ?>
                                                        <?php else: ?>
                                                            Période d'édition
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                                <div class="meta-item">
                                                    <i class="fas fa-users"></i>
                                                    <span><?php echo $category['nomination_count']; ?> nominés</span>
                                                </div>
                                                <div class="meta-item">
                                                    <i class="fas fa-hashtag"></i>
                                                    <span><?php echo $category['plateforme_cible'] ?? 'Toutes plateformes'; ?></span>
                                                </div>
                                            </div>

                                            <?php if ($category['description']): ?>
                                                <p class="category-desc"><?php echo htmlspecialchars(substr($category['description'], 0, 100)); ?>...</p>
                                            <?php endif; ?>

                                            <div class="category-stats">
                                                <div class="stat">
                                                    <div class="number"><?php echo $category['nomination_count']; ?></div>
                                                    <div class="label">Nominés</div>
                                                </div>
                                                <div class="stat">
                                                    <div class="number"><?php echo $status && $status['has_voted'] ? '1' : '0'; ?></div>
                                                    <div class="label">Votes</div>
                                                </div>
                                               
                                            </div>

                                            <div class="category-actions">
                                                <?php if ($status && $status['has_voted']): ?>
                                                    <button class="btn btn-success btn-block" disabled>
                                                        <i class="fas fa-check-circle"></i>
                                                        Déjà voté
                                                    </button>
                                                <?php elseif ($canVote): ?>
                                                    <form method="POST" action="" class="category-form">
                                                        <input type="hidden" name="action" value="start_voting">
                                                        <input type="hidden" name="category_id" value="<?php echo $category['id_categorie']; ?>">
                                                        <button type="submit" class="btn btn-primary btn-block">
                                                            <i class="fas fa-vote-yea"></i>
                                                            Voter maintenant
                                                        </button>
                                                    </form>
                                                <?php elseif (!$hasNominations): ?>
                                                    <button class="btn btn-disabled btn-block" disabled>
                                                        <i class="fas fa-users-slash"></i>
                                                        Pas de nominés
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-disabled btn-block" disabled>
                                                        <i class="fas fa-clock"></i>
                                                        Indisponible
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <!-- Status de votação -->
                    <section class="voting-progress">
                        <div class="section-header">
                            <div class="section-title">
                                <i class="fas fa-chart-line"></i>
                                <h2>Votre progression</h2>
                            </div>
                        </div>

                        <div class="progress-stats">
                            <?php
                            $totalCategories = count($pageData['voting_status']);
                            $votedCategories = 0;
                            $availableCategories = 0;
                            foreach ($pageData['voting_status'] as $status) {
                                if ($status['has_voted']) $votedCategories++;
                                if ($status['can_vote']) $availableCategories++;
                            }
                            $percentage = $totalCategories > 0 ? round(($votedCategories / $totalCategories) * 100) : 0;
                            ?>

                            <div class="progress-circle">
                                <svg width="140" height="140" viewBox="0 0 140 140">
                                    <defs>
                                        <linearGradient id="progress-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" stop-color="#4FBDAB" />
                                            <stop offset="100%" stop-color="#3da895" />
                                        </linearGradient>
                                    </defs>
                                    <circle class="progress-bg" cx="70" cy="70" r="65"></circle>
                                    <circle class="progress-bar" cx="70" cy="70" r="65"
                                        stroke-dasharray="<?php echo 2 * 3.14159 * 65; ?>"
                                        stroke-dashoffset="<?php echo 2 * 3.14159 * 65 * (1 - $percentage / 100); ?>"></circle>
                                </svg>
                                <div class="progress-text">
                                    <span class="percentage"><?php echo $percentage; ?>%</span>
                                    <span class="progress-label">Complété</span>
                                </div>
                            </div>

                            <div class="progress-details">
                                <div class="detail-item">
                                    <span class="detail-value"><?php echo $totalCategories; ?></span>
                                    <span class="detail-label">Catégories totales</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-value"><?php echo $votedCategories; ?></span>
                                    <span class="detail-label">Votes effectués</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-value"><?php echo $availableCategories; ?></span>
                                    <span class="detail-label">Disponibles</span>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php else: ?>
                    <!-- Interface de votação específica -->
                    <section class="voting-interface">
                        <div class="voting-header">
                            <a href="vote.php" class="back-to-categories">
                                <i class="fas fa-arrow-left"></i>
                                Retour aux catégories
                            </a>

                            <div class="voting-title">
                                <h2><?php echo htmlspecialchars($currentCategory['nom']); ?></h2>
                                <p><?php echo htmlspecialchars($currentCategory['description'] ?? 'Sélectionnez votre favori parmi les nominés'); ?></p>

                                <?php if ($currentCategory['date_debut_votes'] && $currentCategory['date_fin_votes']): ?>
                                    <div class="voting-period">
                                        <i class="fas fa-clock"></i>
                                        <span>Période de vote: du <?php echo date('d/m/Y', strtotime($currentCategory['date_debut_votes'])); ?> au <?php echo date('d/m/Y', strtotime($currentCategory['date_fin_votes'])); ?></span>
                                    </div>
                                <?php elseif ($currentCategory['id_edition']): ?>
                                    <div class="voting-period">
                                        <i class="fas fa-clock"></i>
                                        <span>Période de vote: édition <?php echo $currentCategory['annee'] ?? ''; ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="voting-info">
                                <div class="info-card">
                                    <i class="fas fa-info-circle"></i>
                                    <div class="info-content">
                                        <h4>Important</h4>
                                        <p>Vous ne pouvez voter qu'une seule fois dans cette catégorie. Votre vote est anonyme et sécurisé.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($alreadyVoted): ?>
                            <div class="empty-state">
                                <i class="fas fa-check-circle fa-3x" style="color: #32D583;"></i>
                                <h3>Vous avez déjà voté dans cette catégorie</h3>
                                <p>Votre vote a déjà été enregistré. Vous ne pouvez voter qu'une seule fois par catégorie.</p>
                                <div class="empty-state-actions">
                                    <a href="vote.php" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                        Retour aux catégories
                                    </a>
                                </div>
                            </div>
                        <?php elseif (empty($nominations)): ?>
                            <div class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                <h3>Aucun nominé disponible</h3>
                                <p>Il n'y a actuellement aucun nominé dans cette catégorie.</p>
                                <div class="empty-state-actions">
                                    <a href="vote.php" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i>
                                        Retour aux catégories
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if (!$viewResults): ?>
                                <!-- Formulaire de votação -->
                                <form method="POST" action="" class="voting-form" id="voteForm">
                                    <input type="hidden" name="action" value="cast_vote">
                                    <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['voting_token'] ?? ''; ?>">

                                    <div class="nominations-grid">
                                        <?php foreach ($nominations as $index => $nomination): ?>
                                            <div class="nomination-card" onclick="selectNomination(<?php echo $nomination['id_nomination']; ?>, '<?php echo addslashes($nomination['libelle']); ?>')">
                                                <input type="radio"
                                                    name="nomination_id"
                                                    id="nomination_<?php echo $nomination['id_nomination']; ?>"
                                                    value="<?php echo $nomination['id_nomination']; ?>"
                                                    class="nomination-radio"
                                                    style="display: none;">

                                                <div class="nomination-content">
                                                    <div class="nomination-header">
                                                        <div class="nomination-rank">#<?php echo $index + 1; ?></div>
                                                        <div class="nomination-title">
                                                            <h3><?php echo htmlspecialchars($nomination['libelle']); ?></h3>
                                                            <div class="nomination-candidate">
                                                                <i class="fas fa-user"></i>
                                                                <span><?php echo htmlspecialchars($nomination['candidate_name'] ?? 'Candidat'); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="nomination-platform">
                                                            <span class="platform-badge <?php echo strtolower($nomination['plateforme'] ?? 'all'); ?>">
                                                                <i class="fab fa-<?php echo strtolower($nomination['plateforme'] ?? 'users'); ?>"></i>
                                                                <?php echo htmlspecialchars($nomination['plateforme'] ?? 'All'); ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="nomination-body">
                                                        <?php if ($nomination['url_image']): ?>
                                                            <div class="nomination-image">
                                                                <img src="<?php echo htmlspecialchars($nomination['url_image']); ?>"
                                                                    alt="<?php echo htmlspecialchars($nomination['libelle']); ?>"
                                                                    onerror="this.src='https://via.placeholder.com/400x200?text=Image+non+disponible'">
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if ($nomination['argumentaire']): ?>
                                                            <div class="nomination-description">
                                                                <p><?php echo htmlspecialchars(substr($nomination['argumentaire'], 0, 200)); ?>...</p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="nomination-footer">
                                                        <div class="nomination-stats">
                                                            <div class="stat">
                                                                <i class="fas fa-vote-yea"></i>
                                                                <span><?php echo $nomination['vote_count'] ?? 0; ?> votes</span>
                                                            </div>
                                                            <?php if ($nomination['url_content']): ?>
                                                                <div class="stat">
                                                                    <i class="fas fa-eye"></i>
                                                                    <a href="<?php echo htmlspecialchars($nomination['url_content']); ?>" target="_blank" class="btn-link">
                                                                        Voir le contenu
                                                                    </a>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="nomination-select">
                                                            <div class="select-indicator">
                                                                <i class="fas fa-check-circle"></i>
                                                                <span>Sélectionner</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="voting-actions">
                                        <div class="voting-security">
                                            <div class="security-notice">
                                                <i class="fas fa-shield-alt"></i>
                                                <div class="notice-content">
                                                    <h4>Vote sécurisé et anonyme</h4>
                                                    <p>Votre vote est chiffré et ne peut être associé à votre identité.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="voting-submit">
                                            <button type="button" class="btn btn-lg" onclick="confirmVote()" id="submitVoteBtn" disabled>
                                                <i class="fas fa-paper-plane"></i>
                                                <span class="btn-text">Envoyer mon vote</span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php else: ?>
                                <!-- Visualização de resultados -->
                                <div class="results-view">
                                    <div class="results-header">
                                        <h3><i class="fas fa-chart-bar"></i> Résultats pour cette catégorie</h3>
                                        <p>Statistiques actuelles des votes</p>
                                    </div>

                                    <div class="results-grid">
                                        <?php
                                        // Ordenar por número de votos
                                        usort($nominations, function ($a, $b) {
                                            return ($b['vote_count'] ?? 0) - ($a['vote_count'] ?? 0);
                                        });

                                        $totalVotes = array_sum(array_column($nominations, 'vote_count'));

                                        foreach ($nominations as $index => $nomination):
                                            $percentage = $totalVotes > 0 ? round((($nomination['vote_count'] ?? 0) / $totalVotes) * 100) : 0;
                                        ?>
                                            <div class="result-item <?php echo $index < 3 ? 'top-' . ($index + 1) : ''; ?>">
                                                <div class="result-rank">
                                                    <?php if ($index === 0): ?>
                                                        <i class="fas fa-crown gold"></i>
                                                    <?php elseif ($index === 1): ?>
                                                        <i class="fas fa-award silver"></i>
                                                    <?php elseif ($index === 2): ?>
                                                        <i class="fas fa-award bronze"></i>
                                                    <?php else: ?>
                                                        <span class="rank-number">#<?php echo $index + 1; ?></span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="result-content">
                                                    <h4><?php echo htmlspecialchars($nomination['libelle']); ?></h4>
                                                    <div class="result-meta">
                                                        <span class="candidate">
                                                            <i class="fas fa-user"></i>
                                                            <?php echo htmlspecialchars($nomination['candidate_name'] ?? 'Candidat'); ?>
                                                        </span>
                                                        <span class="platform">
                                                            <i class="fab fa-<?php echo strtolower($nomination['plateforme'] ?? 'users'); ?>"></i>
                                                            <?php echo htmlspecialchars($nomination['plateforme'] ?? 'All'); ?>
                                                        </span>
                                                    </div>

                                                    <div class="result-bar">
                                                        <div class="bar-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                                    </div>

                                                    <div class="result-stats">
                                                        <span class="vote-count">
                                                            <i class="fas fa-vote-yea"></i>
                                                            <?php echo $nomination['vote_count'] ?? 0; ?> votes
                                                        </span>
                                                        <span class="vote-percentage">
                                                            <?php echo $percentage; ?>%
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="results-actions">
                                        <a href="vote.php?category_id=<?php echo $categoryId; ?>" class="btn btn-outline">
                                            <i class="fas fa-vote-yea"></i>
                                            Retour au vote
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal de confirmação -->
    <div class="vote-confirm-modal" id="confirmModal">
        <div class="modal-content">
            <h3><i class="fas fa-question-circle"></i> Confirmer votre vote</h3>
            <p id="selectedNomineeName">Êtes-vous sûr de vouloir voter pour ce nominé?</p>
            <p class="text-muted">
                <i class="fas fa-exclamation-triangle"></i>
                Cette action est irréversible. Vous ne pourrez pas modifier votre vote.
            </p>

            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal()">
                    Annuler
                </button>
                <button type="button" class="btn btn-primary" onclick="submitVote()">
                    <i class="fas fa-check"></i>
                    Confirmer le vote
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <div class="footer-content">
            <div class="footer-links">
                <a href="/Social-Media-Awards-/categories.php">Catégories</a>
                <a href="/Social-Media-Awards-/nominees.php">Nominés</a>
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

    <script>
        // Gestion de la sélection des nominés
        let selectedNominationId = null;
        let selectedNominationName = '';

        function selectNomination(nominationId, nomineeName) {
            // Réinitialiser toutes les cartes
            document.querySelectorAll('.nomination-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Marquer la carte sélectionnée
            const card = document.querySelector(`input[value="${nominationId}"]`).closest('.nomination-card');
            if (card) {
                card.classList.add('selected');

                // Atualizar seleção
                document.getElementById(`nomination_${nominationId}`).checked = true;
                selectedNominationId = nominationId;
                selectedNominationName = nomineeName;

                // Habilitar botão de envio
                const submitBtn = document.getElementById('submitVoteBtn');
                submitBtn.disabled = false;

                // Atualizar texto do botão
                const btnText = submitBtn.querySelector('.btn-text');
                const shortName = nomineeName.length > 30 ? nomineeName.substring(0, 30) + '...' : nomineeName;
                btnText.textContent = `Voter pour "${shortName}"`;
            }
        }

        // Confirmação de voto
        function confirmVote() {
            if (!selectedNominationId) {
                showToast('Veuillez sélectionner un nominé avant de voter.', 'error');
                return;
            }

            // Atualizar mensagem no modal
            document.getElementById('selectedNomineeName').textContent =
                `Êtes-vous sûr de vouloir voter pour "${selectedNominationName}"?`;

            // Mostrar modal
            document.getElementById('confirmModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('confirmModal').style.display = 'none';
        }

        function submitVote() {
            // Mostrar loading
            const submitBtn = document.querySelector('.modal-actions .btn-primary');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
            submitBtn.disabled = true;

            // Enviar formulário após breve delay para mostrar feedback
            setTimeout(() => {
                document.getElementById('voteForm').submit();
            }, 1000);
        }

        // Fechar modal ao clicar fora
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Função para mostrar toast
        function showToast(message, type = 'info') {
            // Verificar se já existe um toast
            const existingToast = document.querySelector('.toast');
            if (existingToast) {
                existingToast.remove();
            }

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;

            document.body.appendChild(toast);

            // Animação
            setTimeout(() => toast.classList.add('show'), 10);

            // Remover após 5 segundos
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.voting-alert, .already-voted-alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });

            // Limpar parâmetros da URL para evitar mensagens duplicadas
            const url = new URL(window.location);
            if (url.searchParams.has('success') || url.searchParams.has('error')) {
                url.searchParams.delete('success');
                url.searchParams.delete('error');
                window.history.replaceState({}, '', url.toString());
            }

            // Verificação de sessão
            setInterval(() => {
                fetch('/Social-Media-Awards-/views/check_session.php')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.authenticated) {
                            window.location.href = '/Social-Media-Awards-/login.php';
                        }
                    })
                    .catch(() => console.log('Erreur de vérification de session'));
            }, 300000); // 5 minutes

            // Animar barras de progresso nos resultados
            const barFills = document.querySelectorAll('.bar-fill');
            barFills.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape to close modal
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>

</html>
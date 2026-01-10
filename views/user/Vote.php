<?php
// views/user/vote.php - PÁGINA COMPLETA DE VOTAÇÃO (VERSÃO MELHORADA)
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
    require_once '../../app/Models/Vote.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'start_voting' && isset($_POST['category_id'])) {
        $result = $voteController->startCategoryVoting();
        
        if ($result['success']) {
            // Redirecionar para a página de votação com token
            header('Location: vote.php?category_id=' . $categoryId . '&token=' . urlencode($result['token']));
            exit();
        } else {
            $error = $result['message'];
        }
    } 
    elseif ($_POST['action'] === 'cast_vote' && isset($_POST['nomination_id'])) {
        $result = $voteController->castVote();
        
        if ($result['success']) {
            $success = true;
            $_SESSION['vote_success'] = true;
            $_SESSION['vote_message'] = $result['message'];
            $_SESSION['last_vote_details'] = [
                'vote_id' => $result['vote_id'],
                'certificate' => $result['certificate']
            ];
            header('Location: vote.php?success=1');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Verificar se há mensagem de sucesso
$successMessage = null;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter - Social Media Awards</title>
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/user-dashboard.css">
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/vote.css">
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/vote-enhanced.css"> <!-- Novo CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
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
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <ul>
                    <li><a href="user-dashboard.php">Tableau de bord</a></li>
                    <li class="separator"><i class="fas fa-chevron-right"></i></li>
                    <li class="current">Voter</li>
                </ul>
            </nav>

            <!-- Mensagens -->
            <?php if ($success && $successMessage): ?>
            <div class="voting-alert alert-success">
                <i class="fas fa-check-circle fa-2x"></i>
                <div>
                    <strong>Succès!</strong>
                    <p><?php echo htmlspecialchars($successMessage); ?></p>
                    <?php if (isset($_SESSION['last_vote_details'])): ?>
                    <p class="vote-details">
                        <i class="fas fa-fingerprint"></i>
                        ID de vote: <code><?php echo $_SESSION['last_vote_details']['vote_id']; ?></code>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="voting-alert alert-error">
                <i class="fas fa-exclamation-circle fa-2x"></i>
                <div>
                    <strong>Erreur</strong>
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            </div>
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
                                
                                $canVote = $status && !$status['has_voted'] && $status['is_active'];
                            ?>
                            <div class="category-card <?php echo $status && $status['has_voted'] ? 'voted' : ''; ?>">
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
                                        <?php else: ?>
                                            <span class="badge inactive-badge">
                                                <i class="fas fa-clock"></i>
                                                Indisponible
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
                                                <?php else: ?>
                                                    Période d'édition
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-users"></i>
                                            <span><?php echo $category['nomination_count']; ?> nominés</span>
                                        </div>
                                    </div>
                                    
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
                        foreach ($pageData['voting_status'] as $status) {
                            if ($status['has_voted']) $votedCategories++;
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
                                <span class="detail-value"><?php echo count($pageData['available_categories']); ?></span>
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

                    <?php if (empty($nominations)): ?>
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
                            
                            <div class="nominations-grid">
                                <?php foreach ($nominations as $index => $nomination): ?>
                                <div class="nomination-card" onclick="selectNomination(<?php echo $nomination['id_nomination']; ?>)">
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
                                                    <span><?php echo htmlspecialchars($nomination['candidate_name']); ?></span>
                                                </div>
                                            </div>
                                            <div class="nomination-platform">
                                                <span class="platform-badge <?php echo strtolower($nomination['plateforme']); ?>">
                                                    <i class="fab fa-<?php echo strtolower($nomination['plateforme']); ?>"></i>
                                                    <?php echo htmlspecialchars($nomination['plateforme']); ?>
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
                                            
                                            <div class="nomination-description">
                                                <p><?php echo htmlspecialchars($nomination['argumentaire']); ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="nomination-footer">
                                            <div class="nomination-stats">
                                                <div class="stat">
                                                    <i class="fas fa-vote-yea"></i>
                                                    <span><?php echo $nomination['vote_count']; ?> votes</span>
                                                </div>
                                                <div class="stat">
                                                    <i class="fas fa-eye"></i>
                                                    <a href="<?php echo htmlspecialchars($nomination['url_content']); ?>" target="_blank" class="btn-link">
                                                        Voir le contenu
                                                    </a>
                                                </div>
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
                                usort($nominations, function($a, $b) {
                                    return $b['vote_count'] - $a['vote_count'];
                                });
                                
                                $totalVotes = array_sum(array_column($nominations, 'vote_count'));
                                
                                foreach ($nominations as $index => $nomination): 
                                    $percentage = $totalVotes > 0 ? round(($nomination['vote_count'] / $totalVotes) * 100) : 0;
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
                                                <?php echo htmlspecialchars($nomination['candidate_name']); ?>
                                            </span>
                                            <span class="platform">
                                                <i class="fab fa-<?php echo strtolower($nomination['plateforme']); ?>"></i>
                                                <?php echo htmlspecialchars($nomination['plateforme']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="result-bar">
                                            <div class="bar-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                        </div>
                                        
                                        <div class="result-stats">
                                            <span class="vote-count">
                                                <i class="fas fa-vote-yea"></i>
                                                <?php echo $nomination['vote_count']; ?> votes
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
                &copy; 2024 Social Media Awards. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script>
    // Gestion de la sélection des nominés
    let selectedNominationId = null;
    let selectedNominationName = '';
    
    function selectNomination(nominationId) {
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
            
            // Obter nome do nominé
            const nomineeName = card.querySelector('h3').textContent;
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
        const alerts = document.querySelectorAll('.voting-alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        });
        
        // Animar barras de progresso nos resultados
        const barFills = document.querySelectorAll('.bar-fill');
        barFills.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
        
        // Adicionar efeito de shimmer às categorias
        const categoryCards = document.querySelectorAll('.category-card:not(.voted) .category-actions .btn-primary');
        categoryCards.forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.animation = 'shimmer 2s infinite';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.animation = 'none';
            });
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape to close modal
        if (e.key === 'Escape') {
            closeModal();
        }
        
        // Number keys to select nominations (only on voting page)
        if (window.location.search.includes('category_id') && e.key >= '1' && e.key <= '9') {
            const index = parseInt(e.key) - 1;
            const nominations = document.querySelectorAll('.nomination-card');
            if (nominations[index]) {
                const nominationId = nominations[index].querySelector('.nomination-radio').value;
                selectNomination(nominationId);
                showToast(`Nominé #${e.key} sélectionné`, 'info');
            }
        }
    });
    
    // Efeito de confetti quando voto é enviado com sucesso
    <?php if ($success): ?>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            createConfetti();
        }, 500);
    });
    
    function createConfetti() {
        const colors = ['#4FBDAB', '#FF5A79', '#FFD166', '#32D583'];
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.borderRadius = '50%';
            confetti.style.zIndex = '9998';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = '-20px';
            confetti.style.opacity = '0.8';
            
            document.body.appendChild(confetti);
            
            // Animação
            const animation = confetti.animate([
                { transform: 'translateY(0) rotate(0deg)', opacity: 0.8 },
                { transform: `translateY(${window.innerHeight + 100}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
            ], {
                duration: 2000 + Math.random() * 1000,
                easing: 'cubic-bezier(0.215, 0.610, 0.355, 1)'
            });
            
            animation.onfinish = () => confetti.remove();
        }
    }
    <?php endif; ?>
    
    // Adicionar estilos CSS dinâmicos para toast e animações
    const dynamicStyles = document.createElement('style');
    dynamicStyles.textContent = `
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
            border-left: 4px solid #4FBDAB;
            max-width: 400px;
        }
        
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .toast-success { border-left-color: #32D583; }
        .toast-error { border-left-color: #FF6B6B; }
        .toast-info { border-left-color: #4FBDAB; }
        
        .toast i {
            font-size: 1.2rem;
        }
        
        .toast-success i { color: #32D583; }
        .toast-error i { color: #FF6B6B; }
        .toast-info i { color: #4FBDAB; }
        
        .toast span {
            color: #2E2E2E;
            font-weight: 500;
        }
        
        .vote-details {
            margin-top: 10px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .vote-details i {
            color: #4FBDAB;
        }
        
        .vote-details code {
            background: rgba(0,0,0,0.1);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .gold { color: gold; }
        .silver { color: silver; }
        .bronze { color: #cd7f32; }
    `;
    document.head.appendChild(dynamicStyles);
    </script>
</body>
</html>
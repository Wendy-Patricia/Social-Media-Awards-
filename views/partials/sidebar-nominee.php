<?php
// views/candidat/includes/sidebar-nominee.php

// Verificar autenticação e estado
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/candidat-states.php';

requireAuth();
requireRole('candidate');

$pdo = Database::getInstance()->getConnection();
$stateManager = new CandidatStateManager($pdo);
$userId = getUserId();

// Verificar se é nomeado
$isNominee = $stateManager->isNominee($userId);
if (!$isNominee) {
    // Redirecionar para sidebar de candidato
    include 'sidebar-candidat.php';
    exit;
}

// Verificar votação ativa
$nominations = $stateManager->getActiveNominations($userId);
$hasActiveVoting = false;
foreach ($nominations as $nom) {
    if ($stateManager->getVotingStatus($nom) === 'in_progress') {
        $hasActiveVoting = true;
        break;
    }
}
?>

<nav class="sidebar-nominee">
    <div class="sidebar-header">
        <h3 class="mb-0">Espace Nominé</h3>
        <div class="badge badge-gold mt-2">
            <i class="fas fa-trophy"></i> Nominé
        </div>
    </div>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'candidat-dashboard.php' ? 'active' : '' ?>" 
               href="/Social-Media-Awards-/views/candidat/candidat-dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'nominee-profile.php' ? 'active' : '' ?>" 
               href="/Social-Media-Awards-/views/candidat/nominee-profile.php">
                <i class="fas fa-id-badge"></i> Mon profil public
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'share-nomination.php' ? 'active' : '' ?>" 
               href="/Social-Media-Awards-/views/candidat/share-nomination.php">
                <i class="fas fa-share-alt"></i> Partager ma nomination
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'status-votes.php' ? 'active' : '' ?>" 
               href="/Social-Media-Awards-/views/candidat/status-votes.php">
                <i class="fas fa-chart-line"></i> Statut des votes
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reglement-nominee.php' ? 'active' : '' ?>" 
               href="/Social-Media-Awards-/views/candidat/reglement-nominee.php">
                <i class="fas fa-book"></i> Règlement
            </a>
        </li>
        
        <?php foreach ($nominations as $nom): 
            $status = $stateManager->getVotingStatus($nom);
            if ($status === 'ended'): ?>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'results-nominee.php' ? 'active' : '' ?>" 
                   href="/Social-Media-Awards-/views/candidat/results-nominee.php?nomination=<?= $nom['id_nomination'] ?>">
                    <i class="fas fa-medal"></i> Résultats
                </a>
            </li>
            <?php break; endif; ?>
        <?php endforeach; ?>
        
        <?php if ($hasActiveVoting): ?>
        <div class="alert alert-warning small mt-3 mx-2">
            <i class="fas fa-lock"></i> Profil verrouillé pendant les votes
        </div>
        <?php else: ?>
        <li class="nav-item">
            <a class="nav-link" 
               href="/Social-Media-Awards-/views/candidat/candidat-profile.php">
                <i class="fas fa-user-edit"></i> Modifier mon profil
            </a>
        </li>
        <?php endif; ?>
        
        <li class="nav-item">
            <a class="nav-link" 
               href="/Social-Media-Awards-/views/logout.php">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </li>
    </ul>
</nav>
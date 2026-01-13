<?php
// views/partials/sidebar-candidat.php

// Inicializar serviços
require_once __DIR__ . '/../../config/database.php';
$pdo = Database::getInstance()->getConnection();
$candidatService = new App\Services\CandidatService($pdo);

// Verificar status
$userId = $_SESSION['user_id'] ?? null;
$isNominee = $userId ? $candidatService->isNominee($userId) : false;
$canEditProfile = $isNominee ? $candidatService->canEditProfile($userId) : true;

// Obter nomeações ativas (se for nomeado)
$nominations = [];
if ($isNominee) {
    $nominations = $candidatService->getActiveNominations($userId);
}
?>

<div class="sidebar-card mb-4">
    <div class="card-body">
        <div class="sidebar-title">
            <i class="fas <?= $isNominee ? 'fa-trophy' : 'fa-user' ?>"></i>
            <?= $isNominee ? 'MENU NOMINÉ' : 'MENU CANDIDAT' ?>
        </div>
        
        <ul class="nav-candidat">
            <!-- Dashboard -->
            <li>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'candidate-dashboard.php' ? 'active' : '' ?>" 
                   href="candidate-dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            
            <?php if ($isNominee): ?>
            <!-- ======================= -->
            <!-- MENU DO NOMEADO -->
            <!-- ======================= -->
            
            <!-- Perfil público -->
            <li>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'nominee-profile.php' ? 'active' : '' ?>" 
                   href="nominee-profile.php">
                    <i class="fas fa-id-badge"></i>
                    <span>Mon profil public</span>
                    <?php if (!$canEditProfile): ?>
                    <span class="badge bg-warning ms-auto" title="Modification désactivée pendant les votes">
                        <i class="fas fa-lock"></i>
                    </span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Compartilhar -->
            <li>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'share-nomination.php' ? 'active' : '' ?>" 
                   href="share-nomination.php">
                    <i class="fas fa-share-alt"></i>
                    <span>Partager ma nomination</span>
                    <?php if (!empty($nominations)): ?>
                    <span class="badge bg-success ms-auto" title="Kit promotionnel disponible">
                        <i class="fas fa-rocket"></i>
                    </span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Status dos votos -->
            <li>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'status-votes.php' ? 'active' : '' ?>" 
                   href="status-votes.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Statut des votes</span>
                </a>
            </li>
            
            <!-- Regulamento -->
            <li>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reglement.php' ? 'active' : '' ?>" 
                   href="reglement.php">
                    <i class="fas fa-file-contract"></i>
                    <span>Règlement</span>
                    <span class="badge bg-danger ms-auto" title="À lire attentivement">
                        <i class="fas fa-exclamation"></i>
                    </span>
                </a>
            </li>
            
            <!-- Resultados (se disponível) -->
            <?php 
            $hasEndedNominations = false;
            foreach ($nominations as $nom) {
                $status = $candidatService->getVotingStatus($nom);
                if ($status == 'ended') {
                    $hasEndedNominations = true;
                    break;
                }
            }
            ?>
            <?php if ($hasEndedNominations): ?>
            <li>
                <a class="nav-link" href="#">
                    <i class="fas fa-flag-checkered"></i>
                    <span>Résultats</span>
                    <span class="badge bg-info ms-auto">Bientôt</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php else: ?>
            <!-- ======================= -->
            <!-- MENU DO CANDIDATO -->
            <!-- ======================= -->
            
            <!-- Candidaturas -->
            <li>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'mes-candidatures.php' ? 'active' : '' ?>" 
                   href="mes-candidatures.php">
                    <i class="fas fa-file-alt"></i>
                    <span>Mes candidatures</span>
                </a>
            </li>
            
            <!-- Submeter candidatura -->
            <li>
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'soumettre-candidature.php' ? 'active' : '' ?>" 
                   href="soumettre-candidature.php">
                    <i class="fas fa-paper-plane"></i>
                    <span>Soumettre candidature</span>
                </a>
            </li>
            
            <!-- Perfil -->
            <li>
                <a class="nav-link" href="#">
                    <i class="fas fa-user-edit"></i>
                    <span>Mon profil</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Separador -->
            <li class="nav-separator"></li>
            
            <!-- Conta (comum) -->
            <li>
                <a class="nav-link" href="#">
                    <i class="fas fa-cog"></i>
                    <span>Mon compte</span>
                </a>
            </li>
            
            <!-- Logout -->
            <li>
                <a class="nav-link" href="/Social-Media-Awards/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Badge de status -->
<div class="status-badge-card text-center">
    <?php if ($isNominee): ?>
    <span class="nominee-badge">
        <i class="fas fa-trophy"></i> NOMINÉ(E)
    </span>
    <p class="mt-2 mb-0 small">
        Félicitations !<br>
        Vous participez aux votes.
    </p>
    
    <?php if (!empty($nominations)): 
        $firstNomination = $nominations[0];
        $votingStatus = $candidatService->getVotingStatus($firstNomination);
    ?>
    <div class="mt-3">
        <small class="d-block text-muted">Statut des votes :</small>
        <span class="badge 
            <?= $votingStatus == 'in_progress' ? 'bg-success' : 
               ($votingStatus == 'ended' ? 'bg-secondary' : 'bg-warning') ?>">
            <?= $votingStatus == 'in_progress' ? 'En cours' : 
               ($votingStatus == 'ended' ? 'Terminés' : 'À venir') ?>
        </span>
    </div>
    <?php endif; ?>
    
    <?php else: ?>
    <span class="candidate-badge">
        <i class="fas fa-user"></i> CANDIDAT
    </span>
    <p class="mt-2 mb-0 small">
        Soumettez votre<br>
        première candidature !
    </p>
    <?php endif; ?>
</div>

<style>
.nav-separator {
    height: 1px;
    background: var(--border-color);
    margin: var(--spacing-sm) 0;
    list-style: none;
}
</style>
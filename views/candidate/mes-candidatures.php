<?php
// views/candidate/mes-candidatures.php
session_start();

// Verificar se o usuário está logado como candidato
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: /Social-Media-Awards-/views/login.php');
    exit;
}

// Incluir configurações
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatService;
use App\Services\CategoryService;
use App\Services\EditionService;

// Inicializar conexão
$pdo = Database::getInstance()->getConnection();

// Inicializar serviços
$candidatService = new CandidatService($pdo);
$categoryService = new CategoryService($pdo);
$editionService = new EditionService($pdo);

// Obter candidaturas do usuário
$userId = $_SESSION['user_id'];
$candidatures = $candidatService->getUserCandidatures($userId);
$stats = $candidatService->getCandidatStats($userId);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Candidatures - Social Media Awards</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="/Social-Media-Awards-/assets/css/candidat.css">
</head>

<body class="bg-light">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/Social-Media-Awards-/index.php">
                <i class="fas fa-trophy me-2"></i>Social Media Awards
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="candidate-dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i> Tableau de bord
                </a>
                <span class="navbar-text me-3">
                    <?= htmlspecialchars($_SESSION['user_pseudonyme'] ?? 'Candidat') ?>
                </span>
                <a class="nav-link" href="/Social-Media-Awards-/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <?php include __DIR__ . '/../partials/sidebar-candidat.php'; ?>
            </div>

            <!-- Conteúdo principal -->
            <div class="col-md-9">
                <!-- Cabeçalho -->
                <div class="page-header-candidatures">
                    <h1>
                        <i class="fas fa-file-alt me-2"></i>
                        Mes Candidatures
                    </h1>
                    <p class="text-muted">Suivez l'état de toutes vos candidatures</p>
                </div>

                <!-- Alertas -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php unset($_SESSION['success']);
                endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php unset($_SESSION['error']);
                endif; ?>

                <!-- Estatísticas -->
                <div class="stats-candidatures">
                    <div class="stat-card-candidature total">
                        <div class="stat-content">
                            <i class="fas fa-file-alt stat-icon"></i>
                            <h6>Total</h6>
                            <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
                        </div>
                    </div>

                    <div class="stat-card-candidature pending">
                        <div class="stat-content">
                            <i class="fas fa-clock stat-icon"></i>
                            <h6>En attente</h6>
                            <div class="stat-number"><?= $stats['pending'] ?? 0 ?></div>
                        </div>
                    </div>

                    <div class="stat-card-candidature approved">
                        <div class="stat-content">
                            <i class="fas fa-check-circle stat-icon"></i>
                            <h6>Approuvées</h6>
                            <div class="stat-number"><?= $stats['approved'] ?? 0 ?></div>
                        </div>
                    </div>

                    <div class="stat-card-candidature rejected">
                        <div class="stat-content">
                            <i class="fas fa-times-circle stat-icon"></i>
                            <h6>Rejetées</h6>
                            <div class="stat-number"><?= $stats['rejected'] ?? 0 ?></div>
                        </div>
                    </div>
                </div>

                <!-- Lista de candidaturas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Liste de vos candidatures
                        </h5>
                    </div>
                    <div class="table-container-candidatures">
                        <div class="actions-header-candidatures">
                            <div class="filter-group">
                                <span class="filter-label">Filtrer par :</span>
                                <select class="filter-select" id="filter-status">
                                    <option value="">Tous les statuts</option>
                                    <option value="En attente">En attente</option>
                                    <option value="Approuvée">Approuvées</option>
                                    <option value="Rejetée">Rejetées</option>
                                </select>
                            </div>

                            <a href="soumettre-candidature.php" class="btn-add-candidature">
                                <i class="fas fa-plus"></i> Nouvelle candidature
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($candidatures)): ?>
                                <div class="empty-state-candidatures">
                                    <i class="fas fa-file-alt"></i>
                                    <h4>Vous n'avez pas encore soumis de candidature</h4>
                                    <p>Commencez par soumettre votre première candidature pour participer aux Social Media Awards.</p>
                                    <a href="soumettre-candidature.php" class="btn btn-primary btn-empty-state">
                                        <i class="fas fa-paper-plane me-2"></i> Soumettre une candidature
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Titre</th>
                                                <th>Catégorie</th>
                                                <th>Date</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($candidatures as $cand):
                                                $statusClass = '';
                                                if ($cand['statut'] == 'Approuvée') $statusClass = 'approved';
                                                elseif ($cand['statut'] == 'Rejetée') $statusClass = 'rejected';
                                                else $statusClass = 'pending';
                                            ?>
                                                <tr class="table-candidatures <?= $statusClass ?>">
                                                    <td>#<?= $cand['id_candidature'] ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars(substr($cand['libelle'], 0, 40)) ?><?= strlen($cand['libelle']) > 40 ? '...' : '' ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($cand['plateforme'] ?? '') ?></small>
                                                    </td>
                                                    <td><?= htmlspecialchars($cand['categorie_nom'] ?? 'N/A') ?></td>
                                                    <td>
                                                        <?= date('d/m/Y', strtotime($cand['date_soumission'])) ?><br>
                                                        <small class="text-muted"><?= date('H:i', strtotime($cand['date_soumission'])) ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge 
                                                <?= $cand['statut'] == 'Approuvée' ? 'bg-success' : ($cand['statut'] == 'Rejetée' ? 'bg-danger' : 'bg-warning') ?>">
                                                            <?= $cand['statut'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="candidature-details.php?id=<?= $cand['id_candidature'] ?>"
                                                                class="btn btn-sm btn-outline-primary" title="Voir les détails">
                                                                <i class="fas fa-eye"></i>
                                                            </a>

                                                            <?php if ($cand['statut'] == 'En attente'): ?>
                                                                <a href="soumettre-candidature.php?edit=<?= $cand['id_candidature'] ?>"
                                                                    class="btn btn-sm btn-outline-warning" title="Modifier">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="delete-candidature.php?id=<?= $cand['id_candidature'] ?>"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    title="Supprimer"
                                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette candidature ?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-dark text-white py-4 mt-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Social Media Awards</h5>
                        <p class="mb-0">Célébrons la créativité numérique ensemble.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="/Social-Media-Awards-/views/candidate/reglement.php" class="text-white me-3">Règlement</a>
                        <a href="/Social-Media-Awards-/contact.php" class="text-white me-3">Contact</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
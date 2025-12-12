<?php
$page_title = 'Tableau de Bord Administratif';
$is_admin_page = true;

require_once __DIR__ . '/../partials/admin-sidebar.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Social Media Awards 2025</title>
</head>

<main class="main-content">
    <?php include __DIR__ . '../views/partials/admin-header.php'; ?>
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Total Électeurs</h3>
                <p class="stat-number">1,245</p>
                <p class="stat-label">+12 ce mois-ci</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-content">
                <h3>Candidats</h3>
                <p class="stat-number">86</p>
                <p class="stat-label">12 en attente</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-vote-yea"></i>
            </div>
            <div class="stat-content">
                <h3>Élections Actives</h3>
                <p class="stat-number">3</p>
                <p class="stat-label">2 se terminent cette semaine</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3>Total des Votes</h3>
                <p class="stat-number">8,542</p>
                <p class="stat-label">+1,245 aujourd'hui</p>
            </div>
        </div>
    </section>

    <div class="table-container">
        <div class="table-header">
            <h3>Activité Récente</h3>
            <div class="table-actions">
                <select id="filterActivity">
                    <option value="">Toutes activités</option>
                    <option value="user">Utilisateurs</option>
                    <option value="candidate">Candidats</option>
                    <option value="vote">Votes</option>
                </select>
            </div>
        </div>

        <table class="data-table" id="activityTable">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Utilisateur</th>
                    <th>Date/Heure</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><i class="fas fa-user-plus text-success"></i> Inscription</td>
                    <td>Nouvel électeur inscrit</td>
                    <td>jean.dupont@email.com</td>
                    <td>10/01/2024 14:30</td>
                    <td><span class="status-badge status-approved">Terminé</span></td>
                </tr>
                <tr>
                    <td><i class="fas fa-file-upload text-warning"></i> Candidature</td>
                    <td>Nouvelle candidature soumise</td>
                    <td>marie.martin@email.com</td>
                    <td>10/01/2024 13:15</td>
                    <td><span class="status-badge status-pending">En attente</span></td>
                </tr>
                <tr>
                    <td><i class="fas fa-vote-yea text-info"></i> Vote</td>
                    <td>Vote enregistré dans "Meilleur Influenceur"</td>
                    <td>pierre.lefevre@email.com</td>
                    <td>10/01/2024 12:45</td>
                    <td><span class="status-badge status-approved">Valide</span></td>
                </tr>
                <tr>
                    <td><i class="fas fa-user-check text-primary"></i> Modération</td>
                    <td>Candidature approuvée</td>
                    <td>Système</td>
                    <td>10/01/2024 11:20</td>
                    <td><span class="status-badge status-approved">Approuvé</span></td>
                </tr>
                <tr>
                    <td><i class="fas fa-calendar-alt text-secondary"></i> Élection</td>
                    <td>Nouvelle élection créée</td>
                    <td>Administrateur</td>
                    <td>09/01/2024 16:40</td>
                    <td><span class="status-badge status-active">Active</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</main>

<script src="../assets/js/admin-sidebar.js"></script>

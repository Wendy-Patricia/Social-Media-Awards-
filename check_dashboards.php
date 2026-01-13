<?php
// check_dashboards.php

session_start();

// Se não estiver logado, redirecionar para login
if (!isset($_SESSION['user_id'])) {
    header('Location: /Social-Media-Awards/views/login.php');
    exit;
}

// Determinar dashboard baseado no papel do usuário
$role = $_SESSION['user_role'] ?? 'guest';

switch ($role) {
    case 'admin':
        // Verificar se o dashboard do admin existe
        if (file_exists(__DIR__ . '/views/admin/dashboard.php')) {
            header('Location: /Social-Media-Awards/views/admin/dashboard.php');
        } else {
            // Fallback para admin-dashboard.php se dashboard.php não existir
            header('Location: /Social-Media-Awards/views/admin/admin-dashboard.php');
        }
        break;
        
    case 'candidate':
        // Verificar se o dashboard do candidato existe
        if (file_exists(__DIR__ . '/views/candidate/candidate-dashboard.php')) {
            header('Location: /Social-Media-Awards/views/candidate/candidate-dashboard.php');
        } else {
            // Criar um dashboard temporário se não existir
            echo "<h1>Tableau de bord Candidat</h1>";
            echo "<p>Le dashboard n'est pas encore configuré.</p>";
            echo "<p><a href='/Social-Media-Awards/logout.php'>Déconnexion</a></p>";
        }
        break;
        
    case 'voter':
        // Verificar se o dashboard do votante existe
        if (file_exists(__DIR__ . '/views/user/user-dashboard.php')) {
            header('Location: /Social-Media-Awards/views/user/user-dashboard.php');
        } else {
            // Fallback para dashboard genérico
            header('Location: /Social-Media-Awards/index.php');
        }
        break;
        
    default:
        // Se não reconhecer o papel, redirecionar para página principal
        header('Location: /Social-Media-Awards/index.php');
        break;
}

exit;
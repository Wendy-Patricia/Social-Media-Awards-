<?php
// delete-nomination.php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../app/Services/NominationService.php';

// Verificar autenticação usando o sistema correto
if (!isAuthenticated() || !isAdmin()) {
    $_SESSION['error'] = "Accès non autorisé. Vous devez être administrateur.";
    header('Location: /Social-Media-Awards-/views/login.php');
    exit;
}

// Verificar se o método é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: manage-nominations.php');
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $_SESSION['error'] = "ID de nomination invalide.";
    header('Location: manage-nominations.php');
    exit;
}

$nominationId = (int) $_POST['id'];
$adminId = getUserId(); // Usar a função do session.php

// Obter conexão PDO e criar serviço
$pdo = Database::getInstance()->getConnection();
$nominationService = new App\Services\NominationService($pdo);

try {
    // Verificar se a nomination existe
    $nomination = $nominationService->getNominationById($nominationId);
    
    if (!$nomination) {
        $_SESSION['error'] = "Nomination non trouvée.";
        header('Location: manage-nominations.php');
        exit;
    }
    
    // Tentar excluir a nomination
    $success = $nominationService->deleteNomination($nominationId);
    
    if ($success) {
        $_SESSION['success'] = "Nomination supprimée avec succès.";
        
        // Log da ação
        error_log("Admin {$adminId} deleted nomination {$nominationId} - " . date('Y-m-d H:i:s'));        
        
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression de la nomination.";
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur technique: " . $e->getMessage();
    error_log("Error deleting nomination {$nominationId}: " . $e->getMessage());
}

// Redirecionar de volta para a página de gerenciamento
header('Location: manage-nominations.php');
exit;
?>
<?php
// views/candidate/delete-candidature.php
session_start();

// Verificar se o usuário está logado como candidato
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: /Social-Media-Awards/views/login.php');
    exit;
}

// Verificar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Candidature non spécifiée.";
    header('Location: mes-candidatures.php');
    exit;
}

// Incluir configurações
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatService;

// Inicializar conexão
$pdo = Database::getInstance()->getConnection();

// Inicializar serviço
$candidatService = new CandidatService($pdo);

// Excluir candidatura
$userId = $_SESSION['user_id'];
$candidatureId = (int)$_GET['id'];

$success = $candidatService->deleteCandidature($candidatureId, $userId);

if ($success) {
    $_SESSION['success'] = "Candidature supprimée avec succès.";
} else {
    $_SESSION['error'] = "Erreur lors de la suppression. La candidature est peut-être déjà traitée.";
}

header('Location: mes-candidatures.php');
exit;
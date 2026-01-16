<?php
// views/candidate/check-candidature-duplicate.php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatService;

$pdo = Database::getInstance()->getConnection();
$candidatService = new CandidatService($pdo);

$categoryId = (int) ($_GET['category_id'] ?? 0);
$platform = $_GET['platform'] ?? '';

if (!$categoryId || !$platform) {
    echo json_encode(['error' => 'Paramètres invalides']);
    exit;
}

$userId = $_SESSION['user_id'];

// Verificar se já tem candidatura na categoria para esta plataforma
$hasDuplicate = $candidatService->hasCandidatureInCategoryForPlatform(
    $userId, 
    $categoryId, 
    $platform
);

echo json_encode([
    'has_duplicate' => $hasDuplicate,
    'message' => $hasDuplicate 
        ? 'Vous avez déjà une candidature dans cette catégorie pour ' . htmlspecialchars($platform) 
        : 'Plateforme disponible'
]);
?>
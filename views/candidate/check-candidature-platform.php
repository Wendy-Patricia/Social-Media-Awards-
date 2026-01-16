<?php
// views/candidate/check-candidature-platform.php
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

// Verificar se já tem candidatura na categoria para esta plataforma
$hasCandidature = $candidatService->hasCandidatureInCategoryForPlatform(
    $_SESSION['user_id'], 
    $categoryId, 
    $platform
);

// Obter plataformas já usadas nesta categoria
$existingCandidatures = $candidatService->getCandidaturesInCategory(
    $_SESSION['user_id'], 
    $categoryId
);

$usedPlatforms = array_map(function($candidature) {
    return $candidature['plateforme'];
}, $existingCandidatures);

echo json_encode([
    'has_candidature' => $hasCandidature,
    'used_platforms' => $usedPlatforms,
    'message' => $hasCandidature 
        ? 'Vous avez déjà une candidature dans cette catégorie pour ' . htmlspecialchars($platform) 
        : 'Vous pouvez soumettre une candidature pour ' . htmlspecialchars($platform)
]);
?>
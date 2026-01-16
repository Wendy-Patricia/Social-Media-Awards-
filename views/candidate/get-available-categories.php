<?php
// views/candidate/get-available-categories.php
session_start();

header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatService;

$pdo = Database::getInstance()->getConnection();
$candidatService = new CandidatService($pdo);

$userId = (int) ($_GET['user_id'] ?? $_SESSION['user_id']);

if ($userId !== $_SESSION['user_id']) {
    echo json_encode(['error' => 'ID utilisateur invalide']);
    exit;
}

try {
    $categories = $candidatService->getAvailableCategoriesForCandidature($userId);
    
    // Filtrar categorias onde já tem candidatura
    $filteredCategories = array_filter($categories, function($category) use ($candidatService, $userId) {
        return !$candidatService->hasCandidatureInCategoryForPlatform($userId, $category['id_categorie'], $category['plateforme_cible']);
    });
    
    echo json_encode([
        'success' => true, 
        'categories' => array_values($filteredCategories)
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erreur technique: ' . $e->getMessage()]);
}
?>
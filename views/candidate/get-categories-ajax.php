<?php
// views/candidate/get-categories-ajax.php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$editionId = isset($_GET['edition_id']) ? (int)$_GET['edition_id'] : 0;

if ($editionId <= 0) {
    echo json_encode(['success' => false, 'categories' => []]);
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Obter categorias da edição que ainda estão aceitando candidaturas
    $sql = "SELECT c.*, e.nom as edition_nom, e.date_fin_candidatures
            FROM categorie c
            JOIN edition e ON c.id_edition = e.id_edition
            WHERE c.id_edition = :edition_id
            AND e.date_fin_candidatures >= NOW()
            ORDER BY c.nom ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':edition_id' => $editionId]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    error_log('Erreur get-categories-ajax: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
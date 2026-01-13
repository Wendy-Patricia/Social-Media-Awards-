<?php
// views/candidate/candidature-details.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Controllers\CandidatController;
use App\Services\CandidatService;
use App\Services\CategoryService;
use App\Services\EditionService;

// Inicializar serviços
$pdo = Database::getInstance()->getConnection();
$candidatService = new CandidatService($pdo);
$categoryService = new CategoryService($pdo);
$editionService = new EditionService($pdo);

// Criar controller
$controller = new CandidatController($candidatService, $categoryService, $editionService);

// Executar método
$controller->candidatureDetails();
?>
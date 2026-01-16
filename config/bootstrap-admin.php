<?php

require_once __DIR__ . '/../app/autoload.php';

use App\Services\CategoryService;
use App\Services\EditionService;
use App\Services\CandidatureService;
use App\Services\NominationService;

use App\Controllers\AdminCategoryController;
use App\Controllers\AdminEditionController;
use App\Controllers\AdminCandidatureController;
use App\Controllers\NominationController;

require_once __DIR__ . '/../app/Interfaces/UserServiceInterface.php';

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=social_media_awards;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}

// Services
$categoryService    = new CategoryService($pdo);
$editionService     = new EditionService($pdo);
$candidatureService = new CandidatureService($pdo);
$nominationService  = new NominationService($pdo);

// Controllers
$categoryController    = new AdminCategoryController($categoryService);
$editionController     = new AdminEditionController($pdo, $editionService);
$candidatureController = new AdminCandidatureController($candidatureService);
$nominationController  = new NominationController($pdo, $nominationService); 
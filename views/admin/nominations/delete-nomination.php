<?php
require_once __DIR__ . '/../../../app/autoload.php'; // AJOUTER CETTE LIGNE
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/database.php';

if (!isAuthenticated() || !isAdmin()) {
    $_SESSION['error'] = "Accès non autorisé. Vous devez être administrateur.";
    header('Location: /Social-Media-Awards-/views/login.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode non autorisée.";
    header('Location: manage-nominations.php');
    exit;
}


if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $_SESSION['error'] = "ID de nomination invalide.";
    header('Location: manage-nominations.php');
    exit;
}

$nominationId = (int) $_POST['id'];
$adminId = getUserId();


$pdo = Database::getInstance()->getConnection();
$nominationService = new App\Services\NominationService($pdo);

try {
    $nomination = $nominationService->getNominationById($nominationId);

    if (!$nomination) {
        $_SESSION['error'] = "Nomination non trouvée.";
        header('Location: manage-nominations.php');
        exit;
    }

    $success = $nominationService->deleteNomination($nominationId);

    if ($success) {
        $_SESSION['success'] = "Nomination supprimée avec succès.";
        error_log("Admin {$adminId} deleted nomination {$nominationId} - " . date('Y-m-d H:i:s'));

        try {
            $sql = "UPDATE candidature SET statut = 'Rejetée' WHERE id_candidature = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nomination->getIdCandidature()]);


            $sql = "UPDATE candidat SET est_nomine = FALSE WHERE id_compte = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nomination->getIdCompte()]);
        } catch (Exception $e) {

            error_log("Error updating related records: " . $e->getMessage());
        }
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression de la nomination.";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur technique: " . $e->getMessage();
    error_log("Error deleting nomination {$nominationId}: " . $e->getMessage());
}


header('Location: manage-nominations.php');
exit;

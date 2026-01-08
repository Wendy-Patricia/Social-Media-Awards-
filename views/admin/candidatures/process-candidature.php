<?php
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();

require_once __DIR__ . '/../../../app/Controllers/AdminController.php';
require_once __DIR__ . '/../../../config/database.php';

$controller = new App\Controllers\AdminController();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage-candidatures.php?error=1");
    exit;
}

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($id <= 0 || !in_array($action, ['approve', 'reject'])) {
    header("Location: view-candidature.php?id=$id&error=1");
    exit;
}

$candidature = $controller->getCandidatureById($id);

if (!$candidature) {
    header("Location: manage-candidatures.php?error=2");
    exit;
}

if ($candidature['statut'] !== 'En attente') {
    header("Location: view-candidature.php?id=$id&error=3");
    exit;
}

try {
    $database = new Database();
    $pdo = $database->getConnection();
    $pdo->beginTransaction();

    if ($action === 'approve') {
        $nominationData = [
            'libelle' => $candidature['libelle'],
            'plateforme' => $candidature['plateforme'],
            'url_content' => $candidature['url_contenu'],
            'url_image' => $candidature['image'],
            'argumentaire' => $candidature['argumentaire'] . ($comment ? "\n\nNote admin: $comment" : ''),
            'id_candidature' => $id,
            'id_categorie' => $candidature['id_categorie'],
            'id_compte' => $candidature['id_compte'],
            'id_admin' => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 6
        ];

        $sql = "INSERT INTO nomination 
                (libelle, plateforme, url_content, url_image, argumentaire, 
                 id_candidature, id_categorie, id_compte, id_admin, date_creation)
                VALUES (:libelle, :plateforme, :url_content, :url_image, :argumentaire, 
                        :id_candidature, :id_categorie, :id_compte, :id_admin, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($nominationData);
        
        $updateSql = "UPDATE candidature SET statut = 'Approuvée' WHERE id_candidature = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute(['id' => $id]);
        
        $notifySql = "UPDATE candidat SET statut = 'Nominé' WHERE id_candidat = :id_candidat";
        $notifyStmt = $pdo->prepare($notifySql);
        $notifyStmt->execute(['id_candidat' => $candidature['id_candidat']]);

        $pdo->commit();
        header("Location: view-candidature.php?id=$id&success=2");
        
    } elseif ($action === 'reject') {
        $updateSql = "UPDATE candidature SET statut = 'Rejetée' WHERE id_candidature = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute(['id' => $id]);
        
        if ($comment) {
            $commentSql = "INSERT INTO candidature_notes (id_candidature, note, id_admin, date_note)
                          VALUES (:id, :note, :admin_id, NOW())";
            $commentStmt = $pdo->prepare($commentSql);
            $commentStmt->execute([
                'id' => $id,
                'note' => $comment,
                'admin_id' => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 6
            ]);
        }
        
        $notifySql = "UPDATE candidat SET statut = 'Rejeté' WHERE id_candidat = :id_candidat";
        $notifyStmt = $pdo->prepare($notifySql);
        $notifyStmt->execute(['id_candidat' => $candidature['id_candidat']]);

        $pdo->commit();
        header("Location: view-candidature.php?id=$id&success=3");
    }
    
    exit;

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Erro process-candidature: " . $e->getMessage());
    header("Location: view-candidature.php?id=$id&error=4");
    exit;
}
<?php

require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/permissions.php';
requireAdmin();

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/bootstrap-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage-candidatures.php?error=1');
    exit;
}

$action  = $_POST['action'] ?? '';
$id      = (int) ($_POST['id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($id <= 0 || !in_array($action, ['approve', 'reject'], true)) {
    header("Location: view-candidature.php?id=$id&error=1");
    exit;
}

$candidature = $candidatureController->getCandidatureById($id);

if (!$candidature) {
    header('Location: manage-candidatures.php?error=2');
    exit;
}

// Use object getter method instead of array access
if ($candidature->getStatut() !== 'En attente') {
    header("Location: view-candidature.php?id=$id&error=3");
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->beginTransaction();

    if ($action === 'approve') {
        // Primeiro, atualizar a candidatura para "Approuvée"
        $stmt = $pdo->prepare(
            "UPDATE candidature 
             SET statut = 'Approuvée' 
             WHERE id_candidature = :id"
        );
        $stmt->execute(['id' => $id]);

        // Verificar se já existe uma nomeação para esta candidatura
        $checkSql = "SELECT id_nomination FROM nomination WHERE id_candidature = :id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute(['id' => $id]);
        
        if (!$checkStmt->fetch()) {
            // Criar a nomeação
            $sqlNomination = "
                INSERT INTO nomination (
                    libelle,
                    plateforme,
                    url_content,
                    url_image,
                    argumentaire,
                    id_candidature,
                    id_categorie,
                    id_compte,
                    id_admin,
                    date_approbation
                ) VALUES (
                    :libelle,
                    :plateforme,
                    :url_content,
                    :url_image,
                    :argumentaire,
                    :id_candidature,
                    :id_categorie,
                    :id_compte,
                    :id_admin,
                    NOW()
                )
            ";

            $stmtNomination = $pdo->prepare($sqlNomination);
            $stmtNomination->execute([
                'libelle'        => $candidature->getLibelle(), // Use getter
                'plateforme'     => $candidature->getPlateforme(), // Use getter
                'url_content'    => $candidature->getUrlContenu(), // Use getter
                'url_image'      => $candidature->getImage(), // Use getter
                'argumentaire'   => $candidature->getArgumentaire() . ($comment ? "\n\nNote admin : $comment" : ''), // Use getter
                'id_candidature' => $id,
                'id_categorie'   => $candidature->getIdCategorie(), // Use getter
                'id_compte'      => $candidature->getIdCompte(), // Use getter
                'id_admin'       => $_SESSION['admin_id'] ?? $_SESSION['user_id']
            ]);
        }

        // Atualizar o candidato para "Nominé"
        $stmt = $pdo->prepare(
            "UPDATE candidat 
             SET est_nomine = 1 
             WHERE id_compte = :id_compte"
        );
        $stmt->execute(['id_compte' => $candidature->getIdCompte()]); // Use getter

        $pdo->commit();

        header("Location: view-candidature.php?id=$id&success=2");
        exit;
    }

    if ($action === 'reject') {
        $stmt = $pdo->prepare(
            "UPDATE candidature 
             SET statut = 'Rejetée' 
             WHERE id_candidature = :id"
        );
        $stmt->execute(['id' => $id]);

        if ($comment !== '') {
            $stmt = $pdo->prepare(
                "INSERT INTO candidature_notes (
                    id_candidature,
                    note,
                    id_admin,
                    date_note
                ) VALUES (
                    :id,
                    :note,
                    :id_admin,
                    NOW()
                )"
            );
            $stmt->execute([
                'id'       => $id,
                'note'     => $comment,
                'id_admin' => $_SESSION['admin_id'] ?? $_SESSION['user_id']
            ]);
        }

        $pdo->commit();

        header("Location: view-candidature.php?id=$id&success=3");
        exit;
    }

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Erreur process-candidature: ' . $e->getMessage());
    error_log($e->getTraceAsString());

    header("Location: view-candidature.php?id=$id&error=4");
    exit;
}
<?php

namespace App\Controllers;

use App\Services\NominationService;
use Database;
use PDO;

class NominationController
{
    private NominationService $service;

    public function __construct(PDO $pdo)
    {
        $this->service = new NominationService($pdo);
    }

    public function getService(): NominationService
    {
        return $this->service;
    }

    public function index()
    {
        $nominations = $this->service->getAllNominations();
        $stats = [
            'total' => count($nominations),
            'total_votes' => 0 // a preencher se houver tabela de votos
        ];
        require __DIR__ . '/../../../views/admin/nominations/manage-nominations.php';
    }

    public function edit(int $id)
    {
        $nomination = $this->service->getNominationById($id);
        if (!$nomination) {
            $_SESSION['error'] = "Nomination non trouvée.";
            header('Location: manage-nominations.php');
            exit;
        }

        $categories = $this->getCategories();
        $platforms = ['TikTok', 'Instagram', 'YouTube', 'Twitch', 'Spotify', 'Facebook', 'X'];

        require __DIR__ . '/../../../views/admin/nominations/edit-nomination.php';
    }

    public function update(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: manage-nominations.php');
            exit;
        }

        $data = [
            'libelle' => $_POST['titre'] ?? '',
            'plateforme' => $_POST['plateforme'] ?? '',
            'url_contenu' => $_POST['lien_contenu'] ?? '',
            'argumentaire' => $_POST['argumentation'] ?? '',
            'remove_image' => $_POST['remove_image'] ?? '0'
        ];

        $success = $this->service->updateNomination(
            $id,
            $data,
            $_FILES['image_file'] ?? null,
            $_SESSION['admin_id'] ?? 6
        );

        if ($success) {
            $_SESSION['success'] = "Nomination mise à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour.";
        }

        header("Location: edit-nomination.php?id=$id");
        exit;
    }

    public function delete(int $id)
    {
        $success = $this->service->deleteNomination($id);
        $_SESSION[$success ? 'success' : 'error'] = $success
            ? "Nomination supprimée."
            : "Erreur lors de la suppression.";

        header('Location: manage-nominations.php');
        exit;
    }

    private function getCategories(): array
    {
        $pdo = $GLOBALS['pdo'] ?? (new Database())->getConnection();
        $stmt = $pdo->query("SELECT id_categorie, nom FROM categorie ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
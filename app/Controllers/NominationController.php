<?php

namespace App\Controllers;

use App\Services\NominationService;
use Database;
use PDO;

/**
 * Contrôleur gérant les nominations (nominações) dans l'administration
 */
class NominationController
{
    private NominationService $service;

    /**
     * Constructeur du contrôleur de nominations
     *
     * @param PDO               $pdo              Connexion à la base de données
     * @param NominationService|null $nominationService Service optionnel (pour tests/injection)
     */
    public function __construct(PDO $pdo, ?NominationService $nominationService = null)
    {
        // Utilise le service fourni ou en crée un nouveau
        $this->service = $nominationService ?: new NominationService($pdo);
    }

    /**
     * Retourne le service de nominations utilisé par ce contrôleur
     *
     * @return NominationService
     */
    public function getService(): NominationService
    {
        return $this->service;
    }

    /**
     * Crée une nomination à partir d'une candidature validée par un administrateur
     *
     * @param int $candidatureId ID de la candidature à transformer en nomination
     * @param int $adminId       ID de l'administrateur qui valide
     * @return bool Succès de l'opération
     */
    public function createNominationFromCandidature(int $candidatureId, int $adminId): bool
    {
        return $this->service->createFromCandidature($candidatureId, $adminId);
    }

    /**
     * Affiche la liste de toutes les nominations (interface d'administration)
     *
     * @return void
     */
    public function index()
    {
        $nominations = $this->service->getAllNominations();
        
        $stats = [
            'total' => count($nominations),
            'total_votes' => 0 // À compléter ultérieurement avec la vraie table de votes
        ];

        require __DIR__ . '/../../../views/admin/nominations/manage-nominations.php';
    }

    /**
     * Affiche le formulaire d'édition d'une nomination existante
     *
     * @param int $id Identifiant de la nomination à modifier
     * @return void
     */
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

    /**
     * Traite la mise à jour d'une nomination existante
     *
     * @param int $id Identifiant de la nomination à mettre à jour
     * @return void
     */
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

    /**
     * Supprime une nomination
     *
     * @param int $id Identifiant de la nomination à supprimer
     * @return void
     */
    public function delete(int $id)
    {
        $success = $this->service->deleteNomination($id);
        
        $_SESSION[$success ? 'success' : 'error'] = $success
            ? "Nomination supprimée avec succès."
            : "Erreur lors de la suppression.";

        header('Location: manage-nominations.php');
        exit;
    }

    /**
     * Récupère la liste de toutes les catégories disponibles
     *
     * @return array Liste des catégories (id_categorie + nom)
     */
    private function getCategories(): array
    {
        $pdo = $GLOBALS['pdo'] ?? (new Database())->getConnection();
        
        $stmt = $pdo->query("SELECT id_categorie, nom FROM categorie ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
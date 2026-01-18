<?php

namespace App\Controllers;

use App\Services\EditionService;
use PDO;
use App\Interfaces\AdminEditionControllerInterface;
use App\Models\Edition;

/**
 * Contrôleur responsable de la gestion administrative des éditions.
 * 
 * Gère les opérations CRUD sur les éditions des Social Media Awards
 * ainsi qu'une liste simplifiée des éditions pour l'affichage administrateur.
 */
class AdminEditionController implements AdminEditionControllerInterface
{
    private EditionService $editionService;
    private PDO $pdo;

    /**
     * Constructeur du contrôleur.
     *
     * @param PDO $pdo Connexion à la base de données
     * @param EditionService $editionService Service gérant les opérations sur les éditions
     */
    public function __construct(PDO $pdo, EditionService $editionService)
    {
        $this->pdo = $pdo;
        $this->editionService = $editionService;
    }

    /**
     * Récupère une liste simplifiée des éditions (id, nom, année).
     * 
     * Utilisé principalement pour les menus déroulants dans l'interface admin.
     *
     * @return array Liste des éditions triées par année décroissante
     */
    public function getEditionsList(): array
    {
        $sql = "SELECT id_edition, nom, annee FROM edition ORDER BY annee DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Récupère toutes les éditions avec leurs données complètes.
     *
     * @return Edition[] Tableau contenant toutes les éditions sous forme d'objets Edition
     */
    public function getAllEditions(): array
    {
        $this->editionService->updateAllEditionStatus();
        return $this->editionService->getAllEditions();
    }

    /**
     * Récupère une édition spécifique par son identifiant.
     *
     * @param int $id Identifiant de l'édition
     * @return Edition|null Objet Edition ou null si non trouvée
     */
    public function getEditionById(int $id): ?Edition
    {
        $data = $this->editionService->getEditionById($id);
        return $data ? new Edition($data) : null;
    }

    /**
     * Crée une nouvelle édition.
     *
     * @param array $data Données de l'édition (nom, année, dates, etc.)
     * @param array|null $imageFile Image associée (facultatif)
     * @return bool True en cas de succès, false sinon
     */
    public function createEdition(array $data, ?array $imageFile = null): bool
    {
        return $this->editionService->createEdition($data, $imageFile);
    }

    /**
     * Met à jour une édition existante.
     *
     * @param int $id Identifiant de l'édition à modifier
     * @param array $data Nouvelles données
     * @param array|null $imageFile Nouvelle image (facultatif)
     * @param bool $removeImage Supprimer l'image actuelle
     * @return bool True en cas de succès, false sinon
     */
    public function updateEdition(int $id, array $data, ?array $imageFile = null, bool $removeImage = false): bool
    {
        if ($removeImage) {
            $_POST['remove_image'] = '1';
        }
        return $this->editionService->updateEdition($id, $data, $imageFile);
    }

    /**
     * Supprime une édition par son identifiant.
     *
     * @param int $id Identifiant de l'édition à supprimer
     * @return bool True en cas de succès, false sinon
     */
    public function deleteEdition(int $id): bool
    {
        return $this->editionService->deleteEdition($id);
    }
}
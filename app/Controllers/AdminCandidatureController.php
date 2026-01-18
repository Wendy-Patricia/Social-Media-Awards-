<?php

namespace App\Controllers;

use App\Services\CandidatureService;
use App\Interfaces\AdminCandidatureControllerInterface;
use App\Models\Candidature;

/**
 * Contrôleur responsable de la gestion administrative des candidatures.
 * 
 * Permet à l'administrateur de consulter, modifier le statut et supprimer
 * les candidatures soumises aux Social Media Awards.
 */
class AdminCandidatureController implements AdminCandidatureControllerInterface
{
    private CandidatureService $candidatureService;

    /**
     * Constructeur du contrôleur.
     *
     * @param CandidatureService $candidatureService Service gérant les candidatures
     */
    public function __construct(CandidatureService $candidatureService)
    {
        $this->candidatureService = $candidatureService;
    }

    /**
     * Récupère la liste complète de toutes les candidatures.
     *
     * @return Candidature[] Tableau contenant toutes les candidatures sous forme d'objets Candidature
     */
    public function getAllCandidatures(): array
    {
        return $this->candidatureService->getAllCandidatures();
    }

    /**
     * Récupère une candidature spécifique par son identifiant.
     *
     * @param int $id Identifiant de la candidature
     * @return Candidature|null Objet Candidature ou null si non trouvée
     */
    public function getCandidatureById(int $id): ?Candidature
    {
        return $this->candidatureService->getCandidatureById($id);
    }

    /**
     * Met à jour le statut d'une candidature (ex: en_attente, acceptée, refusée).
     *
     * @param int $id Identifiant de la candidature
     * @param string $statut Nouveau statut à appliquer
     * @return bool True en cas de succès, false sinon
     */
    public function updateCandidatureStatus(int $id, string $statut): bool
    {
        return $this->candidatureService->updateStatus($id, $statut);
    }

    /**
     * Supprime une candidature par son identifiant.
     *
     * @param int $id Identifiant de la candidature à supprimer
     * @return bool True en cas de succès, false sinon
     */
    public function deleteCandidature(int $id): bool
    {
        return $this->candidatureService->deleteCandidature($id);
    }
}
<?php

namespace app\Interfaces;
use app\Models\Candidature;

/**
 * Interface pour le contrôleur administratif des candidatures.
 * 
 * Définit les méthodes obligatoires pour la consultation, la mise à jour du statut
 * et la suppression des candidatures.
 */
interface AdminCandidatureControllerInterface
{
    /**
     * Récupère la liste complète de toutes les candidatures.
     *
     * @return array Tableau contenant toutes les candidatures
     */
    public function getAllCandidatures(): array;

    /**
     * Récupère une candidature spécifique par son identifiant.
     *
     * @param int $id Identifiant de la candidature
     * @return array|null Données de la candidature ou null si non trouvée
     */
    public function getCandidatureById(int $id): ?Candidature;

    /**
     * Met à jour le statut d'une candidature (ex: en_attente, acceptée, refusée).
     *
     * @param int $id Identifiant de la candidature
     * @param string $statut Nouveau statut à appliquer
     * @return bool True en cas de succès, false sinon
     */
    public function updateCandidatureStatus(int $id, string $statut): bool;

    /**
     * Supprime une candidature par son identifiant.
     *
     * @param int $id Identifiant de la candidature à supprimer
     * @return bool True en cas de succès, false sinon
     */
    public function deleteCandidature(int $id): bool;
}
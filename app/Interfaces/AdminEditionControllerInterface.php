<?php

namespace App\Interfaces;

/**
 * Interface pour le contrôleur administratif des éditions.
 * 
 * Définit les méthodes obligatoires pour la gestion CRUD des éditions
 * ainsi que la récupération d'une liste simplifiée pour les menus.
 */
interface AdminEditionControllerInterface
{
 
    /**
     * Récupère toutes les éditions avec leurs données complètes.
     *
     * @return array Tableau contenant toutes les éditions
     */
    public function getAllEditions(): array;

    /**
     * Récupère une édition spécifique par son identifiant.
     *
     * @param int $id Identifiant de l'édition
     * @return array|null Données de l'édition ou null si non trouvée
     */
    public function getEditionById(int $id): ?array;

    /**
     * Crée une nouvelle édition.
     *
     * @param array $data Données de l'édition (nom, année, dates, etc.)
     * @param array|null $imageFile Image associée (facultatif)
     * @return bool True en cas de succès, false sinon
     */
    public function createEdition(array $data, ?array $imageFile = null): bool;

    /**
     * Met à jour une édition existante.
     *
     * @param int $id Identifiant de l'édition à modifier
     * @param array $data Nouvelles données
     * @param array|null $imageFile Nouvelle image (facultatif)
     * @return bool True en cas de succès, false sinon
     */
    public function updateEdition(int $id, array $data, ?array $imageFile = null): bool;

    /**
     * Supprime une édition par son identifiant.
     *
     * @param int $id Identifiant de l'édition à supprimer
     * @return bool True en cas de succès, false sinon
     */
    public function deleteEdition(int $id): bool;
}
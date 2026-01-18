<?php

namespace App\Models;

/**
 * Modèle représentant une catégorie aux Social Media Awards.
 */
class Categorie
{
    private ?int $id_categorie = null;
    private string $nom;
    private ?string $description = null;
    private ?string $image = null;
    private ?string $plateforme_cible = 'Toutes';
    private ?string $date_debut_votes = null;
    private ?string $date_fin_votes = null;
    private int $id_edition;
    private ?string $edition_nom = null;
    private int $limite_nomines = 10;
    private int $nb_candidatures = 0;
    private int $nb_nominations = 0;

    /**
     * Constructeur du modèle Categorie.
     *
     * @param array $data Données pour initialiser l'objet.
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->setIdCategorie($data['id_categorie'] ?? null);
            $this->setNom($data['nom'] ?? '');
            $this->setDescription($data['description'] ?? null);
            $this->setImage($data['image'] ?? null);
            $this->setPlateformeCible($data['plateforme_cible'] ?? 'Toutes');
            $this->setDateDebutVotes($data['date_debut_votes'] ?? null);
            $this->setDateFinVotes($data['date_fin_votes'] ?? null);
            $this->setIdEdition($data['id_edition'] ?? 0);
            $this->setLimiteNomines($data['limite_nomines'] ?? 10);
            $this->setEditionNom($data['edition_nom'] ?? null);
            $this->setNbCandidatures($data['nb_candidatures'] ?? 0);
            $this->setNbNominations($data['nb_nominations'] ?? 0);
        }
    }

    // ======================
    // GETTERS
    // ======================
    /**
     * Récupère l'ID de la catégorie.
     *
     * @return int|null
     */
    public function getIdCategorie(): ?int
    {
        return $this->id_categorie;
    }

    /**
     * Récupère le nom de la catégorie.
     *
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * Récupère la description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Récupère l'image.
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Récupère la plateforme cible.
     *
     * @return string|null
     */
    public function getPlateformeCible(): ?string
    {
        return $this->plateforme_cible ?: 'Toutes';
    }

    /**
     * Récupère la date de début des votes.
     *
     * @return string|null
     */
    public function getDateDebutVotes(): ?string
    {
        return $this->date_debut_votes;
    }

    /**
     * Récupère la date de fin des votes.
     *
     * @return string|null
     */
    public function getDateFinVotes(): ?string
    {
        return $this->date_fin_votes;
    }

    /**
     * Récupère l'ID de l'édition.
     *
     * @return int
     */
    public function getIdEdition(): int
    {
        return $this->id_edition;
    }

    /**
     * Récupère le nom de l'édition.
     *
     * @return string|null
     */
    public function getEditionNom(): ?string
    {
        return $this->edition_nom;
    }

    /**
     * Récupère la limite de nominés.
     *
     * @return int
     */
    public function getLimiteNomines(): int
    {
        return $this->limite_nomines;
    }

    /**
     * Récupère le nombre de candidatures.
     *
     * @return int
     */
    public function getNbCandidatures(): int
    {
        return $this->nb_candidatures;
    }

    /**
     * Récupère le nombre de nominations.
     *
     * @return int
     */
    public function getNbNominations(): int
    {
        return $this->nb_nominations;
    }

    // ======================
    // SETTERS
    // ======================
    /**
     * Définit l'ID de la catégorie.
     *
     * @param int|null $id_categorie
     * @return void
     */
    public function setIdCategorie(?int $id_categorie): void
    {
        $this->id_categorie = $id_categorie;
    }

    /**
     * Définit le nom de la catégorie.
     *
     * @param string $nom
     * @return void
     * @throws \InvalidArgumentException Si le nom est invalide.
     */
    public function setNom(string $nom): void
    {
        $nom = trim($nom);
        if (empty($nom)) {
            throw new \InvalidArgumentException('Le nom de la catégorie est obligatoire.');
        }
        if (strlen($nom) > 100) {
            throw new \InvalidArgumentException('Le nom ne peut pas dépasser 100 caractères.');
        }
        $this->nom = $nom;
    }

    /**
     * Définit la description.
     *
     * @param string|null $description
     * @return void
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description ? trim($description) : null;
    }

    /**
     * Définit l'image.
     *
     * @param string|null $image
     * @return void
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * Définit la plateforme cible.
     *
     * @param string|null $plateforme_cible
     * @return void
     * @throws \InvalidArgumentException Si la plateforme est invalide.
     */
    /**
     * Définit la plateforme cible.
     *
     * @param string|null $plateforme_cible
     * @return void
     * @throws \InvalidArgumentException Si la plateforme est invalide.
     */
    public function setPlateformeCible(?string $plateforme_cible): void
    {
        $allowed = [
            'Toutes',
            'TikTok',
            'Instagram',
            'YouTube',
            'Twitch',
            'Spotify',
            'X',
            'Facebook',
            'Snapchat',
            'Autre'
        ];

        if ($plateforme_cible !== null && !in_array($plateforme_cible, $allowed)) {
            $this->plateforme_cible = $plateforme_cible;
            return;
        }

        $this->plateforme_cible = $plateforme_cible ?: 'Toutes';
    }

    /**
     * Définit la date de début des votes.
     *
     * @param string|null $date_debut_votes
     * @return void
     */
    public function setDateDebutVotes(?string $date_debut_votes): void
    {
        $this->date_debut_votes = $date_debut_votes;
    }

    /**
     * Définit la date de fin des votes.
     *
     * @param string|null $date_fin_votes
     * @return void
     */
    public function setDateFinVotes(?string $date_fin_votes): void
    {
        $this->date_fin_votes = $date_fin_votes;
    }

    /**
     * Définit l'ID de l'édition.
     *
     * @param int $id_edition
     * @return void
     * @throws \InvalidArgumentException Si l'ID est invalide.
     */
    public function setIdEdition(int $id_edition): void
    {
        if ($id_edition <= 0) {
            throw new \InvalidArgumentException('ID édition invalide.');
        }
        $this->id_edition = $id_edition;
    }

    /**
     * Définit le nom de l'édition.
     *
     * @param string|null $edition_nom
     * @return void
     */
    public function setEditionNom(?string $edition_nom): void
    {
        $this->edition_nom = $edition_nom;
    }

    /**
     * Définit la limite de nominés.
     *
     * @param int $limite_nomines
     * @return void
     * @throws \InvalidArgumentException Si la limite est invalide.
     */
    public function setLimiteNomines(int $limite_nomines): void
    {
        if ($limite_nomines < 1 || $limite_nomines > 50) {
            throw new \InvalidArgumentException('La limite doit être entre 1 et 50.');
        }
        $this->limite_nomines = $limite_nomines;
    }

    /**
     * Définit le nombre de candidatures.
     *
     * @param int $nb_candidatures
     * @return void
     */
    public function setNbCandidatures(int $nb_candidatures): void
    {
        $this->nb_candidatures = max(0, $nb_candidatures);
    }

    /**
     * Définit le nombre de nominations.
     *
     * @param int $nb_nominations
     * @return void
     */
    public function setNbNominations(int $nb_nominations): void
    {
        $this->nb_nominations = max(0, $nb_nominations);
    }

    /**
     * Vérifie si la catégorie est active (votes en cours).
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if (!$this->date_fin_votes) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $debut = $this->date_debut_votes ?? $now;

        return $now >= $debut && $now <= $this->date_fin_votes;
    }
}

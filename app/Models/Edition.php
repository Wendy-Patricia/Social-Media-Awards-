<?php

namespace App\Models;

/**
 * Modèle représentant une édition aux Social Media Awards.
 */
class Edition
{
    private ?int $id_edition = null;
    private int $annee;
    private string $nom;
    private ?string $description = null;
    private ?string $image = null;
    private string $date_debut_candidatures;
    private string $date_fin_candidatures;
    private string $date_debut;
    private string $date_fin;
    private ?string $theme = null;
    private int $nb_categories = 0;
    private int $nb_candidatures = 0;
    private int $nb_votants = 0;

    /**
     * Constructeur du modèle Edition.
     *
     * @param array $data Données pour initialiser l'objet.
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->setIdEdition($data['id_edition'] ?? null);
            $this->setAnnee($data['annee'] ?? 0);
            $this->setNom($data['nom'] ?? '');
            $this->setDescription($data['description'] ?? null);
            $this->setImage($data['image'] ?? null);
            $this->setDateDebutCandidatures($data['date_debut_candidatures'] ?? '');
            $this->setDateFinCandidatures($data['date_fin_candidatures'] ?? '');
            $this->setDateDebut($data['date_debut'] ?? '');
            $this->setDateFin($data['date_fin'] ?? '');
            $this->setTheme($data['theme'] ?? null);
            $this->setNbCategories($data['nb_categories'] ?? 0);
            $this->setNbCandidatures($data['nb_candidatures'] ?? 0);
            $this->setNbVotants($data['nb_votants'] ?? 0);
        }
    }

    /**
     * Récupère l'ID de l'édition.
     *
     * @return int|null
     */
    public function getIdEdition(): ?int
    {
        return $this->id_edition;
    }

    /**
     * Récupère l'année.
     *
     * @return int
     */
    public function getAnnee(): int
    {
        return $this->annee;
    }

    /**
     * Récupère l'année de l'édition.
     *
     * @return int
     */
    public function getEditionAnnee(): int
    {
        return $this->annee;
    }

    /**
     * Récupère le nom.
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
     * Récupère la date de début des candidatures.
     *
     * @return string
     */
    public function getDateDebutCandidatures(): string
    {
        return $this->date_debut_candidatures;
    }

    /**
     * Récupère la date de fin des candidatures.
     *
     * @return string
     */
    public function getDateFinCandidatures(): string
    {
        return $this->date_fin_candidatures;
    }

    /**
     * Récupère la date de début.
     *
     * @return string
     */
    public function getDateDebut(): string
    {
        return $this->date_debut;
    }

    /**
     * Récupère la date de fin.
     *
     * @return string
     */
    public function getDateFin(): string
    {
        return $this->date_fin;
    }

    /**
     * Récupère le thème.
     *
     * @return string|null
     */
    public function getTheme(): ?string
    {
        return $this->theme;
    }

    /**
     * Récupère le nombre de catégories.
     *
     * @return int
     */
    public function getNbCategories(): int
    {
        return $this->nb_categories;
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
     * Récupère le nombre de votants.
     *
     * @return int
     */
    public function getNbVotants(): int
    {
        return $this->nb_votants;
    }

    /**
     * Vérifie si l'édition est active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $now = new \DateTime();
        $debut = new \DateTime($this->date_debut);
        $fin = new \DateTime($this->date_fin);

        return ($now >= $debut && $now <= $fin);
    }

    /**
     * Définit l'ID de l'édition.
     *
     * @param int|null $id_edition
     * @return void
     */
    public function setIdEdition(?int $id_edition): void
    {
        $this->id_edition = $id_edition;
    }

    /**
     * Définit l'année.
     *
     * @param int $annee
     * @return void
     * @throws \InvalidArgumentException Si l'année est invalide.
     */
    public function setAnnee(int $annee): void
    {
        if ($annee < 2000 || $annee > 2100) {
            throw new \InvalidArgumentException('Année invalide.');
        }
        $this->annee = $annee;
    }

    /**
     * Définit le nom.
     *
     * @param string $nom
     * @return void
     * @throws \InvalidArgumentException Si le nom est invalide.
     */
    public function setNom(string $nom): void
    {
        $nom = trim($nom);
        if (empty($nom)) {
            throw new \InvalidArgumentException('Le nom de l\'édition est obligatoire.');
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
     * Définit la date de début des candidatures.
     *
     * @param string $date
     * @return void
     */
    public function setDateDebutCandidatures(string $date): void
    {
        $this->date_debut_candidatures = $date;
    }

    /**
     * Définit la date de fin des candidatures.
     *
     * @param string $date
     * @return void
     */
    public function setDateFinCandidatures(string $date): void
    {
        $this->date_fin_candidatures = $date;
    }

    /**
     * Définit la date de début.
     *
     * @param string $date
     * @return void
     */
    public function setDateDebut(string $date): void
    {
        $this->date_debut = $date;
    }

    /**
     * Définit la date de fin.
     *
     * @param string $date
     * @return void
     */
    public function setDateFin(string $date): void
    {
        $this->date_fin = $date;
    }

    /**
     * Définit le thème.
     *
     * @param string|null $theme
     * @return void
     */
    public function setTheme(?string $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * Définit le nombre de catégories.
     *
     * @param int $nb
     * @return void
     */
    public function setNbCategories(int $nb): void
    {
        $this->nb_categories = max(0, $nb);
    }

    /**
     * Définit le nombre de candidatures.
     *
     * @param int $nb
     * @return void
     */
    public function setNbCandidatures(int $nb): void
    {
        $this->nb_candidatures = max(0, $nb);
    }

    /**
     * Définit le nombre de votants.
     *
     * @param int $nb
     * @return void
     */
    public function setNbVotants(int $nb): void
    {
        $this->nb_votants = max(0, $nb);
    }

    /**
     * Récupère le statut de l'édition.
     *
     * @return string Statut ('upcoming', 'active', 'completed').
     */
    public function getStatus(): string
    {
        $now = date('Y-m-d H:i:s');

        if ($now < $this->date_debut_candidatures) return 'pré-candidatures';
        if ($now <= $this->date_fin_candidatures) return 'candidatures';
        if ($now <= $this->date_fin) return 'votes-en-cours';
        return 'terminée';
    }
    /**
     * Vérifie si l'édition est active.
     *
     * @return bool
     */
    /**
     * Vérifie si l'édition est active (de début candidatures à fin votes).
     *
     * @return bool
     */
    public function getEstActive(): bool
    {
        $now = new \DateTime();
        $debutCandidatures = new \DateTime($this->date_debut_candidatures);
        $finVotos = new \DateTime($this->date_fin);  // Fim total = fim dos votos

        return $now >= $debutCandidatures && $now <= $finVotos;
    }

    /**
     * Vérifie si l'édition accepte des candidatures.
     *
     * @return bool
     */
    public function isAcceptingCandidatures(): bool
    {
        return $this->getEstActive();
    }
}

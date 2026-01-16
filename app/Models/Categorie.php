<?php

namespace App\Models;

class Category
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
    public function getIdCategorie(): ?int
    {
        return $this->id_categorie;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getPlateformeCible(): ?string
    {
        return $this->plateforme_cible ?: 'Toutes';
    }

    public function getDateDebutVotes(): ?string
    {
        return $this->date_debut_votes;
    }

    public function getDateFinVotes(): ?string
    {
        return $this->date_fin_votes;
    }

    public function getIdEdition(): int
    {
        return $this->id_edition;
    }

    public function getEditionNom(): ?string
    {
        return $this->edition_nom;
    }

    public function getLimiteNomines(): int
    {
        return $this->limite_nomines;
    }

    public function getNbCandidatures(): int
    {
        return $this->nb_candidatures;
    }

    public function getNbNominations(): int
    {
        return $this->nb_nominations;
    }

    // ======================
    // SETTERS
    // ======================
    public function setIdCategorie(?int $id_categorie): void
    {
        $this->id_categorie = $id_categorie;
    }

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

    public function setDescription(?string $description): void
    {
        $this->description = $description ? trim($description) : null;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function setPlateformeCible(?string $plateforme_cible): void
    {
        $allowed = ['Toutes', 'TikTok', 'Instagram', 'YouTube', 'X', 'Facebook', 'Autre'];
        if ($plateforme_cible && !in_array($plateforme_cible, $allowed)) {
            throw new \InvalidArgumentException('Plateforme cible non autorisée.');
        }
        $this->plateforme_cible = $plateforme_cible;
    }

    public function setDateDebutVotes(?string $date_debut_votes): void
    {
        $this->date_debut_votes = $date_debut_votes;
    }

    public function setDateFinVotes(?string $date_fin_votes): void
    {
        $this->date_fin_votes = $date_fin_votes;
    }

    public function setIdEdition(int $id_edition): void
    {
        if ($id_edition <= 0) {
            throw new \InvalidArgumentException('ID édition invalide.');
        }
        $this->id_edition = $id_edition;
    }

    public function setEditionNom(?string $edition_nom): void
    {
        $this->edition_nom = $edition_nom;
    }

    public function setLimiteNomines(int $limite_nomines): void
    {
        if ($limite_nomines < 1 || $limite_nomines > 50) {
            throw new \InvalidArgumentException('La limite doit être entre 1 et 50.');
        }
        $this->limite_nomines = $limite_nomines;
    }

    public function setNbCandidatures(int $nb_candidatures): void
    {
        $this->nb_candidatures = max(0, $nb_candidatures);
    }

    public function setNbNominations(int $nb_nominations): void
    {
        $this->nb_nominations = max(0, $nb_nominations);
    }

    /**
     * Método auxiliar: verifica se a categoria está ativa (votos em curso)
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

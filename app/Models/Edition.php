<?php

namespace App\Models;

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

    public function getIdEdition(): ?int { return $this->id_edition; }
    public function getAnnee(): int { return $this->annee; }
    public function getNom(): string { return $this->nom; }
    public function getDescription(): ?string { return $this->description; }
    public function getImage(): ?string { return $this->image; }
    public function getDateDebutCandidatures(): string { return $this->date_debut_candidatures; }
    public function getDateFinCandidatures(): string { return $this->date_fin_candidatures; }
    public function getDateDebut(): string { return $this->date_debut; }
    public function getDateFin(): string { return $this->date_fin; }
    public function getTheme(): ?string { return $this->theme; }
    public function getNbCategories(): int { return $this->nb_categories; }
    public function getNbCandidatures(): int { return $this->nb_candidatures; }
    public function getNbVotants(): int { return $this->nb_votants; }
    
    public function isActive(): bool 
    { 
        $now = new \DateTime();
        $debut = new \DateTime($this->date_debut);
        $fin = new \DateTime($this->date_fin);
        
        return ($now >= $debut && $now <= $fin);
    }

    public function setIdEdition(?int $id_edition): void { $this->id_edition = $id_edition; }
    public function setAnnee(int $annee): void
    {
        if ($annee < 2000 || $annee > 2100) {
            throw new \InvalidArgumentException('Année invalide.');
        }
        $this->annee = $annee;
    }
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
    public function setDescription(?string $description): void { $this->description = $description ? trim($description) : null; }
    public function setImage(?string $image): void { $this->image = $image; }
    public function setDateDebutCandidatures(string $date): void { $this->date_debut_candidatures = $date; }
    public function setDateFinCandidatures(string $date): void { $this->date_fin_candidatures = $date; }
    public function setDateDebut(string $date): void { $this->date_debut = $date; }
    public function setDateFin(string $date): void { $this->date_fin = $date; }
    public function setTheme(?string $theme): void { $this->theme = $theme; }
    public function setNbCategories(int $nb): void { $this->nb_categories = max(0, $nb); }
    public function setNbCandidatures(int $nb): void { $this->nb_candidatures = max(0, $nb); }
    public function setNbVotants(int $nb): void { $this->nb_votants = max(0, $nb); }

    public function getStatus(): string
    {
        $now = new \DateTime();
        $debut = new \DateTime($this->date_debut);
        $fin = new \DateTime($this->date_fin);

        if ($now < $debut) return 'upcoming';
        if ($now <= $fin && $this->isActive()) return 'active';
        return 'completed';
    }
}

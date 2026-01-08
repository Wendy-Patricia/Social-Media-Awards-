<?php

namespace App\Models;

class Nomination
{
    private ?int $id_nomination = null;
    private string $libelle;
    private string $plateforme;
    private string $url_contenu;
    private ?string $url_image = null;
    private string $argumentaire;
    private ?string $date_approbation = null;
    private int $id_candidature;
    private int $id_categorie;
    private ?int $id_compte = null;
    private ?int $id_admin = null;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->setIdNomination($data['id_nomination'] ?? null);
            $this->setLibelle($data['libelle'] ?? '');
            $this->setPlateforme($data['plateforme'] ?? '');
            $this->setUrlContenu($data['url_contenu'] ?? '');
            $this->setUrlImage($data['url_image'] ?? null);
            $this->setArgumentaire($data['argumentaire'] ?? '');
            $this->setDateApprobation($data['date_approbation'] ?? null);
            $this->setIdCandidature($data['id_candidature'] ?? 0);
            $this->setIdCategorie($data['id_categorie'] ?? 0);
            $this->setIdCompte($data['id_compte'] ?? null);
            $this->setIdAdmin($data['id_admin'] ?? null);
        }
    }

    public function getIdNomination(): ?int { return $this->id_nomination; }
    public function getLibelle(): string { return $this->libelle; }
    public function getPlateforme(): string { return $this->plateforme; }
    public function getUrlContenu(): string { return $this->url_contenu; }
    public function getUrlImage(): ?string { return $this->url_image; }
    public function getArgumentaire(): string { return $this->argumentaire; }
    public function getDateApprobation(): ?string { return $this->date_approbation; }
    public function getIdCandidature(): int { return $this->id_candidature; }
    public function getIdCategorie(): int { return $this->id_categorie; }
    public function getIdCompte(): ?int { return $this->id_compte; }
    public function getIdAdmin(): ?int { return $this->id_admin; }

    public function setIdNomination(?int $id): void { $this->id_nomination = $id; }
    public function setLibelle(string $libelle): void
    {
        $libelle = trim($libelle);
        if (empty($libelle)) throw new \InvalidArgumentException('Le libellé est obligatoire.');
        $this->libelle = $libelle;
    }
    public function setPlateforme(string $plateforme): void
    {
        $plateforme = trim($plateforme);
        if (empty($plateforme)) throw new \InvalidArgumentException('La plateforme est obligatoire.');
        $this->plateforme = $plateforme;
    }
    public function setUrlContenu(string $url): void
    {
        $url = trim($url);
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('URL du contenu invalide.');
        }
        $this->url_contenu = $url;
    }
    public function setUrlImage(?string $url): void { $this->url_image = $url; }
    public function setArgumentaire(string $argumentaire): void
    {
        $argumentaire = trim($argumentaire);
        if (strlen($argumentaire) < 100) {
            throw new \InvalidArgumentException('L\'argumentaire doit faire au moins 100 caractères.');
        }
        $this->argumentaire = $argumentaire;
    }
    public function setDateApprobation(?string $date): void { $this->date_approbation = $date; }
    public function setIdCandidature(int $id): void { $this->id_candidature = $id; }
    public function setIdCategorie(int $id): void { $this->id_categorie = $id; }
    public function setIdCompte(?int $id): void { $this->id_compte = $id; }
    public function setIdAdmin(?int $id): void { $this->id_admin = $id; }
}
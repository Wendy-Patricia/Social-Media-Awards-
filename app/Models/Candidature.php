<?php

namespace App\Models;

class Candidature
{
    private ?int $id_candidature = null;
    private string $libelle;
    private string $plateforme;
    private string $url_contenu;
    private ?string $image = null;
    private string $argumentaire;
    private string $date_soumission;
    private string $statut = 'En attente';
    private int $id_compte;
    private int $id_categorie;
    private ?string $candidat_pseudonyme = null;
    private ?string $candidat_email = null;
    private ?string $categorie_nom = null;
    private ?string $edition_nom = null;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->setIdCandidature($data['id_candidature'] ?? null);
            $this->setLibelle($data['libelle'] ?? '');
            $this->setPlateforme($data['plateforme'] ?? '');
            $this->setUrlContenu($data['url_contenu'] ?? '');
            $this->setImage($data['image'] ?? null);
            $this->setArgumentaire($data['argumentaire'] ?? '');
            $this->setDateSoumission($data['date_soumission'] ?? date('Y-m-d H:i:s'));
            $this->setStatut($data['statut'] ?? 'En attente');
            $this->setIdCompte($data['id_compte'] ?? 0);
            $this->setIdCategorie($data['id_categorie'] ?? 0);
            $this->setCandidatPseudonyme($data['candidat_pseudonyme'] ?? null);
            $this->setCandidatEmail($data['candidat_email'] ?? null);
            $this->setCategorieNom($data['categorie_nom'] ?? null);
            $this->setEditionNom($data['edition_nom'] ?? null);
        }
    }

    public function getIdCandidature(): ?int { return $this->id_candidature; }
    public function getLibelle(): string { return $this->libelle; }
    public function getPlateforme(): string { return $this->plateforme; }
    public function getUrlContenu(): string { return $this->url_contenu; }
    public function getImage(): ?string { return $this->image; }
    public function getArgumentaire(): string { return $this->argumentaire; }
    public function getDateSoumission(): string { return $this->date_soumission; }
    public function getStatut(): string { return $this->statut; }
    public function getIdCompte(): int { return $this->id_compte; }
    public function getIdCategorie(): int { return $this->id_categorie; }
    public function getCandidatPseudonyme(): ?string { return $this->candidat_pseudonyme; }
    public function getCandidatEmail(): ?string { return $this->candidat_email; }
    public function getCategorieNom(): ?string { return $this->categorie_nom; }
    public function getEditionNom(): ?string { return $this->edition_nom; }

    public function setIdCandidature(?int $id): void { $this->id_candidature = $id; }
    public function setLibelle(string $libelle): void
    {
        $libelle = trim($libelle);
        if (empty($libelle)) throw new \InvalidArgumentException('Le libellé est obligatoire.');
        if (strlen($libelle) > 255) throw new \InvalidArgumentException('Le libellé ne peut pas dépasser 255 caractères.');
        $this->libelle = $libelle;
    }
    public function setPlateforme(string $plateforme): void
    {
        $allowed = ['TikTok', 'Instagram', 'YouTube', 'Facebook', 'X', 'Twitch', 'Autre'];
        if (!in_array($plateforme, $allowed)) throw new \InvalidArgumentException('Plateforme non autorisée.');
        $this->plateforme = $plateforme;
    }
    public function setUrlContenu(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) throw new \InvalidArgumentException('URL invalide.');
        $this->url_contenu = $url;
    }
    public function setImage(?string $image): void { $this->image = $image; }
    public function setArgumentaire(string $argumentaire): void
    {
        $argumentaire = trim($argumentaire);
        if (empty($argumentaire)) throw new \InvalidArgumentException('L\'argumentaire est obligatoire.');
        $this->argumentaire = $argumentaire;
    }
    public function setDateSoumission(string $date): void { $this->date_soumission = $date; }
    public function setStatut(string $statut): void
    {
        $allowed = ['En attente', 'Approuvée', 'Rejetée'];
        if (!in_array($statut, $allowed)) throw new \InvalidArgumentException('Statut invalide.');
        $this->statut = $statut;
    }
    public function setIdCompte(int $id): void
    {
        if ($id <= 0) throw new \InvalidArgumentException('ID compte invalide.');
        $this->id_compte = $id;
    }
    public function setIdCategorie(int $id): void
    {
        if ($id <= 0) throw new \InvalidArgumentException('ID catégorie invalide.');
        $this->id_categorie = $id;
    }
    public function setCandidatPseudonyme(?string $pseudo): void { $this->candidat_pseudonyme = $pseudo; }
    public function setCandidatEmail(?string $email): void { $this->candidat_email = $email; }
    public function setCategorieNom(?string $nom): void { $this->categorie_nom = $nom; }
    public function setEditionNom(?string $nom): void { $this->edition_nom = $nom; }
}
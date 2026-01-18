<?php

namespace App\Models;

/**
 * Modèle représentant une candidature aux Social Media Awards.
 */
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

    /**
     * Constructeur du modèle Candidature.
     *
     * @param array $data Données pour initialiser l'objet.
     */
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

    /**
     * Récupère l'ID de la candidature.
     *
     * @return int|null
     */
    public function getIdCandidature(): ?int { return $this->id_candidature; }

    /**
     * Récupère le libellé de la candidature.
     *
     * @return string
     */
    public function getLibelle(): string { return $this->libelle; }

    /**
     * Récupère la plateforme de la candidature.
     *
     * @return string
     */
    public function getPlateforme(): string { return $this->plateforme; }

    /**
     * Récupère l'URL du contenu.
     *
     * @return string
     */
    public function getUrlContenu(): string { return $this->url_contenu; }

    /**
     * Récupère l'image associée.
     *
     * @return string|null
     */
    public function getImage(): ?string { return $this->image; }

    /**
     * Récupère l'argumentaire.
     *
     * @return string
     */
    public function getArgumentaire(): string { return $this->argumentaire; }

    /**
     * Récupère la date de soumission.
     *
     * @return string
     */
    public function getDateSoumission(): string { return $this->date_soumission; }

    /**
     * Récupère le statut.
     *
     * @return string
     */
    public function getStatut(): string { return $this->statut; }

    /**
     * Récupère l'ID du compte.
     *
     * @return int
     */
    public function getIdCompte(): int { return $this->id_compte; }

    /**
     * Récupère l'ID de la catégorie.
     *
     * @return int
     */
    public function getIdCategorie(): int { return $this->id_categorie; }

    /**
     * Récupère le pseudonyme du candidat.
     *
     * @return string|null
     */
    public function getCandidatPseudonyme(): ?string { return $this->candidat_pseudonyme; }

    /**
     * Récupère l'email du candidat.
     *
     * @return string|null
     */
    public function getCandidatEmail(): ?string { return $this->candidat_email; }

    /**
     * Récupère le nom de la catégorie.
     *
     * @return string|null
     */
    public function getCategorieNom(): ?string { return $this->categorie_nom; }

    /**
     * Récupère le nom de l'édition.
     *
     * @return string|null
     */
    public function getEditionNom(): ?string { return $this->edition_nom; }

    /**
     * Définit l'ID de la candidature.
     *
     * @param int|null $id
     * @return void
     */
    public function setIdCandidature(?int $id): void { $this->id_candidature = $id; }

    /**
     * Définit le libellé de la candidature.
     *
     * @param string $libelle
     * @return void
     * @throws \InvalidArgumentException Si le libellé est invalide.
     */
    public function setLibelle(string $libelle): void
    {
        $libelle = trim($libelle);
        if (empty($libelle)) throw new \InvalidArgumentException('Le libellé est obligatoire.');
        if (strlen($libelle) > 255) throw new \InvalidArgumentException('Le libellé ne peut pas dépasser 255 caractères.');
        $this->libelle = $libelle;
    }

    /**
     * Définit la plateforme.
     *
     * @param string $plateforme
     * @return void
     * @throws \InvalidArgumentException Si la plateforme est invalide.
     */
    public function setPlateforme(string $plateforme): void
    {
        $allowed = ['TikTok', 'Instagram', 'YouTube', 'Facebook', 'X', 'Twitch', 'Autre'];
        if (!in_array($plateforme, $allowed)) throw new \InvalidArgumentException('Plateforme non autorisée.');
        $this->plateforme = $plateforme;
    }

    /**
     * Définit l'URL du contenu.
     *
     * @param string $url
     * @return void
     * @throws \InvalidArgumentException Si l'URL est invalide.
     */
    public function setUrlContenu(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) throw new \InvalidArgumentException('URL invalide.');
        $this->url_contenu = $url;
    }

    /**
     * Définit l'image.
     *
     * @param string|null $image
     * @return void
     */
    public function setImage(?string $image): void { $this->image = $image; }

    /**
     * Définit l'argumentaire.
     *
     * @param string $argumentaire
     * @return void
     * @throws \InvalidArgumentException Si l'argumentaire est invalide.
     */
    public function setArgumentaire(string $argumentaire): void
    {
        $argumentaire = trim($argumentaire);
        if (empty($argumentaire)) throw new \InvalidArgumentException('L\'argumentaire est obligatoire.');
        $this->argumentaire = $argumentaire;
    }

    /**
     * Définit la date de soumission.
     *
     * @param string $date
     * @return void
     */
    public function setDateSoumission(string $date): void { $this->date_soumission = $date; }

    /**
     * Définit le statut.
     *
     * @param string $statut
     * @return void
     * @throws \InvalidArgumentException Si le statut est invalide.
     */
    public function setStatut(string $statut): void
    {
        $allowed = ['En attente', 'Approuvée', 'Rejetée'];
        if (!in_array($statut, $allowed)) throw new \InvalidArgumentException('Statut invalide.');
        $this->statut = $statut;
    }

    /**
     * Définit l'ID du compte.
     *
     * @param int $id
     * @return void
     * @throws \InvalidArgumentException Si l'ID est invalide.
     */
    public function setIdCompte(int $id): void
    {
        if ($id <= 0) throw new \InvalidArgumentException('ID compte invalide.');
        $this->id_compte = $id;
    }

    /**
     * Définit l'ID de la catégorie.
     *
     * @param int $id
     * @return void
     * @throws \InvalidArgumentException Si l'ID est invalide.
     */
    public function setIdCategorie(int $id): void
    {
        if ($id <= 0) throw new \InvalidArgumentException('ID catégorie invalide.');
        $this->id_categorie = $id;
    }

    /**
     * Définit le pseudonyme du candidat.
     *
     * @param string|null $pseudo
     * @return void
     */
    public function setCandidatPseudonyme(?string $pseudo): void { $this->candidat_pseudonyme = $pseudo; }

    /**
     * Définit l'email du candidat.
     *
     * @param string|null $email
     * @return void
     */
    public function setCandidatEmail(?string $email): void { $this->candidat_email = $email; }

    /**
     * Définit le nom de la catégorie.
     *
     * @param string|null $nom
     * @return void
     */
    public function setCategorieNom(?string $nom): void { $this->categorie_nom = $nom; }

    /**
     * Définit le nom de l'édition.
     *
     * @param string|null $nom
     * @return void
     */
    public function setEditionNom(?string $nom): void { $this->edition_nom = $nom; }
}
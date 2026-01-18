<?php

namespace App\Models;

/**
 * Modèle représentant une nomination aux Social Media Awards.
 */
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

    /**
     * Constructeur du modèle Nomination.
     *
     * @param array $data Données pour initialiser l'objet.
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->setIdNomination($data['id_nomination'] ?? null);
            $this->setLibelle($data['libelle'] ?? '');
            $this->setPlateforme($data['plateforme'] ?? '');
            $urlContenu = $data['url_contenu'] ?? $data['url_content'] ?? '';
            $this->setUrlContenu($urlContenu);
            $this->setUrlImage($data['url_image'] ?? null);
            $this->setArgumentaire($data['argumentaire'] ?? '');
            $this->setDateApprobation($data['date_approbation'] ?? null);
            $this->setIdCandidature($data['id_candidature'] ?? 0);
            $this->setIdCategorie($data['id_categorie'] ?? 0);
            $this->setIdCompte($data['id_compte'] ?? null);
            $this->setIdAdmin($data['id_admin'] ?? null);
        }
    }

    /**
     * Récupère l'ID de la nomination.
     *
     * @return int|null
     */
    public function getIdNomination(): ?int
    {
        return $this->id_nomination;
    }

    /**
     * Récupère le libellé.
     *
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * Récupère la plateforme.
     *
     * @return string
     */
    public function getPlateforme(): string
    {
        return $this->plateforme;
    }

    /**
     * Récupère l'URL du contenu.
     *
     * @return string
     */
    public function getUrlContenu(): string
    {
        return $this->url_contenu;
    }

    /**
     * Récupère l'URL de l'image.
     *
     * @return string|null
     */
    public function getUrlImage(): ?string
    {
        return $this->url_image;
    }

    /**
     * Récupère l'argumentaire.
     *
     * @return string
     */
    public function getArgumentaire(): string
    {
        return $this->argumentaire;
    }

    /**
     * Récupère la date d'approbation.
     *
     * @return string|null
     */
    public function getDateApprobation(): ?string
    {
        return $this->date_approbation;
    }

    /**
     * Récupère l'ID de la candidature.
     *
     * @return int
     */
    public function getIdCandidature(): int
    {
        return $this->id_candidature;
    }

    /**
     * Récupère l'ID de la catégorie.
     *
     * @return int
     */
    public function getIdCategorie(): int
    {
        return $this->id_categorie;
    }

    /**
     * Récupère l'ID du compte.
     *
     * @return int|null
     */
    public function getIdCompte(): ?int
    {
        return $this->id_compte;
    }

    /**
     * Récupère l'ID de l'admin.
     *
     * @return int|null
     */
    public function getIdAdmin(): ?int
    {
        return $this->id_admin;
    }

    /**
     * Définit l'ID de la nomination.
     *
     * @param int|null $id
     * @return void
     */
    public function setIdNomination(?int $id): void
    {
        $this->id_nomination = $id;
    }

    /**
     * Définit le libellé.
     *
     * @param string $libelle
     * @return void
     * @throws \InvalidArgumentException Si le libellé est invalide.
     */
    public function setLibelle(string $libelle): void
    {
        $libelle = trim($libelle);
        if (empty($libelle)) throw new \InvalidArgumentException('Le libellé est obligatoire.');
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
        $plateforme = trim($plateforme);
        if (empty($plateforme)) throw new \InvalidArgumentException('La plateforme est obligatoire.');
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
        $url = trim($url);
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('URL du contenu invalide.');
        }
        $this->url_contenu = $url;
    }

    /**
     * Définit l'URL de l'image.
     *
     * @param string|null $url
     * @return void
     */
    public function setUrlImage(?string $url): void
    {
        $this->url_image = $url;
    }

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
        if (strlen($argumentaire) < 100) {
            throw new \InvalidArgumentException('L\'argumentaire doit faire au moins 100 caractères.');
        }
        $this->argumentaire = $argumentaire;
    }

    /**
     * Définit la date d'approbation.
     *
     * @param string|null $date
     * @return void
     */
    public function setDateApprobation(?string $date): void
    {
        $this->date_approbation = $date;
    }

    /**
     * Définit l'ID de la candidature.
     *
     * @param int $id
     * @return void
     */
    public function setIdCandidature(int $id): void
    {
        $this->id_candidature = $id;
    }

    /**
     * Définit l'ID de la catégorie.
     *
     * @param int $id
     * @return void
     */
    public function setIdCategorie(int $id): void
    {
        $this->id_categorie = $id;
    }

    /**
     * Définit l'ID du compte.
     *
     * @param int|null $id
     * @return void
     */
    public function setIdCompte(?int $id): void
    {
        $this->id_compte = $id;
    }

    /**
     * Définit l'ID de l'admin.
     *
     * @param int|null $id
     * @return void
     */
    public function setIdAdmin(?int $id): void
    {
        $this->id_admin = $id;
    }
}

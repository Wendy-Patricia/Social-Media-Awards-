<?php
// app/Controllers/CandidatController.php

namespace App\Controllers;

use App\Services\CandidatService;
use App\Services\CategoryService;
use App\Services\EditionService;

class CandidatController
{
    private CandidatService $candidatService;
    private CategoryService $categoryService;
    private EditionService $editionService;

    /**
     * Constructeur du contrôleur
     *
     * @param CandidatService $candidatService Service de gestion des candidats
     * @param CategoryService $categoryService Service de gestion des catégories
     * @param EditionService  $editionService  Service de gestion des éditions
     */
    public function __construct(
        CandidatService $candidatService,
        CategoryService $categoryService,
        EditionService $editionService
    ) {
        $this->candidatService = $candidatService;
        $this->categoryService = $categoryService;
        $this->editionService = $editionService;
    }

    /**
     * Affiche le tableau de bord du candidat ou nominé
     *
     * @return void
     */
    public function dashboard(): void
    {
        // Vérification de l'authentification
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Vérification du statut de nominé
        $isNominee = $this->candidatService->isNominee($userId);
        $nominations = [];
        $votingStatus = 'not_started';

        if ($isNominee) {
            $nominations = $this->candidatService->getActiveNominations($userId);
            if (!empty($nominations)) {
                $votingStatus = $this->candidatService->getVotingStatus($nominations[0]);
            }
        }

        // Statistiques du candidat
        $stats = $this->candidatService->getCandidatStats($userId);

        // Candidatures récentes
        $candidatures = $this->candidatService->getUserCandidatures($userId);
        $recentCandidatures = array_slice($candidatures, 0, 5);

        // Éditions actives
        $activeEditions = $this->getActiveEditions();

        // Chargement de la vue
        require __DIR__ . '/../../views/candidate/candidate-dashboard.php';
    }

    /**
     * Affiche la liste des candidatures de l'utilisateur
     *
     * @return void
     */
    public function mesCandidatures(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $candidatures = $this->candidatService->getUserCandidatures($userId);
        $stats = $this->candidatService->getCandidatStats($userId);

        require __DIR__ . '/../../views/candidate/mes-candidatures.php';
    }

    /**
     * Permet de soumettre ou modifier une candidature
     *
     * @return void
     */
    public function soumettreCandidature(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Vérification si le candidat peut encore modifier pendant la phase de vote
        $isNominee = $this->candidatService->isNominee($userId);
        if ($isNominee) {
            $canEdit = $this->candidatService->canEditProfile($userId);
            if (!$canEdit) {
                $_SESSION['error'] = "Vous ne pouvez pas soumettre de candidature pendant les votes.";
                header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
                exit;
            }
        }

        // Récupération des catégories disponibles
        $categories = $this->getCategoriesForCandidature();

        // Traitement de la soumission/modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCandidatureSubmission($userId);
        }

        // Mode édition
        $candidature = null;
        if (isset($_GET['edit'])) {
            $candidature = $this->candidatService->getCandidature($_GET['edit'], $userId);
            if (!$candidature) {
                $_SESSION['error'] = "Candidature non trouvée.";
                header('Location: /Social-Media-Awards-/views/candidate/mes-candidatures.php');
                exit;
            }
        }

        require __DIR__ . '/../../views/candidate/soumettre-candidature.php';
    }

    /**
     * Affiche les détails d'une candidature spécifique
     *
     * @return void
     */
    public function candidatureDetails(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        if (!isset($_GET['id'])) {
            $_SESSION['error'] = "Candidature non spécifiée.";
            header('Location: /Social-Media-Awards-/views/candidate/mes-candidatures.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $candidature = $this->candidatService->getCandidature($_GET['id'], $userId);

        if (!$candidature) {
            $_SESSION['error'] = "Candidature non trouvée.";
            header('Location: /Social-Media-Awards-/views/candidate/mes-candidatures.php');
            exit;
        }

        require __DIR__ . '/../../views/candidate/candidature-details.php';
    }

    /**
     * Affiche le profil public du nominé (gestion depuis l'espace candidat)
     *
     * @return void
     */
    public function nomineeProfile(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Vérification si le candidat est nominé
        $isNominee = $this->candidatService->isNominee($userId);
        if (!$isNominee) {
            $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        // Récupération des nominations actives
        $nominations = $this->candidatService->getActiveNominations($userId);
        if (empty($nominations)) {
            $_SESSION['error'] = "Aucune nomination active trouvée.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        // Sélection de la nomination spécifique ou première par défaut
        $nominationId = $_GET['nomination'] ?? null;
        if ($nominationId) {
            $nomination = array_filter($nominations, function ($nom) use ($nominationId) {
                return $nom['id_nomination'] == $nominationId;
            });
            $nomination = reset($nomination);
        } else {
            $nomination = $nominations[0];
        }

        if (!$nomination) {
            $_SESSION['error'] = "Nomination non trouvée.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        // Génération de l'URL publique
        $publicProfileUrl = "https://" . $_SERVER['HTTP_HOST'] . "/Social-Media-Awards-/nominee.php?id=" . $nomination['id_nomination'];

        require __DIR__ . '/../../views/candidate/nominee-profile.php';
    }

    /**
     * Page de partage de la nomination
     *
     * @return void
     */
    public function shareNomination(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];

        $isNominee = $this->candidatService->isNominee($userId);
        if (!$isNominee) {
            $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        $nominations = $this->candidatService->getActiveNominations($userId);
        if (empty($nominations)) {
            $_SESSION['error'] = "Aucune nomination active trouvée.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        $nomination = $nominations[0];
        $publicProfileUrl = "https://" . $_SERVER['HTTP_HOST'] . "/Social-Media-Awards-/nominee.php?id=" . $nomination['id_nomination'];

        require __DIR__ . '/../../views/candidate/share-nomination.php';
    }

    /**
     * Affiche l'état actuel des votes pour les nominations actives
     *
     * @return void
     */
    public function statusVotes(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];

        $isNominee = $this->candidatService->isNominee($userId);
        if (!$isNominee) {
            $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        $nominations = $this->candidatService->getActiveNominations($userId);

        require __DIR__ . '/../../views/candidate/status-votes.php';
    }

    /**
     * Affiche le règlement du concours
     *
     * @return void
     */
    public function reglement(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $isNominee = $this->candidatService->isNominee($userId);

        require __DIR__ . '/../../views/candidate/reglement.php';
    }

    /**
     * Supprime une candidature (si autorisé)
     *
     * @return void
     */
    public function deleteCandidature(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        if (!isset($_GET['id'])) {
            $_SESSION['error'] = "Candidature non spécifiée.";
            header('Location: /Social-Media-Awards-/views/candidate/mes-candidatures.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $success = $this->candidatService->deleteCandidature($_GET['id'], $userId);

        if ($success) {
            $_SESSION['success'] = "Candidature supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression. La candidature est peut-être déjà traitée.";
        }

        header('Location: /Social-Media-Awards-/views/candidate/mes-candidatures.php');
        exit;
    }

    /**
     * Traite la soumission ou la mise à jour d'une candidature
     *
     * @param int $userId Identifiant de l'utilisateur connecté
     * @return void
     */
    private function processCandidatureSubmission(int $userId): void
    {
        $data = [
            'libelle' => $_POST['libelle'] ?? '',
            'plateforme' => $_POST['plateforme'] ?? '',
            'url_contenu' => $_POST['url_contenu'] ?? '',
            'argumentaire' => $_POST['argumentaire'] ?? '',
            'id_categorie' => $_POST['id_categorie'] ?? 0
        ];

        // Gestion de l'upload d'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $imagePath = $this->candidatService->uploadImage($_FILES['image']);
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }

        // Validation des champs obligatoires
        if (empty($data['libelle']) || empty($data['url_contenu']) || empty($data['argumentaire'])) {
            $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
            return;
        }

        $success = false;

        if (isset($_POST['id_candidature']) && !empty($_POST['id_candidature'])) {
            // Mise à jour d'une candidature existante
            $success = $this->candidatService->updateCandidature(
                $_POST['id_candidature'],
                $data,
                $userId
            );
            $message = $success ? "Candidature mise à jour avec succès." : "Erreur lors de la mise à jour.";
        } else {
            // Création d'une nouvelle candidature
            $success = $this->candidatService->createCandidature($data, $userId);
            $message = $success ? "Candidature soumise avec succès." : "Erreur lors de la soumission.";
        }

        if ($success) {
            $_SESSION['success'] = $message;
            header('Location: /Social-Media-Awards-/views/candidate/mes-candidatures.php');
            exit;
        } else {
            $_SESSION['error'] = $message;
        }
    }

    /**
     * Récupère les catégories utilisables pour une nouvelle candidature
     *
     * @return array Liste des catégories disponibles
     */
    private function getCategoriesForCandidature(): array
    {
        $categories = $this->categoryService->getAllCategories();

        // Filtrage (logique simplifiée ici - à adapter selon besoins)
        return array_filter($categories, function ($category) {
            return true; // Pour l'instant toutes les catégories sont retournées
        });
    }

    /**
     * Récupère les éditions actuellement ouvertes aux candidatures
     *
     * @return array Liste des éditions actives pour candidatures
     */
    private function getActiveEditions(): array
    {
        $allEditions = $this->editionService->getAllEditions();
        $now = date('Y-m-d H:i:s');

        return array_filter($allEditions, function ($edition) use ($now) {
            return $edition['est_active'] == 1 &&
                   $edition['date_fin_candidatures'] >= $now;
        });
    }

    /**
     * Permet l'édition du profil public du nominé
     *
     * @return void
     */
    public function editNomineeProfile(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];

        $isNominee = $this->candidatService->isNominee($userId);
        if (!$isNominee) {
            $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        $canEdit = $this->candidatService->canEditProfile($userId);
        if (!$canEdit) {
            $_SESSION['error'] = "Vous ne pouvez pas modifier votre profil pendant les votes.";
            header('Location: /Social-Media-Awards-/views/candidate/nominee-profile.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'bio' => trim($_POST['bio'] ?? ''),
                'photo_profil' => $_FILES['photo_profil'] ?? null,
                'url_instagram' => trim($_POST['url_instagram'] ?? ''),
                'url_tiktok' => trim($_POST['url_tiktok'] ?? ''),
                'url_youtube' => trim($_POST['url_youtube'] ?? ''),
                'url_twitter' => trim($_POST['url_twitter'] ?? '')
            ];

            if ($data['photo_profil'] && $data['photo_profil']['error'] === 0) {
                $photoPath = $this->candidatService->uploadProfilePhoto($data['photo_profil'], $userId);
                if ($photoPath) {
                    $data['photo_profil_path'] = $photoPath;
                }
            }

            $success = $this->candidatService->updateNomineeProfile($userId, $data);

            if ($success) {
                $_SESSION['success'] = "Profil mis à jour avec succès.";
                header('Location: /Social-Media-Awards-/views/candidate/nominee-profile.php');
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du profil.";
            }
        }

        $nomineeData = $this->candidatService->getNomineeData($userId);

        require __DIR__ . '/../../views/candidate/edit-nominee-profile.php';
    }

    /**
     * Affiche les résultats des votes (disponibles après la fin des votes)
     *
     * @return void
     */
    public function viewResults(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];

        $isNominee = $this->candidatService->isNominee($userId);
        if (!$isNominee) {
            $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        $nominations = $this->candidatService->getActiveNominations($userId);

        $hasResults = false;
        $results = [];

        foreach ($nominations as $nomination) {
            $status = $this->candidatService->getVotingStatus($nomination);
            if ($status === 'ended') {
                $nominationResults = $this->candidatService->getNominationResults($nomination['id_nomination']);
                if ($nominationResults) {
                    $hasResults = true;
                    $results[] = [
                        'nomination' => $nomination,
                        'results' => $nominationResults
                    ];
                }
            }
        }

        if (!$hasResults) {
            $_SESSION['error'] = "Aucun résultat n'est encore disponible.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        require __DIR__ . '/../../views/candidate/results.php';
    }

    /**
     * Page de gestion des candidatures en attente / possibles
     *
     * @return void
     */
    public function pendingCandidatures(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];

        $isNominee = $this->candidatService->isNominee($userId);
        if ($isNominee) {
            $canEdit = $this->candidatService->canEditProfile($userId);
            if (!$canEdit) {
                $_SESSION['error'] = "Vous ne pouvez pas soumettre de candidature pendant les votes.";
                header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
                exit;
            }
        }

        $activeEditions = $this->getActiveEditionsForCandidature();
        $availableCategories = $this->getAvailableCategoriesForCandidature($userId);
        $userCandidatures = $this->candidatService->getUserCandidatures($userId);

        require __DIR__ . '/../../views/candidate/pending-candidatures.php';
    }

    /**
     * Récupère les éditions actuellement ouvertes pour soumettre des candidatures
     *
     * @return array Liste des éditions actives pour candidatures
     */
    private function getActiveEditionsForCandidature(): array
    {
        $allEditions = $this->editionService->getAllEditions();
        $now = date('Y-m-d H:i:s');

        return array_filter($allEditions, function ($edition) use ($now) {
            return $edition['est_active'] == 1 &&
                   $edition['date_fin_candidatures'] >= $now;
        });
    }

    /**
     * Récupère les catégories encore disponibles pour le candidat (non déjà postulées)
     *
     * @param int $userId Identifiant du candidat
     * @return array Liste des catégories disponibles
     */
    private function getAvailableCategoriesForCandidature(int $userId): array
    {
        $activeEditions = $this->getActiveEditionsForCandidature();

        if (empty($activeEditions)) {
            return [];
        }

        $editionIds = array_column($activeEditions, 'id_edition');
        $editionIdsString = implode(',', $editionIds);

        $pdo = $this->candidatService->getPdo();

        $sql = "SELECT c.*, e.nom as edition_nom, e.date_fin_candidatures
                FROM categorie c
                JOIN edition e ON c.id_edition = e.id_edition
                WHERE e.id_edition IN ($editionIdsString)
                AND e.date_fin_candidatures >= NOW()
                ORDER BY e.annee DESC, c.nom ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $allCategories = $stmt->fetchAll();

        $userCandidatures = $this->candidatService->getUserCandidatures($userId);
        $userCategoryIds = array_column($userCandidatures, 'id_categorie');

        return array_filter($allCategories, function ($category) use ($userCategoryIds) {
            return !in_array($category['id_categorie'], $userCategoryIds);
        });
    }
}
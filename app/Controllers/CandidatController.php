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
     * Dashboard do candidato/nomeado
     */
    public function dashboard(): void
    {
        // Verificar autenticação
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        // Verificar estado
        $isNominee = $this->candidatService->isNominee($userId);
        $nominations = [];
        $votingStatus = 'not_started';
        
        if ($isNominee) {
            $nominations = $this->candidatService->getActiveNominations($userId);
            if (!empty($nominations)) {
                $votingStatus = $this->candidatService->getVotingStatus($nominations[0]);
            }
        }
        
        // Estatísticas
        $stats = $this->candidatService->getCandidatStats($userId);
        
        // Candidaturas recentes
        $candidatures = $this->candidatService->getUserCandidatures($userId);
        $recentCandidatures = array_slice($candidatures, 0, 5);
        
        // Edições ativas
        $activeEditions = $this->getActiveEditions();

        // Carregar view
        require __DIR__ . '/../../views/candidate/candidate-dashboard.php';
    }

    /**
     * Lista de candidaturas
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
     * Submeter candidatura
     */
    public function soumettreCandidature(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        // Verificar se pode editar (se for nomeado com votação ativa)
        $isNominee = $this->candidatService->isNominee($userId);
        if ($isNominee) {
            $canEdit = $this->candidatService->canEditProfile($userId);
            if (!$canEdit) {
                $_SESSION['error'] = "Vous ne pouvez pas soumettre de candidature pendant les votes.";
                header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
                exit;
            }
        }

        // Obter categorias disponíveis
        $categories = $this->getCategoriesForCandidature();
        
        // Processar submissão
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processCandidatureSubmission($userId);
        }

        // Se for edição
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
     * Detalhes da candidatura
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
     * Perfil público do nomeado
     */
    public function nomineeProfile(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        // Verificar se é nomeado
        $isNominee = $this->candidatService->isNominee($userId);
        if (!$isNominee) {
            $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        // Obter nomeações
        $nominations = $this->candidatService->getActiveNominations($userId);
        if (empty($nominations)) {
            $_SESSION['error'] = "Aucune nomination active trouvée.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        // Obter nomeação específica ou a primeira
        $nominationId = $_GET['nomination'] ?? null;
        if ($nominationId) {
            $nomination = array_filter($nominations, function($nom) use ($nominationId) {
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

        // Gerar URL pública
        $publicProfileUrl = "https://" . $_SERVER['HTTP_HOST'] . "/Social-Media-Awards-/nominee.php?id=" . $nomination['id_nomination'];

        require __DIR__ . '/../../views/candidate/nominee-profile.php';
    }

    /**
     * Compartilhar nomeação
     */
    public function shareNomination(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        // Verificar se é nomeado
        $isNominee = $this->candidatService->isNominee($userId);
        if (!$isNominee) {
            $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        // Obter nomeações
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
     * Status dos votos
     */
    public function statusVotes(): void
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
            header('Location: /Social-Media-Awards-/login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        // Verificar se é nomeado
        $isNominee = $this->candidatService->isNominee($userId);
        if (!$isNominee) {
            $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
            header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
            exit;
        }

        // Obter nomeações
        $nominations = $this->candidatService->getActiveNominations($userId);
        
        require __DIR__ . '/../../views/candidate/status-votes.php';
    }

    /**
     * Regulamento
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
     * Excluir candidatura
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
     * Métodos auxiliares privados
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

        // Upload da imagem
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $imagePath = $this->candidatService->uploadImage($_FILES['image']);
            if ($imagePath) {
                $data['image'] = $imagePath;
            }
        }

        // Validação básica
        if (empty($data['libelle']) || empty($data['url_contenu']) || empty($data['argumentaire'])) {
            $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
            return;
        }

        $success = false;
        
        if (isset($_POST['id_candidature']) && !empty($_POST['id_candidature'])) {
            // Atualização
            $success = $this->candidatService->updateCandidature(
                $_POST['id_candidature'],
                $data,
                $userId
            );
            $message = $success ? "Candidature mise à jour avec succès." : "Erreur lors de la mise à jour.";
        } else {
            // Criação
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

    private function getCategoriesForCandidature(): array
    {
        // Obter categorias com edições ativas
        $categories = $this->categoryService->getAllCategories();
        
        // Filtrar apenas categorias com edições ativas para candidaturas
        return array_filter($categories, function($category) {
            // Aqui você implementaria a lógica para verificar se a categoria aceita candidaturas
            // Por enquanto, retorna todas
            return true;
        });
    }

    private function getActiveEditions(): array
    {
        // Obter todas as edições
        $allEditions = $this->editionService->getAllEditions();
        
        // Filtrar edições ativas para candidaturas
        $now = date('Y-m-d H:i:s');
        return array_filter($allEditions, function($edition) use ($now) {
            return $edition['est_active'] == 1 && 
                   $edition['date_fin_candidatures'] >= $now;
        });
    }


    // Adicionar estes métodos ao CandidatController.php existente

/**
 * Editar perfil do nomeado
 */
public function editNomineeProfile(): void
{
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
        header('Location: /Social-Media-Awards-/login.php');
        exit;
    }

    $userId = $_SESSION['user_id'];
    
    // Verificar se é nomeado
    $isNominee = $this->candidatService->isNominee($userId);
    if (!$isNominee) {
        $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
        header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
        exit;
    }

    // Verificar se pode editar
    $canEdit = $this->candidatService->canEditProfile($userId);
    if (!$canEdit) {
        $_SESSION['error'] = "Vous ne pouvez pas modifier votre profil pendant les votes.";
        header('Location: /Social-Media-Awards-/views/candidate/nominee-profile.php');
        exit;
    }

    // Processar atualização
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'bio' => trim($_POST['bio'] ?? ''),
            'photo_profil' => $_FILES['photo_profil'] ?? null,
            'url_instagram' => trim($_POST['url_instagram'] ?? ''),
            'url_tiktok' => trim($_POST['url_tiktok'] ?? ''),
            'url_youtube' => trim($_POST['url_youtube'] ?? ''),
            'url_twitter' => trim($_POST['url_twitter'] ?? '')
        ];

        // Upload da foto
        if ($data['photo_profil'] && $data['photo_profil']['error'] === 0) {
            $photoPath = $this->candidatService->uploadProfilePhoto($data['photo_profil'], $userId);
            if ($photoPath) {
                $data['photo_profil_path'] = $photoPath;
            }
        }

        // Atualizar no banco
        $success = $this->candidatService->updateNomineeProfile($userId, $data);
        
        if ($success) {
            $_SESSION['success'] = "Profil mis à jour avec succès.";
            header('Location: /Social-Media-Awards-/views/candidate/nominee-profile.php');
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du profil.";
        }
    }

    // Obter dados atuais
    $nomineeData = $this->candidatService->getNomineeData($userId);

    require __DIR__ . '/../../views/candidate/edit-nominee-profile.php';
}

/**
 * Visualizar resultados (após fim dos votos)
 */
public function viewResults(): void
{
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
        header('Location: /Social-Media-Awards-/login.php');
        exit;
    }

    $userId = $_SESSION['user_id'];
    
    // Verificar se é nomeado
    $isNominee = $this->candidatService->isNominee($userId);
    if (!$isNominee) {
        $_SESSION['error'] = "Vous devez être nominé pour accéder à cette page.";
        header('Location: /Social-Media-Awards-/views/candidate/candidate-dashboard.php');
        exit;
    }

    // Obter nomeações
    $nominations = $this->candidatService->getActiveNominations($userId);
    
    // Verificar se alguma nomeação já tem resultados
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
}
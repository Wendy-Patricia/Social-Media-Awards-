<?php
// views/candidate/update-profile.php
session_start();

// Verificar se o usuário está logado como candidato
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'candidate') {
    header('Location: /Social-Media-Awards-/views/login.php');
    exit;
}

// Incluir configurações
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/autoload.php';

use App\Services\CandidatService;
use App\Services\CandidatureService;

// Inicializar conexão
$pdo = Database::getInstance()->getConnection();

// Inicializar serviços
$candidatService = new CandidatService($pdo);
$candidatureService = new CandidatureService($pdo);

// Verificar se pode editar perfil (não durante votação ativa)
$userId = $_SESSION['user_id'];
$canEditProfile = $candidatService->canEditProfile($userId);

if (!$canEditProfile) {
    $_SESSION['error'] = "Vous ne pouvez pas modifier votre profil pendant une période de votes active.";
    header('Location: perfil-candidat.php');
    exit;
}

// Obter dados atuais do candidato
$candidat = $candidatService->getCandidatById($userId);

if (!$candidat) {
    $_SESSION['error'] = "Profil non trouvé.";
    header('Location: perfil-candidat.php');
    exit;
}

// Processar formulário se for POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'pseudonyme' => trim($_POST['pseudonyme'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'type_candidature' => $_POST['type_candidature'] ?? 'Créateur',
        'nom_legal_ou_societe' => trim($_POST['nom_legal_ou_societe'] ?? ''),
    ];
    
    // Validações
    $errors = [];
    
    // Verificar pseudonyme
    if (empty($data['pseudonyme'])) {
        $errors[] = "Le pseudonyme est obligatoire.";
    } elseif (strlen($data['pseudonyme']) < 3) {
        $errors[] = "Le pseudonyme doit contenir au moins 3 caractères.";
    } elseif (strlen($data['pseudonyme']) > 50) {
        $errors[] = "Le pseudonyme ne peut pas dépasser 50 caractères.";
    } elseif (!$candidatService->isPseudonymeAvailable($data['pseudonyme'], $userId)) {
        $errors[] = "Ce pseudonyme est déjà utilisé par un autre utilisateur.";
    }
    
    // Verificar email
    if (empty($data['email'])) {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    } elseif (!$candidatService->isEmailAvailable($data['email'], $userId)) {
        $errors[] = "Cet email est déjà utilisé par un autre utilisateur.";
    }
    
    // Verificar tipo de candidatura
    $allowedTypes = ['Créateur', 'Marque', 'Autre'];
    if (!in_array($data['type_candidature'], $allowedTypes)) {
        $errors[] = "Type de candidature invalide.";
    }
    
    // Processar foto de perfil
    $photoProfilPath = $candidat['photo_profil'] ?? null;
    $hasNewPhoto = !empty($_FILES['photo_profil']['name']) && $_FILES['photo_profil']['error'] === 0;
    
    if ($hasNewPhoto) {
        // Validar imagem
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        $fileType = mime_content_type($_FILES['photo_profil']['tmp_name']);
        $fileSize = $_FILES['photo_profil']['size'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Format d'image non supporté. Utilisez JPG, PNG, GIF ou WebP.";
        } elseif ($fileSize > $maxSize) {
            $errors[] = "L'image est trop volumineuse. Taille maximale: 5MB.";
        } else {
            // Upload da nova imagem
            $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Excluir imagem antiga se existir
            if ($photoProfilPath && file_exists(__DIR__ . '/../../public/' . $photoProfilPath)) {
                unlink(__DIR__ . '/../../public/' . $photoProfilPath);
            }
            
            $ext = pathinfo($_FILES['photo_profil']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $userId . '_' . time() . '.' . strtolower($ext);
            $dest = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $dest)) {
                $photoProfilPath = 'uploads/profiles/' . $filename;
            } else {
                $errors[] = "Erreur lors du téléchargement de l'image.";
            }
        }
    }
    
    // ADICIONAR CAMPOS QUE EXISTEM NA TABELA
    if (!empty($_POST['pays'])) {
        $data['pays'] = trim($_POST['pays']);
    }
    
    if (!empty($_POST['genre'])) {
        $allowedGenres = ['Homme', 'Femme', 'Autre'];
        if (in_array($_POST['genre'], $allowedGenres)) {
            $data['genre'] = $_POST['genre'];
        }
    }
    
    // Se não houver erros, atualizar perfil
    if (empty($errors)) {
        // Adicionar caminho da foto ao dados
        $data['photo_profil'] = $photoProfilPath;
        
        // Atualizar dados principais
        $success = $candidatService->updateCandidat($userId, $data);
        
        if ($success) {
            // Atualizar dados da sessão
            $_SESSION['user_pseudonyme'] = $data['pseudonyme'];
            $_SESSION['user_email'] = $data['email'];
            
            $_SESSION['success'] = "Profil mis à jour avec succès !";
            header('Location: perfil-candidat.php');
            exit;
        } else {
            $_SESSION['error'] = "Une erreur s'est produite lors de la mise à jour du profil.";
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
    
    // Redirecionar de volta ao formulário com os dados
    header('Location: perfil-candidat.php');
    exit;
} else {
    // Se não for POST, redirecionar para o perfil
    header('Location: perfil-candidat.php');
    exit;
}
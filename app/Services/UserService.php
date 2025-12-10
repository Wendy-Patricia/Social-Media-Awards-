<?php
require_once __DIR__ . '/../Models/User.php';

/**
 * Service de gestion des utilisateurs
 */
class UserService {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Traitement de la connexion
     */
    public function login($email, $password, $code2fa = null) {
        $result = $this->userModel->authenticate($email, $password, $code2fa);
        
        if ($result['success']) {
            // Initialiser la session
            initUserSession($result['user']);
            
            // Redirection selon le type d'utilisateur
            return [
                'success' => true,
                'redirect' => $this->getRedirectUrl($result['user']['user_type'])
            ];
        }
        
        return $result;
    }
    
    /**
     * Traitement de l'inscription
     */
    public function register($data) {
        // Validation des données
        $errors = $this->validateRegistration($data);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            // Vérifier si l'email existe déjà
            if ($this->userModel->emailExists($data['email'])) {
                return ['success' => false, 'errors' => ['email' => 'Cet email est déjà utilisé']];
            }
            
            // Créer le compte
            $user = $this->userModel->create($data);
            
            // Initialiser la session
            initUserSession($user);
            
            return [
                'success' => true,
                'user' => $user,
                'redirect' => $this->getRedirectUrl($user['user_type'])
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['general' => $e->getMessage()]];
        }
    }
    
    /**
     * Déconnexion
     */
    public function logout() {
        logout();
        return ['success' => true, 'redirect' => '/login.php'];
    }
    
    /**
     * Génère un code 2FA
     */
    public function generate2FACode() {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Valide les données d'inscription
     */
    private function validateRegistration($data) {
        $errors = [];
        
        // Pseudonyme
        if (empty($data['pseudonyme']) || strlen($data['pseudonyme']) < 3) {
            $errors['pseudonyme'] = 'Le pseudonyme doit avoir au moins 3 caractères';
        }
        
        // Email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide';
        }
        
        // Mot de passe
        if (empty($data['mot_de_passe']) || strlen($data['mot_de_passe']) < 6) {
            $errors['mot_de_passe'] = 'Le mot de passe doit avoir au moins 6 caractères';
        }
        
        if ($data['mot_de_passe'] !== ($data['confirm_mot_de_passe'] ?? '')) {
            $errors['confirm_mot_de_passe'] = 'Les mots de passe ne correspondent pas';
        }
        
        // Date de naissance
        if (empty($data['date_naissance'])) {
            $errors['date_naissance'] = 'Date de naissance requise';
        } else {
            $birthDate = new DateTime($data['date_naissance']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            
            if ($age < 13) {
                $errors['date_naissance'] = 'Vous devez avoir au moins 13 ans';
            }
        }
        
        // Pays
        if (empty($data['pays'])) {
            $errors['pays'] = 'Pays requis';
        }
        
        // Type d'utilisateur
        $allowedTypes = ['voter', 'candidate', 'admin'];
        if (empty($data['user_type']) || !in_array($data['user_type'], $allowedTypes)) {
            $errors['user_type'] = 'Type d\'utilisateur invalide';
        }
        
        return $errors;
    }
    
    /**
     * Détermine l'URL de redirection selon le type d'utilisateur
     */
    private function getRedirectUrl($userType) {
        switch($userType) {
            case 'admin':
                return '/admin/dashboard.php';
            case 'candidate':
                return '/candidate/dashboard.php';
            case 'voter':
                // Vérifier l'inscription à l'élection
                if ($this->hasElectionRegistration($_SESSION['user']['id_compte'])) {
                    return '/user/dashboard.php';
                } else {
                    return '/inscription-election.php';
                }
            default:
                return '/index.php';
        }
    }
    
    /**
     * Vérifie si l'utilisateur est inscrit à une élection
     */
    private function hasElectionRegistration($userId) {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM INSCRIPTION_ELECTION ie
                JOIN EDITION e ON ie.id_edition = e.id_edition
                WHERE ie.id_compte = ? 
                AND ie.statut = 'validé'
                AND e.date_debut <= NOW()
                AND e.date_fin >= NOW()
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
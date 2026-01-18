<?php
// app/Services/UserService.php 

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../../config/session.php';

/**
 * Service gérant l'authentification des utilisateurs
 * (Version simplifiée sans 2FA)
 */
class UserService {
    private $userModel;
    
    /**
     * Constructeur du service utilisateur
     */
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Gère le processus de connexion
     * - Authentifie l'utilisateur avec email et mot de passe
     * - Démarre la session en cas de succès
     * - Retourne le résultat de l'authentification
     * 
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @param string|null $code2fa Code 2FA (non utilisé dans cette version)
     * @return array Résultat de l'authentification
     */
    public function login($email, $password, $code2fa = null) {
        // Authentification directe : email et mot de passe
        $result = $this->userModel->authenticate($email, $password);
        
        if (!$result['success']) {
            return $result;
        }
        
        $user = $result['user'];
        
        // REMOVÉE : vérification 2FA
        // Connexion réussie - démarrer la session directement
        $this->startSession($user);
        
        return [
            'success' => true,
            'user' => $user,
            'redirect' => $this->getRedirectPath($user['role'])
        ];
    }
    
    /**
     * Démarre la session utilisateur
     * - Initialise les variables de session
     * - Enregistre les informations de l'utilisateur
     * 
     * @param array $user Informations de l'utilisateur
     */
    private function startSession($user) {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_pseudonyme'] = $user['pseudonyme'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }
    
    /**
     * Détermine le chemin de redirection selon le rôle de l'utilisateur
     * 
     * @param string $role Rôle de l'utilisateur
     * @return string Chemin de redirection approprié
     */
    private function getRedirectPath($role) {
        switch($role) {
            case 'admin':
                return '/Social-Media-Awards-/views/admin/admin-dashboard.php';
            case 'candidate':
                return '/Social-Media-Awards-/views/candidate/candidate-dashboard.php';
            case 'voter':
                return '/Social-Media-Awards-/views/user/user-dashboard.php';
            default:
                return '../index.php';
        }
    }
}
?>
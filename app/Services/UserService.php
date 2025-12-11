<?php
// app/Services/UserService.php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Interfaces/UserServiceInterface.php';

class UserService implements UserServiceInterface {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login($email, $password, $code2fa = null) {
        // Primeira verificação: email e senha
        $result = $this->userModel->authenticate($email, $password);
        
        if (!$result['success']) {
            return $result;
        }
        
        $user = $result['user'];
        
        // Verificar se precisa de 2FA (se tem código de verificação)
        $userData = $this->userModel->getUserByEmail($email);
        
        if ($userData && !empty($userData['code_verification'])) {
            if (empty($code2fa)) {
                // Requer 2FA
                return [
                    'success' => false,
                    'requires_2fa' => true,
                    'email' => $email
                ];
            }
            
            // Verificar código 2FA
            if (!$this->userModel->verify2FACode($email, $code2fa)) {
                return [
                    'success' => false,
                    'message' => 'Code de vérification incorrect'
                ];
            }
        }
        
        // Login bem-sucedido - iniciar sessão
        $this->startSession($user);
        
        return [
            'success' => true,
            'redirect' => $this->getRedirectPath($user['role'])
        ];
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    public function getUserType() {
        return $_SESSION['user_role'] ?? null;
    }
    
    private function startSession($user) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_pseudonyme'] = $user['pseudonyme'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }
    
    private function getRedirectPath($role) {
        switch($role) {
            case 'admin':
                return '/admin/dashboard.php';
            case 'candidate':
                return '/candidate/dashboard.php';
            case 'voter':
                return '/user/dashboard.php';
            default:
                return '/index.php';
        }
    }
}
?>
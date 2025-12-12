
<?php
// app/Services/UserService.php - SEM 2FA

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../../config/session.php';

class UserService {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login($email, $password, $code2fa = null) {
        // Verificação direta: email e senha
        $result = $this->userModel->authenticate($email, $password);
        
        if (!$result['success']) {
            return $result;
        }
        
        $user = $result['user'];
        
        // REMOVIDA verificação de 2FA
        // Login bem-sucedido - iniciar sessão diretamente
        $this->startSession($user);
        
        return [
            'success' => true,
            'user' => $user,
            'redirect' => $this->getRedirectPath($user['role'])
        ];
    }
    
    private function startSession($user) {
        // Iniciar sessão se não estiver iniciada
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
    
    private function getRedirectPath($role) {
        switch($role) {
            case 'admin':
                return '../admin/admin-dashboard.php';
            case 'candidate':
                return '../candidate/candidate-dashboard.php';
            case 'voter':
                return '../user/user-dashboard.php';
            default:
                return '../index.php';
        }
    }
}
?>

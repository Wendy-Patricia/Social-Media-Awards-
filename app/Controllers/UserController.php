
<?php
// app/Controllers/UserController.php - SEM 2FA

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Services/UserService.php';

class UserController {
    private $userService;
    private $userModel;
    
    public function __construct() {
        $this->userService = new UserService();
        $this->userModel = new User();
    }
    
    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [];
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['mot_de_passe'] ?? '';
        
        // NOTA: removido parâmetro $code2fa
        $result = $this->userService->login($email, $password);
        
        if ($result['success']) {
            $redirect = $this->getDashboardPath($result['user']['role']);
            header('Location: ' . $redirect);
            exit();
        }
        
        return [
            'error' => $result['message']
            // Removidos: 'requires_2fa' e 'temp_email'
        ];
    }
    
    public function handleRegistration() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'errors' => []];
        }
        
        $errors = [];
        $data = [
            'pseudonyme' => trim($_POST['pseudonyme'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
            'confirm_mot_de_passe' => $_POST['confirm_mot_de_passe'] ?? '',
            'type_user' => $_POST['type_user'] ?? '',
            'date_naissance' => $_POST['date_naissance'] ?? '',
            'pays' => $_POST['pays'] ?? '',
            'genre' => $_POST['genre'] ?? ''
        ];
        
        // Validações (mantidas)
        if (empty($data['pseudonyme']) || strlen($data['pseudonyme']) < 3) {
            $errors[] = "Le pseudonyme doit contenir au moins 3 caractères";
    
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide";
        
        }
        
        if (strlen($data['mot_de_passe']) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
        }
        
        if ($data['mot_de_passe'] !== $data['confirm_mot_de_passe']) {
            $errors[] = "Les mots de passe ne correspondent pas";
        }
        
        if (empty($data['type_user']) || !in_array($data['type_user'], ['voter', 'candidate'])) {
            $errors[] = "Veuillez sélectionner un type d'utilisateur valide";
        }
        
        if (empty($data['date_naissance'])) {
            $errors[] = "La date de naissance est obligatoire";
        } elseif (strtotime($data['date_naissance']) > strtotime('-13 years')) {
            $errors[] = "Vous devez avoir au moins 13 ans";
        }
        
        if (empty($data['pays'])) {
            $errors[] = "Le pays est obligatoire";
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'data' => $data];
        }
        
        // Hash da senha
        $hashedPassword = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        
        // Código de verificação opcional (podemos usar '000000' como placeholder)
        $codeVerification = '000000';
        
        // Dados para criação
        $userData = [
            'pseudonyme' => $data['pseudonyme'],
            'email' => $data['email'],
            'mot_de_passe' => $hashedPassword,
            'date_naissance' => $data['date_naissance'],
            'pays' => $data['pays'],
            'genre' => $data['genre'] ?? '',
            'code_verification' => $codeVerification,
            'type_user' => $data['type_user']
        ];
        
        // Criar usuário
        $userId = $this->userModel->createUser($userData);
        
        if ($userId) {
            // Determinar role
            $role = $data['type_user'] === 'candidate' ? 'candidate' : 'voter';
            
            // Iniciar sessão
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_pseudonyme'] = $data['pseudonyme'];
            $_SESSION['user_email'] = $data['email'];
            $_SESSION['user_role'] = $role;
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            return [
                'success' => true,
                'user_id' => $userId,
                'role' => $role,
                'data' => $data
            ];
        }
        
        return ['success' => false, 'errors' => ['Erreur lors de l\'inscription'], 'data' => $data];
    }
    
    private function getDashboardPath($role) {
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
    
    public function logout() {
        session_start();
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        return true;
    }
}
?>

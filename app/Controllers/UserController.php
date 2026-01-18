<?php
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Services/UserService.php';

/**
 * Contrôleur gérant l'authentification et l'inscription des utilisateurs
 */
class UserController
{
    private $userService;
    private $userModel;

    /**
     * Constructeur du contrôleur utilisateur
     */
    public function __construct()
    {
        $this->userService = new UserService();
        $this->userModel = new User();
    }

    /**
     * Gère le processus de connexion
     * - Authentifie l'utilisateur
     * - Définit les variables de session
     * - Redirige vers la page appropriée
     * 
     * @return array Résultat de l'authentification
     */
    public function handleLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [];
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['mot_de_passe'] ?? '';
        $redirectParam = $_POST['redirect'] ?? ''; // Récupère la redirection

        $result = $this->userService->login($email, $password);

        if ($result['success']) {
            // PRIORITÉS DE REDIRECTION :
            // 1. Paramètre 'redirect' du formulaire (venant de login.php)
            // 2. Dashboard par défaut selon le rôle
            if (!empty($redirectParam)) {
                $redirect = $redirectParam;
            } else {
                $redirect = $this->getDashboardPath($result['user']['role']);
            }
            
            header('Location: ' . $redirect);
            exit();
        }

        return [
            'error' => $result['message']
        ];
    }

    /**
     * Gère l'inscription d'un nouvel utilisateur
     * - Valide les données
     * - Crée le compte
     * - Redirige vers le dashboard approprié
     * 
     * @return array Résultat de l'inscription
     */
    public function handleRegistration()
    {
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

        // Validations du pseudonyme
        if (empty($data['pseudonyme']) || strlen($data['pseudonyme']) < 3) {
            $errors[] = "Le pseudonyme doit contenir au moins 3 caractères";
        } elseif (strlen($data['pseudonyme']) > 50) {
            $errors[] = "Le pseudonyme ne doit pas dépasser 50 caractères";
        }

        // Vérification unicité du pseudonyme
        if (!empty($data['pseudonyme']) && strlen($data['pseudonyme']) >= 3) {
            $existingPseudonyme = $this->userModel->getUserByPseudonyme($data['pseudonyme']);
            if ($existingPseudonyme) {
                $errors[] = "Ce pseudonyme est déjà utilisé. Veuillez en choisir un autre.";
            }
        }

        // Validation de l'email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide";
        }

        // Vérification unicité de l'email
        $existingUser = $this->userModel->getUserByEmail($data['email']);
        if ($existingUser) {
            $errors[] = "Cet email est déjà utilisé";
        }

        // Validation du mot de passe
        if (strlen($data['mot_de_passe']) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
        }

        if ($data['mot_de_passe'] !== $data['confirm_mot_de_passe']) {
            $errors[] = "Les mots de passe ne correspondent pas";
        }

        // Validation du type d'utilisateur
        if (empty($data['type_user']) || !in_array($data['type_user'], ['voter', 'candidate'])) {
            $errors[] = "Veuillez sélectionner un type d'utilisateur valide";
        }

        // Validation de la date de naissance (âge minimum 13 ans)
        if (empty($data['date_naissance'])) {
            $errors[] = "La date de naissance est obligatoire";
        } elseif (strtotime($data['date_naissance']) > strtotime('-13 years')) {
            $errors[] = "Vous devez avoir au moins 13 ans";
        }

        // VALIDATION DU PAYS
        if (empty($data['pays'])) {
            $errors[] = "Le pays est obligatoire";
        }

        // Vérification acceptation des conditions
        if (!isset($_POST['terms'])) {
            $errors[] = "Vous devez accepter les conditions d'utilisation";
        }

        // Retourner les erreurs si validation échoue
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors, 'data' => $data];
        }

        try {
            // Hashage du mot de passe
            $hashedPassword = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);

            // Code de vérification (à remplacer par un vrai système plus tard)
            $codeVerification = '000000';

            // Préparation des données pour la table compte
            $userData = [
                ':pseudonyme' => $data['pseudonyme'],
                ':email' => $data['email'],
                ':mot_de_passe' => $hashedPassword,
                ':date_naissance' => $data['date_naissance'],
                ':pays' => $data['pays'],
                ':genre' => $data['genre'] ?? null,
                ':code_verification' => $codeVerification
            ];

            // Création du compte principal
            $userId = $this->userModel->createUser($userData);

            if (!$userId) {
                return ['success' => false, 'errors' => ['Erreur lors de la création du compte'], 'data' => $data];
            }

            // Insertion dans la table spécifique selon le type d'utilisateur
            if ($data['type_user'] === 'candidate') {
                $this->insertCandidate($userId);
                $role = 'candidate';
            } else {
                $this->insertUtilisateur($userId);
                $role = 'voter';
            }

            // Démarrage de la session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Enregistrement des informations de session
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_pseudonyme'] = $data['pseudonyme'];
            $_SESSION['user_email'] = $data['email'];
            $_SESSION['user_role'] = $role;
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();

            // Redirection vers le tableau de bord approprié
            $redirect = $this->getDashboardPath($role);
            header('Location: ' . $redirect);
            exit();
        } catch (Exception $e) {
            error_log("Erreur inscription: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Une erreur technique est survenue'], 'data' => $data];
        }
    }

    /**
     * Insère un nouvel enregistrement dans la table CANDIDAT
     * 
     * @param int $userId ID du compte utilisateur
     * @return bool Succès de l'insertion
     */
    private function insertCandidate($userId)
    {
        try {
            $db = $this->userModel->getDb();
            $stmt = $db->prepare("
                INSERT INTO candidat (id_compte, nom_legal_ou_societe, type_candidature, est_nomine) 
                VALUES (:id_compte, NULL, 'Autre', 0)
            ");
            return $stmt->execute([':id_compte' => $userId]);
        } catch (Exception $e) {
            error_log("Erreur insertion candidat: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Insère un nouvel enregistrement dans la table UTILISATEUR
     * 
     * @param int $userId ID du compte utilisateur
     * @return bool Succès de l'insertion
     */
    private function insertUtilisateur($userId)
    {
        try {
            $db = $this->userModel->getDb();
            $stmt = $db->prepare("
                INSERT INTO utilisateur (id_compte) 
                VALUES (:id_compte)
            ");
            return $stmt->execute([':id_compte' => $userId]);
        } catch (Exception $e) {
            error_log("Erreur insertion utilisateur: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Détermine le chemin du dashboard selon le rôle de l'utilisateur
     * 
     * @param string $role Rôle de l'utilisateur (admin, voter, candidate)
     * @return string Chemin du dashboard approprié
     */
    private function getDashboardPath($role)
    {
        switch ($role) {
            case 'admin':
                return '/Social-Media-Awards-/views/admin/dashboard.php';
            case 'candidate':
                return '/Social-Media-Awards-/views/candidate/candidate-dashboard.php';
            case 'voter':
                // MODIFICATION : ÉLECTEURS REDIRIGÉS VERS LA PAGE DE VOTE
                return '/Social-Media-Awards-/views/user/Vote.php';
            default:
                return '/index.php';
        }
    }

    /**
     * Gère la déconnexion de l'utilisateur
     * - Nettoie la session
     * - Détruit les cookies
     * - Redirige vers l'accueil
     * 
     * @return bool Succès de la déconnexion
     */
    public function logout()
    {
        session_start();
        $_SESSION = array();

        // SUPPRESSION DES COOKIES DE SESSION
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        return true;
    }

    /**
     * Obtient l'instance de la base de données
     * 
     * @return PDO Instance de connexion à la base de données
     */
    private function getDb()
    {
        return $this->userModel->getDb();
    }
}
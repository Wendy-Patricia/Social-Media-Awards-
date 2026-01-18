<?php
require_once __DIR__ . '/../../config/database.php';

/**
 * Modèle gérant les opérations de base de données pour les utilisateurs
 * - Authentification
 * - Création de compte
 * - Récupération et mise à jour des profils
 * - Vérifications d'unicité
 */
class User {
    private $db;

    /**
     * Constructeur du modèle utilisateur
     * Initialise la connexion à la base de données
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtient l'instance de connexion à la base de données
     * 
     * @return PDO Instance de connexion à la base de données
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * Authentifie un utilisateur par email et mot de passe
     * - Vérifie les identifiants
     * - Détermine le rôle de l'utilisateur
     * - Retourne les informations utilisateur en cas de succès
     * 
     * @param string $email Adresse email de l'utilisateur
     * @param string $password Mot de passe en clair
     * @return array Résultat de l'authentification
     */
    public function authenticate($email, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                    CASE 
                        WHEN a.id_compte IS NOT NULL THEN 'admin'
                        WHEN ca.id_compte IS NOT NULL THEN 'candidate'
                        WHEN u.id_compte IS NOT NULL THEN 'voter'
                        ELSE 'unknown'
                    END as role
                FROM compte c
                LEFT JOIN administrateur a ON c.id_compte = a.id_compte
                LEFT JOIN candidat ca ON c.id_compte = ca.id_compte
                LEFT JOIN utilisateur u ON c.id_compte = u.id_compte
                WHERE c.email = :email
                LIMIT 1
            ");
            
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['mot_de_passe'])) {
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user['id_compte'],
                        'pseudonyme' => $user['pseudonyme'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ];
            }
            
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
            
        } catch (PDOException $e) {
            error_log("Erreur authentification: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion'];
        }
    }

    /**
     * Récupère un utilisateur par son adresse email
     * 
     * @param string $email Adresse email à rechercher
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM compte WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Récupère un utilisateur par son pseudonyme
     * 
     * @param string $pseudonyme Pseudonyme à rechercher
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getUserByPseudonyme($pseudonyme) {
        $stmt = $this->db->prepare("SELECT * FROM compte WHERE pseudonyme = :pseudonyme");
        $stmt->execute([':pseudonyme' => $pseudonyme]);
        return $stmt->fetch();
    }

    /**
     * Crée un nouvel utilisateur dans la base de données
     * - Insère les informations de base dans la table 'compte'
     * - Retourne l'ID du nouvel utilisateur
     * 
     * @param array $data Données de l'utilisateur à créer
     * @return int|false ID du nouvel utilisateur ou false en cas d'erreur
     */
    public function createUser($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO compte (pseudonyme, email, mot_de_passe, date_naissance, pays, genre, code_verification)
                VALUES (:pseudonyme, :email, :mot_de_passe, :date_naissance, :pays, :genre, :code_verification)
            ");
            
            $stmt->execute($data);
            return $this->db->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Erreur createUser: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un utilisateur par son ID avec son rôle
     * - Joint les tables de rôles pour déterminer le type d'utilisateur
     * 
     * @param int $userId ID de l'utilisateur à rechercher
     * @return array|false Données complètes de l'utilisateur ou false si non trouvé
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                    CASE 
                        WHEN a.id_compte IS NOT NULL THEN 'admin'
                        WHEN ca.id_compte IS NOT NULL THEN 'candidate'
                        WHEN u.id_compte IS NOT NULL THEN 'voter'
                        ELSE 'unknown'
                    END as role
                FROM compte c
                LEFT JOIN administrateur a ON c.id_compte = a.id_compte
                LEFT JOIN candidat ca ON c.id_compte = ca.id_compte
                LEFT JOIN utilisateur u ON c.id_compte = u.id_compte
                WHERE c.id_compte = :id_compte
                LIMIT 1
            ");
            
            $stmt->execute([':id_compte' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erreur getUserById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour le profil d'un utilisateur
     * - Met à jour uniquement les champs fournis
     * - Gère les mises à jour partielles
     * 
     * @param int $userId ID de l'utilisateur à mettre à jour
     * @param array $data Données à mettre à jour
     * @return bool Succès de la mise à jour
     */
    public function updateUserProfile($userId, $data) {
        try {
            $fields = [];
            $params = [':id_compte' => $userId];
            
            if (isset($data['pseudonyme'])) {
                $fields[] = 'pseudonyme = :pseudonyme';
                $params[':pseudonyme'] = $data['pseudonyme'];
            }
            
            if (isset($data['email'])) {
                $fields[] = 'email = :email';
                $params[':email'] = $data['email'];
            }
            
            if (isset($data['date_naissance'])) {
                $fields[] = 'date_naissance = :date_naissance';
                $params[':date_naissance'] = $data['date_naissance'];
            }
            
            if (isset($data['pays'])) {
                $fields[] = 'pays = :pays';
                $params[':pays'] = $data['pays'];
            }
            
            if (isset($data['genre'])) {
                $fields[] = 'genre = :genre';
                $params[':genre'] = $data['genre'];
            }
            
            if (isset($data['photo_profil'])) {
                $fields[] = 'photo_profil = :photo_profil';
                $params[':photo_profil'] = $data['photo_profil'];
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $sql = "UPDATE compte SET " . implode(', ', $fields) . " WHERE id_compte = :id_compte";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Erreur updateUserProfile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un email est déjà utilisé
     * - Optionnellement exclut un utilisateur spécifique (pour les mises à jour)
     * 
     * @param string $email Email à vérifier
     * @param int|null $excludeUserId ID de l'utilisateur à exclure de la vérification
     * @return bool True si l'email est déjà utilisé
     */
    public function isEmailTaken($email, $excludeUserId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM compte WHERE email = :email";
            $params = [':email' => $email];
            
            if ($excludeUserId) {
                $sql .= " AND id_compte != :exclude_id";
                $params[':exclude_id'] = $excludeUserId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Erreur isEmailTaken: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un pseudonyme est déjà utilisé
     * - Optionnellement exclut un utilisateur spécifique (pour les mises à jour)
     * 
     * @param string $pseudonyme Pseudonyme à vérifier
     * @param int|null $excludeUserId ID de l'utilisateur à exclure de la vérification
     * @return bool True si le pseudonyme est déjà utilisé
     */
    public function isPseudonymeTaken($pseudonyme, $excludeUserId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM compte WHERE pseudonyme = :pseudonyme";
            $params = [':pseudonyme' => $pseudonyme];
            
            if ($excludeUserId) {
                $sql .= " AND id_compte != :exclude_id";
                $params[':exclude_id'] = $excludeUserId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Erreur isPseudonymeTaken: " . $e->getMessage());
            return false;
        }
    }
}
?>
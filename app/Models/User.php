<?php
// app/Models/User.php - VERSÃO CORRIGIDA

require_once __DIR__ . '/DBModel.php';

class User extends DBModel {
    private $id_compte;
    private $pseudonyme;
    private $email;
    private $date_creation;
    private $role;
    
    public function __construct() {
        parent::__construct();
    }
    
    // Adicione este método para obter a conexão PDO
    public function getDb() {
        return $this->db;
    }
    
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
    
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM compte WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
    
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
            error_log("Dados: " . print_r($data, true));
            return false;
        }
    }
    /**
     * Obter usuário pelo ID
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
     * Atualizar perfil do usuário
     */
    public function updateUserProfile($userId, $data) {
        try {
            // Construir a query dinamicamente
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
                return false; // Nada para atualizar
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
     * Verificar se email já existe (exceto para o usuário atual)
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
     * Verificar se pseudonyme já existe (exceto para o usuário atual)
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
    public function getUserByPseudonyme($pseudonyme) {
    try {
        $stmt = $this->db->prepare("SELECT * FROM compte WHERE pseudonyme = :pseudonyme");
        $stmt->execute([':pseudonyme' => $pseudonyme]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Erreur getUserByPseudonyme: " . $e->getMessage());
        return false;
    }
}
    
    // Getters
    public function getId() { return $this->id_compte; }
    public function getPseudonyme() { return $this->pseudonyme; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
}
?>
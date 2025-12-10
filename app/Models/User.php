<?php
/**
 * Modèle COMPTE - Conforme MCD
 */
class User {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDB();
    }
    
    /**
     * Authentifie un utilisateur avec vérification 2FA
     */
    public function authenticate($email, $password, $code2fa = null) {
        // Récupération du compte avec son type
        $stmt = $this->pdo->prepare("
            SELECT 
                c.*,
                CASE 
                    WHEN a.id_compte IS NOT NULL THEN 'admin'
                    WHEN ca.id_compte IS NOT NULL THEN 'candidate'
                    ELSE 'voter'
                END as user_type
            FROM COMPTE c
            LEFT JOIN ADMINISTRATEUR a ON c.id_compte = a.id_compte
            LEFT JOIN CANDIDAT ca ON c.id_compte = ca.id_compte
            WHERE c.email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
        
        // Vérification du mot de passe
        if (!password_verify($password, $user['mot_de_passe'])) {
            return ['success' => false, 'message' => 'Mot de passe incorrect'];
        }
        
        // Vérification 2FA si activé
        if (!empty($user['code_verification'])) {
            if (empty($code2fa)) {
                return [
                    'success' => false, 
                    'requires_2fa' => true,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT)
                ];
            }
            
            if ($user['code_verification'] !== $code2fa) {
                return ['success' => false, 'message' => 'Code 2FA incorrect'];
            }
        }
        
        return [
            'success' => true, 
            'user' => $user
        ];
    }
    
    /**
     * Crée un nouveau compte (inscription)
     */
    public function create($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Insertion dans COMPTE
            $stmt = $this->pdo->prepare("
                INSERT INTO COMPTE 
                (pseudonyme, email, mot_de_passe, date_naissance, pays, genre, photo_profil) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['pseudonyme'],
                $data['email'],
                password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
                $data['date_naissance'],
                $data['pays'],
                $data['genre'] ?? null,
                $data['photo_profil'] ?? null
            ]);
            
            $id_compte = $this->pdo->lastInsertId();
            
            // Création du type d'utilisateur spécifique
            switch($data['user_type']) {
                case 'candidate':
                    $stmt = $this->pdo->prepare("
                        INSERT INTO CANDIDAT (id_compte, biographie, statut) 
                        VALUES (?, '', 'en_attente')
                    ");
                    break;
                case 'admin':
                    $stmt = $this->pdo->prepare("
                        INSERT INTO ADMINISTRATEUR (id_compte, niveau_acces) 
                        VALUES (?, 1)
                    ");
                    break;
                default: // voter
                    $stmt = $this->pdo->prepare("
                        INSERT INTO UTILISATEUR (id_compte, adresse, ville) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([
                        $id_compte,
                        $data['adresse'] ?? '',
                        $data['ville'] ?? ''
                    ]);
                    break;
            }
            
            if (isset($stmt)) {
                $stmt->execute([$id_compte]);
            }
            
            $this->pdo->commit();
            
            // Récupérer l'utilisateur créé
            return $this->findById($id_compte);
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Erreur création compte: " . $e->getMessage());
        }
    }
    
    /**
     * Trouve un utilisateur par ID
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                c.*,
                CASE 
                    WHEN a.id_compte IS NOT NULL THEN 'admin'
                    WHEN ca.id_compte IS NOT NULL THEN 'candidate'
                    ELSE 'voter'
                END as user_type
            FROM COMPTE c
            LEFT JOIN ADMINISTRATEUR a ON c.id_compte = a.id_compte
            LEFT JOIN CANDIDAT ca ON c.id_compte = ca.id_compte
            WHERE c.id_compte = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Vérifie si l'email existe déjà
     */
    public function emailExists($email) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM COMPTE WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Met à jour le code 2FA
     */
    public function update2FACode($userId, $code) {
        $stmt = $this->pdo->prepare("
            UPDATE COMPTE 
            SET code_verification = ? 
            WHERE id_compte = ?
        ");
        return $stmt->execute([$code, $userId]);
    }
}
?>
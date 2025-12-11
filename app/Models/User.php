<?php
// app/Models/User.php

require_once __DIR__ . '/Database.php';

class User extends DBModel {
    private $id_compte;
    private $pseudonyme;
    private $email;
    private $date_creation;
    private $role; // 'admin', 'candidate', 'voter'
    
    public function __construct() {
        parent::__construct();
    }
    
    public function authenticate($email, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                    CASE 
                        WHEN a.id_compte IS NOT NULL THEN 'admin'
                        WHEN ca.id_compte IS NOT NULL THEN 'candidate'
                        ELSE 'voter'
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
                $this->id_compte = $user['id_compte'];
                $this->pseudonyme = $user['pseudonyme'];
                $this->email = $user['email'];
                $this->date_creation = $user['date_creation'];
                $this->role = $user['role'];
                
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
            return ['success' => false, 'message' => 'Erreur de base de données'];
        }
    }
    
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM compte WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
    
    public function verify2FACode($email, $code) {
        $stmt = $this->db->prepare("
            SELECT * FROM compte 
            WHERE email = :email AND code_verification = :code
        ");
        $stmt->execute([
            ':email' => $email,
            ':code' => $code
        ]);
        
        return $stmt->fetch() !== false;
    }
    
    // Getters
    public function getId() { return $this->id_compte; }
    public function getPseudonyme() { return $this->pseudonyme; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
}
?>
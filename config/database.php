<?php
// config/database.php

/**
 * Classe de connexion singleton à la base de données
 * Gère une connexion unique et centralisée pour toute l'application
 */
class Database
{
    // Instance unique de la classe (pattern Singleton)
    private static $instance = null;
    
    // Connexion PDO à la base de données
    private $connection;
    
    /**
     * Constructeur privé (pattern Singleton)
     * Établit la connexion à la base de données avec les paramètres définis
     */
    private function __construct()
    {
        try {
            // Configuration de la connexion
            $host = 'localhost';
            $dbname = 'social_media_awards';
            $username = 'root';
            $password = '';
            
            // Création de l'instance PDO avec options recommandées
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // Exceptions pour les erreurs SQL
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Résultats sous forme de tableau associatif
                    PDO::ATTR_EMULATE_PREPARES => false               // Préparation réelle des requêtes
                ]
            );
            
        } catch (PDOException $e) {
            // Message d'erreur convivial en production
            // En développement, on pourrait logger l'erreur complète
            die("Erro de conexão à base de dados. Verifique se o MySQL está a correr e se os dados estão corretos.");
        }
    }
    
    /**
     * Obtient l'instance unique de la classe Database
     * 
     * @return Database Instance unique de la base de données
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    /**
     * Retourne la connexion PDO active
     * 
     * @return PDO Connexion à la base de données
     */
    public function getConnection()
    {
        return $this->connection;
    }
}

/**
 * Fonction utilitaire pour obtenir rapidement la connexion à la base de données
 * 
 * @return PDO Connexion à la base de données
 * 
 * Note: Fonction conservée pour compatibilité mais non utilisée directement dans le projet actuel
 */
function getDB()
{
    return Database::getInstance()->getConnection();
}
?>

<?php
// app/Models/DBModel.php

require_once __DIR__ . '/../../config/database.php';

abstract class DBModel {
    protected $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Métodos comuns para todos os modelos
    protected function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    protected function commit() {
        return $this->db->commit();
    }
    
    protected function rollBack() {
        return $this->db->rollBack();
    }
    
    protected function lastInsertId() {
        return $this->db->lastInsertId();
    }
    
    // Método para executar queries com parâmetros
    protected function executeQuery($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    // Método para buscar um único registro
    protected function fetchOne($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    // Método para buscar múltiplos registros
    protected function fetchAll($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>

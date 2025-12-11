<?php
// app/Models/Database.php

require_once __DIR__ . '/../../config/database.php';

class DBModel {
    protected $db;
    
    public function __construct() {
        $this->db = getDB();
    }
}
?>
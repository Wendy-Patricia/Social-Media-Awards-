<?php
// app/autoload.php

spl_autoload_register(function ($class) {
    // Base directory
    $base_dir = __DIR__ . '/';
    
    // Remove o namespace principal
    $class = str_replace('App\\', '', $class);
    
    // Converte namespace para caminho de arquivo
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    
    // Verifica se o arquivo existe
    if (file_exists($file)) {
        require_once $file;
    }
});
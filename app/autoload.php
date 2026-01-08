<?php
spl_autoload_register(function ($class) {
    // Apenas processa classes que começam com "App\"
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/'; // Pasta atual: app/

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Não é uma classe do nosso namespace → ignora
        return;
    }

    // Remove o prefixo e converte namespace em caminho de diretório
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Se o ficheiro existir, inclui-o
    if (file_exists($file)) {
        require $file;
    }
});
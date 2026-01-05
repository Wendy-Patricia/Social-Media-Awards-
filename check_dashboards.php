
<?php
// check_dashboards.php - Verificar estrutura de dashboards
echo "<h1>Verificação de Estrutura</h1>";

$paths = [
    'views/user/' => 'user-dashboard.php',
    'views/candidate/' => 'candidate-dashboard.php', 
    'views/admin/' => 'admin-dashboard.php',
    'views/' => 'login.php',
    '' => 'inscription.php'
];

foreach ($paths as $folder => $file) {
    $fullPath = $folder . $file;
    
    if (file_exists($fullPath)) {
        echo "<p style='color: green;'>✓ $fullPath EXISTE</p>";
    } else {
        echo "<p style='color: red;'>✗ $fullPath NÃO EXISTE</p>";
        
        // Se for pasta, verificar se existe
        if ($folder && !is_dir($folder)) {
            echo "<p style='color: orange;'>⚠ A pasta '$folder' também não existe</p>";
        }
    }
}

// Verificar redirecionamento atual
echo "<h2>Teste de Redirecionamento</h2>";
session_start();
if (isset($_SESSION['user_role'])) {
    echo "<p>Role na sessão: " . $_SESSION['user_role'] . "</p>";
    
    $redirect = match($_SESSION['user_role']) {
        'admin' => 'admin/admin-dashboard.php',
        'candidate' => 'candidate/candidate-dashboard.php',
        'voter' => 'user/user-dashboard.php',
        default => 'index.php'
    };
    
    echo "<p>Redirecionaria para: $redirect</p>";
    echo "<p><a href='$redirect'>Testar link</a></p>";
} else {
    echo "<p>Nenhuma sessão ativa</p>";
}
?>

[file name]: create_new_user.php
[file content begin]
<?php
// create_new_user.php - Criar novo usuário funcionando
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

try {
    $db = getDB();
    
    echo "<h1>Criar Novo Usuário Funcional</h1>";
    
    // Dados
    $pseudonyme = 'testuser' . rand(100, 999);
    $email = 'euniceligeiro@gmail.com';
    $password = '123456';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<p><strong>Credenciais de teste:</strong></p>";
    echo "<ul>";
    echo "<li>Email: <code>$email</code></li>";
    echo "<li>Senha: <code>$password</code></li>";
    echo "</ul>";
    
    // Inserir
    $db->beginTransaction();
    
    // 1. Inserir na compte
    $sql1 = "INSERT INTO compte (pseudonyme, email, mot_de_passe, date_naissance, pays, code_verification) 
             VALUES (:pseudonyme, :email, :mot_de_passe, :date_naissance, :pays, :code_verification)";
    
    $stmt1 = $db->prepare($sql1);
    $result1 = $stmt1->execute([
        ':pseudonyme' => $pseudonyme,
        ':email' => $email,
        ':mot_de_passe' => $hashedPassword,
        ':date_naissance' => '1990-01-01',
        ':pays' => 'France',
        ':code_verification' => '000000'
    ]);
    
    if (!$result1) {
        throw new Exception("Erro ao inserir em compte");
    }
    
    $userId = $db->lastInsertId();
    
    // 2. Inserir na utilisateur
    $sql2 = "INSERT INTO utilisateur (id_compte) VALUES (:id)";
    $stmt2 = $db->prepare($sql2);
    $result2 = $stmt2->execute([':id' => $userId]);
    
    if (!$result2) {
        throw new Exception("Erro ao inserir em utilisateur");
    }
    
    $db->commit();
    
    echo "<h2 style='color: green;'>✓ Usuário criado com sucesso! ID: $userId</h2>";
    
    // Testar login programaticamente
    echo "<h2>Teste Automático de Login:</h2>";
    
    require_once 'app/Models/User.php';
    $userModel = new User();
    $authResult = $userModel->authenticate($email, $password);
    
    echo "<pre>";
    print_r($authResult);
    echo "</pre>";
    
    if ($authResult['success']) {
        echo "<p style='color: green; font-size: 20px;'>✓ LOGIN FUNCIONANDO!</p>";
        echo "<p><a href='views/login.php' style='background: green; color: white; padding: 15px; font-size: 18px; text-decoration: none;'>👉 FAZER LOGIN AGORA</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Problema no login: " . $authResult['message'] . "</p>";
    }
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>
[file content end]
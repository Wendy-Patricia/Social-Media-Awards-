
<?php
// clear_session.php
session_start();
session_destroy();

// Limpar cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

echo "<h1>Sessão Limpa!</h1>";
echo "<p>Sua sessão foi destruída. Agora você pode:</p>";
echo "<ul>";
echo "<li><a href='inscription.php'>Criar nova conta</a></li>";
echo "<li><a href='views/login.php'>Fazer login</a></li>";
echo "</ul>";

// Redirecionar automaticamente após 3 segundos
header("refresh:3;url=inscription.php");
?>

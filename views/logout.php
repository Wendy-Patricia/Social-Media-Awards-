
<?php
// logout.php
require_once 'app/Controllers/UserController.php';

$controller = new UserController();
$controller->logout();

header('Location: index.php');
exit();
?>

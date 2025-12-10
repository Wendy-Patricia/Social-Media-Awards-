<?php
require_once '../config/session.php';
require_once '../app/Services/UserService.php';

$userService = new UserService();
$result = $userService->logout();

// Redirection avec message
$_SESSION['logout_message'] = "Vous avez été déconnecté avec succès.";
header('Location: /login.php');
exit();
?>
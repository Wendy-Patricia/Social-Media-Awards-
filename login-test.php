<?php
require_once 'config/session.php';

$_SESSION['user'] = [
    'id' => 1,
    'nom' => 'Admin Test',
    'role' => 'admin'
];

header('Location: /Social-Media-Awards-/admin/categories');
exit;
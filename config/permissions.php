<?php
require_once __DIR__ . '/session.php';

function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /Social-Media-Awards-/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        die('Accès refusé. Vous devez être administrateur.');
    }
}
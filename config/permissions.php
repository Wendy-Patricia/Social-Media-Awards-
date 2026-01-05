<?php

function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        die('Access denied');
    }
}

function requireCandidate() {
    requireLogin();
    if ($_SESSION['user']['role'] !== 'candidate') {
        http_response_code(403);
        die('Access denied');
    }
}

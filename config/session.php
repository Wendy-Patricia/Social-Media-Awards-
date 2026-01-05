<?php
// config/session.php

require_once __DIR__ . '/database.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAuthenticated() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function getUserType() {
    return $_SESSION['user_role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserEmail() {
    return $_SESSION['user_email'] ?? null;
}

function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: /login.php');
        exit();
    }
}

function requireRole($role) {
    requireAuth();
    
    if (getUserType() !== $role) {
        // Redirecionar para dashboard apropriado
        $redirect = match(getUserType()) {
            'admin' => '/admin/dashboard.php',
            'candidate' => '/candidate/dashboard.php',
            'voter' => '/user/dashboard.php',
            default => '/index.php'
        };
        
        header("Location: $redirect");
        exit();
    }
}
?><?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

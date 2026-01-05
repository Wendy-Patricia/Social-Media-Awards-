<?php
// config/session.php

require_once __DIR__ . '/database.php';

// Iniciar sessão apenas uma vez
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAuthenticated() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
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

function getUserPseudo() {
    return $_SESSION['user_pseudonyme'] ?? null;
}

function requireAuth() {
    if (!isAuthenticated()) {
        $loginPath = '/Social-Media-Awards-/views/login.php';
        header("Location: $loginPath");
        exit();
    }
}

function requireRole($role) {
    requireAuth();
    
    if (getUserType() !== $role) {
        $redirect = match(getUserType()) {
            'admin' => '/Social-Media-Awards-/views/admin/dashboard.php',
            'candidate' => '/Social-Media-Awards-/views/candidate/candidate-dashboard.php',
            'voter' => '/Social-Media-Awards-/views/user/user-dashboard.php',
            default => '/Social-Media-Awards-/index.php'
        };
        
        header("Location: $redirect");
        exit();
    }
}
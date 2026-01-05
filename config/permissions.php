<?php
// config/permissions.php

require_once __DIR__ . '/session.php';

function isLoggedIn(): bool {
    return isAuthenticated();
}

function requireLogin() {
    requireAuth(); // Reutiliza a função existente
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        http_response_code(403);
        die('Accès refusé. Vous devez être administrateur.');
    }
}
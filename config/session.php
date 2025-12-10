<?php
/**
 * Gestion des sessions et authentification
 */
session_start();

/**
 * Vérifie si l'utilisateur est authentifié
 */
function isAuthenticated() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id_compte']);
}

/**
 * Redirige vers la page de login si non authentifié
 */
function requireAuth() {
    if (!isAuthenticated()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit();
    }
}

/**
 * Obtient le type d'utilisateur depuis la session
 */
function getUserType() {
    if (isAuthenticated()) {
        return $_SESSION['user']['user_type'] ?? null;
    }
    return null;
}

/**
 * Vérifie le type d'utilisateur et redirige si nécessaire
 */
function requireUserType($allowedTypes) {
    requireAuth();
    
    $userType = getUserType();
    if (!in_array($userType, (array)$allowedTypes)) {
        // Redirection basée sur le type d'utilisateur
        switch($userType) {
            case 'admin':
                header('Location: /admin/dashboard.php');
                break;
            case 'candidate':
                header('Location: /candidate/dashboard.php');
                break;
            case 'voter':
                header('Location: /user/dashboard.php');
                break;
            default:
                header('Location: /index.php');
        }
        exit();
    }
}

/**
 * Initialise la session utilisateur après authentification
 */
function initUserSession($userData) {
    $_SESSION['user'] = [
        'id_compte' => $userData['id_compte'],
        'pseudonyme' => $userData['pseudonyme'],
        'email' => $userData['email'],
        'user_type' => $userData['user_type'],
        'authenticated_at' => time()
    ];
    
    // Journalisation de la connexion
    logConnection($userData['id_compte']);
}

/**
 * Journalise la connexion dans AUDIT_CONNEXION
 */
function logConnection($userId) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO AUDIT_CONNEXION 
            (id_compte, date_connexion, ip, user_agent) 
            VALUES (?, NOW(), ?, ?)
        ");
        $stmt->execute([
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu'
        ]);
    } catch (Exception $e) {
        // Ne pas bloquer l'authentification en cas d'erreur de journalisation
        error_log("Erreur journalisation connexion: " . $e->getMessage());
    }
}

/**
 * Déconnexion de l'utilisateur
 */
function logout() {
    if (isset($_SESSION['user']['id_compte'])) {
        // Journalisation de la déconnexion
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("
                UPDATE AUDIT_CONNEXION 
                SET date_deconnexion = NOW() 
                WHERE id_compte = ? 
                AND date_deconnexion IS NULL 
                ORDER BY date_connexion DESC 
                LIMIT 1
            ");
            $stmt->execute([$_SESSION['user']['id_compte']]);
        } catch (Exception $e) {
            error_log("Erreur journalisation déconnexion: " . $e->getMessage());
        }
    }
    
    // Destruction de la session
    session_unset();
    session_destroy();
    session_start(); // Démarrer une nouvelle session propre
}
?>
<?php
// auth.php
session_start();

function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

function requireAuth() {
    if (!isAuthenticated()) {
        // Redirect ke login page
        header('Location: dashboard.php');
        exit();
    }
}

function getUserInfo() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'] ?? 'unknown',
        'auth_method' => $_SESSION['auth_method'] ?? 'unknown',
        'login_time' => $_SESSION['login_time'] ?? 0,
        'login_time_formatted' => isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'unknown'
    ];
}

function logout() {
    session_destroy();
    header('Location: dashboard.php');
    exit();
}
?>

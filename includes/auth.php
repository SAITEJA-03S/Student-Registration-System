<?php
/**
 * Authentication Middleware & Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash_error'] = "Please log in to access this page.";
        header("Location: login.php");
        exit;
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? 'Guest',
        'role' => $_SESSION['role'] ?? 'guest'
    ];
}

function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function set_flash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

function get_flash($type) {
    if (isset($_SESSION['flash_' . $type])) {
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
    return null;
}


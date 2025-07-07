<?php
/**
 * Archivo para verificar y debuggear sesiones
 * Incluir este archivo en cualquier página para debugging
 */

function debug_session() {
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc; font-family: monospace; font-size: 12px;'>";
    echo "<h4>Debug de Sesión:</h4>";
    echo "<strong>Estado de sesión:</strong> " . session_status() . "<br>";
    echo "<strong>ID de sesión:</strong> " . session_id() . "<br>";
    echo "<strong>Datos de sesión:</strong><br>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    echo "</div>";
}

// Función para verificar si el usuario está logueado
function is_logged_in() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

// Función para obtener el rol del usuario
function get_user_role() {
    return $_SESSION['rol'] ?? 'guest';
}

// Función para obtener el nombre del usuario
function get_user_name() {
    return $_SESSION['nombre'] ?? 'Invitado';
}

// Función para requerir login
function require_login($redirect_to = 'login/login.php') {
    if (!is_logged_in()) {
        header("Location: $redirect_to");
        exit;
    }
}

// Función para requerir un rol específico
function require_role($required_role, $redirect_to = 'index.php') {
    if (!is_logged_in() || get_user_role() !== $required_role) {
        header("Location: $redirect_to");
        exit;
    }
}
?>

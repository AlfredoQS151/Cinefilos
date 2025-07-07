<?php
session_start();

// Verificar si el usuario está autenticado y es un usuario normal
$loggedIn = isset($_SESSION['usuario_id']) && isset($_SESSION['rol']);
$rol = $_SESSION['rol'] ?? '';

// Respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode([
    'loggedIn' => $loggedIn,
    'rol' => $rol,
    'usuario_id' => $_SESSION['usuario_id'] ?? null
]);
?>

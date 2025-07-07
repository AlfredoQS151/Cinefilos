<?php
// Configuración de rutas para el header
function getBasePath() {
    $script_path = $_SERVER['SCRIPT_NAME'];
    $script_dir = dirname($script_path);
    
    // Contar el número de niveles desde la raíz
    $levels = substr_count($script_dir, '/');
    
    // Si estamos en la raíz, no necesitamos '../'
    if ($levels <= 1) {
        return '';
    }
    
    // Calcular el número de '../' necesarios
    return str_repeat('../', $levels - 1);
}

$base_path = getBasePath();

// Definir las rutas base
$routes = [
    'index' => $base_path . 'index.php',
    'empleados' => $base_path . 'empleados/empleados.php',
    'horarios' => $base_path . 'horarios/editar_horarios.php',
    'alimentos' => $base_path . 'alimentos/alimentos.php',
    'mostrar_alimentos' => $base_path . 'most_alimentos/mostrar_alimentos.php',
    'comprar' => $base_path . 'comprar/comprar_entradas.php',
    'historial' => $base_path . 'historial_pagos_entradas/historial.php',
    'perfil' => $base_path . 'perfil/perfil.php',
    'login' => $base_path . 'login/login.php',
    'logout' => $base_path . 'login/logout.php',
    'logo' => $base_path . 'resources/index/img/logo.png',
    'header_css' => $base_path . 'resources/header/css/styles.css'
];
?>

<?php
// Incluir la configuración unificada
include_once dirname(__DIR__) . '/config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'pelicula';
    if ($tipo === 'pelicula') {
        // Eliminar primero de historial_pagos_entradas (y otras tablas con FK a peliculas)
        $stmt = $pdo->prepare('DELETE FROM historial_pagos_entradas WHERE pelicula_id = :id');
        $stmt->execute(['id' => $id]);
        // Eliminar horarios asociados
        $stmt = $pdo->prepare('DELETE FROM pelicula_horario WHERE pelicula_id = :id');
        $stmt->execute(['id' => $id]);
        // Finalmente eliminar la película
        $stmt = $pdo->prepare('DELETE FROM peliculas WHERE id = :id');
        $stmt->execute(['id' => $id]);
    } elseif ($tipo === 'proximo') {
        // Eliminar próximo estreno
        $stmt = $pdo->prepare('DELETE FROM proximos WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

header("Location: ../index.php?deleted=1");
exit;

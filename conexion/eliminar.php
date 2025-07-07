<?php
$host = "localhost";
$port = "5432";
$dbname = "cinefilos";
$user = "postgres";
$password = "admin";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}


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

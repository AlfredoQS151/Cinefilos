<?php
require_once '../conexion.php';
header('Content-Type: application/json');

$pelicula_id = $_GET['pelicula_id'] ?? null;
if (!$pelicula_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        ph.id           AS pelicula_horario_id,
        s.numero_sala,
        h.fecha,
        h.hora_inicio,
        h.hora_fin
    FROM pelicula_horario ph
    JOIN salas    s ON ph.sala_id    = s.id
    JOIN horarios h ON ph.horario_id = h.id
    WHERE ph.pelicula_id = ?
    ORDER BY h.fecha, h.hora_inicio
");
$stmt->execute([$pelicula_id]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

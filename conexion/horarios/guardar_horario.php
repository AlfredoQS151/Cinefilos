<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ──────────────── Datos del formulario ──────────────── */
    $pelicula_id  = $_POST['pelicula_id']  ?? null;
    $numero_sala  = $_POST['numero_sala']  ?? null;
    $fecha        = $_POST['fecha']        ?? null;   // ← NUEVO  ░fecha
    $hora_inicio  = $_POST['hora_inicio']  ?? null;
    $hora_fin     = $_POST['hora_fin']     ?? null;

    /* ──────────────── Validación básica ─────────────────── */
    if (!$pelicula_id || !$numero_sala || !$fecha || !$hora_inicio || !$hora_fin) {
        header("Location: ../../horarios/editar_horarios.php?error=Faltan datos obligatorios.");
        exit;
    }

    try {
        /* ───────────── Obtener sala_id a partir del número ───────────── */
        $stmtSala = $pdo->prepare("SELECT id FROM salas WHERE numero_sala = ?");
        if (!$stmtSala || !$stmtSala->execute([$numero_sala])) {
            throw new Exception('Error al localizar la sala.');
        }
        $sala_id = $stmtSala->fetchColumn();
        if (!$sala_id) {
            throw new Exception('Sala no encontrada.');
        }

        /* ───────────── Duración de la película ───────────── */
        $stmtDur = $pdo->prepare("SELECT duracion FROM peliculas WHERE id = ?");
        $stmtDur->execute([$pelicula_id]);
        $duracion = $stmtDur->fetchColumn();
        if (!$duracion) {
            throw new Exception('Película no encontrada.');
        }

        /* ───────────── Validar que el rango cubra la duración ───────────── */
        $ini = DateTime::createFromFormat('H:i', $hora_inicio);
        $fin = DateTime::createFromFormat('H:i', $hora_fin);
        $diff = $ini->diff($fin);
        $min  = $diff->h * 60 + $diff->i;

        if ($min < $duracion) {
            throw new Exception('El horario seleccionado es menor a la duración de la película.');
        }

        /* ───────────── Validar SOLAPAMIENTO (misma sala / fecha) ─────────────
           - Si se encuentra una función que se cruce, se lanza excepción   */
        $stmtSolapa = $pdo->prepare("
            SELECT 1
            FROM pelicula_horario ph
            JOIN horarios h ON ph.horario_id = h.id
            WHERE ph.sala_id = ?
              AND h.fecha    = ?
              AND (
                    (h.hora_inicio <= ? AND h.hora_fin  >  ?) OR  -- se solapa por inicio
                    (h.hora_inicio <  ? AND h.hora_fin  >= ?) OR  -- se solapa por fin
                    (h.hora_inicio >= ? AND h.hora_fin  <= ?)     -- está contenida
                  )
            LIMIT 1
        ");
        $stmtSolapa->execute([
            $sala_id,
            $fecha,
            $hora_inicio, $hora_inicio,
            $hora_fin,    $hora_fin,
            $hora_inicio, $hora_fin
        ]);

        if ($stmtSolapa->fetch()) {
            throw new Exception('Ya existe una función en esa sala y fecha.');
        }

        /* ───────────── Insertar en horarios y obtener id ───────────── */
        $stmtInsH = $pdo->prepare("
            INSERT INTO horarios (fecha, hora_inicio, hora_fin)
            VALUES (?,?,?)
            RETURNING id
        ");
        $stmtInsH->execute([$fecha, $hora_inicio, $hora_fin]);
        $horario_id = $stmtInsH->fetchColumn();

        /* ───────────── Insertar en película_horario ───────────── */
        $stmtPH = $pdo->prepare("
            INSERT INTO pelicula_horario (pelicula_id, sala_id, horario_id)
            VALUES (?,?,?)
        ");
        $stmtPH->execute([$pelicula_id, $sala_id, $horario_id]);

        /* ───────────── Redirección con mensaje de éxito ───────────── */
        header("Location: ../../horarios/editar_horarios.php?mensaje=Horario guardado correctamente");
        exit;

    } catch (Exception $e) {
        $msg = urlencode($e->getMessage());
        header("Location: ../../horarios/editar_horarios.php?error={$msg}");
        exit;
    }
}
?>

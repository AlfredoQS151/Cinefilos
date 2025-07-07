<?php
session_start();

require_once '../conexion/conexion.php';
require_once '../phpqrcode/qrlib.php';   // ya lo tenías incluido

/* ───────────── Helper para generar / guardar QR ───────────── */
function generarQR(string $texto, string $rutaDestino, int $tam = 4): void
{
    // ­Crear carpeta destino si no existe
    $dir = dirname($rutaDestino);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    // ­Generar código QR y guardarlo
    QRcode::png($texto, $rutaDestino, QR_ECLEVEL_L, $tam, 2);
}

/* ───────────── Datos básicos ───────────── */
$usuario_id  = $_SESSION['usuario_id'];
$funcion_id  = (int)($_POST['funcion_id'] ?? 0);
$asientosSel = array_map('intval', $_POST['asientos'] ?? []);

if (!$funcion_id || !$asientosSel) {
    header("Location: ../comprar/comprar_entradas.php");
    exit();
}

/* ───────────── Verificar butacas libres ───────────── */
$placeholders = implode(',', array_fill(0, count($asientosSel), '?'));
$sqlCheck = "
   SELECT asiento_num
   FROM butacas_ocupadas
   WHERE funcion_id = ? AND asiento_num IN ($placeholders)
";
$stmt = $pdo->prepare($sqlCheck);
$stmt->execute(array_merge([$funcion_id], $asientosSel));
$yaVendidas = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($yaVendidas) {
    $msg = 'Asientos ya ocupados: ' . implode(', ', $yaVendidas);
    header("Location: ../comprar/comprar_entradas.php?error=" . urlencode($msg));
    exit();
}

/* ───────────── Precio y monto ───────────── */
$PRECIO_UNIT = 70;                 // ejemplo: precio fijo
$boletos     = count($asientosSel);
$monto       = $boletos * $PRECIO_UNIT;

/* ───────────── Transacción ───────────── */
try {
    $pdo->beginTransaction();

    /* 1) bloquear las butacas */
    $ins = $pdo->prepare("
        INSERT INTO butacas_ocupadas(funcion_id, asiento_num, usuario_id)
        VALUES (?,?,?)
    ");
    foreach ($asientosSel as $n) {
        $ins->execute([$funcion_id, $n, $usuario_id]);
    }

    /* 2) registrar pago en historial_pagos_entradas */
    $qrNombre = 'qr_' . uniqid('', true) . '.png';
    $qrPath   = '../qr/' . $qrNombre;     // asegúrate de que ../qr/ tenga permisos de escritura

    // Obtener datos de la función para el QR
    $sql_funcion = "
        SELECT p.titulo, s.numero_sala, h.fecha, h.hora_inicio
        FROM pelicula_horario ph
        JOIN salas s ON s.id = ph.sala_id
        JOIN horarios h ON h.id = ph.horario_id
        JOIN peliculas p ON p.id = ph.pelicula_id
        WHERE ph.id = ?
    ";
    $stmt_funcion = $pdo->prepare($sql_funcion);
    $stmt_funcion->execute([$funcion_id]);
    $datos_funcion = $stmt_funcion->fetch(PDO::FETCH_ASSOC);

    $asientos_str = implode(',', $asientosSel);
    $qr_text = "Película: {$datos_funcion['titulo']}\nSala: {$datos_funcion['numero_sala']}\nFecha: {$datos_funcion['fecha']} {$datos_funcion['hora_inicio']}\nAsientos: $asientos_str\nUsuario: $usuario_id";
    generarQR($qr_text, $qrPath);

    $pdo->prepare("
        INSERT INTO historial_pagos_entradas
              (monto_pago, fecha_pago, pelicula_id, boletos_comprados, usuario_id, qr_imagen, asientos, funcion_id)
        SELECT  ?, NOW(), ph.pelicula_id, ?, ?, ?, ?, ?
        FROM pelicula_horario ph
        WHERE ph.id = ?
    ")->execute([$monto, $boletos, $usuario_id, $qrNombre, $asientos_str, $funcion_id, $funcion_id]);

    /* 3) Actualizar puntos del usuario (5 puntos por asiento) */
    $puntos_ganados = $boletos * 5;
    $pdo->prepare("
        UPDATE usuarios_normales 
        SET puntos = COALESCE(puntos, 0) + ? 
        WHERE id = ?
    ")->execute([$puntos_ganados, $usuario_id]);

    $pdo->commit();

} catch (Throwable $e) {
    $pdo->rollBack();
    header("Location: ../comprar/comprar_entradas.php?error=" . urlencode($e->getMessage()));
    exit();
}

/* ───────────── Éxito ───────────── */
header("Location: ../historial_pagos_entradas/historial.php?mensaje=Compra realizada con éxito");
exit();
?>

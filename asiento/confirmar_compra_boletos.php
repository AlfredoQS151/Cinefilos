<?php
session_start();

// Validar que el usuario esté logueado
if (empty($_SESSION['usuario_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Validar datos recibidos
if (empty($_POST['funcion_id']) || empty($_POST['asientos'])) {
    header('Location: ../comprar/comprar_entradas.php');
    exit();
}

require_once '../conexion/conexion.php';
require_once '../phpqrcode/qrlib.php';

$usuario_id = $_SESSION['usuario_id'];
$funcion_id = (int)($_POST['funcion_id'] ?? 0);
$asientosSel = array_map('intval', $_POST['asientos'] ?? []);

// Validar que haya asientos seleccionados
if (!$funcion_id || !$asientosSel) {
    header('Location: ../comprar/comprar_entradas.php');
    exit();
}

// Inicializar variable de control de transacción
$transaction_started = false;

try {
    // Verificar butacas libres
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

    // Iniciar transacción
    $pdo->beginTransaction();
    $transaction_started = true;

    // Precio y monto
    $PRECIO_UNIT = 70;
    $boletos = count($asientosSel);
    $monto = $boletos * $PRECIO_UNIT;

    // Bloquear las butacas
    $ins = $pdo->prepare("
        INSERT INTO butacas_ocupadas(funcion_id, asiento_num, usuario_id)
        VALUES (?,?,?)
    ");
    foreach ($asientosSel as $n) {
        $ins->execute([$funcion_id, $n, $usuario_id]);
    }

    // Obtener datos de la función para el QR y la confirmación
    $sql_funcion = "
        SELECT p.titulo, s.numero_sala, h.fecha, h.hora_inicio, p.id as pelicula_id, p.poster
        FROM pelicula_horario ph
        JOIN salas s ON s.id = ph.sala_id
        JOIN horarios h ON h.id = ph.horario_id
        JOIN peliculas p ON p.id = ph.pelicula_id
        WHERE ph.id = ?
    ";
    $stmt_funcion = $pdo->prepare($sql_funcion);
    $stmt_funcion->execute([$funcion_id]);
    $datos_funcion = $stmt_funcion->fetch(PDO::FETCH_ASSOC);

    // Generar código QR
    $qrNombre = 'qr_boletos_' . uniqid('', true) . '.png';
    $qrPath = '../qr/' . $qrNombre;
    
    $asientos_str = implode(',', $asientosSel);
    $qr_text = "Película: {$datos_funcion['titulo']}\nSala: {$datos_funcion['numero_sala']}\nFecha: {$datos_funcion['fecha']} {$datos_funcion['hora_inicio']}\nAsientos: $asientos_str\nUsuario: $usuario_id";
    
    // Crear directorio si no existe
    if (!is_dir('../qr')) {
        mkdir('../qr', 0755, true);
    }
    
    QRcode::png($qr_text, $qrPath, QR_ECLEVEL_L, 8);

    // Registrar pago en historial_pagos_entradas
    $stmt = $pdo->prepare("
        INSERT INTO historial_pagos_entradas
              (monto_pago, fecha_pago, pelicula_id, boletos_comprados, usuario_id, qr_imagen, asientos, funcion_id)
        VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$monto, $datos_funcion['pelicula_id'], $boletos, $usuario_id, $qrNombre, $asientos_str, $funcion_id]);
    $compra_id = $pdo->lastInsertId();

    // Actualizar puntos del usuario (5 puntos por asiento)
    $puntos_ganados = $boletos * 5;
    $stmt = $pdo->prepare("
        UPDATE usuarios_normales 
        SET puntos = COALESCE(puntos, 0) + ? 
        WHERE id = ?
    ");
    $stmt->execute([$puntos_ganados, $usuario_id]);

    // Confirmar transacción
    $pdo->commit();

    // Obtener información del usuario
    $stmt = $pdo->prepare("SELECT nombre, correo FROM usuarios_normales WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    $exito = true;

} catch (Exception $e) {
    // Revertir transacción solo si se inició
    if ($transaction_started) {
        try {
            $pdo->rollBack();
        } catch (PDOException $rollbackError) {
            // Error al hacer rollback - posiblemente ya no hay transacción activa
            error_log("Error en rollback: " . $rollbackError->getMessage());
        }
    }
    $error = "Error al procesar la compra: " . $e->getMessage();
    $exito = false;
}

require_once '../resources/header/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra - Boletos</title>
    <link rel="stylesheet" href="../resources/header/css/styles.css">
    <link rel="stylesheet" href="../resources/index/css/styles.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/confirmacion_boletos.css">
    <link rel="stylesheet" href="../pagos/css/confirmacion_alimentos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="body-confirmacion">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="confirmation-container">
                    <?php if ($exito): ?>
                        <div class="success-header">
                            <i class="fas fa-check-circle"></i>
                            <h2>¡Compra de Boletos Exitosa!</h2>
                        </div>
                        
                        <div class="purchase-details">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4>Detalles de la Compra</h4>
                                    <p style="color: #ffffff;"><strong style="color: #eaf822;">Cliente:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
                                    <p style="color: #ffffff;"><strong style="color: #eaf822;">Email:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
                                    <p style="color: #ffffff;"><strong style="color: #eaf822;">Fecha:</strong> <?= date('d/m/Y') ?></p>
                                    
                                    <h5 style="color: #eaf822;">Información de la Función:</h5>
                                    <div class="movie-details">
                                        <div class="function-info">
                                            <p style="color: #ffffff;"><strong style="color: #eaf822;">Película:</strong> <?= htmlspecialchars($datos_funcion['titulo']) ?></p>
                                            <p style="color: #ffffff;"><strong style="color: #eaf822;">Sala:</strong> <?= $datos_funcion['numero_sala'] ?></p>
                                            <p style="color: #ffffff;"><strong style="color: #eaf822;">Fecha:</strong> <?= date('d/m/Y', strtotime($datos_funcion['fecha'])) ?></p>
                                            <p style="color: #ffffff;"><strong style="color: #eaf822;">Horario:</strong> <?= date('g:i A', strtotime($datos_funcion['hora_inicio'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="movie-poster">
                                        <?php if (preg_match('/^https?:\/\//', $datos_funcion['poster'])): ?>
                                            <img src="<?= htmlspecialchars($datos_funcion['poster']) ?>" alt="Poster de <?= htmlspecialchars($datos_funcion['titulo']) ?>" class="poster-image">
                                        <?php else: ?>
                                            <img src="../posters/<?= htmlspecialchars($datos_funcion['poster']) ?>" alt="Poster de <?= htmlspecialchars($datos_funcion['titulo']) ?>" class="poster-image">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 style="color: #eaf822;">Boletos Comprados:</h5>
                            <div class="ticket-summary">
                                <div class="seats-info">
                                    <h6>Asientos Reservados:</h6>
                                    <?php foreach ($asientosSel as $asiento): ?>
                                        <span class="seat-number"><?= $asiento ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="items-purchased">
                                    <div class="item-row">
                                        <span class="item-name">Boletos de Cine</span>
                                        <span class="item-quantity">x<?= $boletos ?></span>
                                        <span class="item-price">$<?= number_format($monto, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="total-section">
                                <h4>Total Pagado: $<?= number_format($monto, 2) ?></h4>
                                <p class="points-earned">Has ganado <?= $puntos_ganados ?> puntos</p>
                            </div>
                        </div>
                        
                        <div class="qr-section">
                            <h4>Código QR de tus Boletos</h4>
                            <p>Presenta este código en la entrada del cine para acceder a la función:</p>
                            <div class="qr-container">
                                <img src="<?= $qrPath ?>" alt="QR de boletos" class="qr-image">
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="../comprar/comprar_entradas.php" class="btn btn-primary">
                                <i class="fas fa-ticket-alt"></i> Comprar Más Boletos
                            </a>
                            <a href="../historial_pagos_entradas/historial.php" class="btn btn-secondary">
                                <i class="fas fa-history"></i> Ver Historial
                            </a>
                            <a href="../index.php" class="btn btn-success">
                                <i class="fas fa-home"></i> Volver al Inicio
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <div class="error-header">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h2>Error al Procesar la Compra</h2>
                        </div>
                        
                        <div class="error-details">
                            <p><?= htmlspecialchars($error) ?></p>
                            <a href="../comprar/comprar_entradas.php" class="btn btn-danger">
                                <i class="fas fa-arrow-left"></i> Volver a Intentar
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

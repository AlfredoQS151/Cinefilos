<?php
session_start();

// Validar que el usuario esté logueado
if (empty($_SESSION['usuario_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Validar que se haya recibido el ID de la compra
if (empty($_GET['id'])) {
    header('Location: ../historial_pagos_entradas/historial.php');
    exit();
}

require_once '../conexion/conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$compra_id = (int)$_GET['id'];

try {
    // Obtener los detalles de la compra
    $stmt = $pdo->prepare("
        SELECT hpe.*, p.titulo, p.poster, s.numero_sala, h.fecha, h.hora_inicio, un.nombre, un.correo
        FROM historial_pagos_entradas hpe
        JOIN peliculas p ON p.id = hpe.pelicula_id
        JOIN pelicula_horario ph ON ph.id = hpe.funcion_id
        JOIN salas s ON s.id = ph.sala_id
        JOIN horarios h ON h.id = ph.horario_id
        JOIN usuarios_normales un ON un.id = hpe.usuario_id
        WHERE hpe.id = ? AND hpe.usuario_id = ?
    ");
    $stmt->execute([$compra_id, $usuario_id]);
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
        header('Location: ../historial_pagos_entradas/historial.php?error=Compra no encontrada');
        exit();
    }

    // Procesar los asientos
    $asientosSel = explode(',', $compra['asientos']);
    $boletos = $compra['boletos_comprados'];
    $monto = $compra['monto_pago'];
    $puntos_ganados = $boletos * 5;

    // Datos de la función
    $datos_funcion = [
        'titulo' => $compra['titulo'],
        'poster' => $compra['poster'],
        'numero_sala' => $compra['numero_sala'],
        'fecha' => $compra['fecha'],
        'hora_inicio' => $compra['hora_inicio']
    ];

    // Datos del usuario
    $usuario = [
        'nombre' => $compra['nombre'],
        'correo' => $compra['correo']
    ];

    $exito = true;
    $qrPath = '../qr/' . $compra['qr_imagen'];

} catch (Exception $e) {
    $error = "Error al cargar los detalles de la compra: " . $e->getMessage();
    $exito = false;
}

require_once '../resources/header/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Compra - Boletos</title>
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
                            <i class="fas fa-ticket-alt"></i>
                            <h2>Detalles de Compra de Boletos</h2>
                        </div>
                        
                        <div class="purchase-details">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4>Detalles de la Compra</h4>
                                    <p style="color: #ffffff;"><strong style="color: #eaf822;">Cliente:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
                                    <p style="color: #ffffff;"><strong style="color: #eaf822;">Email:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
                                    <p style="color: #ffffff;"><strong style="color: #eaf822;">Fecha de Compra:</strong> <?= date('d/m/Y', strtotime($compra['fecha_pago'])) ?></p>
                                    
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
                                <p class="points-earned">Ganaste <?= $puntos_ganados ?> puntos</p>
                            </div>
                        </div>
                        
                        <div class="qr-section">
                            <h4>Código QR de tus Boletos</h4>
                            <p>Presenta este código en la entrada del cine para acceder a la función:</p>
                            <div class="qr-container">
                                <?php if (file_exists($qrPath)): ?>
                                    <img src="<?= $qrPath ?>" alt="QR de boletos" class="qr-image">
                                <?php else: ?>
                                    <p style="color: #ff6b6b;">Código QR no disponible</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="../historial_pagos_entradas/historial.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Volver al Historial
                            </a>
                            <a href="../comprar/comprar_entradas.php" class="btn btn-secondary">
                                <i class="fas fa-ticket-alt"></i> Comprar Más Boletos
                            </a>
                            <a href="../index.php" class="btn btn-success">
                                <i class="fas fa-home"></i> Volver al Inicio
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <div class="error-header">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h2>Error al Cargar los Detalles</h2>
                        </div>
                        
                        <div class="error-details">
                            <p><?= htmlspecialchars($error) ?></p>
                            <a href="../historial_pagos_entradas/historial.php" class="btn btn-danger">
                                <i class="fas fa-arrow-left"></i> Volver al Historial
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

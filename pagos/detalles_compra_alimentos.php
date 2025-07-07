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
    // Obtener los detalles de la compra de alimentos
    $stmt = $pdo->prepare("
        SELECT hpa.*, un.nombre, un.correo
        FROM historial_pagos_alimentos hpa
        JOIN usuarios_normales un ON un.id = hpa.usuario_id
        WHERE hpa.id = ? AND hpa.usuario_id = ?
    ");
    $stmt->execute([$compra_id, $usuario_id]);
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
        header('Location: ../historial_pagos_entradas/historial.php?error=Compra no encontrada');
        exit();
    }

    // Procesar los items comprados
    $carrito_alimentos = json_decode($compra['items_comprados'], true);
    $total = $compra['monto_pago'];
    
    // Calcular puntos correctamente: 5 puntos por cada producto individual
    $puntos_ganados = 0;
    if ($carrito_alimentos) {
        foreach ($carrito_alimentos as $item) {
            $puntos_ganados += $item['cantidad'] * 5; // 5 puntos por cada producto individual
        }
    }

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
    <title>Detalles de Compra - Alimentos</title>
    <link rel="stylesheet" href="../resources/header/css/styles.css">
    <link rel="stylesheet" href="../resources/index/css/styles.css">
    <link rel="stylesheet" href="../asiento/css/styles.css">
    <link rel="stylesheet" href="css/confirmacion_alimentos.css">
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
                            <i class="fas fa-utensils"></i>
                            <h2>Detalles de Compra de Alimentos</h2>
                        </div>
                        
                        <div class="purchase-details">
                            <h4>Detalles de la Compra</h4>
                            <p style="color: #ffffff;"><strong style="color: #eaf822;">Cliente:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
                            <p style="color: #ffffff;"><strong style="color: #eaf822;">Email:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
                            <p style="color: #ffffff;"><strong style="color: #eaf822;">Fecha:</strong> <?= date('d/m/Y', strtotime($compra['fecha_pago'])) ?></p>
                            
                            <h5 style="color: #eaf822;">Productos Comprados:</h5>
                            <div class="items-purchased">
                                <?php if ($carrito_alimentos): ?>
                                    <?php foreach ($carrito_alimentos as $item): ?>
                                        <div class="item-row">
                                            <span class="item-name"><?= htmlspecialchars($item['nombre']) ?></span>
                                            <span class="item-quantity">x<?= $item['cantidad'] ?></span>
                                            <span class="item-price">$<?= number_format($item['precio'] * $item['cantidad'], 2) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="total-section">
                                <h4>Total Pagado: $<?= number_format($total, 2) ?></h4>
                                <p class="points-earned">Ganaste <?= $puntos_ganados ?> puntos</p>
                            </div>
                        </div>
                        
                        <div class="qr-section">
                            <h4>Código QR de tu Compra</h4>
                            <p>Presenta este código en el mostrador para recoger tus productos:</p>
                            <div class="qr-container">
                                <?php if (file_exists($qrPath)): ?>
                                    <img src="<?= $qrPath ?>" alt="QR de compra de alimentos" class="qr-image">
                                <?php else: ?>
                                    <p style="color: #ff6b6b;">Código QR no disponible</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="../historial_pagos_entradas/historial.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Volver al Historial
                            </a>
                            <a href="../most_alimentos/mostrar_alimentos.php" class="btn btn-secondary">
                                <i class="fas fa-utensils"></i> Comprar Más Alimentos
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

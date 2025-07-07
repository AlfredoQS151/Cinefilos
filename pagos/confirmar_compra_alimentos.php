<?php
session_start();

// Validar que el usuario esté logueado
if (empty($_SESSION['usuario_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Validar datos recibidos
if (empty($_POST['carrito_alimentos']) || empty($_POST['total_alimentos'])) {
    header('Location: ../most_alimentos/mostrar_alimentos.php');
    exit();
}

require_once '../conexion/conexion.php';
require_once '../phpqrcode/qrlib.php';

$usuario_id = $_SESSION['usuario_id'];
$carrito_alimentos = json_decode($_POST['carrito_alimentos'], true);
$total = floatval($_POST['total_alimentos']);

// Validar que el carrito no esté vacío
if (empty($carrito_alimentos)) {
    header('Location: ../most_alimentos/mostrar_alimentos.php');
    exit();
}

// Inicializar variable de control de transacción
$transaction_started = false;

try {
    // Iniciar transacción
    $pdo->beginTransaction();
    $transaction_started = true;
    
    // Insertar en historial_pagos_alimentos (adaptado a tu estructura de tabla)
    $stmt = $pdo->prepare("
        INSERT INTO historial_pagos_alimentos (usuario_id, monto_pago, fecha_pago, items_comprados, cantidad_items, metodo_pago)
        VALUES (?, ?, NOW(), ?, ?, ?)
    ");
    
    // Crear detalles de la compra
    $detalles = [];
    foreach ($carrito_alimentos as $item) {
        $detalles[] = [
            'id' => $item['id'],
            'nombre' => $item['nombre'],
            'precio' => $item['precio'],
            'cantidad' => $item['cantidad'],
            'subtotal' => $item['precio'] * $item['cantidad']
        ];
    }
    $detalles_json = json_encode($detalles);
    $cantidad_total_items = array_sum(array_column($carrito_alimentos, 'cantidad'));
    
    $stmt->execute([$usuario_id, $total, $detalles_json, $cantidad_total_items, 'Tarjeta de Crédito']);
    $compra_id = $pdo->lastInsertId();
    
    // Generar código QR
    $qr_data = "COMPRA_ALIMENTOS|ID:{$compra_id}|USUARIO:{$usuario_id}|TOTAL:{$total}|FECHA:" . date('Y-m-d H:i:s');
    $qr_filename = "qr_alimentos_{$compra_id}_" . uniqid() . ".png";
    $qr_path = "../qr/" . $qr_filename;
    
    // Crear directorio si no existe
    if (!is_dir('../qr')) {
        mkdir('../qr', 0755, true);
    }
    
    // Generar QR
    QRcode::png($qr_data, $qr_path, QR_ECLEVEL_L, 8);
    
    // Actualizar el registro con el QR
    $stmt = $pdo->prepare("UPDATE historial_pagos_alimentos SET qr_imagen = ? WHERE id = ?");
    $stmt->execute([$qr_filename, $compra_id]);
    
    // Actualizar puntos del usuario (5 puntos por cada producto individual)
    $puntos_ganados = $cantidad_total_items * 5;
    $stmt = $pdo->prepare("UPDATE usuarios_normales SET puntos = puntos + ? WHERE id = ?");
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
    <title>Confirmación de Compra - Alimentos</title>
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
                            <i class="fas fa-check-circle"></i>
                            <h2>¡Compra de Alimentos Exitosa!</h2>
                        </div>
                        
                        <div class="purchase-details">
                            <h4>Detalles de la Compra</h4>
                            <p style="color: #ffffff;"><strong style="color: #eaf822;">Cliente:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
                            <p style="color: #ffffff;"><strong style="color: #eaf822;">Email:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
                            <p style="color: #ffffff;"><strong style="color: #eaf822;">Fecha:</strong> <?= date('d/m/Y H:i:s') ?></p>
                            
                            <h5 style="color: #eaf822;">Productos Comprados:</h5>
                            <div class="items-purchased">
                                <?php foreach ($carrito_alimentos as $item): ?>
                                    <div class="item-row">
                                        <span class="item-name"><?= htmlspecialchars($item['nombre']) ?></span>
                                        <span class="item-quantity">x<?= $item['cantidad'] ?></span>
                                        <span class="item-price">$<?= number_format($item['precio'] * $item['cantidad'], 2) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="total-section">
                                <h4>Total Pagado: $<?= number_format($total, 2) ?></h4>
                                <p class="points-earned">Has ganado <?= $puntos_ganados ?> puntos</p>
                            </div>
                        </div>
                        
                        <div class="qr-section">
                            <h4>Código QR de tu Compra</h4>
                            <p>Presenta este código en el mostrador para recoger tus productos:</p>
                            <div class="qr-container">
                                <img src="<?= $qr_path ?>" alt="QR de compra de alimentos" class="qr-image">
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="../most_alimentos/mostrar_alimentos.php" class="btn btn-primary">
                                <i class="fas fa-utensils"></i> Comprar Más Alimentos
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
                            <a href="../most_alimentos/mostrar_alimentos.php" class="btn btn-danger">
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

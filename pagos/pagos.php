<?php
session_start();

// Verificar si es compra de alimentos o boletos
$es_compra_alimentos = isset($_POST['tipo_compra']) && $_POST['tipo_compra'] === 'alimentos';

if ($es_compra_alimentos) {
    // Validar datos de alimentos
    if (empty($_POST['carrito_alimentos']) || empty($_POST['total_alimentos'])) {
        header('Location: ../most_alimentos/mostrar_alimentos.php');
        exit();
    }
    $carrito_alimentos = json_decode($_POST['carrito_alimentos'], true);
    $total = floatval($_POST['total_alimentos']);
    $tipo_compra = 'alimentos';
} else {
    // Validar datos de boletos (lógica original)
    if (empty($_POST['funcion_id']) || empty($_POST['asientos'])) {
        header('Location: ../comprar/comprar_entradas.php');
        exit();
    }
    $funcion_id = (int)$_POST['funcion_id'];
    $asientos = $_POST['asientos'];
    $total = isset($_POST['total']) ? floatval($_POST['total']) : (count($asientos) * 70);
    $tipo_compra = 'boletos';
}

require_once '../resources/header/header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Método de Pago</title>
    <link rel="stylesheet" href="../resources/header/css/styles.css">
    <link rel="stylesheet" href="../resources/index/css/styles.css">
        <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="body-pagos">
<div class="container">
    <div class="payment-container">
        <h1>💳 Método de Pago <?= $es_compra_alimentos ? '- Alimentos' : '- Boletos' ?></h1>
        <form class="formulario-registro" id="paymentForm" method="post" action="guardar_pago.php">
            <input type="hidden" name="tipo_compra" value="<?= htmlspecialchars($tipo_compra) ?>">
            
            <?php if ($es_compra_alimentos): ?>
                <input type="hidden" name="carrito_alimentos" value="<?= htmlspecialchars($_POST['carrito_alimentos']) ?>">
                <input type="hidden" name="total_alimentos" value="<?= htmlspecialchars($total) ?>">
            <?php else: ?>
                <input type="hidden" name="funcion_id" value="<?= htmlspecialchars($funcion_id) ?>">
                <?php foreach ($asientos as $a): ?>
                    <input type="hidden" name="asientos[]" value="<?= htmlspecialchars($a) ?>">
                <?php endforeach; ?>
                <input type="hidden" name="total" value="<?= htmlspecialchars($total) ?>">
            <?php endif; ?>

            <label>🔢 Número de Tarjeta</label>
            <input type="text" id="card-number" name="card-number" maxlength="19" placeholder="1234 5678 9012 3456" required>
            <div class="error-message"></div>

            <label>📅 Fecha de Expiración</label>
            <input type="text" id="card-expiry" name="card-expiry" maxlength="5" placeholder="MM/AA" required>
            <div class="error-message"></div>

            <label>🔒 Código de Seguridad</label>
            <input type="text" id="card-cvc" name="card-cvc" maxlength="4" placeholder="123" required>
            <div class="error-message"></div>

            <label>👤 Nombre del Titular</label>
            <input type="text" id="card-holder" name="card-holder" maxlength="60" placeholder="Nombre completo del titular" required>
            <div class="error-message"></div>

            <label>📍 Código Postal</label>
            <input type="text" id="postal-code" name="postal-code" maxlength="10" placeholder="12345" required>
            <div class="error-message"></div>

            <label>💰 TOTAL A PAGAR</label>
            <input type="text" id="total-view" value="$<?= number_format($total,2) ?>" readonly>
            
            <div class="centro">
                <button type="submit" class="boton">Procesar Pago</button>
            </div>
        </form>
        <div class="credit-card-logos">
            <img src="../resources/index/img/card.png" alt="Visa" title="Visa">
        </div>
    </div>
</div>

<script src="js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

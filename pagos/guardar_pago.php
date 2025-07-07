<?php
session_start();
// Validar que el usuario esté logueado
if (empty($_SESSION['usuario_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Verificar si es compra de alimentos o boletos
$es_compra_alimentos = isset($_POST['tipo_compra']) && $_POST['tipo_compra'] === 'alimentos';

if ($es_compra_alimentos) {
    // Validar campos para alimentos
    $campos = ['carrito_alimentos', 'total_alimentos', 'card-number', 'card-expiry', 'card-cvc', 'card-holder', 'postal-code'];
    foreach ($campos as $campo) {
        if (empty($_POST[$campo])) {
            header('Location: pagos.php?error=Faltan+campos');
            exit();
        }
    }
    
    $carrito_alimentos = $_POST['carrito_alimentos'];
    $total = floatval($_POST['total_alimentos']);
    $usuario_id = $_SESSION['usuario_id'];
    
    // Redirigir a confirmar compra de alimentos
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Procesando pago de alimentos...</title>
        <meta http-equiv="refresh" content="0;url=confirmar_compra_alimentos.php">
    </head>
    <body>
    <form id="redir" action="confirmar_compra_alimentos.php" method="post">
        <input type="hidden" name="carrito_alimentos" value="<?= htmlspecialchars($carrito_alimentos) ?>">
        <input type="hidden" name="total_alimentos" value="<?= htmlspecialchars($total) ?>">
    </form>
    <script>
    document.getElementById('redir').submit();
    </script>
    <noscript>
        <p>Redirigiendo... Si no ocurre nada, haz clic en el botón:</p>
        <button onclick="document.getElementById('redir').submit()">Continuar</button>
    </noscript>
    </body>
    </html>
    <?php
} else {
    // Validar campos para boletos (lógica original)
    $campos = ['funcion_id', 'asientos', 'card-number', 'card-expiry', 'card-cvc', 'card-holder', 'postal-code', 'total'];
    foreach ($campos as $campo) {
        if (empty($_POST[$campo])) {
            header('Location: pagos.php?error=Faltan+campos');
            exit();
        }
    }

    $funcion_id = (int)$_POST['funcion_id'];
    $asientos = $_POST['asientos'];
    $total = floatval($_POST['total']);
    $usuario_id = $_SESSION['usuario_id'];

    // Redirigir a confirmar compra de boletos
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Procesando pago de boletos...</title>
        <meta http-equiv="refresh" content="0;url=../asiento/confirmar_compra_boletos.php">
    </head>
    <body>
    <form id="redir" action="../asiento/confirmar_compra_boletos.php" method="post">
        <input type="hidden" name="funcion_id" value="<?= htmlspecialchars($funcion_id) ?>">
        <?php foreach ($asientos as $a): ?>
            <input type="hidden" name="asientos[]" value="<?= htmlspecialchars($a) ?>">
        <?php endforeach; ?>
    </form>
    <script>
    document.getElementById('redir').submit();
    </script>
    <noscript>
        <p>Redirigiendo... Si no ocurre nada, haz clic en el botón:</p>
        <button onclick="document.getElementById('redir').submit()">Continuar</button>
    </noscript>
    </body>
    </html>
    <?php
}
?>

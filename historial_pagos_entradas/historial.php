<?php
/* ─────────────── Config & seguridad ─────────────── */
session_start();

$usuario_id = $_SESSION['usuario_id'];          // ← viene del login

require_once '../conexion/conexion.php';

/* ───────────────  Consulta de historial de entradas ─────────────── */
$sql_entradas = "
    SELECT
        h.id,
        h.monto_pago,
        h.fecha_pago,
        h.boletos_comprados,
        h.qr_imagen,
        h.asientos,
        h.funcion_id,
        p.titulo,
        p.poster,
        ho.fecha AS funcion_fecha,
        ho.hora_inicio AS funcion_hora,
        'entradas' as tipo_compra
    FROM historial_pagos_entradas h
    JOIN peliculas p ON p.id = h.pelicula_id
    JOIN pelicula_horario ph ON ph.id = h.funcion_id
    JOIN horarios ho ON ho.id = ph.horario_id
    WHERE h.usuario_id = ?
    ORDER BY h.fecha_pago DESC
";
$stmt_entradas = $pdo->prepare($sql_entradas);
$stmt_entradas->execute([$usuario_id]);
$historial_entradas = $stmt_entradas->fetchAll(PDO::FETCH_ASSOC);

/* ───────────────  Consulta de historial de alimentos ─────────────── */
$sql_alimentos = "
    SELECT
        h.id,
        h.monto_pago,
        h.fecha_pago,
        h.items_comprados,
        h.cantidad_items,
        h.qr_imagen,
        h.metodo_pago,
        'alimentos' as tipo_compra
    FROM historial_pagos_alimentos h
    WHERE h.usuario_id = ?
    ORDER BY h.fecha_pago DESC
";
$stmt_alimentos = $pdo->prepare($sql_alimentos);
$stmt_alimentos->execute([$usuario_id]);
$historial_alimentos = $stmt_alimentos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras - Cinéfilos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../resources/header/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="historial-container">
    <?php require_once '../resources/header/header.php'; ?>
    
    <div class="container">
        <div class="historial-header">
            <h1 class="historial-title">
                <i class="fas fa-history"></i>
                Historial de Compras
            </h1>
            <p class="historial-subtitle">Revisa todas tus compras de entradas y alimentos</p>
        </div>

        <!-- Tabs para alternar entre tipos de historial -->
        <ul class="nav nav-tabs" id="historialTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="entradas-tab" data-bs-toggle="tab" data-bs-target="#entradas" type="button" role="tab">
                    <i class="fas fa-ticket-alt"></i> Entradas (<?= count($historial_entradas) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="alimentos-tab" data-bs-toggle="tab" data-bs-target="#alimentos" type="button" role="tab">
                    <i class="fas fa-utensils"></i> Alimentos (<?= count($historial_alimentos) ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="historialTabsContent">
            <!-- Tab de Entradas -->
            <div class="tab-pane fade show active" id="entradas" role="tabpanel">
                <?php if (!$historial_entradas): ?>
                    <div class="sin-historial">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>Sin compras de entradas</h3>
                        <p>Aún no has comprado entradas para películas</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark align-middle">
                            <thead>
                                <tr>
                                    <th>Película</th>
                                    <th>Boletos</th>
                                    <th>Asientos</th>
                                    <th>Función</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>QR</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($historial_entradas as $h): ?>
                                <tr class="clickable-row" data-href="../asiento/detalles_compra.php?id=<?= $h['id'] ?>" style="cursor: pointer;">
                                    <td>
                                        <?php if (!empty($h['poster'])): ?>
                                            <?php if (preg_match('/^https?:\/\//', $h['poster'])): ?>
                                                <img src="<?= htmlspecialchars($h['poster']) ?>" alt="<?= htmlspecialchars($h['titulo']) ?>" class="poster-img"><br>
                                            <?php else: ?>
                                                <img src="<?= '../posters/' . htmlspecialchars($h['poster']) ?>" alt="<?= htmlspecialchars($h['titulo']) ?>" class="poster-img"><br>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <strong><?= htmlspecialchars($h['titulo']) ?></strong>
                                    </td>
                                    <td><?= (int)$h['boletos_comprados'] ?></td>
                                    <td><?= htmlspecialchars($h['asientos'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                        if (!empty($h['funcion_fecha']) && !empty($h['funcion_hora'])) {
                                            $dt = new DateTime($h['funcion_fecha'] . ' ' . $h['funcion_hora']);
                                            echo $dt->format('d/m/Y') . '<br>';
                                            echo $dt->format('g:i A');
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><span class="monto-badge">$<?= number_format($h['monto_pago'], 2) ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($h['fecha_pago'])) ?></td>
                                    <td>
                                        <?php if (!empty($h['qr_imagen'])): ?>
                                            <a href="<?= '../qr/' . htmlspecialchars($h['qr_imagen']) ?>" target="_blank" onclick="event.stopPropagation();">
                                                <img src="<?= '../qr/' . htmlspecialchars($h['qr_imagen']) ?>" alt="QR" class="qr-img">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab de Alimentos -->
            <div class="tab-pane fade" id="alimentos" role="tabpanel">
                <?php if (!$historial_alimentos): ?>
                    <div class="sin-historial">
                        <i class="fas fa-utensils"></i>
                        <h3>Sin compras de alimentos</h3>
                        <p>Aún no has comprado alimentos en nuestro menú</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark align-middle">
                            <thead>
                                <tr>
                                    <th>Items Comprados</th>
                                    <th>Cantidad Total</th>
                                    <th>Método de Pago</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>QR</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($historial_alimentos as $h): ?>
                                <tr class="clickable-row" data-href="../pagos/detalles_compra_alimentos.php?id=<?= $h['id'] ?>" style="cursor: pointer;">
                                    <td>
                                        <div class="items-list">
                                            <?php 
                                            $items = json_decode($h['items_comprados'], true);
                                            if ($items && is_array($items)) {
                                                foreach ($items as $item) {
                                                    echo '<div class="item-comprado">';
                                                    echo '<span class="item-nombre">' . htmlspecialchars($item['nombre']) . '</span>';
                                                    echo ' <span class="item-cantidad">x' . $item['cantidad'] . '</span>';
                                                    echo '</div>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">Items no disponibles</span>';
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td><?= (int)$h['cantidad_items'] ?></td>
                                    <td><?= ucfirst(htmlspecialchars($h['metodo_pago'])) ?></td>
                                    <td><span class="monto-badge">$<?= number_format($h['monto_pago'], 2) ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($h['fecha_pago'])) ?></td>
                                    <td>
                                        <?php if (!empty($h['qr_imagen'])): ?>
                                            <a href="<?= '../qr/' . htmlspecialchars($h['qr_imagen']) ?>" target="_blank" onclick="event.stopPropagation();">
                                                <img src="<?= '../qr/' . htmlspecialchars($h['qr_imagen']) ?>" alt="QR" class="qr-img">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hacer las filas clicables
        document.addEventListener('DOMContentLoaded', function() {
            const clickableRows = document.querySelectorAll('.clickable-row');
            clickableRows.forEach(row => {
                row.addEventListener('click', function() {
                    window.location.href = this.dataset.href;
                });
                
                // Agregar efecto hover
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#444';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        });
    </script>
</body>
</html>

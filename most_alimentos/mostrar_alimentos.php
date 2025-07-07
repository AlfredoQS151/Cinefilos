<?php
session_start();
include '../conexion/conexion.php';

// Obtener todos los alimentos
$stmt = $pdo->query("SELECT * FROM alimentos ORDER BY categoria, nombre");
$alimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alimentos - Cinéfilos</title>
    <link rel="icon" type="image/png" href="../resources/index/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../resources/header/css/styles.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="body-alimentos">
    <?php include '../resources/header/header.php'; ?>
    
    <div class="container-fluid">
        <div class="alimentos-header">
            <h1 class="page-title">Menú de Alimentos</h1>
            <p class="page-subtitle">Descubre nuestra deliciosa selección de snacks y bebidas</p>
        </div>

        <div class="alimentos-container">
            <?php if (empty($alimentos)): ?>
                <div class="sin-alimentos">
                    <h3>Menú en preparación</h3>
                    <p>Próximamente tendremos disponible nuestro delicioso menú</p>
                </div>
            <?php else: ?>
                <?php
                // Agrupar por categorías
                $categorias = [];
                foreach ($alimentos as $alimento) {
                    $categorias[$alimento['categoria']][] = $alimento;
                }
                ?>
                
                <?php foreach ($categorias as $categoria => $items): ?>
                    <div class="categoria-section">
                        <h2 class="categoria-title">
                            <i class="categoria-icon">
                                <?php
                                switch(strtolower($categoria)) {
                                    case 'palomitas':
                                        echo '🍿';
                                        break;
                                    case 'bebidas':
                                        echo '🥤';
                                        break;
                                    case 'dulces':
                                        echo '🍭';
                                        break;
                                    case 'combos':
                                        echo '🎉';
                                        break;
                                    case 'snacks':
                                        echo '🍪';
                                        break;
                                    default:
                                        echo '🍽️';
                                }
                                ?>
                            </i>
                            <?= htmlspecialchars($categoria) ?>
                        </h2>
                        <div class="alimentos-grid">
                            <?php foreach ($items as $alimento): ?>
                                <div class="alimento-card">
                                    <div class="alimento-imagen">
                                        <?php if (!empty($alimento['foto']) && $alimento['foto'] !== ''): ?>
                                            <img src="<?= htmlspecialchars($alimento['foto']) ?>" 
                                                 alt="<?= htmlspecialchars($alimento['nombre']) ?>"
                                                 onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'no-image-placeholder\'>🍽️</div>';">
                                        <?php else: ?>
                                            <div class="no-image-placeholder">🍽️</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="alimento-info">
                                        <h4 class="alimento-nombre"><?= htmlspecialchars($alimento['nombre']) ?></h4>
                                        <?php if (!empty($alimento['descripcion'])): ?>
                                            <p class="alimento-descripcion"><?= htmlspecialchars($alimento['descripcion']) ?></p>
                                        <?php endif; ?>
                                        <div class="alimento-detalles">
                                            <div class="alimento-precio">$<?= number_format($alimento['precio'], 2) ?></div>
                                            <div class="alimento-categoria-badge">
                                                <?= htmlspecialchars($alimento['categoria']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alimento-acciones">
                                        <button class="btn btn-primary btn-agregar-carrito" 
                                                data-id="<?= $alimento['id'] ?>"
                                                data-nombre="<?= htmlspecialchars($alimento['nombre']) ?>"
                                                data-precio="<?= $alimento['precio'] ?>">
                                            <i class="fas fa-shopping-cart"></i> Agregar al Carrito
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Carrito flotante -->
        <div class="carrito-flotante" id="carritoFlotante" style="display: none;">
            <div class="carrito-header">
                <h5><i class="fas fa-shopping-cart"></i> Carrito</h5>
                <button class="btn-cerrar-carrito" onclick="cerrarCarrito()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="carrito-contenido" id="carritoContenido">
                <!-- Items del carrito se agregan dinámicamente -->
            </div>
            <div class="carrito-footer">
                <div class="carrito-puntos">
                    <i class="fas fa-star" style="color: #eaf822;"></i> Ganarás <span id="puntosGanados">0</span> puntos
                </div>
                <div class="carrito-total">
                    Total: $<span id="carritoTotal">0.00</span>
                </div>
                <button class="btn btn-success btn-proceder" onclick="procederCompra()">
                    <i></i> Proceder al Pago
                </button>
            </div>
        </div>

        <!-- Botón del carrito -->
        <button class="btn-carrito-flotante" id="btnCarrito" onclick="mostrarCarrito()" style="display: none;">
            <i class="fas fa-shopping-cart"></i>
            <span class="carrito-contador" id="carritoContador">0</span>
        </button>
    </div>

    <!-- Modal de inicio de sesión -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content elimination-modal">
          <div class="modal-header elimination-modal-header">
            <h5 class="modal-title elimination-modal-title" id="loginModalLabel">
                Iniciar Sesión
            </h5>
            <button type="button" class="btn-close elimination-modal-close" data-bs-dismiss="modal" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body elimination-modal-body">
            <p class="elimination-modal-question">Debes iniciar sesión para continuar con la compra</p>
          </div>
          <div class="modal-footer elimination-modal-footer">
            <button type="button" class="btn btn-elimination-cancel" data-bs-dismiss="modal">Cancelar</button>
            <a href="../login/login.php" class="btn btn-elimination-confirm">Iniciar Sesión</a>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a81368914c.js" crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
</body>
</html>

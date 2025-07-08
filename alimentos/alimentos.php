<?php
session_start();

// Verificar que sea empleado
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'medium') {
    header("Location: ../index.php");
    exit;
}

include '../conexion/conexion.php';
include '../resources/header/header.php';

// Obtener todos los alimentos
$stmt = $pdo->query("SELECT * FROM alimentos ORDER BY categoria, nombre");
$alimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alimentos</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../resources/header/css/styles.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="body-alimentos">
    <div class="container-fluid">
        <div class="alimentos-header">
            <h1 class="page-title">Gestión de Alimentos</h1>
            <p class="page-subtitle">Administra el menú de alimentos disponibles para los clientes</p>
            <button class="btn btn-primary btn-agregar" data-bs-toggle="modal" data-bs-target="#modalAgregarAlimento">
                <i class="fas fa-plus"></i> Agregar Alimento
            </button>
        </div>

        <div class="alimentos-container">
            <?php if (empty($alimentos)): ?>
                <div class="sin-alimentos">
                    <h3>No hay alimentos registrados</h3>
                    <p>Comienza agregando el primer alimento al menú</p>
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
                        <h2 class="categoria-title"><?= htmlspecialchars($categoria) ?></h2>
                        <div class="alimentos-grid">
                            <?php foreach ($items as $alimento): ?>
                                <div class="alimento-card">
                                    <div class="alimento-imagen">
                                        <img src="<?= htmlspecialchars($alimento['foto']) ?>" 
                                             alt="<?= htmlspecialchars($alimento['nombre']) ?>"
                                             onerror="this.src='../resources/index/img/no-image.png'">
                                    </div>
                                    <div class="alimento-info">
                                        <h4 class="alimento-nombre"><?= htmlspecialchars($alimento['nombre']) ?></h4>
                                        <p class="alimento-descripcion"><?= htmlspecialchars($alimento['descripcion']) ?></p>
                                        <div class="alimento-precio">$<?= number_format($alimento['precio'], 2) ?></div>
                                        <div class="alimento-categoria"><?= htmlspecialchars($alimento['categoria']) ?></div>
                                    </div>
                                    <div class="alimento-acciones">
                                        <button class="btn btn-sm btn-warning btn-editar" 
                                                data-id="<?= $alimento['id'] ?>"
                                                data-nombre="<?= htmlspecialchars($alimento['nombre']) ?>"
                                                data-foto="<?= htmlspecialchars($alimento['foto']) ?>"
                                                data-descripcion="<?= htmlspecialchars($alimento['descripcion']) ?>"
                                                data-precio="<?= $alimento['precio'] ?>"
                                                data-categoria="<?= htmlspecialchars($alimento['categoria']) ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEditarAlimento">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-eliminar" 
                                                data-id="<?= $alimento['id'] ?>"
                                                data-nombre="<?= htmlspecialchars($alimento['nombre']) ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEliminarAlimento">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Agregar Alimento -->
    <div class="modal fade" id="modalAgregarAlimento" tabindex="-1" aria-labelledby="modalAgregarAlimentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarAlimentoLabel">Agregar Nuevo Alimento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="insertar_alimento.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Alimento *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">URL de la Foto *</label>
                            <input type="url" class="form-control" id="foto" name="foto" required>
                            <div class="form-text">Ingresa la URL de la imagen del alimento</div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio *</label>
                            <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría *</label>
                            <select class="form-select" id="categoria" name="categoria" required>
                                <option value="">Selecciona una categoría</option>
                                <option value="Palomitas">Palomitas</option>
                                <option value="Bebidas">Bebidas</option>
                                <option value="Dulces">Dulces</option>
                                <option value="Combos">Combos</option>
                                <option value="Snacks">Snacks</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agregar Alimento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Alimento -->
    <div class="modal fade" id="modalEditarAlimento" tabindex="-1" aria-labelledby="modalEditarAlimentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarAlimentoLabel">Editar Alimento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="editar_alimento.php" method="POST">
                    <input type="hidden" id="editar_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editar_nombre" class="form-label">Nombre del Alimento *</label>
                            <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_foto" class="form-label">URL de la Foto *</label>
                            <input type="url" class="form-control" id="editar_foto" name="foto" required>
                            <div class="form-text">Ingresa la URL de la imagen del alimento</div>
                        </div>
                        <div class="mb-3">
                            <label for="editar_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="editar_descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editar_precio" class="form-label">Precio *</label>
                            <input type="number" class="form-control" id="editar_precio" name="precio" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_categoria" class="form-label">Categoría *</label>
                            <select class="form-select" id="editar_categoria" name="categoria" required>
                                <option value="">Selecciona una categoría</option>
                                <option value="Palomitas">Palomitas</option>
                                <option value="Bebidas">Bebidas</option>
                                <option value="Dulces">Dulces</option>
                                <option value="Combos">Combos</option>
                                <option value="Snacks">Snacks</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Alimento -->
    <div class="modal fade" id="modalEliminarAlimento" tabindex="-1" aria-labelledby="modalEliminarAlimentoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEliminarAlimentoLabel">Eliminar Alimento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="eliminar_alimento.php" method="POST">
                    <input type="hidden" id="eliminar_id" name="id">
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar el alimento <strong id="eliminar_nombre_mostrar"></strong>?</p>
                        <p style="color: #fffff">Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    <script src="js/script.js"></script>
</body>
</html>

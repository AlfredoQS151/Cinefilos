<!DOCTYPE html>
<html lang="<link rel="stylesheet" href="../resources/header/css/styles.css">
<link rel="stylesheet" href="../resources/index/css/styles.css">
<link rel="stylesheet" href="css/styles.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet")
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados</title>
    <link rel="icon" type="image/png" href="../resources/index/img/logo.png">

<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../conexion/conexion.php'; 
include '../resources/header/header.php';

try {
    $stmt = $pdo->prepare("SELECT id, nombre, apellido, fecha_nacimiento, correo FROM usuarios_medium ORDER BY id");
    $stmt->execute();
    $usuarios_medium = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $usuarios_medium = [];
    echo "<p>Error al obtener usuarios: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<link rel="stylesheet" href="../resources/header/css/styles.css">
<link rel="stylesheet" href="../resources/index/css/styles.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<style>
.body-empleados {
    background-color: #1a1a1a;
    font-family: 'Roboto', sans-serif;
    color: #ffffff;
}

.empleados-header {
    background-color: #1a1a1a;
    padding: 30px;
    text-align: center;
    border-radius: 10px;
    margin: 20px 0;
}

.page-title {
    color: #eaf822;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.page-subtitle {
    color: #cccccc;
    font-size: 1.1rem;
    margin-bottom: 20px;
}

.empleados-container {
    padding: 0 20px;
}

.empleados-table-container {
    background-color: #222;
    border-radius: 12px;
    padding: 25px;
    margin: 30px 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    border: 1px solid #333;
}

.table-header {
    margin-bottom: 25px;
    text-align: center;
    border-bottom: 2px solid #eaf822;
    padding-bottom: 15px;
}

.table-title {
    color: #eaf822;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.empleados-table {
    width: 100%;
    border-collapse: collapse;
    background-color: transparent;
    color: #ffffff;
}

.empleados-table thead th {
    background-color: #333;
    color: #eaf822;
    font-weight: 600;
    padding: 15px;
    border: 1px solid #555;
    text-align: center;
}

.empleados-table tbody tr {
    background-color: #2a2a2a;
    border-bottom: 1px solid #555;
    transition: all 0.3s ease;
}

.empleados-table tbody tr:hover {
    background-color: #333;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.empleados-table tbody tr:nth-child(even) {
    background-color: #252525;
}

.empleados-table tbody tr:nth-child(even):hover {
    background-color: #333;
}

.empleados-table tbody td {
    padding: 15px;
    border: 1px solid #555;
    text-align: center;
    vertical-align: middle;
}

.empleados-table tbody tr.no-data {
    background-color: #222;
}

.empleados-table tbody tr.no-data td {
    color: #cccccc;
    font-style: italic;
    text-align: center;
    padding: 40px;
}

.btn-agregar {
    background-color: #eaf822;
    border: none;
    color: #000;
    font-weight: 600;
    padding: 12px 30px;
    border-radius: 8px;
    transition: all 0.3s ease;
    margin: 20px 0;
    font-size: 1rem;
}

.btn-agregar:hover {
    background-color: #f50c18;
    color: #ffffff;
    transform: translateY(-2px);
}

.btn-editar {
    background-color: #17a2b8;
    border: none;
    color: #fff;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 6px;
    transition: all 0.3s ease;
    margin: 0 2px;
}

.btn-editar:hover {
    background-color: #138496;
    transform: translateY(-2px);
}

.btn-eliminar {
    background-color: #dc3545;
    border: none;
    color: #fff;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 6px;
    transition: all 0.3s ease;
    margin: 0 2px;
}

.btn-eliminar:hover {
    background-color: #c82333;
    transform: translateY(-2px);
}

/* Estilos para el formulario */
.form-container {
    background-color: #222;
    border-radius: 12px;
    padding: 25px;
    margin: 30px 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    border: 1px solid #333;
}

.form-header {
    margin-bottom: 25px;
    text-align: center;
    border-bottom: 2px solid #eaf822;
    padding-bottom: 15px;
}

.form-title {
    color: #eaf822;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 0;
}

.form-label {
    color: #eaf822;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.form-control {
    background-color: #333;
    border: 1px solid #555;
    color: #ffffff;
    border-radius: 8px;
    padding: 10px 15px;
    transition: all 0.3s ease;
    width: 100%;
}

.form-control:focus {
    background-color: #333;
    border-color: #eaf822;
    box-shadow: 0 0 0 0.2rem rgba(234, 248, 34, 0.25);
    color: #ffffff;
    outline: none;
}

.form-control::placeholder {
    color: #888;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.form-actions .btn {
    min-width: 150px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    padding: 10px 20px;
}

.form-actions .btn:hover {
    transform: translateY(-2px);
}

.btn-primary {
    background-color: #eaf822;
    border-color: #eaf822;
    color: #000;
}

.btn-primary:hover {
    background-color: #f50c18;
    border-color: #f50c18;
    color: #ffffff;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: #ffffff;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #5a6268;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #ffffff;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #c82333;
}

/* Estilos para el modal */
.modal-content {
    background-color: #222;
    border: 1px solid #333;
    color: #ffffff;
    border-radius: 12px;
}

.modal-header {
    border-bottom: 1px solid #333;
}

.modal-title {
    color: #eaf822;
}

.btn-close {
    filter: invert(1);
}

.modal-footer {
    border-top: 1px solid #333;
}

/* Responsivo */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .empleados-header {
        padding: 20px;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .empleados-table-container {
        padding: 15px;
        margin: 20px 0;
    }
    
    .empleados-table {
        font-size: 0.9rem;
    }
    
    .empleados-table thead th,
    .empleados-table tbody td {
        padding: 10px 8px;
    }
}
</style>
</head>

<body class="body-empleados">

<div class="empleados-header">
    <h1 class="page-title">Gestión de Empleados</h1>
    <p class="page-subtitle">Administra los empleados del sistema</p>
</div>

<div class="empleados-container">
    <div class="container">
        
        <?php if (isset($_GET['insertado'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> El empleado se ha agregado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['editado'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> El empleado se ha actualizado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['eliminado'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>¡Éxito!</strong> El empleado se ha eliminado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="empleados-table-container">
            <div class="table-header">
                <h2 class="table-title">Lista de Empleados</h2>
            </div>
            
            <div class="table-responsive">
                <table class="empleados-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Fecha de Nacimiento</th>
                            <th>Correo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php if (empty($usuarios_medium)): ?>
                <tr class="no-data">
                    <td colspan="5">No hay empleados registrados</td>
                </tr>
            <?php else: ?>
                <?php foreach ($usuarios_medium as $usuario): ?>
                    <tr data-id="<?= $usuario['id'] ?>"
                        data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                        data-apellido="<?= htmlspecialchars($usuario['apellido']) ?>"
                        data-fecha_nacimiento="<?= $usuario['fecha_nacimiento'] ?>"
                        data-correo="<?= htmlspecialchars($usuario['correo']) ?>">
                        <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                        <td><?= htmlspecialchars($usuario['apellido']) ?></td>
                        <td><?= htmlspecialchars($usuario['fecha_nacimiento']) ?></td>
                        <td><?= htmlspecialchars($usuario['correo']) ?></td>
                        <td>
                            <button class="btn btn-editar btn-sm btnEditar">Editar</button>
                            <button type="button" class="btn btn-eliminar btn-sm btnEliminar" data-id="<?= $usuario['id'] ?>">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center">
                <button class="btn btn-agregar" id="btnAgregarUsuario">Agregar Nuevo Empleado</button>
            </div>
        </div>

        <div id="formUsuarioContainer" class="form-container" style="display:none;">
            <div class="form-header">
                <h2 class="form-title">Formulario de Empleado</h2>
            </div>
            
            <form id="formUsuario" action="../conexion/empleados/insertar_editar.php" method="POST">
                <input type="hidden" name="id" id="inputId" value="">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="inputNombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" id="inputNombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="inputApellido" class="form-label">Apellido</label>
                        <input type="text" class="form-control" name="apellido" id="inputApellido" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="inputFechaNacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" name="fecha_nacimiento" id="inputFechaNacimiento" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="inputCorreo" class="form-label">Correo</label>
                        <input type="email" class="form-control" name="correo" id="inputCorreo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="inputPassword" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="contrasena" id="inputPassword" placeholder="Dejar vacío para no cambiar contraseña en edición">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                    <button type="button" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnCerrar">Cerrar</button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- Modal Confirmación Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEliminar" method="POST" action="">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEliminarLabel">Confirmar eliminación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          ¿Está seguro que desea eliminar este empleado?
        </div>
        <input type="hidden" name="id" id="inputEliminarId" value="">
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Eliminar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>

</body>
</html>

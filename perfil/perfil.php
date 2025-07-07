<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../conexion/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Verificar que sea un usuario normal (no admin ni empleado)
if ($_SESSION['rol'] !== 'normal') {
    header("Location: ../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';
$tipo_mensaje = '';

// Procesar edición del perfil

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $correo = trim($_POST['correo']);
        $telefono = trim($_POST['telefono']);
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $sexo = $_POST['sexo'];
        $nueva_contrasena = trim($_POST['nueva_contrasena']);
        $confirmar_contrasena = trim($_POST['confirmar_contrasena']);
        // Validaciones
        if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono)) {
            throw new Exception("Todos los campos son obligatorios.");
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El correo electrónico no es válido.");
        }
        // Validar edad
        $fecha_nac = new DateTime($fecha_nacimiento);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha_nac)->y;
        if ($edad < 18) {
            throw new Exception("Debes ser mayor de 18 años.");
        }
        // Validar contraseña si se está cambiando
        if (!empty($nueva_contrasena)) {
            if ($nueva_contrasena !== $confirmar_contrasena) {
                throw new Exception("Las contraseñas no coinciden.");
            }
            if (strlen($nueva_contrasena) < 8) {
                throw new Exception("La contraseña debe tener al menos 8 caracteres.");
            }
        }
        // Verificar si el correo ya existe (excluyendo el usuario actual)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios_normales WHERE correo = ? AND id != ?");
        $stmt->execute([$correo, $usuario_id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("El correo electrónico ya está en uso por otro usuario.");
        }
        // Actualizar datos
        if (!empty($nueva_contrasena)) {
            $contrasena_hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios_normales SET nombre = ?, apellido = ?, correo = ?, telefono = ?, fecha_nacimiento = ?, sexo = ?, contrasena = ? WHERE id = ?");
            $stmt->execute([$nombre, $apellido, $correo, $telefono, $fecha_nacimiento, $sexo, $contrasena_hash, $usuario_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios_normales SET nombre = ?, apellido = ?, correo = ?, telefono = ?, fecha_nacimiento = ?, sexo = ? WHERE id = ?");
            $stmt->execute([$nombre, $apellido, $correo, $telefono, $fecha_nacimiento, $sexo, $usuario_id]);
        }
        // Actualizar sesión
        $_SESSION['nombre'] = $nombre;
        $mensaje = "Perfil actualizado correctamente.";
        $tipo_mensaje = 'success';
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipo_mensaje = 'error';
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar el perfil: " . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// ...existing code...
    
    try {
        // Obtener datos del usuario
        $stmt = $pdo->prepare("SELECT * FROM usuarios_normales WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            header("Location: ../login/login.php");
            exit();
        }
        
        // Calcular la edad
        $fecha_nacimiento = new DateTime($usuario['fecha_nacimiento']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha_nacimiento)->y;
        
        // Obtener total de compras realizadas
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_compras FROM historial_pagos_entradas WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $total_compras_entradas = $stmt->fetch(PDO::FETCH_ASSOC)['total_compras'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_compras FROM historial_pagos_alimentos WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $total_compras_alimentos = $stmt->fetch(PDO::FETCH_ASSOC)['total_compras'];
        
        $total_compras = $total_compras_entradas + $total_compras_alimentos;
        
        // Obtener total gastado
        $stmt = $pdo->prepare("SELECT SUM(monto_pago) as total_gastado FROM historial_pagos_entradas WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $total_gastado_entradas = $stmt->fetch(PDO::FETCH_ASSOC)['total_gastado'] ?? 0;
        
        $stmt = $pdo->prepare("SELECT SUM(monto_pago) as total_gastado FROM historial_pagos_alimentos WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $total_gastado_alimentos = $stmt->fetch(PDO::FETCH_ASSOC)['total_gastado'] ?? 0;
        
        $total_gastado = $total_gastado_entradas + $total_gastado_alimentos;
        
    } catch (PDOException $e) {
        $mensaje = "Error al obtener los datos del perfil: " . $e->getMessage();
        $tipo_mensaje = 'error';
    }
    
    include '../resources/header/header.php';
    ?>
    
    <div class="contenido">
        <div class="perfil-header perfil-contenido">
            <h1 class="perfil-titulo">Mi Perfil</h1>
            <p class="perfil-subtitulo">Aquí puedes ver toda la información de tu cuenta</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje === 'error' ? 'error' : 'success' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>
        
        <div class="perfil-container">
            <!-- Información Personal -->
            <div class="perfil-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2 class="card-title">Información Personal</h2>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Nombre Completo:</span>
                    <span class="info-value"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Correo Electrónico:</span>
                    <span class="info-value"><?= htmlspecialchars($usuario['correo']) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value"><?= htmlspecialchars($usuario['telefono']) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Fecha de Nacimiento:</span>
                    <span class="info-value"><?= date('d/m/Y', strtotime($usuario['fecha_nacimiento'])) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Edad:</span>
                    <span class="info-value"><?= $edad ?> años</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Sexo:</span>
                    <span class="info-value"><?= htmlspecialchars($usuario['sexo']) ?></span>
                </div>
            </div>
            
            <!-- Estadísticas de Compras -->
            <div class="perfil-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h2 class="card-title">Estadísticas de Compras</h2>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Total de Compras:</span>
                    <span class="info-value"><?= number_format($total_compras) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Compras de Entradas:</span>
                    <span class="info-value"><?= number_format($total_compras_entradas) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Compras de Alimentos:</span>
                    <span class="info-value"><?= number_format($total_compras_alimentos) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Total Gastado:</span>
                    <span class="info-value">$<?= number_format($total_gastado, 2) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Gastado en Entradas:</span>
                    <span class="info-value">$<?= number_format($total_gastado_entradas, 2) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Gastado en Alimentos:</span>
                    <span class="info-value">$<?= number_format($total_gastado_alimentos, 2) ?></span>
                </div>
            </div>
            
            <!-- Puntos de Fidelidad -->
            <div class="perfil-card puntos-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h2 class="card-title">Puntos de Fidelidad</h2>
                </div>
                
                <div class="puntos-numero"><?= number_format($usuario['puntos'] ?? 0) ?></div>
                <p class="puntos-descripcion">
                    Puntos acumulados por tus compras. ¡Sigue comprando para obtener más beneficios!
                </p>
            </div>
        </div>
        
        <!-- Botones de Acción -->
        <div class="acciones-container">
            <a href="#" class="btn-accion btn-editar" data-bs-toggle="modal" data-bs-target="#modalEditarPerfil">
                <i class="fas fa-edit"></i>
                Editar Perfil
            </a>
            <a href="../historial_pagos_entradas/historial.php" class="btn-accion btn-historial">
                <i class="fas fa-history"></i>
                Historial de Compras
            </a>
            <a href="../index.php" class="btn-accion btn-volver">
                <i class="fas fa-arrow-left"></i>
                Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Modal para Editar Perfil -->
    <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarPerfilLabel">Editar Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarPerfil" method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombre:</label>
                                    <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Apellido:</label>
                                    <input type="text" class="form-control" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico:</label>
                            <input type="email" class="form-control" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Teléfono:</label>
                            <input type="tel" class="form-control" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Fecha de Nacimiento:</label>
                                    <input type="date" class="form-control" name="fecha_nacimiento" value="<?= $usuario['fecha_nacimiento'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Sexo:</label>
                                    <select class="form-select" name="sexo" required>
                                        <option value="Hombre" <?= $usuario['sexo'] == 'Hombre' ? 'selected' : '' ?>>Hombre</option>
                                        <option value="Mujer" <?= $usuario['sexo'] == 'Mujer' ? 'selected' : '' ?>>Mujer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña (opcional):</label>
                            <input type="password" class="form-control" name="nueva_contrasena" placeholder="Dejar vacío para mantener la contraseña actual">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nueva Contraseña:</label>
                            <input type="password" class="form-control" name="confirmar_contrasena" placeholder="Confirmar nueva contraseña">
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> Si cambias tu correo electrónico, deberás verificar la nueva dirección.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEditarPerfil" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación del formulario
        document.getElementById('formEditarPerfil').addEventListener('submit', function(e) {
            const nuevaContrasena = document.querySelector('input[name="nueva_contrasena"]').value;
            const confirmarContrasena = document.querySelector('input[name="confirmar_contrasena"]').value;
            
            if (nuevaContrasena || confirmarContrasena) {
                if (nuevaContrasena !== confirmarContrasena) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden.');
                    return;
                }
                
                if (nuevaContrasena.length < 8) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 8 caracteres.');
                    return;
                }
            }
            
            // Validación de edad
            const fechaNacimiento = new Date(document.querySelector('input[name="fecha_nacimiento"]').value);
            const hoy = new Date();
            const edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
            
            if (edad < 18) {
                e.preventDefault();
                alert('Debes ser mayor de 18 años para registrarte.');
                return;
            }
        });
    </script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'medium') {
    header("Location: ../login.php");  exit();
}

include '../conexion/conexion.php';
include '../resources/header/header.php';

/* ──────────────────────────────────  Datos base ────────────────────────────────── */
$peliculas  = $pdo->query("SELECT id, titulo, poster, duracion FROM peliculas ORDER BY titulo")->fetchAll(PDO::FETCH_ASSOC);
$salas      = $pdo->query("SELECT numero_sala FROM salas ORDER BY numero_sala")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Horarios</title>
    <link rel="icon" type="image/png" href="../resources/index/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../resources/header/css/styles.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="body-horarios">
    <div class="container-fluid">
        <div class="horarios-header">
            <h1 class="page-title">Gestión de Horarios</h1>
            <p class="page-subtitle">Administra los horarios de las películas en cartelera</p>
            
            <?php if (!empty($_GET['error'])): ?>
                <div class="alert alert-danger alert-custom">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php elseif (!empty($_GET['mensaje'])): ?>
                <div class="alert alert-success alert-custom">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['mensaje']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="peliculas-container">
            <?php if (empty($peliculas)): ?>
                <div class="sin-peliculas">
                    <h3>No hay películas disponibles</h3>
                    <p>Agrega películas para poder asignar horarios</p>
                </div>
            <?php else: ?>
                <div class="peliculas-grid">
                    <?php foreach ($peliculas as $p): ?>
                        <div class="pelicula-card">
                            <div class="pelicula-contenido">
                                <div class="pelicula-info">
                                    <h4 class="pelicula-titulo"><?= htmlspecialchars($p['titulo']) ?></h4>
                                    <p style="margin: 5px 0; font-size: 14px; font-family: 'Roboto', sans-serif;"><span style="color: #eaf822; font-weight: 500;">Duración:</span> <span style="color: #ffffff;">
                                        <?php 
                                        // Formatear duración desde minutos a horas y minutos
                                        $duracion_total = (int)$p['duracion'];
                                        $horas = floor($duracion_total / 60);
                                        $minutos = $duracion_total % 60;
                                        echo $horas . "h " . $minutos . "min";
                                        ?>
                                    </span></p>
                                    <p class="pelicula-descripcion">Configura los horarios de esta película</p>
                                </div>
                                <div class="pelicula-poster">
                                    <?php if (!empty($p['poster'])): ?>
                                        <?php if (preg_match('/^https?:\/\//', $p['poster'])): ?>
                                            <img src="<?= htmlspecialchars($p['poster']) ?>" 
                                                 alt="<?= htmlspecialchars($p['titulo']) ?>"
                                                 onerror="this.src='../resources/index/img/no-image.png'">
                                        <?php else: ?>
                                            <img src="../posters/<?= htmlspecialchars($p['poster']) ?>" 
                                                 alt="<?= htmlspecialchars($p['titulo']) ?>"
                                                 onerror="this.src='../resources/index/img/no-image.png'">
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="poster-placeholder">
                                            <i class="fas fa-film"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="pelicula-acciones">
                                <button class="btn btn-warning btn-editar-horario btnEditar"
                                        data-id="<?= $p['id'] ?>"
                                        data-titulo="<?= htmlspecialchars($p['titulo']) ?>">
                                    <i class="fas fa-clock"></i> Editar Horario
                                </button>
                                <button class="btn btn-info btn-ver-horarios btnVerHorarios"
                                        data-id="<?= $p['id'] ?>"
                                        data-titulo="<?= htmlspecialchars($p['titulo']) ?>">
                                    <i class="fas fa-calendar-alt"></i> Ver Horarios
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ──────────────── Panel: crear / editar horario ──────────────── -->
        <div id="formHorarioContainer" class="form-container" style="display:none;">
            <div class="form-header">
                <h5 class="form-title">
                    <i class="fas fa-clock"></i> Asignar horario a <span id="peliculaNombre"></span>
                </h5>
            </div>

            <form id="formHorario" method="POST" action="../conexion/horarios/guardar_horario.php">
                <input type="hidden" name="pelicula_id" id="inputPelId">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-door-open"></i> Sala
                        </label>
                        <select name="numero_sala" id="inputSala" class="form-select" required>
                            <option value="">Seleccione una sala...</option>
                            <?php foreach ($salas as $s): ?>
                                <option value="<?= $s ?>">Sala <?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar"></i> Fecha
                        </label>
                        <input type="date" class="form-control" name="fecha" id="inputFecha" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-play"></i> Hora de inicio
                        </label>
                        <input type="time" name="hora_inicio" id="inputHoraInicio" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-stop"></i> Hora de fin
                        </label>
                        <input type="time" name="hora_fin" id="inputHoraFin" class="form-control" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Horario
                    </button>
                    <button type="button" id="btnCerrarHorario" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>

        <!-- ──────────────── Panel: lista de horarios ──────────────── -->
        <div id="panelHorarios" class="horarios-panel" style="display:none;">
            <div class="panel-header">
                <h5 class="panel-title">
                    <i class="fas fa-calendar-alt"></i> Horarios de <span id="horarioTitulo"></span>
                </h5>
            </div>
            <div id="listaHorarios" class="horarios-lista"></div>
            <div class="panel-actions">
                <button id="btnCerrarListaHorarios" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de eliminación -->
    <div class="modal fade" id="modalEliminarHorario" tabindex="-1" aria-labelledby="modalEliminarHorarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEliminarHorario" method="POST" action="../conexion/horarios/eliminar_horario.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEliminarHorarioLabel">
                            <i class="fas fa-trash"></i> Eliminar horario
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar este horario?</p>
                        <p>Esta acción no se puede deshacer.</p>
                    </div>
                    <input type="hidden" name="pelicula_horario_id" id="inputEliminarHorarioId">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
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

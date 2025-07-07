<title>Cinéfilos</title>
<link rel="icon" type="image/png" href="resources/index/img/logo.png">

<?php
include 'conexion/conexion.php';
include 'resources/header/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="resources/index/css/styles.css">


<?php
// Seleccionar una película destacada (la más reciente por ID) para todos excepto admin y empleados
$pelicula_destacada = null;
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'medium')) {
    $stmtDestacada = $pdo->query("SELECT * FROM peliculas ORDER BY id DESC LIMIT 1");
    $pelicula_destacada = $stmtDestacada->fetch(PDO::FETCH_ASSOC);
}
?>

<?php if ($pelicula_destacada): ?>
<!-- Sección Destacada -->
<div class="seccion-destacada">
    <div class="destacada-background">
        <?php if (isset($pelicula_destacada['trailer']) && !empty($pelicula_destacada['trailer'])): ?>
            <div class="destacada-video-wrapper">
                <?php 
                // Extraer ID de YouTube
                $video_id = '';
                if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $pelicula_destacada['trailer'], $match)) {
                    $video_id = $match[1];
                }
                ?>
                <?php if (!empty($video_id)): ?>
                    <iframe class="destacada-video" src="https://www.youtube.com/embed/<?= $video_id ?>?autoplay=1&mute=1&controls=0&showinfo=0&loop=1&playlist=<?= $video_id ?>&modestbranding=1&rel=0&vq=hd1080" frameborder="0" allowfullscreen></iframe>
                <?php endif; ?>
                <div class="destacada-overlay"></div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="destacada-contenido">
        <div class="destacada-info">
            <div class="destacada-etiqueta">
                <span class="estrella">★</span> DESTACADA DE LA SEMANA
            </div>
            <h2 class="destacada-titulo"><?= htmlspecialchars($pelicula_destacada['titulo']) ?></h2>
            <div class="destacada-botones">
                <a href="comprar/comprar_entradas.php" class="btn-destacada btn-comprar-destacada">Comprar boletos</a>
                <?php if (isset($pelicula_destacada['trailer']) && !empty($pelicula_destacada['trailer'])): ?>
                    <a href="<?= htmlspecialchars($pelicula_destacada['trailer']) ?>" target="_blank" class="btn-destacada btn-trailer">Ver trailer</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="destacada-poster">
            <?php if (!empty($pelicula_destacada['poster'])): ?>
                <?php if (preg_match('/^https?:\/\//', $pelicula_destacada['poster'])): ?>
                    <img src="<?= htmlspecialchars($pelicula_destacada['poster']) ?>" alt="<?= htmlspecialchars($pelicula_destacada['titulo']) ?>">
                <?php else: ?>
                    <img src="posters/<?= htmlspecialchars($pelicula_destacada['poster']) ?>" alt="<?= htmlspecialchars($pelicula_destacada['titulo']) ?>">
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
<!-- Sección Panel de Administración -->
<?php
// Obtener estadísticas para el admin
try {
    // Cantidad de usuarios registrados (solo usuarios normales)
    $stmtUsuarios = $pdo->query("SELECT COUNT(*) as total_usuarios FROM usuarios_normales");
    $totalUsuarios = $stmtUsuarios->fetch(PDO::FETCH_ASSOC)['total_usuarios'];
    
    // Ingresos por comida (de la tabla historial_pagos_alimentos)
    $stmtComida = $pdo->query("SELECT SUM(monto_pago) as ingresos_comida FROM historial_pagos_alimentos");
    $resultComida = $stmtComida->fetch(PDO::FETCH_ASSOC);
    $ingresosComida = $resultComida['ingresos_comida'] ?? 0;
    
    // Ingresos por sala (de la tabla historial_pagos_entradas)
    $stmtSala = $pdo->query("SELECT SUM(monto_pago) as ingresos_sala FROM historial_pagos_entradas");
    $resultSala = $stmtSala->fetch(PDO::FETCH_ASSOC);
    $ingresosSala = $resultSala['ingresos_sala'] ?? 0;
    
    // Ingresos totales
    $ingresosTotales = $ingresosComida + $ingresosSala;
    
} catch (PDOException $e) {
    // Si hay error, establecer valores por defecto
    $totalUsuarios = 0;
    $ingresosComida = 0;
    $ingresosSala = 0;
    $ingresosTotales = 0;
}
?>

<div class="panel-admin">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Usuarios Registrados -->
            <div class="col-md-6 col-lg-3">
                <div class="card-admin card-usuarios">
                    <div class="card-admin-body">
                        <div class="card-admin-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-admin-info">
                            <h3 class="card-admin-number"><?= number_format($totalUsuarios) ?></h3>
                            <p class="card-admin-title">Usuarios Registrados</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ingresos por Comida -->
            <div class="col-md-6 col-lg-3">
                <div class="card-admin card-comida">
                    <div class="card-admin-body">
                        <div class="card-admin-icon">
                            <i class="fas fa-hamburger"></i>
                        </div>
                        <div class="card-admin-info">
                            <h3 class="card-admin-number">$<?= number_format($ingresosComida, 2) ?></h3>
                            <p class="card-admin-title">Ingresos por Comida</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ingresos por Sala -->
            <div class="col-md-6 col-lg-3">
                <div class="card-admin card-sala">
                    <div class="card-admin-body">
                        <div class="card-admin-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="card-admin-info">
                            <h3 class="card-admin-number">$<?= number_format($ingresosSala, 2) ?></h3>
                            <p class="card-admin-title">Ingresos por Sala</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ingresos Totales -->
            <div class="col-md-6 col-lg-3">
                <div class="card-admin card-totales">
                    <div class="card-admin-body">
                        <div class="card-admin-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="card-admin-info">
                            <h3 class="card-admin-number">$<?= number_format($ingresosTotales, 2) ?></h3>
                            <p class="card-admin-title">Ingresos Totales</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'medium'): ?>
<!-- Sección Panel de Empleados -->
<?php
// Obtener estadísticas para el empleado
try {
    // Salas con funciones activas (salas que tienen horarios programados)
    $stmtSalas = $pdo->query("
        SELECT COUNT(DISTINCT ph.sala_id) as salas_activas 
        FROM pelicula_horario ph 
        JOIN horarios h ON ph.horario_id = h.id 
        WHERE h.fecha >= CURRENT_DATE
    ");
    $salasActivas = $stmtSalas->fetch(PDO::FETCH_ASSOC)['salas_activas'] ?? 0;
    
    // Total de horarios programados (registros en pelicula_horario)
    $stmtHorarios = $pdo->query("SELECT COUNT(*) as total_horarios FROM pelicula_horario");
    $totalHorarios = $stmtHorarios->fetch(PDO::FETCH_ASSOC)['total_horarios'] ?? 0;
    
    // Número de alimentos en la base de datos
    $stmtAlimentos = $pdo->query("SELECT COUNT(*) as total_alimentos FROM alimentos");
    $totalAlimentos = $stmtAlimentos->fetch(PDO::FETCH_ASSOC)['total_alimentos'] ?? 0;
    
} catch (PDOException $e) {
    // Si hay error, establecer valores por defecto
    $salasActivas = 0;
    $totalHorarios = 0;
    $totalAlimentos = 0;
}
?>

<div class="panel-empleado">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Salas Activas -->
            <div class="col-md-6 col-lg-4">
                <div class="card-empleado card-salas">
                    <div class="card-empleado-body">
                        <div class="card-empleado-icon">
                            <i class="fas fa-film"></i>
                        </div>
                        <div class="card-empleado-info">
                            <h3 class="card-empleado-number"><?= number_format($salasActivas) ?></h3>
                            <p class="card-empleado-title">Salas con Funciones Activas</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total de Horarios -->
            <div class="col-md-6 col-lg-4">
                <div class="card-empleado card-horarios">
                    <div class="card-empleado-body">
                        <div class="card-empleado-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-empleado-info">
                            <h3 class="card-empleado-number"><?= number_format($totalHorarios) ?></h3>
                            <p class="card-empleado-title">Horarios Programados</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total de Alimentos -->
            <div class="col-md-6 col-lg-4">
                <div class="card-empleado card-alimentos">
                    <div class="card-empleado-body">
                        <div class="card-empleado-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="card-empleado-info">
                            <h3 class="card-empleado-number"><?= number_format($totalAlimentos) ?></h3>
                            <p class="card-empleado-title">Alimentos en el Menú</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<body style="background-color: #1a1a1a">

<div id="ultimos-estrenos" class="seccion-principal <?php 
    if ($pelicula_destacada) {
        echo 'con-destacada';
    } elseif (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
        echo 'con-admin';
    } elseif (isset($_SESSION['rol']) && $_SESSION['rol'] === 'medium') {
        echo 'con-empleado';
    }
?>">
    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <div class="cartelera-header">
            <div class="cartelera-info">
                <h1 class="titulos">Cartelera</h1>
                <p class="p-text">Presiona para eliminar o editar la información de una película.</p>
            </div>
            <div class="cartelera-botones">
                <button class="btn-admin-action btn-agregar" data-bs-toggle="modal" data-bs-target="#modalFormAgregar">
                    <i class="fas fa-plus"></i>
                    Agregar Película
                </button>
                <button class="btn-admin-action btn-proximo" data-bs-toggle="modal" data-bs-target="#modalFormAgregarProximo">
                    <i class="fas fa-calendar-plus"></i>
                    Agregar Próximo Estreno
                </button>
            </div>
        </div>
    <?php elseif (isset($_SESSION['rol']) && $_SESSION['rol'] === 'medium'): ?>
        <h1 class="titulos">Cartelera</h1>
    <?php else: ?>
        <h2 class="titulos">Últimos Estrenos</h2>
    <?php endif; ?>


<!-- Mostrar películas (Cartelera) -->
<div class="peliculas-container">
    <?php foreach ($peliculas_guardadas as $pelicula): ?>
        <div class="pelicula" data-id="<?= $pelicula['id'] ?>"
            data-titulo="<?= htmlspecialchars($pelicula['titulo']) ?>"
            data-idioma="<?= htmlspecialchars($pelicula['idioma']) ?>"
            data-clasificacion="<?= htmlspecialchars($pelicula['clasificacion']) ?>"
            data-genero="<?= htmlspecialchars($pelicula['genero']) ?>"
            data-valoracion="<?= $pelicula['valoracion'] ?>"
            data-duracion="<?= $pelicula['duracion'] ?>"
            data-descripcion="<?= htmlspecialchars($pelicula['descripcion']) ?>"
            data-fecha_estreno="<?= $pelicula['fecha_estreno'] ?>"
            data-trailer="<?= htmlspecialchars($pelicula['trailer']) ?>"
            data-actores="<?= htmlspecialchars($pelicula['actores']) ?>"
            data-poster="<?= htmlspecialchars($pelicula['poster']) ?>">
            
            <?php if (preg_match('/^https?:\/\//', $pelicula['poster'])): ?>
                <img src="<?= htmlspecialchars($pelicula['poster']) ?>" alt="<?= htmlspecialchars($pelicula['titulo']) ?>">
            <?php else: ?>
                <img src="posters/<?= htmlspecialchars($pelicula['poster']) ?>" alt="<?= htmlspecialchars($pelicula['titulo']) ?>">
            <?php endif; ?>
                        
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <div class="pelicula-overlay">
                    <button class="editar-btn" type="button">Editar</button>
                    <button type="button" class="eliminar-btn" data-id="<?= $pelicula['id'] ?>" data-bs-toggle="modal" data-bs-target="#modalEliminarPelicula">Eliminar</button>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Sección Próximos Estrenos -->
<?php
$stmtProx = $pdo->query("SELECT * FROM proximos ORDER BY fecha_estreno DESC");
$proximos_guardados = $stmtProx->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="titulos mt-5">Próximos Estrenos</h2>
<?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
    <p class="p-text">Presiona para eliminar o editar la información de un próximo estreno.</p>
<?php endif; ?>
<div class="peliculas-container">
    <?php foreach ($proximos_guardados as $p): ?>
        <div class="pelicula" data-id="<?= $p['id'] ?>" data-tipo="proximo"
            data-titulo="<?= htmlspecialchars($p['titulo']) ?>"
            data-idioma="<?= htmlspecialchars($p['idioma']) ?>"
            data-clasificacion="<?= htmlspecialchars($p['clasificacion']) ?>"
            data-genero="<?= htmlspecialchars($p['genero']) ?>"
            data-valoracion="<?= $p['valoracion'] ?>"
            data-duracion="<?= $p['duracion'] ?>"
            data-descripcion="<?= htmlspecialchars($p['descripcion']) ?>"
            data-fecha_estreno="<?= $p['fecha_estreno'] ?>"
            data-trailer="<?= htmlspecialchars($p['trailer']) ?>"
            data-actores="<?= htmlspecialchars($p['actores']) ?>"
            data-poster="<?= htmlspecialchars($p['poster']) ?>">
            
            <?php if (preg_match('/^https?:\/\//', $p['poster'])): ?>
                <img src="<?= htmlspecialchars($p['poster']) ?>" alt="<?= htmlspecialchars($p['titulo']) ?>">
            <?php else: ?>
                <img src="posters/<?= htmlspecialchars($p['poster']) ?>" alt="<?= htmlspecialchars($p['titulo']) ?>">
            <?php endif; ?>
                        
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <div class="pelicula-overlay">
                    <button class="editar-btn" type="button">Editar</button>
                    <button type="button" class="eliminar-btn" data-id="<?= $p['id'] ?>" data-bs-toggle="modal" data-bs-target="#modalEliminarPelicula">Eliminar</button>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>



<!-- Modal para agregar/editar películas -->
<div class="modal fade" id="modalFormAgregar" tabindex="-1" aria-labelledby="modalFormAgregarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalFormAgregarLabel">Agregar Película</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formAgregarPelicula" action="conexion/insertar.php" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
              <label class="form-label">Título de la película:</label>
              <input type="text" class="form-control" name="titulo" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Poster:</label>
              <input type="url" class="form-control mb-2" name="poster_url" id="posterUrlInput" placeholder="Coloca aquí la URL del poster" pattern="https?://.*" required>
              <input type="hidden" name="poster_actual" id="posterActualInput">
              <div id="posterPreviewContainer" class="mt-2"></div>
          </div>
          <div class="mb-3">
              <label class="form-label">Idioma:</label>
              <input type="text" class="form-control" name="idioma" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Clasificación:</label>
              <select class="form-select" name="clasificacion" required>
                  <option value="">Seleccione...</option>
                  <option value="AA">AA</option>
                  <option value="A">A</option>
                  <option value="B">B</option>
                  <option value="B15">B15</option>
                  <option value="C">C</option>
                  <option value="D">D</option>
              </select>
          </div>
          <div class="mb-3">
              <label class="form-label">Género:</label>
              <div id="generosSeleccionados" class="mb-2"></div>
              <button type="button" id="btnAgregarGenero" class="btn btn-secondary btn-sm mb-2">Agregar Género</button>
              <select id="selectGeneros" class="form-select" style="display:none;"></select>
          </div>
          <div class="mb-3">
              <label class="form-label">Valoración (0 a 100):</label>
              <input type="number" class="form-control" name="valoracion" min="0" max="100" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Duración:</label>
              <div class="d-flex gap-2">
                  <select class="form-select" name="horas" required>
                      <option value="">Horas</option>
                      <?php for ($i = 0; $i <= 4; $i++): ?>
                          <option value="<?= $i ?>"><?= $i ?> h</option>
                      <?php endfor; ?>
                  </select>
                  <select class="form-select" name="minutos" required>
                      <option value="">Minutos</option>
                      <?php for ($i = 0; $i < 60; $i += 5): ?>
                          <option value="<?= $i ?>"><?= str_pad($i, 2, "0", STR_PAD_LEFT) ?> min</option>
                      <?php endfor; ?>
                  </select>
              </div>
          </div>
          <div class="mb-3">
              <label class="form-label">Descripción:</label>
              <textarea class="form-control" name="descripcion" rows="3" required></textarea>
          </div>
          <div class="mb-3">
              <label class="form-label">Fecha de estreno:</label>
              <input type="date" class="form-control" name="fecha_estreno" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Tráiler (URL):</label>
              <input type="url" class="form-control" name="trailer">
          </div>
          <div class="mb-3">
              <label class="form-label">Actores principales:</label>
              <div id="actoresContainer" class="mb-2">
                  <input type="text" class="form-control mb-1" name="actores[]" placeholder="Nombre del actor" required>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="agregarActor()">+ Agregar otro actor</button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
          <button type="submit" form="formAgregarPelicula" class="btn btn-primary" id="btnAgregar">Agregar Película</button>
          <button type="submit" form="formAgregarPelicula" class="btn btn-success" id="btnGuardarCambios" style="display:none;">Guardar Cambios</button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal para agregar próximos estrenos -->
<!-- Modal para eliminar (película o próximo estreno) -->
<div class="modal fade" id="modalEliminarPelicula" tabindex="-1" aria-labelledby="modalEliminarPeliculaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEliminarPeliculaLabel">Confirmar eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formEliminarPelicula" method="POST" action="conexion/eliminar.php">
        <div class="modal-body">
          <p style="color: #ffffff; font-size: 1.1rem; text-align: center; margin-bottom: 20px;">
            ¿Está seguro que desea eliminar este elemento?
          </p>
          <div style="text-align: center; color: #dc3545; font-weight: 600;">
            <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 10px;"></i>
            <br>
            Esta acción no se puede deshacer
          </div>
        </div>
        <input type="hidden" name="id" id="inputEliminarPeliculaId" value="">
        <input type="hidden" name="tipo" id="inputEliminarTipo" value="">
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Eliminar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="modalFormAgregarProximo" tabindex="-1" aria-labelledby="modalFormAgregarProximoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalFormAgregarProximoLabel">Agregar Próximo Estreno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formAgregarProximo" action="conexion/insertar.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="es_proximo" value="1">
          <div class="mb-3">
              <label class="form-label">Título:</label>
              <input type="text" class="form-control" name="titulo" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Poster:</label>
              <input type="url" class="form-control mb-2" name="poster_url" placeholder="Coloca aquí la URL del poster" pattern="https?://.*" required>
              <div id="posterPreviewContainerProximo" class="mt-2"></div>
          </div>
          <div class="mb-3">
              <label class="form-label">Idioma:</label>
              <input type="text" class="form-control" name="idioma" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Clasificación:</label>
              <select class="form-select" name="clasificacion" required>
                  <option value="">Seleccione...</option>
                  <option value="AA">AA</option>
                  <option value="A">A</option>
                  <option value="B">B</option>
                  <option value="B15">B15</option>
                  <option value="C">C</option>
                  <option value="D">D</option>
              </select>
          </div>
          <div class="mb-3">
              <label class="form-label">Género:</label>
              <div id="generosSeleccionadosProximo" class="mb-2"></div>
              <button type="button" id="btnAgregarGeneroProximo" class="btn btn-secondary btn-sm mb-2">Agregar Género</button>
              <select id="selectGenerosProximo" class="form-select" style="display:none;"></select>
          </div>
          <div class="mb-3">
              <label class="form-label">Valoración (0 a 100):</label>
              <input type="number" class="form-control" name="valoracion" min="0" max="100" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Duración:</label>
              <div class="d-flex gap-2">
                  <select class="form-select" name="horas_proximo" required>
                      <option value="">Horas</option>
                      <?php for ($i = 0; $i <= 4; $i++): ?>
                          <option value="<?= $i ?>"><?= $i ?> h</option>
                      <?php endfor; ?>
                  </select>
                  <select class="form-select" name="minutos_proximo" required>
                      <option value="">Minutos</option>
                      <?php for ($i = 0; $i < 60; $i += 5): ?>
                          <option value="<?= $i ?>"><?= str_pad($i, 2, "0", STR_PAD_LEFT) ?> min</option>
                      <?php endfor; ?>
                  </select>
              </div>
          </div>
          <div class="mb-3">
              <label class="form-label">Descripción:</label>
              <textarea class="form-control" name="descripcion" rows="3" required></textarea>
          </div>
          <div class="mb-3">
              <label class="form-label">Fecha de estreno:</label>
              <input type="date" class="form-control" name="fecha_estreno" required>
          </div>
          <div class="mb-3">
              <label class="form-label">Tráiler (URL):</label>
              <input type="url" class="form-control" name="trailer">
          </div>
          <div class="mb-3">
              <label class="form-label">Actores principales:</label>
              <div id="actoresContainerProximo" class="mb-2">
                  <input type="text" class="form-control mb-1" name="actores_proximo[]" placeholder="Nombre del actor" required>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="agregarActorProximo()">+ Agregar otro actor</button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
          <button type="submit" form="formAgregarProximo" class="btn btn-primary" id="btnAgregarProximo">Agregar Estreno</button>
          <button type="submit" form="formAgregarProximo" class="btn btn-success" id="btnGuardarCambiosProximo" style="display:none;">Guardar Cambios</button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<!-- Panel lateral de información de película -->
<div class="overlay-panel-pelicula" id="overlayPanelPelicula"></div>
<div class="panel-pelicula" id="panelPelicula">
    <button class="cerrar-panel" onclick="cerrarPanelPelicula()">&times;</button>
    
    <div class="contenido-panel">
        <h2 class="titulo-panel" id="tituloPanel"></h2>
        
        <!-- Trailer hasta arriba de la sinopsis -->
        <div class="trailer-panel" id="trailerPanel">
            <iframe id="trailerIframe" src="" frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen>
            </iframe>
        </div>
        
        <!-- Sinopsis -->
        <div class="sinopsis-panel">
            <p class="color-info"><span class="categoria">Sinopsis:</span></p>
            <p class="descripcion-panel" id="descripcionPanel"></p>
        </div>
        
        <!-- Información básica en orden descendente -->
        <div class="info-basica">
            <p class="color-info"><span class="categoria">Valoración:</span> <span id="valoracionPanel" class="color-info"></span> / 100</p>
            <p><span class="categoria">Idioma:</span> <span id="idiomaPanel" class="color-info"></span></p>
            <p><span class="categoria">Fecha de estreno:</span> <span id="fechaEstrenoPanel" class="color-info"></span></p>
            <p><span class="categoria">Clasificación:</span> <span id="clasificacionPanel" class="color-info"></span></p>
            <p><span class="categoria">Duración:</span> <span id="duracionPanel" class="color-info"></span></p>
            <p><span class="categoria">Actores:</span> <span id="actoresPanel" class="color-info"></span></p>
        </div>
        
        <!-- Géneros -->
        <div class="generos-panel">
            <p class="color-info"><span class="categoria">Géneros:</span></p>
            <div class="generos" id="generosPanel"></div>
        </div>
        
        <button class="btn-comprar" onclick="comprarEntradas()">Comprar Entradas</button>
    </div>
</div>

</body>

<script>
// Solo funciones básicas necesarias para los modales
function agregarActorProximo() {
    const container = document.getElementById('actoresContainerProximo');
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control mb-1';
    input.name = 'actores_proximo[]';
    input.placeholder = 'Nombre del actor';
    input.required = true;
    container.appendChild(input);
}

function agregarActor() {
    const container = document.getElementById('actoresContainer');
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control mb-1';
    input.name = 'actores[]';
    input.placeholder = 'Nombre del actor';
    input.required = true;
    container.appendChild(input);
}

// Función para comprar entradas
function comprarEntradas() {
    window.location.href = 'comprar/comprar_entradas.php';
}

// Funciones para limpiar modales
function limpiarModalPelicula() {
    // Limpiar formulario
    const form = document.getElementById('formAgregarPelicula');
    form.reset();
    
    // Restaurar acción original del formulario
    form.action = 'conexion/insertar.php';
    
    // Remover campos ocultos de edición si existen
    const camposEdicion = ['id', 'id_editar', 'tipo_editar'];
    camposEdicion.forEach(campo => {
        const input = form.querySelector(`input[name="${campo}"]`);
        if (input) {
            input.remove();
        }
    });
    
    // Limpiar géneros seleccionados
    document.getElementById('generosSeleccionados').innerHTML = '';
    document.getElementById('selectGeneros').style.display = 'none';
    
    // Limpiar actores (dejar solo uno)
    const actoresContainer = document.getElementById('actoresContainer');
    actoresContainer.innerHTML = '<input type="text" class="form-control mb-1" name="actores[]" placeholder="Nombre del actor" required>';
    
    // Limpiar preview del poster
    document.getElementById('posterPreviewContainer').innerHTML = '';
    
    // Resetear botones
    document.getElementById('btnAgregar').style.display = 'inline-block';
    document.getElementById('btnGuardarCambios').style.display = 'none';
    document.getElementById('modalFormAgregarLabel').textContent = 'Agregar Película';
}

function limpiarModalProximo() {
    // Limpiar formulario
    const form = document.getElementById('formAgregarProximo');
    form.reset();
    
    // Restaurar acción original del formulario
    form.action = 'conexion/insertar.php';
    
    // Remover campos ocultos de edición si existen
    const camposEdicion = ['id', 'id_editar', 'tipo_editar'];
    camposEdicion.forEach(campo => {
        const input = form.querySelector(`input[name="${campo}"]`);
        if (input) {
            input.remove();
        }
    });
    
    // Asegurar que el campo es_proximo esté presente
    let inputEsProximo = form.querySelector('input[name="es_proximo"]');
    if (!inputEsProximo) {
        inputEsProximo = document.createElement('input');
        inputEsProximo.type = 'hidden';
        inputEsProximo.name = 'es_proximo';
        inputEsProximo.value = '1';
        form.appendChild(inputEsProximo);
    }
    
    // Limpiar géneros seleccionados
    document.getElementById('generosSeleccionadosProximo').innerHTML = '';
    document.getElementById('selectGenerosProximo').style.display = 'none';
    
    // Limpiar actores (dejar solo uno)
    const actoresContainer = document.getElementById('actoresContainerProximo');
    actoresContainer.innerHTML = '<input type="text" class="form-control mb-1" name="actores_proximo[]" placeholder="Nombre del actor" required>';
    
    // Limpiar preview del poster
    document.getElementById('posterPreviewContainerProximo').innerHTML = '';
    
    // Resetear botones
    document.getElementById('btnAgregarProximo').style.display = 'inline-block';
    document.getElementById('btnGuardarCambiosProximo').style.display = 'none';
    document.getElementById('modalFormAgregarProximoLabel').textContent = 'Agregar Próximo Estreno';
}

// Event listeners para limpiar modales al cerrar
document.addEventListener('DOMContentLoaded', function() {
    // Limpiar modal de películas al cerrar
    const modalPelicula = document.getElementById('modalFormAgregar');
    modalPelicula.addEventListener('hidden.bs.modal', function () {
        limpiarModalPelicula();
    });
    
    // Limpiar modal de películas al abrir (para asegurar estado limpio)
    modalPelicula.addEventListener('show.bs.modal', function () {
        // Solo limpiar si no se está editando (si no hay campos de edición)
        const form = document.getElementById('formAgregarPelicula');
        const esEdicion = form.querySelector('input[name="id"]') || form.querySelector('input[name="id_editar"]');
        if (!esEdicion) {
            limpiarModalPelicula();
        }
    });
    
    // Limpiar modal de próximos estrenos al cerrar
    const modalProximo = document.getElementById('modalFormAgregarProximo');
    modalProximo.addEventListener('hidden.bs.modal', function () {
        limpiarModalProximo();
    });
    
    // Limpiar modal de próximos estrenos al abrir (para asegurar estado limpio)
    modalProximo.addEventListener('show.bs.modal', function () {
        // Solo limpiar si no se está editando (si no hay campos de edición)
        const form = document.getElementById('formAgregarProximo');
        const esEdicion = form.querySelector('input[name="id"]') || form.querySelector('input[name="id_editar"]');
        if (!esEdicion) {
            limpiarModalProximo();
        }
    });
    
    // Funcionalidad para agregar géneros en película
    document.getElementById('btnAgregarGenero').addEventListener('click', function() {
        const select = document.getElementById('selectGeneros');
        if (select.style.display === 'none') {
            // Cargar géneros disponibles
            cargarGeneros(select);
            select.style.display = 'block';
        } else {
            select.style.display = 'none';
        }
    });
    
    // Funcionalidad para agregar géneros en próximo estreno
    document.getElementById('btnAgregarGeneroProximo').addEventListener('click', function() {
        const select = document.getElementById('selectGenerosProximo');
        if (select.style.display === 'none') {
            // Cargar géneros disponibles
            cargarGeneros(select);
            select.style.display = 'block';
        } else {
            select.style.display = 'none';
        }
    });
    
    // Función centralizada para obtener la lista de géneros
    function obtenerGeneros() {
        return ['Acción', 'Aventura', 'Animación', 'Bélica', 'Ciencia ficción', 'Comedia', 'Crimen', 'Documental', 'Drama', 'Familia', 'Fantasía', 'Historia', 'Misterio', 'Música', 'Película de TV', 'Romance', 'Suspenso', 'Terror', 'Western'];
    }
    
    // Función para cargar géneros en un select
    function cargarGeneros(select) {
        const generos = obtenerGeneros();
        select.innerHTML = '<option value="">Seleccionar género...</option>';
        generos.forEach(genero => {
            select.innerHTML += `<option value="${genero}">${genero}</option>`;
        });
    }
    
    // Event listener para seleccionar género en película
    document.getElementById('selectGeneros').addEventListener('change', function() {
        if (this.value) {
            const container = document.getElementById('generosSeleccionados');
            
            // Verificar si el género ya está agregado
            const existingGenres = Array.from(container.children).map(badge => badge.textContent.replace(' ×', ''));
            if (existingGenres.includes(this.value)) {
                alert('Este género ya ha sido agregado');
                this.value = '';
                return;
            }
            
            // Crear badge del género
            const badge = document.createElement('span');
            badge.className = 'badge bg-warning text-dark me-2 mb-2';
            badge.innerHTML = `${this.value} <span style="cursor: pointer;" onclick="this.parentElement.remove()">×</span>`;
            badge.innerHTML += `<input type="hidden" name="generos[]" value="${this.value}">`;
            container.appendChild(badge);
            
            this.value = '';
            this.style.display = 'none';
        }
    });
    
    // Event listener para seleccionar género en próximo estreno
    document.getElementById('selectGenerosProximo').addEventListener('change', function() {
        if (this.value) {
            const container = document.getElementById('generosSeleccionadosProximo');
            
            // Verificar si el género ya está agregado
            const existingGenres = Array.from(container.children).map(badge => badge.textContent.replace(' ×', ''));
            if (existingGenres.includes(this.value)) {
                alert('Este género ya ha sido agregado');
                this.value = '';
                return;
            }
            
            // Crear badge del género
            const badge = document.createElement('span');
            badge.className = 'badge bg-warning text-dark me-2 mb-2';
            badge.innerHTML = `${this.value} <span style="cursor: pointer;" onclick="this.parentElement.remove()">×</span>`;
            badge.innerHTML += `<input type="hidden" name="generos_proximo[]" value="${this.value}">`;
            container.appendChild(badge);
            
            this.value = '';
            this.style.display = 'none';
        }
    });
});
</script>
<script src="resources/index/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<title>Comprar Entradas</title>

<?php
include '../conexion/conexion.php';
include '../resources/header/header.php';

/* Películas en cartelera */
$stmt = $pdo->query("SELECT id, titulo, poster, idioma, clasificacion, genero, duracion, descripcion, valoracion, fecha_estreno, trailer, actores FROM peliculas ORDER BY titulo");
$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Meses en español */
$mesES = [
    1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril',
    5=>'Mayo',  6=>'Junio',   7=>'Julio', 8=>'Agosto',
    9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
];
?>
<link rel="icon" type="image/png" href="../resources/index/img/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../resources/header/css/styles.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="../resources/index/css/styles.css">

<body class="body-comprar">
    

    <div class="peliculas-container">
        <?php foreach ($peliculas as $pelicula): ?>
            <?php
            /* Funciones de la película */
            $stmtFunciones = $pdo->prepare("
                SELECT ph.id AS funcion_id,
                       h.fecha,
                       h.hora_inicio
                FROM pelicula_horario ph
                JOIN horarios h ON ph.horario_id = h.id
                WHERE ph.pelicula_id = ?
                ORDER BY h.fecha, h.hora_inicio
            ");
            $stmtFunciones->execute([$pelicula['id']]);
            $funciones = $stmtFunciones->fetchAll(PDO::FETCH_ASSOC);

            /* Agrupar por fecha ► 2025-07-05, 2025-07-06… */
            $porDia = [];
            foreach ($funciones as $f) {
                $dt = new DateTime($f['fecha']);
                $claveDia = $dt->format('Y-m-d');
                $nombreDia = $dt->format('d') . ' del ' . $mesES[(int)$dt->format('n')] . ' del ' . $dt->format('Y');

                $porDia[$claveDia]['nombre'] = $nombreDia;
                $porDia[$claveDia]['fecha_formateada'] = $dt->format('d') . ' del ' . $mesES[(int)$dt->format('n')] . ' del ' . $dt->format('Y');
                $porDia[$claveDia]['items'][] = [
                    'funcion_id' => $f['funcion_id'],
                    'fecha_formateada' => $dt->format('d') . ' del ' . $mesES[(int)$dt->format('n')],
                    'hora' => substr($f['hora_inicio'], 0, 5)
                ];
            }
            ?>

            <div class="pelicula-card">
                <div class="pelicula-header">
                    <div class="pelicula-poster">
                        <?php if ($pelicula['poster']): ?>
                            <?php if (preg_match('/^https?:\/\//', $pelicula['poster'])): ?>
                                <img src="<?= htmlspecialchars($pelicula['poster']) ?>"
                                     alt="<?= htmlspecialchars($pelicula['titulo']) ?>">
                            <?php else: ?>
                                <img src="<?= '../posters/' . htmlspecialchars($pelicula['poster']) ?>"
                                     alt="<?= htmlspecialchars($pelicula['titulo']) ?>">
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="pelicula-info">
                        <h3 class="pelicula-titulo">
                            <?= htmlspecialchars($pelicula['titulo']) ?>
                            <span class="en-cartelera">En Cartelera</span>
                        </h3>
                        <div class="pelicula-meta">
                            <span class="clasificacion-badge clasificacion-<?= strtolower($pelicula['clasificacion']) ?>"><?= $pelicula['clasificacion'] ?></span>
                            <span class="duracion"><?= $pelicula['duracion'] ?> min 🕒</span>
                        </div>
                        <p><a href="#" class="ver-detalle" data-pelicula='<?= json_encode($pelicula) ?>'>Ver detalles 🎞️</a></p>
                        <p class="idioma-texto"><span class="idioma-label">Idioma:</span> <?= htmlspecialchars(ucfirst(strtolower($pelicula['idioma']))) ?></p>
                    </div>
                    
                    <div class="trailer-container">
                        <?php if (!empty($pelicula['trailer'])): ?>
                            <div class="trailer-video">
                                <?php
                                // Extraer ID del video de YouTube
                                $trailer_url = $pelicula['trailer'];
                                $video_id = '';
                                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $trailer_url, $matches)) {
                                    $video_id = $matches[1];
                                }
                                ?>
                                <?php if ($video_id): ?>
                                    <iframe 
                                        src="https://www.youtube.com/embed/<?= $video_id ?>" 
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                        allowfullscreen>
                                    </iframe>

                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$funciones): ?>
                    <div class="sin-funciones">
                        <p>Sin funciones disponibles</p>
                    </div>
                <?php else: ?>
                    <div class="pelicula-card-divider">
                    <?php foreach ($porDia as $clave => $dia): ?>
                        <div class="dia-header">
                            <span class="dia-fecha"><?= $dia['nombre'] ?></span>
                        </div>
                        <div class="horarios-container">
                            <?php foreach ($dia['items'] as $func): ?>
                                <form method="POST" action="../asiento/asiento.php" style="display: inline-block;">
                                    <input type="hidden" name="funcion_id" value="<?= $func['funcion_id'] ?>">
                                    <button type="submit" class="horario-btn">
                                        <img src="../resources/index/img/chair.png" class="horario-icon" alt="Asiento">
                                        <?php
                                        // Convert 24-hour time format to 12-hour AM/PM format
                                        $time24 = $func['hora'];
                                        $timestamp = strtotime($time24);
                                        echo date('g:i a', $timestamp);
                                        ?>
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Panel lateral de información de película -->
<div class="overlay-panel-pelicula" id="overlayPanelPelicula"></div>
<div class="panel-pelicula" id="panelPelicula">
    <button class="cerrar-panel" onclick="cerrarPanelPelicula()">&times;</button>
    
    <div class="contenido-panel">
        <div class="titulo-container">
            <h2 class="titulo-panel" id="tituloPanel"></h2>
            <span class="en-cartelera" id="enCarteleraPanel">En Cartelera</span>
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
        
    </div>
</div>
</body>

<script src="js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
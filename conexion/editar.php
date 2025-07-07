<?php
// Incluir la configuración unificada
include_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['id']) || isset($_POST['id_editar']))) {
    // Obtener ID desde cualquiera de los dos campos posibles
    $id = isset($_POST['id_editar']) ? $_POST['id_editar'] : $_POST['id'];
    
    $titulo = $_POST['titulo'];
    $idioma = $_POST['idioma'];
    $clasificacion = $_POST['clasificacion'];
    $valoracion = $_POST['valoracion'];
    $descripcion = $_POST['descripcion'];
    $fecha_estreno = $_POST['fecha_estreno'];
    $trailer = $_POST['trailer'];
    
    // === Manejo del póster ===
    // Si hay una nueva URL, usarla; si no, usar el valor anterior
    if (isset($_POST['poster_url']) && trim($_POST['poster_url']) !== '') {
        $poster_nombre = trim($_POST['poster_url']);
    } else {
        $poster_nombre = isset($_POST['poster_actual']) ? $_POST['poster_actual'] : '';
    }
    
    // Determinar si estamos editando una película normal o un próximo estreno
    $es_proximo = (isset($_POST['tipo_editar']) && $_POST['tipo_editar'] === 'proximo') || 
                  (isset($_GET['tipo']) && $_GET['tipo'] === 'proximo') ||
                  (isset($_POST['es_proximo']) && $_POST['es_proximo'] === '1');
    
    if ($es_proximo) {
        // Gestionar datos para próximos estrenos
        $genero = '';
        if (isset($_POST['generos_proximo']) && is_array($_POST['generos_proximo'])) {
            $generos_filtrados = array_filter(array_map('trim', $_POST['generos_proximo']), function($g) { return $g !== ''; });
            $genero = implode(',', $generos_filtrados);
        }
        
        $horas = isset($_POST['horas_proximo']) ? intval($_POST['horas_proximo']) : 0;
        $minutos = isset($_POST['minutos_proximo']) ? intval($_POST['minutos_proximo']) : 0;
        
        $actores = '';
        if (isset($_POST['actores_proximo']) && is_array($_POST['actores_proximo'])) {
            $actores_filtrados = array_filter(array_map('trim', $_POST['actores_proximo']), function($a) { return $a !== ''; });
            $actores = implode(',', $actores_filtrados);
        }
    } else {
        // Gestionar datos para películas normales
        $genero = isset($_POST['generos']) && is_array($_POST['generos']) ? implode(', ', $_POST['generos']) : '';
        $horas = isset($_POST['horas']) ? intval($_POST['horas']) : 0;
        $minutos = isset($_POST['minutos']) ? intval($_POST['minutos']) : 0;
        $actores = isset($_POST['actores']) && is_array($_POST['actores']) ? implode(', ', $_POST['actores']) : '';
    }
    
    // Calcular duración total en minutos
    $duracion = ($horas * 60) + $minutos;
    
    if ($es_proximo) {
        // === Actualizar próximo estreno ===
        $stmt = $pdo->prepare("UPDATE proximos SET
            titulo = :titulo,
            poster = :poster,
            idioma = :idioma,
            clasificacion = :clasificacion,
            genero = :genero,
            valoracion = :valoracion,
            duracion = :duracion,
            descripcion = :descripcion,
            fecha_estreno = :fecha_estreno,
            trailer = :trailer,
            actores = :actores
        WHERE id = :id");
    } else {
        // === Actualizar película ===
        $stmt = $pdo->prepare("UPDATE peliculas SET
            titulo = :titulo,
            poster = :poster,
            idioma = :idioma,
            clasificacion = :clasificacion,
            genero = :genero,
            valoracion = :valoracion,
            duracion = :duracion,
            descripcion = :descripcion,
            fecha_estreno = :fecha_estreno,
            trailer = :trailer,
            actores = :actores
        WHERE id = :id");
    }

    $stmt->execute([
        'titulo' => $titulo,
        'poster' => $poster_nombre,
        'idioma' => $idioma,
        'clasificacion' => $clasificacion,
        'genero' => $genero,
        'valoracion' => $valoracion,
        'duracion' => $duracion,
        'descripcion' => $descripcion,
        'fecha_estreno' => $fecha_estreno,
        'trailer' => $trailer,
        'actores' => $actores,
        'id' => $id
    ]);

    // Mostrar mensaje diferente según el tipo de elemento editado
    $message = $es_proximo ? 'proximo_updated=1' : 'updated=1';
    header("Location: ../index.php?$message");
    exit;
}

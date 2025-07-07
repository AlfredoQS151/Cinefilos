<?php
$host = "localhost";
$port = "5432";
$dbname = "cinefilos";
$user = "postgres";
$password = "admin";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// === Insertar película o próximo estreno si se envió el formulario ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'])) {
    $titulo = $_POST['titulo'];
    $idioma = $_POST['idioma'];
    $clasificacion = $_POST['clasificacion'];

    // Determinar si es próximo estreno o película normal
    $es_proximo = isset($_POST['es_proximo']) && $_POST['es_proximo'] === '1';

    // Convertir array de géneros a string separado por comas
    if ($es_proximo) {
        $genero = '';
        if (isset($_POST['generos_proximo']) && is_array($_POST['generos_proximo'])) {
            $generos_filtrados = array_filter(array_map('trim', $_POST['generos_proximo']), function($g) { return $g !== ''; });
            $genero = implode(',', $generos_filtrados);
        }
    } else {
        $genero = isset($_POST['generos']) ? implode(', ', $_POST['generos']) : '';
    }

    $valoracion = $_POST['valoracion'];

    // Formatear duración desde selects de horas y minutos
    if ($es_proximo) {
        $horas = isset($_POST['horas_proximo']) ? intval($_POST['horas_proximo']) : 0;
        $minutos = isset($_POST['minutos_proximo']) ? intval($_POST['minutos_proximo']) : 0;
    } else {
        $horas = $_POST['horas'];
        $minutos = $_POST['minutos'];
    }
    $duracion = ($horas * 60) + $minutos;

    $descripcion = $_POST['descripcion'];
    $fecha_estreno = $_POST['fecha_estreno'];
    $trailer = $_POST['trailer'];

    // Convertir array de actores a string separado por comas
    if ($es_proximo) {
        $actores = '';
        if (isset($_POST['actores_proximo']) && is_array($_POST['actores_proximo'])) {
            $actores_filtrados = array_filter(array_map('trim', $_POST['actores_proximo']), function($a) { return $a !== ''; });
            $actores = implode(',', $actores_filtrados);
        }
    } else {
        $actores = isset($_POST['actores']) ? implode(', ', $_POST['actores']) : '';
    }

    // Guardar imagen del póster o usar URL
    $poster_nombre = null;
    if (!empty($_POST['poster_url'])) {
        $poster_nombre = trim($_POST['poster_url']);
    } elseif (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $poster_nombre = time() . '_' . basename($_FILES['poster']['name']);
        $ruta_destino = __DIR__ . '/../posters/' . $poster_nombre;
        move_uploaded_file($_FILES['poster']['tmp_name'], $ruta_destino);
    }

    // Insertar en la tabla correspondiente
    $tabla = $es_proximo ? 'proximos' : 'peliculas';
    $stmt = $pdo->prepare("INSERT INTO $tabla (
        titulo, poster, idioma, clasificacion, genero, valoracion, duracion,
        descripcion, fecha_estreno, trailer, actores
    ) VALUES (
        :titulo, :poster, :idioma, :clasificacion, :genero, :valoracion, :duracion,
        :descripcion, :fecha_estreno, :trailer, :actores
    )");

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
        'actores' => $actores
    ]);

    header("Location: ../index.php?success=1");
    exit;
}

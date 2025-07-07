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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'])) {
    $titulo = $_POST['titulo'];
    $idioma = $_POST['idioma'];
    $clasificacion = $_POST['clasificacion'];
    // Unir géneros, asegurando que nunca sea NULL
    $genero = '';
    if (isset($_POST['genero_proximo']) && is_array($_POST['genero_proximo'])) {
        $generos_filtrados = array_filter(array_map('trim', $_POST['genero_proximo']), function($g) { return $g !== ''; });
        $genero = implode(',', $generos_filtrados);
    }
    $valoracion = $_POST['valoracion'];
    // Calcular duración en minutos
    $horas = isset($_POST['horas_proximo']) ? intval($_POST['horas_proximo']) : 0;
    $minutos = isset($_POST['minutos_proximo']) ? intval($_POST['minutos_proximo']) : 0;
    $duracion = $horas * 60 + $minutos;
    $descripcion = $_POST['descripcion'];
    $fecha_estreno = $_POST['fecha_estreno'];
    $trailer = $_POST['trailer'];
    // Unir actores, asegurando que nunca sea NULL
    $actores = '';
    if (isset($_POST['actores_proximo']) && is_array($_POST['actores_proximo'])) {
        $actores_filtrados = array_filter(array_map('trim', $_POST['actores_proximo']), function($a) { return $a !== ''; });
        $actores = implode(',', $actores_filtrados);
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

    $stmt = $pdo->prepare("INSERT INTO proximos (
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

    header("Location: ../proximos/proximos.php?success=1");
    exit;
}

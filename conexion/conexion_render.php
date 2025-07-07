<?php
// Configuración de base de datos para desarrollo y producción
if (isset($_ENV['DATABASE_URL']) || isset($_SERVER['DATABASE_URL'])) {
    // Configuración para producción (Render)
    $database_url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'];
    $parsed_url = parse_url($database_url);
    
    $host = $parsed_url['host'];
    $port = isset($parsed_url['port']) ? $parsed_url['port'] : 5432;
    $dbname = ltrim($parsed_url['path'], '/');
    $user = $parsed_url['user'];
    $password = $parsed_url['pass'];
} else {
    // Configuración para desarrollo (local)
    $host = "localhost";
    $port = "5432";
    $dbname = "cinefilos";
    $user = "postgres";
    $password = "admin";
}

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener películas guardadas (últimos estrenos)
$stmt = $pdo->query("SELECT * FROM peliculas ORDER BY fecha_estreno DESC LIMIT 8");
$peliculas_guardadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

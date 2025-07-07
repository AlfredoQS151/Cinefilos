<?php
// Configuración para desarrollo local y producción en Render

// Detectar si estamos en Render (producción)
$is_production = isset($_ENV['DATABASE_URL']) || isset($_SERVER['DATABASE_URL']);

if ($is_production) {
    // Configuración para Render (producción)
    $database_url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'];
    $db_parts = parse_url($database_url);
    
    $host = $db_parts['host'];
    $port = $db_parts['port'] ?? 5432;
    $dbname = ltrim($db_parts['path'], '/');
    $user = $db_parts['user'];
    $password = $db_parts['pass'];
} else {
    // Configuración para desarrollo local
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

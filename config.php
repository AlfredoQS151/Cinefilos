<?php
// Configuración para desarrollo local y producción en Render

// Configurar sesiones de manera más robusta
if (session_status() === PHP_SESSION_NONE) {
    // Configurar parámetros de sesión
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    // Iniciar sesión
    session_start();
}

// Detectar si estamos en Render (producción) - mejorado
$is_production = isset($_ENV['DATABASE_URL']) || 
                 isset($_SERVER['DATABASE_URL']) || 
                 isset($_ENV['RENDER']) || 
                 !empty($_ENV['DB_HOST']) ||
                 !empty($_SERVER['DB_HOST']);

if ($is_production) {
    // Configuración para Render (producción)
    if (isset($_ENV['DATABASE_URL']) || isset($_SERVER['DATABASE_URL'])) {
        // Usar DATABASE_URL completa
        $database_url = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'];
        $db_parts = parse_url($database_url);
        
        $host = $db_parts['host'];
        $port = $db_parts['port'] ?? 5432;
        $dbname = ltrim($db_parts['path'], '/');
        $user = $db_parts['user'];
        $password = $db_parts['pass'];
    } else {
        // Usar variables individuales
        $host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'dpg-d1liv8ur433s73dpk4ng-a.oregon-postgres.render.com';
        $port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? 5432;
        $dbname = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'cinefilos';
        $user = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'cinefilos_user';
        $password = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? 'Le1jqHBUg3LFKEB7d7z550QeoMHtAAup';
    }
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

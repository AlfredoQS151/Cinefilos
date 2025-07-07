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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $sexo = isset($_POST['sexo']) ? $_POST['sexo'] : 'Hombre';

    // Verificar si ya existe el correo
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios_normales WHERE correo = :correo");
    $stmt->execute(['correo' => $correo]);
    $existe = $stmt->fetchColumn();

    if ($existe > 0) {
        // Si la petición es AJAX, responder con error específico
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(200);
            header('Content-Type: text/plain');
            echo 'correo ya está registrado';
            exit;
        }
        header("Location: ../registro/registro.php?error=1");
        exit;
    }

    // Hashear contraseña
    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Insertar usuario
    $stmt = $pdo->prepare("INSERT INTO usuarios_normales (nombre, apellido, correo, contrasena, fecha_nacimiento, telefono, sexo)
                           VALUES (:nombre, :apellido, :correo, :contrasena, :fecha_nacimiento, :telefono, :sexo)");

    $stmt->execute([
        'nombre' => $nombre,
        'apellido' => $apellido,
        'correo' => $correo,
        'contrasena' => $contrasena_hash,
        'fecha_nacimiento' => $fecha_nacimiento,
        'telefono' => $telefono,
        'sexo' => $sexo
    ]);

    // Redirigir a login.php con mensaje de éxito
    header("Location: ../login/login.php?registro_exitoso=1");
    exit;
}

// Si la petición es AJAX, responder con error y status 400
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'error';
    exit;
}
header("Location: ../registro/registro.php?error=1");
exit;

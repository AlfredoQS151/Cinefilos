<?php
session_start();

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
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];

    // Intentar como usuario_admin
    $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE correo = :correo");
    $stmt->execute(['correo' => $correo]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($contrasena, $admin['contrasena'])) {
        $_SESSION['usuario_id'] = $admin['id'];
        $_SESSION['nombre'] = $admin['nombre'];
        $_SESSION['correo'] = $admin['correo'];
        $_SESSION['rol'] = 'admin';
        header("Location: ../index.php");
        exit;
    }

    // Intentar como usuario_medium
    $stmt = $pdo->prepare("SELECT * FROM usuarios_medium WHERE correo = :correo");
    $stmt->execute(['correo' => $correo]);
    $medium = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($medium && password_verify($contrasena, $medium['contrasena'])) {
        $_SESSION['usuario_id'] = $medium['id'];
        $_SESSION['nombre'] = $medium['nombre'];
        $_SESSION['correo'] = $medium['correo'];
        $_SESSION['rol'] = 'medium';
        header("Location: ../index.php");
        exit;
    }

    // Buscar en usuarios_normales
    $stmt = $pdo->prepare("SELECT * FROM usuarios_normales WHERE correo = :correo");
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['rol'] = 'normal';
        header("Location: ../index.php");
        exit;
    }

    header("Location: ../login/login.php?error_login=1");
    exit;
}

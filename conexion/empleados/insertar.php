<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion/conexion.php';

function validarNombreApellido($texto) {
    return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $texto);
}

function validarEdad($fechaNacimiento) {
    $hoy = new DateTime();
    $fechaNac = new DateTime($fechaNacimiento);
    $edad = $hoy->diff($fechaNac)->y;
    return $edad >= 18;
}

function validarPassword($password) {
    return strlen($password) >= 8;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    $errores = [];

    // Validaciones
    if (!$nombre || !$apellido || !$fecha_nacimiento || !$correo || !$contrasena) {
        $errores[] = 'Todos los campos son requeridos.';
    }

    if ($nombre && !validarNombreApellido($nombre)) {
        $errores[] = 'El nombre no puede contener números ni caracteres especiales.';
    }

    if ($apellido && !validarNombreApellido($apellido)) {
        $errores[] = 'El apellido no puede contener números ni caracteres especiales.';
    }

    if ($fecha_nacimiento && !validarEdad($fecha_nacimiento)) {
        $errores[] = 'El empleado debe ser mayor de 18 años.';
    }

    if ($contrasena && !validarPassword($contrasena)) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }

    // Validar que no exista el correo
    if (empty($errores)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios_medium WHERE correo = :correo");
        $stmt->execute(['correo' => $correo]);
        if ($stmt->fetch()) {
            $errores[] = 'El correo ya está registrado.';
        }
    }

    if (!empty($errores)) {
        $errorMessage = implode(' ', $errores);
        header("Location: ../../empleados/empleados.php?error=" . urlencode($errorMessage));
        exit();
    }

    // Hashear la contraseña
    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Insertar usuario
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios_medium (nombre, apellido, fecha_nacimiento, correo, contrasena)
                               VALUES (:nombre, :apellido, :fecha_nacimiento, :correo, :contrasena)");
        $stmt->execute([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'fecha_nacimiento' => $fecha_nacimiento,
            'correo' => $correo,
            'contrasena' => $contrasena_hash
        ]);

        header("Location: ../../empleados/empleados.php?insertado=1");
        exit();
    } catch (PDOException $e) {
        header("Location: ../../empleados/empleados.php?error=" . urlencode("Error al insertar empleado: " . $e->getMessage()));
        exit();
    }
}

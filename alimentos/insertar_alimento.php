<?php
session_start();

// Verificar que sea empleado
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'medium') {
    header("Location: ../index.php");
    exit;
}

include '../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $foto = trim($_POST['foto']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $categoria = trim($_POST['categoria']);
    
    // Validaciones
    if (empty($nombre) || empty($foto) || empty($categoria) || $precio < 0) {
        header("Location: alimentos.php?error=datos_incompletos");
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO alimentos (nombre, foto, descripcion, precio, categoria) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $foto, $descripcion, $precio, $categoria]);
        
        header("Location: alimentos.php?success=agregado");
        exit;
    } catch (PDOException $e) {
        header("Location: alimentos.php?error=db_error");
        exit;
    }
} else {
    header("Location: alimentos.php");
    exit;
}
?>

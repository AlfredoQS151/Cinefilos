<?php
session_start();

// Verificar que sea empleado
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'medium') {
    header("Location: ../index.php");
    exit;
}

include '../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    
    if ($id <= 0) {
        header("Location: alimentos.php?error=id_invalido");
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM alimentos WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: alimentos.php?success=eliminado");
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

<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM usuarios_medium WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    header("Location: ../../empleados/empleados.php?eliminado=1");
    exit();
}

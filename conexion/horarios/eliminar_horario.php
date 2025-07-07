<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['pelicula_horario_id'] ?? null;

    if ($id) {
        $pdo->prepare("DELETE FROM pelicula_horario WHERE id = ?")
            ->execute([$id]);
    }

    /*  Mensaje de confirmación  */
    header("Location: ../../horarios/editar_horarios.php?mensaje=Horario eliminado correctamente");
    exit();
}
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}?>

<link rel="stylesheet" href="resources/header/css/styles.css">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<header>
    <nav>
        <div class="nav-left">
            <div class="logo">
                <a href="/index.php">
                    <img src="/resources/index/img/logo.png" alt="Cinéfilos" class="logo-img">
                </a>
            </div>

            <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="welcome-container">
                    <span class="welcome-message">
                        <?php
                            if ($_SESSION['rol'] === 'admin') {
                                echo "Bienvenido administrador " . htmlspecialchars($_SESSION['nombre']);
                            } elseif ($_SESSION['rol'] === 'medium') {
                                echo "Bienvenido empleado " . htmlspecialchars($_SESSION['nombre']);
                            } else {
                                echo "Bienvenido " . htmlspecialchars($_SESSION['nombre']);
                            }
                        ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <div class="menu">
            <?php if (isset($_SESSION['rol'])): ?>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a href="/index.php">Cartelera</a>
                    <a href="/empleados/empleados.php">Empleados</a>
                <?php elseif ($_SESSION['rol'] === 'medium'): ?>
                    <a href="/index.php">Cartelera</a>
                    <a href="/horarios/editar_horarios.php">Editar Horarios</a>
                    <a href="/alimentos/alimentos.php">Alimentos</a>
                <?php else: ?>
                    <a href="/index.php">Inicio</a>
                    <a href="/most_alimentos/mostrar_alimentos.php">Alimentos</a>
                    <a href="/comprar/comprar_entradas.php">Comprar Entradas</a>
                    <a href="/historial_pagos_entradas/historial.php">Historial de Compras</a>
                    <a href="/perfil/perfil.php">Mi perfil</a>
                <?php endif; ?>
                <a href="/login/logout.php" class="logout-link">Cerrar Sesión</a>
            <?php else: ?>
                <a href="/index.php">Inicio</a>
                <a href="/most_alimentos/mostrar_alimentos.php">Alimentos</a>
                <a href="/comprar/comprar_entradas.php">Comprar Entradas</a>
                <a href="/login/login.php">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<div class="contenido">
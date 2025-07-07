<?php
// Incluir configuración de rutas
include_once __DIR__ . '/header_config.php';

// No necesitamos iniciar sesión aquí ya que config.php la maneja
?>

<link rel="stylesheet" href="<?php echo $routes['header_css']; ?>">
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<header>
    <nav>
        <div class="nav-left">
            <div class="logo">
                <a href="<?php echo $routes['index']; ?>">
                    <img src="<?php echo $routes['logo']; ?>" alt="Cinéfilos" class="logo-img">
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
                    <a href="<?php echo $routes['index']; ?>">Cartelera</a>
                    <a href="<?php echo $routes['empleados']; ?>">Empleados</a>
                <?php elseif ($_SESSION['rol'] === 'medium'): ?>
                    <a href="<?php echo $routes['index']; ?>">Cartelera</a>
                    <a href="<?php echo $routes['horarios']; ?>">Editar Horarios</a>
                    <a href="<?php echo $routes['alimentos']; ?>">Alimentos</a>
                <?php else: ?>
                    <a href="<?php echo $routes['index']; ?>">Inicio</a>
                    <a href="<?php echo $routes['mostrar_alimentos']; ?>">Alimentos</a>
                    <a href="<?php echo $routes['comprar']; ?>">Comprar Entradas</a>
                    <a href="<?php echo $routes['historial']; ?>">Historial de Compras</a>
                    <a href="<?php echo $routes['perfil']; ?>">Mi perfil</a>
                <?php endif; ?>
                <a href="<?php echo $routes['logout']; ?>" class="logout-link">Cerrar Sesión</a>
            <?php else: ?>
                <a href="<?php echo $routes['index']; ?>">Inicio</a>
                <a href="<?php echo $routes['mostrar_alimentos']; ?>">Alimentos</a>
                <a href="<?php echo $routes['comprar']; ?>">Comprar Entradas</a>
                <a href="<?php echo $routes['login']; ?>">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<div class="contenido">
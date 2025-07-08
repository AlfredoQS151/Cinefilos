<?php
include '../resources/header/header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../resources/header/css/styles.css">
    <link rel="stylesheet" href="../resources/index/css/styles.css">
</head>
<body class="body-login">
<div class="container" id="container">
    <!-- Registro real -->
    <div class="form-container sign-up-container">
        <form id="registroForm" action="../conexion/registro.php" method="POST">
            <h1>Crear Cuenta</h1>

            <p style="color: #eaf822">Usa tu correo electrónico para registrarte</p>

            <div class="iconos">
            <img src="../resources/index/img/name.png" alt="Correo" class="iconos-logos">
            <input type="text" placeholder="Nombre" name="nombre" class="entradas" required />
            </div>

            <div class="iconos">
            <img src="../resources/index/img/last_name.png" alt="Correo" class="iconos-logos">
            <input type="text" placeholder="Apellido" name="apellido" class="entradas" required />
            </div>

            <div class="iconos">
            <img src="../resources/index/img/email.png" alt="Correo" class="iconos-logos">
            <input type="email" placeholder="Correo electrónico" name="correo" class="entradas" required />
            </div>

            <div class="iconos">
            <img src="../resources/index/img/password.png" alt="Correo" class="iconos-logos">
            <input type="password" placeholder="Contraseña" name="contrasena" class="entradas" required />
            </div>

            <div class="iconos">
            <img src="../resources/index/img/repeat_password.png" alt="Correo" class="iconos-logos">
            <input type="password" placeholder="Repite tu contraseña" name="repite_contrasena" class="entradas" required />
            </div>

            <div class="iconos">
            <img src="../resources/index/img/phone.png" alt="Correo" class="iconos-logos">
            <input type="tel" placeholder="Teléfono" name="telefono" pattern="[0-9]{10}" title="Ingresa un número de 10 dígitos" class="entradas" required />
            </div>

            <div class="iconos">
            <img src="../resources/index/img/date.png" alt="Correo" class="iconos-logos">
            <input type="date" placeholder="Fecha de nacimiento" name="fecha_nacimiento" class="entradas" required />
            </div>

            <div class="iconos">
            <img src="../resources/index/img/genre.png" alt="Sexo" class="iconos-logos">
            <select name="sexo" required class="entradas">
                <option value="">Selecciona tu sexo</option>
                <option value="Hombre">Hombre</option>
                <option value="Mujer">Mujer</option>
            </select>
            </div>

            <br>
            <!-- Contenedor para mensajes de error -->
            <div id="mensajesError" style="display: none; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px 20px; border-radius: 10px; margin: 10px 0; font-size: 14px; font-family: 'Roboto', sans-serif;">
            </div>
            
            <button type="submit">Registrarse</button>
            <?php if (isset($_GET['error'])): ?>
                <p style="color:red; margin-top:10px;">Error: el correo ya está registrado. Intenta con otro.</p>
            <?php endif; ?>
        </form>
    </div>
    <!-- Login real -->
    <div class="form-container sign-in-container">
        
        <form action="../conexion/login.php" method="POST">

            <h1>Inicia Sesión</h1>
            <p style="color: #eaf822">Usa tu correo para iniciar sesión</p>

            <?php if (isset($_GET['registro_exitoso'])): ?>
                <div style="background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:10px 20px; border-radius:10px; margin-bottom:15px; font-size:15px;">
                    ¡Registro exitoso! Ahora puedes iniciar sesión.
                </div>
            <?php endif; ?>

            <div class="iconos">
                <img src="../resources/index/img/email.png" alt="Correo" class="iconos-logos">
                <input type="email" placeholder="Correo electrónico" name="correo" class="entradas" required />
            </div>

            <div class="iconos">
                <img src="../resources/index/img/password.png" alt="Correo" class="iconos-logos">
                <input type="password" placeholder="Contraseña" name="contrasena" class="entradas" required />
            </div>

            <br>
            <button type="submit">Iniciar Sesión</button>
            <?php if (isset($_GET['error_login'])): ?>
                <div style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:10px 20px; border-radius:10px; margin-top:15px; font-size:15px; font-family:'Roboto', sans-serif;">
                    Correo o contraseña incorrectos. Intenta nuevamente.
                </div>
            <?php endif; ?>
        </form>
    </div>
    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Bienvenido de Nuevo!</h1>
                <p class="p-menu">Conéctate con tu información personal para iniciar sesión</p>
                <button class="ghost" id="signIn">Inicia Sesión</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>Hola Amigo!</h1>
                <p class="p-menu">Introduce tus datos personales para unirte a Cinefilos</p>
                <button class="ghost" id="signUp">Regístrate</button>
            </div>
        </div>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>

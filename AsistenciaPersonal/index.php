<?php
// Iniciamos la sesión para poder verificar si el usuario ya está logueado
session_start();

// Incluimos el archivo de conexión a la base de datos
// Esto permite que, si en el futuro se necesita usar la base de datos aquí,
// ya esté disponible la conexión
require_once __DIR__ . "/Database/connection.php";

// Si el usuario ya tiene una sesión activa, significa que ya inició sesión
// En ese caso lo redirigimos directamente a la página principal de asistencia
if (isset($_SESSION["usuario_id"])) {
    header("Location: asistencia/asistencia.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <!-- Título de la página que aparece en la pestaña del navegador -->
    <title>Asistencia Personal</title>

    <!-- Archivo CSS que contiene los estilos del sistema -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="contenedor">

    <!-- Título principal del sistema -->
    <h1>Asistencia Personal</h1>

    <!-- Mensaje de bienvenida -->
    <p>¡Bienvenido! <br> Inicia sesión o crea tu cuenta.</p>

    <!-- Enlace que lleva al archivo de inicio de sesión -->
    <a href="auth/login.php">Iniciar sesión</a>

    <br>

    <!-- Enlace que lleva al archivo de registro de nuevos usuarios -->
    <a href="auth/register.php">Crear cuenta</a>

</div>

</body>
</html>

<!--
Este archivo es la página principal del sistema.

Aquí se muestra la pantalla de bienvenida con las opciones
de iniciar sesión o crear una cuenta.

Primero se inicia la sesión de PHP y se verifica si el usuario
ya está logueado. Si existe una sesión activa, el sistema
redirige automáticamente a la página principal de asistencia.

Si no hay sesión, el usuario puede elegir entre iniciar sesión
o registrarse en el sistema.
-->
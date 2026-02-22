<?php
session_start();
require_once __DIR__ . "/Database/connection.php";

if (isset($_SESSION["usuario_id"])) {
    header("Location: asistencia/asistencia.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Asistencia Personal</title>
</head>
<body>
  <h1>Asistencia Personal</h1>
  <p>Bienvenido. Inicia sesión o crea tu cuenta.</p>

  <a href="auth/login.php">Iniciar sesión</a>
  <br><br>
  <a href="auth/register.php">Crear cuenta</a>
</body>
</html>
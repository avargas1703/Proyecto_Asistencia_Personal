<?php
// Iniciamos la sesión para poder usar los datos del usuario que inició sesión
session_start();

// Traemos la conexión a la base de datos
require_once __DIR__ . "/../Database/connection.php";

// Validamos que exista una sesión activa.
// Si el usuario no está logueado, no puede ver el historial.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

// Guardamos los datos del usuario que vienen desde la sesión
$usuario_id = $_SESSION["usuario_id"];
$correo = $_SESSION["correo"] ?? "";

// Traer el historial del usuario
// Aquí consultamos todas las asistencias que tiene este usuario
// y las ordenamos por fecha descendente (las más recientes primero)
$stmt = $pdo->prepare("
    SELECT fecha, hora_entrada, hora_salida
    FROM asistencias
    WHERE usuario_id = ?
    ORDER BY fecha DESC
");

$stmt->execute([$usuario_id]);

// fetchAll obtiene todos los registros encontrados en forma de arreglo
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de asistencia</title>

  <!-- Archivo CSS del sistema -->
  <link rel="stylesheet" href="../style.css">
</head>

<body>
<div class="contenedor ancho">

  <h1>Historial de asistencia</h1>

  <!-- Mostramos el correo del usuario -->
  <p><strong>Usuario:</strong> <?php echo htmlspecialchars($correo); ?></p>

  <!-- Tabla donde se mostrará el historial -->
  <table border="1" cellpadding="8" cellspacing="0">

    <thead>
      <tr>
        <th>Fecha</th>
        <th>Hora entrada</th>
        <th>Hora salida</th>
        <th>Estado</th>
      </tr>
    </thead>

    <tbody>

      <!-- Si no hay registros todavía -->
      <?php if (count($historial) === 0): ?>

        <tr>
          <td colspan="4">No hay registros todavía.</td>
        </tr>

      <?php else: ?>

        <!-- Recorremos cada registro del historial -->
        <?php foreach ($historial as $fila): ?>

          <?php
            // Guardamos los datos de cada fila
            $entrada = $fila["hora_entrada"];
            $salida  = $fila["hora_salida"];

            // Calculamos el estado de la asistencia según los datos
            if ($entrada && $salida) {
                $estado = "Completa";
            } elseif ($entrada && !$salida) {
                $estado = "Entrada marcada (falta salida)";
            } else {
                $estado = "Sin entrada";
            }
          ?>

          <tr>

            <!-- Mostramos la fecha -->
            <td><?php echo htmlspecialchars($fila["fecha"]); ?></td>

            <!-- Mostramos la hora de entrada si existe -->
            <td><?php echo $entrada ? htmlspecialchars($entrada) : "—"; ?></td>

            <!-- Mostramos la hora de salida si existe -->
            <td><?php echo $salida ? htmlspecialchars($salida) : "—"; ?></td>

            <!-- Mostramos el estado calculado -->
            <td><?php echo htmlspecialchars($estado); ?></td>

          </tr>

        <?php endforeach; ?>

      <?php endif; ?>

    </tbody>

  </table>

  <hr>

  <!-- Enlaces de navegación -->
  <a href="asistencia.php">Volver</a> |
  <a href="../auth/logout.php">Cerrar sesión</a>

</div>
</body>
</html>

<!--
Este archivo muestra el historial de asistencias del usuario.

Primero valida que el usuario tenga una sesión activa para
evitar accesos sin autenticación.

Luego consulta en la base de datos todas las asistencias
registradas para ese usuario y las ordena por fecha
de la más reciente a la más antigua.

Después recorre cada registro y calcula el estado
de la asistencia según los datos guardados:

- Si hay entrada y salida -> asistencia completa
- Si solo hay entrada -> falta marcar salida
- Si no hay entrada -> sin registro

Finalmente muestra toda la información en una tabla
para que el usuario pueda ver su historial de asistencia.
-->
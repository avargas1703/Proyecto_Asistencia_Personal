<?php
session_start();
require_once __DIR__ . "/../Database/connection.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$correo = $_SESSION["correo"] ?? "";

// Traer el historial del usuario
$stmt = $pdo->prepare("
    SELECT fecha, hora_entrada, hora_salida
    FROM asistencias
    WHERE usuario_id = ?
    ORDER BY fecha DESC
");
$stmt->execute([$usuario_id]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de asistencia</title>
</head>
<body>
  <h1>Historial de asistencia</h1>

  <p><strong>Usuario:</strong> <?php echo htmlspecialchars($correo); ?></p>

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
      <?php if (count($historial) === 0): ?>
        <tr>
          <td colspan="4">No hay registros todavía.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($historial as $fila): ?>
          <?php
            $entrada = $fila["hora_entrada"];
            $salida  = $fila["hora_salida"];

            if ($entrada && $salida) {
                $estado = "Completa";
            } elseif ($entrada && !$salida) {
                $estado = "Entrada marcada (falta salida)";
            } else {
                $estado = "Sin entrada";
            }
          ?>
          <tr>
            <td><?php echo htmlspecialchars($fila["fecha"]); ?></td>
            <td><?php echo $entrada ? htmlspecialchars($entrada) : "—"; ?></td>
            <td><?php echo $salida ? htmlspecialchars($salida) : "—"; ?></td>
            <td><?php echo htmlspecialchars($estado); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <hr>
  <a href="asistencia.php">Volver</a> |
  <a href="../auth/logout.php">Cerrar sesión</a>
</body>
</html>
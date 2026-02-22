<?php
session_start();
require_once __DIR__ . "/../Database/connection.php";

// 1) Validar sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$correo = $_SESSION["correo"] ?? "";
$hoy = date("Y-m-d"); // fecha del servidor

$mensaje = "";

// 2) Si el usuario presiona un botón hace un POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    // Buscar el registro de hoy
    $stmt = $pdo->prepare("SELECT * FROM asistencias WHERE usuario_id = ? AND fecha = ?");
    $stmt->execute([$usuario_id, $hoy]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($accion === "entrada") {

        if (!$registro) {
            // No existe registro hoy -> crear con hora_entrada
            $stmtIns = $pdo->prepare("INSERT INTO asistencias (usuario_id, fecha, hora_entrada) VALUES (?, ?, CURTIME())");
            $stmtIns->execute([$usuario_id, $hoy]);
            $mensaje = "✅ Entrada registrada correctamente.";
        } else {
            if (!empty($registro["hora_entrada"])) {
                $mensaje = "⚠️ Ya tienes una entrada registrada hoy.";
            } else {
                // Caso raro (no me paso): existe fila pero sin entrada
                $stmtUp = $pdo->prepare("UPDATE asistencias SET hora_entrada = CURTIME() WHERE id = ?");
                $stmtUp->execute([$registro["id"]]);
                $mensaje = "✅ Entrada registrada correctamente.";
            }
        }

    } elseif ($accion === "salida") {

        if (!$registro || empty($registro["hora_entrada"])) {
            $mensaje = "❌ No puedes marcar salida sin haber marcado entrada.";
        } else {
            if (!empty($registro["hora_salida"])) {
                $mensaje = "⚠️ Ya tienes una salida registrada hoy.";
            } else {
                $stmtUp = $pdo->prepare("UPDATE asistencias SET hora_salida = CURTIME() WHERE id = ?");
                $stmtUp->execute([$registro["id"]]);
                $mensaje = "✅ Salida registrada correctamente.";
            }
        }
    }
}

// 3) Volver a consultar el estado del dia para mostrarlo actualizado
$stmt = $pdo->prepare("SELECT * FROM asistencias WHERE usuario_id = ? AND fecha = ?");
$stmt->execute([$usuario_id, $hoy]);
$registroHoy = $stmt->fetch(PDO::FETCH_ASSOC);

// 4) Definir el estado y habilitar/deshabilitar botones
$estado = "No se ha marcado entrada todavía.";
$entradaHabilitada = true;
$salidaHabilitada = false;

$horaEntrada = $registroHoy["hora_entrada"] ?? null;
$horaSalida  = $registroHoy["hora_salida"] ?? null;

if ($registroHoy) {
    if ($horaEntrada && !$horaSalida) {
        $estado = "Entrada registrada. Falta marcar salida.";
        $entradaHabilitada = false;
        $salidaHabilitada = true;
    } elseif ($horaEntrada && $horaSalida) {
        $estado = "Asistencia completada por hoy ✅";
        $entradaHabilitada = false;
        $salidaHabilitada = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Asistencia de hoy</title>
</head>
<body>
  <h1>Asistencia de hoy</h1>

  <p><strong>Usuario:</strong> <?php echo htmlspecialchars($correo); ?></p>
  <p><strong>Fecha:</strong> <?php echo htmlspecialchars($hoy); ?></p>

  <?php if ($mensaje !== ""): ?>
    <p><strong><?php echo htmlspecialchars($mensaje); ?></strong></p>
  <?php endif; ?>

  <p><strong>Estado:</strong> <?php echo htmlspecialchars($estado); ?></p>

  <p>
    <strong>Hora entrada:</strong>
    <?php echo $horaEntrada ? htmlspecialchars($horaEntrada) : "—"; ?>
    <br>
    <strong>Hora salida:</strong>
    <?php echo $horaSalida ? htmlspecialchars($horaSalida) : "—"; ?>
  </p>

  <form method="POST" style="display:inline;">
    <input type="hidden" name="accion" value="entrada">
    <button type="submit" <?php echo $entradaHabilitada ? "" : "disabled"; ?>>
      Marcar entrada
    </button>
  </form>

  <form method="POST" style="display:inline;">
    <input type="hidden" name="accion" value="salida">
    <button type="submit" <?php echo $salidaHabilitada ? "" : "disabled"; ?>>
      Marcar salida
    </button>
  </form>

  <hr>

  <a href="historial.php">Ver historial</a> |
  <a href="../auth/logout.php">Cerrar sesión</a>
</body>
</html>
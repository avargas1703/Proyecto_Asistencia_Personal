<?php
// Iniciamos la sesión para poder usar los datos del usuario que ya inició sesión
session_start();

// Traemos la conexión a la base de datos
require_once __DIR__ . "/../Database/connection.php";

// 1) Validar sesión
// Aquí revisamos si el usuario realmente está logueado.
// Si no existe la sesión, no puede entrar a esta pantalla.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

// Guardamos en variables los datos de sesión que vamos a ocupar
$usuario_id = $_SESSION["usuario_id"];
$correo = $_SESSION["correo"] ?? "";
$nombre = $_SESSION["nombre"] ?? "";

// Tomamos la fecha actual del servidor para trabajar la asistencia del día
$hoy = date("Y-m-d");

$mensaje = "";

// 2) Si el usuario presiona un botón hace un POST
// Esta parte se ejecuta cuando el usuario da click en "Marcar entrada" o "Marcar salida"
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Capturamos la acción enviada desde el formulario oculto
    $accion = $_POST["accion"] ?? "";

    // Buscamos si ya existe un registro de asistencia para este usuario en el día de hoy
    $stmt = $pdo->prepare("SELECT * FROM asistencias WHERE usuario_id = ? AND fecha = ?");
    $stmt->execute([$usuario_id, $hoy]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si la acción enviada fue "entrada"
    if ($accion === "entrada") {

        // Si no existe un registro hoy, creamos uno nuevo con la hora de entrada actual
        if (!$registro) {
            $stmtIns = $pdo->prepare("INSERT INTO asistencias (usuario_id, fecha, hora_entrada) VALUES (?, ?, CURTIME())");
            $stmtIns->execute([$usuario_id, $hoy]);
            $mensaje = "✅ Entrada registrada correctamente.";

        } else {
            // Si ya existe un registro y ya tiene hora de entrada, no se permite volver a marcar entrada
            if (!empty($registro["hora_entrada"])) {
                $mensaje = "⚠️ Ya tienes una entrada registrada hoy.";
            } else {
                // Este sería un caso poco común:
                // existe el registro del día pero todavía no tiene hora de entrada
                $stmtUp = $pdo->prepare("UPDATE asistencias SET hora_entrada = CURTIME() WHERE id = ?");
                $stmtUp->execute([$registro["id"]]);
                $mensaje = "✅ Entrada registrada correctamente.";
            }
        }

    // Si la acción enviada fue "salida"
    } elseif ($accion === "salida") {

        // No se puede marcar salida si no existe registro o si no hay entrada primero
        if (!$registro || empty($registro["hora_entrada"])) {
            $mensaje = "❌ No puedes marcar salida sin haber marcado entrada.";
        } else {
            // Si ya existe una hora de salida, no se permite volver a marcar
            if (!empty($registro["hora_salida"])) {
                $mensaje = "⚠️ Ya tienes una salida registrada hoy.";
            } else {
                // Si sí hay entrada pero todavía no hay salida, actualizamos la hora de salida
                $stmtUp = $pdo->prepare("UPDATE asistencias SET hora_salida = CURTIME() WHERE id = ?");
                $stmtUp->execute([$registro["id"]]);
                $mensaje = "✅ Salida registrada correctamente.";
            }
        }
    }
}

// 3) Volver a consultar el estado del día para mostrarlo actualizado
// Esto se hace después de marcar entrada o salida para que la pantalla muestre la información nueva
$stmt = $pdo->prepare("SELECT * FROM asistencias WHERE usuario_id = ? AND fecha = ?");
$stmt->execute([$usuario_id, $hoy]);
$registroHoy = $stmt->fetch(PDO::FETCH_ASSOC);

// 4) Definir el estado y habilitar/deshabilitar botones
// Aquí preparamos el mensaje de estado y decidimos si los botones se pueden usar o no
$estado = "No se ha marcado entrada todavía.";
$entradaHabilitada = true;
$salidaHabilitada = false;

// Guardamos en variables la hora de entrada y salida del registro del día
$horaEntrada = $registroHoy["hora_entrada"] ?? null;
$horaSalida  = $registroHoy["hora_salida"] ?? null;

// Según el estado del registro, cambiamos el mensaje y activamos o desactivamos botones
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

  <!-- Archivo CSS general del sistema -->
  <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="contenedor">
        <h1>Asistencia de hoy</h1>

        <!-- Mostramos el correo del usuario logueado -->
        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($nombre); ?></p>

        <!-- Mostramos la fecha actual -->
        <p><strong>Fecha:</strong> <?php echo htmlspecialchars($hoy); ?></p>

        <!-- Si existe un mensaje, lo mostramos en pantalla -->
        <?php if ($mensaje !== ""): ?>
            <p><strong><?php echo htmlspecialchars($mensaje); ?></strong></p>
        <?php endif; ?>

        <!-- Mostramos el estado actual del día -->
        <p><strong>Estado:</strong> <?php echo htmlspecialchars($estado); ?></p>

        <!-- Mostramos la hora de entrada y salida si existen -->
        <p>
            <strong>Hora entrada:</strong>
            <?php echo $horaEntrada ? htmlspecialchars($horaEntrada) : "—"; ?>
            <br>
            <strong>Hora salida:</strong>
            <?php echo $horaSalida ? htmlspecialchars($horaSalida) : "—"; ?>
        </p>

        <div style="display: flex; gap: 10px;">

            <!-- Formulario para marcar entrada -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="accion" value="entrada">
                <button type="submit" <?php echo $entradaHabilitada ? "" : "disabled"; ?>>
                    Marcar entrada
                </button>
            </form>

            <!-- Formulario para marcar salida -->
            <form method="POST" style="display:inline;">
                <input type="hidden" name="accion" value="salida">
                <button type="submit" <?php echo $salidaHabilitada ? "" : "disabled"; ?>>
                    Marcar salida
                </button>
            </form>
        </div>

        <hr>

        <!-- Enlaces de navegación -->
        <a href="historial.php">Ver historial</a> |
        <a href="../auth/logout.php">Cerrar sesión</a>
    </div>
</body>
</html>

<!--
Este archivo controla la asistencia diaria del usuario.

Primero valida que exista una sesión activa para que solo los
usuarios logueados puedan entrar.

Luego revisa si el usuario presionó el botón de marcar entrada
o marcar salida. Según la acción, consulta si ya existe un
registro para el día actual en la base de datos.

Si no existe registro y se marca entrada, crea una nueva fila
con la hora actual. Si ya existe entrada, no permite repetirla.

Si se intenta marcar salida, primero revisa que la entrada ya
haya sido registrada. Si la salida todavía no existe, la guarda
en el mismo registro del día.

Después vuelve a consultar la asistencia del día para mostrar
el estado actualizado, las horas registradas y habilitar o
deshabilitar los botones según corresponda.
-->
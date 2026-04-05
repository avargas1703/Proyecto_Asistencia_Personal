<?php
// Iniciamos la sesión para poder guardar los datos del usuario una vez que entre al sistema
session_start();

// Indicamos que la respuesta de este archivo va a ser en formato JSON
// Esto se hace porque este archivo responde a un fetch de JavaScript
header("Content-Type: application/json");

// Traemos la conexión a la base de datos
require_once __DIR__ . "/../Database/connection.php";

// Aquí recibimos los datos que vienen por POST desde login.php o register.php
// Si alguno no viene, le ponemos un valor por defecto
$accion = $_POST["accion"] ?? "";
$uid    = $_POST["uid"] ?? "";
$correo = $_POST["correo"] ?? "";
$nombre = $_POST["nombre"] ?? null;

// Validamos que al menos vengan los datos más importantes
// Sin uid y sin correo no podemos identificar al usuario
if ($uid === "" || $correo === "") {
    echo json_encode(["ok" => false, "error" => "Datos incompletos."]);
    exit;
}

try {
    // Buscamos en MySQL si ya existe un usuario con ese firebase_uid
    // Aquí usamos prepare y execute para hacer la consulta de forma segura
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE firebase_uid = ?");
    $stmt->execute([$uid]);

    // Si encuentra un resultado, lo guardamos en la variable $usuario
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el usuario NO existe en MySQL, lo creamos
    if (!$usuario) {
        // Esto puede pasar cuando alguien se registra por primera vez
        // o también si inició sesión con Firebase pero todavía no estaba guardado en nuestra base
        $stmtIns = $pdo->prepare("INSERT INTO usuarios (firebase_uid, correo, nombre) VALUES (?, ?, ?)");
        $stmtIns->execute([$uid, $correo, $nombre]);

        // Guardamos el id que MySQL le asignó a ese nuevo usuario
        $usuario_id = $pdo->lastInsertId();

    } else {
        // Si el usuario sí existe, tomamos su id para usarlo en la sesión
        $usuario_id = $usuario["id"];

        // Si viene el nombre y antes estaba vacío, lo actualizamos
        // Esta parte es opcional, pero sirve por si el usuario se registró primero sin nombre
        if ($nombre !== null && $nombre !== "") {
            $stmtUp = $pdo->prepare("UPDATE usuarios SET nombre = COALESCE(NULLIF(nombre,''), ?) WHERE id = ?");
            $stmtUp->execute([$nombre, $usuario_id]);
        }
    }
    
    $stmtUser = $pdo->prepare("SELECT nombre, correo FROM usuarios WHERE id = ?");
    $stmtUser->execute([$usuario_id]);
    $datos = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Aquí creamos la sesión del usuario en PHP
    // Esto es lo que permite que el sistema recuerde que el usuario ya inició sesión
    $_SESSION["usuario_id"] = $usuario_id;
    $_SESSION["firebase_uid"] = $uid;
    $_SESSION["correo"] = $datos["correo"];
    $_SESSION["nombre"] = $datos["nombre"];

    // Respondemos a JavaScript indicando que todo salió bien
    echo json_encode(["ok" => true]);

} catch (Exception $e) {
    // Si ocurre algún error en el proceso, devolvemos el mensaje en formato JSON
    echo json_encode(["ok" => false, "error" => $e->getMessage()]);
}

/*
Este archivo PHP funciona como puente entre Firebase, PHP y MySQL.Cuando el usuario se autentica en Firebase,
este archivo recibe por POST el uid, el correo y opcionalmente el nombre.

Luego revisa si el usuario ya existe en la tabla usuarios. Si no existe, lo crea;
si ya existe, reutiliza ese registro. Después guarda los datos principales del usuario en la sesión de PHP,
para que el sistema lo reconozca como usuario activo.
*/
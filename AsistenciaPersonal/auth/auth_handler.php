<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/../Database/connection.php";

$accion = $_POST["accion"] ?? "";
$uid    = $_POST["uid"] ?? "";
$correo = $_POST["correo"] ?? "";
$nombre = $_POST["nombre"] ?? null;

if ($uid === "" || $correo === "") {
    echo json_encode(["ok" => false, "error" => "Datos incompletos."]);
    exit;
}

try {
    // Buscar si el usuario ya existe por el firebase_uid
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE firebase_uid = ?");
    $stmt->execute([$uid]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        // Si no existe lo creamos (aplica para register o login por primera vez)
        $stmtIns = $pdo->prepare("INSERT INTO usuarios (firebase_uid, correo, nombre) VALUES (?, ?, ?)");
        $stmtIns->execute([$uid, $correo, $nombre]);
        $usuario_id = $pdo->lastInsertId();
    } else {
        $usuario_id = $usuario["id"];

        // Si viene nombre y antes estaba vacío, lo podemos actualizar (opcional)
        if ($nombre !== null && $nombre !== "") {
            $stmtUp = $pdo->prepare("UPDATE usuarios SET nombre = COALESCE(NULLIF(nombre,''), ?) WHERE id = ?");
            $stmtUp->execute([$nombre, $usuario_id]);
        }
    }

    // Crear sesión
    $_SESSION["usuario_id"] = $usuario_id;
    $_SESSION["firebase_uid"] = $uid;
    $_SESSION["correo"] = $correo;

    echo json_encode(["ok" => true]);

} catch (Exception $e) {
    echo json_encode(["ok" => false, "error" => $e->getMessage()]);
}
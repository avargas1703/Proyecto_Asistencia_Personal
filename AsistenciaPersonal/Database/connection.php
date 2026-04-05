<?php

// Datos de conexión a la base de datos
// Aquí definimos el servidor, el nombre de la base de datos,
// el usuario y la contraseña que se utilizarán para conectarse.

$host = "localhost";
$dbname = "asistencia_personal";
$user = "root";
$pass = "123456";

try {

    // Creamos una nueva conexión usando PDO
    // PDO es una interfaz de PHP que permite conectarse a diferentes bases de datos
    // En este caso se utiliza para conectarse a MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

    // Esta línea configura el modo de errores de PDO
    // Si ocurre un error en una consulta, PDO lanzará una excepción
    // Esto facilita detectar problemas durante la ejecución del sistema
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    // Si ocurre un error en la conexión, se muestra el mensaje de error
    // y el sistema se detiene
    die("Error de conexión: " . $e->getMessage());
}

/*
Este archivo se encarga de crear la conexión con la base de datos MySQL.

Aquí se definen los datos de conexión como el servidor, el nombre
de la base de datos, el usuario y la contraseña.

Luego se crea la conexión utilizando PDO, que es una forma segura
de trabajar con bases de datos en PHP.

La conexión se coloca dentro de un bloque try-catch para poder
capturar errores en caso de que la base de datos no esté disponible
o exista un problema con las credenciales.
*/
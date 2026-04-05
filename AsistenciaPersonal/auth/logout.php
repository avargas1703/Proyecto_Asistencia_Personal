<?php

// Iniciamos la sesión para poder acceder a las variables de sesión existentes
session_start();

// session_destroy elimina completamente la sesión actual del usuario.
// Esto borra todas las variables almacenadas en $_SESSION
// y hace que el sistema deje de reconocer al usuario como autenticado.
session_destroy();

// Después de cerrar la sesión, redirigimos al usuario
// a la página principal del sistema (index.php)
header("Location: ../index.php");

// exit se usa para detener la ejecución del script inmediatamente
exit;

/*
Este archivo se encarga de cerrar la sesión del usuario.

Primero inicia la sesión actual para poder acceder a las
variables de sesión. Luego utiliza session_destroy()
para eliminar completamente la sesión activa.

Después redirige al usuario a la página principal
(index.php), lo que obliga a iniciar sesión nuevamente
si desea volver a usar el sistema.
*/
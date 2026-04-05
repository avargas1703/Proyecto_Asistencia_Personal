<?php
// Iniciamos la sesión para poder verificar si el usuario ya está autenticado
session_start();

// Si ya existe una sesión activa significa que el usuario ya inició sesión
// En ese caso no tiene sentido mostrar la pantalla de registro
if (isset($_SESSION["usuario_id"])) {
    // Redirigimos directamente a la página principal de asistencia
    header("Location: ../asistencia/asistencia.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Crear cuenta</title>

        <!-- Cargamos el archivo CSS general del proyecto -->
        <link rel="stylesheet" href="../style.css">
    </head>

    <body>
        <div class="contenedor">
            <h1>Crear cuenta</h1>

            <!--
            Formulario para registrar un nuevo usuario en el sistema.
            Aquí el usuario ingresa su nombre, correo y contraseña.
            -->
            <form id="formRegistro">

                <label>Nombre</label><br>
                <input type="text" id="nombre"><br><br>

                <label>Correo</label><br>
                <input type="email" id="correo" required><br><br>

                <label>Contraseña</label><br>
                <input type="password" id="password" required><br><br>

                <button type="submit">Crear cuenta</button>
            </form>

            <!-- Aquí se mostrarán mensajes de error o estado -->
            <p id="mensaje"></p>

            <!-- Enlace para ir a la pantalla de login si el usuario ya tiene cuenta -->
            <a href="login.php">¿Ya tenés cuenta? Iniciar sesión</a>


            <!-- Firebase SDK -->
            <script type="module">

                // 1) Importamos las funciones necesarias de Firebase
                import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-app.js";
                import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-auth.js";


                // 2) Configuración del proyecto de Firebase
                const firebaseConfig = {
                    apiKey: "AIzaSyCY_gZtET_Q3vLkosyoo61GQpIJYhV4qNc",
                    authDomain: "asistenciapersonal-fdb78.firebaseapp.com",
                    projectId: "asistenciapersonal-fdb78",
                    storageBucket: "asistenciapersonal-fdb78.firebasestorage.app",
                    messagingSenderId: "626440617732",
                    appId: "1:626440617732:web:1fb4be1e1a41ec447c9a51"
                };

                // 3) Inicializamos Firebase y el sistema de autenticación
                const app = initializeApp(firebaseConfig);
                const auth = getAuth(app);

                // 4) Capturamos el formulario de registro
                const form = document.getElementById("formRegistro");

                // Elemento donde se mostrarán los mensajes al usuario
                const mensaje = document.getElementById("mensaje");


                // Evento que se ejecuta cuando el usuario envía el formulario
                form.addEventListener("submit", async (e) => {

                    // Evita que el formulario recargue la página
                    e.preventDefault();

                    // Capturamos los datos ingresados por el usuario
                    const nombre = document.getElementById("nombre").value.trim();
                    const correo = document.getElementById("correo").value.trim();
                    const password = document.getElementById("password").value;

                    mensaje.textContent = "Creando cuenta...";

                    try {

                        // Aquí Firebase crea el usuario en su sistema de autenticación
                        const cred = await createUserWithEmailAndPassword(auth, correo, password);

                        // cred.user.uid es el identificador único que Firebase genera para cada usuario
                        const uid = cred.user.uid;


                        // Luego enviamos esos datos al servidor PHP
                        // para guardar el usuario en MySQL y crear la sesión
                        const resp = await fetch("auth_handler.php", {
                            method: "POST",

                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },

                            body: new URLSearchParams({
                                accion: "register",
                                uid: uid,
                                correo: correo,
                                nombre: nombre
                            })
                        });

                        // Convertimos la respuesta del servidor a formato JSON
                        const data = await resp.json();


                        // Si todo salió bien redirigimos a la página principal
                        if (data.ok) {
                            window.location.href = "../asistencia/asistencia.php";
                        } else {
                            mensaje.textContent = data.error || "No se pudo crear la cuenta.";
                        }

                    } catch (err) {

                        // Si ocurre un error se muestra el mensaje
                        mensaje.textContent = "Error: " + err.message;
                    }
                });

            </script>

        </div>
    </body>
</html>

<!--
Este archivo muestra la pantalla de registro del sistema.

Aquí el usuario puede crear una cuenta ingresando su nombre,
correo y contraseña.

El registro se realiza usando Firebase Authentication con la
función createUserWithEmailAndPassword().

Si Firebase crea el usuario correctamente, se obtiene el UID
del usuario y se envía al archivo auth_handler.php mediante fetch.
Ese archivo se encarga de guardar el usuario en MySQL y crear
la sesión en PHP. Luego el usuario es redirigido a la página
principal de asistencia.
-->
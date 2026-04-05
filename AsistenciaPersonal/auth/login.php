<?php
// Iniciamos la sesión para poder revisar si el usuario ya está logueado
session_start();

// Aquí revisamos si ya existe una sesión activa del usuario
// Si ya inició sesión, no tiene sentido mostrarle otra vez el login
if (isset($_SESSION["usuario_id"])) {
    // Si ya está logueado, lo mandamos directo a la pantalla de asistencia
    header("Location: ../asistencia/asistencia.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">

        <!-- Título que aparece en la pestaña del navegador -->
        <title>Iniciar sesión</title>

        <!-- Enlace al archivo CSS general para darle diseño a la página -->
        <link rel="stylesheet" href="../style.css">
    </head>
    <body>
        <div class="contenedor">
            <h1>Iniciar sesión</h1>

            <!--
                Este formulario pide los datos para entrar al sistema.
                No usamos action porque el proceso no se manda con un submit tradicional,
                sino con JavaScript usando Firebase y luego fetch hacia PHP.
            -->
            <form id="formLogin">
                <label><strong>Correo</strong></label><br>
                <!-- Campo para escribir el correo -->
                <input type="email" id="correo" required><br><br>

                <label><strong>Contraseña</strong></label><br>
                <!-- Campo para escribir la contraseña -->
                <input type="password" id="password" required><br><br>

                <!-- Botón para intentar iniciar sesión -->
                <button type="submit">Entrar</button>
            </form>

            <!-- Aquí se muestran mensajes como:
                 "Iniciando sesión..."
                 o errores si algo sale mal -->
            <p id="mensaje"></p>

            <!-- Enlace para ir a la pantalla de registro -->
            <a href="register.php">Crear cuenta</a>

            <br>
            <a href="recuperar.php">¿Olvidaste tu contraseña?</a>

            <script type="module">
                // Importamos las funciones de Firebase que ocupamos en esta pantalla
                // initializeApp: para inicializar Firebase
                // getAuth: para trabajar la autenticación
                // signInWithEmailAndPassword: para iniciar sesión con correo y contraseña
                import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-app.js";
                import { getAuth, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-auth.js";

                // Esta es la configuración del proyecto de Firebase
                // Con esto nuestra página queda conectada con el proyecto que creamos en Firebase
                const firebaseConfig = {
                    apiKey: "AIzaSyCY_gZtET_Q3vLkosyoo61GQpIJYhV4qNc",
                    authDomain: "asistenciapersonal-fdb78.firebaseapp.com",
                    projectId: "asistenciapersonal-fdb78",
                    storageBucket: "asistenciapersonal-fdb78.firebasestorage.app",
                    messagingSenderId: "626440617732",
                    appId: "1:626440617732:web:1fb4be1e1a41ec447c9a51"
                };

                // Aquí inicializamos Firebase con la configuración anterior
                const app = initializeApp(firebaseConfig);

                // Aquí obtenemos el servicio de autenticación de Firebase
                const auth = getAuth(app);

                // Agarramos el formulario y el párrafo donde vamos a mostrar mensajes
                const form = document.getElementById("formLogin");
                const mensaje = document.getElementById("mensaje");

                // Este evento se ejecuta cuando el usuario presiona el botón Entrar
                form.addEventListener("submit", async (e) => {
                    // Evitamos que el formulario recargue la página de forma normal
                    e.preventDefault();

                    // Tomamos los datos escritos por el usuario
                    const correo = document.getElementById("correo").value.trim();
                    const password = document.getElementById("password").value;

                    // Mostramos un mensaje mientras se hace el proceso
                    mensaje.textContent = "Iniciando sesión...";

                    try {
                        // Aquí Firebase hace la autenticación real del usuario
                        // O sea, aquí es donde se valida si el correo y la contraseña son correctos
                        const cred = await signInWithEmailAndPassword(auth, correo, password);

                        // Si Firebase autentica bien al usuario, nos devuelve un objeto
                        // Dentro viene el uid, que es el identificador único del usuario en Firebase
                        const uid = cred.user.uid;

                        // Después de autenticar con Firebase, mandamos esos datos a PHP
                        // para que el sistema cree la sesión y relacione el usuario con MySQL
                        const resp = await fetch("auth_handler.php", {
                            method: "POST",
                            headers: {"Content-Type": "application/x-www-form-urlencoded"},
                            body: new URLSearchParams({
                                accion: "login",
                                uid: uid,
                                correo: correo
                            })
                        });

                        // Convertimos la respuesta de PHP a formato JSON
                        const data = await resp.json();

                        // Si todo salió bien, redirigimos al usuario a la pantalla de asistencia
                        if (data.ok) {
                            window.location.href = "../asistencia/asistencia.php";
                        } else {
                            // Si PHP devolvió un problema, mostramos el mensaje
                            mensaje.textContent = data.error || "No se pudo iniciar sesión.";
                        }

                    } catch (err) {
                        // Si Firebase da error, por ejemplo:
                        // correo incorrecto, contraseña mala o usuario no existe,
                        // se muestra aquí el error
                        mensaje.textContent = "Error: " + err.message;
                    }
                });
            </script>
        </div>
    </body>
</html>

<!--
Este archivo muestra la pantalla de inicio de sesión del sistema.

Aquí el usuario ingresa su correo y contraseña para autenticarse. 
La autenticación se realiza utilizando Firebase Authentication mediante 
la función signInWithEmailAndPassword().

Si Firebase valida correctamente las credenciales, se obtiene el UID 
(identificador único del usuario). Ese UID se envía al servidor mediante 
fetch al archivo auth_handler.php.

Luego auth_handler.php se encarga de verificar o crear el usuario en la 
base de datos MySQL y de crear la sesión en PHP. Si todo sale bien, el 
usuario es redirigido a la página principal de asistencia.
-->
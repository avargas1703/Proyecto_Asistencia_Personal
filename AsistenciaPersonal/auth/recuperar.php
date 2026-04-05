<?php
// Iniciamos la sesión por si en algún momento se necesita revisar información del usuario
session_start();

// Si el usuario ya está logueado, igual podría recuperar contraseña,
// pero normalmente esta pantalla se usa antes de iniciar sesión.
// Por eso aquí no redirigimos, solo dejamos que cargue normalmente.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>

    <!-- Archivo CSS general del proyecto -->
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="contenedor">
        <h1>Recuperar contraseña</h1>

        <p>
            Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        <!--
            Formulario para recuperar contraseña.
            El usuario solo necesita escribir su correo.
        -->
        <form id="formRecuperar">
            <label><strong>Correo</strong></label><br>
            <input type="email" id="correo" required><br><br>

            <button type="submit">Enviar enlace</button>
        </form>

        <!-- Aquí se mostrará el resultado del proceso -->
        <p id="mensaje"></p>

        <!-- Enlace para volver al login -->
        <a href="login.php">Volver a iniciar sesión</a>

        <script type="module">
            // Importamos las funciones necesarias de Firebase
            // initializeApp: inicializa Firebase
            // getAuth: obtiene el servicio de autenticación
            // sendPasswordResetEmail: envía el correo de recuperación
            import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-app.js";
            import { getAuth, sendPasswordResetEmail } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-auth.js";

            // Configuración del proyecto de Firebase
            const firebaseConfig = {
                apiKey: "AIzaSyCY_gZtET_Q3vLkosyoo61GQpIJYhV4qNc",
                authDomain: "asistenciapersonal-fdb78.firebaseapp.com",
                projectId: "asistenciapersonal-fdb78",
                storageBucket: "asistenciapersonal-fdb78.firebasestorage.app",
                messagingSenderId: "626440617732",
                appId: "1:626440617732:web:1fb4be1e1a41ec447c9a51"
            };

            // Inicializamos Firebase
            const app = initializeApp(firebaseConfig);

            // Obtenemos el módulo de autenticación
            const auth = getAuth(app);

            // Tomamos el formulario y el campo donde mostraremos mensajes
            const form = document.getElementById("formRecuperar");
            const mensaje = document.getElementById("mensaje");

            // Evento que se ejecuta cuando el usuario envía el formulario
            form.addEventListener("submit", async (e) => {
                // Evitamos que la página se recargue
                e.preventDefault();

                // Obtenemos el correo escrito por el usuario
                const correo = document.getElementById("correo").value.trim();

                // Mensaje mientras se procesa la solicitud
                mensaje.textContent = "Enviando enlace de recuperación...";

                try {
                    // Aquí Firebase se encarga de enviar el correo de recuperación
                    await sendPasswordResetEmail(auth, correo);

                    // Si todo sale bien, mostramos confirmación
                    mensaje.textContent = "✅ Se envió el enlace de recuperación al correo indicado.";

                } catch (err) {
                    // Si ocurre un error, mostramos el mensaje
                    mensaje.textContent = "Error: " + err.message;
                }
            });
        </script>
    </div>
</body>
</html>

<!--
Este archivo muestra la pantalla de recuperación de contraseña.

Aquí el usuario ingresa su correo electrónico para solicitar
un enlace de restablecimiento de contraseña.

La recuperación se realiza usando Firebase Authentication
mediante la función sendPasswordResetEmail().

Si el correo existe en Firebase, se envía automáticamente
un enlace de recuperación al usuario. Luego la página
muestra un mensaje indicando si el proceso fue exitoso
o si ocurrió algún error.
-->
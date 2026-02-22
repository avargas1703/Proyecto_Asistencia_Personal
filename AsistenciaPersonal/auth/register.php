<?php
session_start();
if (isset($_SESSION["usuario_id"])) {
    header("Location: ../asistencia/asistencia.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Crear cuenta</title>
    </head>
    <body>
        <h1>Crear cuenta</h1>

        <form id="formRegistro">
            <label>Nombre</label><br>
            <input type="text" id="nombre"><br><br>

            <label>Correo</label><br>
            <input type="email" id="correo" required><br><br>

            <label>Contraseña</label><br>
            <input type="password" id="password" required><br><br>

            <button type="submit">Crear cuenta</button>
        </form>

        <p id="mensaje"></p>
        <a href="login.php">¿Ya tenés cuenta? Iniciar sesión</a>

        <!-- Firebase SDK -->
        <script type="module">
            // 1) IMPORTS de Firebase
            import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-app.js";
            import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-auth.js";

            
            const firebaseConfig = {
                apiKey: "AIzaSyCY_gZtET_Q3vLkosyoo61GQpIJYhV4qNc",
                authDomain: "asistenciapersonal-fdb78.firebaseapp.com",
                projectId: "asistenciapersonal-fdb78",
                storageBucket: "asistenciapersonal-fdb78.firebasestorage.app",
                messagingSenderId: "626440617732",
                appId: "1:626440617732:web:1fb4be1e1a41ec447c9a51"
            };

            // 3) Inicializar Firebase
            const app = initializeApp(firebaseConfig);
            const auth = getAuth(app);

            // 4) Registro
            const form = document.getElementById("formRegistro");
            const mensaje = document.getElementById("mensaje");

            form.addEventListener("submit", async (e) => {
                e.preventDefault();

                const nombre = document.getElementById("nombre").value.trim();
                const correo = document.getElementById("correo").value.trim();
                const password = document.getElementById("password").value;

                mensaje.textContent = "Creando cuenta...";

                try {
                    const cred = await createUserWithEmailAndPassword(auth, correo, password);

                    // cred.user.uid es el UID de Firebase
                    const uid = cred.user.uid;

                    // Enviamos a PHP para crear sesion y guardar usuario en base de datos
                    const resp = await fetch("auth_handler.php", {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: new URLSearchParams({
                            accion: "register",
                            uid: uid,
                            correo: correo,
                            nombre: nombre
                        })
                    });

                    const data = await resp.json();

                    if (data.ok) {
                        window.location.href = "../asistencia/asistencia.php";
                    } else {
                        mensaje.textContent = data.error || "No se pudo crear la cuenta.";
                    }

                } catch (err) {
                    mensaje.textContent = "Error: " + err.message;
                }
            });
        </script>
    </body>
</html>
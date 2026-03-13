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
        <title>Iniciar sesión</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <h1>Iniciar sesión</h1>

        <form id="formLogin">
            <label>Correo</label><br>
            <input type="email" id="correo" required><br><br>

            <label>Contraseña</label><br>
            <input type="password" id="password" required><br><br>

            <button type="submit">Entrar</button>
        </form>

        <p id="mensaje"></p>
        <a href="register.php">Crear cuenta</a>

        <script type="module">
            import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-app.js";
            import { getAuth, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-auth.js";

            const firebaseConfig = {
                apiKey: "AIzaSyCY_gZtET_Q3vLkosyoo61GQpIJYhV4qNc",
                authDomain: "asistenciapersonal-fdb78.firebaseapp.com",
                projectId: "asistenciapersonal-fdb78",
                storageBucket: "asistenciapersonal-fdb78.firebasestorage.app",
                messagingSenderId: "626440617732",
                appId: "1:626440617732:web:1fb4be1e1a41ec447c9a51"
            };

            const app = initializeApp(firebaseConfig);
            const auth = getAuth(app);

            const form = document.getElementById("formLogin");
            const mensaje = document.getElementById("mensaje");

            form.addEventListener("submit", async (e) => {
                e.preventDefault();

                const correo = document.getElementById("correo").value.trim();
                const password = document.getElementById("password").value;

                mensaje.textContent = "Iniciando sesión...";

                try {
                    const cred = await signInWithEmailAndPassword(auth, correo, password);
                    const uid = cred.user.uid;

                    const resp = await fetch("auth_handler.php", {
                        method: "POST",
                        headers: {"Content-Type": "application/x-www-form-urlencoded"},
                        body: new URLSearchParams({
                            accion: "login",
                            uid: uid,
                            correo: correo
                        })
                    });

                    const data = await resp.json();

                    if (data.ok) {
                        window.location.href = "../asistencia/asistencia.php";
                    } else {
                        mensaje.textContent = data.error || "No se pudo iniciar sesión.";
                    }

                } catch (err) {
                    mensaje.textContent = "Error: " + err.message;
                }
            });
        </script>
    </body>
</html>
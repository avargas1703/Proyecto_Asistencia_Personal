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
        <link rel="stylesheet" href="../style.css">
    </head>
    <body>
        <div class="contenedor">
        <h1>Iniciar sesión</h1>

        <form id="formLogin">
            <label><strong>Correo</strong></label><br>
            <input type="email" id="correo" required><br><br>

            <label><strong>Contraseña</strong></label><br>
            <input type="password" id="password" required><br><br>

            <button type="submit">Entrar</button>
        </form>

        <p id="mensaje"></p>
        <a href="register.php">Crear cuenta</a>

        <script type="module">
            import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-app.js";
            import { getAuth, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-auth.js";

            const firebaseConfig = {
                apiKey: "AIzaSyDJ5gXM3gAj7lIvnPXcyU8A_uDkL9Iksm4",
                authDomain: "asistenciapersonal-8cf8e.firebaseapp.com",
                projectId: "asistenciapersonal-8cf8e",
                storageBucket: "asistenciapersonal-8cf8e.firebasestorage.app",
                messagingSenderId: "238513813057",
                appId: "1:238513813057:web:0d5c310ca167c81c7ab8c0"
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
        </div>
    </body>
</html>
<?php
// Archivo: admin/index.php

// Ruta relativa para acceder a los includes desde el subdirectorio
require_once '../includes/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirigir al panel si el admin ya ha iniciado sesión
if (isset($_SESSION['id_admin'])) {
    header("Location: panel.php");
    exit();
}

// Contraseña fija para el administrador
define('ADMIN_PASSWORD', 'veintidosdiez1088');

$error_message = '';

// Procesar el formulario de login del administrador
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST['id_usuario'];
    $password = $_POST['password'];

    if (!empty($id_usuario) && is_numeric($id_usuario) && !empty($password)) {
        // Primero, verificar la contraseña fija
        if ($password === ADMIN_PASSWORD) {
            // Si la contraseña es correcta, verificar que el usuario es administrador
            $stmt = $conn->prepare("SELECT id_usuario, nombre FROM usuarios WHERE id_usuario = ? AND tipo_usuario = 'administrador'");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $admin = $result->fetch_assoc();
                
                // Guardar los datos del administrador en la sesión
                $_SESSION['id_admin'] = $admin['id_usuario'];
                $_SESSION['nombre_admin'] = $admin['nombre'];
                
                // Redirigir al panel de control
                header("Location: panel.php");
                exit();
            } else {
                $error_message = "Este ID no corresponde a un administrador.";
            }
            $stmt->close();
        } else {
            $error_message = "Contraseña incorrecta.";
        }
    } else {
        $error_message = "Por favor, introduce un ID y una contraseña válidos.";
    }
}

// Ruta relativa para la cabecera
// Debido a que el CSS y las imágenes usan rutas absolutas (/css, /Imagenes), no hay problema
include '../includes/header.php';
?>

<div class="login-container">
    <div class="login-box">
        <h1>Panel de Administrador</h1>
        <p>Inicia sesión para gestionar la biblioteca.</p>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="index.php" method="post">
            <div class="form-group">
                <label for="id_usuario">ID de Administrador</label>
                <input type="text" id="id_usuario" name="id_usuario" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
    </div>
</div>

</body>
</html>

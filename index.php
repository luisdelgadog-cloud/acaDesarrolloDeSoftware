<?php
// Archivo: index.php

// Incluir el archivo de conexión a la base de datos
require_once 'includes/db.php';
// Iniciar la sesión para manejar los datos del usuario
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirigir al catálogo si el estudiante ya ha iniciado sesión
if (isset($_SESSION['id_usuario'])) {
    header("Location: catalogo.php");
    exit();
}

$error_message = '';

// Procesar el formulario de login cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST['id_usuario'];

    if (!empty($id_usuario) && is_numeric($id_usuario)) {
        // Preparar la consulta para evitar inyección SQL
        $stmt = $conn->prepare("SELECT id_usuario, nombre FROM usuarios WHERE id_usuario = ? AND tipo_usuario = 'estudiante'");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si el usuario existe y es un estudiante
        if ($result->num_rows == 1) {
            $usuario = $result->fetch_assoc();
            
            // Guardar los datos del usuario en la sesión
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre'];
            
            // Redirigir al catálogo de libros
            header("Location: catalogo.php");
            exit();
        } else {
            $error_message = "ID de estudiante no válido o no encontrado.";
        }
        $stmt->close();
    } else {
        $error_message = "Por favor, introduce un ID de estudiante válido.";
    }
}

// Incluir la cabecera de la página
include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-box">
        <h1>Bienvenido a AcaBiblioteca</h1>
        <p>Inicia sesión con tu ID de estudiante para continuar.</p>
        
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="index.php" method="post">
            <div class="form-group">
                <label for="id_usuario">ID de Estudiante</label>
                <input type="text" id="id_usuario" name="id_usuario" required autocomplete="off">
            </div>
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
    </div>
</div>

<?php
// No se necesita un footer específico para esta página, el body y html se cierran en un archivo footer si lo hubiera.
// Por ahora, lo dejamos así.
?>
</body>
</html>

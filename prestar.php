<?php
// Archivo: prestar.php

require_once 'includes/db.php';
require_once 'includes/funciones.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Proteger: solo para estudiantes logueados
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

// Validar que se recibió un ID de libro
if (isset($_GET['id_libro']) && is_numeric($_GET['id_libro'])) {
    $id_libro = (int)$_GET['id_libro'];
    $id_usuario = $_SESSION['id_usuario'];

    // Llamar a la función para prestar el libro
    if (prestar_libro($conn, $id_usuario, $id_libro)) {
        // Préstamo exitoso, redirigir a "Mi Cuenta" para ver el préstamo
        header("Location: mi_cuenta.php?prestamo=exito");
        exit();
    } else {
        // Fallo en el préstamo (ej. no hay stock), redirigir al catálogo con error
        header("Location: catalogo.php?error=prestamo_fallido");
        exit();
    }
} else {
    // Si no hay ID de libro, redirigir al catálogo
    header("Location: catalogo.php");
    exit();
}
?>

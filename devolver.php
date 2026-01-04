<?php
// Archivo: devolver.php

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

// Validar que se recibió un ID de préstamo
if (isset($_GET['id_prestamo']) && is_numeric($_GET['id_prestamo'])) {
    $id_prestamo = (int)$_GET['id_prestamo'];
    $id_usuario = $_SESSION['id_usuario'];

    // Llamar a la función para devolver el libro
    if (devolver_libro($conn, $id_prestamo, $id_usuario)) {
        // Devolución exitosa
        header("Location: mi_cuenta.php?devolucion=exito");
        exit();
    } else {
        // Fallo en la devolución (ej. el préstamo no pertenece al usuario)
        header("Location: mi_cuenta.php?error=devolucion_fallida");
        exit();
    }
} else {
    // Si no hay ID de préstamo, redirigir a "Mi Cuenta"
    header("Location: mi_cuenta.php");
    exit();
}
?>

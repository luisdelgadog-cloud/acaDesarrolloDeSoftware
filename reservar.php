<?php
// Archivo: reservar.php

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

    // Llamar a la función para reservar el libro
    if (reservar_libro($conn, $id_usuario, $id_libro)) {
        // Reserva exitosa, redirigir a "Mi Cuenta" para ver la reserva
        // Opcional: podrías añadir un mensaje de éxito en la sesión
        header("Location: mi_cuenta.php?reserva=exito");
        exit();
    } else {
        // Fallo en la reserva, redirigir al catálogo con un mensaje de error
        header("Location: catalogo.php?error=reserva_fallida");
        exit();
    }
} else {
    // Si no hay ID de libro, redirigir al catálogo
    header("Location: catalogo.php");
    exit();
}
?>

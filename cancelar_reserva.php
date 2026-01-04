<?php
// Archivo: cancelar_reserva.php

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

// Validar que se recibió un ID de reserva
if (isset($_GET['id_reserva']) && is_numeric($_GET['id_reserva'])) {
    $id_reserva = (int)$_GET['id_reserva'];
    $id_usuario = $_SESSION['id_usuario'];

    // Llamar a la función para cancelar la reserva
    if (cancelar_reserva($conn, $id_reserva, $id_usuario)) {
        // Cancelación exitosa
        header("Location: mi_cuenta.php?cancelacion=exito");
        exit();
    } else {
        // Fallo en la cancelación
        header("Location: mi_cuenta.php?error=cancelacion_fallida");
        exit();
    }
} else {
    // Si no hay ID de reserva, redirigir a "Mi Cuenta"
    header("Location: mi_cuenta.php");
    exit();
}
?>

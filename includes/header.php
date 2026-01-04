<?php
// Archivo: includes/header.php

// Iniciar la sesión en cada página para poder gestionar los datos del usuario logueado.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcaBiblioteca - Gestión de Biblioteca</title>
    <!-- Enlace a la hoja de estilos CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

    <header>
        <div class="logo">
            <!-- El logo se cargará desde la carpeta de imágenes -->
            <a href="/index.php">
                <img src="/Imagenes/LOGO.png" alt="Logo AcaBiblioteca">
            </a>
        </div>
        <nav>
            <?php if (isset($_SESSION['id_usuario'])): ?>
                <!-- Menú para usuarios logueados (estudiantes) -->
                <a href="/catalogo.php">Catálogo</a>
                <a href="/mi_cuenta.php">Mi Cuenta</a>
                <a href="/logout.php">Cerrar Sesión</a>
            <?php elseif (isset($_SESSION['id_admin'])): ?>
                <!-- Menú para administradores logueados -->
                <a href="/admin/panel.php">Panel de Control</a>
                <a href="/logout.php">Cerrar Sesión</a>
            <?php else: ?>
                <!-- Menú para usuarios no logueados -->
                <a href="/index.php">Inicio</a>
                <a href="/admin/index.php">Admin</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">
        <!-- El contenido principal de cada página se insertará aquí -->

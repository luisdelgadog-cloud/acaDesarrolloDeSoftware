<?php
// Archivo: includes/db.php

// --- Configuración de la Base de Datos ---
// Reemplaza estos valores con tus propias credenciales de MySQL.
define('DB_HOST', 'localhost');       // Host de la base de datos (ej. 'localhost' o '127.0.0.1')
define('DB_USER', 'root');            // Nombre de usuario de la base de datos
define('DB_PASS', '');                // Contraseña de la base de datos
define('DB_NAME', 'AcaBiblioteca');   // Nombre de la base de datos

// --- Creación de la Conexión ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// --- Verificación de la Conexión ---
if ($conn->connect_error) {
    // Si la conexión falla, se muestra un error y se detiene la ejecución.
    // En un entorno de producción, sería mejor registrar este error en lugar de mostrarlo al usuario.
    die("Error de conexión: " . $conn->connect_error);
}

// --- Establecer el juego de caracteres a UTF-8 ---
// Esto es importante para evitar problemas con tildes y caracteres especiales.
if (!$conn->set_charset("utf8")) {
    printf("Error al cargar el conjunto de caracteres utf8: %s\n", $conn->error);
    exit();
}
?>

<?php
// Archivo: admin/panel.php

require_once '../includes/db.php';
require_once '../includes/header.php';

// Proteger la página: solo para administradores logueados
if (!isset($_SESSION['id_admin'])) {
    header("Location: index.php");
    exit();
}

$nombre_admin = htmlspecialchars($_SESSION['nombre_admin']);
echo "<h1>Panel de Control del Administrador</h1>";
echo "<p>Bienvenido, $nombre_admin. Aquí tienes un resumen de la actividad de la biblioteca.</p>";

/**
 * Función auxiliar para generar una tabla HTML a partir de una consulta SQL.
 * @param mysqli $conn La conexión a la base de datos.
 * @param string $sql La consulta SQL a ejecutar.
 * @param string $titulo El título a mostrar sobre la tabla.
 */
function mostrar_tabla_registros($conn, $sql, $titulo) {
    $result = $conn->query($sql);
    echo "<div class='panel-admin'>";
    echo "<h2 style='margin-top: 30px;'>$titulo</h2>";
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        // Encabezados de la tabla
        $fields = $result->fetch_fields();
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr>";
        // Datos de la tabla
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $data) {
                echo "<td>" . htmlspecialchars($data) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay registros para mostrar en esta tabla.</p>";
    }
    echo "</div>";
}

// --- Mostrar Tabla de Préstamos ---
$sql_prestamos = "SELECT id_prestamo, id_usuario, id_libro, fecha_prestamo, fecha_devolucion, estado FROM Prestamos ORDER BY fecha_prestamo DESC";
mostrar_tabla_registros($conn, $sql_prestamos, "Registros de Préstamos");

// --- Mostrar Tabla de Reservas ---
$sql_reservas = "SELECT id_reserva, id_usuario, id_libro, fecha_reserva, estado FROM RESERVAS ORDER BY fecha_reserva DESC";
mostrar_tabla_registros($conn, $sql_reservas, "Registros de Reservas");

// --- Mostrar Tabla de Historial ---
$sql_historial = "SELECT id_historial, id_usuario, id_libro, accion, fecha_accion FROM REGISTRO ORDER BY fecha_accion DESC";
mostrar_tabla_registros($conn, $sql_historial, "Historial de Acciones (REGISTRO)");

?>

</main>
</body>
</html>

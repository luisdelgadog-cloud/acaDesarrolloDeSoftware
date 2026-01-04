<?php
// Archivo: mi_cuenta.php

require_once 'includes/db.php';
require_once 'includes/header.php';

// Proteger la página: solo para estudiantes logueados
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = htmlspecialchars($_SESSION['nombre_usuario']);

echo "<h1>Mi Cuenta - $nombre_usuario</h1>";

// --- Mostrar Préstamos Activos ---
echo "<h2>Mis Préstamos Activos</h2>";

$sql_prestamos = "
    SELECT p.id_prestamo, l.titulo, p.fecha_prestamo, p.fecha_devolucion, p.estado
    FROM Prestamos p
    JOIN libros l ON p.id_libro = l.id_libro
    WHERE p.id_usuario = ? AND p.estado IN ('pendiente', 'retrasado')
    ORDER BY p.fecha_prestamo DESC
";

$stmt_prestamos = $conn->prepare($sql_prestamos);
$stmt_prestamos->bind_param("i", $id_usuario);
$stmt_prestamos->execute();
$result_prestamos = $stmt_prestamos->get_result();

if ($result_prestamos->num_rows > 0) {
    echo "<div class='panel-admin'><table>"; // Reutilizamos el estilo de tabla del admin
    echo "<tr><th>Título del Libro</th><th>Fecha de Préstamo</th><th>Fecha de Devolución</th><th>Estado</th><th>Acción</th></tr>";
    while ($prestamo = $result_prestamos->fetch_assoc()) {
        $titulo = htmlspecialchars($prestamo['titulo']);
        $fecha_prestamo = htmlspecialchars($prestamo['fecha_prestamo']);
        $fecha_devolucion = htmlspecialchars($prestamo['fecha_devolucion']);
        $estado = htmlspecialchars($prestamo['estado']);
        $id_prestamo = $prestamo['id_prestamo'];

        echo "<tr>";
        echo "<td>$titulo</td>";
        echo "<td>$fecha_prestamo</td>";
        echo "<td>$fecha_devolucion</td>";
        echo "<td>$estado</td>";
        // El script devolver.php se creará más adelante
        echo "<td><a href='devolver.php?id_prestamo=$id_prestamo' class='btn' style='width: auto; padding: 8px 12px;'>Devolver</a></td>";
        echo "</tr>";
    }
    echo "</table></div>";
} else {
    echo "<p>No tienes ningún libro prestado en este momento.</p>";
}
$stmt_prestamos->close();


// --- Mostrar Reservas Activas ---
echo "<h2 style='margin-top: 40px;'>Mis Reservas Activas</h2>";

$sql_reservas = "
    SELECT r.id_reserva, l.titulo, r.fecha_reserva
    FROM RESERVAS r
    JOIN libros l ON r.id_libro = l.id_libro
    WHERE r.id_usuario = ? AND r.estado = 'activa'
    ORDER BY r.fecha_reserva DESC
";

$stmt_reservas = $conn->prepare($sql_reservas);
$stmt_reservas->bind_param("i", $id_usuario);
$stmt_reservas->execute();
$result_reservas = $stmt_reservas->get_result();

if ($result_reservas->num_rows > 0) {
    echo "<div class='panel-admin'><table>";
    echo "<tr><th>Título del Libro</th><th>Fecha de Reserva</th><th>Acción</th></tr>";
    while ($reserva = $result_reservas->fetch_assoc()) {
        $titulo_reserva = htmlspecialchars($reserva['titulo']);
        $fecha_reserva = htmlspecialchars($reserva['fecha_reserva']);
        $id_reserva = $reserva['id_reserva'];

        echo "<tr>";
        echo "<td>$titulo_reserva</td>";
        echo "<td>$fecha_reserva</td>";
        // El script cancelar_reserva.php se creará más adelante
        echo "<td><a href='cancelar_reserva.php?id_reserva=$id_reserva' class='btn' style='width: auto; padding: 8px 12px; background-color: var(--color-error);'>Cancelar</a></td>";
        echo "</tr>";
    }
    echo "</table></div>";
} else {
    echo "<p>No tienes ninguna reserva activa.</p>";
}
$stmt_reservas->close();

?>

</main>
</body>
</html>

<?php
// Archivo: includes/funciones.php

/**
 * Registra una acción en el historial (tabla REGISTRO).
 * @param mysqli $conn La conexión a la BD.
 * @param int $id_usuario ID del usuario que realiza la acción.
 * @param int $id_libro ID del libro afectado.
 * @param string $accion Descripción de la acción (e.g., 'reserva', 'prestamo').
 */
function registrar_accion($conn, $id_usuario, $id_libro, $accion) {
    $sql = "INSERT INTO REGISTRO (id_usuario, id_libro, accion, fecha_accion) VALUES (?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id_usuario, $id_libro, $accion);
    $stmt->execute();
    $stmt->close();
}

/**
 * Procesa la reserva de un libro.
 * @param mysqli $conn
 * @param int $id_usuario
 * @param int $id_libro
 * @return bool True si la reserva fue exitosa, False en caso contrario.
 */
function reservar_libro($conn, $id_usuario, $id_libro) {
    // Iniciar transacción para asegurar la integridad de los datos
    $conn->begin_transaction();

    try {
        // 1. Verificar si hay libros disponibles (aunque la reserva no descuenta, es buena práctica)
        $stmt_check = $conn->prepare("SELECT cantidad_disponibles FROM libros WHERE id_libro = ?");
        $stmt_check->bind_param("i", $id_libro);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $libro = $result_check->fetch_assoc();
        $stmt_check->close();

        if ($libro && $libro['cantidad_disponibles'] > 0) {
            // 2. Insertar la reserva
            $sql_reserva = "INSERT INTO RESERVAS (id_usuario, id_libro, fecha_reserva, estado) VALUES (?, ?, CURDATE(), 'activa')";
            $stmt_reserva = $conn->prepare($sql_reserva);
            $stmt_reserva->bind_param("ii", $id_usuario, $id_libro);
            $stmt_reserva->execute();
            $stmt_reserva->close();

            // 3. Registrar la acción en el historial
            registrar_accion($conn, $id_usuario, $id_libro, 'reserva');

            // Confirmar la transacción
            $conn->commit();
            return true;
        } else {
            // No hay libros, deshacer la transacción
            $conn->rollback();
            return false;
        }
    } catch (Exception $e) {
        // Ocurrió un error, deshacer la transacción
        $conn->rollback();
        // Opcional: registrar el error $e->getMessage()
        return false;
    }
}

/**
 * Procesa el préstamo de un libro.
 * @param mysqli $conn
 * @param int $id_usuario
 * @param int $id_libro
 * @return bool True si el préstamo fue exitoso, False en caso contrario.
 */
function prestar_libro($conn, $id_usuario, $id_libro) {
    $conn->begin_transaction();

    try {
        // 1. Verificar disponibilidad y bloquear la fila para evitar concurrencia
        $stmt_check = $conn->prepare("SELECT cantidad_disponibles FROM libros WHERE id_libro = ? FOR UPDATE");
        $stmt_check->bind_param("i", $id_libro);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $libro = $result_check->fetch_assoc();
        $stmt_check->close();

        if ($libro && $libro['cantidad_disponibles'] > 0) {
            // 2. Disminuir la cantidad de libros disponibles
            $sql_update = "UPDATE libros SET cantidad_disponibles = cantidad_disponibles - 1 WHERE id_libro = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $id_libro);
            $stmt_update->execute();
            $stmt_update->close();
            
            // 3. Insertar el préstamo
            $fecha_devolucion = date('Y-m-d', strtotime('+15 days')); // Préstamo por 15 días
            $sql_prestamo = "INSERT INTO Prestamos (id_usuario, id_libro, fecha_prestamo, fecha_devolucion, estado) VALUES (?, ?, CURDATE(), ?, 'pendiente')";
            $stmt_prestamo = $conn->prepare($sql_prestamo);
            $stmt_prestamo->bind_param("iis", $id_usuario, $id_libro, $fecha_devolucion);
            $stmt_prestamo->execute();
            $stmt_prestamo->close();

            // 4. Registrar en el historial
            registrar_accion($conn, $id_usuario, $id_libro, 'prestamo');

            $conn->commit();
            return true;
        } else {
            $conn->rollback();
            return false;
        }
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Procesa la devolución de un libro.
 * @param mysqli $conn
 * @param int $id_prestamo
 * @param int $id_usuario_session El ID del usuario en sesión, para seguridad.
 * @return bool True si la devolución fue exitosa, False en caso contrario.
 */
function devolver_libro($conn, $id_prestamo, $id_usuario_session) {
    $conn->begin_transaction();

    try {
        // 1. Obtener el ID del libro y verificar que el préstamo pertenece al usuario
        $stmt_prestamo = $conn->prepare("SELECT id_libro, id_usuario FROM Prestamos WHERE id_prestamo = ? AND estado != 'devuelto'");
        $stmt_prestamo->bind_param("i", $id_prestamo);
        $stmt_prestamo->execute();
        $result_prestamo = $stmt_prestamo->get_result();
        $prestamo = $result_prestamo->fetch_assoc();
        $stmt_prestamo->close();
        
        if ($prestamo && $prestamo['id_usuario'] == $id_usuario_session) {
            $id_libro = $prestamo['id_libro'];

            // 2. Actualizar el estado del préstamo a 'devuelto'
            $sql_update = "UPDATE Prestamos SET estado = 'devuelto', fecha_devolucion = CURDATE() WHERE id_prestamo = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $id_prestamo);
            $stmt_update->execute();
            $stmt_update->close();

            // 3. Incrementar la cantidad de libros disponibles
            $sql_stock = "UPDATE libros SET cantidad_disponibles = cantidad_disponibles + 1 WHERE id_libro = ?";
            $stmt_stock = $conn->prepare($sql_stock);
            $stmt_stock->bind_param("i", $id_libro);
            $stmt_stock->execute();
            $stmt_stock->close();

            // 4. Registrar en el historial
            registrar_accion($conn, $id_usuario_session, $id_libro, 'devolucion');

            $conn->commit();
            return true;
        } else {
            $conn->rollback();
            return false;
        }
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Cancela una reserva activa.
 * @param mysqli $conn
 * @param int $id_reserva
 * @param int $id_usuario_session
 * @return bool
 */
function cancelar_reserva($conn, $id_reserva, $id_usuario_session) {
    $conn->begin_transaction();
    try {
        // 1. Verificar que la reserva pertenece al usuario
        $stmt_reserva = $conn->prepare("SELECT id_libro FROM RESERVAS WHERE id_reserva = ? AND id_usuario = ? AND estado = 'activa'");
        $stmt_reserva->bind_param("ii", $id_reserva, $id_usuario_session);
        $stmt_reserva->execute();
        $result = $stmt_reserva->get_result();
        $reserva = $result->fetch_assoc();
        $stmt_reserva->close();

        if ($reserva) {
            // 2. Actualizar el estado de la reserva
            $stmt_update = $conn->prepare("UPDATE RESERVAS SET estado = 'cancelada' WHERE id_reserva = ?");
            $stmt_update->bind_param("i", $id_reserva);
            $stmt_update->execute();
            $stmt_update->close();

            // 3. Registrar en el historial
            registrar_accion($conn, $id_usuario_session, $reserva['id_libro'], 'cancelar_reserva');

            $conn->commit();
            return true;
        } else {
            $conn->rollback();
            return false;
        }
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}
?>

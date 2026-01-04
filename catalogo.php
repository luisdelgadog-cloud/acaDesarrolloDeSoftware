<?php
// Archivo: catalogo.php

require_once 'includes/db.php';
require_once 'includes/header.php'; // La sesión ya se inicia en header.php

// Proteger la página: solo para estudiantes logueados
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

// Mensaje de bienvenida
$nombre_usuario = htmlspecialchars($_SESSION['nombre_usuario']);
echo "<h1>Bienvenido, $nombre_usuario!</h1>";
echo "<p>Explora nuestro catálogo de libros disponibles.</p>";

// --- Obtener Categorías y Libros ---
$sql_categorias = "SELECT id_categoria, nombre_categoria FROM Categorias ORDER BY nombre_categoria ASC";
$result_categorias = $conn->query($sql_categorias);

if ($result_categorias->num_rows > 0) {
    // Iterar sobre cada categoría
    while ($categoria = $result_categorias->fetch_assoc()) {
        $id_categoria = $categoria['id_categoria'];
        $nombre_categoria = htmlspecialchars($categoria['nombre_categoria']);
        
        echo "<div class='categoria-libros'>";
        echo "<h2>$nombre_categoria</h2>";

        // Preparar consulta para obtener los libros de esta categoría
        $stmt = $conn->prepare(
            "SELECT id_libro, titulo, autor, anio_publicacion, cantidad_disponibles 
             FROM libros 
             WHERE categoria_id = ? AND cantidad_disponibles > 0 
             ORDER BY titulo ASC"
        );
        $stmt->bind_param("i", $id_categoria);
        $stmt->execute();
        $result_libros = $stmt->get_result();

        if ($result_libros->num_rows > 0) {
            echo "<div class='libros-grid'>";
            // Iterar sobre cada libro y mostrarlo como una tarjeta
            while ($libro = $result_libros->fetch_assoc()) {
                $id_libro = $libro['id_libro'];
                $titulo = htmlspecialchars($libro['titulo']);
                $autor = htmlspecialchars($libro['autor']);
                $disponibles = $libro['cantidad_disponibles'];

                echo "<div class='libro-card'>";
                // Usamos la imagen por defecto que sugerí
                echo "<img src='/Imagenes/portada_default.png' alt='Portada de $titulo'>";
                echo "<h4>$titulo</h4>";
                echo "<p><strong>Autor:</strong> $autor</p>";
                echo "<p><strong>Disponibles:</strong> $disponibles</p>";
                
                // --- Acciones del Libro ---
                // Los enlaces pasarán el ID del libro a los scripts que manejarán la lógica.
                // Estos scripts se crearán más adelante.
                echo "<div class='acciones'>";
                echo "<a href='reservar.php?id_libro=$id_libro'>Reservar</a>";
                echo "<a href='prestar.php?id_libro=$id_libro'>Prestar</a>";
                echo "</div>";

                echo "</div>"; // Fin de .libro-card
            }
            echo "</div>"; // Fin de .libros-grid
        } else {
            echo "<p>No hay libros disponibles en esta categoría en este momento.</p>";
        }
        $stmt->close();
        echo "</div>"; // Fin de .categoria-libros
    }
} else {
    echo "<p>No hay categorías de libros para mostrar.</p>";
}

?>

</main>
</body>
</html>

<?php
include("../config/db.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Verificar si el autor tiene libros asociados
    $sql_check = "SELECT COUNT(*) as total FROM libros WHERE autor_id = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $resultado = $stmt_check->get_result();
    $fila = $resultado->fetch_assoc();
    $stmt_check->close();
    
    if ($fila['total'] > 0) {
        // Si tiene libros, no se puede eliminar
        header("Location: index.php?error=No se puede eliminar el autor porque tiene libros asociados");
        exit();
    }
    
    // Obtener información de la imagen para eliminarla
    $sql_imagen = "SELECT foto_url FROM autores WHERE id = ?";
    $stmt_imagen = $conexion->prepare($sql_imagen);
    $stmt_imagen->bind_param("i", $id);
    $stmt_imagen->execute();
    $result_imagen = $stmt_imagen->get_result();
    
    if ($result_imagen->num_rows > 0) {
        $autor = $result_imagen->fetch_assoc();
        // Eliminar la imagen si existe
        if (!empty($autor['foto_url']) && file_exists("imagenes/" . $autor['foto_url'])) {
            unlink("imagenes/" . $autor['foto_url']);
        }
    }
    $stmt_imagen->close();
    
    // Eliminar el autor
    $sql = "DELETE FROM autores WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=Autor eliminado correctamente");
    } else {
        header("Location: index.php?error=Error al eliminar el autor");
    }

    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}

$conexion->close();
?>
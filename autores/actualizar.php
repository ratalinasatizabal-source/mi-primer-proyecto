<?php 
include("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y validar datos
    $id = intval($_POST['id']);
    $nombre = $conexion->real_escape_string(trim($_POST['nombre']));
    $apellido = $conexion->real_escape_string(trim($_POST['apellido']));
    $nacionalidad = $conexion->real_escape_string(trim($_POST['nacionalidad']));
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?: NULL;
    $biografia = $conexion->real_escape_string(trim($_POST['biografia']));
    
    // Validaciones
    if (empty($nombre) || empty($apellido)) {
        header("Location: editar.php?id=$id&error=El nombre y apellido son obligatorios");
        exit();
    }

    // Obtener datos actuales para mantener la imagen si no se cambia
    $sql_actual = "SELECT foto_url FROM autores WHERE id = ?";
    $stmt_actual = $conexion->prepare($sql_actual);
    $stmt_actual->bind_param("i", $id);
    $stmt_actual->execute();
    $result_actual = $stmt_actual->get_result();
    $autor_actual = $result_actual->fetch_assoc();
    $stmt_actual->close();
    
    $nombreImagen = $autor_actual['foto_url'];

    // Verificar si se subió una nueva imagen
    if (!empty($_FILES['foto_url']['name'])) {
        $nombreImagen = basename($_FILES['foto_url']['name']);
        $rutaDestino = "imagenes/" . $nombreImagen;
        
        // Validar tipo de archivo (MEJORADO: incluye SVG)
        $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));
        $extensionesPermitidas = array("jpg", "jpeg", "png", "gif", "svg");
        
        if (in_array($tipoArchivo, $extensionesPermitidas)) {
            if ($_FILES['foto_url']['size'] > 2000000) {
                header("Location: editar.php?id=$id&error=La imagen es demasiado grande (máximo 2MB)");
                exit();
            }
            
            // Crear directorio si no existe
            if (!is_dir('imagenes')) {
                mkdir('imagenes', 0755, true);
            }
            
            // Eliminar imagen anterior si existe
            if (!empty($autor_actual['foto_url']) && file_exists("imagenes/" . $autor_actual['foto_url'])) {
                unlink("imagenes/" . $autor_actual['foto_url']);
            }
            
            if (!move_uploaded_file($_FILES['foto_url']['tmp_name'], $rutaDestino)) {
                header("Location: editar.php?id=$id&error=Error al subir la nueva imagen");
                exit();
            }
        } else {
            header("Location: editar.php?id=$id&error=Formato de imagen no válido. Use JPG, PNG, GIF o SVG");
            exit();
        }
    }

    // Actualizar en la base de datos usando prepared statement
    $sql = "UPDATE autores SET 
            nombre = ?, 
            apellido = ?, 
            nacionalidad = ?, 
            fecha_nacimiento = ?, 
            biografia = ?, 
            foto_url = ? 
            WHERE id = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssssi", $nombre, $apellido, $nacionalidad, $fecha_nacimiento, $biografia, $nombreImagen, $id);

    if ($stmt->execute()) {
        header("Location: index.php?success=Autor actualizado correctamente");
        exit();
    } else {
        header("Location: editar.php?id=$id&error=Error al actualizar: " . $conexion->error);
        exit();
    }

    $stmt->close();
} else {
    header("Location: index.php");
    exit();
}

$conexion->close();
?>
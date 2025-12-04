<?php
include("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y sanitizar datos
    $nombre = $conexion->real_escape_string(trim($_POST['nombre']));
    $apellido = $conexion->real_escape_string(trim($_POST['apellido']));
    $nacionalidad = $conexion->real_escape_string(trim($_POST['nacionalidad']));
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?: NULL;
    $biografia = $conexion->real_escape_string(trim($_POST['biografia']));
    
    // Validaciones básicas
    if (empty($nombre) || empty($apellido)) {
        header("Location: crear.php?error=El nombre y apellido son obligatorios");
        exit();
    }

    // Manejo de imagen
    $nombreImagen = "";
    if (!empty($_FILES['foto_url']['name'])) {
        $nombreImagen = basename($_FILES['foto_url']['name']);
        $rutaDestino = "imagenes/" . $nombreImagen;
        
        // Validar tipo de archivo (CORREGIDO: incluye SVG)
        $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));
        $extensionesPermitidas = array("jpg", "jpeg", "png", "gif", "svg");
        
        if (in_array($tipoArchivo, $extensionesPermitidas)) {
            if ($_FILES['foto_url']['size'] > 2000000) { // 2MB máximo
                header("Location: crear.php?error=La imagen es demasiado grande (máximo 2MB)");
                exit();
            }
            
            // Crear directorio si no existe
            if (!is_dir('imagenes')) {
                mkdir('imagenes', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['foto_url']['tmp_name'], $rutaDestino)) {
                // Imagen subida correctamente
            } else {
                header("Location: crear.php?error=Error al subir la imagen al servidor");
                exit();
            }
        } else {
            header("Location: crear.php?error=Formato de imagen no válido. Use JPG, PNG, GIF o SVG");
            exit();
        }
    }

    // Insertar en la base de datos - CORREGIDO: sin $$ duplicado
    $sql = "INSERT INTO autores (nombre, apellido, nacionalidad, fecha_nacimiento, biografia, foto_url)
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssss", $nombre, $apellido, $nacionalidad, $fecha_nacimiento, $biografia, $nombreImagen);

    if ($stmt->execute()) {
        header("Location: index.php?success=Autor creado correctamente");
        exit();
    } else {
        header("Location: crear.php?error=Error al guardar el autor: " . $conexion->error);
        exit();
    }

    $stmt->close();
} else {
    header("Location: crear.php");
    exit();
}

$conexion->close();
?>
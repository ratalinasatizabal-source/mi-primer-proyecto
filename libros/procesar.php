<?php
require_once '../config/db.php'; 

// Verificar si se recibió la acción desde el formulario
$accion = $_POST['accion'] ?? '';

function subirImagen($file) {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($extension), $extensionesPermitidas)) {
            $nombreUnico = uniqid() . '.' . $extension;
            $rutaDestino = '../assets/images/' . $nombreUnico;
            
            if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                return $rutaDestino;
            }
        }
    }
    return null;
}

if ($accion === 'crear') {
    $titulo = trim($_POST['titulo']);
    $autor_id = (int) $_POST['autor_id'];
    $categoria_id = (int) $_POST['categoria_id'];
    $isbn = trim($_POST['isbn']);
    $editorial = trim($_POST['editorial']);
    $stock = (int) $_POST['stock'];
    $estado = $_POST['estado'];
    $año_publicacion = !empty($_POST['año_publicacion']) ? (int) $_POST['año_publicacion'] : null;
    $idioma = trim($_POST['idioma']);
    $precio = !empty($_POST['precio']) ? (float) $_POST['precio'] : null;
    $descripcion = trim($_POST['descripcion']);

    // Validación básica
    if (empty($titulo) || empty($isbn) || $autor_id <= 0 || $categoria_id <= 0) {
        die("Faltan datos obligatorios.");
    }

    // Procesar imagen
    $portada_url = null;
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $portada_url = subirImagen($_FILES['portada']);
    }

    // Preparar consulta segura
    $stmt = $conexion->prepare("INSERT INTO libros (titulo, autor_id, categoria_id, isbn, editorial, stock, estado, año_publicacion, idioma, precio, descripcion, portada_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("siisssissdss", $titulo, $autor_id, $categoria_id, $isbn, $editorial, $stock, $estado, $año_publicacion, $idioma, $precio, $descripcion, $portada_url);

    if ($stmt->execute()) {
        echo "<div style='text-align:center; padding:20px;'>";
        echo "<h3 style='color:green;'>Libro agregado correctamente</h3>";
        echo "<p>Redirigiendo al listado...</p>";
        echo "</div>";
    } else {
        echo "<div style='text-align:center; padding:20px;'>";
        echo "<h3 style='color:red;'>Error al agregar el libro: " . $stmt->error . "</h3>";
        echo "</div>";
    }

    $stmt->close();

} elseif ($accion === 'editar') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $titulo = trim($_POST['titulo']);
    $autor_id = (int) $_POST['autor_id'];
    $categoria_id = (int) $_POST['categoria_id'];
    $isbn = trim($_POST['isbn']);
    $editorial = trim($_POST['editorial']);
    $stock = (int) $_POST['stock'];
    $estado = $_POST['estado'];
    $año_publicacion = !empty($_POST['año_publicacion']) ? (int) $_POST['año_publicacion'] : null;
    $idioma = trim($_POST['idioma']);
    $precio = !empty($_POST['precio']) ? (float) $_POST['precio'] : null;
    $descripcion = trim($_POST['descripcion']);

    if ($id <= 0) {
        die("ID inválido para edición.");
    }

    // Procesar nueva imagen si se subió
    $portada_url = null;
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $portada_url = subirImagen($_FILES['portada']);
    }

    // Preparar la consulta de actualización
    if ($portada_url) {
        // Si hay nueva imagen, actualizar también la portada
        $stmt = $conexion->prepare("UPDATE libros SET titulo = ?, autor_id = ?, categoria_id = ?, isbn = ?, editorial = ?, stock = ?, estado = ?, año_publicacion = ?, idioma = ?, precio = ?, descripcion = ?, portada_url = ? WHERE id = ?");
        $stmt->bind_param("siisssissdssi", $titulo, $autor_id, $categoria_id, $isbn, $editorial, $stock, $estado, $año_publicacion, $idioma, $precio, $descripcion, $portada_url, $id);
    } else {
        // Si no hay nueva imagen, mantener la actual
        $stmt = $conexion->prepare("UPDATE libros SET titulo = ?, autor_id = ?, categoria_id = ?, isbn = ?, editorial = ?, stock = ?, estado = ?, año_publicacion = ?, idioma = ?, precio = ?, descripcion = ? WHERE id = ?");
        $stmt->bind_param("siisssissdsi", $titulo, $autor_id, $categoria_id, $isbn, $editorial, $stock, $estado, $año_publicacion, $idioma, $precio, $descripcion, $id);
    }

    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    if ($stmt->execute()) {
        echo "<div style='text-align:center; padding:20px;'>";
        echo "<h3 style='color:green;'>Libro actualizado correctamente</h3>";
        echo "<p>Redirigiendo al listado...</p>";
        echo "</div>";
    } else {
        echo "<div style='text-align:center; padding:20px;'>";
        echo "<h3 style='color:red;'>Error al actualizar: " . $stmt->error . "</h3>";
        echo "</div>";
    }

    $stmt->close();

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['accion'] ?? '') === 'toggle_estado') {
    require_once __DIR__ . '/../config/db.php';

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        header('Location: index.php');
        exit;
    }

    // Obtener estado actual
    $stmt = $conexion->prepare("SELECT estado FROM libros WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if (!$row) {
        // libro no encontrado
        header('Location: index.php');
        exit;
    }

    $nuevo_estado = ($row['estado'] === 'activo') ? 'inactivo' : 'activo';

    // Actualizar estado
    $stmt = $conexion->prepare("UPDATE libros SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $id);
    $ok = $stmt->execute();
    $stmt->close();

    // Redirigir de vuelta al listado
    header('Location: index.php');
    exit;
} else {
    die("Acción no válida.");
}

// Cerrar conexión
$conexion->close();

// Redirigir al listado principal después de unos segundos
echo '<meta http-equiv="refresh" content="2; url=index.php">';
?>
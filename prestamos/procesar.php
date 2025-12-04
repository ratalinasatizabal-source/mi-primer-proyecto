<?php
require_once '../config/db.php';

// Función para mostrar mensaje y redirigir
function mostrarMensaje($mensaje, $tipo = 'success', $url_redireccion = 'index.php', $tiempo = 3) {
    $clase = $tipo == 'success' ? 'alert-success' : 'alert-danger';
    $icono = $tipo == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Procesando - Biblioteca</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        <link rel='stylesheet' href='../assets/css/estilo.css'>
    </head>
    <body class='bg-light'>
        <?php include '../includes/header.php'; ?>
        <div class='container mt-4'>
            <div class='row justify-content-center'>
                <div class='col-md-6'>
                    <div class='card shadow'>
                        <div class='card-body text-center py-5'>
                            <i class='fas {$icono} fa-3x text-{$tipo} mb-3'></i>
                            <h3 class='text-{$tipo}'>{$mensaje}</h3>
                            <p class='text-muted'>Redirigiendo en {$tiempo} segundos...</p>
                            <div class='progress mt-3' style='height: 5px;'>
                                <div class='progress-bar bg-{$tipo}' role='progressbar' style='width: 100%' aria-valuenow='100' aria-valuemin='0' aria-valuemax='100'></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <meta http-equiv='refresh' content='{$tiempo}; url={$url_redireccion}'>
        <?php include '../includes/footer.php'; ?>
    </body>
    </html>
    ";
    exit;
}

// Verificar si se recibió la acción
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

// Función para verificar disponibilidad del libro
function libro_disponible($conexion, $libro_id) {
    $stmt = $conexion->prepare("SELECT stock FROM libros WHERE id = ?");
    $stmt->bind_param("i", $libro_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row && $row['stock'] > 0;
}

// Función para actualizar stock
function actualizar_stock($conexion, $libro_id, $cambio) {
    $stmt = $conexion->prepare("UPDATE libros SET stock = stock + ? WHERE id = ?");
    $stmt->bind_param("ii", $cambio, $libro_id);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'crear') {
    // REGISTRAR NUEVO PRÉSTAMO
    $libro_id = intval($_POST['libro_id'] ?? 0);
    $nombre = trim($_POST['nombre_prestamista'] ?? '');
    $email = trim($_POST['email_prestamista'] ?? '');
    $fecha_prestamo = $_POST['fecha_prestamo'] ?? date('Y-m-d');
    $fecha_devolucion_esperada = $_POST['fecha_devolucion_esperada'] ?? '';
    $notas = trim($_POST['notas'] ?? '');

    // Validaciones
    if ($libro_id <= 0 || empty($nombre) || empty($email) || empty($fecha_devolucion_esperada)) {
        mostrarMensaje('Todos los campos obligatorios deben ser completados.', 'danger', 'crear.php');
    }

    // Verificar disponibilidad
    if (!libro_disponible($conexion, $libro_id)) {
        mostrarMensaje('El libro seleccionado no está disponible (sin stock).', 'danger', 'crear.php');
    }

    // Insertar préstamo
    $sql = "INSERT INTO prestamos (libro_id, nombre_prestamista, email_prestamista, fecha_prestamo, fecha_devolucion_esperada, estado, notas) 
            VALUES (?, ?, ?, ?, ?, 'activo', ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("isssss", $libro_id, $nombre, $email, $fecha_prestamo, $fecha_devolucion_esperada, $notas);

    if ($stmt->execute()) {
        // Actualizar stock del libro
        actualizar_stock($conexion, $libro_id, -1);
        mostrarMensaje('Préstamo registrado correctamente.', 'success', 'index.php');
    } else {
        mostrarMensaje('Error al registrar el préstamo: ' . $conexion->error, 'danger', 'crear.php');
    }

    $stmt->close();

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'devolver') {
    // REGISTRAR DEVOLUCIÓN
    $id = intval($_POST['id'] ?? 0);
    $fecha_real = date('Y-m-d');
    $notas_devolucion = trim($_POST['notas_devolucion'] ?? '');

    if ($id <= 0) {
        mostrarMensaje('ID de préstamo inválido.', 'danger', 'devolver.php');
    }

    // Verificar que el préstamo existe y está activo
    $stmt = $conexion->prepare("SELECT libro_id, estado FROM prestamos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prestamo = $result->fetch_assoc();
    $stmt->close();

    if (!$prestamo) {
        mostrarMensaje('El préstamo especificado no existe.', 'danger', 'devolver.php');
    }

    if ($prestamo['estado'] !== 'activo') {
        mostrarMensaje('Este préstamo ya fue devuelto anteriormente.', 'warning', 'devolver.php');
    }

    // Actualizar préstamo
    $sql = "UPDATE prestamos SET fecha_devolucion_real = ?, estado = 'devuelto', notas = CONCAT(IFNULL(notas, ''), ?) WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $notas_completas = "\n\n--- DEVOLUCIÓN ---\nFecha: " . $fecha_real;
    if (!empty($notas_devolucion)) {
        $notas_completas .= "\nObservaciones: " . $notas_devolucion;
    }
    $stmt->bind_param("ssi", $fecha_real, $notas_completas, $id);

    if ($stmt->execute()) {
        // Actualizar stock del libro
        actualizar_stock($conexion, $prestamo['libro_id'], +1);
        mostrarMensaje('Devolución registrada correctamente.', 'success', 'index.php');
    } else {
        mostrarMensaje('Error al registrar la devolución: ' . $conexion->error, 'danger', 'devolver.php');
    }

    $stmt->close();

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'devolver') {
    // DEVOLUCIÓN DIRECTA DESDE EL LISTADO
    $id = intval($_GET['id'] ?? 0);
    $fecha_real = date('Y-m-d');

    if ($id <= 0) {
        mostrarMensaje('ID de préstamo inválido.', 'danger', 'index.php');
    }

    // Verificar que el préstamo existe y está activo
    $stmt = $conexion->prepare("SELECT libro_id, estado FROM prestamos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prestamo = $result->fetch_assoc();
    $stmt->close();

    if (!$prestamo) {
        mostrarMensaje('El préstamo especificado no existe.', 'danger', 'index.php');
    }

    if ($prestamo['estado'] !== 'activo') {
        mostrarMensaje('Este préstamo ya fue devuelto anteriormente.', 'warning', 'index.php');
    }

    // Actualizar préstamo
    $sql = "UPDATE prestamos SET fecha_devolucion_real = ?, estado = 'devuelto' WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $fecha_real, $id);

    if ($stmt->execute()) {
        // Actualizar stock del libro
        actualizar_stock($conexion, $prestamo['libro_id'], +1);
        mostrarMensaje('Devolución registrada correctamente.', 'success', 'index.php');
    } else {
        mostrarMensaje('Error al registrar la devolución: ' . $conexion->error, 'danger', 'index.php');
    }

    $stmt->close();

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'eliminar') {
    // ELIMINAR PRÉSTAMO
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        mostrarMensaje('ID de préstamo inválido.', 'danger', 'index.php');
    }

    // Verificar que el préstamo existe
    $stmt = $conexion->prepare("SELECT libro_id, estado FROM prestamos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prestamo = $result->fetch_assoc();
    $stmt->close();

    if (!$prestamo) {
        mostrarMensaje('El préstamo especificado no existe.', 'danger', 'index.php');
    }

    // Si el préstamo está activo, restaurar stock
    if ($prestamo['estado'] === 'activo') {
        actualizar_stock($conexion, $prestamo['libro_id'], +1);
    }

    // Eliminar préstamo
    $stmt = $conexion->prepare("DELETE FROM prestamos WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        mostrarMensaje('Préstamo eliminado correctamente.', 'success', 'index.php');
    } else {
        mostrarMensaje('Error al eliminar el préstamo: ' . $conexion->error, 'danger', 'index.php');
    }

    $stmt->close();

} else {
    mostrarMensaje('Acción no válida.', 'danger', 'index.php');
}
$conexion->close();
?>
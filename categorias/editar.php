<?php
include("../config/db.php");

// Obtener datos actuales
if (!isset($_GET['id'])) {
    die("ID no especificado");
}

$id = intval($_GET['id']);

$stmt = $conexion->prepare("SELECT * FROM categorias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$categoria = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$categoria) {
    die("Categoría no encontrada");
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $color_hex = trim($_POST['color_hex']);

    if ($nombre === '') {
        echo "<script>alert('El nombre es obligatorio');</script>";
    } else {
        $stmt = $conexion->prepare("
            UPDATE categorias 
            SET nombre=?, descripcion=?, color_hex=? 
            WHERE id=?
        ");
        $stmt->bind_param("sssi", $nombre, $descripcion, $color_hex, $id);

        if ($stmt->execute()) {
            echo "<script>alert('Categoría actualizada correctamente'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar');</script>";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/estilo.css">
<title>Editar categoría</title>
</head>
<body class="p-4">
    <?php include('../includes/header.php'); ?>
<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h4 class="mb-0">Editar categoría</h4>
    </div>

    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nombre *</label>
                <input type="text" class="form-control" name="nombre" value="<?php echo $categoria['nombre']; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" name="descripcion" rows="3"><?php echo $categoria['descripcion']; ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Color</label>
                <input type="color" class="form-control form-control-color" name="color_hex" value="<?php echo $categoria['color_hex']; ?>">
            </div>

            <div class="text-end">
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-warning">Actualizar</button>
            </div>
        </form>
    </div>
</div>
    <?php include('../includes/footer.php'); ?>
    <script src="../assets/js/categorias.js"></script>
</body>
</html>
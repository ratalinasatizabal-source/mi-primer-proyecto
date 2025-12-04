<? require_once '../config/db.php';?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Categoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body class="container py-4">
    <?php include('../includes/header.php'); ?>

<h2>Nueva categoría</h2>

<form action="procesar.php" method="POST">
    <input type="hidden" name="accion" value="crear">

    <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control"></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Color</label>
        <input type="color" name="color_hex" class="form-control form-control-color">
    </div>

    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>
    <?php include('../includes/footer.php'); ?>
    <script src="../assets/js/categorias.js"></script>
</body>
</html>

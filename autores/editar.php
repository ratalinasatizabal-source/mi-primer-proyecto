<?php
include("../config/db.php");

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

// Obtener datos del autor usando prepared statement
$sql = "SELECT * FROM autores WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows != 1) {
    header("Location: index.php?error=Autor no encontrado");
    exit();
}

$autor = $resultado->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Autor - Biblioteca Personal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body class="bg-light">
    <?php include('../includes/header.php'); ?>
    
    <div class="container mt-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Inicio</a></li>
                <li class="breadcrumb-item"><a href="index.php">Autores</a></li>
                <li class="breadcrumb-item active">Editar Autor</li>
            </ol>
        </nav>

        <!-- Alertas -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-lg">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Editar Autor: <?= htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']) ?>
                </h4>
            </div>
            <div class="card-body">
                <form action="actualizar.php" method="POST" enctype="multipart/form-data" onsubmit="return validarFormularioAutor()">
                    <input type="hidden" name="id" value="<?php echo $autor['id']; ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?php echo htmlspecialchars($autor['nombre']); ?>" required maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Apellido *</label>
                            <input type="text" name="apellido" class="form-control" 
                                   value="<?php echo htmlspecialchars($autor['apellido']); ?>" required maxlength="100">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nacionalidad</label>
                            <input type="text" name="nacionalidad" class="form-control" 
                                   value="<?php echo htmlspecialchars($autor['nacionalidad']); ?>" maxlength="50">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" 
                                   value="<?php echo $autor['fecha_nacimiento']; ?>"
                                   max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Biografía</label>
                        <textarea name="biografia" class="form-control" rows="4" maxlength="1000"><?php echo htmlspecialchars($autor['biografia']); ?></textarea>
                        <div class="form-text">
                            <span id="contador-biografia"><?= strlen($autor['biografia']) ?></span>/1000 caracteres
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Foto Actual</label>
                        <div class="current-image text-center mb-3">
                            <?php if (!empty($autor['foto_url'])): ?>
                                <img src="imagenes/<?php echo htmlspecialchars($autor['foto_url']); ?>" 
                                     class="preview-image rounded" 
                                     alt="Foto actual" 
                                     style="max-width: 200px;"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjNkM3NTdEIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiNGRkZGRkYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj48dHNwYW4+8J+RqPCfkYg8L3RzcGFuPjwvdGV4dD48L3N2Zz4='">
                                <div class="mt-2">
                                    <small class="text-muted"><?= htmlspecialchars($autor['foto_url']) ?></small>
                                </div>
                            <?php else: ?>
                                <div class="bg-secondary p-4 text-center text-light rounded" style="max-width: 200px;">
                                    <i class="fas fa-user fa-3x"></i>
                                    <p class="mb-0 small mt-2">Sin imagen</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <label class="form-label fw-bold">Cambiar Imagen</label>
                        <input type="file" name="foto_url" class="form-control" accept="image/*" 
                               onchange="previewImage(this, 'previewNuevaImagen')">
                        <div class="form-text">Dejar vacío para mantener la imagen actual. Formatos: JPG, PNG, GIF, SVG (max 2MB)</div>
                        
                        <!-- Vista previa de nueva imagen -->
                        <div class="mt-3 text-center">
                            <img id="previewNuevaImagen" class="preview-image" style="display:none; max-width: 200px;" 
                                 src="#" alt="Vista previa de la nueva imagen">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Actualizar Autor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    
    <script>
    // Contador de caracteres para biografía
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.querySelector('textarea[name="biografia"]');
        const contador = document.getElementById('contador-biografia');
        
        textarea.addEventListener('input', function() {
            contador.textContent = this.value.length;
        });
    });
    </script>
</body>
</html>

<?php
$conexion->close();
?>
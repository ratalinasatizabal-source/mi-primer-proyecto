<?php 
include("../config/db.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Autor - Biblioteca Personal</title>
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
                <li class="breadcrumb-item active">Agregar Autor</li>
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
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Agregar Autor
                </h4>
            </div>
            <div class="card-body">
                <form action="procesar.php" method="POST" enctype="multipart/form-data" onsubmit="return validarFormularioAutor()">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" required maxlength="100" 
                                   placeholder="Ingrese el nombre del autor">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Apellido *</label>
                            <input type="text" name="apellido" class="form-control" required maxlength="100"
                                   placeholder="Ingrese el apellido del autor">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nacionalidad</label>
                            <input type="text" name="nacionalidad" class="form-control" maxlength="50"
                                   placeholder="Ej: Colombiano, Mexicano, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control"
                                   max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Biografía</label>
                        <textarea name="biografia" class="form-control" rows="4" maxlength="1000" 
                                  placeholder="Escribe una breve biografía del autor..."></textarea>
                        <div class="form-text">
                            <span id="contador-biografia">0</span>/1000 caracteres
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Foto del Autor</label>
                        <input type="file" name="foto_url" class="form-control" accept="image/*" 
                               onchange="previewImage(this, 'previewNuevaImagen')">
                        <div class="form-text">
                            Formatos aceptados: JPG, PNG, GIF, SVG. Tamaño máximo: 2MB
                        </div>
                        
                        <!-- Vista previa de imagen -->
                        <div class="mt-3 text-center">
                            <img id="previewNuevaImagen" class="preview-image" style="display:none; max-width: 200px;" 
                                 src="#" alt="Vista previa de la imagen">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Volver a Autores
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Guardar Autor
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

    function validarFormularioAutor() {
        const nombre = document.querySelector('input[name="nombre"]').value.trim();
        const apellido = document.querySelector('input[name="apellido"]').value.trim();
        const archivo = document.querySelector('input[name="foto_url"]').files[0];
        
        // Validar campos obligatorios
        if (nombre === '' || apellido === '') {
            mostrarAlerta('Por favor, complete los campos obligatorios (Nombre y Apellido)', 'danger');
            return false;
        }
        
        // Validar imagen si se seleccionó una
        if (archivo) {
            const errores = validarImagen(archivo);
            if (errores.length > 0) {
                mostrarAlerta(errores.join(', '), 'danger');
                return false;
            }
        }
        
        return true;
    }
    </script>
</body>
</html>
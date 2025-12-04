<?php
require_once '../config/db.php';

// Obtener autores y categorías para los select
$autores = $conexion->query("SELECT id, CONCAT(nombre, ' ', apellido) AS nombre_completo FROM autores ORDER BY nombre ASC");
$categorias = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Libro - Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/crear.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="fas fa-book-open me-2"></i>Mi Biblioteca
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-primary mb-0">
                    <i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Libro
                </h1>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                </a>
            </div>

            <form action="procesar.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="crear">

                <!-- Información Básica -->
                <div class="form-section">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-info-circle me-2"></i>Información Básica
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Título del Libro *</label>
                            <input type="text" name="titulo" class="form-control" placeholder="Ingrese el título del libro" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ISBN</label>
                            <input type="text" name="isbn" class="form-control" placeholder="Ej: 978-1234567890" maxlength="13">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Autor *</label>
                            <select name="autor_id" class="form-select" required>
                                <option value="">Seleccione un autor</option>
                                <?php while ($autor = $autores->fetch_assoc()): ?>
                                    <option value="<?php echo $autor['id']; ?>">
                                        <?php echo htmlspecialchars($autor['nombre_completo']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Categoría *</label>
                            <select name="categoria_id" class="form-select" required>
                                <option value="">Seleccione una categoría</option>
                                <?php while ($categoria = $categorias->fetch_assoc()): ?>
                                    <option value="<?php echo $categoria['id']; ?>">
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Detalles Adicionales -->
                <div class="form-section">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-book-open me-2"></i>Detalles Adicionales
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Año de Publicación</label>
                            <input type="number" name="año_publicacion" class="form-control" 
                                   placeholder="Ej: 2023" min="1000" max="<?php echo date('Y'); ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Editorial</label>
                            <input type="text" name="editorial" class="form-control" placeholder="Nombre de la editorial">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Idioma</label>
                            <input type="text" name="idioma" class="form-control" placeholder="Ej: Español">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Precio</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="precio" class="form-control" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Stock *</label>
                            <input type="number" name="stock" class="form-control" value="1" min="0" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Estado *</label>
                            <select name="estado" class="form-select" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Portada y Descripción -->
                <div class="form-section">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-image me-2"></i>Portada y Descripción
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Portada del Libro</label>
                            <input type="file" name="portada" class="form-control" accept="image/*" 
                                   onchange="previewImage(this)">
                            <div class="mt-2">
                                <img id="imagePreview" class="preview-image" style="display:none;" 
                                     src="#" alt="Vista previa de la portada">
                            </div>
                            <small class="text-muted">Formatos aceptados: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="5" 
                                      placeholder="Ingrese una descripción del libro..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="index.php" class="btn btn-secondary me-md-2">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Libro
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php
// Cerrar conexión
$conexion->close();
?>
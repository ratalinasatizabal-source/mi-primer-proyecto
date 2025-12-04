<?php
require_once '../config/db.php';

// Obtener lista de libros disponibles para prestar
$libros = $conexion->query("SELECT id, titulo, isbn FROM libros WHERE stock > 0 ORDER BY titulo ASC");
?>
<?php include '../includes/header.php'; ?>

<div class="form-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-primary mb-0">
            <i class="fas fa-handshake me-2"></i>Registrar Nuevo Préstamo
        </h1>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver a Préstamos
        </a>
    </div>

    <?php if ($libros->num_rows === 0): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>No hay libros disponibles</strong> - Todos los libros están prestados o sin stock.
        <a href="../libros/index.php" class="alert-link">Gestionar libros</a>
    </div>
    <?php endif; ?>

    <form action="procesar.php" method="POST">
        <input type="hidden" name="accion" value="crear">

        <!-- Selección de Libro -->
        <div class="form-section">
            <h5 class="text-primary mb-3">
                <i class="fas fa-book me-2"></i>Seleccionar Libro
            </h5>
            
            <?php if ($libros->num_rows > 0): ?>
            <div class="mb-3">
                <label class="form-label fw-bold">Libro a Prestar *</label>
                <select name="libro_id" class="form-select" required id="libroSelect">
                    <option value="">Seleccione un libro disponible</option>
                    <?php while ($libro = $libros->fetch_assoc()): ?>
                        <option value="<?= $libro['id'] ?>" data-isbn="<?= htmlspecialchars($libro['isbn']) ?>">
                            <?= htmlspecialchars($libro['titulo']) ?> (ISBN: <?= htmlspecialchars($libro['isbn']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No hay libros disponibles para préstamo en este momento.
            </div>
            <?php endif; ?>
        </div>

        <!-- Información del Prestatario -->
        <div class="form-section">
            <h5 class="text-primary mb-3">
                <i class="fas fa-user me-2"></i>Información del Prestatario
            </h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nombre Completo *</label>
                    <input type="text" name="nombre_prestamista" class="form-control" 
                           placeholder="Ingrese el nombre del prestatario" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Email *</label>
                    <input type="email" name="email_prestamista" class="form-control" 
                           placeholder="ejemplo@correo.com" required>
                </div>
            </div>
        </div>

        <!-- Fechas del Préstamo -->
        <div class="form-section">
            <h5 class="text-primary mb-3">
                <i class="fas fa-calendar-alt me-2"></i>Fechas del Préstamo
            </h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Fecha del Préstamo *</label>
                    <input type="date" name="fecha_prestamo" class="form-control" 
                           value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Fecha de Devolución Esperada *</label>
                    <input type="date" name="fecha_devolucion_esperada" class="form-control" 
                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        La fecha debe ser posterior a hoy
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="form-section">
            <h5 class="text-primary mb-3">
                <i class="fas fa-sticky-note me-2"></i>Información Adicional
            </h5>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Notas u Observaciones</label>
                <textarea name="notas" class="form-control" rows="4" 
                          placeholder="Observaciones sobre el préstamo, condiciones especiales, etc."></textarea>
                <div class="form-text">
                    Opcional: información adicional sobre el préstamo
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="index.php" class="btn btn-secondary me-md-2">
                <i class="fas fa-times me-2"></i>Cancelar
            </a>
            <?php if ($libros->num_rows > 0): ?>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Registrar Préstamo
            </button>
            <?php else: ?>
            <button type="button" class="btn btn-primary" disabled>
                <i class="fas fa-save me-2"></i>Registrar Préstamo
            </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de fecha de devolución
    const fechaDevolucion = document.querySelector('input[name="fecha_devolucion_esperada"]');
    const fechaPrestamo = document.querySelector('input[name="fecha_prestamo"]');
    
    fechaPrestamo.addEventListener('change', function() {
        const minDate = new Date(this.value);
        minDate.setDate(minDate.getDate() + 1);
        fechaDevolucion.min = minDate.toISOString().split('T')[0];
        
        if (fechaDevolucion.value && new Date(fechaDevolucion.value) <= new Date(this.value)) {
            fechaDevolucion.value = minDate.toISOString().split('T')[0];
        }
    });
});
</script>

<?php
$libros->free();
$conexion->close();
?>
<?php include '../includes/footer.php'; ?>
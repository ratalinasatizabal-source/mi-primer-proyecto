<?php
require_once '../config/db.php';

// Obtener préstamos activos para sugerencias
$prestamos_activos = [];
$result = $conexion->query("
    SELECT p.id, l.titulo, p.nombre_prestamista, p.fecha_devolucion_esperada, l.isbn
    FROM prestamos p 
    JOIN libros l ON p.libro_id = l.id 
    WHERE p.estado = 'activo'
    ORDER BY p.fecha_devolucion_esperada ASC
");

if ($result && $result->num_rows > 0) {
    while ($prestamo = $result->fetch_assoc()) {
        $prestamos_activos[] = $prestamo;
    }
    $result->free();
}
?>
<?php include '../includes/header.php'; ?>

<div class="form-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-success mb-0">
            <i class="fas fa-undo me-2"></i>Registrar Devolución de Libro
        </h1>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver a Préstamos
        </a>
    </div>

    <!-- Lista de préstamos activos -->
    <?php if (!empty($prestamos_activos)): ?>
    <div class="mb-4">
        <h5 class="text-primary mb-3">
            <i class="fas fa-list me-2"></i>Préstamos Activos Disponibles
        </h5>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Libro</th>
                        <th>Prestatario</th>
                        <th>Devolución Esperada</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prestamos_activos as $prestamo): 
                        $fecha_esperada = new DateTime($prestamo['fecha_devolucion_esperada']);
                        $hoy = new DateTime();
                        $diferencia = $hoy->diff($fecha_esperada);
                        $dias_restantes = $diferencia->days;
                        
                        if ($fecha_esperada < $hoy) {
                            $estado_clase = 'bg-danger';
                            $estado_texto = 'Vencido';
                            $dias_texto = 'Vencido hace ' . $dias_restantes . ' días';
                            $fila_clase = 'urgente';
                        } elseif ($dias_restantes <= 2) {
                            $estado_clase = 'bg-warning';
                            $estado_texto = 'Urgente';
                            $dias_texto = $dias_restantes . ' días';
                            $fila_clase = 'proximo';
                        } else {
                            $estado_clase = 'bg-success';
                            $estado_texto = 'En plazo';
                            $dias_texto = $dias_restantes . ' días';
                            $fila_clase = 'normal';
                        }
                    ?>
                    <tr class="prestamo-item <?= $fila_clase ?>" 
                        data-prestamo-id="<?= $prestamo['id'] ?>"
                        onclick="seleccionarPrestamo(<?= $prestamo['id'] ?>, '<?= htmlspecialchars(addslashes($prestamo['titulo'])) ?>')">
                        <td><strong><?= $prestamo['id'] ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($prestamo['titulo']) ?></strong>
                            <br><small class="text-muted">ISBN: <?= htmlspecialchars($prestamo['isbn']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($prestamo['nombre_prestamista']) ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])) ?>
                            <br><small class="text-muted"><?= $dias_texto ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $estado_clase ?>"><?= $estado_texto ?></span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-success" 
                                    onclick="event.stopPropagation(); seleccionarPrestamo(<?= $prestamo['id'] ?>, '<?= htmlspecialchars(addslashes($prestamo['titulo'])) ?>')">
                                <i class="fas fa-check me-1"></i>Seleccionar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        No hay préstamos activos en este momento.
    </div>
    <?php endif; ?>

    <!-- Formulario de devolución -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-clipboard-check me-2"></i>Formulario de Devolución
            </h5>
        </div>
        <div class="card-body">
            <form action="procesar.php" method="POST">
                <input type="hidden" name="accion" value="devolver">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_prestamo" class="form-label fw-bold">ID del Préstamo *</label>
                        <input type="number" class="form-control" id="id_prestamo" name="id" required 
                               placeholder="Ingresa el ID del préstamo o selecciona uno de la lista">
                        <div class="form-text">
                            <i class="fas fa-mouse-pointer me-1"></i>
                            Haz clic en un préstamo de la lista para completar automáticamente
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Libro Seleccionado</label>
                        <div id="libroInfo" class="form-control bg-light" style="height: auto; min-height: 38px;">
                            <span class="text-muted">Ningún préstamo seleccionado</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Observaciones de la Devolución</label>
                    <textarea name="notas_devolucion" class="form-control" rows="3" 
                              placeholder="Estado del libro, observaciones, etc."></textarea>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="index.php" class="btn btn-secondary me-md-2">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                    <?php if (!empty($prestamos_activos)): ?>
                    <button type="submit" class="btn btn-success" id="btnDevolver" disabled>
                        <i class="fas fa-check me-1"></i>Registrar Devolución
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn btn-success" disabled>
                        <i class="fas fa-check me-1"></i>Registrar Devolución
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function seleccionarPrestamo(id, titulo) {
    // Actualizar campo ID
    document.getElementById('id_prestamo').value = id;
    
    // Actualizar información del libro
    document.getElementById('libroInfo').innerHTML = 
        `<strong>${titulo}</strong> (Préstamo #${id})`;
    
    // Habilitar botón
    document.getElementById('btnDevolver').disabled = false;
    
    // Resaltar fila seleccionada
    document.querySelectorAll('.prestamo-item').forEach(item => {
        item.classList.remove('selected');
    });
    document.querySelector(`[data-prestamo-id="${id}"]`).classList.add('selected');
    
    // Scroll al formulario
    document.querySelector('.card').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
}

// Validación manual del ID
document.getElementById('id_prestamo').addEventListener('input', function() {
    const btnDevolver = document.getElementById('btnDevolver');
    const libroInfo = document.getElementById('libroInfo');
    
    if (this.value.trim() !== '') {
        btnDevolver.disabled = false;
        libroInfo.innerHTML = `<strong>Préstamo #${this.value}</strong>`;
        
        // Remover selección visual
        document.querySelectorAll('.prestamo-item').forEach(item => {
            item.classList.remove('selected');
        });
    } else {
        btnDevolver.disabled = true;
        libroInfo.innerHTML = '<span class="text-muted">Ningún préstamo seleccionado</span>';
    }
});

// Confirmación antes de enviar
document.querySelector('form').addEventListener('submit', function(e) {
    if (!confirm('¿Estás seguro de registrar la devolución de este libro?')) {
        e.preventDefault();
    }
});
</script>

<?php
$conexion->close();
?>
<?php include '../includes/footer.php'; ?>
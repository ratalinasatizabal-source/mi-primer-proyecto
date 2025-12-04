<?php
require_once '../config/db.php';

$query = "
SELECT p.id, p.nombre_prestamista, p.email_prestamista, p.fecha_prestamo, 
       p.fecha_devolucion_esperada, p.fecha_devolucion_real, p.estado, p.notas,
       l.titulo AS libro_titulo, l.id AS libro_id
FROM prestamos p
LEFT JOIN libros l ON p.libro_id = l.id
ORDER BY p.fecha_prestamo DESC
";

$result = $conexion->query($query);

if (!$result) {
    die("Error en la consulta: " . $conexion->error);
}
?>
<?php include '../includes/header.php'; ?>

<!-- Header con Estadísticas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="p-4 bg-white rounded shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 text-primary mb-2">
                        <i class="fas fa-handshake me-2"></i>Gestión de Préstamos
                    </h1>
                    <p class="text-muted mb-0">Administra los préstamos y devoluciones de libros</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php
                    $stats_query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                        SUM(CASE WHEN estado = 'devuelto' THEN 1 ELSE 0 END) as devueltos,
                        SUM(CASE WHEN estado = 'activo' AND fecha_devolucion_esperada < CURDATE() THEN 1 ELSE 0 END) as vencidos
                        FROM prestamos";
                    $stats_result = $conexion->query($stats_query);
                    $stats = $stats_result->fetch_assoc();
                    $stats_result->free();
                    ?>
                    <div class="d-flex justify-content-end gap-3">
                        <div class="text-center">
                            <h4 class="mb-0 text-primary"><?php echo $stats['total']; ?></h4>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="text-center">
                            <h4 class="mb-0 text-success"><?php echo $stats['activos']; ?></h4>
                            <small class="text-muted">Activos</small>
                        </div>
                        <div class="text-center">
                            <h4 class="mb-0 text-warning"><?php echo $stats['devueltos']; ?></h4>
                            <small class="text-muted">Devueltos</small>
                        </div>
                        <div class="text-center">
                            <h4 class="mb-0 text-danger"><?php echo $stats['vencidos']; ?></h4>
                            <small class="text-muted">Vencidos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barra de Acciones -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="btn-group" role="group">
                            <a href="crear.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i>Nuevo Préstamo
                            </a>
                            <a href="devolver.php" class="btn btn-success">
                                <i class="fas fa-undo me-2"></i>Registrar Devolución
                            </a>
                            <a href="../index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Inicio
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end gap-2">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary vista-activa" id="btn_vista_lista" data-vista="lista">
                                    <i class="fas fa-list me-2"></i>Vista Lista
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="btn_vista_cuadricula" data-vista="cuadricula">
                                    <i class="fas fa-grip me-2"></i>Vista Cuadrícula
                                </button>
                            </div>
                            <button class="btn btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosAvanzados">
                                <i class="fas fa-filter me-2"></i>Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtros Avanzados -->
                <div class="collapse mt-3" id="filtrosAvanzados">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select class="form-select filtro" data-filtro="estado">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activo</option>
                                <option value="devuelto">Devuelto</option>
                                <option value="vencido">Vencido</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Préstamo</label>
                            <input type="date" class="form-control filtro" data-filtro="fecha_prestamo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Devolución</label>
                            <input type="date" class="form-control filtro" data-filtro="fecha_devolucion">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vista Lista (Tabla) -->
<div id="vista_lista" class="table-responsive">
    <table class="table table-hover table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th width="20%">Libro</th>
                <th width="15%">Prestatario</th>
                <th width="10%">Fecha Préstamo</th>
                <th width="12%">Devolución Esperada</th>
                <th width="12%">Devolución Real</th>
                <th width="10%">Estado</th>
                <th width="21%" class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result->num_rows > 0) {
                $result->data_seek(0);
                while ($prestamo = $result->fetch_assoc()): 
                    // Determinar estado y clases
                    $estado = $prestamo['estado'];
                    $fecha_esperada = new DateTime($prestamo['fecha_devolucion_esperada']);
                    $hoy = new DateTime();
                    
                    // Calcular días antes de cambiar el estado
                    $dias_texto = '';
                    if ($estado == 'activo') {
                        $diferencia = $hoy->diff($fecha_esperada);
                        $dias_restantes = $diferencia->days;
                        if ($fecha_esperada < $hoy) {
                            $dias_texto = 'Vencido hace ' . $dias_restantes . ' días';
                            $estado = 'vencido';
                        } else {
                            $dias_texto = $dias_restantes . ' días restantes';
                        }
                    }
                    
                    $estado_clase = match($estado) {
                        'activo' => 'estado-activo',
                        'devuelto' => 'estado-devuelto',
                        'vencido' => 'estado-vencido',
                        default => 'bg-secondary'
                    };
                    
                    $estado_texto = ucfirst($estado);
            ?>
            <tr class="prestamo-fila" 
                data-estado="<?= htmlspecialchars($estado) ?>"
                data-fecha-prestamo="<?= $prestamo['fecha_prestamo'] ?>"
                data-fecha-devolucion="<?= $prestamo['fecha_devolucion_esperada'] ?>">
                
                <td>
                    <strong><?= htmlspecialchars($prestamo['libro_titulo']) ?></strong>
                    <?php if ($dias_texto): ?>
                        <br><small class="text-muted"><?= $dias_texto ?></small>
                    <?php endif; ?>
                </td>
                
                <td>
                    <div>
                        <strong><?= htmlspecialchars($prestamo['nombre_prestamista']) ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($prestamo['email_prestamista']) ?></small>
                    </div>
                </td>
                
                <td>
                    <span class="badge bg-primary">
                        <?= date('d/m/Y', strtotime($prestamo['fecha_prestamo'])) ?>
                    </span>
                </td>
                
                <td>
                    <?php
                    $clase_fecha = 'bg-info';
                    if ($estado == 'vencido') {
                        $clase_fecha = 'bg-danger';
                    } elseif ($estado == 'activo') {
                        $diferencia_dias = $hoy->diff($fecha_esperada);
                        $dias_restantes = $diferencia_dias->days;
                        if ($dias_restantes <= 2) {
                            $clase_fecha = 'fecha-urgente';
                        } elseif ($dias_restantes <= 7) {
                            $clase_fecha = 'fecha-proxima';
                        }
                    }
                    ?>
                    <span class="badge <?= $clase_fecha ?>">
                        <?= date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])) ?>
                    </span>
                </td>
                
                <td>
                    <?php if ($prestamo['fecha_devolucion_real']): ?>
                        <span class="badge bg-success">
                            <?= date('d/m/Y', strtotime($prestamo['fecha_devolucion_real'])) ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Pendiente</span>
                    <?php endif; ?>
                </td>
                
                <td>
                    <span class="badge <?= $estado_clase ?>">
                        <?= $estado_texto ?>
                    </span>
                </td>
                
                <td class="text-center">
                    <div class="btn-group-vertical btn-group-sm" role="group">
                        <?php if ($estado == 'activo'): ?>
                            <a href="procesar.php?accion=devolver&id=<?= $prestamo['id'] ?>" 
                               class="btn btn-success btn-action" 
                               title="Registrar Devolución"
                               onclick="return confirm('¿Registrar devolución de este libro?')">
                                <i class="fas fa-undo me-1"></i>Devolver
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($prestamo['notas']): ?>
                            <button type="button" class="btn btn-info btn-action" 
                                    data-bs-toggle="tooltip" 
                                    title="<?= htmlspecialchars($prestamo['notas']) ?>">
                                <i class="fas fa-sticky-note me-1"></i>Notas
                            </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endwhile; 
            } else { ?>
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="text-muted">
                        <i class="fas fa-handshake fa-3x mb-3"></i>
                        <h4>No hay préstamos registrados</h4>
                        <p class="mb-3">Comienza registrando tu primer préstamo</p>
                        <a href="crear.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Registrar Primer Préstamo
                        </a>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Vista Cuadrícula -->
<div id="vista_cuadricula" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" style="display: none;">
    <?php 
    if ($result->num_rows > 0) {
        $result->data_seek(0);
        while ($prestamo = $result->fetch_assoc()):
            // Determinar estado y clases (misma lógica que arriba)
            $estado = $prestamo['estado'];
            $fecha_esperada = new DateTime($prestamo['fecha_devolucion_esperada']);
            $hoy = new DateTime();
            
            // Calcular días antes de cambiar el estado
            $dias_texto = '';
            if ($estado == 'activo') {
                $diferencia = $hoy->diff($fecha_esperada);
                $dias_restantes = $diferencia->days;
                if ($fecha_esperada < $hoy) {
                    $dias_texto = 'Vencido hace ' . $dias_restantes . ' días';
                    $estado = 'vencido';
                } else {
                    $dias_texto = $dias_restantes . ' días restantes';
                }
            }
            
            $estado_clase = match($estado) {
                'activo' => 'estado-activo',
                'devuelto' => 'estado-devuelto',
                'vencido' => 'estado-vencido',
                default => 'bg-secondary'
            };
            
            $estado_texto = ucfirst($estado);
    ?>
    <div class="col prestamo-card" 
         data-estado="<?= htmlspecialchars($estado) ?>"
         data-fecha-prestamo="<?= $prestamo['fecha_prestamo'] ?>"
         data-fecha-devolucion="<?= $prestamo['fecha_devolucion_esperada'] ?>">
        <div class="card prestamo-card h-100">
            <div class="card-header bg-transparent border-bottom-0">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge <?= $estado_clase ?>"><?= $estado_texto ?></span>
                    <small class="text-muted">#<?= $prestamo['id'] ?></small>
                </div>
            </div>
            <div class="card-body">
                <h6 class="card-title fw-bold text-primary"><?= htmlspecialchars($prestamo['libro_titulo']) ?></h6>
                
                <div class="mb-3">
                    <p class="mb-1">
                        <i class="fas fa-user me-2 text-muted"></i>
                        <strong><?= htmlspecialchars($prestamo['nombre_prestamista']) ?></strong>
                    </p>
                    <p class="mb-1 small text-muted">
                        <i class="fas fa-envelope me-2"></i>
                        <?= htmlspecialchars($prestamo['email_prestamista']) ?>
                    </p>
                </div>
                
                <div class="row small text-center">
                    <div class="col-6">
                        <div class="border rounded p-2 bg-light">
                            <strong>Préstamo</strong><br>
                            <?= date('d/m/Y', strtotime($prestamo['fecha_prestamo'])) ?>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 bg-light">
                            <strong>Devolución</strong><br>
                            <?= date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])) ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($dias_texto): ?>
                    <div class="mt-2 text-center">
                        <small class="badge bg-warning text-dark"><?= $dias_texto ?></small>
                    </div>
                <?php endif; ?>
                
                <?php if ($prestamo['notas']): ?>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-sticky-note me-1"></i>
                            <?= substr($prestamo['notas'], 0, 50) . (strlen($prestamo['notas']) > 50 ? '...' : '') ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-transparent border-0">
                <div class="btn-group w-100" role="group">
                    <?php if ($estado == 'activo'): ?>
                        <a href="procesar.php?accion=devolver&id=<?= $prestamo['id'] ?>" 
                           class="btn btn-success btn-sm"
                           onclick="return confirm('¿Registrar devolución de este libro?')">
                            <i class="fas fa-undo"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endwhile; 
    } else { ?>
    <div class="col-12">
        <div class="text-center py-5">
            <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No hay préstamos registrados</h4>
            <p class="text-muted">Comienza registrando tu primer préstamo</p>
            <a href="crear.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Registrar Primer Préstamo
            </a>
        </div>
    </div>
    <?php } ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar vistas
    inicializarVistas({
        btnLista: document.getElementById('btn_vista_lista'),
        btnCuadricula: document.getElementById('btn_vista_cuadricula'),
        vistaLista: document.getElementById('vista_lista'),
        vistaCuadricula: document.getElementById('vista_cuadricula')
    });

    // Inicializar filtros
    inicializarFiltros({
        inputBusqueda: null,
        filtros: [
            { elemento: document.querySelector('[data-filtro="estado"]'), atributo: 'data-estado', tipo: 'text' },
            { elemento: document.querySelector('[data-filtro="fecha_prestamo"]'), atributo: 'data-fecha-prestamo', tipo: 'text' },
            { elemento: document.querySelector('[data-filtro="fecha_devolucion"]'), atributo: 'data-fecha-devolucion', tipo: 'text' }
        ],
        elementos: document.querySelectorAll('.prestamo-fila, .prestamo-card'),
        atributos: []
    });
});
</script>

<?php 
$result->free();
$conexion->close(); 
?>
<?php include '../includes/footer.php'; ?>
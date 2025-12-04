<?php
include("../config/db.php");

// Consultar categorías
$sql = "SELECT * FROM categorias ORDER BY id DESC";
$resultado = $conexion->query($sql);

// Obtener estadísticas por categoría (número de libros y valor total)
$queryEstadisticas = "
    SELECT 
        c.id,
        c.nombre,
        c.color_hex,
        COUNT(l.id) as total_libros,
        COALESCE(SUM(l.precio), 0) as valor_total,
        COALESCE(AVG(l.precio), 0) as precio_promedio,
        SUM(l.stock) as stock_total
    FROM categorias c
    LEFT JOIN libros l ON c.id = l.categoria_id
    GROUP BY c.id, c.nombre, c.color_hex
    ORDER BY total_libros DESC
";
$resultEstadisticas = $conexion->query($queryEstadisticas);
// Obtener todas las categorías
$query = "SELECT * FROM categorias ORDER BY nombre ASC";
$result = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <title>Categorías</title>
</head>
<body class="py-0">
    <?php include('../includes/header.php'); ?>

<div class="p-4 bg-white rounded shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 text-primary mb-0">
            <i class="fas fa-tags me-2"></i>Gestión de Categorías
        </h1>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Nueva Categoría
        </a>
    </div>
    <p class="text-muted mb-0">Administra las categorías de tu biblioteca personal</p>
</div>

<!-- Estadísticas por Categoría -->
<div class="row mb-4">
    <?php if ($resultEstadisticas && $resultEstadisticas->num_rows > 0): ?>
        <?php while ($stats = $resultEstadisticas->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 shadow-sm border-0" style="border-left: 4px solid <?php echo htmlspecialchars($stats['color_hex'] ?? '#6c757d'); ?> !important;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title text-primary mb-0">
                                <i class="fas fa-bookmark me-2"></i><?php echo htmlspecialchars($stats['nombre']); ?>
                            </h5>
                            <span class="badge rounded-pill" style="background-color: <?php echo htmlspecialchars($stats['color_hex'] ?? '#6c757d'); ?>;">
                                <?php echo $stats['total_libros']; ?>
                            </span>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <i class="fas fa-book text-primary"></i>
                                    <div class="fw-bold"><?php echo $stats['total_libros']; ?></div>
                                    <small class="text-muted">Libros</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <i class="fas fa-boxes text-success"></i>
                                    <div class="fw-bold"><?php echo $stats['stock_total'] ?? 0; ?></div>
                                    <small class="text-muted">Stock Total</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-2 bg-light rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">
                                            <i class="fas fa-dollar-sign text-warning"></i> Valor Total
                                        </span>
                                        <span class="fw-bold text-success">
                                            $<?php echo number_format($stats['valor_total'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No hay categorías registradas aún.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Tabla de Categorías -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="fas fa-list me-2"></i>Listado de Categorías</h5>
        <input type="text" id="buscarCategoria" class="form-control w-25" placeholder="Buscar categoría...">
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Color</th>
                    <th>Libros</th>
                    <th>Valor Total</th>
                    <th>Creado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaCategorias">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php 
                    // Obtener estadísticas de nuevo para la tabla
                    $statsArray = [];
                    if ($resultEstadisticas) {
                        $resultEstadisticas->data_seek(0);
                        while ($s = $resultEstadisticas->fetch_assoc()) {
                            $statsArray[$s['id']] = $s;
                        }
                    }
                    
                    while ($cat = $result->fetch_assoc()): 
                        $catStats = $statsArray[$cat['id']] ?? ['total_libros' => 0, 'valor_total' => 0];
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['id']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($cat['nombre']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($cat['descripcion'] ?? '—'); ?></td>
                            <td>
                                <span class="badge rounded-pill" 
                                      style="background-color: <?php echo htmlspecialchars($cat['color_hex'] ?? '#6c757d'); ?>; color: white;">
                                    <?php echo htmlspecialchars($cat['color_hex'] ?? '—'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo $catStats['total_libros']; ?></span>
                            </td>
                            <td>
                                <strong class="text-success">$<?php echo number_format($catStats['valor_total'], 2); ?></strong>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($cat['created_at'])); ?></td>
                            <td class="text-center">
                                <a href="editar.php?id=<?php echo $cat['id']; ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>Editar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No hay categorías registradas</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/categorias.js"></script>    
</body>
</html>
<?php
session_start();
require_once 'config/db.php';
// Obtener estadísticas básicas
// Total libros
$result = $conexion->query("SELECT COUNT(*) as total FROM libros");
$total_libros = $result ? $result->fetch_assoc()['total'] : 0;
if ($result) $result->free();
// Total autores
$result = $conexion->query("SELECT COUNT(*) as total FROM autores");
$total_autores = $result ? $result->fetch_assoc()['total'] : 0;
if ($result) $result->free();
// Préstamos activos
$result = $conexion->query("SELECT COUNT(*) as total FROM prestamos WHERE estado = 'activo'");
$prestamos_activos = $result ? $result->fetch_assoc()['total'] : 0;
if ($result) $result->free();
// Préstamos vencidos
$result = $conexion->query("SELECT COUNT(*) as total FROM prestamos WHERE estado = 'vencido'");
$prestamos_vencidos = $result ? $result->fetch_assoc()['total'] : 0;
if ($result) $result->free();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Personal - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/estilo.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-library">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-book-open me-2"></i>Mi Biblioteca
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="libros/index.php">
                            <i class="fas fa-book me-1"></i>Libros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="autores/index.php">
                            <i class="fas fa-users me-1"></i>Autores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categorias/index.php">
                            <i class="fas fa-tags me-1"></i>Categorias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prestamos/index.php">
                            <i class="fas fa-handshake me-1"></i>Préstamos
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="p-4 bg-white rounded shadow-sm">
                    <h1 class="h3 text-primary mb-2">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </h1>
                    <p class="text-muted mb-0">Bienvenido al sistema de gestión de tu biblioteca personal</p>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0"><?php echo $total_libros; ?></h2>
                                <p class="mb-0">Total Libros</p>
                            </div>
                            <div class="display-6">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-primary bg-opacity-25">
                        <a href="libros/index.php" class="text-white text-decoration-none small">
                            Ver detalles <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0"><?php echo $total_autores; ?></h2>
                                <p class="mb-0">Autores</p>
                            </div>
                            <div class="display-6">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-success bg-opacity-25">
                        <a href="autores/index.php" class="text-white text-decoration-none small">
                            Gestionar <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0"><?php echo $prestamos_activos; ?></h2>
                                <p class="mb-0">Préstamos Activos</p>
                            </div>
                            <div class="display-6">
                                <i class="fas fa-handshake"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-warning bg-opacity-25">
                        <a href="prestamos/index.php" class="text-dark text-decoration-none small">
                            Ver todos <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0"><?php echo $prestamos_vencidos; ?></h2>
                                <p class="mb-0">Vencidos</p>
                            </div>
                            <div class="display-6">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-danger bg-opacity-25">
                        <a href="prestamos/index.php?estado=vencido" class="text-white text-decoration-none small">
                            Revisar <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Acciones Rápidas -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="fas fa-bolt me-2"></i>Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="libros/crear.php" class="btn btn-outline-primary w-100 quick-action p-3 text-start">
                                    <i class="fas fa-plus-circle me-2"></i>
                                    <strong>Agregar Libro</strong>
                                    <small class="d-block text-muted">Nuevo libro a la colección</small>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="prestamos/crear.php" class="btn btn-outline-success w-100 quick-action p-3 text-start">
                                    <i class="fas fa-handshake me-2"></i>
                                    <strong>Registrar Préstamo</strong>
                                    <small class="d-block text-muted">Prestar un libro</small>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="autores/crear.php" class="btn btn-outline-info w-100 quick-action p-3 text-start">
                                    <i class="fas fa-user-plus me-2"></i>
                                    <strong>Agregar Autor</strong>
                                    <small class="d-block text-muted">Nuevo autor al sistema</small>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="libros/index.php" class="btn btn-outline-warning w-100 quick-action p-3 text-start">
                                    <i class="fas fa-search me-2"></i>
                                    <strong>Buscar Libros</strong>
                                    <small class="d-block text-muted">Explorar la colección</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimos Libros -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="fas fa-clock me-2"></i>Últimos Libros Agregados
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $result = $conexion->query("
                            SELECT l.titulo, l.isbn, l.stock, a.nombre, a.apellido 
                            FROM libros l 
                            LEFT JOIN autores a ON l.autor_id = a.id 
                            ORDER BY l.created_at DESC 
                            LIMIT 5
                        ");

                        if ($result && $result->num_rows > 0) {
                            while ($libro = $result->fetch_assoc()) {
                                $autor = $libro['nombre'] && $libro['apellido'] ? 
                                         $libro['nombre'] . ' ' . $libro['apellido'] : 'Autor no asignado';
                                $badge_class = $libro['stock'] > 0 ? 'bg-success' : 'bg-danger';
                                
                                echo "
                                <div class='d-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded recent-item'>
                                    <div>
                                        <h6 class='mb-1'>{$libro['titulo']}</h6>
                                        <p class='mb-0 text-muted small'>{$autor}</p>
                                    </div>
                                    <div class='text-end'>
                                        <span class='badge {$badge_class}'>Stock: {$libro['stock']}</span>
                                        <div class='text-muted small mt-1'>{$libro['isbn']}</div>
                                    </div>
                                </div>
                                ";
                            }
                            $result->free();
                        } else {
                            echo "
                            <div class='text-center py-4'>
                                <i class='fas fa-book fa-3x text-muted mb-3'></i>
                                <p class='text-muted'>No hay libros registrados aún</p>
                                <a href='libros/crear.php' class='btn btn-primary btn-sm'>
                                    <i class='fas fa-plus me-1'></i>Agregar Primer Libro
                                </a>
                            </div>
                            ";
                        }
                        ?>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="libros/index.php" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-list me-1"></i>Ver Todos los Libros
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximas Devoluciones -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0 text-primary">
                            <i class="fas fa-calendar-alt me-2"></i>Próximas Devoluciones
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $result = $conexion->query("
                            SELECT p.nombre_prestamista, p.fecha_devolucion_esperada, l.titulo 
                            FROM prestamos p 
                            JOIN libros l ON p.libro_id = l.id 
                            WHERE p.estado = 'activo' 
                            AND p.fecha_devolucion_esperada >= CURDATE()
                            ORDER BY p.fecha_devolucion_esperada ASC 
                            LIMIT 5
                        ");

                        if ($result && $result->num_rows > 0) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-hover">';
                            echo '<thead class="table-light">';
                            echo '<tr>';
                            echo '<th>Libro</th>';
                            echo '<th>Prestatario</th>';
                            echo '<th>Fecha Devolución</th>';
                            echo '<th>Días Restantes</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';

                            while ($devolucion = $result->fetch_assoc()) {
                                $fecha_esperada = new DateTime($devolucion['fecha_devolucion_esperada']);
                                $hoy = new DateTime();
                                $diferencia = $hoy->diff($fecha_esperada);
                                $dias_restantes = $diferencia->days;
                                $dias_restantes = $fecha_esperada >= $hoy ? $dias_restantes : -$dias_restantes;
                                
                                $badge_class = 'bg-success';
                                if ($dias_restantes < 0) {
                                    $badge_class = 'bg-danger';
                                    $texto_dias = 'Vencido';
                                } elseif ($dias_restantes == 0) {
                                    $badge_class = 'bg-warning';
                                    $texto_dias = 'Hoy';
                                } elseif ($dias_restantes <= 2) {
                                    $badge_class = 'bg-warning';
                                    $texto_dias = "$dias_restantes días";
                                } else {
                                    $texto_dias = "$dias_restantes días";
                                }

                                echo '<tr>';
                                echo '<td><strong>' . htmlspecialchars($devolucion['titulo']) . '</strong></td>';
                                echo '<td>' . htmlspecialchars($devolucion['nombre_prestamista']) . '</td>';
                                echo '<td>' . $fecha_esperada->format('d/m/Y') . '</td>';
                                echo '<td><span class="badge ' . $badge_class . '">' . $texto_dias . '</span></td>';
                                echo '</tr>';
                            }

                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                            $result->free();
                        } else {
                            echo '
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <p class="text-muted">No hay préstamos activos en este momento</p>
                            </div>
                            ';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6>Sistema de Biblioteca Personal</h6>
                    <p class="mb-0 small">Gestiona tu colección de libros de manera eficiente</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 small">
                        <i class="fas fa-code me-1"></i> 
                        Desarrollado con PHP, MySQL y Bootstrap 5
                    </p>
                    <p class="mb-0 small">
                        <i class="fas fa-calendar me-1"></i>
                        <?php echo date('Y'); ?> - Todos los derechos reservados
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tooltips
            var tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(function(el) {
                new bootstrap.Tooltip(el);
            });
        });
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php
// Cerrar conexión
$conexion->close();
?>
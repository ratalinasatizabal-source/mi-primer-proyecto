<?php 
include("../config/db.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autores - Biblioteca Personal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilo.css">
</head>
<body class="bg-light">
    <?php include('../includes/header.php'); ?>
    
    <div class="container mt-4">
        <!-- Header de la página -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 text-primary">
                    <i class="fas fa-users me-2"></i>Autores
                </h1>
                <p class="text-muted">Gestiona los autores de tu biblioteca</p>
            </div>
            <a href="crear.php" class='btn btn-success'>
                <i class="fas fa-plus me-1"></i>Nuevo Autor
            </a>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control" id="search_input" placeholder="Buscar por nombre, apellido o nacionalidad...">
                            <button class="btn btn-outline-secondary" type="button" id="btn_limpiar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosAvanzados">
                            <i class="fas fa-filter me-2"></i>Filtros
                        </button>
                    </div>
                </div>

                <!-- Filtros Avanzados -->
                <div class="collapse mt-3" id="filtrosAvanzados">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nacionalidad</label>
                            <input type="text" class="form-control filtro" data-filtro="nacionalidad" placeholder="Filtrar por nacionalidad...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ordenar por</label>
                            <select class="form-select" id="ordenar">
                                <option value="nombre">Nombre A-Z</option>
                                <option value="apellido">Apellido A-Z</option>
                                <option value="nacionalidad">Nacionalidad</option>
                                <option value="reciente">Más recientes</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Autores -->
        <div class="row" id="contenedor-autores">
            <?php
            $sql = "SELECT * FROM autores ORDER BY apellido, nombre";
            $resultado = $conexion->query($sql);

            if ($resultado->num_rows > 0) {
                while ($fila = $resultado->fetch_assoc()) {
                    $autor_id = $fila['id'];
                    $nombre_completo = htmlspecialchars($fila['nombre'] . ' ' . $fila['apellido']);
                    $nacionalidad = htmlspecialchars($fila['nacionalidad']);
                    $biografia = htmlspecialchars($fila['biografia']);
                    
                    // Contar libros del autor
                    $sql_libros = "SELECT COUNT(*) as total FROM libros WHERE autor_id = $autor_id";
                    $result_libros = $conexion->query($sql_libros);
                    $total_libros = $result_libros->fetch_assoc()['total'];
                    $result_libros->free();
                    ?>
                    <div class="col-md-4 mb-4 autor-card" 
                         data-nombre="<?= strtolower($fila['nombre'] . ' ' . $fila['apellido']) ?>"
                         data-apellido="<?= strtolower($fila['apellido']) ?>"
                         data-nacionalidad="<?= strtolower($fila['nacionalidad']) ?>">
                        <div class="card h-100 author-card">
                            
                            <!-- Imagen del autor - CORREGIDA -->
                            <?php if (!empty($fila['foto_url'])): ?>
                                <img src="imagenes/<?= htmlspecialchars($fila['foto_url']) ?>" 
                                     class="card-img-top author-image" 
                                     alt="<?= $nombre_completo ?>"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjNkM3NTdEIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiNGRkZGRkYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj48dHNwYW4+8J+RqPCfkYg8L3RzcGFuPjwvdGV4dD48L3N2Zz4='">
                            <?php else: ?>
                                <div class="card-img-top author-image bg-secondary d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user fa-4x text-light"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?= $nombre_completo ?></h5>
                                
                                <?php if (!empty($nacionalidad)): ?>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-flag me-1"></i><?= $nacionalidad ?>
                                        </small>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="card-text">
                                    <span class="badge bg-primary">
                                        <i class="fas fa-book me-1"></i><?= $total_libros ?> libro<?= $total_libros != 1 ? 's' : '' ?>
                                    </span>
                                </p>
                                
                                <?php if (!empty($biografia)): ?>
                                    <?php 
                                    $biografia_corta = strlen($biografia) > 100 ? substr($biografia, 0, 100) . '...' : $biografia;
                                    ?>
                                    <p class="card-text small text-muted"><?= $biografia_corta ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer bg-white">
                                <div class="btn-group w-100" role="group">
                                    <a href="ver.php?id=<?= $autor_id ?>" class="btn btn-outline-primary btn-sm" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="editar.php?id=<?= $autor_id ?>" class="btn btn-outline-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $autor_id ?>" 
                                       class="btn btn-outline-danger btn-sm" 
                                       title="Eliminar"
                                       onclick="return confirm('¿Estás seguro de eliminar al autor <?= addslashes($nombre_completo) ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='col-12'>";
                echo "   <div class='text-center py-5'>";
                echo "       <i class='fas fa-users fa-4x text-muted mb-3'></i>";
                echo "       <h4 class='text-muted'>No hay autores registrados</h4>";
                echo "       <p class='text-muted'>Comienza agregando el primer autor a tu biblioteca</p>";
                echo "       <a href='crear.php' class='btn btn-primary'>";
                echo "           <i class='fas fa-plus me-1'></i>Agregar Primer Autor";
                echo "       </a>";
                echo "   </div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    
    <script>
    // Filtrado y búsqueda para autores
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search_input');
        const filtroNacionalidad = document.querySelector('[data-filtro="nacionalidad"]');
        const btnLimpiar = document.getElementById('btn_limpiar');
        const ordenarSelect = document.getElementById('ordenar');
        const autorCards = document.querySelectorAll('.autor-card');

        function filtrarAutores() {
            const searchTerm = searchInput.value.toLowerCase();
            const filtroNac = filtroNacionalidad.value.toLowerCase();
            
            autorCards.forEach(card => {
                const nombre = card.getAttribute('data-nombre') || '';
                const apellido = card.getAttribute('data-apellido') || '';
                const nacionalidad = card.getAttribute('data-nacionalidad') || '';
                
                let coincide = true;
                
                // Búsqueda general
                if (searchTerm) {
                    coincide = nombre.includes(searchTerm) || 
                              apellido.includes(searchTerm) || 
                              nacionalidad.includes(searchTerm);
                }
                
                // Filtro por nacionalidad
                if (coincide && filtroNac) {
                    coincide = nacionalidad.includes(filtroNac);
                }
                
                card.style.display = coincide ? 'block' : 'none';
            });
        }

        // Event listeners
        searchInput.addEventListener('input', filtrarAutores);
        filtroNacionalidad.addEventListener('input', filtrarAutores);
        
        btnLimpiar.addEventListener('click', function() {
            searchInput.value = '';
            filtroNacionalidad.value = '';
            filtrarAutores();
        });

        // Ordenamiento
        ordenarSelect.addEventListener('change', function() {
            // Aquí podrías implementar ordenamiento AJAX si lo necesitas
            mostrarAlerta('Función de ordenamiento en desarrollo', 'info');
        });
    });
    </script>
</body>
</html>

<?php
$conexion->close();
?>
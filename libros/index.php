<?php
require_once '../config/db.php';

$query = "
SELECT l.id, l.titulo, l.isbn, l.stock, l.estado, l.portada_url, l.descripcion, l.año_publicacion, c.color_hex,
a.nombre AS autor_nombre, a.apellido AS autor_apellido, c.nombre AS categoria_nombre
FROM libros l
LEFT JOIN autores a ON l.autor_id = a.id
LEFT JOIN categorias c ON l.categoria_id = c.id
ORDER BY l.created_at DESC
";

$result = $conexion->query($query);

if (!$result) {
    die("Error en la consulta: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Libros - Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body class="bg-light">
<?php include('../includes/header.php'); ?>
    <div class="container mt-4">
        <!-- Header con Estadísticas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="p-4 bg-white rounded shadow-sm">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 text-primary mb-2">
                                <i class="fas fa-books me-2"></i>Gestión de Libros
                            </h1>
                            <p class="text-muted mb-0">Administra tu colección completa de libros</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <?php
                            $stats_query = "SELECT 
                                COUNT(*) as total,
                                SUM(CASE WHEN stock > 0 THEN 1 ELSE 0 END) as disponibles,
                                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as agotados
                                FROM libros";
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
                                    <h4 class="mb-0 text-success"><?php echo $stats['disponibles']; ?></h4>
                                    <small class="text-muted">Disponibles</small>
                                </div>
                                <div class="text-center">
                                    <h4 class="mb-0 text-danger"><?php echo $stats['agotados']; ?></h4>
                                    <small class="text-muted">Agotados</small>
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
                                        <i class="fas fa-plus-circle me-2"></i>Agregar Libro
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
                                            <i class="fas fa-grid me-2"></i>Vista Cuadrícula
                                        </button>
                                    </div>
                                    <button class="btn btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosAvanzados">
                                        <i class="fas fa-filter me-2"></i>Filtros
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de Búsqueda -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control" id="search_input" placeholder="Buscar por título, autor, ISBN o categoría...">
                                    <button class="btn btn-outline-secondary" type="button" id="btn_limpiar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Filtros Avanzados -->
                        <div class="collapse mt-3" id="filtrosAvanzados">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select filtro" data-filtro="categoria">
                                        <option value="">Todas las categorías</option>
                                        <?php
                                        $cat_result = $conexion->query("SELECT DISTINCT nombre FROM categorias ORDER BY nombre");
                                        while ($cat = $cat_result->fetch_assoc()) {
                                            echo "<option value='{$cat['nombre']}'>{$cat['nombre']}</option>";
                                        }
                                        $cat_result->free();
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select filtro" data-filtro="estado">
                                        <option value="">Todos los estados</option>
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Stock</label>
                                    <select class="form-select filtro" data-filtro="stock">
                                        <option value="">Todo el stock</option>
                                        <option value="disponible">Disponible</option>
                                        <option value="agotado">Agotado</option>
                                        <option value="bajo">Stock Bajo (<5)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Año Publicación</label>
                                    <input type="number" class="form-control filtro" data-filtro="año" placeholder="Ej: 2023" min="1900" max="2025">
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
                        <th width="25%">Título</th>
                        <th width="15%">Autor</th>
                        <th width="12%">Categoría</th>
                        <th width="10%">ISBN</th>
                        <th width="10%">Año</th>
                        <th width="10%">Estado</th>
                        <th width="10%">Stock</th>
                        <th width="20%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($libro = $result->fetch_assoc()):
                        // Botones de estado
                        $estado_btn_class = match ($libro['estado']) {
                            'activo' => 'btn-outline-warning',
                            'inactivo' => 'btn-outline-danger',
                            default => 'btn-outline-info',
                        };
                        $estado_texto = ucfirst($libro['estado']);

                        // Botones de stock
                        $stock_btn_class = $libro['stock'] > 0 ? 'btn-outline-success' : 'btn-outline-danger';
                        $stock_texto = $libro['stock'] > 0 ? "Stock: {$libro['stock']}" : "Agotado";
                        
                        $autor_completo = trim($libro['autor_nombre'] . ' ' . $libro['autor_apellido']);
                        $autor_display = $autor_completo ?: 'No Asignado';
                        
                        // Año de publicación - asegurar que sea número
                        $año_publicacion = $libro['año_publicacion'] ? intval($libro['año_publicacion']) : '';
                    ?>
                    <tr class="libro-fila" 
                        data-titulo="<?= htmlspecialchars(strtolower($libro['titulo'])) ?>"
                        data-autor="<?= htmlspecialchars(strtolower($autor_display)) ?>"
                        data-categoria="<?= htmlspecialchars(strtolower($libro['categoria_nombre'] ?: '')) ?>"
                        data-isbn="<?= htmlspecialchars(strtolower($libro['isbn'])) ?>"
                        data-estado="<?= htmlspecialchars($libro['estado']) ?>"
                        data-stock="<?= $libro['stock'] ?>"
                        data-año="<?= $año_publicacion ?>">
                        
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= $libro['portada_url'] ?: '../assets/imagen/default-book.png' ?>" 
                                     alt="Portada" class="rounded me-3" width="40" height="55" style="object-fit: cover;">
                                <div>
                                    <strong class="d-block"><?= htmlspecialchars($libro['titulo']) ?></strong>
                                    <small class="text-muted">ID: <?= $libro['id'] ?></small>
                                </div>
                            </div>
                        </td>
                        
                        <td><?= htmlspecialchars($autor_display) ?></td>
                        
                        <td>
                            <?php if ($libro['color_hex']): ?>
                                <button class="btn btn-sm text-white" style="background-color: <?= $libro['color_hex'] ?>; border-color: <?= $libro['color_hex'] ?>;">
                                    <?= htmlspecialchars($libro['categoria_nombre'] ?: 'N/A') ?>
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-dark">
                                    <?= htmlspecialchars($libro['categoria_nombre'] ?: 'N/A') ?>
                                </button>
                            <?php endif; ?>
                        </td>
                        
                        <td><button class="btn btn-sm btn-outline-dark"><code><?= htmlspecialchars($libro['isbn']) ?></code></button></td>
                        
                        <td>
                            <button class="btn btn-sm btn-outline-info">
                                <?= $año_publicacion ?>
                            </button>
                        </td>
                        
                        <td>
                            <?php
                            if ($libro['estado'] === 'activo') {
                                echo '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Activo</span>';
                            } else {
                                echo '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Inactivo</span>';
                            }
                            ?>
                        </td>
                        
                        <td>
                            <button class="btn btn-sm <?= $stock_btn_class ?> btn-action">
                                <i class="fas fa-box me-1"></i><?= $stock_texto ?>
                            </button>
                        </td>
                        
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="ver.php?id=<?= $libro['id'] ?>" class="btn btn-info btn-action" title="Ver detalle">
                                    <i class="fas fa-eye me-1"></i>Ver
                                </a>
                                <a href="editar.php?id=<?= $libro['id'] ?>" class="btn btn-warning btn-action" title="Editar">
                                    <i class="fas fa-edit me-1"></i>Editar
                                </a>
                                <a href="../prestamos/crear.php?libro_id=<?= $libro['id'] ?>" class="btn btn-success btn-action" title="Prestar">
                                    <i class="fas fa-handshake me-1"></i>Prestar
                                </a>
                                <?php
                                // Asumiendo que $libro contiene ['id'] y ['estado']
                                $estado_actual = $libro['estado'] ?? 'inactivo';
                                $btn_class = $estado_actual === 'activo' ? 'btn-warning' : 'btn-secondary';
                                ?>
                                <button type="button" class="btn <?= $btn_class ?> btn-sm"
                                    onclick="if(confirm('¿Deseas cambiar el estado de este libro?')) { window.location.href='procesar.php?accion=toggle_estado&id=<?= $libro['id'] ?>'; }"
                                    title="Cambiar estado">
                                    <i class="fas fa-exchange-alt me-1"></i>Estado
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <?php if ($result->num_rows === 0): ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No hay libros registrados</h4>
                <p class="text-muted">Comienza agregando tu primer libro a la colección</p>
                <a href="crear.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Agregar Primer Libro
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Vista Cuadrícula -->
        <div id="vista_cuadricula" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" style="display: none;">
            <?php 
            // Reiniciar el puntero del resultado para la vista cuadrícula
            $result->data_seek(0);
            while ($libro = $result->fetch_assoc()):
                $estado_btn_class = match ($libro['estado']) {
                    'activo' => 'btn-outline-warning',
                    'inactivo' => 'btn-outline-danger',
                    default => 'btn-outline-info',
                };
                
                $stock_btn_class = $libro['stock'] > 0 ? 'btn-outline-success' : 'btn-outline-danger';
                $autor_completo = trim($libro['autor_nombre'] . ' ' . $libro['autor_apellido']);
                $autor_display = $autor_completo ?: 'No Asignado';
                $descripcion_corta = $libro['descripcion'] ? substr($libro['descripcion'], 0, 100) . '...' : 'Sin descripción';
                
                // Año de publicación - asegurar que sea número
                $año_publicacion = $libro['año_publicacion'] ? intval($libro['año_publicacion']) : '';
            ?>
            <div class="col libro-card" 
                 data-titulo="<?= htmlspecialchars(strtolower($libro['titulo'])) ?>"
                 data-autor="<?= htmlspecialchars(strtolower($autor_display)) ?>"
                 data-categoria="<?= htmlspecialchars(strtolower($libro['categoria_nombre'] ?: '')) ?>"
                 data-isbn="<?= htmlspecialchars(strtolower($libro['isbn'])) ?>"
                 data-estado="<?= htmlspecialchars($libro['estado']) ?>"
                 data-stock="<?= $libro['stock'] ?>"
                 data-año="<?= $año_publicacion ?>">
                <div class="card book-card h-100">
                    <div class="position-relative">
                        <img src="<?= $libro['portada_url'] ?: '../assets/imagen/default-book.png' ?>" 
                             class="card-img-top book-cover" alt="<?= htmlspecialchars($libro['titulo']) ?>">
                        <span class="badge category-badge" style="background-color: <?= $libro['color_hex'] ?: '#6c757d' ?>;">
                            <?= htmlspecialchars($libro['categoria_nombre'] ?: 'General') ?>
                        </span>
                        <span class="badge stock-badge <?= $stock_btn_class === 'btn-outline-success' ? 'bg-success' : 'bg-danger' ?>">
                            <?= $libro['stock'] > 0 ? "{$libro['stock']} disp." : 'Agotado' ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title fw-bold"><?= htmlspecialchars($libro['titulo']) ?></h6>
                        <p class="card-text small text-muted mb-2">
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($autor_display) ?>
                        </p>
                        <p class="card-text small"><?= $descripcion_corta ?></p>
                        
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <?php
                            if ($libro['estado'] === 'activo') {
                                echo '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Activo</span>';
                            } else {
                                echo '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Inactivo</span>';
                            }
                            ?>
                            <button class="btn btn-sm <?= $stock_btn_class ?>">
                                <i class="fas fa-box"></i> <?= $libro['stock'] ?>
                            </button>
                            <button class="btn btn-sm btn-outline-info">
                                <i class="fas fa-calendar"></i> <?= $año_publicacion ?>
                            </button>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 action-buttons">
                        <div class="btn-group-vertical w-100" role="group">
                            <a href="ver.php?id=<?= $libro['id'] ?>" class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye me-1"></i> Ver Detalle
                            </a>
                            <a href="editar.php?id=<?= $libro['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit me-1"></i> Editar
                            </a>
                            <a href="../prestamos/crear.php?libro_id=<?= $libro['id'] ?>" class="btn btn-sm btn-success" title="Prestar">
                                <i class="fas fa-handshake me-1"></i> Prestar
                            </a>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminacion(id, titulo) {
            if (confirm(`¿Estás seguro de eliminar el libro "${titulo}"? Esta acción es irreversible.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'eliminar.php';

                const idField = document.createElement('input');
                idField.type = 'hidden';
                idField.name = 'id';
                idField.value = id;

                form.appendChild(idField);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Alternar entre vistas
        document.addEventListener('DOMContentLoaded', function() {
            const btnLista = document.getElementById('btn_vista_lista');
            const btnCuadricula = document.getElementById('btn_vista_cuadricula');
            const vistaLista = document.getElementById('vista_lista');
            const vistaCuadricula = document.getElementById('vista_cuadricula');

            function cambiarVista(vista) {
                if (vista === 'lista') {
                    vistaLista.style.display = 'block';
                    vistaCuadricula.style.display = 'none';
                    btnLista.classList.add('vista-activa');
                    btnCuadricula.classList.remove('vista-activa');
                } else {
                    vistaLista.style.display = 'none';
                    vistaCuadricula.style.display = 'flex';
                    btnLista.classList.remove('vista-activa');
                    btnCuadricula.classList.add('vista-activa');
                }
            }

            btnLista.addEventListener('click', () => cambiarVista('lista'));
            btnCuadricula.addEventListener('click', () => cambiarVista('cuadricula'));

            // Filtrado y búsqueda
            const searchInput = document.getElementById('search_input');
            const filtros = document.querySelectorAll('.filtro');
            const librosFilas = document.querySelectorAll('.libro-fila, .libro-card');

            function filtrarLibros() {
                const searchTerm = searchInput.value.toLowerCase();
                const categoria = document.querySelector('[data-filtro="categoria"]').value.toLowerCase();
                const estado = document.querySelector('[data-filtro="estado"]').value;
                const stock = document.querySelector('[data-filtro="stock"]').value;
                const año = document.querySelector('[data-filtro="año"]').value;

                librosFilas.forEach(libro => {
                    const titulo = libro.getAttribute('data-titulo');
                    const autor = libro.getAttribute('data-autor');
                    const cat = libro.getAttribute('data-categoria');
                    const isbn = libro.getAttribute('data-isbn');
                    const est = libro.getAttribute('data-estado');
                    const stk = parseInt(libro.getAttribute('data-stock'));
                    const anio = libro.getAttribute('data-año');

                    const coincideBusqueda = !searchTerm || 
                        titulo.includes(searchTerm) || 
                        autor.includes(searchTerm) || 
                        cat.includes(searchTerm) || 
                        isbn.includes(searchTerm);

                    const coincideCategoria = !categoria || cat.includes(categoria);
                    const coincideEstado = !estado || est === estado;
                    const coincideStock = !stock || 
                        (stock === 'disponible' && stk > 0) ||
                        (stock === 'agotado' && stk === 0) ||
                        (stock === 'bajo' && stk < 5 && stk > 0);
                    const coincideAño = !año || anio === año;

                    if (coincideBusqueda && coincideCategoria && coincideEstado && coincideStock && coincideAño) {
                        libro.style.display = '';
                    } else {
                        libro.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('input', filtrarLibros);
            filtros.forEach(filtro => filtro.addEventListener('change', filtrarLibros));
            filtros.forEach(filtro => {
                if (filtro.type === 'number') {
                    filtro.addEventListener('input', filtrarLibros);
                }
            });

            // Limpiar búsqueda
            document.getElementById('btn_limpiar').addEventListener('click', function() {
                searchInput.value = '';
                filtrarLibros();
            });
        });
    </script>
</body>
</html>
<?php $conexion->close(); ?>
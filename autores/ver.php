<?php
include("../config/db.php");

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

// Obtener información del autor
$sql_autor = "SELECT * FROM autores WHERE id = ?";
$stmt_autor = $conexion->prepare($sql_autor);
$stmt_autor->bind_param("i", $id);
$stmt_autor->execute();
$resultado_autor = $stmt_autor->get_result();

if ($resultado_autor->num_rows == 0) {
    header("Location: index.php?error=Autor no encontrado");
    exit();
}

$autor = $resultado_autor->fetch_assoc();
$stmt_autor->close();

// Obtener libros del autor
$sql_libros = "SELECT id, titulo, isbn, stock, portada_url FROM libros WHERE autor_id = ? ORDER BY titulo";
$stmt_libros = $conexion->prepare($sql_libros);
$stmt_libros->bind_param("i", $id);
$stmt_libros->execute();
$resultado_libros = $stmt_libros->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']); ?> - Biblioteca</title>
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
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Información del Autor -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>Información del Autor
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($autor['foto_url'])): ?>
                            <img src="imagenes/<?php echo htmlspecialchars($autor['foto_url']); ?>" 
                                 class="img-fluid rounded-circle mb-3" 
                                 alt="<?php echo htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']); ?>"
                                 style="width: 200px; height: 200px; object-fit: cover;"
                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjNkM3NTdEIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiNGRkZGRkYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj48dHNwYW4+8J+RqPCfkYg8L3RzcGFuPjwvdGV4dD48L3N2Zz4='">
                        <?php else: ?>
                            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 200px; height: 200px;">
                                <i class="fas fa-user fa-5x text-light"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h4 class="text-primary"><?php echo htmlspecialchars($autor['nombre'] . ' ' . $autor['apellido']); ?></h4>
                        
                        <?php if (!empty($autor['nacionalidad'])): ?>
                            <p class="text-muted mb-1">
                                <i class="fas fa-flag me-2"></i>
                                <?php echo htmlspecialchars($autor['nacionalidad']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($autor['fecha_nacimiento'])): ?>
                            <p class="text-muted mb-1">
                                <i class="fas fa-calendar me-2"></i>
                                Nacimiento: <?php echo date('d/m/Y', strtotime($autor['fecha_nacimiento'])); ?>
                            </p>
                        <?php endif; ?>
                        
                        <p class="text-muted mb-0">
                            <i class="fas fa-id-card me-2"></i>
                            ID: <?php echo $autor['id']; ?>
                        </p>
                    </div>
                    <div class="card-footer">
                        <div class="d-grid gap-2">
                            <a href="editar.php?id=<?php echo $autor['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i>Editar Autor
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Volver a Autores
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biografía y Libros -->
            <div class="col-md-8">
                <!-- Biografía -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-align-left me-2"></i>Biografía
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($autor['biografia'])): ?>
                            <p class="card-text" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($autor['biografia'])); ?></p>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No hay biografía disponible para este autor.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Libros del Autor -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-book me-2"></i>
                            Libros del Autor
                            <span class="badge bg-light text-dark ms-2">
                                <?php echo $resultado_libros->num_rows; ?>
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($resultado_libros->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($libro = $resultado_libros->fetch_assoc()): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="row g-0">
                                                <div class="col-4">
                                                    <img src="<?= $libro['portada_url'] ?: '../assets/imagen/default-book.png' ?>" 
                                                         class="img-fluid rounded-start h-100" 
                                                         alt="<?= htmlspecialchars($libro['titulo']) ?>"
                                                         style="object-fit: cover;">
                                                </div>
                                                <div class="col-8">
                                                    <div class="card-body">
                                                        <h6 class="card-title"><?= htmlspecialchars($libro['titulo']) ?></h6>
                                                        <p class="card-text small text-muted mb-1">
                                                            ISBN: <?= $libro['isbn'] ?>
                                                        </p>
                                                        <p class="card-text small">
                                                            <span class="badge <?= $libro['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                                                <?= $libro['stock'] > 0 ? $libro['stock'] . ' disponibles' : 'Agotado' ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="card-footer bg-transparent border-0 pt-0">
                                                        <a href="../libros/ver.php?id=<?= $libro['id'] ?>" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            Ver Detalles
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Este autor no tiene libros registrados.</p>
                                <a href="../libros/crear.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Agregar Libro
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conexion->close();
?>
<?php
require_once '../config/db.php';
$id = $_GET['id'] ?? null;
if (!$id) { 
    die("ID no especificado."); 
}

$stmt = $conexion->prepare("
    SELECT l.*, a.nombre AS autor_nombre, a.apellido AS autor_apellido, c.nombre AS categoria_nombre, c.color_hex
    FROM libros l
    LEFT JOIN autores a ON l.autor_id = a.id
    LEFT JOIN categorias c ON l.categoria_id = c.id
    WHERE l.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$libro = $result->fetch_assoc();

if (!$libro) { 
    die("Libro no encontrado."); 
}

$autor_completo = trim($libro['autor_nombre'] . ' ' . $libro['autor_apellido']);
$autor_display = $autor_completo ?: 'No Asignado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Libro - Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/ver.css">
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 text-primary">
                <i class="fas fa-book me-2"></i>Detalle del Libro
            </h1>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver al Listado
            </a>
        </div>

        <div class="row">
            <!-- Portada -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?= $libro['portada_url'] ?: '../assets/imagen/default-book.png' ?>" 
                         class="card-img-top book-cover" alt="<?= htmlspecialchars($libro['titulo']) ?>">
                </div>
            </div>

            <!-- Información -->
            <div class="col-md-8">
                <div class="info-card">
                    <h2 class="text-primary mb-3"><?= htmlspecialchars($libro['titulo']) ?></h2>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-user me-2"></i>Autor:</strong> <?= htmlspecialchars($autor_display) ?></p>
                            <p><strong><i class="fas fa-tag me-2"></i>Categoría:</strong> 
                                <span class="badge" style="background-color: <?= $libro['color_hex'] ?: '#6c757d' ?>; color: white;">
                                    <?= htmlspecialchars($libro['categoria_nombre'] ?: 'N/A') ?>
                                </span>
                            </p>
                            <p><strong><i class="fas fa-barcode me-2"></i>ISBN:</strong> <?= htmlspecialchars($libro['isbn']) ?></p>
                            <p><strong><i class="fas fa-building me-2"></i>Editorial:</strong> <?= htmlspecialchars($libro['editorial'] ?: 'N/A') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong><i class="fas fa-calendar me-2"></i>Año:</strong> 
                                <span class="badge bg-info"><?= htmlspecialchars($libro['año_publicacion'] ?: 'N/A') ?></span>
                            </p>
                            <p><strong><i class="fas fa-language me-2"></i>Idioma:</strong> <?= htmlspecialchars($libro['idioma'] ?: 'N/A') ?></p>
                            <p><strong><i class="fas fa-box me-2"></i>Stock:</strong> 
                                <span class="badge <?= $libro['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $libro['stock'] > 0 ? $libro['stock'] . ' disponibles' : 'Agotado' ?>
                                </span>
                            </p>
                            <p><strong><i class="fas fa-tag me-2"></i>Estado:</strong> 
                                <span class="badge <?= $libro['estado'] == 'activo' ? 'bg-warning' : 'bg-secondary' ?>">
                                    <?= ucfirst($libro['estado']) ?>
                                </span>
                            </p>
                            <?php if ($libro['precio']): ?>
                            <p><strong><i class="fas fa-dollar-sign me-2"></i>Precio:</strong> $<?= number_format($libro['precio'], 2) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($libro['descripcion']): ?>
                    <div class="mt-4">
                        <h5 class="text-primary"><i class="fas fa-align-left me-2"></i>Descripción</h5>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($libro['descripcion'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="editar.php?id=<?= $libro['id'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Editar Libro
                        </a>
                        <a href="../prestamos/crear.php?libro_id=<?= $libro['id'] ?>" class="btn btn-success">
                            <i class="fas fa-handshake me-2"></i>Prestar Libro
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Cerrar conexión
$conexion->close();
?>
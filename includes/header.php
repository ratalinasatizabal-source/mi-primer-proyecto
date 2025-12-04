<?php

// Verificar si la sesión ya está activa antes de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determinar la ruta base dinámicamente
$current_path = $_SERVER['PHP_SELF'];
$is_root = ($current_path == '/index.php' || $current_path == '/' || strpos($current_path, '/biblioteca-personal/') !== false);

// Función para determinar página activa
function isActivePage($page) {
    $current_uri = $_SERVER['REQUEST_URI'];
    
    switch($page) {
        case 'inicio':
            return ($current_uri == '/' || 
                    $current_uri == '../index.php' || 
                    strpos($current_uri, '/biblioteca-personal/') !== false && 
                    (strpos($current_uri, 'index.php') !== false || 
                     preg_match('/\/biblioteca-personal\/?$/', $current_uri)));
        case 'libros':
            return strpos($current_uri, 'libros') !== false;
        case 'autores':
            return strpos($current_uri, 'autores') !== false;
        case 'prestamos':
            return strpos($current_uri, 'prestamos') !== false;
        default:
            return false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Personal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilo.css">
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

    <!-- Inicio -->
    <li class="nav-item">
        <a class="nav-link <?php echo isActivePage('inicio') ? 'active' : ''; ?>" 
           href="/biblioteca/index.php">
            <i class="fas fa-home me-1"></i>Inicio
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?php echo isActivePage('libros') ? 'active' : ''; ?>" 
           href="/biblioteca/libros/index.php">
            <i class="fas fa-book me-1"></i>Libros
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/autores/') !== false ? 'active' : ''; ?>" 
           href="/biblioteca/autores/index.php">
            <i class="fas fa-users me-1"></i>Autores
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/categorias/') !== false ? 'active' : ''; ?>" 
           href="/biblioteca/categorias/index.php">
            <i class="fas fa-tags me-1"></i>Categorías
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?php echo isActivePage('prestamos') ? 'active' : ''; ?>" 
           href="/biblioteca/prestamos/index.php">
            <i class="fas fa-handshake me-1"></i>Préstamos
        </a>
    </li>

</ul>

            </div>
        </div>
    </nav>

    <!-- Alertas Dinámicas -->
    <div id="alertContainer" class="alert-fixed"></div>

    <div class="container mt-4">
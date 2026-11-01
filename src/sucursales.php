<?php
session_start();

require_once __DIR__ . '/conexiones/conexionPDO.php';

function e($valor) {
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

$sql = "SELECT id, nombre, direccion
        FROM sucursales
        ORDER BY nombre";

$stmt = $pdo->query($sql);
$sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usuarioSesion = $_SESSION['usuario'] ?? $_SESSION['user'] ?? null;
$logueado = !empty($usuarioSesion);

$nombreUsuario = is_array($usuarioSesion)
    ? ($usuarioSesion['nombre'] ?? $usuarioSesion['usuario'] ?? 'Usuario')
    : $usuarioSesion;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucursales - Don Pancho</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/Estilos.css">
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-donpancho">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            DON <span>PANCHO</span>
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Inicio</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="catalogo.php">Catálogo</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="kit.php">Arma tu Kit</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="facturacion.php">Facturación</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="sucursales.php">Sucursales</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="historial.php">Historial</a>
                </li>

                <?php if ($logueado): ?>
                    <li class="nav-item">
                        <span class="nav-link">
                            Hola, <?= e($nombreUsuario) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar sesión</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Iniciar sesión</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- ENCABEZADO -->
<section class="catalogo-header">
    <div class="container">
        <span class="subtitulo">DON PANCHO</span>
        <h1 class="titulo-seccion mt-2">Nuestras sucursales</h1>

        <p class="texto-seccion">
            Encuentra tu sucursal más cercana y visítanos.
            Estamos listos para ayudarte a encontrar los repuestos que necesitas.
        </p>
    </div>
</section>

<!-- SUCURSALES -->
<section class="container py-5">
    <div class="text-center mb-5">
        <span class="subtitulo">ESTAMOS CERCA DE TI</span>
        <h2 class="titulo-seccion mt-2">Conoce nuestras ubicaciones</h2>
    </div>

    <?php if (count($sucursales) > 0): ?>

        <div class="row g-4">

            <?php foreach ($sucursales as $sucursal): ?>

                <div class="col-md-6 col-lg-4">
                    <div class="sucursal-card h-100 text-center">

                        <div class="icono-sucursal mb-3">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>

                        <h4>
                            <?= e($sucursal['nombre']) ?>
                        </h4>

                        <p>
                            <?= e($sucursal['direccion']) ?>
                        </p>

                    </div>
                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="alert alert-warning text-center">
            No hay sucursales registradas actualmente.
        </div>

    <?php endif; ?>
</section>

<!-- FOOTER -->
<footer class="footer-donpancho">
    <div class="container">
        <div class="row g-4">

            <div class="col-md-4">
                <h5>DON PANCHO</h5>
                <p>
                    Tu aliado en repuestos y accesorios para mantener
                    tu vehículo en las mejores condiciones.
                </p>
            </div>

            <div class="col-md-4">
                <h5>Enlaces rápidos</h5>
                <ul class="footer-links list-unstyled">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="catalogo.php">Catálogo</a></li>
                    <li><a href="kit.php">Arma tu Kit</a></li>
                    <li><a href="facturacion.php">Facturación</a></li>
                    <li><a href="sucursales.php">Sucursales</a></li>
                    <li><a href="historial.php">Historial de compras</a></li>
                </ul>
            </div>

            <div class="col-md-4">
                <h5>Visítanos</h5>
                <p>La Ceiba, San Pedro Sula y Tegucigalpa.</p>
            </div>

        </div>

        <hr>

        <div class="text-center">
            <p class="mb-0">
                © <?= date('Y') ?> Don Pancho. Todos los derechos reservados.
            </p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

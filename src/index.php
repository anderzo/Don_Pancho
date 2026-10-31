
<?php
require __DIR__ . '/conexiones/conexionPDO.php';

function e($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

try {
    // CONSULTAR REPUESTOS
    // Se agrega fotografia para mostrar las imágenes del catálogo.
    $repuestos = $pdo->query(
        "SELECT r.id, c.nombre AS categoria, r.nombre, r.descripcion,
                r.precio, r.stock, r.insignia, r.fotografia
         FROM repuestos r
         JOIN categorias c ON c.id = r.categoria_id"
    )->fetchAll();

    // CONSULTAR SUCURSALES
    $sucursales = $pdo->query(
        "SELECT nombre, direccion FROM sucursales"
    )->fetchAll();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

// SELECCIONAR LOS CINCO PRODUCTOS DESTACADOS
$destacados = [];

foreach ($repuestos as $r) {
    if ($r['insignia'] === 'Destacado' && count($destacados) < 5) {
        $destacados[] = $r;
    }
}

// MARCAS DE VEHÍCULOS
$marcas = ['Toyota', 'Nissan', 'Honda', 'Hyundai', 'Isuzu', 'Ford'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio | Autopartes Don Pancho</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Estilos del proyecto -->
    <link rel="stylesheet" href="../css/Estilos.css">
</head>

<body>

<!-- =====================================
     NAVEGACIÓN
====================================== -->

<nav class="navbar navbar-expand-lg navbar-dark navbar-donpancho">

    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            DON <span>PANCHO</span>
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal"
                aria-expanded="false"
                aria-label="Mostrar menú">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        Repuestos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="catalogo.php">
                        Catálogo
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="categorias.php">
                        Categorías
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="sucursales.php">
                        Sucursales
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="facturas.php">
                        Facturas
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php">
                        Usuarios
                    </a>
                </li>

            </ul>

        </div>

    </div>

</nav>


<!-- =====================================
     BANNER PRINCIPAL
====================================== -->

<header class="hero">

    <div class="container">

        <span class="hero-etiqueta">
            AUTOPARTES Y REPUESTOS
        </span>

        <h1>
            Expertos en Repuestos<br>
            <span>Genuinos y Compatibles</span>
        </h1>

        <p class="lead">
            Calidad, confianza y el repuesto que tu vehículo necesita.
        </p>

        <a href="catalogo.php" class="btn btn-vino btn-lg mt-3">
            Explorar catálogo
        </a>

    </div>

</header>


<!-- =====================================
     PRODUCTOS DESTACADOS
====================================== -->

<section class="container py-5">

    <div class="text-center mb-5">

        <span class="subtitulo">
            CALIDAD PARA TU VEHÍCULO
        </span>

        <h2 class="titulo-seccion">
            Productos destacados
        </h2>

        <p class="texto-seccion">
            Descubre algunos de nuestros repuestos seleccionados.
        </p>

    </div>

    <div class="row g-4">

        <?php if (count($destacados) > 0): ?>

            <?php foreach ($destacados as $r): ?>

                <div class="col-sm-6 col-lg">

                    <div class="card producto-card h-100">

                        <!-- IMAGEN DEL REPUESTO -->
                        <div class="producto-imagen">

                            <?php if (!empty($r['fotografia'])): ?>

                                <img
                                    src="../img/<?= e($r['fotografia']) ?>"
                                    alt="<?= e($r['nombre']) ?>"
                                    loading="lazy"
                                >

                            <?php else: ?>

                                <div class="producto-placeholder">
                                    <span>⚙</span>
                                </div>

                            <?php endif; ?>

                        </div>

                        <div class="card-body d-flex flex-column">

                            <span class="badge badge-vino mb-2">
                                <?= e($r['insignia']) ?>
                            </span>

                            <small class="categoria-producto">
                                <?= e($r['categoria']) ?>
                            </small>

                            <h5 class="card-title mt-2">
                                <?= e($r['nombre']) ?>
                            </h5>

                            <p class="card-text text-muted small">
                                <?= e($r['descripcion']) ?>
                            </p>

                            <p class="precio mt-auto">
                                L. <?= number_format((float)$r['precio'], 2) ?>
                            </p>

                            <small class="<?= (int)$r['stock'] > 0 ? 'disponible' : 'agotado' ?>">
                                <?= (int)$r['stock'] > 0 ? 'Disponible' : 'Agotado' ?>
                            </small>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12 text-center">

                <p class="text-muted">
                    No hay productos destacados registrados.
                </p>

            </div>

        <?php endif; ?>

    </div>

    <div class="text-center mt-5">

        <a href="catalogo.php" class="btn btn-outline-vino">
            Ver catálogo completo
        </a>

    </div>

</section>


<!-- =====================================
     MARCAS DE VEHÍCULOS
====================================== -->

<section class="seccion-marcas py-5">

    <div class="container text-center">

        <span class="subtitulo">
            NUESTRA EXPERIENCIA
        </span>

        <h2 class="titulo-seccion mb-4">
            Trabajamos con las marcas que conoces
        </h2>

        <div class="row g-3">

            <?php foreach ($marcas as $marca): ?>

                <div class="col-6 col-md-4 col-lg-2">

                    <div class="marca-item">
                        <?= e($marca) ?>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>


<!-- =====================================
     SUCURSALES
====================================== -->

<section class="container py-5">

    <div class="text-center mb-5">

        <span class="subtitulo">
            ESTAMOS CERCA DE TI
        </span>

        <h2 class="titulo-seccion">
            Nuestras sucursales
        </h2>

        <p class="texto-seccion">
            Visítanos y encuentra los repuestos que necesitas.
        </p>

    </div>

    <div class="row g-4">

        <?php if (count($sucursales) > 0): ?>

            <?php foreach ($sucursales as $s): ?>

                <div class="col-md-6 col-lg-3">

                    <div class="sucursal-card h-100">

                        <div class="icono-sucursal">
                            📍
                        </div>

                        <h5>
                            <?= e($s['nombre']) ?>
                        </h5>

                        <p class="mb-0">
                            <?= e($s['direccion']) ?>
                        </p>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12 text-center">

                <p>No hay sucursales registradas.</p>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- =====================================
     PIE DE PÁGINA
====================================== -->

<footer class="footer-donpancho">

    <div class="container">

        <div class="row g-4">

            <div class="col-md-6">

                <h4>DON <span>PANCHO</span></h4>

                <p>
                    Autopartes y repuestos de calidad para mantener
                    tu vehículo en las mejores condiciones.
                </p>

            </div>

            <div class="col-md-3">

                <h5>Enlaces rápidos</h5>

                <ul class="footer-links">
                    <li><a href="index.php">Repuestos</a></li>
                    <li><a href="catalogo.php">Catálogo</a></li>
                    <li><a href="categorias.php">Categorías</a></li>
                    <li><a href="sucursales.php">Sucursales</a></li>
                </ul>

            </div>

            <div class="col-md-3">

                <h5>Síguenos</h5>

                <div class="redes">
                    <a href="#">Facebook</a>
                    <a href="#">Instagram</a>
                    <a href="#">WhatsApp</a>
                </div>

            </div>

        </div>

        <hr class="footer-divider">

        <div class="text-center">

            <small>
                &copy; <?= date('Y') ?> Don Pancho.
                Todos los derechos reservados.
            </small>

        </div>

    </div>

</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

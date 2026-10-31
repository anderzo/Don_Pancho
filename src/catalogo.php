
<?php
require __DIR__ . '/conexiones/conexionPDO.php';

try {
    $repuestos = $pdo->query(
        "SELECT r.id, c.nombre AS categoria, r.nombre, r.descripcion,
                r.precio, r.stock, r.insignia, r.fotografia
         FROM repuestos r
         JOIN categorias c ON c.id = r.categoria_id"
    )->fetchAll();

    $sucursales = $pdo->query(
        "SELECT nombre, direccion FROM sucursales"
    )->fetchAll();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

function e($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// OBTENER LAS CATEGORÍAS DISPONIBLES
$categorias = [];

foreach ($repuestos as $r) {
    $categorias[$r['categoria']] = $r['categoria'];
}

// OBTENER LA CATEGORÍA SELECCIONADA
$filtro = '';

if (isset($_GET['categoria']) && is_string($_GET['categoria'])) {
    $filtro = trim($_GET['categoria']);
}

// VALIDAR LA CATEGORÍA
if ($filtro !== '' && !isset($categorias[$filtro])) {
    $filtro = '';
}

// FILTRAR LOS REPUESTOS
$productosFiltrados = [];

foreach ($repuestos as $r) {

    if ($filtro === '' || $r['categoria'] === $filtro) {
        $productosFiltrados[] = $r;
    }

}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Catálogo | Don Pancho</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Estilos propios -->
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
                    <a class="nav-link" href="index.php">
                        Repuestos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="catalogo.php">
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
     ENCABEZADO DEL CATÁLOGO
====================================== -->

<header class="catalogo-header">

    <div class="container">

        <span class="subtitulo">
            ENCUENTRA LO QUE NECESITAS
        </span>

        <h1>Nuestro catálogo</h1>

        <p>
            Repuestos de calidad para mantener tu vehículo en marcha.
        </p>

    </div>

</header>


<!-- =====================================
     CONTENIDO DEL CATÁLOGO
====================================== -->

<main class="container py-5">

    <div class="text-center mb-4">

        <h2 class="titulo-seccion">
            Nuestros repuestos
        </h2>

        <p class="texto-seccion">
            Explora nuestros productos y filtra por categoría.
        </p>

    </div>


    <!-- FILTROS DE CATEGORÍAS -->

    <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">

        <a href="catalogo.php"
           class="btn <?= $filtro === '' ? 'btn-vino' : 'btn-outline-vino' ?>">

            Todos

        </a>

        <?php foreach ($categorias as $categoria): ?>

            <a
                href="catalogo.php?categoria=<?= urlencode($categoria) ?>"
                class="btn <?= $filtro === $categoria ? 'btn-vino' : 'btn-outline-vino' ?>"
            >
                <?= e($categoria) ?>
            </a>

        <?php endforeach; ?>

    </div>


    <!-- CANTIDAD DE PRODUCTOS -->

    <p class="text-muted mb-4">
        Mostrando <?= count($productosFiltrados) ?> productos
    </p>


    <!-- TARJETAS DE PRODUCTOS -->

    <div class="row g-4">

        <?php if (count($productosFiltrados) > 0): ?>

            <?php foreach ($productosFiltrados as $r): ?>

                <div class="col-sm-6 col-lg-4">

                    <div class="card producto-card h-100">

                        <!-- FOTOGRAFÍA -->
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

                            <span class="badge badge-categoria mb-2">
                                <?= e($r['categoria']) ?>
                            </span>

                            <h5 class="card-title">
                                <?= e($r['nombre']) ?>
                            </h5>

                            <p class="card-text text-muted">
                                <?= e($r['descripcion']) ?>
                            </p>

                            <p class="precio mt-auto">
                                L. <?= number_format((float)$r['precio'], 2) ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center">

                                <small class="<?= (int)$r['stock'] > 0 ? 'disponible' : 'agotado' ?>">
                                    <?= (int)$r['stock'] > 0 ? 'Disponible' : 'Agotado' ?>
                                </small>

                                <small class="text-muted">
                                    Stock: <?= (int)$r['stock'] ?>
                                </small>

                            </div>

                            <?php if (!empty($r['insignia'])): ?>

                                <small class="insignia-producto mt-2">
                                    <?= e($r['insignia']) ?>
                                </small>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12 text-center">

                <p class="text-muted">
                    No hay repuestos registrados en esta categoría.
                </p>

            </div>

        <?php endif; ?>

    </div>

</main>


<!-- =====================================
     FOOTER
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

                <h5>Sucursales</h5>

                <?php foreach ($sucursales as $s): ?>

                    <p class="footer-sucursal">
                        <strong><?= e($s['nombre']) ?>:</strong>
                        <?= e($s['direccion']) ?>
                    </p>

                <?php endforeach; ?>

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

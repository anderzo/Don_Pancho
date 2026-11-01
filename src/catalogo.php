
<?php

/* =========================================
   INICIO DE SESION
========================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================
   CONEXION A LA BASE DE DATOS
========================================= */

require __DIR__ . '/conexiones/conexionPDO.php';

/* =========================================
   FUNCION DE SEGURIDAD HTML
========================================= */

function e($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

/* =========================================
   CONSULTAR REPUESTOS
========================================= */

$repuestos = [];
$categorias = [];
$errorProductos = false;

try {

    $sql = "SELECT
                r.id,
                c.nombre AS categoria,
                r.nombre,
                r.descripcion,
                r.precio,
                r.stock,
                r.insignia,
                r.fotografia
            FROM repuestos r
            INNER JOIN categorias c
                ON c.id = r.categoria_id
            ORDER BY c.nombre ASC, r.nombre ASC";

    $consulta = $pdo->query($sql);

    $repuestos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    /* =====================================
       OBTENER CATEGORIAS DISPONIBLES
    ===================================== */

    foreach ($repuestos as $r) {

        $categorias[$r['categoria']] = $r['categoria'];

    }

} catch (PDOException $ex) {

    $errorProductos = true;

}

/* =========================================
   OBTENER CATEGORIA SELECCIONADA
========================================= */

$filtro = '';

if (
    isset($_GET['categoria']) &&
    is_string($_GET['categoria'])
) {

    $filtro = trim($_GET['categoria']);

}

/* =========================================
   VALIDAR CATEGORIA
========================================= */

if (
    $filtro !== '' &&
    !isset($categorias[$filtro])
) {

    $filtro = '';

}

/* =========================================
   FILTRAR REPUESTOS
========================================= */

$productosFiltrados = [];

foreach ($repuestos as $r) {

    if (
        $filtro === '' ||
        $r['categoria'] === $filtro
    ) {

        $productosFiltrados[] = $r;

    }

}

/* =========================================
   CONTROL DE SESION
========================================= */

$usuarioSesion = $_SESSION['usuario']
    ?? $_SESSION['user']
    ?? null;

$logueado = !empty($usuarioSesion);

$nombreUsuario = '';
$rolUsuario = '';

if (is_array($usuarioSesion)) {

    $nombreUsuario = $usuarioSesion['nombre']
        ?? $usuarioSesion['usuario']
        ?? $usuarioSesion['username']
        ?? 'Usuario';

    $rolUsuario = $usuarioSesion['rol']
        ?? $usuarioSesion['role']
        ?? $_SESSION['rol']
        ?? $_SESSION['role']
        ?? '';

} elseif (is_string($usuarioSesion)) {

    $nombreUsuario = $usuarioSesion;

    $rolUsuario = $_SESSION['rol']
        ?? $_SESSION['role']
        ?? '';

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="description"
          content="Consulta nuestro catálogo de autopartes y repuestos Don Pancho. Encuentra repuestos genuinos y compatibles.">

    <title>Catálogo | Autopartes Don Pancho</title>

    <!-- Bootstrap 5 -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- CSS DEL PROYECTO -->

    <link rel="stylesheet" href="../css/Estilos.css">

</head>

<body>


<!-- =========================================
     BARRA DE NAVEGACION
========================================= -->

<nav class="navbar navbar-expand-lg navbar-dark navbar-donpancho">

    <div class="container">

        <!-- LOGOTIPO -->

        <a class="navbar-brand fw-bold" href="index.php">

            DON <span>PANCHO</span>

        </a>


        <!-- BOTON RESPONSIVE -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#menuPrincipal"
            aria-controls="menuPrincipal"
            aria-expanded="false"
            aria-label="Mostrar menú"
        >

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- OPCIONES DEL MENU -->

        <div class="collapse navbar-collapse" id="menuPrincipal">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">

                    <a class="nav-link" href="index.php">

                        Inicio

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link active" href="catalogo.php">

                        Catálogo

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="kit.php">

                        Arma tu Kit

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="facturacion.php">

                        Facturación SAR

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="sucursales.php">

                        Sucursales
                    </a>

                </li>

                <li class="nav-item">

                    <a class="nav-link" href="historial.php">
                        Historial
                    </a>
                </li>


                <!-- CONTROL DINAMICO DE SESION -->

                <?php if ($logueado): ?>

                    <li class="nav-item ms-lg-3">

                        <span class="nav-link">

                            <?= e($nombreUsuario) ?>

                            <?php if (!empty($rolUsuario)): ?>

                                (<?= e($rolUsuario) ?>)

                            <?php endif; ?>

                        </span>

                    </li>


                    <li class="nav-item">

                        <a class="nav-link" href="logout.php">

                            Cerrar sesión

                        </a>

                    </li>

                <?php else: ?>

                    <li class="nav-item ms-lg-3">

                        <a class="nav-link" href="login.php">

                            Iniciar sesión

                        </a>

                    </li>

                <?php endif; ?>

            </ul>

        </div>

    </div>

</nav>



<!-- =========================================
     ENCABEZADO DEL CATALOGO
========================================= -->

<header class="catalogo-header">

    <div class="container">

        <span class="subtitulo">

            ENCUENTRA LO QUE NECESITAS

        </span>


        <h1>

            Nuestro catálogo

        </h1>


        <p>

            Repuestos de calidad para mantener
            tu vehículo en marcha.

        </p>

    </div>

</header>



<!-- =========================================
     CONTENIDO DEL CATALOGO
========================================= -->

<main class="container py-5">


    <!-- TITULO -->

    <div class="text-center mb-4">

        <h2 class="titulo-seccion">

            Nuestros repuestos

        </h2>


        <p class="texto-seccion">

            Explora nuestros productos y filtra por categoría.

        </p>

    </div>



    <!-- =====================================
         FILTROS DE CATEGORIAS
    ====================================== -->

    <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">


        <!-- MOSTRAR TODOS -->

        <a
            href="catalogo.php"
            class="btn <?= $filtro === ''
                ? 'btn-vino'
                : 'btn-outline-vino' ?>"
        >

            Todos

        </a>


        <!-- CATEGORIAS DINAMICAS -->

        <?php foreach ($categorias as $categoria): ?>

            <a
                href="catalogo.php?categoria=<?= urlencode($categoria) ?>"
                class="btn <?= $filtro === $categoria
                    ? 'btn-vino'
                    : 'btn-outline-vino' ?>"
            >

                <?= e($categoria) ?>

            </a>

        <?php endforeach; ?>


    </div>



    <!-- =====================================
         CANTIDAD DE PRODUCTOS
    ====================================== -->

    <p class="text-muted mb-4">

        Mostrando

        <strong>

            <?= count($productosFiltrados) ?>

        </strong>

        productos

        <?php if ($filtro !== ''): ?>

            de la categoría

            <strong>

                <?= e($filtro) ?>

            </strong>

        <?php endif; ?>

    </p>



    <!-- =====================================
         TARJETAS DE PRODUCTOS
    ====================================== -->

    <div class="row g-4">


        <?php if ($errorProductos): ?>


            <div class="col-12 text-center">

                <p class="text-danger">

                    No fue posible cargar los productos.
                    Intenta nuevamente más tarde.

                </p>

            </div>


        <?php elseif (count($productosFiltrados) > 0): ?>


            <?php foreach ($productosFiltrados as $r): ?>


                <div class="col-sm-6 col-lg-4">

                    <div class="card producto-card h-100">


                        <!-- FOTOGRAFIA DEL REPUESTO -->

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



                        <!-- INFORMACION DEL PRODUCTO -->

                        <div class="card-body d-flex flex-column">


                            <!-- CATEGORIA -->

                            <span class="badge badge-categoria mb-2">

                                <?= e($r['categoria']) ?>

                            </span>


                            <!-- NOMBRE -->

                            <h5 class="card-title">

                                <?= e($r['nombre']) ?>

                            </h5>


                            <!-- DESCRIPCION -->

                            <p class="card-text text-muted">

                                <?= e($r['descripcion']) ?>

                            </p>


                            <!-- PRECIO -->

                            <p class="precio mt-auto">

                                L.

                                <?= number_format(
                                    (float)$r['precio'],
                                    2
                                ) ?>

                            </p>


                            <!-- EXISTENCIAS -->

                            <div class="d-flex justify-content-between align-items-center">

                                <small class="<?= (int)$r['stock'] > 0
                                    ? 'disponible'
                                    : 'agotado' ?>">

                                    <?= (int)$r['stock'] > 0
                                        ? 'Disponible'
                                        : 'Agotado' ?>

                                </small>


                                <small class="text-muted">

                                    Stock: <?= (int)$r['stock'] ?>

                                </small>

                            </div>


                            <!-- INSIGNIA -->

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


                <a href="catalogo.php"
                   class="btn btn-outline-vino">

                    Ver todos los productos

                </a>

            </div>


        <?php endif; ?>


    </div>

</main>



<!-- =========================================
     FOOTER
========================================= -->

<footer class="footer-donpancho">

    <div class="container">

        <div class="row g-4">


            <!-- INFORMACION DE LA EMPRESA -->

            <div class="col-md-4">

                <h4>

                    DON <span>PANCHO</span>

                </h4>


                <p>

                    Autopartes y Repuestos Don Pancho,
                    expertos en repuestos genuinos y compatibles
                    para vehículos japoneses, coreanos y americanos.

                </p>

            </div>



            <!-- ENLACES RAPIDOS -->

            <div class="col-md-3">

                <h5>

                    Enlaces rápidos

                </h5>


                <ul class="footer-links">

                    <li>

                        <a href="index.php">

                            Inicio

                        </a>

                    </li>


                    <li>

                        <a href="catalogo.php">

                            Catálogo

                        </a>

                    </li>


                    <li>

                        <a href="kit.php">

                            Arma tu Kit

                        </a>

                    </li>


                    <li>

                        <a href="facturacion.php">

                            Facturación SAR

                        </a>

                    </li>


                    <li>

                        <a href="sucursales.php">

                            Sucursales

                        </a>

                    </li>

                    <li>

                        <a href="historial.php">

                            Historial de compras

                        </a>

                    </li>


                </ul>

            </div>



            <!-- DIRECCIONES -->

            <div class="col-md-5">

                <h5>

                    Nuestras sucursales

                </h5>


                <ul class="footer-links">

                    <li class="mb-2">

                        <strong>Tegucigalpa:</strong>

                        <br>

                        Toncontín y Suyapa.

                    </li>


                    <li class="mb-2">

                        <strong>San Pedro Sula:</strong>

                        <br>

                        Medina.

                    </li>


                    <li class="mb-2">

                        <strong>La Ceiba:</strong>

                        <br>

                        Carretera CA-13.

                    </li>

                </ul>

            </div>


        </div>



        <hr class="footer-divider">



        <!-- REDES SOCIALES -->

        <div class="text-center mb-3">

            <h5>

                Síguenos

            </h5>


            <div class="redes">

                <a href="#" aria-label="Facebook">

                    Facebook

                </a>


                <a href="#" aria-label="Instagram">

                    Instagram

                </a>


                <a href="#" aria-label="WhatsApp">

                    WhatsApp

                </a>

            </div>

        </div>



        <!-- COPYRIGHT -->

        <div class="text-center">

            <small>

                &copy; <?= date('Y') ?> Don Pancho.

                Todos los derechos reservados.

            </small>

        </div>


    </div>

</footer>



<!-- =========================================
     BOOTSTRAP JAVASCRIPT
========================================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>

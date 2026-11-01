<?php
session_start();

require __DIR__ . '/conexiones/conexionPDO.php';

/* =========================================
   FUNCION PARA PROTEGER LA SALIDA HTML
========================================= */
function e($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

/* =========================================
   CONSULTAR PRODUCTOS DESTACADOS
========================================= */
$destacados = [];
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
            WHERE r.insignia = 'Destacado'
            ORDER BY r.id DESC
            LIMIT 5";

    $consulta = $pdo->query($sql);
    $destacados = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $ex) {
    $errorProductos = true;
}

/* =========================================
   MARCAS DE VEHICULOS
========================================= */
$marcas = [
    'Toyota',
    'Nissan',
    'Honda',
    'Hyundai',
    'Isuzu',
    'Ford'
];

/* =========================================
   SUCURSALES SOLICITADAS EN EL EJERCICIO
========================================= */
$sucursales = [
    [
        'nombre' => 'Tegucigalpa - Toncontín',
        'direccion' => 'Sucursal Toncontín, Tegucigalpa.'
    ],
    [
        'nombre' => 'Tegucigalpa - Suyapa',
        'direccion' => 'Sucursal Suyapa, Tegucigalpa.'
    ],
    [
        'nombre' => 'San Pedro Sula - Medina',
        'direccion' => 'Sucursal Medina, San Pedro Sula.'
    ],
    [
        'nombre' => 'La Ceiba - CA-13',
        'direccion' => 'Sucursal ubicada en carretera CA-13, La Ceiba.'
    ]
];

/* =========================================
   CONTROL DE SESION PARA NAVBAR
========================================= */

// Se contemplan dos nombres comunes de variable de sesión.
// Ajustar estas claves si tu login utiliza otras.
$usuarioSesion = $_SESSION['usuario'] ?? $_SESSION['user'] ?? null;

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
        ?? '';

} elseif (is_string($usuarioSesion)) {
    $nombreUsuario = $usuarioSesion;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="description"
          content="Autopartes y Repuestos Don Pancho. Repuestos genuinos y compatibles para tu vehículo.">

    <title>Inicio | Autopartes Don Pancho</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Estilos del proyecto -->
    <link rel="stylesheet" href="../css/Estilos.css">

</head>

<body>

<!-- =========================================
     BARRA DE NAVEGACION
========================================= -->

<nav class="navbar navbar-expand-lg navbar-dark navbar-donpancho">

    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            DON <span>PANCHO</span>
        </a>

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

        <div class="collapse navbar-collapse" id="menuPrincipal">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
                        Inicio
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="catalogo.php">
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
                        Facturación
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

                <?php if ($logueado): ?>

                    <li class="nav-item ms-lg-3">

                        <span class="nav-link">
                            <?= e($nombreUsuario) ?>

                            <?php if ($rolUsuario !== ''): ?>
                                (<?= e($rolUsuario) ?>)
                            <?php endif; ?>
                        </span>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link"
                           href="logout.php">
                            Cerrar sesión
                        </a>

                    </li>

                <?php else: ?>

                    <li class="nav-item ms-lg-3">

                        <a class="nav-link"
                           href="login.php">
                            Iniciar sesión
                        </a>

                    </li>

                <?php endif; ?>

            </ul>

        </div>

    </div>

</nav>


<!-- =========================================
     BANNER PRINCIPAL
========================================= -->

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

        <a href="catalogo.php"
           class="btn btn-vino btn-lg mt-3">

            Explorar catálogo

        </a>

    </div>

</header>


<!-- =========================================
     PRODUCTOS DESTACADOS
========================================= -->

<section class="container py-5">

    <div class="text-center mb-5">

        <span class="subtitulo">
            CALIDAD PARA TU VEHÍCULO
        </span>

        <h2 class="titulo-seccion">
            Productos destacados
        </h2>

        <p class="texto-seccion">
            Descubre nuestros repuestos seleccionados.
        </p>

    </div>

    <div class="row g-4">

        <?php if ($errorProductos): ?>

            <div class="col-12 text-center">

                <p class="text-danger">
                    No fue posible consultar los productos.
                    Intenta nuevamente más tarde.
                </p>

            </div>

        <?php elseif (count($destacados) > 0): ?>

            <?php foreach ($destacados as $r): ?>

                <div class="col-sm-6 col-lg">

                    <div class="card producto-card h-100">

                        <!-- Imagen del producto -->

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

                                L. <?= number_format(
                                    (float)$r['precio'],
                                    2
                                ) ?>

                            </p>

                            <small class="<?= (int)$r['stock'] > 0
                                ? 'disponible'
                                : 'agotado' ?>">

                                <?= (int)$r['stock'] > 0
                                    ? 'Disponible'
                                    : 'Agotado' ?>

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

        <a href="catalogo.php"
           class="btn btn-outline-vino">

            Ver catálogo completo

        </a>

    </div>

</section>


<!-- =========================================
     MARCAS DE VEHICULOS
========================================= -->

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


<!-- =========================================
     SUCURSALES
========================================= -->

<section class="container py-5">

    <div class="text-center mb-5">

        <span class="subtitulo">
            ESTAMOS CERCA DE TI
        </span>

        <h2 class="titulo-seccion">
            Nuestras sucursales
        </h2>

        <p class="texto-seccion">
            Visítanos en cualquiera de nuestras sucursales
            y encuentra los repuestos que necesitas.
        </p>

    </div>

    <div class="row g-4">

        <?php foreach ($sucursales as $s): ?>

            <div class="col-sm-6 col-lg-3">

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

    </div>

</section>


<!-- =========================================
     FOOTER
========================================= -->

<footer class="footer-donpancho">

    <div class="container">

        <div class="row g-4">

            <!-- Información de la empresa -->

            <div class="col-md-4">

                <h4>
                    DON <span>PANCHO</span>
                </h4>

                <p>
                    Autopartes y repuestos genuinos y compatibles
                    para mantener tu vehículo en las mejores
                    condiciones.
                </p>

            </div>


            <!-- Enlaces rápidos -->

            <div class="col-md-3">

                <h5>
                    Enlaces rápidos
                </h5>

                <ul class="footer-links">

                    <li>
                        <a href="index.php">Inicio</a>
                    </li>

                    <li>
                        <a href="catalogo.php">Catálogo</a>
                    </li>

                    <li>
                        <a href="kit.php">Arma tu Kit</a>
                    </li>

                    <li>
                        <a href="facturacion.php">Facturación</a>
                    </li>

                    <li>
                        <a href="sucursales.php">Sucursales</a>
                    </li>

                    <li>
                        <a href="historial.php">Historial de compras</a>
                    </li>

                </ul>

            </div>


            <!-- Direcciones -->

            <div class="col-md-5">

                <h5>
                    Nuestras direcciones
                </h5>

                <ul class="footer-links">

                    <li>
                        <strong>Tegucigalpa:</strong>
                        Toncontín y Suyapa.
                    </li>

                    <li>
                        <strong>San Pedro Sula:</strong>
                        Medina.
                    </li>

                    <li>
                        <strong>La Ceiba:</strong>
                        Carretera CA-13.
                    </li>

                </ul>

            </div>

        </div>


        <hr class="footer-divider">


        <!-- Redes sociales -->

        <div class="text-center mb-3">

            <h5>Síguenos</h5>

            <div class="redes">

                <a href="https://www.facebook.com/"
                target="_blank" rel="noopener noreferrer">
                    Facebook
                </a>

                <a href="https://www.instagram.com/"
                target="_blank" rel="noopener noreferrer">
                    Instagram
                </a>

                <a href="https://www.whatsapp.com/"
                target="_blank" rel="noopener noreferrer">
                    WhatsApp
                </a>

            </div>

        </div>


        <div class="text-center">

            <small>

                &copy; <?= date('Y') ?> Don Pancho.

                Todos los derechos reservados.

            </small>

        </div>

    </div>

</footer>


<!-- Bootstrap JavaScript -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

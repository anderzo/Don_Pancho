<?php
session_start();

require_once __DIR__ . '/conexiones/conexionPDO.php';

/* =========================================
   FUNCION PARA PROTEGER LA SALIDA HTML
========================================= */
function e($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/* =========================================
   VARIABLES DEL HISTORIAL
========================================= */
$rtn = '';
$facturas = [];
$mensaje = '';

/* =========================================
   CONSULTAR DETALLES DE UNA FACTURA
========================================= */
function obtenerDetalles(PDO $pdo, int $facturaId) {

    $sql = "SELECT
                d.cantidad,
                d.precio_unitario,
                d.subtotal_linea,
                r.nombre AS repuesto
            FROM detalle_factura d
            INNER JOIN repuestos r
                ON d.repuesto_id = r.id
            WHERE d.factura_id = :factura_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['factura_id' => $facturaId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================================
   PROCESAR BUSQUEDA POR RTN
========================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rtn = trim($_POST['rtn'] ?? '');

    if (!preg_match('/^[0-9]{14}$/', $rtn)) {

        $mensaje = 'Ingresa un RTN válido de 14 dígitos.';

    } else {

        $sql = "SELECT
                    f.id,
                    f.numero_factura,
                    f.rtn,
                    f.telefono,
                    f.tipo_flete,
                    f.subtotal,
                    f.descuento,
                    f.isv,
                    f.total,
                    f.fecha,
                    s.nombre AS sucursal
                FROM facturas f
                LEFT JOIN sucursales s
                    ON f.sucursal_id = s.id
                WHERE f.rtn = :rtn
                ORDER BY f.fecha DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['rtn' => $rtn]);

        $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($facturas) === 0) {
            $mensaje = 'No se encontraron facturas registradas con ese RTN.';
        }
    }
}

/* =========================================
   CONTROL DE SESION PARA NAVBAR
========================================= */
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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Historial de compras - Don Pancho</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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
                    <a class="nav-link" href="sucursales.php">Sucursales</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="historial.php">Historial</a>
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
     ENCABEZADO
========================================= -->

<header class="historial-header">

    <div class="container">

        <span class="subtitulo">
            DON PANCHO
        </span>

        <h1 class="titulo-seccion mt-2">
            Historial de compras
        </h1>

        <p class="texto-seccion">
            Consulta las facturas y los detalles de tus compras
            ingresando el RTN del cliente.
        </p>

    </div>

</header>


<!-- =========================================
     BUSQUEDA DE FACTURAS
========================================= -->

<main class="historial-contenido">

    <div class="container">

        <div class="historial-busqueda">

            <div class="text-center mb-4">

                <span class="subtitulo">
                    CONSULTA TUS COMPRAS
                </span>

                <h2 class="titulo-seccion mt-2">
                    Buscar facturas
                </h2>

                <p class="texto-seccion">
                    Escribe el RTN utilizado al momento de realizar la compra.
                </p>

            </div>

            <form method="POST" action="historial.php">

                <div class="row justify-content-center align-items-end">

                    <div class="col-md-8 mb-3">

                        <label for="rtn" class="form-label">
                            RTN del cliente
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="rtn"
                            name="rtn"
                            maxlength="14"
                            minlength="14"
                            pattern="[0-9]{14}"
                            inputmode="numeric"
                            placeholder="Ingrese los 14 dígitos del RTN"
                            value="<?= e($rtn) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-4 mb-3">

                        <button
                            type="submit"
                            class="btn btn-vino historial-boton w-100"
                        >
                            Buscar compras
                        </button>

                    </div>

                </div>

            </form>

        </div>


        <!-- =========================================
             MENSAJES
        ========================================= -->

        <?php if ($mensaje !== ''): ?>

            <div class="alert alert-info text-center mt-4">
                <?= e($mensaje) ?>
            </div>

        <?php endif; ?>


        <!-- =========================================
             RESULTADOS DE FACTURAS
        ========================================= -->

        <?php if (count($facturas) > 0): ?>

            <div class="mt-5">

                <div class="mb-4">

                    <span class="subtitulo">
                        RESULTADOS DE LA CONSULTA
                    </span>

                    <h2 class="titulo-seccion mt-2">
                        Facturas encontradas
                    </h2>

                    <p class="texto-seccion">
                        Se encontraron <?= count($facturas) ?> factura(s)
                        asociadas al RTN ingresado.
                    </p>

                </div>


                <?php foreach ($facturas as $factura): ?>

                    <?php
                    $detalles = obtenerDetalles($pdo, (int)$factura['id']);
                    ?>

                    <div class="historial-factura mb-4">

                        <div class="historial-factura-header">

                            <div class="row align-items-center">

                                <div class="col-md-8">

                                    <h4 class="mb-1">
                                        Factura:
                                        <?= e($factura['numero_factura']) ?>
                                    </h4>

                                    <small>
                                        Fecha:
                                        <?= e($factura['fecha']) ?>
                                    </small>

                                </div>

                                <div class="col-md-4 text-md-end">

                                    <strong>
                                        RTN: <?= e($factura['rtn']) ?>
                                    </strong>

                                </div>

                            </div>

                        </div>


                        <div class="historial-factura-body">

                            <div class="row mb-4">

                                <div class="col-md-6 mb-2">

                                    <strong>Sucursal:</strong>

                                    <?= e($factura['sucursal'] ?? 'No disponible') ?>

                                </div>

                                <div class="col-md-6 mb-2">

                                    <strong>Teléfono:</strong>

                                    <?= e($factura['telefono']) ?>

                                </div>

                                <div class="col-md-6 mb-2">

                                    <strong>Tipo de flete:</strong>

                                    <?= e($factura['tipo_flete']) ?>

                                </div>

                            </div>


                            <h5 class="titulo-seccion mb-3">
                                Productos comprados
                            </h5>


                            <div class="table-responsive">

                                <table class="table historial-tabla">

                                    <thead>
                                        <tr>
                                            <th>Repuesto</th>
                                            <th>Cantidad</th>
                                            <th>Precio unitario</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php foreach ($detalles as $detalle): ?>

                                            <tr>

                                                <td>
                                                    <?= e($detalle['repuesto']) ?>
                                                </td>

                                                <td>
                                                    <?= e($detalle['cantidad']) ?>
                                                </td>

                                                <td>
                                                    L.
                                                    <?= number_format((float)$detalle['precio_unitario'], 2) ?>
                                                </td>

                                                <td>
                                                    L.
                                                    <?= number_format((float)$detalle['subtotal_linea'], 2) ?>
                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>


                            <!-- TOTALES -->

                            <div class="row justify-content-end mt-4">

                                <div class="col-md-6 col-lg-5">

                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>

                                        <strong>
                                            L. <?= number_format((float)$factura['subtotal'], 2) ?>
                                        </strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Descuento:</span>

                                        <strong>
                                            L. <?= number_format((float)$factura['descuento'], 2) ?>
                                        </strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span>ISV:</span>

                                        <strong>
                                            L. <?= number_format((float)$factura['isv'], 2) ?>
                                        </strong>
                                    </div>

                                    <div class="historial-total d-flex justify-content-between mt-3">
                                        <strong>Total:</strong>

                                        <strong>
                                            L. <?= number_format((float)$factura['total'], 2) ?>
                                        </strong>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

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

            <div class="col-md-4">

                <h4>
                    DON <span>PANCHO</span>
                </h4>

                <p>
                    Autopartes y repuestos genuinos y compatibles
                    para mantener tu vehículo en las mejores condiciones.
                </p>

            </div>


            <div class="col-md-3">

                <h5>Enlaces rápidos</h5>

                <ul class="footer-links">

                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="catalogo.php">Catálogo</a></li>
                    <li><a href="kit.php">Arma tu Kit</a></li>
                    <li><a href="facturacion.php">Facturación</a></li>
                    <li><a href="sucursales.php">Sucursales</a></li>
                    <li><a href="historial.php">Historial de compras</a></li>

                </ul>

            </div>


            <div class="col-md-5">

                <h5>Nuestras direcciones</h5>

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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

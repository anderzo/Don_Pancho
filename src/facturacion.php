<?php
session_start();

require_once __DIR__ . '/conexiones/conexionPDO.php';
require_once __DIR__ . '/factura.php';

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

/* =========================================
   FUNCION PARA PROTEGER LA SALIDA HTML
========================================= */
function e($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/* =========================================
   CONSULTAR REPUESTOS Y SUCURSALES
========================================= */
$factura = new Factura($pdo);

$repuestos = $factura->listarRepuestos();
$sucursales = $factura->listarSucursales();

/* =========================================
   VARIABLES PARA MENSAJES
========================================= */
$mensaje = "";
$tipoMensaje = "";
$numeroFactura = "";

/* =========================================
   PROCESAR FORMULARIO
========================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $rtn = trim($_POST['rtn'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $sucursalId = (int)($_POST['sucursal_id'] ?? 0);
        $tipoFlete = $_POST['tipo_flete'] ?? '';

        $productos = [];
        $productosRecibidos = $_POST['productos'] ?? [];

        if (!is_array($productosRecibidos)) {
            throw new InvalidArgumentException(
                "Los productos seleccionados no son válidos."
            );
        }

        foreach ($productosRecibidos as $id => $cantidad) {

            $cantidad = filter_var(
                $cantidad,
                FILTER_VALIDATE_INT
            );

            if ($cantidad === false || $cantidad < 0) {
                throw new InvalidArgumentException(
                    "La cantidad de los repuestos no es válida."
                );
            }

            if ($cantidad > 0) {
                $productos[] = [
                    'id' => (int)$id,
                    'cantidad' => $cantidad
                ];
            }
        }

        $numeroFactura = $factura->guardarFactura(
            $rtn,
            $telefono,
            $sucursalId,
            $tipoFlete,
            $productos
        );

        $mensaje = "¡Factura generada correctamente!";
        $tipoMensaje = "success";

        // Actualizar existencias después de la compra
        $repuestos = $factura->listarRepuestos();

    } catch (Throwable $e) {

        $mensaje = "No se pudo generar la factura: " . $e->getMessage();
        $tipoMensaje = "error";
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Facturación - Don Pancho</title>

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

<?php include __DIR__ . '/navbar/navbar_admin.php'; ?>


<!-- =========================================
     ENCABEZADO
========================================= -->

<header class="catalogo-header">

    <div class="container">

        <span class="subtitulo">
            DON PANCHO
        </span>

        <h1 class="titulo-seccion mt-2">
            Facturación
        </h1>

        <p class="texto-seccion">
            Registra tus compras, selecciona tus repuestos
            y consulta el total de tu factura.
        </p>

    </div>

</header>


<!-- =========================================
     CONTENIDO DE FACTURACION
========================================= -->

<main class="container py-5">

    <?php if ($mensaje !== ""): ?>

        <div class="alert alert-<?= $tipoMensaje === 'success' ? 'success' : 'danger' ?> mb-4">

            <?= e($mensaje) ?>

            <?php if ($numeroFactura !== ""): ?>

                <br>

                <strong>Número de factura:</strong>
                <?= e($numeroFactura) ?>

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <form method="POST" action="">

        <!-- DATOS DEL CLIENTE -->

        <div class="facturacion-seccion mb-4">

            <span class="subtitulo">INFORMACIÓN DEL CLIENTE</span>

            <h2 class="titulo-seccion mt-2 mb-4">
                Datos del cliente
            </h2>

            <div class="row g-3">

                <div class="col-md-6">

                    <label for="rtn" class="form-label">
                        RTN
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="rtn"
                        name="rtn"
                        maxlength="14"
                        pattern="[0-9]{14}"
                        inputmode="numeric"
                        placeholder="Ingrese los 14 dígitos"
                        required
                    >

                </div>

                <div class="col-md-6">

                    <label for="telefono" class="form-label">
                        Teléfono
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="telefono"
                        name="telefono"
                        maxlength="8"
                        pattern="[2389][0-9]{7}"
                        inputmode="numeric"
                        placeholder="Ejemplo: 98765432"
                        required
                    >

                </div>

            </div>

        </div>


        <!-- SELECCIONAR SUCURSAL -->

        <div class="facturacion-seccion mb-4">

            <span class="subtitulo">PUNTO DE ATENCIÓN</span>

            <h2 class="titulo-seccion mt-2 mb-4">
                Seleccionar sucursal
            </h2>

            <label for="sucursal_id" class="form-label">
                Sucursal donde se realiza la compra
            </label>

            <select
                name="sucursal_id"
                id="sucursal_id"
                class="form-select"
                required
            >

                <option value="">Seleccione una sucursal</option>

                <?php foreach ($sucursales as $sucursal): ?>

                    <option value="<?= (int)$sucursal['id'] ?>">

                        <?= e($sucursal['nombre']) ?>
                        - <?= e($sucursal['direccion']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- SELECCIONAR REPUESTOS -->

        <div class="facturacion-seccion mb-4">

            <span class="subtitulo">PRODUCTOS DISPONIBLES</span>

            <h2 class="titulo-seccion mt-2 mb-2">
                Seleccionar repuestos
            </h2>

            <p class="texto-seccion mb-4">
                Indica la cantidad de cada repuesto que deseas comprar.
            </p>

            <?php if (empty($repuestos)): ?>

                <div class="alert alert-warning">
                    No hay repuestos disponibles en inventario.
                </div>

            <?php else: ?>

                <div class="row g-3">

                    <?php foreach ($repuestos as $repuesto): ?>

                        <div class="col-md-6">

                            <div class="facturacion-producto h-100">

                                <div class="d-flex justify-content-between align-items-start gap-3">

                                    <div>

                                        <h5>
                                            <?= e($repuesto['nombre']) ?>
                                        </h5>

                                        <p class="mb-1">
                                            Precio:
                                            <strong>
                                                L. <?= number_format((float)$repuesto['precio'], 2) ?>
                                            </strong>
                                        </p>

                                        <small class="text-muted">
                                            Disponibles:
                                            <?= (int)$repuesto['stock'] ?>
                                        </small>

                                    </div>

                                    <div class="facturacion-cantidad">

                                        <label
                                            for="producto_<?= (int)$repuesto['id'] ?>"
                                            class="form-label"
                                        >
                                            Cantidad
                                        </label>

                                        <input
                                            type="number"
                                            class="form-control cantidad-producto"
                                            id="producto_<?= (int)$repuesto['id'] ?>"
                                            name="productos[<?= (int)$repuesto['id'] ?>]"
                                            min="0"
                                            max="<?= (int)$repuesto['stock'] ?>"
                                            value="0"
                                            data-precio="<?= e($repuesto['precio']) ?>"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>


        <!-- TIPO DE FLETE -->

        <div class="facturacion-seccion mb-4">

            <span class="subtitulo">ENTREGA DE COMPRA</span>

            <h2 class="titulo-seccion mt-2 mb-4">
                Tipo de flete
            </h2>

            <label for="tipo_flete" class="form-label">
                Selecciona cómo deseas recibir tus productos
            </label>

            <select
                name="tipo_flete"
                id="tipo_flete"
                class="form-select"
                required
            >

                <option value="">Seleccione el flete</option>

                <option value="retiro" data-costo="0">
                    Retiro en sucursal - L. 0
                </option>

                <option value="moto_express" data-costo="50">
                    Moto express - L. 50
                </option>

                <option value="camionsito" data-costo="180">
                    Camioncito - L. 180
                </option>

            </select>

        </div>


        <!-- RESUMEN DE COMPRA -->

        <div class="facturacion-resumen mb-4">

            <span class="subtitulo">DETALLE DE PAGO</span>

            <h2 class="titulo-seccion mt-2 mb-4">
                Resumen de compra
            </h2>

            <div class="resumen-linea">
                <span>Subtotal:</span>
                <strong id="subtotal">L. 0.00</strong>
            </div>

            <div class="resumen-linea">
                <span>Descuento:</span>
                <strong id="descuento">L. 0.00</strong>
            </div>

            <div class="resumen-linea">
                <span>ISV (15%):</span>
                <strong id="isv">L. 0.00</strong>
            </div>

            <div class="resumen-linea">
                <span>Flete:</span>
                <strong id="flete">L. 0.00</strong>
            </div>

            <hr>

            <div class="resumen-total">
                <span>Total a pagar:</span>
                <strong id="total">L. 0.00</strong>
            </div>

            <small class="text-muted d-block mt-3">
                El descuento del 10% se aplica al comprar 10 unidades o más.
            </small>

        </div>


        <!-- BOTON -->

        <div class="text-center">

            <button
                type="submit"
                class="btn btn-vino btn-lg px-5"
                name="generar_factura"
            >
                Generar factura
            </button>

        </div>

    </form>

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


        <div class="text-center">

            <small>
                &copy; <?= date('Y') ?> Don Pancho.
                Todos los derechos reservados.
            </small>

        </div>

    </div>

</footer>


<!-- =========================================
     CALCULO DEL RESUMEN
========================================= -->

<script>

// Obtener los elementos del formulario
const cantidades = document.querySelectorAll('.cantidad-producto');
const selectorFlete = document.getElementById('tipo_flete');

// Formatear cantidades en lempiras
function lempiras(valor) {
    return 'L. ' + valor.toFixed(2);
}

// Calcular los totales
function calcularResumen() {

    let subtotal = 0;
    let cantidadTotal = 0;

    cantidades.forEach(function(input) {

        const cantidad = parseInt(input.value) || 0;
        const precio = parseFloat(input.dataset.precio) || 0;

        subtotal += cantidad * precio;
        cantidadTotal += cantidad;

    });

    // Descuento del 10% a partir de 10 unidades
    let descuento = 0;

    if (cantidadTotal >= 10) {
        descuento = subtotal * 0.10;
    }

    const baseImponible = subtotal - descuento;

    // Calcular ISV
    const isv = baseImponible * 0.15;

    // Obtener costo del flete
    const opcionFlete =
        selectorFlete.options[selectorFlete.selectedIndex];

    const flete = parseFloat(
        opcionFlete.dataset.costo || 0
    );

    // Calcular total
    const total = baseImponible + isv + flete;

    // Mostrar resultados
    document.getElementById('subtotal').textContent =
        lempiras(subtotal);

    document.getElementById('descuento').textContent =
        lempiras(descuento);

    document.getElementById('isv').textContent =
        lempiras(isv);

    document.getElementById('flete').textContent =
        lempiras(flete);

    document.getElementById('total').textContent =
        lempiras(total);
}

// Actualizar cálculos cuando cambien las cantidades
cantidades.forEach(function(input) {

    input.addEventListener('input', calcularResumen);

});

// Actualizar cálculo cuando cambie el flete
selectorFlete.addEventListener('change', calcularResumen);

// Mostrar valores iniciales
calcularResumen();

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

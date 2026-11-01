<?php
require_once __DIR__ . '/conexiones/conexionPDO.php';
require_once __DIR__ . '/factura.php';

// Crear el objeto Factura
$factura = new Factura($pdo);

// Consultar repuestos y sucursales
$repuestos = $factura->listarRepuestos();
$sucursales = $factura->listarSucursales();

// Variables para mensajes
$mensaje = "";
$tipoMensaje = "";
$numeroFactura = "";

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $rtn = trim($_POST['rtn'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $sucursalId = (int)($_POST['sucursal_id'] ?? 0);
        $tipoFlete = $_POST['tipo_flete'] ?? '';

        // Obtener productos seleccionados
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

        // Guardar factura y obtener su número
        $numeroFactura = $factura->guardarFactura(
            $rtn,
            $telefono,
            $sucursalId,
            $tipoFlete,
            $productos
        );

        $mensaje = "¡Factura generada correctamente!";
        $tipoMensaje = "success";

        // Actualizar existencias
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

    <title>Facturación SAR - Don Pancho</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../css/Estilos.css">
</head>

<body>

<div class="container py-5">

    <h1 class="mb-4">Facturación SAR</h1>

    <?php if ($mensaje !== ""): ?>

        <div class="alert alert-<?= $tipoMensaje === 'success' ? 'success' : 'danger' ?>">

            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>

            <?php if ($numeroFactura !== ""): ?>
                <br>
                <strong>Número de factura:</strong>
                <?= htmlspecialchars($numeroFactura, ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>

        </div>

    <?php endif; ?>

    <form method="POST" action="">

        <h2 class="mb-3">Datos del cliente</h2>

        <div class="mb-3">

            <label for="rtn" class="form-label">RTN:</label>

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

        <div class="mb-3">

            <label for="telefono" class="form-label">Teléfono:</label>

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

        <h2 class="mb-3">Seleccionar sucursal</h2>

        <div class="mb-3">

            <select
                name="sucursal_id"
                class="form-select"
                required
            >

                <option value="">Seleccione una sucursal</option>

                <?php foreach ($sucursales as $sucursal): ?>

                    <option value="<?= (int)$sucursal['id'] ?>">

                        <?= htmlspecialchars(
                            $sucursal['nombre'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                        -
                        <?= htmlspecialchars(
                            $sucursal['direccion'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <h2 class="mb-3">Seleccionar repuestos</h2>

        <?php if (empty($repuestos)): ?>

            <p>No hay repuestos disponibles en inventario.</p>

        <?php else: ?>

            <?php foreach ($repuestos as $repuesto): ?>

                <div class="row align-items-center mb-3">

                    <div class="col-md-8">

                        <label
                            for="producto_<?= (int)$repuesto['id'] ?>"
                            class="form-label"
                        >

                            <strong>
                                <?= htmlspecialchars(
                                    $repuesto['nombre'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </strong>

                            - L.
                            <?= number_format(
                                (float)$repuesto['precio'],
                                2
                            ) ?>

                            (Disponibles:
                            <?= (int)$repuesto['stock'] ?>)

                        </label>

                    </div>

                    <div class="col-md-4">

                        <input
                            type="number"
                            class="form-control cantidad-producto"
                            id="producto_<?= (int)$repuesto['id'] ?>"
                            name="productos[<?= (int)$repuesto['id'] ?>]"
                            min="0"
                            max="<?= (int)$repuesto['stock'] ?>"
                            value="0"
                            data-precio="<?= htmlspecialchars(
                                (string)$repuesto['precio'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

        <h2 class="mb-3">Tipo de flete</h2>

        <div class="mb-4">

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

        <h2 class="mb-3">Resumen de compra</h2>

        <div class="card p-3 mb-4">

            <p>
                Subtotal:
                <strong id="subtotal">L. 0.00</strong>
            </p>

            <p>
                Descuento:
                <strong id="descuento">L. 0.00</strong>
            </p>

            <p>
                ISV (15%):
                <strong id="isv">L. 0.00</strong>
            </p>

            <p>
                Flete:
                <strong id="flete">L. 0.00</strong>
            </p>

            <hr>

            <h4>
                Total a pagar:
                <span id="total">L. 0.00</span>
            </h4>

            <small class="text-muted">
                El descuento se aplica al comprar 10 unidades o más.
            </small>

        </div>

        <button
            type="submit"
            class="btn btn-primary"
            name="generar_factura"
        >
            Generar factura
        </button>

    </form>

</div>

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

</body>
</html>

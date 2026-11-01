<?php
require __DIR__ . '/conexiones/conexionPDO.php';

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

function e($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Arma Tu Kit | Autopartes Don Pancho</title>

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

<?php include __DIR__ . '/navbar/navbar.php'; ?>


<!-- =====================================
     BANNER PRINCIPAL
====================================== -->

<header class="hero">

    <div class="container">

        <span class="hero-etiqueta">
            AUTOPARTES Y REPUESTOS
        </span>

        <h1>
            Arma Tu Kit
        </h1>

    </div>

</header>


<main>
<?php
// ---------- Datos para el formulario ----------
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")
                  ->fetchAll(PDO::FETCH_ASSOC);

$productosPorCategoria = [];
$consulta = $pdo->query(
    "SELECT id, nombre, precio, categoria_id
     FROM repuestos
     WHERE stock > 0
     ORDER BY nombre"
);
foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $p) {
    $productosPorCategoria[$p['categoria_id']][] = $p;
}

// ---------- Procesar el envío ----------
$errores   = [];
$resumen   = [];
$totalKit  = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca      = trim($_POST['marca'] ?? '');
    $modelo     = trim($_POST['modelo'] ?? '');
    $anio       = (int)($_POST['anio'] ?? 0);
    $nombreKit  = trim($_POST['nombre_kit'] ?? '');
    $seleccion  = $_POST['productos'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];

    if ($marca === '' || $modelo === '') {
        $errores[] = 'Indica la marca y el modelo del vehículo.';
    }
    if ($anio < 1980 || $anio > (int)date('Y') + 1) {
        $errores[] = 'El año del vehículo no es válido.';
    }
    if (empty($seleccion)) {
        $errores[] = 'Selecciona al menos un repuesto para tu kit.';
    }

    if (empty($errores)) {
        // Se vuelven a consultar los precios en la BD: nunca confiar en los del navegador
        $ids = array_map('intval', $seleccion);
        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare(
            "SELECT id, nombre, precio FROM productos WHERE id IN ($marcadores)"
        );
        $stmt->execute($ids);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $cant = max(1, (int)($cantidades[$p['id']] ?? 1));
            $subtotal = $p['precio'] * $cant;
            $totalKit += $subtotal;
            $resumen[] = [
                'nombre'   => $p['nombre'],
                'cantidad' => $cant,
                'precio'   => $p['precio'],
                'subtotal' => $subtotal,
            ];
        }
        // Aquí iría el INSERT para guardar el kit si la tarea lo pide
    }
}
?>

<section class="py-5">
    <div class="container">

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($resumen)): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">
                        Resumen de tu kit
                        <?= $nombreKit !== '' ? '"' . e($nombreKit) . '"' : '' ?>
                    </h4>
                    <p class="text-muted">
                        Vehículo: <?= e($marca) ?> <?= e($modelo) ?> (<?= e($anio) ?>)
                    </p>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Repuesto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resumen as $fila): ?>
                                    <tr>
                                        <td><?= e($fila['nombre']) ?></td>
                                        <td class="text-center"><?= e($fila['cantidad']) ?></td>
                                        <td class="text-end">L <?= number_format($fila['precio'], 2) ?></td>
                                        <td class="text-end">L <?= number_format($fila['subtotal'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end">L <?= number_format($totalKit, 2) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="armar_kit.php" id="formKit">

            <!-- Paso 1: vehículo -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">1. Datos del vehículo</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca"
                                   value="<?= e($_POST['marca'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="modelo" name="modelo"
                                   value="<?= e($_POST['modelo'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="anio" class="form-label">Año</label>
                            <input type="number" class="form-control" id="anio" name="anio"
                                   min="1980" max="<?= date('Y') + 1 ?>"
                                   value="<?= e($_POST['anio'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paso 2: nombre del kit -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">2. Nombre del kit</h5>
                    <input type="text" class="form-control" name="nombre_kit"
                           placeholder="Ej: Mantenimiento 10,000 km"
                           value="<?= e($_POST['nombre_kit'] ?? '') ?>">
                </div>
            </div>

            <!-- Paso 3: repuestos por categoría -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">3. Elige los repuestos</h5>

                    <div class="accordion" id="accordionKit">
                        <?php foreach ($categorias as $i => $cat): ?>
                            <?php $lista = $productosPorCategoria[$cat['id']] ?? []; ?>
                            <?php if (empty($lista)) continue; ?>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#cat<?= e($cat['id']) ?>">
                                        <?= e($cat['nombre']) ?>
                                    </button>
                                </h2>

                                <div id="cat<?= e($cat['id']) ?>"
                                     class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                                     data-bs-parent="#accordionKit">
                                    <div class="accordion-body">

                                        <?php foreach ($lista as $p): ?>
                                            <div class="row align-items-center mb-2">
                                                <div class="col-md-7">
                                                    <div class="form-check">
                                                        <input class="form-check-input item-kit"
                                                               type="checkbox"
                                                               name="productos[]"
                                                               value="<?= e($p['id']) ?>"
                                                               id="prod<?= e($p['id']) ?>"
                                                               data-precio="<?= e($p['precio']) ?>"
                                                               <?= in_array($p['id'], $_POST['productos'] ?? []) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="prod<?= e($p['id']) ?>">
                                                            <?= e($p['nombre']) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 text-md-end">
                                                    L <?= number_format($p['precio'], 2) ?>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="number"
                                                           class="form-control form-control-sm cantidad-kit"
                                                           name="cantidad[<?= e($p['id']) ?>]"
                                                           value="<?= e($_POST['cantidad'][$p['id']] ?? 1) ?>"
                                                           min="1" max="99"
                                                           aria-label="Cantidad">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Total en vivo + envío -->
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    Total estimado: L <span id="totalVivo">0.00</span>
                </h4>
                <button type="submit" class="btn btn-vino btn-lg">Armar kit</button>
            </div>

        </form>
    </div>
</section>

</main>


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

    <script>
        (function () {
            const items = document.querySelectorAll('.item-kit');
            const totalSpan = document.getElementById('totalVivo');
    
            function recalcular() {
                let total = 0;
                items.forEach(chk => {
                    if (chk.checked) {
                        const fila = chk.closest('.row');
                        const cant = parseInt(fila.querySelector('.cantidad-kit').value) || 1;
                        total += parseFloat(chk.dataset.precio) * cant;
                    }
                });
                totalSpan.textContent = total.toFixed(2);
            }
    
            document.querySelectorAll('.item-kit, .cantidad-kit').forEach(el => {
                el.addEventListener('input', recalcular);
                el.addEventListener('change', recalcular);
            });
            recalcular();
        })();
    </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/conexiones/conexionPDO.php';
require_once __DIR__ . '/repuestos.php';

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

function e($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

$rol = $_SESSION['usuario']['rol'] ?? null;
if (!in_array($rol, ['admin', 'bodeguero'], true)) {
    header('Location: login.php');
    exit;
}

$repuestos=[];
if($pdo !== null){
    $repo = new Repuesto($pdo);
    $repuestos = $repo->listar();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $accion = $_POST['action'] ?? '';
    if($accion === 'eliminar'){
        $repo->eliminar((int) ($_POST['id'] ?? 0));
        header("Location: crud_repuestos.php");
        exit;
    }

    if($accion === 'crear'){

        $archivo = $_FILES['fotografia'];

        if($archivo['error'] !== UPLOAD_ERR_OK){
            die("Error al recibir la fotografía. Código: " . $archivo['error']);
        }

        $maxBytes = 4 * 1024 * 1024;

        if($archivo['size'] > $maxBytes){
            die("Error: La fotografía supera los 4 MB.");
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if(!in_array($extension, $extensionesPermitidas, true)){
            die("Error: Extensión de imagen no permitida.");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($archivo['tmp_name']);

        $mimesValidos = ['image/jpeg', 'image/png', 'image/webp'];

        if(!in_array($mimeReal, $mimesValidos, true)){
            die("Error: El archivo no es una imagen válida.");
        }

        $nuevoNombre = uniqid('prod_', true) . '.' . $extension;

        $carpetaDestino = __DIR__ . '/../img/';

        if(!is_dir($carpetaDestino)){
            mkdir($carpetaDestino, 0755, true);
        }

        $rutaFinal = $carpetaDestino . $nuevoNombre;

        if(move_uploaded_file($archivo['tmp_name'], $rutaFinal)){

            $repo->insertar(
                (int)($_POST['categoria_id'] ?? 0),
                (string)($_POST['nombre'] ?? ''),
                (string)($_POST['descripcion'] ?? ''),
                (float)($_POST['precio'] ?? 0),
                (int)($_POST['stock'] ?? 0),
                $nuevoNombre,
                (string)($_POST['insignia'] ?? '')
            );

            header("Location: crud_repuestos.php");
            exit;

        }else{
            die("Error al guardar la fotografía.");
        }
    }
    if($accion === 'actualizar'){
        $repo->actualizar(
            (int)($_POST['id'] ?? 0),
            (float)($_POST['precio'] ?? 0),
            (int)($_POST['stock'] ?? 0),
            (string)($_POST['insignia'] ?? '')
        );

        header("Location: crud_repuestos.php");
        exit;
    }    
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Repuestos | Don Pancho</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="../css/Estilos.css">
</head>
<body>

    <?php include __DIR__ . '/navbar/navbar_admin.php'; ?>

    <header class="catalogo-header">

        <div class="container">

            <span class="subtitulo">
                PANEL DE ADMINISTRACIÓN
            </span>

            <h1>
                Gestión de repuestos
            </h1>

            <p>
                Administra los productos registrados en Don Pancho.
            </p>

        </div>

    </header>

    <main class="container py-5">
        <button type="button" id="btncrear" class="btn btn-vino mb-3">Insertar Producto</button>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">

                    <table class="table table-hover table-striped align-middle mb-0">

                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>ID Categoria</th>
                                <th>Descripcion</th>
                                <th>Fotografia</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Insignia</th>
                                <th>Accion</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach($repuestos as $p): ?>

                            <tr>
                                <td><?= ($p['id']) ?></td>
                                <td><?= ($p['nombre']) ?></td>
                                <td><?= ($p['categoria_id']) ?></td>
                                <td><?= ($p['descripcion']) ?></td>
                                <td>
                                    <img
                                        src="../img/<?= htmlspecialchars($p['fotografia'], ENT_QUOTES, 'UTF-8') ?>"
                                        width="100"
                                        alt="Fotografía del producto"
                                        class="rounded"
                                    >
                                </td>
                                <td>L. <?= ($p['precio']) ?></td>
                                <td><?= ($p['stock']) ?></td>
                                <td><?= ($p['insignia']) ?></td>
                                <td>
                                    <form
                                        method="POST"
                                        action="crud_repuestos.php"
                                        onsubmit="return confirm('Desea Eliminar el producto?');"
                                        class="mb-2"
                                    >
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Eliminar
                                        </button>
                                    </form>
                                    <button
                                        type="button"
                                        class="btnEditar btn btn-sm btn-outline-primary"
                                        data-id="<?= $p['id'] ?>"
                                        data-precio="<?= $p['precio'] ?>"
                                        data-stock="<?= $p['stock'] ?>"
                                        data-insignia="<?= htmlspecialchars($p['insignia'], ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        Editar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <dialog id="modalCrear">

        <div>

            <div class="modal-header">
                <h2 class="modal-title">Registrar Repuesto</h2>
            </div>

            <form method="POST" action="crud_repuestos.php" enctype="multipart/form-data">

                <input type="hidden" name="action" value="crear">

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ID Categoría</label>
                        <input type="number" name="categoria_id" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <input type="number" name="precio" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fotografía</label>
                        <input
                            type="file"
                            name="fotografia"
                            class="form-control"
                            accept="image/jpeg, image/png, image/webp"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Insignia</label>

                        <select name="insignia" class="form-select" required>
                            <option value="Destacado">Destacado</option>
                            <option value="NoDestacado" selected>No Destacado</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="submit" class="btn btn-vino">
                        Guardar
                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="document.getElementById('modalCrear').close();"
                    >
                        Cancelar
                    </button>

                </div>

            </form>

        </div>

    </dialog>

    <dialog id="modalEditar">

        <div>

            <div class="modal-header">
                <h2 class="modal-title">Actualizar Repuesto</h2>
            </div>

            <form method="POST" action="crud_repuestos.php">

                <input type="hidden" name="action" value="actualizar">
                <input type="hidden" name="id" id="editarId">

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <input
                            type="number"
                            name="precio"
                            id="editarPrecio"
                            class="form-control"
                            min="0"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input
                            type="number"
                            name="stock"
                            id="editarStock"
                            class="form-control"
                            min="0"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Insignia</label>

                        <select
                            name="insignia"
                            id="editarInsignia"
                            class="form-select"
                            required
                        >
                            <option value="Destacado">Destacado</option>
                            <option value="NoDestacado" selected>No Destacado</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="submit" class="btn btn-vino">
                        Guardar Cambios
                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="document.getElementById('modalEditar').close();"
                    >
                        Cancelar
                    </button>

                </div>

            </form>

        </div>

    </dialog>
    
    <footer class="footer-donpancho">
        
        <div class="container">
            
            <div class="row g-4">
                
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
                
                <div class="col-md-3">
                    
                    <h5>
                        Enlaces rápidos
                    </h5>
                    
                    <ul class="footer-links">
                        
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="catalogo.php">Catálogo</a></li>
                        <li><a href="kit.php">Arma tu Kit</a></li>
                        <li><a href="facturacion.php">Facturación SAR</a></li>
                        <li><a href="sucursales.php">Sucursales</a></li>
                        
                    </ul>
                    
                </div>
                
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
        
        <div class="text-center mb-3">
            
            <h5>
                Síguenos
            </h5>
            
            <div class="redes">

                <a href="#" aria-label="Facebook">Facebook</a>
                <a href="#" aria-label="Instagram">Instagram</a>
                <a href="#" aria-label="WhatsApp">WhatsApp</a>
                
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

    <script>
        const modalCrear = document.getElementById('modalCrear')
        document.getElementById('btncrear').addEventListener('click',()=>{
            modalCrear.showModal();
        })

        const modalEditar = document.getElementById('modalEditar');

        const botonesEditar = document.querySelectorAll('.btnEditar');

        botonesEditar.forEach(boton => {

            boton.addEventListener('click', () => {

                document.getElementById('editarId').value = boton.dataset.id;

                document.getElementById('editarPrecio').value = boton.dataset.precio;

                document.getElementById('editarStock').value = boton.dataset.stock;

                document.getElementById('editarInsignia').value = boton.dataset.insignia;

                modalEditar.showModal();

            });

        });
    </script>

</body>
</html>

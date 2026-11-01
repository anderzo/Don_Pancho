<?php
declare(strict_types=1);
require __DIR__ . '/conexiones/conexionPDO.php';
require_once __DIR__ . '/repuestos.php';

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
    <title>Lista Repuestos</title>
</head>
<body>
    <button type="button" id="btncrear">Insertar Producto</button>
    <table border="1" cellpadding="6">
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
                >
            </td>
            <td><?= ($p['precio']) ?></td>
            <td><?= ($p['stock']) ?></td>
            <td><?= ($p['insignia']) ?></td>
            <td>
                <form method="POST"  action="crud_repuestos.php" onsubmit="return confirm('Desea Eliminar el producto?');">
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="id" value="<?=$p['id']?>">
                    <button type="submit">Eliminar</button>
                </form>
                <button 
                    type="button"
                    class="btnEditar"
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
    </table>
    <dialog id="modalCrear">
        <h2>Registrar Repuesto</h2>
        <form method="POST" action="crud_repuestos.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="crear">
            <label>Nombre</label>
            <input type="text" name="nombre"><br>
            <label>Id Categoria</label>
            <input type="number" name="categoria_id"><br>
            <label>Descripcion</label>
            <input type="text" name="descripcion"><br>
            <label>Precio</label>
            <input type="number" name="precio"><br>
            <label>Stock</label>
            <input type="number" name="stock"><br>
            <label>Fotografia</label>
            <input type="file" name="fotografia" accept="image/jpeg, image/png, image/webp" required>
            <label>Insignia</label>
            <select name="insignia" required>
                <option value="Destacado">Destacado</option>
                <option value="NoDestacado" selected>No Destacado</option>
            </select>
            <button type="submit">Guardar</button>
            <button type="button" onclick="document.getElementById('modalCrear').close();"></button>
        </form>

    </dialog>
    <dialog id="modalEditar">
        <h2>Actualizar Repuesto</h2>

        <form method="POST" action="crud_repuestos.php">

            <input type="hidden" name="action" value="actualizar">
            <input type="hidden" name="id" id="editarId">

            <label>Precio</label>
            <input type="number" name="precio" id="editarPrecio" min="0" required>
            <br>

            <label>Stock</label>
            <input type="number" name="stock" id="editarStock" min="0" required>
            <br>

            <label>Insignia</label>
            <select name="insignia" id="editarInsignia" required>
                <option value="Destacado">Destacado</option>
                <option value="NoDestacado" selected>No Destacado</option>
            </select>
            <br>

            <button type="submit">Guardar Cambios</button>

            <button type="button" onclick="document.getElementById('modalEditar').close();">
                Cancelar
            </button>

        </form>
    </dialog>
</body>
</html>
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
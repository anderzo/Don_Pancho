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
        $repo->insertar(
            (string)($_POST['categoria_id']) ?? '',
            (string)($_POST['nombre'])?? '',
            (string)($_POST['descripcion'])?? '',
            (float)($_POST['precio'])?? 0,
            (int)($_POST['stock'])?? 0,
            (string)($_POST['fotografia'])?? '',
            (string)($_POST['insignia'])?? '');
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
    <title>Productos</title>
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
            <td><?= ($p['fotografia']) ?></td>
            <td><?= ($p['precio']) ?></td>
            <td><?= ($p['stock']) ?></td>
            <td><?= ($p['insignia']) ?></td>
            <td>
                <form method="POST"  action="crud_repuestos.php" onsubmit="return confirm('Desea Eliminar el producto?');">
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="id" value="<?=$p['id']?>">
                    <button type="submit">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach ?>
    </table>
    <dialog id="modalCrear">
        <h2>Registrar Repuesto</h2>
        <form method="POST" action="crud_repuestos.php">
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
            <input type="string" name="fotografia"><br>
            <label>Insignia</label>
            <input type="string" name="insignia"><br>
            <button type="submit">Guardar</button>
            <button type="button" onclick="document.getElementById('modalCrear').close();"></button>
        </form>

    </dialog>
</body>
</html>
<script>
    const modalCrear = document.getElementById('modalCrear')
    document.getElementById('btncrear').addEventListener('click',()=>{
        modalCrear.showModal();
    })
</script>
<?php
require __DIR__ . '/conexiones/conexionPDO.php';

try {
    $repuestos = $pdo->query(
        "SELECT r.id, c.nombre AS categoria, r.nombre, r.descripcion,
                r.precio, r.stock, r.insignia
         FROM repuestos r
         JOIN categorias c ON c.id = r.categoria_id"
    )->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Repuestos</title>
</head>
<body>
    <h1>Repuestos</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Categoría</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Insignia</th>
        </tr>
        <?php foreach ($repuestos as $r): ?>
        <tr>
            <td><?= ($r['id']) ?></td>
            <td><?= ($r['categoria']) ?></td>
            <td><?= ($r['nombre']) ?></td>
            <td><?= ($r['descripcion']) ?></td>
            <td><?= ($r['precio']) ?></td>
            <td><?= ($r['stock']) ?></td>
            <td><?= ($r['insignia']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
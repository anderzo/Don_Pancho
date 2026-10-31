
<?php
require __DIR__ . '/conexiones/conexionPDO.php';

try {
    $repuestos = $pdo->query(
        "SELECT r.id, c.nombre AS categoria, r.nombre, r.descripcion,
                r.precio, r.stock, r.insignia, r.fotografia
         FROM repuestos r
         JOIN categorias c ON c.id = r.categoria_id"
    )->fetchAll();

    $sucursales = $pdo->query(
        "SELECT nombre, direccion FROM sucursales"
    )->fetchAll();

} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}

function e($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// OBTENER LAS CATEGORÍAS DISPONIBLES
$categorias = [];

foreach ($repuestos as $r) {
    $categorias[$r['categoria']] = $r['categoria'];
}

// OBTENER LA CATEGORÍA SELECCIONADA
$filtro = '';

if (isset($_GET['categoria']) && is_string($_GET['categoria'])) {
    $filtro = trim($_GET['categoria']);
}

// VALIDAR LA CATEGORÍA
if ($filtro !== '' && !isset($categorias[$filtro])) {
    $filtro = '';
}

// FILTRAR LOS REPUESTOS
$productosFiltrados = [];

foreach ($repuestos as $r) {

    if ($filtro === '' || $r['categoria'] === $filtro) {
        $productosFiltrados[] = $r;
    }

}
?>

<?php
$sesion   = $_SESSION['usuario'] ?? null;
$logueado = is_array($sesion);
$actual   = basename($_SERVER['PHP_SELF']);

function activo(string $pagina, string $actual): string {
    return $pagina === $actual ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-donpancho">
    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">DON <span>PANCHO</span></a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false"
                aria-label="Mostrar menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link <?= activo('index.php', $actual) ?>" href="index.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= activo('catalogo.php', $actual) ?>" href="catalogo.php">Catálogo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= activo('armar_kit.php', $actual) ?>" href="armar_kit.php">Arma Tu Kit</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= activo('sucursales.php', $actual) ?>" href="sucursales.php">Sucursales</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= activo('facturas.php', $actual) ?>" href="facturas.php">Facturas</a>
                </li>

                <?php if ($logueado): ?>
                    <li class="nav-item">
                        <span class="nav-link">
                            <?= htmlspecialchars($sesion['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar sesión</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= activo('login.php', $actual) ?>" href="login.php">Iniciar Sesión</a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>
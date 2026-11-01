<?php
$sesion   = $_SESSION['usuario'] ?? null;
$logueado = is_array($sesion);
$actual   = basename($_SERVER['PHP_SELF']);

/* =========================================
   CONTROL DE SESION
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

function activo(string $pagina, string $actual): string {
    return $pagina === $actual ? 'active' : '';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-donpancho">

    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            DON <span>PANCHO</span> - Portal Administrativo
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

                <li class="nav-item ms-lg-3">
                    <a class="nav-link <?= activo('crud_repuestos.php', $actual) ?>" href="crud_repuestos.php">
                        Administrar repuestos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= activo('facturacion.php', $actual) ?>" href="facturacion.php">
                        Facturación SAR
                    </a>
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
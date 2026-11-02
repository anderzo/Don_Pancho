<?php
require __DIR__ . '/conexiones/conexionPDO.php';

$conexion = new ConexionBD();
$pdo = $conexion->conectar();

function destinoPorRol(string $rol): string {
    return in_array($rol, ['admin', 'bodeguero'], true)
        ? 'crud_repuestos.php'   // ajusta a la ruta de tu portal administrativo
        : 'index.php';
}

function e($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

// Si ya inició sesión, no tiene sentido ver esta página
if (isset($_SESSION['usuario']['rol'])) {
    header('Location: ' . destinoPorRol($_SESSION['usuario']['rol']));
    exit;
}

$errorLogin       = '';
$erroresRegistro  = [];
$usuarioIngresado = '';

// Pestaña activa: por defecto login; registro si se pide por URL o se envió ese formulario
$tabActiva = (($_GET['tab'] ?? '') === 'registro') ? 'registro' : 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $accion = $_POST['accion'] ?? '';

    /* ---------- INICIO DE SESIÓN ---------- */
    if ($accion === 'login') {
        $tabActiva = 'login';

        $usuarioIngresado = trim($_POST['usuario'] ?? '');
        $password         = $_POST['password'] ?? '';

        $stmt = $pdo->prepare(
            "SELECT id, nombre, usuario, password, rol, estado, sucursal_id
             FROM usuarios
             WHERE usuario = ?"
        );
        $stmt->execute([$usuarioIngresado]);
        $fila = $stmt->fetch();

        if ($fila && $fila['estado'] === 'activo' && password_verify($password, $fila['password'])) {
            session_regenerate_id(true); // evita fijación de sesión

            $_SESSION['usuario'] = [
                'id'          => (int)$fila['id'],
                'nombre'      => $fila['nombre'],
                'usuario'     => $fila['usuario'],
                'rol'         => $fila['rol'],
                'sucursal_id' => $fila['sucursal_id'],
            ];

            header('Location: ' . destinoPorRol($fila['rol']));
            exit;
        }

        // Mismo mensaje para usuario inexistente, clave mala o cuenta inactiva
        $errorLogin = 'Usuario o contraseña incorrectos.';
    }

    /* ---------- REGISTRO ---------- */
    if ($accion === 'registro') {
        $tabActiva = 'registro';

        $nombre    = trim($_POST['nombre'] ?? '');
        $usuario   = trim($_POST['usuario'] ?? '');
        $telefono  = trim($_POST['telefono'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirmar = $_POST['confirmar'] ?? '';

        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $erroresRegistro[] = 'Ingresa tu nombre completo (máximo 100 caracteres).';
        }
        if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $usuario)) {
            $erroresRegistro[] = 'El usuario debe tener de 3 a 50 caracteres: letras, números o guion bajo.';
        }
        if (!preg_match('/^[0-9+\-\s]{8,20}$/', $telefono)) {
            $erroresRegistro[] = 'Ingresa un teléfono válido.';
        }
        if (strlen($password) < 6) {
            $erroresRegistro[] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        if ($password !== $confirmar) {
            $erroresRegistro[] = 'Las contraseñas no coinciden.';
        }

        if (empty($erroresRegistro)) {
            $stmt = $pdo->prepare("SELECT 1 FROM usuarios WHERE usuario = ?");
            $stmt->execute([$usuario]);
            if ($stmt->fetch()) {
                $erroresRegistro[] = 'Ese nombre de usuario ya está en uso.';
            }
        }

        if (empty($erroresRegistro)) {
            try {
                // El rol lo fija el servidor: el registro web siempre crea 'usuario'
                $stmt = $pdo->prepare(
                    "INSERT INTO usuarios (nombre, usuario, password, telefono, rol, estado, sucursal_id)
                     VALUES (?, ?, ?, ?, 'usuario', 'activo', NULL)"
                );
                $stmt->execute([
                    $nombre,
                    $usuario,
                    password_hash($password, PASSWORD_DEFAULT),
                    $telefono,
                ]);

                header('Location: login.php?registro=ok');
                exit;
            } catch (PDOException $ex) {
                // 23000 = violación de UNIQUE si dos personas registran el mismo usuario a la vez
                $erroresRegistro[] = ($ex->getCode() === '23000')
                    ? 'Ese nombre de usuario ya está en uso.'
                    : 'No se pudo crear la cuenta. Inténtalo de nuevo.';
            }
        }
    }
}

// Solo se rellenan los campos del formulario que se envió
$post = ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registro') ? $_POST : [];
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso | Autopartes Don Pancho</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/Estilos.css">
    </head>
    
    <body>
        <?php include __DIR__ . '/navbar/navbar.php'; ?>

        <main class="py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-5">

                        <div class="card">
                            <div class="card-body p-4">

                                <!-- Pestañas -->
                                <ul class="nav nav-tabs nav-fill mb-4" id="tabsAcceso" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?= $tabActiva === 'login' ? 'active' : '' ?>"
                                                id="tab-login" type="button" role="tab"
                                                data-bs-toggle="tab" data-bs-target="#panel-login"
                                                aria-controls="panel-login"
                                                aria-selected="<?= $tabActiva === 'login' ? 'true' : 'false' ?>">
                                            Iniciar sesión
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link <?= $tabActiva === 'registro' ? 'active' : '' ?>"
                                                id="tab-registro" type="button" role="tab"
                                                data-bs-toggle="tab" data-bs-target="#panel-registro"
                                                aria-controls="panel-registro"
                                                aria-selected="<?= $tabActiva === 'registro' ? 'true' : 'false' ?>">
                                            Crear cuenta
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content">

                                    <!-- Panel: login -->
                                    <div class="tab-pane fade <?= $tabActiva === 'login' ? 'show active' : '' ?>"
                                        id="panel-login" role="tabpanel" aria-labelledby="tab-login">

                                        <?php if (($_GET['registro'] ?? '') === 'ok'): ?>
                                            <div class="alert alert-success">
                                                Cuenta creada. Ya puedes iniciar sesión.
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($errorLogin !== ''): ?>
                                            <div class="alert alert-danger"><?= e($errorLogin) ?></div>
                                        <?php endif; ?>

                                        <form method="POST" action="login.php">
                                            <input type="hidden" name="accion" value="login">

                                            <div class="mb-3">
                                                <label for="login_usuario" class="form-label">Usuario</label>
                                                <input type="text" class="form-control" id="login_usuario"
                                                    name="usuario" value="<?= e($usuarioIngresado) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="login_password" class="form-label">Contraseña</label>
                                                <input type="password" class="form-control" id="login_password"
                                                    name="password" required>
                                            </div>

                                            <button type="submit" class="btn btn-vino w-100">Entrar</button>
                                        </form>
                                    </div>

                                    <!-- Panel: registro -->
                                    <div class="tab-pane fade <?= $tabActiva === 'registro' ? 'show active' : '' ?>"
                                        id="panel-registro" role="tabpanel" aria-labelledby="tab-registro">

                                        <?php if (!empty($erroresRegistro)): ?>
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    <?php foreach ($erroresRegistro as $err): ?>
                                                        <li><?= e($err) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <form method="POST" action="login.php">
                                            <input type="hidden" name="accion" value="registro">

                                            <div class="mb-3">
                                                <label for="reg_nombre" class="form-label">Nombre completo</label>
                                                <input type="text" class="form-control" id="reg_nombre" name="nombre"
                                                    value="<?= e($post['nombre'] ?? '') ?>" maxlength="100" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="reg_usuario" class="form-label">Usuario</label>
                                                <input type="text" class="form-control" id="reg_usuario" name="usuario"
                                                    value="<?= e($post['usuario'] ?? '') ?>" maxlength="50" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="reg_telefono" class="form-label">Teléfono</label>
                                                <input type="tel" class="form-control" id="reg_telefono" name="telefono"
                                                    value="<?= e($post['telefono'] ?? '') ?>" maxlength="20" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="reg_password" class="form-label">Contraseña</label>
                                                <input type="password" class="form-control" id="reg_password"
                                                    name="password" minlength="6" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="reg_confirmar" class="form-label">Confirmar contraseña</label>
                                                <input type="password" class="form-control" id="reg_confirmar"
                                                    name="confirmar" minlength="6" required>
                                            </div>

                                            <button type="submit" class="btn btn-vino w-100">Registrarme</button>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
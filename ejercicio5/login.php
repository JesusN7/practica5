<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar los datos de entrada
    $dni = htmlspecialchars(trim($_POST['dni'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($dni) || empty($password)) {
        $errores[] = "Todos los campos son obligatorios.";
    } elseif (!validarDni($dni)) {
        $errores[] = "El DNI no es válido.";
    } elseif (!longPassword($password)) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        try {
            $conexion = conectar();

            // Consultar al usuario por DNI
            $sql = "SELECT * FROM clientes WHERE dni = :dni";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':dni' => $dni]);
            $usuario = $stmt->fetch();

            // Verificar si el usuario existe y la contraseña es correcta
            if ($usuario && password_verify($password, $usuario['password'])) {
                $_SESSION['dni'] = $usuario['dni'];
                $_SESSION['rol'] = $usuario['rol'];

                // Redirigir según el rol
                switch ($usuario['rol']) {
                    case 'administrador':
                        header("Location: admin.php");
                        break;
                    case 'editor':
                        header("Location: editor.php");
                        break;
                    case 'usuario':
                        header("Location: usuario.php");
                        break;
                    default:
                        $errores[] = "Rol desconocido.";
                }
                exit();
            } else {
                $errores[] = "DNI o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            error_log("Error en el login: " . $e->getMessage());
            $errores[] = "Error en el servidor, intenta más tarde.";
        }
    }

    // Guardar errores en sesión y redirigir al login
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center justify-content-center vh-100" style="background-color: #f8f9fa;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow p-4">
                    <h2 class="text-center mb-4">Iniciar Sesión</h2>

                    <?php if (isset($_SESSION['errores'])): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($_SESSION['errores'] as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['errores']); ?>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="dni" class="form-label">DNI:</label>
                            <input type="text" name="dni" id="dni" class="form-control" required pattern="[0-9]{8}[A-Z]" title="Debe tener 8 números seguidos de una letra mayúscula" placeholder="Ej: 12345678A">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña:</label>
                            <input type="password" name="password" id="password" class="form-control" required minlength="8" title="Debe tener al menos 8 caracteres" placeholder="Contraseña">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
                    </form>

                    <div class="text-center mt-3">
                        <p><a href="recuperar_contrasena.php">¿Olvidaste tu contraseña?</a></p>
                        <p><a href="registro.php">¿No tienes una cuenta? Regístrate</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
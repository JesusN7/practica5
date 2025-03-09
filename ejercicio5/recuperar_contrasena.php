<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

$errores = [];
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dni = htmlspecialchars(trim($_POST['dni'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $newPassword = trim($_POST['password'] ?? '');

    $pdo = conectar();

    // Verificar si el usuario existe con su DNI y correo
    if (!empty($dni) && !empty($email) && empty($newPassword)) {
        if (!validarDni($dni)) {
            $errores[] = "El DNI no es válido.";
        }
        if (!validarEmail($email)) {
            $errores[] = "El correo electrónico no es válido.";
        }

        if (empty($errores)) {
            $query = "SELECT dni FROM clientes WHERE dni = :dni AND email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':dni' => $dni, ':email' => $email]);
            $usuario = $stmt->fetch();

            if ($usuario) {
                // Guardamos el DNI en sesión temporalmente para el segundo paso
                $_SESSION['dni_recuperacion'] = $dni;
                header("Location: crear_contrasena.php");
                exit();
            } else {
                $errores[] = "No se ha encontrado el usuario con los datos proporcionados.";
            }
        }
    }

    // Cambiar la contraseña
    if (!empty($newPassword) && isset($_SESSION['dni_recuperacion'])) {
        $dniRecuperado = $_SESSION['dni_recuperacion'];

        if (!longPassword($newPassword)) {
            $errores[] = "La nueva contraseña debe tener al menos 8 caracteres.";
        }

        if (empty($errores)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = "UPDATE clientes SET password = :password WHERE dni = :dni";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':password' => $hashedPassword, ':dni' => $dniRecuperado]);

            if ($stmt->rowCount() > 0) {
                unset($_SESSION['dni_recuperacion']);
                $_SESSION['mensaje_exito'] = "Tu contraseña ha sido actualizada con éxito.";
                header("Location: login.php");
                exit();
            } else {
                $errores[] = "Error al actualizar la contraseña.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Recuperar Contraseña</h4>
                    </div>
                    <div class="card-body">

                        <!-- Mostrar errores -->
                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach ($errores as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Mostrar mensaje de éxito -->
                        <?php if (isset($_SESSION['mensaje_exito'])): ?>
                            <div class="alert alert-success text-center">
                                <?= htmlspecialchars($_SESSION['mensaje_exito']) ?>
                            </div>
                            <?php unset($_SESSION['mensaje_exito']); ?>
                        <?php endif; ?>

                        <!-- Formulario de recuperación -->
                        <form action="recuperar_contrasena.php" method="POST">
                            <div class="mb-3">
                                <label for="dni" class="form-label">DNI:</label>
                                <input type="text" name="dni" class="form-control" required pattern="[0-9]{8}[A-Z]"
                                    title="Debe tener 8 números y 1 letra" placeholder="12345678A">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico:</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer text-center">
                        <a href="login.php" class="btn btn-secondary">Volver al Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
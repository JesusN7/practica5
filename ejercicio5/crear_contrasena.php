<?php
session_start();
require 'conexion.php';
require_once 'funciones.php';

$errores = [];
$mensaje = '';

if (!isset($_SESSION['dni_recuperacion'])) {
    header("Location: recuperar_contrasena.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = trim($_POST['password'] ?? '');
    $dni = $_SESSION['dni_recuperacion'];

    if (!longPassword($newPassword)) {
        $errores[] = "La nueva contraseña debe tener al menos 8 caracteres.";
    }

    if (empty($errores)) {
        $pdo = conectar();
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE clientes SET password = :password WHERE dni = :dni";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':password' => $hashedPassword, ':dni' => $dni]);

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
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Cambiar Contraseña</h4>
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

                        <form action="crear_contrasena.php" method="POST">
                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva Contraseña:</label>
                                <input type="password" name="password" class="form-control" required minlength="8"
                                    title="Debe tener al menos 8 caracteres" placeholder="Introduce la nueva contraseña">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
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
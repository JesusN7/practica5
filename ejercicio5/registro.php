<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = htmlspecialchars(trim($_POST['dni'] ?? ''));
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''));
    $direccion = htmlspecialchars(trim($_POST['direccion'] ?? ''));
    $localidad = htmlspecialchars(trim($_POST['localidad'] ?? ''));
    $provincia = htmlspecialchars(trim($_POST['provincia'] ?? ''));
    $telefono = trim($_POST['telefono'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password'] ?? '');

    // Validaciones
    if (!validarDni($dni)) {
        $errores[] = "El DNI no es válido.";
    }
    if (!validarTelefono($telefono)) {
        $errores[] = "El teléfono debe tener 9 dígitos.";
    }
    if (!validarEmail($email)) {
        $errores[] = "El email no es válido.";
    }
    if (!longPassword($password)) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres, incluir una mayúscula y un número.";
    }
    if (!validarCampos($nombre, $direccion, $localidad, $provincia, $email)) {
        $errores[] = "Los datos exceden el tamaño permitido.";
    }

    if (empty($errores)) {
        try {
            $conexion = conectar();

            // Verificar si el DNI ya está registrado
            $sql = "SELECT dni FROM clientes WHERE dni = :dni OR email = :email";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':dni' => $dni, ':email' => $email]);

            if ($stmt->fetch()) {
                $errores[] = "El DNI o el email ya están registrados.";
            }
        } catch (PDOException $e) {
            $errores[] = "Error en la base de datos.";
            error_log("Error de PDO: " . $e->getMessage());
        }
    }

    // Si no hay errores, registramos al usuario**
    if (empty($errores)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO clientes (dni, nombre, direccion, localidad, provincia, telefono, email, password, rol) 
                    VALUES (:dni, :nombre, :direccion, :localidad, :provincia, :telefono, :email, :password, 'usuario')";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':dni' => $dni,
                ':nombre' => $nombre,
                ':direccion' => $direccion,
                ':localidad' => $localidad,
                ':provincia' => $provincia,
                ':telefono' => $telefono,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);

            $_SESSION['mensaje_exito'] = "Usuario registrado con éxito.";
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $errores[] = "Error al registrar usuario.";
            error_log("Error de PDO: " . $e->getMessage());
        }
    }

    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header("Location: registro.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0 text-center">Registro de Usuario</h4>
                    </div>
                    <div class="card-body">

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

                        <form action="registro.php" method="POST">
                            <div class="mb-3">
                                <label for="dni" class="form-label">DNI:</label>
                                <input type="text" id="dni" name="dni" class="form-control" required pattern="[0-9]{8}[A-Z]" title="Debe tener 8 números y 1 letra" placeholder="12345678A">
                            </div>

                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="50">
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección:</label>
                                <input type="text" id="direccion" name="direccion" class="form-control" required maxlength="100">
                            </div>

                            <div class="mb-3">
                                <label for="localidad" class="form-label">Localidad:</label>
                                <input type="text" id="localidad" name="localidad" class="form-control" required maxlength="50">
                            </div>

                            <div class="mb-3">
                                <label for="provincia" class="form-label">Provincia:</label>
                                <input type="text" id="provincia" name="provincia" class="form-control" required maxlength="50">
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono:</label>
                                <input type="text" id="telefono" name="telefono" class="form-control" required pattern="[0-9]{9}" maxlength="9" title="Debe tener 9 dígitos">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" id="email" name="email" class="form-control" required maxlength="100">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña:</label>
                                <input type="password" id="password" name="password" class="form-control" required minlength="8" title="Debe tener al menos 8 caracteres" placeholder="Contraseña">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Registrar</button>
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
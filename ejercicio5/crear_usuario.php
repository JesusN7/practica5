<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $localidad = $_POST['localidad'];
    $provincia = $_POST['provincia'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $rol = $_POST['rol'];

    if (empty($dni) || empty($nombre) || empty($password) || empty($rol)) {
        $error = "Error: DNI, nombre, contraseña y rol son obligatorios.";
    } else {
        // Validación del DNI
        if (!validarDni($dni)) {
            $error = "Error: El DNI no es válido.";
        }

        // Validar correo electrónico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "El formato del correo electrónico no es válido.";
        }

        // Validar teléfono (9 dígitos)
        if (!preg_match("/^[0-9]{9}$/", $telefono)) {
            $error = "El teléfono debe tener 9 dígitos.";
        }

        // Si no hay errores, podremos crear un nuevo usuario
        if (!isset($error)) {
            try {
                $conexion = conectar();

                // Verificar si el DNI está registrado
                $sql = "SELECT COUNT(*) FROM clientes WHERE dni = :dni";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([':dni' => $dni]);
                $existe = $stmt->fetchColumn();

                if ($existe > 0) {
                    $error = "Error: El DNI ya está registrado.";
                } else {
                    // Hashear la contraseña
                    $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

                    // Insertar el nuevo usuario
                    $sql = "INSERT INTO clientes (dni, nombre, direccion, localidad, provincia, telefono, email, password, rol) 
                            VALUES (:dni, :nombre, :direccion, :localidad, :provincia, :telefono, :email, :password, :rol)";
                    $stmt = $conexion->prepare($sql);
                    $stmt->execute([
                        ':dni' => $dni,
                        ':nombre' => $nombre,
                        ':direccion' => $direccion,
                        ':localidad' => $localidad,
                        ':provincia' => $provincia,
                        ':telefono' => $telefono,
                        ':email' => $email,
                        ':password' => $passwordHashed,
                        ':rol' => $rol
                    ]);

                    $success = "Usuario creado con éxito.";
                    header("Location: admin.php");
                    exit();
                }
            } catch (PDOException $e) {
                $error = "Error al crear usuario: " . $e->getMessage();
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
    <title>Crear Usuario</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h2>Crear Usuario</h2>

        <!-- mensajes de error y éxito -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?= $success ?>
            </div>
        <?php else: ?>
            <form action="crear_usuario.php" method="POST">
                <div class="mb-3">
                    <label for="dni" class="form-label">DNI:</label>
                    <input type="text" class="form-control" name="dni" required>
                </div>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" class="form-control" name="nombre" required>
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección:</label>
                    <input type="text" class="form-control" name="direccion">
                </div>

                <div class="mb-3">
                    <label for="localidad" class="form-label">Localidad:</label>
                    <input type="text" class="form-control" name="localidad">
                </div>

                <div class="mb-3">
                    <label for="provincia" class="form-label">Provincia:</label>
                    <input type="text" class="form-control" name="provincia">
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input type="text" class="form-control" name="telefono" pattern="[0-9]{9}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico:</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña:</label>
                    <input type="password" class="form-control" name="password" required>
                </div>

                <div class="mb-3">
                    <label for="rol" class="form-label">Rol:</label>
                    <select name="rol" class="form-select" required>
                        <option value="usuario">Usuario</option>
                        <option value="editor">Editor</option>
                        <option value="administrador">Administrador</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Crear Usuario</button>
            </form>

            <br>
            <a href="admin.php" class="btn btn-secondary">Cancelar</a>
        <?php endif; ?>
    </div>

    <!-- JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
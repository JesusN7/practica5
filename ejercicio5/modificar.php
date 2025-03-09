<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

$errores = [];

if (!isset($_SESSION['dni'])) {
    header("Location: login.php");
    exit();
}

try {
    $conexion = conectar();

    // Obtener el DNI del usuario logueado
    $dniUsuario = $_SESSION['dni'];

    if (isset($_GET['dni'])) {
        $dniModificar = $_GET['dni'];

        // Verificar si el administrador está intentando modificar un usuario
        if ($_SESSION['rol'] === 'administrador') {
            // Permitir al administrador modificar cualquier usuario, pero no cambiar su propio rol
            if ($dniModificar === $dniUsuario) {
                // No se permite cambiar el rol, pero sí los demás datos
                $nuevoRol = $usuario['rol'] ?? null; // Mantener el rol actual
            }
        } else {
            // Si no es administrador, debe coincidir con su propio DNI para realizar modificaciones
            if ($dniModificar !== $dniUsuario) {
                $errores[] = "No tienes permisos para modificar los datos de otro usuario.";
            }
        }
    } else {
        $dniModificar = $dniUsuario; // Si no se especifica DNI, usa el del usuario logueado
    }

    // Obtener los datos del usuario
    $sql = "SELECT * FROM clientes WHERE dni = :dni";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':dni' => $dniModificar]);
    $usuario = $stmt->fetch();

    // Si el usuario no existe
    if (!$usuario) {
        $errores[] = "Usuario no encontrado.";
    } else {
        // Si el usuario existe, definir $nuevoRol para evitar errores
        $nuevoRol = $usuario['rol'];
    }

    // Modificación de datos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['modificar'])) {
            // Obtener los datos del formulario
            $nuevoDni = $usuario['dni']; // El DNI original se mantiene sin cambios
            $nuevoNombre = $_POST['nombre'] ?? $usuario['nombre'];
            $nuevaDireccion = $_POST['direccion'] ?? $usuario['direccion'];
            $nuevaLocalidad = $_POST['localidad'] ?? $usuario['localidad'];
            $nuevaProvincia = $_POST['provincia'] ?? $usuario['provincia'];
            $nuevoTelefono = $_POST['telefono'] ?? $usuario['telefono'];
            $nuevoEmail = $_POST['email'] ?? $usuario['email'];

            // Validar los campos
            if (!validarCampos($nuevoNombre, $nuevaDireccion, $nuevaLocalidad, $nuevaProvincia, $nuevoEmail)) {
                $errores[] = "Los datos exceden el tamaño permitido.";
            }

            // Validar teléfono y email
            if (!validarTelefono($nuevoTelefono)) {
                $errores[] = "El teléfono debe tener 9 dígitos.";
            }

            if (!validarEmail($nuevoEmail)) {
                $errores[] = "El email no es válido.";
            }

            // Verificar si el administrador está modificando otro usuario
            if ($_SESSION['rol'] === 'administrador' && $dniModificar !== $dniUsuario) {
                $nuevoRol = $_POST['rol'] ?? $usuario['rol']; // Administrador puede cambiar roles de otros usuarios
            }

            // Si no hay errores, proceder con la actualización
            if (empty($errores)) {
                $sql = "UPDATE clientes SET nombre = :nombre, direccion = :direccion, localidad = :localidad, 
                        provincia = :provincia, telefono = :telefono, email = :email, rol = :rol WHERE dni = :dni";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nuevoNombre,
                    ':direccion' => $nuevaDireccion,
                    ':localidad' => $nuevaLocalidad,
                    ':provincia' => $nuevaProvincia,
                    ':telefono' => $nuevoTelefono,
                    ':email' => $nuevoEmail,
                    ':rol' => $nuevoRol,
                    ':dni' => $dniModificar
                ]);

                $_SESSION['mensaje_exito'] = "Datos actualizados exitosamente.";

                // Redirigir según el rol del usuario
                if ($_SESSION['rol'] === 'administrador') {
                    header("Location: admin.php"); 
                } elseif ($_SESSION['rol'] === 'editor') {
                    header("Location: editor.php"); 
                } else {
                    header("Location: usuario.php");
                }
                exit();
            }
        }
    }
} catch (PDOException $e) {
    $errores[] = "Error al procesar la solicitud: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Modificar Usuario</h4>
                    </div>
                    <div class="card-body">

                        <!-- Mensajes de error -->
                        <?php if (!empty($errores)): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach ($errores as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario -->
                        <?php if (isset($usuario)): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="dni" class="form-label">DNI:</label>
                                    <input type="text" id="dni" name="dni" class="form-control"
                                        value="<?= htmlspecialchars($usuario['dni']) ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre:</label>
                                    <input type="text" id="nombre" name="nombre" class="form-control"
                                        value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="direccion" class="form-label">Dirección:</label>
                                    <input type="text" id="direccion" name="direccion" class="form-control"
                                        value="<?= htmlspecialchars($usuario['direccion']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="localidad" class="form-label">Localidad:</label>
                                    <input type="text" id="localidad" name="localidad" class="form-control"
                                        value="<?= htmlspecialchars($usuario['localidad']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="provincia" class="form-label">Provincia:</label>
                                    <input type="text" id="provincia" name="provincia" class="form-control"
                                        value="<?= htmlspecialchars($usuario['provincia']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Teléfono:</label>
                                    <input type="text" id="telefono" name="telefono" class="form-control"
                                        pattern="[0-9]{9}" value="<?= htmlspecialchars($usuario['telefono']) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email:</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                        value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                </div>

                                <!-- Solo el administrador puede modificar el rol de otros usuarios -->
                                <?php if ($_SESSION['rol'] === 'administrador' && $dniModificar !== $dniUsuario): ?>
                                    <div class="mb-3">
                                        <label for="rol" class="form-label">Rol:</label>
                                        <select name="rol" id="rol" class="form-select">
                                            <option value="usuario" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                                            <option value="editor" <?= $usuario['rol'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                            <option value="administrador" <?= $usuario['rol'] === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                                        </select>
                                    </div>
                                <?php endif; ?>

                                <div class="d-grid">
                                    <button type="submit" name="modificar" class="btn btn-primary">Guardar Cambios</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer text-center">
                        <a href="<?= ($_SESSION['rol'] === 'administrador') ? 'admin.php' : 'usuario.php' ?>"
                            class="btn btn-secondary">Volver</a>
                        <a href="cerrar_sesion.php" class="btn btn-danger">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
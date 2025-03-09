<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['dni'])) {
    header("Location: login.php");
    exit();
}

try {
    $conexion = conectar();

    // Obtener el DNI del usuario
    $dniUsuario = $_SESSION['dni'];

    // Obtener los datos del usuario
    $sql = "SELECT * FROM clientes WHERE dni = :dni";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':dni' => $dniUsuario]);
    $usuario = $stmt->fetch();

    // Si no existe el usuario
    if (!$usuario) {
        die("Usuario no encontrado.");
    }
} catch (PDOException $e) {
    die("Error al procesar la solicitud: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="container mt-4">
        <h3>Detalles de tu cuenta</h3>

        <!-- Tabla con los datos del usuario -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Localidad</th>
                    <th>Provincia</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($usuario['dni']) ?></td>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['direccion']) ?></td>
                    <td><?= htmlspecialchars($usuario['localidad']) ?></td>
                    <td><?= htmlspecialchars($usuario['provincia']) ?></td>
                    <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td>
                        <!-- Solo el usuario puede modificar su cuenta -->
                        <a href="modificar.php?dni=<?= htmlspecialchars($usuario['dni']) ?>" class="btn btn-warning btn-sm">✎ Modificar</a>
                        <!-- Solo el usuario puede eliminar su cuenta -->
                        <a href="eliminar.php?dni=<?= htmlspecialchars($usuario['dni']) ?>" class="btn btn-danger btn-sm">✘ Eliminar</a>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Enlace a la lista de artículos -->
        <a href="listar_articulos.php" class="btn btn-primary">Ver artículos</a><br><br>

        <!-- Botón para cerrar sesión -->
        <a href="cerrar_sesion.php" class="btn btn-secondary">Cerrar Sesión</a>
    </div>

    <!-- JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

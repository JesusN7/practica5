<?php
session_start();
require_once 'conexion.php';

$errores = [];  // Inicializar "errores"

// Solo los editores pueden acceder
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'editor') {
    $errores[] = "No tienes permisos para acceder a esta página.";
    header("Location: login.php");
    exit();
}

try {
    $conexion = conectar();

    // Obtener los datos del editor
    $dniEditor = $_SESSION['dni'];
    $stmtEditor = $conexion->prepare("SELECT * FROM clientes WHERE dni = :dni");
    $stmtEditor->execute([':dni' => $dniEditor]);
    $editor = $stmtEditor->fetch();

    // Si no se encuentra al editor, se maneja el error
    if (!$editor) {
        $errores[] = "Editor no encontrado.";
    }
} catch (PDOException $e) {
    $errores[] = "Error al obtener los datos del editor: " . $e->getMessage();
}

// Si hubo errores, redirigir y mostrar mensajes
if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Editor</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h1 class="text-center"><?= htmlspecialchars($editor['nombre']) ?> (Editor/a)</h1>

        <!-- Mostrar errores -->
        <?php
        if (isset($_SESSION['errores'])) {
            echo '<div class="alert alert-danger">';
            echo '<ul>';
            foreach ($_SESSION['errores'] as $error) {
                echo "<li>$error</li>";
            }
            echo '</ul>';
            echo '</div>';
            unset($_SESSION['errores']);
        }
        ?>

        <!-- Sección de datos personales -->
        <h2>Datos personales</h2>
        <table class="table table-bordered">
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
                    <td><?= htmlspecialchars($editor['dni']) ?></td>
                    <td><?= htmlspecialchars($editor['nombre']) ?></td>
                    <td><?= htmlspecialchars($editor['direccion']) ?></td>
                    <td><?= htmlspecialchars($editor['localidad']) ?></td>
                    <td><?= htmlspecialchars($editor['provincia']) ?></td>
                    <td><?= htmlspecialchars($editor['telefono']) ?></td>
                    <td><?= htmlspecialchars($editor['email']) ?></td>
                    <td>
                        <a href="modificar.php?dni=<?= htmlspecialchars($editor['dni']) ?>" class="btn btn-warning btn-sm">✎</a>
                        <a href="eliminar.php?dni=<?= htmlspecialchars($editor['dni']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este editor?');">✘</a>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Botón para ir a la lista de artículos -->
        <h2>Gestión de Artículos</h2>
        <a href="listar_articulos.php" class="btn btn-primary">Ver y Gestionar Artículos</a><br><br>

        <!-- Cerrar sesión -->
        <a href="cerrar_sesion.php" class="btn btn-secondary">Cerrar Sesión</a>
    </div>

    <!-- JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
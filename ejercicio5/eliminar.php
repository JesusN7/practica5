<?php
session_start();
require_once 'conexion.php';

$errores = [];

if (!isset($_SESSION['dni'])) {
    header("Location: index.php");
    exit();
}

$dni = $_GET['dni'] ?? null;  // Obtener el DNI del usuario a eliminar

if (!$dni) {
    $errores[] = "DNI no encontrado.";
}

try {
    $conexion = conectar();

    // Obtener los datos del usuario a eliminar
    $sql = "SELECT * FROM clientes WHERE dni = :dni";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':dni' => $dni]);
    $usuario = $stmt->fetch();

    // Si el usuario no existe
    if (!$usuario) {
        $errores[] = "El usuario no existe.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Eliminar el usuario
        $sql = "DELETE FROM clientes WHERE dni = :dni";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':dni' => $dni]);

        // Si el usuario elimina su propia cuenta, destruir la sesión y redirigir
        if ($dni === $_SESSION['dni']) {
            $_SESSION['mensaje_exito'] = "Tu cuenta ha sido eliminada con éxito.";
            header("Location: cerrar_sesion.php");
            exit();
        }

        // Redirigir a la página correspondiente dependiendo del rol
        if ($_SESSION['rol'] === 'administrador') {
            $_SESSION['mensaje_exito'] = "Usuario eliminado con éxito.";
            header("Location: admin.php");
        } elseif ($_SESSION['rol'] === 'editor') {
            $_SESSION['mensaje_exito'] = "Usuario eliminado con éxito.";
            header("Location: editor.php");
        } else {
            $_SESSION['mensaje_exito'] = "Usuario eliminado con éxito.";
            header("Location: usuario.php");
        }
        exit();
    }
} catch (PDOException $e) {
    $errores[] = "Error al eliminar el usuario: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Usuario</title>
</head>

<body>
    <h2>Confirmación de Eliminación</h2>

    <?php
    if (!empty($errores)) {
        echo '<div style="color: red;"><ul>';
        foreach ($errores as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul></div>';
    }
    ?>

    <?php if (isset($usuario)): ?>
        <p>¿Estás seguro de que deseas eliminar al usuario <strong><?= htmlspecialchars($usuario['nombre']) ?></strong> (DNI: <?= htmlspecialchars($usuario['dni']) ?>)?</p>
        <!-- Formulario de confirmación -->
        <form method="POST">
            <button type="submit" name="confirmar">Sí, eliminar usuario</button>
        </form><br>
        <!-- Opción para cancelar -->
        <a href="<?= $_SESSION['rol'] === 'administrador' ? 'admin.php' : ($_SESSION['rol'] === 'editor' ? 'editor.php' : 'usuario.php') ?>"><button>Cancelar y volver</button></a>
    <?php else: ?>
        <p>El usuario no existe.</p>
    <?php endif; ?>

</body>

</html>
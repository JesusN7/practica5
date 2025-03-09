<?php
session_start();
require_once 'conexion.php';

$errores = [];  // Inicializar "errores"

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['rol'])) {
    $errores[] = "No has iniciado sesión.";
    $_SESSION['errores'] = $errores;
    header("Location: login.php");
    exit();
}

// Verificar que el usuario tenga permisos para eliminar artículos (administrador o editor)
if ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'editor') {
    $errores[] = "No tienes permisos para eliminar artículos.";
    $_SESSION['errores'] = $errores;
    header("Location: listar_articulos.php");
    exit();
}

// Obtener el código del artículo a eliminar desde el formulario POST
$codigo = $_POST['codigo'] ?? null;

if (!$codigo) {
    $errores[] = "Código del artículo no encontrado.";
    $_SESSION['errores'] = $errores;
    header("Location: listar_articulos.php");
    exit();
}

try {
    $conexion = conectar();

    // Verificar si el artículo existe
    $sql = "SELECT * FROM articulos WHERE codigo = :codigo";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':codigo' => $codigo]);
    $articulo = $stmt->fetch();

    if (!$articulo) {
        $errores[] = "El artículo no existe.";
        $_SESSION['errores'] = $errores;
        header("Location: listar_articulos.php");
        exit();
    }

    // Eliminar el artículo de la base de datos
    $sql = "DELETE FROM articulos WHERE codigo = :codigo";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':codigo' => $codigo]);

    // Redirigir a la lista de artículos después de eliminar
    $_SESSION['exito'] = "Artículo eliminado con éxito.";
    header("Location: listar_articulos.php");
    exit();
} catch (PDOException $e) {
    $errores[] = "Error al eliminar el artículo: " . $e->getMessage();
    $_SESSION['errores'] = $errores;
    header("Location: listar_articulos.php");
    exit();
}

// Si hubo errores, guardarlos en la sesión
if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    header("Location: listar_articulos.php");
    exit();
}

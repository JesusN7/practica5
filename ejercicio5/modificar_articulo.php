<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

// Verificación del rol para permitir solo administradores y editores
if ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'editor') {
    header("Location: listar_articulos.php");
    exit();
}

try {
    $conexion = conectar();

    // Verificar si se ha proporcionado un código de artículo
    if (isset($_GET['codigo'])) {
        $codigo = $_GET['codigo'];

        $sql = "SELECT * FROM articulos WHERE codigo = :codigo";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->execute();
        $articulo = $stmt->fetch();

        // Verificar si el artículo existe
        if (!$articulo) {
            header("Location: listar_articulos.php");
            exit();
        }
    } else {
        header("Location: listar_articulos.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener los datos del formulario
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $categoria = trim($_POST['categoria']);
        $precio = trim($_POST['precio']);
        $imagen_nueva = $_FILES['imagen_nueva'];

        // Validaciones utilizando las funciones
        $errores = [];

        if (!validarDescripcion($descripcion)) {
            $errores[] = "La descripción no debe superar los 500 caracteres.";
        }

        if (!validarPrecio($precio)) {
            $errores[] = "El precio debe ser un número mayor que 0 y con hasta 2 decimales.";
        }

        if (!validarCodigo($codigo)) {
            $errores[] = "El código no es válido.";
        }

        // Comprobar si se ha subido una nueva imagen
        if (isset($imagen_nueva) && $imagen_nueva['error'] === UPLOAD_ERR_OK) {
            // Validar la nueva imagen
            if (!validarImagen($imagen_nueva['name'])) {
                $errores[] = "Solo se permiten imágenes de tipo JPG, JPEG o PNG.";
            }

            if ($imagen_nueva['size'] > 300000) {
                $errores[] = "La imagen no debe tener más de 300 KB.";
            }

            // Verificar dimensiones de la imagen
            list($width, $height) = getimagesize($imagen_nueva['tmp_name']);
            if ($width > 200 || $height > 200) {
                $errores[] = "La imagen no debe exceder 200x200 píxeles.";
            }
        }

        // Si hay errores, redirigir con los errores
        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            header("Location: modificar_articulo.php?codigo=" . $codigo);
            exit();
        }

        $archivoDestino = $articulo['imagen']; // Mantener la imagen que ya tenemos por defecto

        if (isset($imagen_nueva) && $imagen_nueva['error'] === UPLOAD_ERR_OK) {
            // Verificar si la imagen ya existe en la carpeta
            $directorioDestino = 'imagenes/';
            $nombreArchivo = basename($imagen_nueva['name']);
            $rutaCompleta = $directorioDestino . $nombreArchivo;

            if (file_exists($rutaCompleta)) {
                $archivoDestino = $rutaCompleta;
            } else {
                // Si la imagen no existe, subirla con un nombre único
                $nombreArchivo = uniqid() . "." . strtolower(pathinfo($imagen_nueva['name'], PATHINFO_EXTENSION));
                $archivoDestino = $directorioDestino . $nombreArchivo;

                if (!move_uploaded_file($imagen_nueva['tmp_name'], $archivoDestino)) {
                    $_SESSION['errores'][] = "Error al mover la nueva imagen.";
                    header("Location: modificar_articulo.php?codigo=" . $codigo);
                    exit();
                }
            }
        }

        // Actualizar el artículo
        $sql = "UPDATE articulos SET nombre = :nombre, descripcion = :descripcion, categoria = :categoria, precio = :precio, imagen = :imagen WHERE codigo = :codigo";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
        $stmt->bindParam(':precio', $precio, PDO::PARAM_STR);
        $stmt->bindParam(':imagen', $archivoDestino, PDO::PARAM_STR);
        $stmt->execute();

        $_SESSION['exito'] = "Artículo actualizado con éxito.";
        header("Location: listar_articulos.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error al modificar el artículo: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Artículo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Modificar Artículo</h4>
                    </div>
                    <div class="card-body">
                        <!-- Mostrar errores si existen -->
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

                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="codigo" value="<?= htmlspecialchars($articulo['codigo']) ?>">

                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" class="form-control"
                                    value="<?= htmlspecialchars($articulo['nombre']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción:</label>
                                <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required>
                                    <?= htmlspecialchars($articulo['descripcion']) ?>
                                </textarea>
                            </div>

                            <div class="mb-3">
                                <label for="categoria" class="form-label">Categoría:</label>
                                <input type="text" id="categoria" name="categoria" class="form-control"
                                    value="<?= htmlspecialchars($articulo['categoria']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio:</label>
                                <input type="number" id="precio" name="precio" class="form-control"
                                    step="0.01" value="<?= htmlspecialchars($articulo['precio']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Imagen actual:</label><br>
                                <img src="<?= htmlspecialchars($articulo['imagen']) ?>" alt="Imagen actual del artículo"
                                    class="img-thumbnail" width="100">
                            </div>

                            <div class="mb-3">
                                <label for="imagen_nueva" class="form-label">Cambiar Imagen:</label>
                                <input type="file" id="imagen_nueva" name="imagen_nueva" class="form-control">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Actualizar Artículo</button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer text-center">
                        <a href="listar_articulos.php" class="btn btn-secondary">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

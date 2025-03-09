<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['administrador', 'editor'])) {
    header("Location: login.php");
    exit();
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $categoria = trim($_POST['categoria']);
    $precio = trim($_POST['precio']);

    // Validaciones
    if (!validarCodigo($codigo)) {
        $errores[] = "El código debe tener tres letras seguidas de hasta cinco números.";
    }
    if (!validarPrecio($precio)) {
        $errores[] = "El precio debe ser un número mayor que 0 y con hasta 2 decimales.";
    }
    if (strlen($nombre) > 100 || empty($nombre)) {
        $errores[] = "El nombre no debe exceder los 100 caracteres.";
    }
    if (!validarDescripcion($descripcion)) {
        $errores[] = "La descripción no debe superar los 500 caracteres.";
    }
    if (strlen($categoria) > 50 || empty($categoria)) {
        $errores[] = "La categoría no debe superar los 50 caracteres.";
    }

    // Verificar si el código ya existe
    $conexion = conectar();
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM articulos WHERE codigo = :codigo");
    $stmt->execute([':codigo' => $codigo]);
    if ($stmt->fetchColumn() > 0) {
        $errores[] = "El código ya existe. Por favor, elige otro.";
    }

    // Validar imagen
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        $errores[] = "Debes subir una imagen para el artículo.";
    } else {
        $imagen = $_FILES['imagen'];

        // Verificar tamaño (300 KB máximo)
        if ($imagen['size'] > 300000) {
            $errores[] = "La imagen no debe superar los 300 KB.";
        }

        // Verificar dimensiones (200x200 px máximo)
        list($width, $height) = getimagesize($imagen['tmp_name']);
        if ($width > 200 || $height > 200) {
            $errores[] = "Las dimensiones máximas son 200x200 px.";
        }

        // Verificar formato (JPG, JPEG y PNG)
        if (!validarImagen($imagen['name'])) {
            $errores[] = "Solo se permiten imágenes en formato JPG, JPEG o PNG.";
        }

        // Si no hay errores, mover la imagen al directorio
        if (empty($errores)) {
            $directorioDestino = 'imagenes/';
            $nombreArchivo = basename($imagen['name']);
            $rutaCompleta = $directorioDestino . $nombreArchivo;

            // Verificar si la imagen ya existe
            if (file_exists($rutaCompleta)) {
                // Usar la imagen existente
                $archivoDestino = $rutaCompleta;
            } else {
                // Si la imagen no existe, subirla con un nombre único
                $nombreArchivo = uniqid() . "." . strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
                $archivoDestino = $directorioDestino . $nombreArchivo;

                if (!move_uploaded_file($imagen['tmp_name'], $archivoDestino)) {
                    $errores[] = "Error al mover la imagen.";
                }
            }
        }
    }

    // Si no hay errores, insertar el artículo
    if (empty($errores)) {
        try {
            $sql = "INSERT INTO articulos (codigo, nombre, descripcion, categoria, precio, imagen) 
                    VALUES (:codigo, :nombre, :descripcion, :categoria, :precio, :imagen)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':categoria' => $categoria,
                ':precio' => $precio,
                ':imagen' => $archivoDestino
            ]);
            $_SESSION['exito'] = "Artículo creado con éxito.";
            header("Location: listar_articulos.php");
            exit();
        } catch (PDOException $e) {
            $errores[] = "Error al crear el artículo: " . $e->getMessage();
        }
    }

    // Guardar errores
    $_SESSION['errores'] = $errores;
    header("Location: crear_articulo.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Artículo</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Crear Artículo</h2>

        <!-- Mostrar errores -->
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

        <!-- Formulario para crear artículo -->
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="codigo" class="form-label">Código:</label>
                <input type="text" id="codigo" name="codigo" class="form-control" pattern="[A-Za-z]{3}[0-9]{1,5}" maxlength="8" required>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="100">
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción:</label>
                <textarea id="descripcion" name="descripcion" class="form-control" required maxlength="500"></textarea>
            </div>

            <div class="mb-3">
                <label for="categoria" class="form-label">Categoría:</label>
                <input type="text" id="categoria" name="categoria" class="form-control" required maxlength="50">
            </div>

            <div class="mb-3">
                <label for="precio" class="form-label">Precio:</label>
                <input type="number" id="precio" name="precio" class="form-control" step="0.01" required min="0.01">
            </div>

            <div class="mb-3">
                <label for="imagen" class="form-label">Imagen:</label>
                <input type="file" id="imagen" name="imagen" class="form-control" accept=".jpg, .jpeg, .png" required>
            </div>

            <button type="submit" class="btn btn-primary">Crear Artículo</button>
        </form><br>

        <a href="listar_articulos.php">
            <button class="btn btn-secondary">Volver al listado de artículos</button>
        </a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

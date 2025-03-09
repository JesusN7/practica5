<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

try {
    $conexion = conectar();

    // Ordenamiento
    $orden = strtoupper($_GET['orden'] ?? 'ASC');
    if (!in_array($orden, ['ASC', 'DESC'])) {
        $orden = 'ASC';
    }

    // Número de artículos por página
    $PAGS = 2;

    // Página actual (por defecto es 1)
    $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($pagina < 1) $pagina = 1;

    // Desplazamiento
    $inicio = ($pagina - 1) * $PAGS;

    // Búsqueda por código
    $codigo = trim($_GET['buscar'] ?? '');

    // Consulta
    $sql = "SELECT * FROM articulos";
    $sqlCount = "SELECT COUNT(*) FROM articulos";
    $parametros = [];

    if (!empty($codigo)) {
        $sql .= " WHERE codigo LIKE :codigo";
        $sqlCount .= " WHERE codigo LIKE :codigo";
        $parametros[':codigo'] = "%$codigo%";
    }

    $sql .= " ORDER BY precio $orden LIMIT :inicio, :num_articulos";
    $stmt = $conexion->prepare($sql);

    foreach ($parametros as $clave => $valor) {
        $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
    }
    $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
    $stmt->bindValue(':num_articulos', $PAGS, PDO::PARAM_INT);
    $stmt->execute();
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contar total de artículos
    $totalStmt = $conexion->prepare($sqlCount);
    foreach ($parametros as $clave => $valor) {
        $totalStmt->bindValue($clave, $valor, PDO::PARAM_STR);
    }
    $totalStmt->execute();
    $totalArticulos = $totalStmt->fetchColumn();
    $totalPaginas = max(ceil($totalArticulos / $PAGS), 1);

    if ($pagina > $totalPaginas) {
        $pagina = $totalPaginas;
    }
} catch (PDOException $e) {
    die("Error al obtener artículos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artículos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">Artículos</h2>

        <!-- Botón para ir al panel de control según el rol -->
        <div class="mb-3">
            <?php if ($_SESSION['rol'] === 'usuario'): ?>
                <a href="usuario.php" class="btn btn-secondary">Volver al Panel de Usuario</a>
            <?php elseif ($_SESSION['rol'] === 'editor'): ?>
                <a href="editor.php" class="btn btn-secondary">Volver al Panel del Editor</a>
            <?php elseif ($_SESSION['rol'] === 'administrador'): ?>
                <a href="admin.php" class="btn btn-secondary">Volver al Panel de Administrador</a>
            <?php endif; ?>
        </div>

        <!-- Botón para crear un nuevo artículo (solo para editores y administradores) -->
        <?php if ($_SESSION['rol'] === 'editor' || $_SESSION['rol'] === 'administrador'): ?>
            <a href="crear_articulo.php" class="btn btn-success mb-3">Crear Nuevo Artículo</a>
        <?php endif; ?>

        <!-- Formulario de búsqueda -->
        <form method="GET" action="listar_articulos.php" class="mb-4">
            <div class="input-group">
                <input type="text" name="buscar" id="buscar" class="form-control" placeholder="Introduce el código" value="<?= htmlspecialchars($codigo) ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
        </form>

        <!-- Botones para ordenar -->
        <div class="mb-3">
            <a href="listar_articulos.php?orden=ASC<?= !empty($codigo) ? '&buscar=' . urlencode($codigo) : '' ?>" class="btn btn-link">Ordenar por precio (Menor a Mayor)</a> |
            <a href="listar_articulos.php?orden=DESC<?= !empty($codigo) ? '&buscar=' . urlencode($codigo) : '' ?>" class="btn btn-link">Ordenar por precio (Mayor a Menor)</a>
        </div>

        <!-- Tabla de Artículos -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Imagen</th>
                    <?php if ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'editor'): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articulos as $articulo): ?>
                    <tr>
                        <td><?= htmlspecialchars($articulo['codigo']) ?></td>
                        <td><?= htmlspecialchars($articulo['nombre']) ?></td>
                        <td><?= htmlspecialchars($articulo['descripcion']) ?></td>
                        <td><?= htmlspecialchars($articulo['categoria']) ?></td>
                        <td><?= htmlspecialchars($articulo['precio']) ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($articulo['imagen']) ?>" alt="Imagen del artículo" width="50">
                        </td>
                        <?php if ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'editor'): ?>
                            <td>
                                <a href="modificar_articulo.php?codigo=<?= htmlspecialchars($articulo['codigo']) ?>" class="btn btn-warning btn-sm">✎</a>
                                <form method="POST" action="eliminar_articulo.php" style="display:inline;">
                                    <input type="hidden" name="codigo" value="<?= htmlspecialchars($articulo['codigo']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este artículo?');">✘</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <div class="d-flex justify-content-between">
            <div>
                <p>Página <?= $pagina ?> de <?= $totalPaginas ?></p>
            </div>
            <div>
                <?php if ($pagina > 1): ?>
                    <a href="listar_articulos.php?pagina=1&orden=<?= $orden ?><?= !empty($codigo) ? '&buscar=' . urlencode($codigo) : '' ?>" class="btn btn-link">Primera página</a>
                    <a href="listar_articulos.php?pagina=<?= $pagina - 1 ?>&orden=<?= $orden ?><?= !empty($codigo) ? '&buscar=' . urlencode($codigo) : '' ?>" class="btn btn-link">Página anterior</a>
                <?php endif; ?>
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="listar_articulos.php?pagina=<?= $pagina + 1 ?>&orden=<?= $orden ?><?= !empty($codigo) ? '&buscar=' . urlencode($codigo) : '' ?>" class="btn btn-link">Página siguiente</a>
                    <a href="listar_articulos.php?pagina=<?= $totalPaginas ?>&orden=<?= $orden ?><?= !empty($codigo) ? '&buscar=' . urlencode($codigo) : '' ?>" class="btn btn-link">Última página</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Botón para cerrar sesión -->
        <div class="mt-4">
            <a href="cerrar_sesion.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
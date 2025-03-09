<?php
session_start();
require_once 'conexion.php';
require_once 'funciones.php';

// Comprobamos si tiene el rol de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit();
}

try {
    $conexion = conectar();

    // Número de usuarios por página
    $PAGS = 3;

    // Página actual (por defecto será 1)
    $pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? $_GET['pagina'] : 1;
    if ($pagina < 1) $pagina = 1;

    $inicio = ($pagina - 1) * $PAGS;

    // Orden, por defecto ASC
    $orden = isset($_GET['orden']) ? $_GET['orden'] : 'ASC';

    // Filtro por rol, si se pasa en la URL
    $rol = isset($_GET['rol']) ? $_GET['rol'] : '';

    // Si introducimos un DNI para buscar, filtramos los resultados. Si no, mostramos todos los usuarios.
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $dni = $_GET['buscar'];
        $sql = "SELECT * FROM clientes WHERE dni LIKE :dni";

        if (!empty($rol)) {
            $sql .= " AND rol = :rol";
        }

        $sql .= " ORDER BY nombre $orden LIMIT $inicio, $PAGS";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(':dni', "%$dni%");
        if (!empty($rol)) {
            $stmt->bindValue(':rol', $rol);
        }
        $stmt->execute();
    } else {
        $sql = "SELECT * FROM clientes";

        if (!empty($rol)) {
            $sql .= " WHERE rol = :rol";
        }

        $sql .= " ORDER BY nombre $orden LIMIT $inicio, $PAGS";
        $stmt = $conexion->prepare($sql);
        if (!empty($rol)) {
            $stmt->bindValue(':rol', $rol);
        }
        $stmt->execute();
    }

    // Obtener todos los usuarios para la página actual
    $usuarios = $stmt->fetchAll();

    // Obtener el total de registros para calcular el número total de páginas
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $sql_count = "SELECT COUNT(*) FROM clientes WHERE dni LIKE :dni";

        if (!empty($rol)) {
            $sql_count .= " AND rol = :rol";
        }

        $stmt_count = $conexion->prepare($sql_count);
        $stmt_count->bindValue(':dni', "%$dni%");
        if (!empty($rol)) {
            $stmt_count->bindValue(':rol', $rol);
        }
        $stmt_count->execute();
    } else {
        $sql_count = "SELECT COUNT(*) FROM clientes";

        if (!empty($rol)) {
            $sql_count .= " WHERE rol = :rol";
        }

        $stmt_count = $conexion->prepare($sql_count);
        if (!empty($rol)) {
            $stmt_count->bindValue(':rol', $rol);
        }
        $stmt_count->execute();
    }

    $total_registros = $stmt_count->fetchColumn();
    $total_paginas = ceil($total_registros / $PAGS);  // Calcular el total de páginas

} catch (PDOException $e) {
    die("Error al obtener usuarios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Administrador</h2>
        <h3>Gestión de Usuarios</h3>

        <!-- Formulario de búsqueda por DNI -->
        <form method="GET" action="admin.php" class="mb-4">
            <div class="input-group">
                <input type="text" name="buscar" id="buscar" class="form-control" placeholder="DNI del usuario" value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
        </form>

        <!-- Formulario para filtrar por rol -->
        <form method="GET" action="admin.php" id="formRol" class="mb-4">
            <div class="input-group">
                <label for="rol" class="input-group-text">Filtrar por rol:</label>
                <select name="rol" id="rol" class="form-select" onchange="document.getElementById('formRol').submit()">
                    <option value="">Todos los roles</option>
                    <option value="usuario" <?= isset($_GET['rol']) && $_GET['rol'] == 'usuario' ? 'selected' : '' ?>>Usuario</option>
                    <option value="editor" <?= isset($_GET['rol']) && $_GET['rol'] == 'editor' ? 'selected' : '' ?>>Editor</option>
                    <option value="administrador" <?= isset($_GET['rol']) && $_GET['rol'] == 'administrador' ? 'selected' : '' ?>>Administrador</option>
                </select>
                <?php if (isset($_GET['buscar'])): ?>
                    <input type="hidden" name="buscar" value="<?= htmlspecialchars($_GET['buscar']) ?>">
                <?php endif; ?>
            </div>
        </form>

        <!-- Botones para ordenar -->
        <p>
            <a href="admin.php?orden=ASC&rol=<?= htmlspecialchars($rol) ?>&buscar=<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" class="btn btn-link">Ordenar por nombre (A-Z)</a> |
            <a href="admin.php?orden=DESC&rol=<?= htmlspecialchars($rol) ?>&buscar=<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" class="btn btn-link">Ordenar por nombre (Z-A)</a>
        </p>

        <!-- Tabla de usuarios -->
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
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario['dni']) ?></td>
                        <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                        <td><?= htmlspecialchars($usuario['direccion']) ?></td>
                        <td><?= htmlspecialchars($usuario['localidad']) ?></td>
                        <td><?= htmlspecialchars($usuario['provincia']) ?></td>
                        <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td><?= htmlspecialchars($usuario['rol']) ?></td>
                        <td>
                            <!-- Modificar -->
                            <a href="modificar.php?dni=<?= htmlspecialchars($usuario['dni']) ?>" class="btn btn-warning btn-sm">✎</a>
                            <?php if ($usuario['dni'] !== $_SESSION['dni']): ?>
                                <!-- Eliminar -->
                                <a href="eliminar.php?dni=<?= htmlspecialchars($usuario['dni']) ?>" class="btn btn-danger btn-sm">✘</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <div>
            <p>Página <?= $pagina ?> de <?= $total_paginas ?></p>
            <div>
                <?php if ($pagina > 1): ?>
                    <a href="admin.php?pagina=1&orden=<?= $orden ?>&rol=<?= htmlspecialchars($rol) ?>&buscar=<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" class="btn btn-link">Primera página</a> |
                    <a href="admin.php?pagina=<?= $pagina - 1 ?>&orden=<?= $orden ?>&rol=<?= htmlspecialchars($rol) ?>&buscar=<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" class="btn btn-link">Página anterior</a> |
                <?php endif; ?>
                <?php if ($pagina < $total_paginas): ?>
                    <a href="admin.php?pagina=<?= $pagina + 1 ?>&orden=<?= $orden ?>&rol=<?= htmlspecialchars($rol) ?>&buscar=<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" class="btn btn-link">Página siguiente</a> |
                    <a href="admin.php?pagina=<?= $total_paginas ?>&orden=<?= $orden ?>&rol=<?= htmlspecialchars($rol) ?>&buscar=<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" class="btn btn-link">Última página</a>
                <?php endif; ?>
            </div>
        </div>

        <br><a href="crear_usuario.php" class="btn btn-primary">Nuevo Usuario</a><br><br>

        <!-- Enlace a la lista de artículos -->
        <a href="listar_articulos.php" class="btn btn-info">Ver artículos</a><br><br>

        <!-- Cerrar sesión -->
        <a href="cerrar_sesion.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
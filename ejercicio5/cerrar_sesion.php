<?php
session_start(); // Inicia la sesión si no está ya iniciada

// Eliminar todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir con un mensaje de éxito
$_SESSION['mensaje_exito'] = "Has cerrado sesión correctamente.";
header("Location: login.php");
exit();

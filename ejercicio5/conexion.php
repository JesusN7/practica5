<?php
function conectar() {
    static $conexion = null;

    if ($conexion === null) {
        try {
            $dsn = 'mysql:host=localhost;dbname=jesus_garcia_DB;charset=utf8mb4';
            $usuario = 'root';
            $contraseña = '';

            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            $conexion = new PDO($dsn, $usuario, $contraseña, $opciones);
        } catch (PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos."); 
        }
    }

    return $conexion; 
}

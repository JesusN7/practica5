<?php

// Validar DNI
function validarDni($id)
{
    $letras = "TRWAGMYFPDXBNJZSQVHLCKE";
    if (!preg_match("/^\d{8}[A-Za-z]$/", $id)) return false;
    return strtoupper($id[-1]) === $letras[(int)substr($id, 0, 8) % 23];
}

// Validar teléfono (9 dígitos)
function validarTelefono($telefono)
{
    return preg_match("/^[0-9]{9}$/", $telefono);
}

// Validar email
function validarEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validar que los campos no superen la base de datos
function validarCampos($nombre, $direccion, $localidad, $provincia, $email)
{
    return !(
        strlen($nombre) > 100 ||
        strlen($direccion) > 255 ||
        strlen($localidad) > 100 ||
        strlen($provincia) > 100 ||
        strlen($email) > 100
    );
}

// Validar longitud y complejidad de contraseña
function longPassword($password, $minLength = 8)
{
    return strlen($password) >= $minLength && preg_match('/[A-Z]/', $password) && preg_match('/[0-9]/', $password);
}

// Validar código de producto
function validarCodigo($codigo)
{
    return preg_match("/^[A-Za-z]{3}[0-9]{1,5}$/", $codigo);
}

// Validar descripción (máx. 500 caracteres)
function validarDescripcion($descripcion)
{
    return !empty($descripcion) && strlen($descripcion) <= 500;
}

// Validar precio (número positivo con 2 decimales máximo)
function validarPrecio($precio)
{
    return preg_match("/^\d+(\.\d{1,2})?$/", $precio) && $precio > 0;
}

// Validar imagen
function validarImagen($nombreArchivo)
{
    $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    $extPermitidas = ['jpg', 'jpeg', 'png'];
    return in_array($extension, $extPermitidas);
}

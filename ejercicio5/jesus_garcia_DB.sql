-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-03-2025 a las 01:49:42
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `jesus_garcia_db`
--
CREATE DATABASE IF NOT EXISTS `jesus_garcia_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `jesus_garcia_db`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulos`
--

CREATE TABLE `articulos` (
  `codigo` varchar(8) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `articulos`
--

INSERT INTO `articulos` (`codigo`, `nombre`, `descripcion`, `categoria`, `precio`, `imagen`) VALUES
('GHI781', 'Mass Effect 2', 'Juego Mass Effect', 'Videojuegos', 60.00, 'imagenes/MassEffect.jpg'),
('GXC700', 'Scream', 'Película Scream', 'Cine', 30.00, 'imagenes/scream5.jpg'),
('INK666', 'Ice Nine Kills', 'Disco Ice Nine Kills', 'Música', 35.00, 'imagenes/iceninekills.jpg'),
('MNG777', 'Berserk', 'Manga Berserk 42', 'Manga', 9.00, 'imagenes/berserk.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `dni` varchar(9) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `localidad` varchar(50) DEFAULT NULL,
  `provincia` varchar(50) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol` enum('usuario','editor','administrador') NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`dni`, `nombre`, `direccion`, `localidad`, `provincia`, `telefono`, `email`, `password`, `rol`) VALUES
('23456789D', 'Marta Oller', 'Calle Omega, 7', 'Barcelona', 'Barcelona', '640552000', 'marta@gmail.com', '$2y$10$ijxVuILA7KEZSc1mob0K9u6HIHG6jOsyGP2lXKyaMxVFNSMDROGY6', 'usuario'),
('74366466Z', 'Jesús García', 'Clara Campoamor', 'Elche', 'Alicante', '640552101', 'jesus@gmail.com', '$2y$10$znuCgFOUW3yBqu4znLJUj.KLYA5nDhgx9WUGKYAX2ViUEzAi/gvE6', 'administrador'),
('87654321X', 'John Shepard', 'Calle Andromeda, 10', 'Elche', 'Alicante', '640552444', 'john7@gmail.com', '$2y$10$ky9ebtCo2YvsweYnvHAFieUE3zIVEfnjV5J.uwtbKy.QhKi03ehIu', 'editor');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD PRIMARY KEY (`codigo`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`dni`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

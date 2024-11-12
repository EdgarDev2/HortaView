-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 12-11-2024 a las 17:49:36
-- Versión del servidor: 8.3.0
-- Versión de PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistemariego`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cama1`
--

DROP TABLE IF EXISTS `cama1`;
CREATE TABLE IF NOT EXISTS `cama1` (
  `idCama1` int NOT NULL AUTO_INCREMENT,
  `humedad` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`idCama1`)
) ENGINE=MyISAM AUTO_INCREMENT=54808 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `cama2`;
CREATE TABLE IF NOT EXISTS `cama2` (
  `idCama2` int NOT NULL AUTO_INCREMENT,
  `humedad` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`idCama2`)
) ENGINE=MyISAM AUTO_INCREMENT=54808 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cama3`
--

DROP TABLE IF EXISTS `cama3`;
CREATE TABLE IF NOT EXISTS `cama3` (
  `idCama3` int NOT NULL AUTO_INCREMENT,
  `humedad` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`idCama3`)
) ENGINE=MyISAM AUTO_INCREMENT=53586 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cama4`
--

DROP TABLE IF EXISTS `cama4`;
CREATE TABLE IF NOT EXISTS `cama4` (
  `idCama4` int NOT NULL AUTO_INCREMENT,
  `humedad` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`idCama4`)
) ENGINE=MyISAM AUTO_INCREMENT=53587 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cultivo`
--

DROP TABLE IF EXISTS `cultivo`;
CREATE TABLE IF NOT EXISTS `cultivo` (
  `cultivoId` varchar(50) NOT NULL,
  `nombre_cultivo` varchar(100) DEFAULT NULL,
  `germinacion` int DEFAULT NULL,
  `fecha_siembra` datetime DEFAULT NULL,
  `fecha_cosecha` datetime DEFAULT NULL,
  `tipo_riego` varchar(50) DEFAULT NULL,
  `gramaje` varchar(50) DEFAULT NULL,
  `altura_maxima` double DEFAULT NULL,
  `altura_minima` double DEFAULT NULL,
  `temperatura_ambiente_maxima` int DEFAULT NULL,
  `temperatura_ambiente_minima` int DEFAULT NULL,
  `humedad_ambiente_maxima` int DEFAULT NULL,
  `humedad_ambiente_minima` int DEFAULT NULL,
  `humedad_minima_tierra` int DEFAULT NULL,
  `presion_barometrica_maxima` int DEFAULT NULL,
  `presion_barometrica_minima` int DEFAULT NULL,
  PRIMARY KEY (`cultivoId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cultivo`
--

INSERT INTO `cultivo` (`cultivoId`, `nombre_cultivo`, `germinacion`, `fecha_siembra`, `fecha_cosecha`, `tipo_riego`, `gramaje`, `altura_maxima`, `altura_minima`, `temperatura_ambiente_maxima`, `temperatura_ambiente_minima`, `humedad_ambiente_maxima`, `humedad_ambiente_minima`, `humedad_minima_tierra`, `presion_barometrica_maxima`, `presion_barometrica_minima`) VALUES
('1', 'Cama automatizado 1', NULL, NULL, NULL, 'Por goteo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('2', 'Cama automatizado 2', NULL, NULL, NULL, 'Riego con aspersores', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('3', 'Cama tradicional 1', NULL, NULL, NULL, 'Riego manual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('4', 'Cama tradicional 2', NULL, NULL, NULL, 'Riego manual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

DROP TABLE IF EXISTS `estado`;
CREATE TABLE IF NOT EXISTS `estado` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `estado_nombre` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `estado_valor` smallint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id`, `estado_nombre`, `estado_valor`) VALUES
(1, 'Activo', 10),
(2, 'Pendiente', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero`
--

DROP TABLE IF EXISTS `genero`;
CREATE TABLE IF NOT EXISTS `genero` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `genero_nombre` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Volcado de datos para la tabla `genero`
--

INSERT INTO `genero` (`id`, `genero_nombre`) VALUES
(1, 'masculino'),
(2, 'femenino');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migration`
--

DROP TABLE IF EXISTS `migration`;
CREATE TABLE IF NOT EXISTS `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `migration`
--

INSERT INTO `migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1693618984),
('m130524_201442_init', 1693619006),
('m190124_110200_add_verification_token_column_to_user_table', 1693619007);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil`
--

DROP TABLE IF EXISTS `perfil`;
CREATE TABLE IF NOT EXISTS `perfil` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `nombre` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `apellido` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `fecha_nacimiento` datetime NOT NULL,
  `genero_id` smallint NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `genero_id_2` (`genero_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Volcado de datos para la tabla `perfil`
--

INSERT INTO `perfil` (`id`, `user_id`, `nombre`, `apellido`, `fecha_nacimiento`, `genero_id`, `created_at`, `updated_at`) VALUES
(1, 13, 'Edgar Manuel', 'Poot', '2024-07-12 00:00:00', 1, '2024-11-12 11:33:50', '2024-11-12 11:33:50'),
(2, 12, 'Limber Otoniel', 'May Ek', '2024-04-18 00:00:00', 2, '2024-11-12 11:39:02', '2024-11-12 11:39:02'),
(3, 14, 'Bernave', 'Rodríguez Morales', '2024-07-24 00:00:00', 1, '2024-11-12 11:40:48', '2024-11-12 11:40:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presionbarometrica`
--

DROP TABLE IF EXISTS `presionbarometrica`;
CREATE TABLE IF NOT EXISTS `presionbarometrica` (
  `idPresionBarometrica` int NOT NULL AUTO_INCREMENT,
  `presion` decimal(10,3) DEFAULT NULL,
  `temperatura` decimal(10,3) DEFAULT NULL,
  `altitud` decimal(10,3) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  PRIMARY KEY (`idPresionBarometrica`)
) ENGINE=MyISAM AUTO_INCREMENT=54907 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro1`
--

DROP TABLE IF EXISTS `registro1`;
CREATE TABLE IF NOT EXISTS `registro1` (
  `idRegistro` varchar(50) NOT NULL,
  `nombre_semillas` varchar(100) DEFAULT NULL,
  `semillas_plantadas` varchar(50) DEFAULT NULL,
  `semillas_germinadas` varchar(50) DEFAULT NULL,
  `altura_maxima` varchar(50) DEFAULT NULL,
  `altura_minima` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idRegistro`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro2`
--

DROP TABLE IF EXISTS `registro2`;
CREATE TABLE IF NOT EXISTS `registro2` (
  `idRegistro2` varchar(50) NOT NULL,
  `nombre_semillas` varchar(100) DEFAULT NULL,
  `semillas_plantadas` varchar(50) DEFAULT NULL,
  `semillas_germinadas` varchar(50) DEFAULT NULL,
  `altura_maxima` varchar(50) DEFAULT NULL,
  `altura_minima` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idRegistro2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro3`
--

DROP TABLE IF EXISTS `registro3`;
CREATE TABLE IF NOT EXISTS `registro3` (
  `idRegistro3` varchar(50) NOT NULL,
  `nombre_semillas` varchar(100) DEFAULT NULL,
  `semillas_plantadas` varchar(50) DEFAULT NULL,
  `semillas_germinadas` varchar(50) DEFAULT NULL,
  `altura_maxima` varchar(50) DEFAULT NULL,
  `altura_minima` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idRegistro3`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro4`
--

DROP TABLE IF EXISTS `registro4`;
CREATE TABLE IF NOT EXISTS `registro4` (
  `idRegistro4` varchar(50) NOT NULL,
  `nombre_semillas` varchar(100) DEFAULT NULL,
  `semillas_plantadas` varchar(50) DEFAULT NULL,
  `semillas_germinadas` varchar(50) DEFAULT NULL,
  `altura_maxima` varchar(50) DEFAULT NULL,
  `altura_minima` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idRegistro4`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `riegomanual`
--

DROP TABLE IF EXISTS `riegomanual`;
CREATE TABLE IF NOT EXISTS `riegomanual` (
  `idRiegoManual` int NOT NULL AUTO_INCREMENT,
  `fechaEncendido` datetime DEFAULT NULL,
  `fechaApagado` datetime DEFAULT NULL,
  `volumen` decimal(10,3) DEFAULT NULL,
  `cultivoId` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idRiegoManual`),
  KEY `cultivoId` (`cultivoId`)
) ENGINE=MyISAM AUTO_INCREMENT=6693 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

DROP TABLE IF EXISTS `rol`;
CREATE TABLE IF NOT EXISTS `rol` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `rol_nombre` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `rol_valor` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `rol_nombre`, `rol_valor`) VALUES
(1, 'Usuario', 10),
(2, 'Admin', 20),
(7, 'SuperUsuario', 30),
(8, 'Cliente', 11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temperatura`
--

DROP TABLE IF EXISTS `temperatura`;
CREATE TABLE IF NOT EXISTS `temperatura` (
  `idTemperatura` int NOT NULL AUTO_INCREMENT,
  `temperatura` decimal(10,3) NOT NULL,
  `humedad` decimal(10,3) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`idTemperatura`)
) ENGINE=MyISAM AUTO_INCREMENT=55274 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_usuario`
--

DROP TABLE IF EXISTS `tipo_usuario`;
CREATE TABLE IF NOT EXISTS `tipo_usuario` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `tipo_usuario_nombre` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `tipo_usuario_valor` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_usuario`
--

INSERT INTO `tipo_usuario` (`id`, `tipo_usuario_nombre`, `tipo_usuario_valor`) VALUES
(1, 'Gratuito', 10),
(2, 'Pago', 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `auth_key` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `rol_id` smallint NOT NULL DEFAULT '1',
  `estado_id` smallint NOT NULL DEFAULT '1',
  `tipo_usuario_id` smallint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `verification_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `password_reset_token` (`password_reset_token`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`id`, `username`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `rol_id`, `estado_id`, `tipo_usuario_id`, `created_at`, `updated_at`, `verification_token`) VALUES
(11, 'Usuario', 'zNjfYLLbJjSBf7L5gaM5Y7OXgQIQyuwB', '$2y$13$VsSfwAA42VHk4ivN3.qNK.tX2iCV1Myk8e/IhHDmb8NVCIGi5PK8y', NULL, 'Usuario10@gmail.com', 1, 1, 1, '2024-11-12 11:18:44', '2024-11-12 11:29:41', '6agGgUCJFRJ6JzEp1H2ihLEVWvzztvbV_1731431924'),
(12, 'Admin', 'GlPBqDvJUl16KhStpMFohtqz5vrgp7JM', '$2y$13$kn4XTb0uwNiM8KP2cV5nGuIuPj/tlrbppcv4K3Q.63ZxjGOh9d5mq', NULL, 'admin20@gmail.com', 2, 1, 1, '2024-11-12 11:22:54', '2024-11-12 11:30:21', '-wAwKX1gFeWSUNwU2rWs6Al_XikBRV8w_1731432174'),
(13, 'Superusuario', 'oHEQ2bZpHAtXnqGdqwu06cclkHtjwbn0', '$2y$13$KtnPGjDENPg7qX1NaxFxtu/k39wBI9NqXurLTKdmutLJTUy8WBqnC', NULL, 'superusuario30@gmail.com', 7, 1, 1, '2024-11-12 11:24:52', '2024-11-12 11:24:52', 'ak9WihYShbb_TBBedoAreyCXzGxQQpzZ_1731432292'),
(14, 'Cliente', 'kji6i1MwPOh6ba2e9AQxELtO2qrSCLbh', '$2y$13$xAMhF02bj2s1RNsA4OtE4.aw/NskEh0YxvFfvtx2A1Bl.rCBS7clS', NULL, 'cliente11@gmail.com', 8, 1, 1, '2024-11-12 11:25:39', '2024-11-12 11:30:52', 'zR00KyGWsZdkcS0bhVQH2IqhgP811_11_1731432339');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `valvula`
--

DROP TABLE IF EXISTS `valvula`;
CREATE TABLE IF NOT EXISTS `valvula` (
  `idValvula` int NOT NULL AUTO_INCREMENT,
  `fechaEncendido` datetime DEFAULT NULL,
  `fechaApagado` datetime DEFAULT NULL,
  `volumen` decimal(10,3) DEFAULT NULL,
  `cultivoId` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idValvula`),
  KEY `cultivoId` (`cultivoId`)
) ENGINE=MyISAM AUTO_INCREMENT=614 DEFAULT CHARSET=latin1;

--
-- Filtros para la tabla `perfil`
--
ALTER TABLE `perfil`
  ADD CONSTRAINT `perfil_ibfk_1` FOREIGN KEY (`genero_id`) REFERENCES `genero` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

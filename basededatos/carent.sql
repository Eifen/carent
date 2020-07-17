-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 17-07-2020 a las 20:42:44
-- Versión del servidor: 8.0.18
-- Versión de PHP: 7.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `carent`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_auditoria`
--

CREATE TABLE `logs_auditoria` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `direccion_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accion` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tabla` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `logs_auditoria`
--

INSERT INTO `logs_auditoria` (`id`, `usuario_id`, `fecha`, `direccion_ip`, `accion`, `tabla`) VALUES
(1, 1, '2020-07-15 20:40:00', '127.0.0.1', 'Inicio de Sesion', 'inicio'),
(2, 1, '2020-07-16 07:40:42', '127.0.0.1', 'Inicio de Sesion', 'inicio'),
(3, 1, '2020-07-16 07:41:33', '127.0.0.1', 'Registro de Usuario Codigo:0002', 'tbl_usuario'),
(4, 1, '2020-07-16 17:33:07', '127.0.0.1', 'Inicio de Sesion', 'inicio'),
(5, 1, '2020-07-16 17:34:02', '127.0.0.1', 'Registro de Usuario Codigo:222222', 'tbl_usuario'),
(6, 1, '2020-07-16 22:12:08', '127.0.0.1', 'Inicio de Sesion', 'inicio'),
(7, 1, '2020-07-16 22:56:31', '127.0.0.1', 'Inicio de Sesion', 'inicio'),
(8, 1, '2020-07-16 23:44:59', '127.0.0.1', 'Registro de Usuario Codigo:0002', 'tbl_usuario'),
(9, 1, '2020-07-16 23:46:12', '127.0.0.1', 'Registro de Usuario Codigo:0003', 'tbl_usuario'),
(10, 1, '2020-07-16 23:57:06', '127.0.0.1', 'Registro de Usuario Codigo:22222', 'tbl_usuario'),
(11, 1, '2020-07-17 08:31:47', '127.0.0.1', 'Inicio de Sesion', 'inicio'),
(12, 3, '2020-07-17 09:09:31', '127.0.0.1', 'Inicio de Sesion', 'inicio'),
(13, 1, '2020-07-17 09:10:29', '127.0.0.1', 'Inicio de Sesion', 'inicio'),
(14, 1, '2020-07-17 13:10:35', '127.0.0.1', 'Inicio de Sesion', 'inicio'),
(15, 1, '2020-07-17 13:18:36', '127.0.0.1', 'Registro de Usuario Codigo:0002', 'tbl_usuario'),
(16, 1, '2020-07-17 15:36:05', '127.0.0.1', 'Modificacion del Usuario Codigo:0002', 'tbl_usuario');

--
-- Disparadores `logs_auditoria`
--
DELIMITER $$
CREATE TRIGGER `logs_auditoria_AI` AFTER INSERT ON `logs_auditoria` FOR EACH ROW BEGIN
SET @usu = (SELECT id FROM logs.tbl_usuario ORDER BY id DESC LIMIT 1);
SET @clie = (SELECT id FROM logs.tbl_cliente ORDER BY id DESC LIMIT 1);
SET @fact = (SELECT id FROM logs.tbl_cliente_facturacion ORDER BY id DESC LIMIT 1);
SET @proy = (SELECT id FROM logs.tbl_proyecto ORDER BY id DESC LIMIT 1);
SET @proya = (SELECT id FROM logs.tbl_proyecto_analista ORDER BY id DESC LIMIT 1);
SET @horasc = (SELECT id FROM logs.tbl_horas_cargables ORDER BY id DESC LIMIT 1);
SET @concepto = (SELECT id FROM logs.tbl_concepto_horas_no_cargables ORDER BY id DESC LIMIT 1);
  IF NEW.accion LIKE '%Registro de Usuario Codigo%' THEN BEGIN
  	UPDATE logs.tbl_usuario SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @usu;
    END; END IF;
   IF NEW.accion LIKE '%Modificacion del Usuario Codigo%' THEN BEGIN
  	UPDATE logs.tbl_usuario SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @usu;
    END; END IF;
    IF NEW.accion LIKE '%Registro del cliente codigo%' THEN BEGIN
  	UPDATE logs.tbl_cliente SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @clie;
    END; END IF;
    IF NEW.accion LIKE '%Modificacion del cliente%' THEN BEGIN
  	UPDATE logs.tbl_cliente SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @clie;
    END; END IF;
    IF NEW.accion LIKE '%Registro del proyecto%' THEN BEGIN
  	UPDATE logs.tbl_proyecto SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @proy;
    END; END IF;
    IF NEW.accion LIKE '%Modificacion del proyecto%' THEN BEGIN
  	UPDATE logs.tbl_proyecto SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @proy;
    END; END IF;
    IF NEW.accion LIKE '%Asignacion del analista codigo%' THEN BEGIN
  	UPDATE logs.tbl_proyecto_analista SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @proya;
    END; END IF;
    IF NEW.accion LIKE '%Eliminacion del analista codigo%' THEN BEGIN
  	UPDATE logs.tbl_proyecto_analista SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @proya;
    END; END IF;
    IF NEW.accion LIKE '%total de horas asignadas%' THEN BEGIN
  	UPDATE logs.tbl_proyecto_analista SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @proya;
    END; END IF;
    IF NEW.accion LIKE '%Analista codigo%' THEN BEGIN
  	UPDATE logs.tbl_horas_cargables SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @horasc;
    END; END IF;
    IF NEW.accion LIKE '%Modificacion de horas del usuario codigo%' THEN BEGIN
  	UPDATE logs.tbl_horas_cargables SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @horasc;
    END; END IF;
    IF NEW.accion LIKE '%Registro del concepto de horas no cargables%' THEN BEGIN
  	UPDATE logs.tbl_concepto_horas_no_cargables SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @concepto;
    END; END IF;
    IF NEW.accion LIKE '%Registro del detalle de facturacion del cliente%' THEN BEGIN
  	UPDATE logs.tbl_cliente_facturacion SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @fact;
    END; END IF;
    IF NEW.accion LIKE '%Modificacion del detalle de facturacion del cliente%' THEN BEGIN
  	UPDATE logs.tbl_cliente_facturacion SET usuario_id = NEW.usuario_id, fecha = NEW.fecha, direccion_ip = NEW.direccion_ip WHERE id = @fact;
    END; END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_cargo_empleado`
--

CREATE TABLE `tbl_cargo_empleado` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `id_tipo_cargo` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_cargo_empleado`
--

INSERT INTO `tbl_cargo_empleado` (`id`, `descripcion`, `id_tipo_cargo`, `orden`, `id_estatus`) VALUES
(1, 'Contratado por horas', 1, 0, 1),
(2, 'Pasantes', 1, 0, 1),
(3, 'Asistente I', 1, 0, 1),
(4, 'Asistente II', 1, 0, 1),
(5, 'Asistente III', 1, 0, 1),
(6, 'Semi-Senior I', 1, 0, 1),
(7, 'Semi-Senior II', 1, 0, 1),
(8, 'Semi-Senior III', 1, 0, 1),
(9, 'Senior I', 1, 0, 1),
(10, 'Senior II', 1, 0, 1),
(11, 'Senior III', 1, 0, 1),
(12, 'Supervisor', 3, 0, 1),
(13, 'Gerente', 3, 0, 1),
(14, 'Gerente Senior', 3, 0, 1),
(15, 'Director', 3, 0, 1),
(16, 'Socio', 3, 0, 1),
(17, 'Acting Partner', 1, 0, 1),
(18, 'Asesor Legal', 1, 0, 1),
(19, 'Asistente de Socios', 2, 0, 1),
(20, 'Asistente de Gerentes', 2, 0, 1),
(21, 'Analista', 2, 0, 1),
(22, 'Chofer', 2, 0, 1),
(23, 'Supervisor de Mantenimiento', 2, 0, 1),
(24, 'Operaria de Mantenimiento', 2, 0, 1),
(25, 'Recepcionista', 2, 0, 1),
(26, 'Editora', 2, 0, 1),
(27, 'Analista Senior I', 2, 0, 1),
(28, 'Analista Senior II', 2, 0, 1),
(29, 'Analista Senior III', 2, 0, 1),
(30, 'Editora', 2, 0, 2),
(31, 'Asistente', 2, 0, 1),
(32, 'Asistente de Facturación y Cobranza', 2, 0, 1),
(33, 'Asistente Administrativo', 2, 0, 1),
(34, 'Soporte Técnico I', 2, 0, 1),
(35, 'Soporte Técnico II', 2, 0, 1),
(36, 'Soporte Técnico III', 2, 0, 1),
(37, 'Mensajero', 2, 0, 1),
(38, 'Recepcionista', 2, 0, 2),
(39, 'Pasante', 2, 0, 1),
(40, 'Trabajador Social', 2, 0, 1),
(41, 'Pasante Inces', 2, 0, 1),
(42, 'Asistente de Proyecto', 2, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_cargo_supervisa`
--

CREATE TABLE `tbl_cargo_supervisa` (
  `id` int(11) NOT NULL,
  `id_cargo` int(11) NOT NULL,
  `id_cargo_supervisor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbl_cargo_supervisa`
--

INSERT INTO `tbl_cargo_supervisa` (`id`, `id_cargo`, `id_cargo_supervisor`) VALUES
(1, 1, 15),
(2, 2, 15),
(3, 3, 15),
(4, 4, 15),
(5, 5, 15),
(6, 6, 15),
(7, 7, 15),
(8, 8, 15),
(9, 9, 15),
(10, 10, 15),
(11, 11, 15),
(12, 12, 15),
(13, 13, 15),
(14, 14, 15),
(15, 16, 15),
(16, 17, 15),
(17, 18, 15),
(18, 19, 15),
(19, 20, 15),
(20, 21, 15),
(21, 22, 15),
(22, 23, 15),
(23, 24, 15),
(24, 25, 15),
(25, 26, 15),
(26, 27, 15),
(27, 28, 15),
(28, 29, 15),
(29, 30, 15),
(30, 31, 15),
(31, 32, 15),
(32, 33, 15),
(33, 34, 15),
(34, 35, 15),
(35, 36, 15),
(36, 37, 15),
(37, 38, 15),
(38, 39, 15),
(39, 40, 15),
(40, 41, 15),
(41, 42, 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_ciudades`
--

CREATE TABLE `tbl_ciudades` (
  `id_ciudad` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL,
  `ciudad` varchar(200) NOT NULL,
  `capital` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_ciudades`
--

INSERT INTO `tbl_ciudades` (`id_ciudad`, `id_estado`, `ciudad`, `capital`) VALUES
(1, 1, 'Maroa', 0),
(2, 1, 'Puerto Ayacucho', 1),
(3, 1, 'San Fernando de Atabapo', 0),
(4, 2, 'Anaco', 0),
(5, 2, 'Aragua de Barcelona', 0),
(6, 2, 'Barcelona', 1),
(7, 2, 'Boca de Uchire', 0),
(8, 2, 'Cantaura', 0),
(9, 2, 'Clarines', 0),
(10, 2, 'El Chaparro', 0),
(11, 2, 'El Pao Anzoátegui', 0),
(12, 2, 'El Tigre', 0),
(13, 2, 'El Tigrito', 0),
(14, 2, 'Guanape', 0),
(15, 2, 'Guanta', 0),
(16, 2, 'Lechería', 0),
(17, 2, 'Onoto', 0),
(18, 2, 'Pariaguán', 0),
(19, 2, 'Píritu', 0),
(20, 2, 'Puerto La Cruz', 0),
(21, 2, 'Puerto Píritu', 0),
(22, 2, 'Sabana de Uchire', 0),
(23, 2, 'San Mateo Anzoátegui', 0),
(24, 2, 'San Pablo Anzoátegui', 0),
(25, 2, 'San Tomé', 0),
(26, 2, 'Santa Ana de Anzoátegui', 0),
(27, 2, 'Santa Fe Anzoátegui', 0),
(28, 2, 'Santa Rosa', 0),
(29, 2, 'Soledad', 0),
(30, 2, 'Urica', 0),
(31, 2, 'Valle de Guanape', 0),
(43, 3, 'Achaguas', 0),
(44, 3, 'Biruaca', 0),
(45, 3, 'Bruzual', 0),
(46, 3, 'El Amparo', 0),
(47, 3, 'El Nula', 0),
(48, 3, 'Elorza', 0),
(49, 3, 'Guasdualito', 0),
(50, 3, 'Mantecal', 0),
(51, 3, 'Puerto Páez', 0),
(52, 3, 'San Fernando de Apure', 1),
(53, 3, 'San Juan de Payara', 0),
(54, 4, 'Barbacoas', 0),
(55, 4, 'Cagua', 0),
(56, 4, 'Camatagua', 0),
(58, 4, 'Choroní', 0),
(59, 4, 'Colonia Tovar', 0),
(60, 4, 'El Consejo', 0),
(61, 4, 'La Victoria', 0),
(62, 4, 'Las Tejerías', 0),
(63, 4, 'Magdaleno', 0),
(64, 4, 'Maracay', 1),
(65, 4, 'Ocumare de La Costa', 0),
(66, 4, 'Palo Negro', 0),
(67, 4, 'San Casimiro', 0),
(68, 4, 'San Mateo', 0),
(69, 4, 'San Sebastián', 0),
(70, 4, 'Santa Cruz de Aragua', 0),
(71, 4, 'Tocorón', 0),
(72, 4, 'Turmero', 0),
(73, 4, 'Villa de Cura', 0),
(74, 4, 'Zuata', 0),
(75, 5, 'Barinas', 1),
(76, 5, 'Barinitas', 0),
(77, 5, 'Barrancas', 0),
(78, 5, 'Calderas', 0),
(79, 5, 'Capitanejo', 0),
(80, 5, 'Ciudad Bolivia', 0),
(81, 5, 'El Cantón', 0),
(82, 5, 'Las Veguitas', 0),
(83, 5, 'Libertad de Barinas', 0),
(84, 5, 'Sabaneta', 0),
(85, 5, 'Santa Bárbara de Barinas', 0),
(86, 5, 'Socopó', 0),
(87, 6, 'Caicara del Orinoco', 0),
(88, 6, 'Canaima', 0),
(89, 6, 'Ciudad Bolívar', 1),
(90, 6, 'Ciudad Piar', 0),
(91, 6, 'El Callao', 0),
(92, 6, 'El Dorado', 0),
(93, 6, 'El Manteco', 0),
(94, 6, 'El Palmar', 0),
(95, 6, 'El Pao', 0),
(96, 6, 'Guasipati', 0),
(97, 6, 'Guri', 0),
(98, 6, 'La Paragua', 0),
(99, 6, 'Matanzas', 0),
(100, 6, 'Puerto Ordaz', 0),
(101, 6, 'San Félix', 0),
(102, 6, 'Santa Elena de Uairén', 0),
(103, 6, 'Tumeremo', 0),
(104, 6, 'Unare', 0),
(105, 6, 'Upata', 0),
(106, 7, 'Bejuma', 0),
(107, 7, 'Belén', 0),
(108, 7, 'Campo de Carabobo', 0),
(109, 7, 'Canoabo', 0),
(110, 7, 'Central Tacarigua', 0),
(111, 7, 'Chirgua', 0),
(112, 7, 'Ciudad Alianza', 0),
(113, 7, 'El Palito', 0),
(114, 7, 'Guacara', 0),
(115, 7, 'Guigue', 0),
(116, 7, 'Las Trincheras', 0),
(117, 7, 'Los Guayos', 0),
(118, 7, 'Mariara', 0),
(119, 7, 'Miranda', 0),
(120, 7, 'Montalbán', 0),
(121, 7, 'Morón', 0),
(122, 7, 'Naguanagua', 0),
(123, 7, 'Puerto Cabello', 0),
(124, 7, 'San Joaquín', 0),
(125, 7, 'Tocuyito', 0),
(126, 7, 'Urama', 0),
(127, 7, 'Valencia', 1),
(128, 7, 'Vigirimita', 0),
(129, 8, 'Aguirre', 0),
(130, 8, 'Apartaderos Cojedes', 0),
(131, 8, 'Arismendi', 0),
(132, 8, 'Camuriquito', 0),
(133, 8, 'El Baúl', 0),
(134, 8, 'El Limón', 0),
(135, 8, 'El Pao Cojedes', 0),
(136, 8, 'El Socorro', 0),
(137, 8, 'La Aguadita', 0),
(138, 8, 'Las Vegas', 0),
(139, 8, 'Libertad de Cojedes', 0),
(140, 8, 'Mapuey', 0),
(141, 8, 'Piñedo', 0),
(142, 8, 'Samancito', 0),
(143, 8, 'San Carlos', 1),
(144, 8, 'Sucre', 0),
(145, 8, 'Tinaco', 0),
(146, 8, 'Tinaquillo', 0),
(147, 8, 'Vallecito', 0),
(148, 9, 'Tucupita', 1),
(149, 24, 'Caracas', 1),
(150, 24, 'El Junquito', 0),
(151, 10, 'Adícora', 0),
(152, 10, 'Boca de Aroa', 0),
(153, 10, 'Cabure', 0),
(154, 10, 'Capadare', 0),
(155, 10, 'Capatárida', 0),
(156, 10, 'Chichiriviche', 0),
(157, 10, 'Churuguara', 0),
(158, 10, 'Coro', 1),
(159, 10, 'Cumarebo', 0),
(160, 10, 'Dabajuro', 0),
(161, 10, 'Judibana', 0),
(162, 10, 'La Cruz de Taratara', 0),
(163, 10, 'La Vela de Coro', 0),
(164, 10, 'Los Taques', 0),
(165, 10, 'Maparari', 0),
(166, 10, 'Mene de Mauroa', 0),
(167, 10, 'Mirimire', 0),
(168, 10, 'Pedregal', 0),
(169, 10, 'Píritu Falcón', 0),
(170, 10, 'Pueblo Nuevo Falcón', 0),
(171, 10, 'Puerto Cumarebo', 0),
(172, 10, 'Punta Cardón', 0),
(173, 10, 'Punto Fijo', 0),
(174, 10, 'San Juan de Los Cayos', 0),
(175, 10, 'San Luis', 0),
(176, 10, 'Santa Ana Falcón', 0),
(177, 10, 'Santa Cruz De Bucaral', 0),
(178, 10, 'Tocopero', 0),
(179, 10, 'Tocuyo de La Costa', 0),
(180, 10, 'Tucacas', 0),
(181, 10, 'Yaracal', 0),
(182, 11, 'Altagracia de Orituco', 0),
(183, 11, 'Cabruta', 0),
(184, 11, 'Calabozo', 0),
(185, 11, 'Camaguán', 0),
(196, 11, 'Chaguaramas Guárico', 0),
(197, 11, 'El Socorro', 0),
(198, 11, 'El Sombrero', 0),
(199, 11, 'Las Mercedes de Los Llanos', 0),
(200, 11, 'Lezama', 0),
(201, 11, 'Onoto', 0),
(202, 11, 'Ortíz', 0),
(203, 11, 'San José de Guaribe', 0),
(204, 11, 'San Juan de Los Morros', 1),
(205, 11, 'San Rafael de Laya', 0),
(206, 11, 'Santa María de Ipire', 0),
(207, 11, 'Tucupido', 0),
(208, 11, 'Valle de La Pascua', 0),
(209, 11, 'Zaraza', 0),
(210, 12, 'Aguada Grande', 0),
(211, 12, 'Atarigua', 0),
(212, 12, 'Barquisimeto', 1),
(213, 12, 'Bobare', 0),
(214, 12, 'Cabudare', 0),
(215, 12, 'Carora', 0),
(216, 12, 'Cubiro', 0),
(217, 12, 'Cují', 0),
(218, 12, 'Duaca', 0),
(219, 12, 'El Manzano', 0),
(220, 12, 'El Tocuyo', 0),
(221, 12, 'Guaríco', 0),
(222, 12, 'Humocaro Alto', 0),
(223, 12, 'Humocaro Bajo', 0),
(224, 12, 'La Miel', 0),
(225, 12, 'Moroturo', 0),
(226, 12, 'Quíbor', 0),
(227, 12, 'Río Claro', 0),
(228, 12, 'Sanare', 0),
(229, 12, 'Santa Inés', 0),
(230, 12, 'Sarare', 0),
(231, 12, 'Siquisique', 0),
(232, 12, 'Tintorero', 0),
(233, 13, 'Apartaderos Mérida', 0),
(234, 13, 'Arapuey', 0),
(235, 13, 'Bailadores', 0),
(236, 13, 'Caja Seca', 0),
(237, 13, 'Canaguá', 0),
(238, 13, 'Chachopo', 0),
(239, 13, 'Chiguara', 0),
(240, 13, 'Ejido', 0),
(241, 13, 'El Vigía', 0),
(242, 13, 'La Azulita', 0),
(243, 13, 'La Playa', 0),
(244, 13, 'Lagunillas Mérida', 0),
(245, 13, 'Mérida', 1),
(246, 13, 'Mesa de Bolívar', 0),
(247, 13, 'Mucuchíes', 0),
(248, 13, 'Mucujepe', 0),
(249, 13, 'Mucuruba', 0),
(250, 13, 'Nueva Bolivia', 0),
(251, 13, 'Palmarito', 0),
(252, 13, 'Pueblo Llano', 0),
(253, 13, 'Santa Cruz de Mora', 0),
(254, 13, 'Santa Elena de Arenales', 0),
(255, 13, 'Santo Domingo', 0),
(256, 13, 'Tabáy', 0),
(257, 13, 'Timotes', 0),
(258, 13, 'Torondoy', 0),
(259, 13, 'Tovar', 0),
(260, 13, 'Tucani', 0),
(261, 13, 'Zea', 0),
(262, 14, 'Araguita', 0),
(263, 14, 'Carrizal', 0),
(264, 14, 'Caucagua', 0),
(265, 14, 'Chaguaramas Miranda', 0),
(266, 14, 'Charallave', 0),
(267, 14, 'Chirimena', 0),
(268, 14, 'Chuspa', 0),
(269, 14, 'Cúa', 0),
(270, 14, 'Cupira', 0),
(271, 14, 'Curiepe', 0),
(272, 14, 'El Guapo', 0),
(273, 14, 'El Jarillo', 0),
(274, 14, 'Filas de Mariche', 0),
(275, 14, 'Guarenas', 0),
(276, 14, 'Guatire', 0),
(277, 14, 'Higuerote', 0),
(278, 14, 'Los Anaucos', 0),
(279, 14, 'Los Teques', 1),
(280, 14, 'Ocumare del Tuy', 0),
(281, 14, 'Panaquire', 0),
(282, 14, 'Paracotos', 0),
(283, 14, 'Río Chico', 0),
(284, 14, 'San Antonio de Los Altos', 0),
(285, 14, 'San Diego de Los Altos', 0),
(286, 14, 'San Fernando del Guapo', 0),
(287, 14, 'San Francisco de Yare', 0),
(288, 14, 'San José de Los Altos', 0),
(289, 14, 'San José de Río Chico', 0),
(290, 14, 'San Pedro de Los Altos', 0),
(291, 14, 'Santa Lucía', 0),
(292, 14, 'Santa Teresa', 0),
(293, 14, 'Tacarigua de La Laguna', 0),
(294, 14, 'Tacarigua de Mamporal', 0),
(295, 14, 'Tácata', 0),
(296, 14, 'Turumo', 0),
(297, 15, 'Aguasay', 0),
(298, 15, 'Aragua de Maturín', 0),
(299, 15, 'Barrancas del Orinoco', 0),
(300, 15, 'Caicara de Maturín', 0),
(301, 15, 'Caripe', 0),
(302, 15, 'Caripito', 0),
(303, 15, 'Chaguaramal', 0),
(305, 15, 'Chaguaramas Monagas', 0),
(307, 15, 'El Furrial', 0),
(308, 15, 'El Tejero', 0),
(309, 15, 'Jusepín', 0),
(310, 15, 'La Toscana', 0),
(311, 15, 'Maturín', 1),
(312, 15, 'Miraflores', 0),
(313, 15, 'Punta de Mata', 0),
(314, 15, 'Quiriquire', 0),
(315, 15, 'San Antonio de Maturín', 0),
(316, 15, 'San Vicente Monagas', 0),
(317, 15, 'Santa Bárbara', 0),
(318, 15, 'Temblador', 0),
(319, 15, 'Teresen', 0),
(320, 15, 'Uracoa', 0),
(321, 16, 'Altagracia', 0),
(322, 16, 'Boca de Pozo', 0),
(323, 16, 'Boca de Río', 0),
(324, 16, 'El Espinal', 0),
(325, 16, 'El Valle del Espíritu Santo', 0),
(326, 16, 'El Yaque', 0),
(327, 16, 'Juangriego', 0),
(328, 16, 'La Asunción', 1),
(329, 16, 'La Guardia', 0),
(330, 16, 'Pampatar', 0),
(331, 16, 'Porlamar', 0),
(332, 16, 'Puerto Fermín', 0),
(333, 16, 'Punta de Piedras', 0),
(334, 16, 'San Francisco de Macanao', 0),
(335, 16, 'San Juan Bautista', 0),
(336, 16, 'San Pedro de Coche', 0),
(337, 16, 'Santa Ana de Nueva Esparta', 0),
(338, 16, 'Villa Rosa', 0),
(339, 17, 'Acarigua', 0),
(340, 17, 'Agua Blanca', 0),
(341, 17, 'Araure', 0),
(342, 17, 'Biscucuy', 0),
(343, 17, 'Boconoito', 0),
(344, 17, 'Campo Elías', 0),
(345, 17, 'Chabasquén', 0),
(346, 17, 'Guanare', 1),
(347, 17, 'Guanarito', 0),
(348, 17, 'La Aparición', 0),
(349, 17, 'La Misión', 0),
(350, 17, 'Mesa de Cavacas', 0),
(351, 17, 'Ospino', 0),
(352, 17, 'Papelón', 0),
(353, 17, 'Payara', 0),
(354, 17, 'Pimpinela', 0),
(355, 17, 'Píritu de Portuguesa', 0),
(356, 17, 'San Rafael de Onoto', 0),
(357, 17, 'Santa Rosalía', 0),
(358, 17, 'Turén', 0),
(359, 18, 'Altos de Sucre', 0),
(360, 18, 'Araya', 0),
(361, 18, 'Cariaco', 0),
(362, 18, 'Carúpano', 0),
(363, 18, 'Casanay', 0),
(364, 18, 'Cumaná', 1),
(365, 18, 'Cumanacoa', 0),
(366, 18, 'El Morro Puerto Santo', 0),
(367, 18, 'El Pilar', 0),
(368, 18, 'El Poblado', 0),
(369, 18, 'Guaca', 0),
(370, 18, 'Guiria', 0),
(371, 18, 'Irapa', 0),
(372, 18, 'Manicuare', 0),
(373, 18, 'Mariguitar', 0),
(374, 18, 'Río Caribe', 0),
(375, 18, 'San Antonio del Golfo', 0),
(376, 18, 'San José de Aerocuar', 0),
(377, 18, 'San Vicente de Sucre', 0),
(378, 18, 'Santa Fe de Sucre', 0),
(379, 18, 'Tunapuy', 0),
(380, 18, 'Yaguaraparo', 0),
(381, 18, 'Yoco', 0),
(382, 19, 'Abejales', 0),
(383, 19, 'Borota', 0),
(384, 19, 'Bramon', 0),
(385, 19, 'Capacho', 0),
(386, 19, 'Colón', 0),
(387, 19, 'Coloncito', 0),
(388, 19, 'Cordero', 0),
(389, 19, 'El Cobre', 0),
(390, 19, 'El Pinal', 0),
(391, 19, 'Independencia', 0),
(392, 19, 'La Fría', 0),
(393, 19, 'La Grita', 0),
(394, 19, 'La Pedrera', 0),
(395, 19, 'La Tendida', 0),
(396, 19, 'Las Delicias', 0),
(397, 19, 'Las Hernández', 0),
(398, 19, 'Lobatera', 0),
(399, 19, 'Michelena', 0),
(400, 19, 'Palmira', 0),
(401, 19, 'Pregonero', 0),
(402, 19, 'Queniquea', 0),
(403, 19, 'Rubio', 0),
(404, 19, 'San Antonio del Tachira', 0),
(405, 19, 'San Cristobal', 1),
(406, 19, 'San José de Bolívar', 0),
(407, 19, 'San Josecito', 0),
(408, 19, 'San Pedro del Río', 0),
(409, 19, 'Santa Ana Táchira', 0),
(410, 19, 'Seboruco', 0),
(411, 19, 'Táriba', 0),
(412, 19, 'Umuquena', 0),
(413, 19, 'Ureña', 0),
(414, 20, 'Batatal', 0),
(415, 20, 'Betijoque', 0),
(416, 20, 'Boconó', 0),
(417, 20, 'Carache', 0),
(418, 20, 'Chejende', 0),
(419, 20, 'Cuicas', 0),
(420, 20, 'El Dividive', 0),
(421, 20, 'El Jaguito', 0),
(422, 20, 'Escuque', 0),
(423, 20, 'Isnotú', 0),
(424, 20, 'Jajó', 0),
(425, 20, 'La Ceiba', 0),
(426, 20, 'La Concepción de Trujllo', 0),
(427, 20, 'La Mesa de Esnujaque', 0),
(428, 20, 'La Puerta', 0),
(429, 20, 'La Quebrada', 0),
(430, 20, 'Mendoza Fría', 0),
(431, 20, 'Meseta de Chimpire', 0),
(432, 20, 'Monay', 0),
(433, 20, 'Motatán', 0),
(434, 20, 'Pampán', 0),
(435, 20, 'Pampanito', 0),
(436, 20, 'Sabana de Mendoza', 0),
(437, 20, 'San Lázaro', 0),
(438, 20, 'Santa Ana de Trujillo', 0),
(439, 20, 'Tostós', 0),
(440, 20, 'Trujillo', 1),
(441, 20, 'Valera', 0),
(442, 21, 'Carayaca', 0),
(443, 21, 'Litoral', 0),
(444, 25, 'Archipiélago Los Roques', 0),
(445, 22, 'Aroa', 0),
(446, 22, 'Boraure', 0),
(447, 22, 'Campo Elías de Yaracuy', 0),
(448, 22, 'Chivacoa', 0),
(449, 22, 'Cocorote', 0),
(450, 22, 'Farriar', 0),
(451, 22, 'Guama', 0),
(452, 22, 'Marín', 0),
(453, 22, 'Nirgua', 0),
(454, 22, 'Sabana de Parra', 0),
(455, 22, 'Salom', 0),
(456, 22, 'San Felipe', 1),
(457, 22, 'San Pablo de Yaracuy', 0),
(458, 22, 'Urachiche', 0),
(459, 22, 'Yaritagua', 0),
(460, 22, 'Yumare', 0),
(461, 23, 'Bachaquero', 0),
(462, 23, 'Bobures', 0),
(463, 23, 'Cabimas', 0),
(464, 23, 'Campo Concepción', 0),
(465, 23, 'Campo Mara', 0),
(466, 23, 'Campo Rojo', 0),
(467, 23, 'Carrasquero', 0),
(468, 23, 'Casigua', 0),
(469, 23, 'Chiquinquirá', 0),
(470, 23, 'Ciudad Ojeda', 0),
(471, 23, 'El Batey', 0),
(472, 23, 'El Carmelo', 0),
(473, 23, 'El Chivo', 0),
(474, 23, 'El Guayabo', 0),
(475, 23, 'El Mene', 0),
(476, 23, 'El Venado', 0),
(477, 23, 'Encontrados', 0),
(478, 23, 'Gibraltar', 0),
(479, 23, 'Isla de Toas', 0),
(480, 23, 'La Concepción del Zulia', 0),
(481, 23, 'La Paz', 0),
(482, 23, 'La Sierrita', 0),
(483, 23, 'Lagunillas del Zulia', 0),
(484, 23, 'Las Piedras de Perijá', 0),
(485, 23, 'Los Cortijos', 0),
(486, 23, 'Machiques', 0),
(487, 23, 'Maracaibo', 1),
(488, 23, 'Mene Grande', 0),
(489, 23, 'Palmarejo', 0),
(490, 23, 'Paraguaipoa', 0),
(491, 23, 'Potrerito', 0),
(492, 23, 'Pueblo Nuevo del Zulia', 0),
(493, 23, 'Puertos de Altagracia', 0),
(494, 23, 'Punta Gorda', 0),
(495, 23, 'Sabaneta de Palma', 0),
(496, 23, 'San Francisco', 0),
(497, 23, 'San José de Perijá', 0),
(498, 23, 'San Rafael del Moján', 0),
(499, 23, 'San Timoteo', 0),
(500, 23, 'Santa Bárbara Del Zulia', 0),
(501, 23, 'Santa Cruz de Mara', 0),
(502, 23, 'Santa Cruz del Zulia', 0),
(503, 23, 'Santa Rita', 0),
(504, 23, 'Sinamaica', 0),
(505, 23, 'Tamare', 0),
(506, 23, 'Tía Juana', 0),
(507, 23, 'Villa del Rosario', 0),
(508, 21, 'La Guaira', 1),
(509, 21, 'Catia La Mar', 0),
(510, 21, 'Macuto', 0),
(511, 21, 'Naiguatá', 0),
(512, 25, 'Archipiélago Los Monjes', 0),
(513, 25, 'Isla La Tortuga y Cayos adyacentes', 0),
(514, 25, 'Isla La Sola', 0),
(515, 25, 'Islas Los Testigos', 0),
(516, 25, 'Islas Los Frailes', 0),
(517, 25, 'Isla La Orchila', 0),
(518, 25, 'Archipiélago Las Aves', 0),
(519, 25, 'Isla de Aves', 0),
(520, 25, 'Isla La Blanquilla', 0),
(521, 25, 'Isla de Patos', 0),
(522, 25, 'Islas Los Hermanos', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_cliente`
--

CREATE TABLE `tbl_cliente` (
  `id` int(11) NOT NULL,
  `id_usuario_socio` int(11) NOT NULL,
  `id_usuario_gerente` int(11) NOT NULL,
  `codigo` int(11) NOT NULL,
  `rif` varchar(15) NOT NULL,
  `nit` int(11) NOT NULL,
  `razon_social` varchar(500) NOT NULL,
  `id_parroquia_fiscal` int(11) NOT NULL,
  `avenida_calle_fiscal` varchar(250) NOT NULL,
  `edificio_quinta_fiscal` varchar(25) NOT NULL,
  `piso_fiscal` varchar(3) NOT NULL,
  `numero_fiscal` varchar(5) NOT NULL,
  `ciudad_fiscal` varchar(50) NOT NULL,
  `telefono_fiscal` varchar(20) NOT NULL,
  `pagina_web` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `email_fiscal` varchar(100) NOT NULL,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadores `tbl_cliente`
--
DELIMITER $$
CREATE TRIGGER `tbl_cliente_AI` AFTER INSERT ON `tbl_cliente` FOR EACH ROW INSERT INTO logs.tbl_cliente(codigo, id_usuario_socio_nuevo, id_usuario_gerente_nuevo, rif_nuevo, nit_nuevo, razon_social_nuevo, id_parroquia_fiscal_nuevo, avenida_calle_fiscal_nuevo, edificio_quinta_fiscal_nuevo, piso_fiscal_nuevo, ciudad_fiscal_nuevo, telefono_fiscal_nuevo, pagina_web_nuevo, email_fiscal_nuevo, id_estatus_nuevo) VALUES (NEW.codigo, NEW.id_usuario_socio, NEW.id_usuario_gerente, NEW.rif, NEW.nit, NEW.razon_social, NEW.id_parroquia_fiscal, NEW.avenida_calle_fiscal, NEW.edificio_quinta_fiscal, NEW.piso_fiscal, NEW.ciudad_fiscal, NEW.telefono_fiscal, NEW.pagina_web, NEW.email_fiscal, NEW.id_estatus)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tbl_cliente_BU` BEFORE UPDATE ON `tbl_cliente` FOR EACH ROW INSERT INTO logs.tbl_cliente(codigo, id_usuario_socio, id_usuario_gerente, rif, nit, razon_social, id_parroquia_fiscal, avenida_calle_fiscal, edificio_quinta_fiscal, piso_fiscal, ciudad_fiscal, telefono_fiscal, pagina_web, email_fiscal, id_estatus, id_usuario_socio_nuevo, id_usuario_gerente_nuevo, rif_nuevo, nit_nuevo, razon_social_nuevo, id_parroquia_fiscal_nuevo, avenida_calle_fiscal_nuevo, edificio_quinta_fiscal_nuevo, piso_fiscal_nuevo, ciudad_fiscal_nuevo, telefono_fiscal_nuevo, pagina_web_nuevo, email_fiscal_nuevo, id_estatus_nuevo) VALUES (NEW.codigo, OLD.id_usuario_socio, OLD.id_usuario_gerente, OLD.rif, OLD.nit, OLD.razon_social, OLD.id_parroquia_fiscal, OLD.avenida_calle_fiscal, OLD.edificio_quinta_fiscal, OLD.piso_fiscal, OLD.ciudad_fiscal, OLD.telefono_fiscal, OLD.pagina_web, OLD.email_fiscal, OLD.id_estatus, NEW.id_usuario_socio, NEW.id_usuario_gerente, NEW.rif, NEW.nit, NEW.razon_social, NEW.id_parroquia_fiscal, NEW.avenida_calle_fiscal, NEW.edificio_quinta_fiscal, NEW.piso_fiscal, NEW.ciudad_fiscal, NEW.telefono_fiscal, NEW.pagina_web, NEW.email_fiscal, NEW.id_estatus)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_cliente_facturacion`
--

CREATE TABLE `tbl_cliente_facturacion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_proyecto` int(11) DEFAULT NULL,
  `id_parroquia_factura` int(11) DEFAULT NULL,
  `avenida_calle_factura` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edificio_quinta_factura` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `piso_factura` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_factura` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad_factura` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono_factura` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax_factura` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_factura` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_estatus` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `tbl_cliente_facturacion`
--
DELIMITER $$
CREATE TRIGGER `tbl_cliente_facturacion_AI` AFTER INSERT ON `tbl_cliente_facturacion` FOR EACH ROW INSERT INTO logs.tbl_cliente_facturacion(id_facturacion, id_cliente_nuevo, id_proyecto_nuevo, id_parroquia_factura_nuevo, avenida_calle_factura_nuevo, edificio_quinta_factura_nuevo, piso_factura_nuevo, numero_factura_nuevo, ciudad_factura_nuevo, telefono_factura_nuevo, fax_factura_nuevo, email_factura_nuevo, id_estatus_nuevo) VALUES (NEW.id, NEW.id_cliente, NEW.id_proyecto, NEW.id_parroquia_factura, NEW.avenida_calle_factura, NEW.edificio_quinta_factura, NEW.piso_factura, NEW.numero_factura, NEW.ciudad_factura, NEW.telefono_factura, NEW.fax_factura, NEW.email_factura, NEW.id_estatus)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tbl_cliente_facturacion_BU` BEFORE UPDATE ON `tbl_cliente_facturacion` FOR EACH ROW INSERT INTO logs.tbl_cliente_facturacion(id_facturacion, id_cliente, id_proyecto, id_parroquia_factura, avenida_calle_factura, edificio_quinta_factura, piso_factura, numero_factura, ciudad_factura, telefono_factura, fax_factura, email_factura, id_estatus, id_cliente_nuevo, id_proyecto_nuevo, id_parroquia_factura_nuevo, avenida_calle_factura_nuevo, edificio_quinta_factura_nuevo, piso_factura_nuevo, numero_factura_nuevo, ciudad_factura_nuevo, telefono_factura_nuevo, fax_factura_nuevo, email_factura_nuevo, id_estatus_nuevo) VALUES (NEW.id, OLD.id_cliente, OLD.id_proyecto, OLD.id_parroquia_factura, OLD.avenida_calle_factura, OLD.edificio_quinta_factura, OLD.piso_factura, OLD.numero_factura, OLD.ciudad_factura, OLD.telefono_factura, OLD.fax_factura, OLD.email_factura, OLD.id_estatus, NEW.id_cliente, NEW.id_proyecto, NEW.id_parroquia_factura, NEW.avenida_calle_factura, NEW.edificio_quinta_factura, NEW.piso_factura, NEW.numero_factura, NEW.ciudad_factura, NEW.telefono_factura, NEW.fax_factura, NEW.email_factura, NEW.id_estatus)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_concepto_horas_no_cargables`
--

CREATE TABLE `tbl_concepto_horas_no_cargables` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbl_concepto_horas_no_cargables`
--

INSERT INTO `tbl_concepto_horas_no_cargables` (`id`, `descripcion`, `id_estatus`) VALUES
(1, 'Vacaciones', 1),
(2, 'Permiso Actividades profesionales captación futuro cliente', 1),
(3, 'Disponible', 1),
(4, 'Pandemia', 1),
(5, 'Permiso', 1),
(6, 'Permiso Médico', 1),
(7, 'Permiso Universitario', 1),
(8, 'Reposo Médico', 1),
(9, 'Reunion de staff ', 1),
(10, 'Reunión de Staff Personal Directivo', 1),
(11, 'Seminarios Web Vía Zoom', 1),
(12, 'Talleres de Desarrollo Profesional', 1),
(13, 'Tareas administrativas Personal Profesional', 1);

--
-- Disparadores `tbl_concepto_horas_no_cargables`
--
DELIMITER $$
CREATE TRIGGER `tbl_concepto_horas_no_cargables_AI` AFTER INSERT ON `tbl_concepto_horas_no_cargables` FOR EACH ROW INSERT INTO logs.tbl_concepto_horas_no_cargables(id_concepto, descripcion_nuevo, id_estatus_nuevo) VALUES (NEW.id, NEW.descripcion, NEW.id_estatus)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_configuracion`
--

CREATE TABLE `tbl_configuracion` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_configuracion`
--

INSERT INTO `tbl_configuracion` (`id`, `nombre`, `valor`, `descripcion`) VALUES
(1, 'encrypt-key', '0123456789abcdef0123456789abcdef', 'Key de encriptación, generalmente empleada para encriptar y desencriptar valores del cliente al servidor como por ejemplo contraseña del login'),
(2, 'encrypt-iv', 'abcdef9876543210abcdef9876543210', 'IV de encriptación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_contacto_usuario`
--

CREATE TABLE `tbl_contacto_usuario` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `correo_principal` varchar(255) NOT NULL,
  `correo_secundario` varchar(255) DEFAULT NULL,
  `telefono_principal` varchar(30) DEFAULT NULL,
  `telefono_secundario` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_contacto_usuario`
--

INSERT INTO `tbl_contacto_usuario` (`id`, `id_usuario`, `correo_principal`, `correo_secundario`, `telefono_principal`, `telefono_secundario`) VALUES
(1, 1, 'dmolina101@gmail.com', '', '(0000) - 000 0000', '(0000) - 000 0002'),
(2, 2, 'nathalie.lopez@crowe.com.ve', '', '', ''),
(3, 3, 'yesenia.martinez@crowe.com.ve', '', '', ''),
(4, 4, 'jesus.perez@crowe.com.ve', '', '', ''),
(5, 5, 'carol.lopez@crowe.com.ve', '', '', ''),
(6, 6, 'luz.fonseca@crowe.com.ve', '', '', ''),
(7, 7, 'arturo.madriz@crowe.com.ve', '', '', ''),
(8, 8, 'roman.scott@crowe.com.ve', '', '', ''),
(9, 9, 'oliver.paez@crowe.com.ve', '', '', ''),
(10, 10, 'jorge.gonzalez@crowe.com.ve', '', '', ''),
(11, 11, 'maria.sequeda@crowe.com.ve', '', '', ''),
(12, 12, 'yodelina.torres@crowe.com.ve', '', '', ''),
(13, 13, 'katherine.zurita@crowe.com.ve', '', '04241907404', '04243170363'),
(14, 14, 'mileidis.moreno@crowe.com.ve', '', '04241718118', ''),
(15, 15, 'francia.medina@crowe.com.ve', '', '04166947046', ''),
(16, 16, 'astrid.mendoza@crowe.com.ve', '', '04241652571', ''),
(17, 17, 'maria.tovar@crowe.com.ve', '', '02124829623', '04242473031'),
(18, 18, 'mariana.brito@crowe.com.ve', '', '04242902167', ''),
(19, 19, 'belkis.cortina@crowe.com.ve', '', '02124159553', '04164079713'),
(20, 20, 'lucrecia.silva@crowe.com.ve', '', '04264199217', ''),
(21, 21, 'normedy.parra@crowe.com.ve', '', '02125241716', '04120125384'),
(22, 22, 'josvelis.castillo@crowe.com.ve', '', '04148350920', '02123440542'),
(23, 23, 'luis.russian@crowe.com.ve', '', '04242602227', '02123395206'),
(24, 24, 'jonathan.azocar@crowe.com.ve', '', '02123774758', '04266373419'),
(25, 25, 'yerlenis.valderrama@crowe.com.ve', '', '04242950201', ''),
(26, 26, 'kleiver.corro@crowe.com.ve', '', '02124190028', '04129496868'),
(27, 27, 'maryuri.barazarte@crowe.com.ve', '', '02122444894', '04241839221'),
(28, 28, 'pedro.benitez@crowe.com.ve', '', '', ''),
(29, 29, 'dennys.flores@crowe.com.ve', '', '04160192302', ''),
(30, 30, 'genesis.marcano@crowe.com.ve', '', '02392482117', '04140209137'),
(31, 31, 'keilimar.suarez@crowe.com.ve', '', '04169281259', '02127459838'),
(32, 32, 'johanne.muñoz@crowe.com.ve', '', '04143155147', ''),
(33, 33, 'alfredo.hernandez@crowe.com.ve', '', '04127116777', ''),
(34, 34, 'raul.vargas@crowe.com.ve', '', '', ''),
(35, 35, 'shelcie.paz@crowe.com.ve', '', '02122583241', '04149084285'),
(36, 36, 'ladymar.morett@crowe.com.ve', '', '02124512556', '04261197245'),
(37, 37, 'anthony.garcia@crowe.com.ve', '', '04262130363', '04123840786'),
(38, 38, 'solmary.martinez@crowe.com.ve', '', '02123397992', '04129904281'),
(39, 39, 'jackeline.ramos@crowe.com.ve', '', '02126412375', '04168233236'),
(40, 40, 'belkis.vazquez@crowe.com.ve', '', '04262157178', ''),
(41, 41, 'yuzleibby.maldonado@crowe.com.ve', '', '04242194508', '02128703102'),
(42, 42, 'giovanni.corredor@crowe.com.ve', '', '04120102693', '02123472038'),
(43, 43, 'kleiver.cadenas@crowe.com.ve', '', '04143196616', '02122678468'),
(44, 44, 'ivette.orozco@crowe.com.ve', '', '04242613215', '02124341107'),
(45, 45, 'zunaya.wilches@crowe.com.ve', '', '04140316013', '02126135612'),
(46, 46, 'jesus.abraham@crowe.com.ve', '', '02123727075', '04242147829'),
(47, 47, 'jose.perozo@crowe.com.ve', '', '04262539113', ''),
(48, 48, 'roberto.villegas@crowe.com.ve', '', '02128703830', '04241762670'),
(49, 49, 'sandro.mayora@crowe.com.ve', '', '04123675678', '02125163034'),
(50, 50, 'eduardo.bastos@crowe.com.ve', '', '02129875898', '04241304353'),
(51, 51, 'vanessa.rojas@crowe.com.ve', '', '04147826035', ''),
(52, 52, 'carlos.revete@crowe.com.ve', '', '04242591419', ''),
(53, 53, 'vianney.rugeles@crowe.com.ve', '', '02124434371', '04129987473'),
(54, 54, 'edwin.burgos@crowe.com.ve', '', '04142871671', ''),
(55, 55, 'nombre.apellido@dominio.com', '', '02123637192', '04128584022'),
(56, 56, 'freddy.vargas@crowe.com.ve', '', '04241292285', ''),
(57, 57, 'yorman.rangel@crowe.com.ve', '', '', ''),
(58, 58, 'jose.utrera@crowe.com.ve', '', '', ''),
(59, 59, 'alejandro.lira@crowe.com.ve', '', '02126724819', '04142460103'),
(60, 60, 'yordalis.echarrys@crowe.com.ve', '', '04122932692', ''),
(61, 61, 'eliana.ponce@crowe.com.ve', '', '02125761138', '04149113335'),
(62, 62, 'stefany.gonzalez@crowe.com.ve', '', '04242085444', ''),
(63, 63, 'naivelys.altuve@crowe.com.ve', '', '04147914010', ''),
(64, 64, 'gabriela.gil@crowe.com.ve', '', '02126621812', '04262874127'),
(65, 65, 'orianna.alejos@crowe.com.ve', '', '02126689284', '04263158428'),
(66, 66, 'marynes.gonzalez@crowe.com.ve', '', '02124929084', '04242628459'),
(67, 67, 'eligio.mendoza@crowe.com.ve', '', '', ''),
(68, 68, 'marielvi.oller@crowe.com.ve', '', '', ''),
(69, 69, 'alba.navia@crowe.com.ve', '', '02127625333', '04242984865'),
(70, 70, 'nombre.apellido@dominio.com', '', '04141266489', '02128614414'),
(71, 71, 'yessica.rivas@crowe.com.ve', '', '02128750733', '04242677331'),
(72, 72, 'nombre.apellido@dominio.com', '', '04142119162', ''),
(73, 73, 'yda.chirinos@crowe.com.ve', '', '02125159794', '04241360393'),
(74, 74, 'nombre.apellido@dominio.com', '', '02126149790', '04143260002'),
(75, 75, 'nombre.apellido@dominio.com', '', '02123243797', '04122493721'),
(76, 76, 'nombre.apellido@dominio.com', '', '04125746284', ''),
(77, 77, 'nombre.apellido@dominio.com', '', '04122564514', ''),
(78, 78, 'nombre.apellido@dominio.com', '', '04142678216', ''),
(79, 79, 'nahomy.quintero@crowe.com.ve', '', '02127446051', '04241743888'),
(80, 80, 'maria.espina@crowe.com.ve', '', '0127304196', '04265131381'),
(81, 81, 'nombre.apellido@dominio.com', '', '04242696996', '04129217899'),
(82, 82, 'nombre.apellido@dominio.com', '', '04127098992', ''),
(83, 83, 'glender.cortez@crowe.com.ve', '', '04142190677', '02125321810'),
(84, 84, 'alberto.evies@crowe.com.ve', '', '02124335180', '04141057605'),
(85, 85, 'angela.aranea@crowe.com.ve', '', '02125153658', '04263046685'),
(86, 86, 'arturo.sosa@crowe.com.ve', '', '04241340102', ''),
(87, 87, 'adrian.perez@crowe.com.ve', '', '02128613428', '04128045133'),
(88, 88, 'elisa.pasero@crowe.com.ve', '', '04123688968', ''),
(89, 89, 'omar.marquez@crowe.com.ve', '', '', ''),
(90, 90, 'angelica.funes@crowe.com.ve', '', '02128583253', '04262905898'),
(91, 91, 'eslyn.rojas@crowe.com.ve', '', '04243443594', '02128084209'),
(92, 92, 'carmen.ochoa@crowe.com.ve', '', '04241495523', ''),
(93, 93, 'laura.rojas@crowe.com.ve', '', '', ''),
(94, 94, 'nombre.apellido@dominio.com', '', '04242258139', ''),
(95, 95, 'nombre.apellido@dominio.com', '', '', ''),
(96, 96, 'nombre.apellido@dominio.com', '', '04169397195', ''),
(97, 97, 'jose.machado@crowe.com.ve', '', '', ''),
(98, 98, 'nombre.apellido@dominio.com', '', '', ''),
(99, 99, 'nombre.apellido@dominio.com', '', '', ''),
(100, 100, 'ana.castaño@crowe.com.ve', '', '02125716504', ''),
(101, 101, 'amayoisbi.garcia@crowe.com.ve', '', '04127013435', ''),
(102, 102, 'jennifer.chacon@crowe.com.ve', '', '04125897240', ''),
(103, 103, 'ignayari.mendoza@crowe.com.ve', '', '04129289923', ''),
(104, 104, 'reina.fajardo@crowe.com.ve', '', '04164269965', ''),
(105, 105, 'yolymer.mendoza@crowe.com.ve', '', '04149018276', '02126813348'),
(106, 106, 'nombre.apellido@dominio.com', '', '04129762870', ''),
(107, 107, 'adriana.guzman@crowe.com.ve', '', '02129412882', '04144549562'),
(108, 108, 'jose.estaba@crowe.com.ve', '', '02128602803', '04243389487'),
(109, 109, 'karina.perez@crowe.com.ve', '', '04265920655', ''),
(110, 110, 'zonny.garcia@crowe.com.ve', '', '04243138868', '02392252293'),
(111, 111, 'nombre.apellido@dominio.com', '', '04268870548', ''),
(112, 112, 'nombre.apellido@dominio.com', '', '04262166223', ''),
(113, 113, 'leonardo.lopez@crowe.com.ve', '', '04142598750', ''),
(114, 114, 'nombre.apellido@dominio.com', '', '02124818970', '04128259076'),
(115, 115, 'nombre.apellido@dominio.com', '', '02124518087', '04129576671'),
(116, 116, 'antonio.reyes@crowe.com.ve', '', '02122425335', '04141626367'),
(117, 117, 'nombre.apellido@dominio.com', '', '04241842688', ''),
(118, 118, 'freddy.perdomo@crowe.com.ve', '', '02129766425', '04144466147'),
(119, 119, 'fernando.rangel@crowe.com.ve', '', '04141782596', ''),
(120, 120, 'gelen.cardenas@crowe.com.ve', '', '02125767453', '04164654993'),
(121, 121, 'nombre.apellido@dominio.com', '', '02122373113', ''),
(122, 122, 'nombre.apellido@dominio.com', '', '02122373113', '04142081976'),
(123, 123, 'nombre.apellido@dominio.com', '', '04169322811', ''),
(124, 124, 'amelia.diaz@crowe.com.ve', '', '', ''),
(125, 125, 'emilio.leon@crowe.com.ve', '', '04166084971', '04241180197'),
(126, 126, 'gustavo.puchi@crowe.com.ve', '', '02124834655', '04122206492'),
(127, 127, 'alfio.saglimbeni@crowe.com.ve', '', '04168272679', ''),
(128, 128, 'arianna.matos@crowe.com.ve', '', '02123238208', '04169155523'),
(129, 129, 'ana.blandin@crowe.com.ve', '', '02124329839', '04241624237'),
(130, 130, 'oscar.piña@crowe.com.ve', '', '', ''),
(131, 131, 'nombre.apellido@dominio.com', '', '02128084742', '05247042110'),
(132, 132, 'duglimar.mendez@crowe.com.ve', '', '04162062192', ''),
(133, 133, 'sol.viana@crowe.com.ve', '', '02126316797', '04241469101'),
(134, 134, 'douglas.torrealba@crowe.com.ve', '', '04162094874', '04168000868'),
(135, 135, 'nombre.apellido@dominio.com', '', '04267528235', '02128715756'),
(136, 136, 'nombre.apellido@dominio.com', '', '04261396926', ''),
(137, 137, 'nombre.apellido@dominio.com', '', '04126305629', ''),
(138, 138, 'nombre.apellido@dominio.com', '', '04162139037', '02124909126'),
(139, 139, 'nombre.apellido@dominio.com', '', '02124329566', '04168175614'),
(140, 140, 'fredy.bautista@crowe.com.ve', '', '', ''),
(141, 141, 'nombre.apellido@dominio.com', '', '', ''),
(142, 142, 'barbara.zambrano@crowe.com.ve', '', '', ''),
(143, 143, 'mary.cruz@crowe.com.ve', '', '04249686614', '02869341430');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_division`
--

CREATE TABLE `tbl_division` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_division`
--

INSERT INTO `tbl_division` (`id`, `descripcion`, `id_estatus`) VALUES
(1, 'Auditoría Externa', 1),
(2, 'Asesoría Tributaria', 1),
(3, 'Auditoría TI', 1),
(4, 'Outsourcing', 1),
(5, 'Consultoria y Auditoría Interna', 1),
(6, 'Business and Process Consulting', 1),
(7, 'Administración/Capital Humano', 1),
(8, 'Administración/Contabilidad', 1),
(9, 'Administración/Tesorería', 1),
(10, 'Administración/Contraloría', 1),
(11, 'Administración/Servicios Generales', 1),
(12, 'Administración/Edición', 1),
(13, 'Administración/Soporte Técnico', 1),
(14, 'Adiestramiento', 1),
(15, 'Pasantes Inces', 1),
(16, 'Conapdis', 1),
(17, 'Legal', 1),
(18, 'Servicios Profesionales (Puerto Ordaz)', 1),
(19, 'Administración', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_estados`
--

CREATE TABLE `tbl_estados` (
  `id` int(11) NOT NULL,
  `estado` varchar(250) NOT NULL,
  `iso_3166-2` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_estados`
--

INSERT INTO `tbl_estados` (`id`, `estado`, `iso_3166-2`) VALUES
(1, 'Amazonas', 'VE-X'),
(2, 'Anzoátegui', 'VE-B'),
(3, 'Apure', 'VE-C'),
(4, 'Aragua', 'VE-D'),
(5, 'Barinas', 'VE-E'),
(6, 'Bolívar', 'VE-F'),
(7, 'Carabobo', 'VE-G'),
(8, 'Cojedes', 'VE-H'),
(9, 'Delta Amacuro', 'VE-Y'),
(10, 'Falcón', 'VE-I'),
(11, 'Guárico', 'VE-J'),
(12, 'Lara', 'VE-K'),
(13, 'Mérida', 'VE-L'),
(14, 'Miranda', 'VE-M'),
(15, 'Monagas', 'VE-N'),
(16, 'Nueva Esparta', 'VE-O'),
(17, 'Portuguesa', 'VE-P'),
(18, 'Sucre', 'VE-R'),
(19, 'Táchira', 'VE-S'),
(20, 'Trujillo', 'VE-T'),
(21, 'Vargas', 'VE-W'),
(22, 'Yaracuy', 'VE-U'),
(23, 'Zulia', 'VE-V'),
(24, 'Distrito Capital', 'VE-A'),
(25, 'Dependencias Federales', 'VE-Z');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_estatus`
--

CREATE TABLE `tbl_estatus` (
  `id` int(11) NOT NULL,
  `tabla` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `valor` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_estatus`
--

INSERT INTO `tbl_estatus` (`id`, `tabla`, `valor`, `descripcion`) VALUES
(1, 'tbl_usuario', 1, 'activo'),
(2, 'tbl_usuario', 2, 'inactivo'),
(3, 'tbl_usuario', 3, 'de vacaciones'),
(4, 'tbl_usuario', 4, 'de reposo'),
(5, 'tbl_tipo_contacto', 1, 'activo'),
(6, 'tbl_tipo_contacto', 2, 'inactivo'),
(7, 'tbl_cargo_empleado', 1, 'activo'),
(8, 'tbl_cargo_empleado', 2, 'inactivo'),
(9, 'tbl_menu', 1, 'activo'),
(10, 'tbl_menu', 2, 'inactivo'),
(11, 'tbl_division', 1, 'activo'),
(12, 'tbl_division', 2, 'inactivo'),
(13, 'tbl_proyecto', 1, 'activo'),
(14, 'tbl_proyecto', 2, 'inactivo'),
(15, 'tbl_cliente', 1, 'activo'),
(16, 'tbl_cliente', 2, 'inactivo'),
(17, 'tbl_concepto_horas_no_cargables', 1, 'Activo'),
(18, 'tbl_concepto_horas_no_cargables', 2, 'Inactivo'),
(19, 'tbl_horas_no_cargables', 1, 'Por Aprobar'),
(20, 'tbl_horas_no_cargables', 2, 'Aprobada'),
(21, 'tbl_horas_no_cargables', 3, 'No Aprobada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_estatus_login_denegado`
--

CREATE TABLE `tbl_estatus_login_denegado` (
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Estatus de usuario los cuales no se les permite el acceso';

--
-- Volcado de datos para la tabla `tbl_estatus_login_denegado`
--

INSERT INTO `tbl_estatus_login_denegado` (`id_estatus`) VALUES
(2),
(3),
(4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_horas_cargables`
--

CREATE TABLE `tbl_horas_cargables` (
  `id` int(11) NOT NULL,
  `id_proy_analista` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `horas_trabajadas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `tbl_horas_cargables`
--
DELIMITER $$
CREATE TRIGGER `tbl_horas_cargables_AI` AFTER INSERT ON `tbl_horas_cargables` FOR EACH ROW INSERT INTO logs.tbl_horas_cargables(id_horas_cargables, id_proy_analista_nuevo, fecha_horas_cargables_nuevo, descripcion_nuevo, horas_trabajadas_nuevo) VALUES (NEW.id, NEW.id_proy_analista, NEW.fecha, NEW.descripcion, NEW.horas_trabajadas)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tbl_horas_cargables_BU` BEFORE UPDATE ON `tbl_horas_cargables` FOR EACH ROW INSERT INTO logs.tbl_horas_cargables(id_horas_cargables, id_proy_analista, fecha_horas_cargables, descripcion, horas_trabajadas, id_proy_analista_nuevo, fecha_horas_cargables_nuevo, descripcion_nuevo, horas_trabajadas_nuevo) VALUES (NEW.id, OLD.id_proy_analista, OLD.fecha, OLD.descripcion, OLD.horas_trabajadas, NEW.id_proy_analista, NEW.fecha, NEW.descripcion, NEW.horas_trabajadas)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_horas_no_cargables`
--

CREATE TABLE `tbl_horas_no_cargables` (
  `id` int(11) NOT NULL,
  `id_concepto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_division` int(11) NOT NULL,
  `aprobado_por` int(11) DEFAULT NULL,
  `fecha_desde` datetime NOT NULL COMMENT 'El formato de la fecha es UTC',
  `fecha_hasta` datetime NOT NULL COMMENT 'El formato de la fecha es UTC',
  `fecha_aprobacion` datetime DEFAULT NULL,
  `observacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_menu`
--

CREATE TABLE `tbl_menu` (
  `id` int(11) NOT NULL,
  `id_menu_padre` int(11) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `url` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `orden` int(11) NOT NULL,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_menu`
--

INSERT INTO `tbl_menu` (`id`, `id_menu_padre`, `descripcion`, `url`, `orden`, `id_estatus`) VALUES
(1, 0, 'Usuario', '', 0, 1),
(2, 1, 'Crear Usuario', '/formNuevoUsuario', 0, 1),
(3, 1, 'Consultar Usuario', '/formBuscarUsuario', 1, 1),
(4, 0, 'Clientes', '', 0, 1),
(5, 4, 'Crear Cliente', '/formNuevoCliente', 0, 1),
(6, 4, 'Consultar Cliente', '/formBuscarCliente', 1, 1),
(7, 4, 'Detalle Facturacion', '/formDetalleFactCliente', 1, 1),
(8, 0, 'Proyectos', '', 0, 1),
(9, 8, 'Crear Proyecto', '/formNuevoProyecto', 0, 1),
(10, 8, 'Lista de Proyectos', '/proyectos', 1, 1),
(11, 8, 'Asignados/ar Proyectos ', '/proyectoDivision', 2, 1),
(12, 0, 'Horas No Cargables', '', 0, 1),
(13, 12, 'Conceptos', '/formHorasNoCargables', 0, 1),
(14, 12, 'Cargar', '/cargarHorasNoCargables', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_menu_usuario`
--

CREATE TABLE `tbl_menu_usuario` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `C` int(11) NOT NULL,
  `R` int(11) NOT NULL,
  `U` int(11) NOT NULL,
  `D` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_menu_usuario`
--

INSERT INTO `tbl_menu_usuario` (`id`, `id_usuario`, `id_menu`, `C`, `R`, `U`, `D`) VALUES
(1, 1, 2, 0, 0, 0, 0),
(2, 1, 3, 0, 1, 1, 0),
(3, 1, 5, 0, 0, 0, 0),
(4, 1, 6, 0, 1, 1, 0),
(5, 1, 7, 1, 1, 1, 0),
(6, 1, 9, 1, 1, 0, 0),
(7, 1, 10, 1, 1, 1, 0),
(8, 1, 11, 1, 1, 1, 1),
(9, 1, 13, 1, 1, 1, 1),
(10, 1, 14, 1, 1, 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_monedas`
--

CREATE TABLE `tbl_monedas` (
  `id` int(11) NOT NULL,
  `moneda` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `simbolo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int(1) NOT NULL,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbl_monedas`
--

INSERT INTO `tbl_monedas` (`id`, `moneda`, `simbolo`, `orden`, `id_estatus`) VALUES
(1, 'Bolívar', 'Bs', 2, 1),
(2, 'Dólar', '$', 1, 1),
(3, 'Euro', '€', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_municipios`
--

CREATE TABLE `tbl_municipios` (
  `id` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL,
  `municipio` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_municipios`
--

INSERT INTO `tbl_municipios` (`id`, `id_estado`, `municipio`) VALUES
(1, 1, 'Alto Orinoco'),
(2, 1, 'Atabapo'),
(3, 1, 'Atures'),
(4, 1, 'Autana'),
(5, 1, 'Manapiare'),
(6, 1, 'Maroa'),
(7, 1, 'Río Negro'),
(8, 2, 'Anaco'),
(9, 2, 'Aragua'),
(10, 2, 'Manuel Ezequiel Bruzual'),
(11, 2, 'Diego Bautista Urbaneja'),
(12, 2, 'Fernando Peñalver'),
(13, 2, 'Francisco Del Carmen Carvajal'),
(14, 2, 'General Sir Arthur McGregor'),
(15, 2, 'Guanta'),
(16, 2, 'Independencia'),
(17, 2, 'José Gregorio Monagas'),
(18, 2, 'Juan Antonio Sotillo'),
(19, 2, 'Juan Manuel Cajigal'),
(20, 2, 'Libertad'),
(21, 2, 'Francisco de Miranda'),
(22, 2, 'Pedro María Freites'),
(23, 2, 'Píritu'),
(24, 2, 'San José de Guanipa'),
(25, 2, 'San Juan de Capistrano'),
(26, 2, 'Santa Ana'),
(27, 2, 'Simón Bolívar'),
(28, 2, 'Simón Rodríguez'),
(29, 3, 'Achaguas'),
(30, 3, 'Biruaca'),
(31, 3, 'Muñóz'),
(32, 3, 'Páez'),
(33, 3, 'Pedro Camejo'),
(34, 3, 'Rómulo Gallegos'),
(35, 3, 'San Fernando'),
(36, 4, 'Atanasio Girardot'),
(37, 4, 'Bolívar'),
(38, 4, 'Camatagua'),
(39, 4, 'Francisco Linares Alcántara'),
(40, 4, 'José Ángel Lamas'),
(41, 4, 'José Félix Ribas'),
(42, 4, 'José Rafael Revenga'),
(43, 4, 'Libertador'),
(44, 4, 'Mario Briceño Iragorry'),
(45, 4, 'Ocumare de la Costa de Oro'),
(46, 4, 'San Casimiro'),
(47, 4, 'San Sebastián'),
(48, 4, 'Santiago Mariño'),
(49, 4, 'Santos Michelena'),
(50, 4, 'Sucre'),
(51, 4, 'Tovar'),
(52, 4, 'Urdaneta'),
(53, 4, 'Zamora'),
(54, 5, 'Alberto Arvelo Torrealba'),
(55, 5, 'Andrés Eloy Blanco'),
(56, 5, 'Antonio José de Sucre'),
(57, 5, 'Arismendi'),
(58, 5, 'Barinas'),
(59, 5, 'Bolívar'),
(60, 5, 'Cruz Paredes'),
(61, 5, 'Ezequiel Zamora'),
(62, 5, 'Obispos'),
(63, 5, 'Pedraza'),
(64, 5, 'Rojas'),
(65, 5, 'Sosa'),
(66, 6, 'Caroní'),
(67, 6, 'Cedeño'),
(68, 6, 'El Callao'),
(69, 6, 'Gran Sabana'),
(70, 6, 'Heres'),
(71, 6, 'Piar'),
(72, 6, 'Angostura (Raúl Leoni)'),
(73, 6, 'Roscio'),
(74, 6, 'Sifontes'),
(75, 6, 'Sucre'),
(76, 6, 'Padre Pedro Chien'),
(77, 7, 'Bejuma'),
(78, 7, 'Carlos Arvelo'),
(79, 7, 'Diego Ibarra'),
(80, 7, 'Guacara'),
(81, 7, 'Juan José Mora'),
(82, 7, 'Libertador'),
(83, 7, 'Los Guayos'),
(84, 7, 'Miranda'),
(85, 7, 'Montalbán'),
(86, 7, 'Naguanagua'),
(87, 7, 'Puerto Cabello'),
(88, 7, 'San Diego'),
(89, 7, 'San Joaquín'),
(90, 7, 'Valencia'),
(91, 8, 'Anzoátegui'),
(92, 8, 'Tinaquillo'),
(93, 8, 'Girardot'),
(94, 8, 'Lima Blanco'),
(95, 8, 'Pao de San Juan Bautista'),
(96, 8, 'Ricaurte'),
(97, 8, 'Rómulo Gallegos'),
(98, 8, 'San Carlos'),
(99, 8, 'Tinaco'),
(100, 9, 'Antonio Díaz'),
(101, 9, 'Casacoima'),
(102, 9, 'Pedernales'),
(103, 9, 'Tucupita'),
(104, 10, 'Acosta'),
(105, 10, 'Bolívar'),
(106, 10, 'Buchivacoa'),
(107, 10, 'Cacique Manaure'),
(108, 10, 'Carirubana'),
(109, 10, 'Colina'),
(110, 10, 'Dabajuro'),
(111, 10, 'Democracia'),
(112, 10, 'Falcón'),
(113, 10, 'Federación'),
(114, 10, 'Jacura'),
(115, 10, 'José Laurencio Silva'),
(116, 10, 'Los Taques'),
(117, 10, 'Mauroa'),
(118, 10, 'Miranda'),
(119, 10, 'Monseñor Iturriza'),
(120, 10, 'Palmasola'),
(121, 10, 'Petit'),
(122, 10, 'Píritu'),
(123, 10, 'San Francisco'),
(124, 10, 'Sucre'),
(125, 10, 'Tocópero'),
(126, 10, 'Unión'),
(127, 10, 'Urumaco'),
(128, 10, 'Zamora'),
(129, 11, 'Camaguán'),
(130, 11, 'Chaguaramas'),
(131, 11, 'El Socorro'),
(132, 11, 'José Félix Ribas'),
(133, 11, 'José Tadeo Monagas'),
(134, 11, 'Juan Germán Roscio'),
(135, 11, 'Julián Mellado'),
(136, 11, 'Las Mercedes'),
(137, 11, 'Leonardo Infante'),
(138, 11, 'Pedro Zaraza'),
(139, 11, 'Ortíz'),
(140, 11, 'San Gerónimo de Guayabal'),
(141, 11, 'San José de Guaribe'),
(142, 11, 'Santa María de Ipire'),
(143, 11, 'Sebastián Francisco de Miranda'),
(144, 12, 'Andrés Eloy Blanco'),
(145, 12, 'Crespo'),
(146, 12, 'Iribarren'),
(147, 12, 'Jiménez'),
(148, 12, 'Morán'),
(149, 12, 'Palavecino'),
(150, 12, 'Simón Planas'),
(151, 12, 'Torres'),
(152, 12, 'Urdaneta'),
(179, 13, 'Alberto Adriani'),
(180, 13, 'Andrés Bello'),
(181, 13, 'Antonio Pinto Salinas'),
(182, 13, 'Aricagua'),
(183, 13, 'Arzobispo Chacón'),
(184, 13, 'Campo Elías'),
(185, 13, 'Caracciolo Parra Olmedo'),
(186, 13, 'Cardenal Quintero'),
(187, 13, 'Guaraque'),
(188, 13, 'Julio César Salas'),
(189, 13, 'Justo Briceño'),
(190, 13, 'Libertador'),
(191, 13, 'Miranda'),
(192, 13, 'Obispo Ramos de Lora'),
(193, 13, 'Padre Noguera'),
(194, 13, 'Pueblo Llano'),
(195, 13, 'Rangel'),
(196, 13, 'Rivas Dávila'),
(197, 13, 'Santos Marquina'),
(198, 13, 'Sucre'),
(199, 13, 'Tovar'),
(200, 13, 'Tulio Febres Cordero'),
(201, 13, 'Zea'),
(223, 14, 'Acevedo'),
(224, 14, 'Andrés Bello'),
(225, 14, 'Baruta'),
(226, 14, 'Brión'),
(227, 14, 'Buroz'),
(228, 14, 'Carrizal'),
(229, 14, 'Chacao'),
(230, 14, 'Cristóbal Rojas'),
(231, 14, 'El Hatillo'),
(232, 14, 'Guaicaipuro'),
(233, 14, 'Independencia'),
(234, 14, 'Lander'),
(235, 14, 'Los Salias'),
(236, 14, 'Páez'),
(237, 14, 'Paz Castillo'),
(238, 14, 'Pedro Gual'),
(239, 14, 'Plaza'),
(240, 14, 'Simón Bolívar'),
(241, 14, 'Sucre'),
(242, 14, 'Urdaneta'),
(243, 14, 'Zamora'),
(258, 15, 'Acosta'),
(259, 15, 'Aguasay'),
(260, 15, 'Bolívar'),
(261, 15, 'Caripe'),
(262, 15, 'Cedeño'),
(263, 15, 'Ezequiel Zamora'),
(264, 15, 'Libertador'),
(265, 15, 'Maturín'),
(266, 15, 'Piar'),
(267, 15, 'Punceres'),
(268, 15, 'Santa Bárbara'),
(269, 15, 'Sotillo'),
(270, 15, 'Uracoa'),
(271, 16, 'Antolín del Campo'),
(272, 16, 'Arismendi'),
(273, 16, 'García'),
(274, 16, 'Gómez'),
(275, 16, 'Maneiro'),
(276, 16, 'Marcano'),
(277, 16, 'Mariño'),
(278, 16, 'Península de Macanao'),
(279, 16, 'Tubores'),
(280, 16, 'Villalba'),
(281, 16, 'Díaz'),
(282, 17, 'Agua Blanca'),
(283, 17, 'Araure'),
(284, 17, 'Esteller'),
(285, 17, 'Guanare'),
(286, 17, 'Guanarito'),
(287, 17, 'Monseñor José Vicente de Unda'),
(288, 17, 'Ospino'),
(289, 17, 'Páez'),
(290, 17, 'Papelón'),
(291, 17, 'San Genaro de Boconoíto'),
(292, 17, 'San Rafael de Onoto'),
(293, 17, 'Santa Rosalía'),
(294, 17, 'Sucre'),
(295, 17, 'Turén'),
(296, 18, 'Andrés Eloy Blanco'),
(297, 18, 'Andrés Mata'),
(298, 18, 'Arismendi'),
(299, 18, 'Benítez'),
(300, 18, 'Bermúdez'),
(301, 18, 'Bolívar'),
(302, 18, 'Cajigal'),
(303, 18, 'Cruz Salmerón Acosta'),
(304, 18, 'Libertador'),
(305, 18, 'Mariño'),
(306, 18, 'Mejía'),
(307, 18, 'Montes'),
(308, 18, 'Ribero'),
(309, 18, 'Sucre'),
(310, 18, 'Valdéz'),
(341, 19, 'Andrés Bello'),
(342, 19, 'Antonio Rómulo Costa'),
(343, 19, 'Ayacucho'),
(344, 19, 'Bolívar'),
(345, 19, 'Cárdenas'),
(346, 19, 'Córdoba'),
(347, 19, 'Fernández Feo'),
(348, 19, 'Francisco de Miranda'),
(349, 19, 'García de Hevia'),
(350, 19, 'Guásimos'),
(351, 19, 'Independencia'),
(352, 19, 'Jáuregui'),
(353, 19, 'José María Vargas'),
(354, 19, 'Junín'),
(355, 19, 'Libertad'),
(356, 19, 'Libertador'),
(357, 19, 'Lobatera'),
(358, 19, 'Michelena'),
(359, 19, 'Panamericano'),
(360, 19, 'Pedro María Ureña'),
(361, 19, 'Rafael Urdaneta'),
(362, 19, 'Samuel Darío Maldonado'),
(363, 19, 'San Cristóbal'),
(364, 19, 'Seboruco'),
(365, 19, 'Simón Rodríguez'),
(366, 19, 'Sucre'),
(367, 19, 'Torbes'),
(368, 19, 'Uribante'),
(369, 19, 'San Judas Tadeo'),
(370, 20, 'Andrés Bello'),
(371, 20, 'Boconó'),
(372, 20, 'Bolívar'),
(373, 20, 'Candelaria'),
(374, 20, 'Carache'),
(375, 20, 'Escuque'),
(376, 20, 'José Felipe Márquez Cañizalez'),
(377, 20, 'Juan Vicente Campos Elías'),
(378, 20, 'La Ceiba'),
(379, 20, 'Miranda'),
(380, 20, 'Monte Carmelo'),
(381, 20, 'Motatán'),
(382, 20, 'Pampán'),
(383, 20, 'Pampanito'),
(384, 20, 'Rafael Rangel'),
(385, 20, 'San Rafael de Carvajal'),
(386, 20, 'Sucre'),
(387, 20, 'Trujillo'),
(388, 20, 'Urdaneta'),
(389, 20, 'Valera'),
(390, 21, 'Vargas'),
(391, 22, 'Arístides Bastidas'),
(392, 22, 'Bolívar'),
(407, 22, 'Bruzual'),
(408, 22, 'Cocorote'),
(409, 22, 'Independencia'),
(410, 22, 'José Antonio Páez'),
(411, 22, 'La Trinidad'),
(412, 22, 'Manuel Monge'),
(413, 22, 'Nirgua'),
(414, 22, 'Peña'),
(415, 22, 'San Felipe'),
(416, 22, 'Sucre'),
(417, 22, 'Urachiche'),
(418, 22, 'José Joaquín Veroes'),
(441, 23, 'Almirante Padilla'),
(442, 23, 'Baralt'),
(443, 23, 'Cabimas'),
(444, 23, 'Catatumbo'),
(445, 23, 'Colón'),
(446, 23, 'Francisco Javier Pulgar'),
(447, 23, 'Páez'),
(448, 23, 'Jesús Enrique Losada'),
(449, 23, 'Jesús María Semprún'),
(450, 23, 'La Cañada de Urdaneta'),
(451, 23, 'Lagunillas'),
(452, 23, 'Machiques de Perijá'),
(453, 23, 'Mara'),
(454, 23, 'Maracaibo'),
(455, 23, 'Miranda'),
(456, 23, 'Rosario de Perijá'),
(457, 23, 'San Francisco'),
(458, 23, 'Santa Rita'),
(459, 23, 'Simón Bolívar'),
(460, 23, 'Sucre'),
(461, 23, 'Valmore Rodríguez'),
(462, 24, 'Libertador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_parroquias`
--

CREATE TABLE `tbl_parroquias` (
  `id` int(11) NOT NULL,
  `id_municipio` int(11) NOT NULL,
  `parroquia` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_parroquias`
--

INSERT INTO `tbl_parroquias` (`id`, `id_municipio`, `parroquia`) VALUES
(1, 1, 'Alto Orinoco'),
(2, 1, 'Huachamacare Acanaña'),
(3, 1, 'Marawaka Toky Shamanaña'),
(4, 1, 'Mavaka Mavaka'),
(5, 1, 'Sierra Parima Parimabé'),
(6, 2, 'Ucata Laja Lisa'),
(7, 2, 'Yapacana Macuruco'),
(8, 2, 'Caname Guarinuma'),
(9, 3, 'Fernando Girón Tovar'),
(10, 3, 'Luis Alberto Gómez'),
(11, 3, 'Pahueña Limón de Parhueña'),
(12, 3, 'Platanillal Platanillal'),
(13, 4, 'Samariapo'),
(14, 4, 'Sipapo'),
(15, 4, 'Munduapo'),
(16, 4, 'Guayapo'),
(17, 5, 'Alto Ventuari'),
(18, 5, 'Medio Ventuari'),
(19, 5, 'Bajo Ventuari'),
(20, 6, 'Victorino'),
(21, 6, 'Comunidad'),
(22, 7, 'Casiquiare'),
(23, 7, 'Cocuy'),
(24, 7, 'San Carlos de Río Negro'),
(25, 7, 'Solano'),
(26, 8, 'Anaco'),
(27, 8, 'San Joaquín'),
(28, 9, 'Cachipo'),
(29, 9, 'Aragua de Barcelona'),
(30, 11, 'Lechería'),
(31, 11, 'El Morro'),
(32, 12, 'Puerto Píritu'),
(33, 12, 'San Miguel'),
(34, 12, 'Sucre'),
(35, 13, 'Valle de Guanape'),
(36, 13, 'Santa Bárbara'),
(37, 14, 'El Chaparro'),
(38, 14, 'Tomás Alfaro'),
(39, 14, 'Calatrava'),
(40, 15, 'Guanta'),
(41, 15, 'Chorrerón'),
(42, 16, 'Mamo'),
(43, 16, 'Soledad'),
(44, 17, 'Mapire'),
(45, 17, 'Piar'),
(46, 17, 'Santa Clara'),
(47, 17, 'San Diego de Cabrutica'),
(48, 17, 'Uverito'),
(49, 17, 'Zuata'),
(50, 18, 'Puerto La Cruz'),
(51, 18, 'Pozuelos'),
(52, 19, 'Onoto'),
(53, 19, 'San Pablo'),
(54, 20, 'San Mateo'),
(55, 20, 'El Carito'),
(56, 20, 'Santa Inés'),
(57, 20, 'La Romereña'),
(58, 21, 'Atapirire'),
(59, 21, 'Boca del Pao'),
(60, 21, 'El Pao'),
(61, 21, 'Pariaguán'),
(62, 22, 'Cantaura'),
(63, 22, 'Libertador'),
(64, 22, 'Santa Rosa'),
(65, 22, 'Urica'),
(66, 23, 'Píritu'),
(67, 23, 'San Francisco'),
(68, 24, 'San José de Guanipa'),
(69, 25, 'Boca de Uchire'),
(70, 25, 'Boca de Chávez'),
(71, 26, 'Pueblo Nuevo'),
(72, 26, 'Santa Ana'),
(73, 27, 'Bergantín'),
(74, 27, 'Caigua'),
(75, 27, 'El Carmen'),
(76, 27, 'El Pilar'),
(77, 27, 'Naricual'),
(78, 27, 'San Crsitóbal'),
(79, 28, 'Edmundo Barrios'),
(80, 28, 'Miguel Otero Silva'),
(81, 29, 'Achaguas'),
(82, 29, 'Apurito'),
(83, 29, 'El Yagual'),
(84, 29, 'Guachara'),
(85, 29, 'Mucuritas'),
(86, 29, 'Queseras del medio'),
(87, 30, 'Biruaca'),
(88, 31, 'Bruzual'),
(89, 31, 'Mantecal'),
(90, 31, 'Quintero'),
(91, 31, 'Rincón Hondo'),
(92, 31, 'San Vicente'),
(93, 32, 'Guasdualito'),
(94, 32, 'Aramendi'),
(95, 32, 'El Amparo'),
(96, 32, 'San Camilo'),
(97, 32, 'Urdaneta'),
(98, 33, 'San Juan de Payara'),
(99, 33, 'Codazzi'),
(100, 33, 'Cunaviche'),
(101, 34, 'Elorza'),
(102, 34, 'La Trinidad'),
(103, 35, 'San Fernando'),
(104, 35, 'El Recreo'),
(105, 35, 'Peñalver'),
(106, 35, 'San Rafael de Atamaica'),
(107, 36, 'Pedro José Ovalles'),
(108, 36, 'Joaquín Crespo'),
(109, 36, 'José Casanova Godoy'),
(110, 36, 'Madre María de San José'),
(111, 36, 'Andrés Eloy Blanco'),
(112, 36, 'Los Tacarigua'),
(113, 36, 'Las Delicias'),
(114, 36, 'Choroní'),
(115, 37, 'Bolívar'),
(116, 38, 'Camatagua'),
(117, 38, 'Carmen de Cura'),
(118, 39, 'Santa Rita'),
(119, 39, 'Francisco de Miranda'),
(120, 39, 'Moseñor Feliciano González'),
(121, 40, 'Santa Cruz'),
(122, 41, 'José Félix Ribas'),
(123, 41, 'Castor Nieves Ríos'),
(124, 41, 'Las Guacamayas'),
(125, 41, 'Pao de Zárate'),
(126, 41, 'Zuata'),
(127, 42, 'José Rafael Revenga'),
(128, 43, 'Palo Negro'),
(129, 43, 'San Martín de Porres'),
(130, 44, 'El Limón'),
(131, 44, 'Caña de Azúcar'),
(132, 45, 'Ocumare de la Costa'),
(133, 46, 'San Casimiro'),
(134, 46, 'Güiripa'),
(135, 46, 'Ollas de Caramacate'),
(136, 46, 'Valle Morín'),
(137, 47, 'San Sebastían'),
(138, 48, 'Turmero'),
(139, 48, 'Arevalo Aponte'),
(140, 48, 'Chuao'),
(141, 48, 'Samán de Güere'),
(142, 48, 'Alfredo Pacheco Miranda'),
(143, 49, 'Santos Michelena'),
(144, 49, 'Tiara'),
(145, 50, 'Cagua'),
(146, 50, 'Bella Vista'),
(147, 51, 'Tovar'),
(148, 52, 'Urdaneta'),
(149, 52, 'Las Peñitas'),
(150, 52, 'San Francisco de Cara'),
(151, 52, 'Taguay'),
(152, 53, 'Zamora'),
(153, 53, 'Magdaleno'),
(154, 53, 'San Francisco de Asís'),
(155, 53, 'Valles de Tucutunemo'),
(156, 53, 'Augusto Mijares'),
(157, 54, 'Sabaneta'),
(158, 54, 'Juan Antonio Rodríguez Domínguez'),
(159, 55, 'El Cantón'),
(160, 55, 'Santa Cruz de Guacas'),
(161, 55, 'Puerto Vivas'),
(162, 56, 'Ticoporo'),
(163, 56, 'Nicolás Pulido'),
(164, 56, 'Andrés Bello'),
(165, 57, 'Arismendi'),
(166, 57, 'Guadarrama'),
(167, 57, 'La Unión'),
(168, 57, 'San Antonio'),
(169, 58, 'Barinas'),
(170, 58, 'Alberto Arvelo Larriva'),
(171, 58, 'San Silvestre'),
(172, 58, 'Santa Inés'),
(173, 58, 'Santa Lucía'),
(174, 58, 'Torumos'),
(175, 58, 'El Carmen'),
(176, 58, 'Rómulo Betancourt'),
(177, 58, 'Corazón de Jesús'),
(178, 58, 'Ramón Ignacio Méndez'),
(179, 58, 'Alto Barinas'),
(180, 58, 'Manuel Palacio Fajardo'),
(181, 58, 'Juan Antonio Rodríguez Domínguez'),
(182, 58, 'Dominga Ortiz de Páez'),
(183, 59, 'Barinitas'),
(184, 59, 'Altamira de Cáceres'),
(185, 59, 'Calderas'),
(186, 60, 'Barrancas'),
(187, 60, 'El Socorro'),
(188, 60, 'Mazparrito'),
(189, 61, 'Santa Bárbara'),
(190, 61, 'Pedro Briceño Méndez'),
(191, 61, 'Ramón Ignacio Méndez'),
(192, 61, 'José Ignacio del Pumar'),
(193, 62, 'Obispos'),
(194, 62, 'Guasimitos'),
(195, 62, 'El Real'),
(196, 62, 'La Luz'),
(197, 63, 'Ciudad Bolívia'),
(198, 63, 'José Ignacio Briceño'),
(199, 63, 'José Félix Ribas'),
(200, 63, 'Páez'),
(201, 64, 'Libertad'),
(202, 64, 'Dolores'),
(203, 64, 'Santa Rosa'),
(204, 64, 'Palacio Fajardo'),
(205, 65, 'Ciudad de Nutrias'),
(206, 65, 'El Regalo'),
(207, 65, 'Puerto Nutrias'),
(208, 65, 'Santa Catalina'),
(209, 66, 'Cachamay'),
(210, 66, 'Chirica'),
(211, 66, 'Dalla Costa'),
(212, 66, 'Once de Abril'),
(213, 66, 'Simón Bolívar'),
(214, 66, 'Unare'),
(215, 66, 'Universidad'),
(216, 66, 'Vista al Sol'),
(217, 66, 'Pozo Verde'),
(218, 66, 'Yocoima'),
(219, 66, '5 de Julio'),
(220, 67, 'Cedeño'),
(221, 67, 'Altagracia'),
(222, 67, 'Ascensión Farreras'),
(223, 67, 'Guaniamo'),
(224, 67, 'La Urbana'),
(225, 67, 'Pijiguaos'),
(226, 68, 'El Callao'),
(227, 69, 'Gran Sabana'),
(228, 69, 'Ikabarú'),
(229, 70, 'Catedral'),
(230, 70, 'Zea'),
(231, 70, 'Orinoco'),
(232, 70, 'José Antonio Páez'),
(233, 70, 'Marhuanta'),
(234, 70, 'Agua Salada'),
(235, 70, 'Vista Hermosa'),
(236, 70, 'La Sabanita'),
(237, 70, 'Panapana'),
(238, 71, 'Andrés Eloy Blanco'),
(239, 71, 'Pedro Cova'),
(240, 72, 'Raúl Leoni'),
(241, 72, 'Barceloneta'),
(242, 72, 'Santa Bárbara'),
(243, 72, 'San Francisco'),
(244, 73, 'Roscio'),
(245, 73, 'Salóm'),
(246, 74, 'Sifontes'),
(247, 74, 'Dalla Costa'),
(248, 74, 'San Isidro'),
(249, 75, 'Sucre'),
(250, 75, 'Aripao'),
(251, 75, 'Guarataro'),
(252, 75, 'Las Majadas'),
(253, 75, 'Moitaco'),
(254, 76, 'Padre Pedro Chien'),
(255, 76, 'Río Grande'),
(256, 77, 'Bejuma'),
(257, 77, 'Canoabo'),
(258, 77, 'Simón Bolívar'),
(259, 78, 'Güigüe'),
(260, 78, 'Carabobo'),
(261, 78, 'Tacarigua'),
(262, 79, 'Mariara'),
(263, 79, 'Aguas Calientes'),
(264, 80, 'Ciudad Alianza'),
(265, 80, 'Guacara'),
(266, 80, 'Yagua'),
(267, 81, 'Morón'),
(268, 81, 'Yagua'),
(269, 82, 'Tocuyito'),
(270, 82, 'Independencia'),
(271, 83, 'Los Guayos'),
(272, 84, 'Miranda'),
(273, 85, 'Montalbán'),
(274, 86, 'Naguanagua'),
(275, 87, 'Bartolomé Salóm'),
(276, 87, 'Democracia'),
(277, 87, 'Fraternidad'),
(278, 87, 'Goaigoaza'),
(279, 87, 'Juan José Flores'),
(280, 87, 'Unión'),
(281, 87, 'Borburata'),
(282, 87, 'Patanemo'),
(283, 88, 'San Diego'),
(284, 89, 'San Joaquín'),
(285, 90, 'Candelaria'),
(286, 90, 'Catedral'),
(287, 90, 'El Socorro'),
(288, 90, 'Miguel Peña'),
(289, 90, 'Rafael Urdaneta'),
(290, 90, 'San Blas'),
(291, 90, 'San José'),
(292, 90, 'Santa Rosa'),
(293, 90, 'Negro Primero'),
(294, 91, 'Cojedes'),
(295, 91, 'Juan de Mata Suárez'),
(296, 92, 'Tinaquillo'),
(297, 93, 'El Baúl'),
(298, 93, 'Sucre'),
(299, 94, 'La Aguadita'),
(300, 94, 'Macapo'),
(301, 95, 'El Pao'),
(302, 96, 'El Amparo'),
(303, 96, 'Libertad de Cojedes'),
(304, 97, 'Rómulo Gallegos'),
(305, 98, 'San Carlos de Austria'),
(306, 98, 'Juan Ángel Bravo'),
(307, 98, 'Manuel Manrique'),
(308, 99, 'General en Jefe José Laurencio Silva'),
(309, 100, 'Curiapo'),
(310, 100, 'Almirante Luis Brión'),
(311, 100, 'Francisco Aniceto Lugo'),
(312, 100, 'Manuel Renaud'),
(313, 100, 'Padre Barral'),
(314, 100, 'Santos de Abelgas'),
(315, 101, 'Imataca'),
(316, 101, 'Cinco de Julio'),
(317, 101, 'Juan Bautista Arismendi'),
(318, 101, 'Manuel Piar'),
(319, 101, 'Rómulo Gallegos'),
(320, 102, 'Pedernales'),
(321, 102, 'Luis Beltrán Prieto Figueroa'),
(322, 103, 'San José (Delta Amacuro)'),
(323, 103, 'José Vidal Marcano'),
(324, 103, 'Juan Millán'),
(325, 103, 'Leonardo Ruíz Pineda'),
(326, 103, 'Mariscal Antonio José de Sucre'),
(327, 103, 'Monseñor Argimiro García'),
(328, 103, 'San Rafael (Delta Amacuro)'),
(329, 103, 'Virgen del Valle'),
(330, 10, 'Clarines'),
(331, 10, 'Guanape'),
(332, 10, 'Sabana de Uchire'),
(333, 104, 'Capadare'),
(334, 104, 'La Pastora'),
(335, 104, 'Libertador'),
(336, 104, 'San Juan de los Cayos'),
(337, 105, 'Aracua'),
(338, 105, 'La Peña'),
(339, 105, 'San Luis'),
(340, 106, 'Bariro'),
(341, 106, 'Borojó'),
(342, 106, 'Capatárida'),
(343, 106, 'Guajiro'),
(344, 106, 'Seque'),
(345, 106, 'Zazárida'),
(346, 106, 'Valle de Eroa'),
(347, 107, 'Cacique Manaure'),
(348, 108, 'Norte'),
(349, 108, 'Carirubana'),
(350, 108, 'Santa Ana'),
(351, 108, 'Urbana Punta Cardón'),
(352, 109, 'La Vela de Coro'),
(353, 109, 'Acurigua'),
(354, 109, 'Guaibacoa'),
(355, 109, 'Las Calderas'),
(356, 109, 'Macoruca'),
(357, 110, 'Dabajuro'),
(358, 111, 'Agua Clara'),
(359, 111, 'Avaria'),
(360, 111, 'Pedregal'),
(361, 111, 'Piedra Grande'),
(362, 111, 'Purureche'),
(363, 112, 'Adaure'),
(364, 112, 'Adícora'),
(365, 112, 'Baraived'),
(366, 112, 'Buena Vista'),
(367, 112, 'Jadacaquiva'),
(368, 112, 'El Vínculo'),
(369, 112, 'El Hato'),
(370, 112, 'Moruy'),
(371, 112, 'Pueblo Nuevo'),
(372, 113, 'Agua Larga'),
(373, 113, 'El Paují'),
(374, 113, 'Independencia'),
(375, 113, 'Mapararí'),
(376, 114, 'Agua Linda'),
(377, 114, 'Araurima'),
(378, 114, 'Jacura'),
(379, 115, 'Tucacas'),
(380, 115, 'Boca de Aroa'),
(381, 116, 'Los Taques'),
(382, 116, 'Judibana'),
(383, 117, 'Mene de Mauroa'),
(384, 117, 'San Félix'),
(385, 117, 'Casigua'),
(386, 118, 'Guzmán Guillermo'),
(387, 118, 'Mitare'),
(388, 118, 'Río Seco'),
(389, 118, 'Sabaneta'),
(390, 118, 'San Antonio'),
(391, 118, 'San Gabriel'),
(392, 118, 'Santa Ana'),
(393, 119, 'Boca del Tocuyo'),
(394, 119, 'Chichiriviche'),
(395, 119, 'Tocuyo de la Costa'),
(396, 120, 'Palmasola'),
(397, 121, 'Cabure'),
(398, 121, 'Colina'),
(399, 121, 'Curimagua'),
(400, 122, 'San José de la Costa'),
(401, 122, 'Píritu'),
(402, 123, 'San Francisco'),
(403, 124, 'Sucre'),
(404, 124, 'Pecaya'),
(405, 125, 'Tocópero'),
(406, 126, 'El Charal'),
(407, 126, 'Las Vegas del Tuy'),
(408, 126, 'Santa Cruz de Bucaral'),
(409, 127, 'Bruzual'),
(410, 127, 'Urumaco'),
(411, 128, 'Puerto Cumarebo'),
(412, 128, 'La Ciénaga'),
(413, 128, 'La Soledad'),
(414, 128, 'Pueblo Cumarebo'),
(415, 128, 'Zazárida'),
(416, 113, 'Churuguara'),
(417, 129, 'Camaguán'),
(418, 129, 'Puerto Miranda'),
(419, 129, 'Uverito'),
(420, 130, 'Chaguaramas'),
(421, 131, 'El Socorro'),
(422, 132, 'Tucupido'),
(423, 132, 'San Rafael de Laya'),
(424, 133, 'Altagracia de Orituco'),
(425, 133, 'San Rafael de Orituco'),
(426, 133, 'San Francisco Javier de Lezama'),
(427, 133, 'Paso Real de Macaira'),
(428, 133, 'Carlos Soublette'),
(429, 133, 'San Francisco de Macaira'),
(430, 133, 'Libertad de Orituco'),
(431, 134, 'Cantaclaro'),
(432, 134, 'San Juan de los Morros'),
(433, 134, 'Parapara'),
(434, 135, 'El Sombrero'),
(435, 135, 'Sosa'),
(436, 136, 'Las Mercedes'),
(437, 136, 'Cabruta'),
(438, 136, 'Santa Rita de Manapire'),
(439, 137, 'Valle de la Pascua'),
(440, 137, 'Espino'),
(441, 138, 'San José de Unare'),
(442, 138, 'Zaraza'),
(443, 139, 'San José de Tiznados'),
(444, 139, 'San Francisco de Tiznados'),
(445, 139, 'San Lorenzo de Tiznados'),
(446, 139, 'Ortiz'),
(447, 140, 'Guayabal'),
(448, 140, 'Cazorla'),
(449, 141, 'San José de Guaribe'),
(450, 141, 'Uveral'),
(451, 142, 'Santa María de Ipire'),
(452, 142, 'Altamira'),
(453, 143, 'El Calvario'),
(454, 143, 'El Rastro'),
(455, 143, 'Guardatinajas'),
(456, 143, 'Capital Urbana Calabozo'),
(457, 144, 'Quebrada Honda de Guache'),
(458, 144, 'Pío Tamayo'),
(459, 144, 'Yacambú'),
(460, 145, 'Fréitez'),
(461, 145, 'José María Blanco'),
(462, 146, 'Catedral'),
(463, 146, 'Concepción'),
(464, 146, 'El Cují'),
(465, 146, 'Juan de Villegas'),
(466, 146, 'Santa Rosa'),
(467, 146, 'Tamaca'),
(468, 146, 'Unión'),
(469, 146, 'Aguedo Felipe Alvarado'),
(470, 146, 'Buena Vista'),
(471, 146, 'Juárez'),
(472, 147, 'Juan Bautista Rodríguez'),
(473, 147, 'Cuara'),
(474, 147, 'Diego de Lozada'),
(475, 147, 'Paraíso de San José'),
(476, 147, 'San Miguel'),
(477, 147, 'Tintorero'),
(478, 147, 'José Bernardo Dorante'),
(479, 147, 'Coronel Mariano Peraza '),
(480, 148, 'Bolívar'),
(481, 148, 'Anzoátegui'),
(482, 148, 'Guarico'),
(483, 148, 'Hilario Luna y Luna'),
(484, 148, 'Humocaro Alto'),
(485, 148, 'Humocaro Bajo'),
(486, 148, 'La Candelaria'),
(487, 148, 'Morán'),
(488, 149, 'Cabudare'),
(489, 149, 'José Gregorio Bastidas'),
(490, 149, 'Agua Viva'),
(491, 150, 'Sarare'),
(492, 150, 'Buría'),
(493, 150, 'Gustavo Vegas León'),
(494, 151, 'Trinidad Samuel'),
(495, 151, 'Antonio Díaz'),
(496, 151, 'Camacaro'),
(497, 151, 'Castañeda'),
(498, 151, 'Cecilio Zubillaga'),
(499, 151, 'Chiquinquirá'),
(500, 151, 'El Blanco'),
(501, 151, 'Espinoza de los Monteros'),
(502, 151, 'Lara'),
(503, 151, 'Las Mercedes'),
(504, 151, 'Manuel Morillo'),
(505, 151, 'Montaña Verde'),
(506, 151, 'Montes de Oca'),
(507, 151, 'Torres'),
(508, 151, 'Heriberto Arroyo'),
(509, 151, 'Reyes Vargas'),
(510, 151, 'Altagracia'),
(511, 152, 'Siquisique'),
(512, 152, 'Moroturo'),
(513, 152, 'San Miguel'),
(514, 152, 'Xaguas'),
(515, 179, 'Presidente Betancourt'),
(516, 179, 'Presidente Páez'),
(517, 179, 'Presidente Rómulo Gallegos'),
(518, 179, 'Gabriel Picón González'),
(519, 179, 'Héctor Amable Mora'),
(520, 179, 'José Nucete Sardi'),
(521, 179, 'Pulido Méndez'),
(522, 180, 'La Azulita'),
(523, 181, 'Santa Cruz de Mora'),
(524, 181, 'Mesa Bolívar'),
(525, 181, 'Mesa de Las Palmas'),
(526, 182, 'Aricagua'),
(527, 182, 'San Antonio'),
(528, 183, 'Canagua'),
(529, 183, 'Capurí'),
(530, 183, 'Chacantá'),
(531, 183, 'El Molino'),
(532, 183, 'Guaimaral'),
(533, 183, 'Mucutuy'),
(534, 183, 'Mucuchachí'),
(535, 184, 'Fernández Peña'),
(536, 184, 'Matriz'),
(537, 184, 'Montalbán'),
(538, 184, 'Acequias'),
(539, 184, 'Jají'),
(540, 184, 'La Mesa'),
(541, 184, 'San José del Sur'),
(542, 185, 'Tucaní'),
(543, 185, 'Florencio Ramírez'),
(544, 186, 'Santo Domingo'),
(545, 186, 'Las Piedras'),
(546, 187, 'Guaraque'),
(547, 187, 'Mesa de Quintero'),
(548, 187, 'Río Negro'),
(549, 188, 'Arapuey'),
(550, 188, 'Palmira'),
(551, 189, 'San Cristóbal de Torondoy'),
(552, 189, 'Torondoy'),
(553, 190, 'Antonio Spinetti Dini'),
(554, 190, 'Arias'),
(555, 190, 'Caracciolo Parra Pérez'),
(556, 190, 'Domingo Peña'),
(557, 190, 'El Llano'),
(558, 190, 'Gonzalo Picón Febres'),
(559, 190, 'Jacinto Plaza'),
(560, 190, 'Juan Rodríguez Suárez'),
(561, 190, 'Lasso de la Vega'),
(562, 190, 'Mariano Picón Salas'),
(563, 190, 'Milla'),
(564, 190, 'Osuna Rodríguez'),
(565, 190, 'Sagrario'),
(566, 190, 'El Morro'),
(567, 190, 'Los Nevados'),
(568, 191, 'Andrés Eloy Blanco'),
(569, 191, 'La Venta'),
(570, 191, 'Piñango'),
(571, 191, 'Timotes'),
(572, 192, 'Eloy Paredes'),
(573, 192, 'San Rafael de Alcázar'),
(574, 192, 'Santa Elena de Arenales'),
(575, 193, 'Santa María de Caparo'),
(576, 194, 'Pueblo Llano'),
(577, 195, 'Cacute'),
(578, 195, 'La Toma'),
(579, 195, 'Mucuchíes'),
(580, 195, 'Mucurubá'),
(581, 195, 'San Rafael'),
(582, 196, 'Gerónimo Maldonado'),
(583, 196, 'Bailadores'),
(584, 197, 'Tabay'),
(585, 198, 'Chiguará'),
(586, 198, 'Estánquez'),
(587, 198, 'Lagunillas'),
(588, 198, 'La Trampa'),
(589, 198, 'Pueblo Nuevo del Sur'),
(590, 198, 'San Juan'),
(591, 199, 'El Amparo'),
(592, 199, 'El Llano'),
(593, 199, 'San Francisco'),
(594, 199, 'Tovar'),
(595, 200, 'Independencia'),
(596, 200, 'María de la Concepción Palacios Blanco'),
(597, 200, 'Nueva Bolivia'),
(598, 200, 'Santa Apolonia'),
(599, 201, 'Caño El Tigre'),
(600, 201, 'Zea'),
(601, 223, 'Aragüita'),
(602, 223, 'Arévalo González'),
(603, 223, 'Capaya'),
(604, 223, 'Caucagua'),
(605, 223, 'Panaquire'),
(606, 223, 'Ribas'),
(607, 223, 'El Café'),
(608, 223, 'Marizapa'),
(609, 224, 'Cumbo'),
(610, 224, 'San José de Barlovento'),
(611, 225, 'El Cafetal'),
(612, 225, 'Las Minas'),
(613, 225, 'Nuestra Señora del Rosario'),
(614, 226, 'Higuerote'),
(615, 226, 'Curiepe'),
(616, 226, 'Tacarigua de Brión'),
(617, 227, 'Mamporal'),
(618, 228, 'Carrizal'),
(619, 229, 'Chacao'),
(620, 230, 'Charallave'),
(621, 230, 'Las Brisas'),
(622, 231, 'El Hatillo'),
(623, 232, 'Altagracia de la Montaña'),
(624, 232, 'Cecilio Acosta'),
(625, 232, 'Los Teques'),
(626, 232, 'El Jarillo'),
(627, 232, 'San Pedro'),
(628, 232, 'Tácata'),
(629, 232, 'Paracotos'),
(630, 233, 'Cartanal'),
(631, 233, 'Santa Teresa del Tuy'),
(632, 234, 'La Democracia'),
(633, 234, 'Ocumare del Tuy'),
(634, 234, 'Santa Bárbara'),
(635, 235, 'San Antonio de los Altos'),
(636, 236, 'Río Chico'),
(637, 236, 'El Guapo'),
(638, 236, 'Tacarigua de la Laguna'),
(639, 236, 'Paparo'),
(640, 236, 'San Fernando del Guapo'),
(641, 237, 'Santa Lucía del Tuy'),
(642, 238, 'Cúpira'),
(643, 238, 'Machurucuto'),
(644, 239, 'Guarenas'),
(645, 240, 'San Antonio de Yare'),
(646, 240, 'San Francisco de Yare'),
(647, 241, 'Leoncio Martínez'),
(648, 241, 'Petare'),
(649, 241, 'Caucagüita'),
(650, 241, 'Filas de Mariche'),
(651, 241, 'La Dolorita'),
(652, 242, 'Cúa'),
(653, 242, 'Nueva Cúa'),
(654, 243, 'Guatire'),
(655, 243, 'Bolívar'),
(656, 258, 'San Antonio de Maturín'),
(657, 258, 'San Francisco de Maturín'),
(658, 259, 'Aguasay'),
(659, 260, 'Caripito'),
(660, 261, 'El Guácharo'),
(661, 261, 'La Guanota'),
(662, 261, 'Sabana de Piedra'),
(663, 261, 'San Agustín'),
(664, 261, 'Teresen'),
(665, 261, 'Caripe'),
(666, 262, 'Areo'),
(667, 262, 'Capital Cedeño'),
(668, 262, 'San Félix de Cantalicio'),
(669, 262, 'Viento Fresco'),
(670, 263, 'El Tejero'),
(671, 263, 'Punta de Mata'),
(672, 264, 'Chaguaramas'),
(673, 264, 'Las Alhuacas'),
(674, 264, 'Tabasca'),
(675, 264, 'Temblador'),
(676, 265, 'Alto de los Godos'),
(677, 265, 'Boquerón'),
(678, 265, 'Las Cocuizas'),
(679, 265, 'La Cruz'),
(680, 265, 'San Simón'),
(681, 265, 'El Corozo'),
(682, 265, 'El Furrial'),
(683, 265, 'Jusepín'),
(684, 265, 'La Pica'),
(685, 265, 'San Vicente'),
(686, 266, 'Aparicio'),
(687, 266, 'Aragua de Maturín'),
(688, 266, 'Chaguamal'),
(689, 266, 'El Pinto'),
(690, 266, 'Guanaguana'),
(691, 266, 'La Toscana'),
(692, 266, 'Taguaya'),
(693, 267, 'Cachipo'),
(694, 267, 'Quiriquire'),
(695, 268, 'Santa Bárbara'),
(696, 269, 'Barrancas'),
(697, 269, 'Los Barrancos de Fajardo'),
(698, 270, 'Uracoa'),
(699, 271, 'Antolín del Campo'),
(700, 272, 'Arismendi'),
(701, 273, 'García'),
(702, 273, 'Francisco Fajardo'),
(703, 274, 'Bolívar'),
(704, 274, 'Guevara'),
(705, 274, 'Matasiete'),
(706, 274, 'Santa Ana'),
(707, 274, 'Sucre'),
(708, 275, 'Aguirre'),
(709, 275, 'Maneiro'),
(710, 276, 'Adrián'),
(711, 276, 'Juan Griego'),
(712, 276, 'Yaguaraparo'),
(713, 277, 'Porlamar'),
(714, 278, 'San Francisco de Macanao'),
(715, 278, 'Boca de Río'),
(716, 279, 'Tubores'),
(717, 279, 'Los Baleales'),
(718, 280, 'Vicente Fuentes'),
(719, 280, 'Villalba'),
(720, 281, 'San Juan Bautista'),
(721, 281, 'Zabala'),
(722, 283, 'Capital Araure'),
(723, 283, 'Río Acarigua'),
(724, 284, 'Capital Esteller'),
(725, 284, 'Uveral'),
(726, 285, 'Guanare'),
(727, 285, 'Córdoba'),
(728, 285, 'San José de la Montaña'),
(729, 285, 'San Juan de Guanaguanare'),
(730, 285, 'Virgen de la Coromoto'),
(731, 286, 'Guanarito'),
(732, 286, 'Trinidad de la Capilla'),
(733, 286, 'Divina Pastora'),
(734, 287, 'Monseñor José Vicente de Unda'),
(735, 287, 'Peña Blanca'),
(736, 288, 'Capital Ospino'),
(737, 288, 'Aparición'),
(738, 288, 'La Estación'),
(739, 289, 'Páez'),
(740, 289, 'Payara'),
(741, 289, 'Pimpinela'),
(742, 289, 'Ramón Peraza'),
(743, 290, 'Papelón'),
(744, 290, 'Caño Delgadito'),
(745, 291, 'San Genaro de Boconoito'),
(746, 291, 'Antolín Tovar'),
(747, 292, 'San Rafael de Onoto'),
(748, 292, 'Santa Fe'),
(749, 292, 'Thermo Morles'),
(750, 293, 'Santa Rosalía'),
(751, 293, 'Florida'),
(752, 294, 'Sucre'),
(753, 294, 'Concepción'),
(754, 294, 'San Rafael de Palo Alzado'),
(755, 294, 'Uvencio Antonio Velásquez'),
(756, 294, 'San José de Saguaz'),
(757, 294, 'Villa Rosa'),
(758, 295, 'Turén'),
(759, 295, 'Canelones'),
(760, 295, 'Santa Cruz'),
(761, 295, 'San Isidro Labrador'),
(762, 296, 'Mariño'),
(763, 296, 'Rómulo Gallegos'),
(764, 297, 'San José de Aerocuar'),
(765, 297, 'Tavera Acosta'),
(766, 298, 'Río Caribe'),
(767, 298, 'Antonio José de Sucre'),
(768, 298, 'El Morro de Puerto Santo'),
(769, 298, 'Puerto Santo'),
(770, 298, 'San Juan de las Galdonas'),
(771, 299, 'El Pilar'),
(772, 299, 'El Rincón'),
(773, 299, 'General Francisco Antonio Váquez'),
(774, 299, 'Guaraúnos'),
(775, 299, 'Tunapuicito'),
(776, 299, 'Unión'),
(777, 300, 'Santa Catalina'),
(778, 300, 'Santa Rosa'),
(779, 300, 'Santa Teresa'),
(780, 300, 'Bolívar'),
(781, 300, 'Maracapana'),
(782, 302, 'Libertad'),
(783, 302, 'El Paujil'),
(784, 302, 'Yaguaraparo'),
(785, 303, 'Cruz Salmerón Acosta'),
(786, 303, 'Chacopata'),
(787, 303, 'Manicuare'),
(788, 304, 'Tunapuy'),
(789, 304, 'Campo Elías'),
(790, 305, 'Irapa'),
(791, 305, 'Campo Claro'),
(792, 305, 'Maraval'),
(793, 305, 'San Antonio de Irapa'),
(794, 305, 'Soro'),
(795, 306, 'Mejía'),
(796, 307, 'Cumanacoa'),
(797, 307, 'Arenas'),
(798, 307, 'Aricagua'),
(799, 307, 'Cogollar'),
(800, 307, 'San Fernando'),
(801, 307, 'San Lorenzo'),
(802, 308, 'Villa Frontado (Muelle de Cariaco)'),
(803, 308, 'Catuaro'),
(804, 308, 'Rendón'),
(805, 308, 'San Cruz'),
(806, 308, 'Santa María'),
(807, 309, 'Altagracia'),
(808, 309, 'Santa Inés'),
(809, 309, 'Valentín Valiente'),
(810, 309, 'Ayacucho'),
(811, 309, 'San Juan'),
(812, 309, 'Raúl Leoni'),
(813, 309, 'Gran Mariscal'),
(814, 310, 'Cristóbal Colón'),
(815, 310, 'Bideau'),
(816, 310, 'Punta de Piedras'),
(817, 310, 'Güiria'),
(818, 341, 'Andrés Bello'),
(819, 342, 'Antonio Rómulo Costa'),
(820, 343, 'Ayacucho'),
(821, 343, 'Rivas Berti'),
(822, 343, 'San Pedro del Río'),
(823, 344, 'Bolívar'),
(824, 344, 'Palotal'),
(825, 344, 'General Juan Vicente Gómez'),
(826, 344, 'Isaías Medina Angarita'),
(827, 345, 'Cárdenas'),
(828, 345, 'Amenodoro Ángel Lamus'),
(829, 345, 'La Florida'),
(830, 346, 'Córdoba'),
(831, 347, 'Fernández Feo'),
(832, 347, 'Alberto Adriani'),
(833, 347, 'Santo Domingo'),
(834, 348, 'Francisco de Miranda'),
(835, 349, 'García de Hevia'),
(836, 349, 'Boca de Grita'),
(837, 349, 'José Antonio Páez'),
(838, 350, 'Guásimos'),
(839, 351, 'Independencia'),
(840, 351, 'Juan Germán Roscio'),
(841, 351, 'Román Cárdenas'),
(842, 352, 'Jáuregui'),
(843, 352, 'Emilio Constantino Guerrero'),
(844, 352, 'Monseñor Miguel Antonio Salas'),
(845, 353, 'José María Vargas'),
(846, 354, 'Junín'),
(847, 354, 'La Petrólea'),
(848, 354, 'Quinimarí'),
(849, 354, 'Bramón'),
(850, 355, 'Libertad'),
(851, 355, 'Cipriano Castro'),
(852, 355, 'Manuel Felipe Rugeles'),
(853, 356, 'Libertador'),
(854, 356, 'Doradas'),
(855, 356, 'Emeterio Ochoa'),
(856, 356, 'San Joaquín de Navay'),
(857, 357, 'Lobatera'),
(858, 357, 'Constitución'),
(859, 358, 'Michelena'),
(860, 359, 'Panamericano'),
(861, 359, 'La Palmita'),
(862, 360, 'Pedro María Ureña'),
(863, 360, 'Nueva Arcadia'),
(864, 361, 'Delicias'),
(865, 361, 'Pecaya'),
(866, 362, 'Samuel Darío Maldonado'),
(867, 362, 'Boconó'),
(868, 362, 'Hernández'),
(869, 363, 'La Concordia'),
(870, 363, 'San Juan Bautista'),
(871, 363, 'Pedro María Morantes'),
(872, 363, 'San Sebastián'),
(873, 363, 'Dr. Francisco Romero Lobo'),
(874, 364, 'Seboruco'),
(875, 365, 'Simón Rodríguez'),
(876, 366, 'Sucre'),
(877, 366, 'Eleazar López Contreras'),
(878, 366, 'San Pablo'),
(879, 367, 'Torbes'),
(880, 368, 'Uribante'),
(881, 368, 'Cárdenas'),
(882, 368, 'Juan Pablo Peñalosa'),
(883, 368, 'Potosí'),
(884, 369, 'San Judas Tadeo'),
(885, 370, 'Araguaney'),
(886, 370, 'El Jaguito'),
(887, 370, 'La Esperanza'),
(888, 370, 'Santa Isabel'),
(889, 371, 'Boconó'),
(890, 371, 'El Carmen'),
(891, 371, 'Mosquey'),
(892, 371, 'Ayacucho'),
(893, 371, 'Burbusay'),
(894, 371, 'General Ribas'),
(895, 371, 'Guaramacal'),
(896, 371, 'Vega de Guaramacal'),
(897, 371, 'Monseñor Jáuregui'),
(898, 371, 'Rafael Rangel'),
(899, 371, 'San Miguel'),
(900, 371, 'San José'),
(901, 372, 'Sabana Grande'),
(902, 372, 'Cheregüé'),
(903, 372, 'Granados'),
(904, 373, 'Arnoldo Gabaldón'),
(905, 373, 'Bolivia'),
(906, 373, 'Carrillo'),
(907, 373, 'Cegarra'),
(908, 373, 'Chejendé'),
(909, 373, 'Manuel Salvador Ulloa'),
(910, 373, 'San José'),
(911, 374, 'Carache'),
(912, 374, 'La Concepción'),
(913, 374, 'Cuicas'),
(914, 374, 'Panamericana'),
(915, 374, 'Santa Cruz'),
(916, 375, 'Escuque'),
(917, 375, 'La Unión'),
(918, 375, 'Santa Rita'),
(919, 375, 'Sabana Libre'),
(920, 376, 'El Socorro'),
(921, 376, 'Los Caprichos'),
(922, 376, 'Antonio José de Sucre'),
(923, 377, 'Campo Elías'),
(924, 377, 'Arnoldo Gabaldón'),
(925, 378, 'Santa Apolonia'),
(926, 378, 'El Progreso'),
(927, 378, 'La Ceiba'),
(928, 378, 'Tres de Febrero'),
(929, 379, 'El Dividive'),
(930, 379, 'Agua Santa'),
(931, 379, 'Agua Caliente'),
(932, 379, 'El Cenizo'),
(933, 379, 'Valerita'),
(934, 380, 'Monte Carmelo'),
(935, 380, 'Buena Vista'),
(936, 380, 'Santa María del Horcón'),
(937, 381, 'Motatán'),
(938, 381, 'El Baño'),
(939, 381, 'Jalisco'),
(940, 382, 'Pampán'),
(941, 382, 'Flor de Patria'),
(942, 382, 'La Paz'),
(943, 382, 'Santa Ana'),
(944, 383, 'Pampanito'),
(945, 383, 'La Concepción'),
(946, 383, 'Pampanito II'),
(947, 384, 'Betijoque'),
(948, 384, 'José Gregorio Hernández'),
(949, 384, 'La Pueblita'),
(950, 384, 'Los Cedros'),
(951, 385, 'Carvajal'),
(952, 385, 'Campo Alegre'),
(953, 385, 'Antonio Nicolás Briceño'),
(954, 385, 'José Leonardo Suárez'),
(955, 386, 'Sabana de Mendoza'),
(956, 386, 'Junín'),
(957, 386, 'Valmore Rodríguez'),
(958, 386, 'El Paraíso'),
(959, 387, 'Andrés Linares'),
(960, 387, 'Chiquinquirá'),
(961, 387, 'Cristóbal Mendoza'),
(962, 387, 'Cruz Carrillo'),
(963, 387, 'Matriz'),
(964, 387, 'Monseñor Carrillo'),
(965, 387, 'Tres Esquinas'),
(966, 388, 'Cabimbú'),
(967, 388, 'Jajó'),
(968, 388, 'La Mesa de Esnujaque'),
(969, 388, 'Santiago'),
(970, 388, 'Tuñame'),
(971, 388, 'La Quebrada'),
(972, 389, 'Juan Ignacio Montilla'),
(973, 389, 'La Beatriz'),
(974, 389, 'La Puerta'),
(975, 389, 'Mendoza del Valle de Momboy'),
(976, 389, 'Mercedes Díaz'),
(977, 389, 'San Luis'),
(978, 390, 'Caraballeda'),
(979, 390, 'Carayaca'),
(980, 390, 'Carlos Soublette'),
(981, 390, 'Caruao Chuspa'),
(982, 390, 'Catia La Mar'),
(983, 390, 'El Junko'),
(984, 390, 'La Guaira'),
(985, 390, 'Macuto'),
(986, 390, 'Maiquetía'),
(987, 390, 'Naiguatá'),
(988, 390, 'Urimare'),
(989, 391, 'Arístides Bastidas'),
(990, 392, 'Bolívar'),
(991, 407, 'Chivacoa'),
(992, 407, 'Campo Elías'),
(993, 408, 'Cocorote'),
(994, 409, 'Independencia'),
(995, 410, 'José Antonio Páez'),
(996, 411, 'La Trinidad'),
(997, 412, 'Manuel Monge'),
(998, 413, 'Salóm'),
(999, 413, 'Temerla'),
(1000, 413, 'Nirgua'),
(1001, 414, 'San Andrés'),
(1002, 414, 'Yaritagua'),
(1003, 415, 'San Javier'),
(1004, 415, 'Albarico'),
(1005, 415, 'San Felipe'),
(1006, 416, 'Sucre'),
(1007, 417, 'Urachiche'),
(1008, 418, 'El Guayabo'),
(1009, 418, 'Farriar'),
(1010, 441, 'Isla de Toas'),
(1011, 441, 'Monagas'),
(1012, 442, 'San Timoteo'),
(1013, 442, 'General Urdaneta'),
(1014, 442, 'Libertador'),
(1015, 442, 'Marcelino Briceño'),
(1016, 442, 'Pueblo Nuevo'),
(1017, 442, 'Manuel Guanipa Matos'),
(1018, 443, 'Ambrosio'),
(1019, 443, 'Carmen Herrera'),
(1020, 443, 'La Rosa'),
(1021, 443, 'Germán Ríos Linares'),
(1022, 443, 'San Benito'),
(1023, 443, 'Rómulo Betancourt'),
(1024, 443, 'Jorge Hernández'),
(1025, 443, 'Punta Gorda'),
(1026, 443, 'Arístides Calvani'),
(1027, 444, 'Encontrados'),
(1028, 444, 'Udón Pérez'),
(1029, 445, 'Moralito'),
(1030, 445, 'San Carlos del Zulia'),
(1031, 445, 'Santa Cruz del Zulia'),
(1032, 445, 'Santa Bárbara'),
(1033, 445, 'Urribarrí'),
(1034, 446, 'Carlos Quevedo'),
(1035, 446, 'Francisco Javier Pulgar'),
(1036, 446, 'Simón Rodríguez'),
(1037, 446, 'Guamo-Gavilanes'),
(1038, 448, 'La Concepción'),
(1039, 448, 'San José'),
(1040, 448, 'Mariano Parra León'),
(1041, 448, 'José Ramón Yépez'),
(1042, 449, 'Jesús María Semprún'),
(1043, 449, 'Barí'),
(1044, 450, 'Concepción'),
(1045, 450, 'Andrés Bello'),
(1046, 450, 'Chiquinquirá'),
(1047, 450, 'El Carmelo'),
(1048, 450, 'Potreritos'),
(1049, 451, 'Libertad'),
(1050, 451, 'Alonso de Ojeda'),
(1051, 451, 'Venezuela'),
(1052, 451, 'Eleazar López Contreras'),
(1053, 451, 'Campo Lara'),
(1054, 452, 'Bartolomé de las Casas'),
(1055, 452, 'Libertad'),
(1056, 452, 'Río Negro'),
(1057, 452, 'San José de Perijá'),
(1058, 453, 'San Rafael'),
(1059, 453, 'La Sierrita'),
(1060, 453, 'Las Parcelas'),
(1061, 453, 'Luis de Vicente'),
(1062, 453, 'Monseñor Marcos Sergio Godoy'),
(1063, 453, 'Ricaurte'),
(1064, 453, 'Tamare'),
(1065, 454, 'Antonio Borjas Romero'),
(1066, 454, 'Bolívar'),
(1067, 454, 'Cacique Mara'),
(1068, 454, 'Carracciolo Parra Pérez'),
(1069, 454, 'Cecilio Acosta'),
(1070, 454, 'Cristo de Aranza'),
(1071, 454, 'Coquivacoa'),
(1072, 454, 'Chiquinquirá'),
(1073, 454, 'Francisco Eugenio Bustamante'),
(1074, 454, 'Idelfonzo Vásquez'),
(1075, 454, 'Juana de Ávila'),
(1076, 454, 'Luis Hurtado Higuera'),
(1077, 454, 'Manuel Dagnino'),
(1078, 454, 'Olegario Villalobos'),
(1079, 454, 'Raúl Leoni'),
(1080, 454, 'Santa Lucía'),
(1081, 454, 'Venancio Pulgar'),
(1082, 454, 'San Isidro'),
(1083, 455, 'Altagracia'),
(1084, 455, 'Faría'),
(1085, 455, 'Ana María Campos'),
(1086, 455, 'San Antonio'),
(1087, 455, 'San José'),
(1088, 456, 'Donaldo García'),
(1089, 456, 'El Rosario'),
(1090, 456, 'Sixto Zambrano'),
(1091, 457, 'San Francisco'),
(1092, 457, 'El Bajo'),
(1093, 457, 'Domitila Flores'),
(1094, 457, 'Francisco Ochoa'),
(1095, 457, 'Los Cortijos'),
(1096, 457, 'Marcial Hernández'),
(1097, 458, 'Santa Rita'),
(1098, 458, 'El Mene'),
(1099, 458, 'Pedro Lucas Urribarrí'),
(1100, 458, 'José Cenobio Urribarrí'),
(1101, 459, 'Rafael Maria Baralt'),
(1102, 459, 'Manuel Manrique'),
(1103, 459, 'Rafael Urdaneta'),
(1104, 460, 'Bobures'),
(1105, 460, 'Gibraltar'),
(1106, 460, 'Heras'),
(1107, 460, 'Monseñor Arturo Álvarez'),
(1108, 460, 'Rómulo Gallegos'),
(1109, 460, 'El Batey'),
(1110, 461, 'Rafael Urdaneta'),
(1111, 461, 'La Victoria'),
(1112, 461, 'Raúl Cuenca'),
(1113, 447, 'Sinamaica'),
(1114, 447, 'Alta Guajira'),
(1115, 447, 'Elías Sánchez Rubio'),
(1116, 447, 'Guajira'),
(1117, 462, 'Altagracia'),
(1118, 462, 'Antímano'),
(1119, 462, 'Caricuao'),
(1120, 462, 'Catedral'),
(1121, 462, 'Coche'),
(1122, 462, 'El Junquito'),
(1123, 462, 'El Paraíso'),
(1124, 462, 'El Recreo'),
(1125, 462, 'El Valle'),
(1126, 462, 'La Candelaria'),
(1127, 462, 'La Pastora'),
(1128, 462, 'La Vega'),
(1129, 462, 'Macarao'),
(1130, 462, 'San Agustín'),
(1131, 462, 'San Bernardino'),
(1132, 462, 'San José'),
(1133, 462, 'San Juan'),
(1134, 462, 'San Pedro'),
(1135, 462, 'Santa Rosalía'),
(1136, 462, 'Santa Teresa'),
(1137, 462, 'Sucre (Catia)'),
(1138, 462, '23 de enero');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_proyecto`
--

CREATE TABLE `tbl_proyecto` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_contratacion` varchar(10) NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `id_moneda` int(11) NOT NULL,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Disparadores `tbl_proyecto`
--
DELIMITER $$
CREATE TRIGGER `tbl_proyecto_AI` AFTER INSERT ON `tbl_proyecto` FOR EACH ROW INSERT INTO logs.tbl_proyecto(id_proyecto, descripcion_nuevo, id_cliente_nuevo, fecha_contratacion_nuevo, monto_nuevo, id_moneda_nuevo,  id_estatus_nuevo) VALUES (NEW.id, NEW.descripcion, NEW.id_cliente, NEW.fecha_contratacion, NEW.monto, NEW.id_moneda, NEW.id_estatus)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tbl_proyecto_BU` BEFORE UPDATE ON `tbl_proyecto` FOR EACH ROW INSERT INTO logs.tbl_proyecto(id_proyecto, descripcion, id_cliente, fecha_contratacion, monto, id_moneda, id_estatus, descripcion_nuevo, id_cliente_nuevo, fecha_contratacion_nuevo, monto_nuevo, id_moneda_nuevo, id_estatus_nuevo) VALUES (NEW.id, OLD.descripcion, OLD.id_cliente, OLD.fecha_contratacion, OLD.monto, OLD.id_moneda, OLD.id_estatus, NEW.descripcion, NEW.id_cliente, NEW.fecha_contratacion, NEW.monto, NEW.id_moneda, NEW.id_estatus)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_proyecto_analista`
--

CREATE TABLE `tbl_proyecto_analista` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `id_proyecto_division` int(11) DEFAULT NULL,
  `id_analista` int(11) NOT NULL,
  `horas_asignadas` int(11) DEFAULT NULL,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `tbl_proyecto_analista`
--
DELIMITER $$
CREATE TRIGGER `tbl_proyecto_analista_AI` AFTER INSERT ON `tbl_proyecto_analista` FOR EACH ROW INSERT INTO logs.tbl_proyecto_analista(id_proyecto_analista, id_proyecto_nuevo, id_proyecto_division_nuevo, id_analista_nuevo, horas_asignadas_nuevo, id_estatus_nuevo) VALUES (NEW.id, NEW.id_proyecto, NEW.id_proyecto_division, NEW.id_analista, NEW.horas_asignadas, NEW.id_estatus)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tbl_proyecto_analista_BU` BEFORE UPDATE ON `tbl_proyecto_analista` FOR EACH ROW INSERT INTO logs.tbl_proyecto_analista(id_proyecto_analista, id_proyecto, id_proyecto_division, id_analista, horas_asignadas, id_estatus, id_proyecto_nuevo, id_proyecto_division_nuevo, id_analista_nuevo, horas_asignadas_nuevo, id_estatus_nuevo) VALUES (NEW.id, OLD.id_proyecto, OLD.id_proyecto_division, OLD.id_analista, OLD.horas_asignadas, OLD.id_estatus, NEW.id_proyecto, NEW.id_proyecto_division, NEW.id_analista, NEW.horas_asignadas, NEW.id_estatus)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_proyecto_divisiones`
--

CREATE TABLE `tbl_proyecto_divisiones` (
  `id` int(11) NOT NULL,
  `id_division` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `horas_contratadas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_tipo_cargo`
--

CREATE TABLE `tbl_tipo_cargo` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbl_tipo_cargo`
--

INSERT INTO `tbl_tipo_cargo` (`id`, `descripcion`) VALUES
(1, 'Profesional'),
(2, 'Administrativo'),
(3, 'Profesional y Administrativo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_tipo_contacto`
--

CREATE TABLE `tbl_tipo_contacto` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(30) NOT NULL,
  `estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_tipo_contacto`
--

INSERT INTO `tbl_tipo_contacto` (`id`, `descripcion`, `estatus`) VALUES
(1, 'Contacto en el Cliente', 1),
(2, 'Quien Recibe las Facturas', 1),
(3, 'Referidor del Cliente', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_tipo_documento_identidad`
--

CREATE TABLE `tbl_tipo_documento_identidad` (
  `id` int(11) NOT NULL,
  `abreviatura` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbl_tipo_documento_identidad`
--

INSERT INTO `tbl_tipo_documento_identidad` (`id`, `abreviatura`, `descripcion`, `id_estatus`) VALUES
(1, 'V', 'Cédula Venezolana', 1),
(2, 'E', 'Cédula Extranjera', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_usuario`
--

CREATE TABLE `tbl_usuario` (
  `id` int(11) NOT NULL,
  `codigo` varchar(6) NOT NULL,
  `clave` text NOT NULL,
  `nombre_1` varchar(20) NOT NULL,
  `nombre_2` varchar(20) DEFAULT NULL,
  `apellido_1` varchar(20) NOT NULL,
  `apellido_2` varchar(20) DEFAULT NULL,
  `fecha_nacimiento` datetime DEFAULT NULL COMMENT 'El formato de la fecha es UTC',
  `id_cargo` int(11) DEFAULT NULL,
  `id_division` int(11) DEFAULT NULL,
  `id_parroquia` int(11) DEFAULT NULL,
  `avatar` varchar(30) DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT NULL COMMENT 'El formato de la fecha es UTC',
  `fecha_egreso` datetime DEFAULT NULL COMMENT 'El formato de la fecha es UTC',
  `id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tbl_usuario`
--

INSERT INTO `tbl_usuario` (`id`, `codigo`, `clave`, `nombre_1`, `nombre_2`, `apellido_1`, `apellido_2`, `fecha_nacimiento`, `id_cargo`, `id_division`, `id_parroquia`, `avatar`, `fecha_ingreso`, `fecha_egreso`, `id_estatus`) VALUES
(1, '0001', 'eyJpdiI6IjB5dnJXUUswTEdZenNcLzRHbTRWXC9HUT09IiwidmFsdWUiOiJwbUpkdURFdGhoc3FOSFpGQU1yaU5RPT0iLCJtYWMiOiI2ZDY1NTVlMTBkYmQ4NGNiNWM0MWRkMTllMjcxZjkxOTM5MmFhZmMxYTIwNmFiMzM4MjRmYTgwYjEwYTQ0NTY0In0=', 'DAVID', 'LEONARDO', 'MOLINA', 'RUÍZ', NULL, 11, 3, 1131, '', NULL, NULL, 1),
(2, '10', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'NATHALIE', 'YAMILET', 'LOPEZ', 'TREJO', '1972-08-20 00:00:00', 15, 1, 1131, NULL, '2000-02-21 00:00:00', NULL, 1),
(3, '10092', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YESENIA', 'BEATRIZ', 'MARTINEZ', 'GALLARDO', '1979-06-01 00:00:00', 14, 1, 1131, NULL, '2004-09-01 00:00:00', NULL, 1),
(4, '10141', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JESUS', 'ERASMO', 'PEREZ', 'ERASMO', '1959-11-09 00:00:00', 17, 1, 1131, NULL, '2005-02-02 00:00:00', NULL, 1),
(5, '10168', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'CAROL', 'JOSEFINA', 'LOPEZ', 'CAMPOS', '1962-11-07 00:00:00', 15, 1, 1131, NULL, '2005-06-06 00:00:00', NULL, 1),
(6, '10367', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'LUZ', 'AMANDA', 'FONSECA', 'GARCIA', '1985-01-13 00:00:00', 14, 1, 1131, NULL, '2007-10-29 00:00:00', NULL, 1),
(7, '10473', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ARTURO', 'LORENZO', 'MADRIZ', 'VARGAS', '1954-12-16 00:00:00', 17, 1, 1131, NULL, '2008-10-14 00:00:00', NULL, 1),
(8, '10509', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ROMAN', 'ALBERTO', 'SCOTT', '', '1975-07-16 00:00:00', 12, 1, 1131, NULL, '2009-05-06 00:00:00', NULL, 1),
(9, '10572', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'OLIVER', 'JOSE', 'PAEZ', 'RANGEL', '1982-10-16 00:00:00', 14, 1, 1131, NULL, '2010-01-18 00:00:00', NULL, 1),
(10, '10721', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JORGE', 'ALEJANDRO', 'GONZALEZ', 'MORALES', '1990-05-19 00:00:00', 12, 1, 1131, NULL, '2011-11-15 00:00:00', NULL, 1),
(11, '10786', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'MARIA', 'ANDREINA', 'SEQUEDA', 'BANDES', '1990-05-30 00:00:00', 12, 1, 1131, NULL, '2012-07-20 00:00:00', NULL, 1),
(12, '10968', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YODELINA', '', 'TORRES', 'MORALES', '1994-09-15 00:00:00', 11, 1, 1131, NULL, '2014-02-24 00:00:00', NULL, 1),
(13, '11030', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'KATHERINE', 'BETHZABEL', 'ZURITA', 'CHACON', '1989-06-08 00:00:00', 12, 1, 1131, NULL, '2015-01-13 00:00:00', NULL, 1),
(14, '11044', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'MILEIDIS', 'ALEXANDRA', 'MORENO', 'MATUZALEM', '1992-05-30 00:00:00', 9, 1, 1131, NULL, '2015-01-21 00:00:00', NULL, 1),
(15, '11116', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'FRANCIA', 'CAROLINA', 'MEDINA', 'TINEDO', '1987-03-15 00:00:00', 9, 1, 1131, NULL, '2015-11-04 00:00:00', NULL, 1),
(16, '11220', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ASTRID', 'CAROLINA', 'MENDOZA', 'GIL', '1992-07-31 00:00:00', 8, 1, 1131, NULL, '2016-04-25 00:00:00', NULL, 1),
(17, '11314', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'MARIA', 'GABRIELA', 'TOVAR', 'CARDENAS', '1991-11-28 00:00:00', 8, 1, 1131, NULL, '2017-03-27 00:00:00', NULL, 1),
(18, '11352', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'MARIANA', 'ALEXANDRA', 'BRITO', 'SIFONTES', '1995-02-19 00:00:00', 7, 1, 1131, NULL, '2017-11-13 00:00:00', NULL, 1),
(19, '11354', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'BELKIS', 'KATERIN', 'CORTINA', 'RUIZ', '1996-12-08 00:00:00', 6, 1, 1131, NULL, '2017-11-13 00:00:00', NULL, 1),
(20, '11364', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'LUCRECIA', 'DISNORA', 'SILVA', 'APONTE', '1989-03-15 00:00:00', 7, 1, 1131, NULL, '2017-12-04 00:00:00', NULL, 1),
(21, '11369', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'NORMEDY', 'ZORIBETH', 'PARRA', 'TOVAR', '1986-08-22 00:00:00', 6, 1, 1131, NULL, '2017-12-04 00:00:00', NULL, 1),
(22, '11371', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JOSVELIS', 'YETSIMAR', 'CASTILLO', 'GIL', '1997-07-14 00:00:00', 7, 1, 1131, NULL, '2017-12-04 00:00:00', NULL, 1),
(23, '11391', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'LUIS', 'ANTONIO', 'RUSSIAN', 'REQUENA', '1996-01-10 00:00:00', 8, 1, 1131, NULL, '2018-02-15 00:00:00', NULL, 1),
(24, '11401', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JONATHAN', 'JOSE', 'AZOCAR', 'RODRIGUEZ', '1994-08-24 00:00:00', 6, 1, 1131, NULL, '2018-02-26 00:00:00', NULL, 1),
(25, '11403', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YERLENIS', 'DELYET', 'VALDERRAMA', 'ROSALES', '1998-09-14 00:00:00', 7, 1, 1131, NULL, '2018-03-06 00:00:00', NULL, 1),
(26, '11410', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'KLEIVER', 'JOHANA', 'CORRO', 'GUDIÑO', '1991-02-13 00:00:00', 6, 1, 1131, NULL, '2018-03-06 00:00:00', NULL, 1),
(27, '11421', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'MARYURI', 'NAILET', 'BARAZARTE', 'VALERA', '1979-09-13 00:00:00', 7, 1, 1131, NULL, '2018-03-26 00:00:00', NULL, 1),
(28, '11437', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'PEDRO', 'ALEXANDER', 'BENITEZ', 'MELENDEZ', '1968-06-05 00:00:00', 15, 1, 1131, NULL, '2018-07-01 00:00:00', NULL, 1),
(29, '11440', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'DENNYS', 'RAMON', 'FLORES', 'MORALES', '1981-04-20 00:00:00', 5, 1, 1131, NULL, '2018-07-17 00:00:00', NULL, 1),
(30, '11446', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'GENESIS', 'VANESSA', 'MARCANO', 'RANGEL', '1997-10-05 00:00:00', 5, 1, 1131, NULL, '2018-07-25 00:00:00', NULL, 1),
(31, '11448', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'KEILIMAR', 'YULISET', 'SUAREZ', 'LARES', '1996-05-12 00:00:00', 5, 1, 1131, NULL, '2018-07-31 00:00:00', NULL, 1),
(32, '11452', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JOHANNE', 'FRANCIS', 'MUÑOZ', 'MARTINEZ', '1981-07-22 00:00:00', 12, 1, 1131, NULL, '2018-08-15 00:00:00', NULL, 1),
(33, '11453', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ALFREDO', 'JOSE', 'HERNANDEZ', 'TORO', '1969-03-14 00:00:00', 7, 1, 1131, NULL, '2018-08-14 00:00:00', NULL, 1),
(34, '11457', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'RAUL', 'IGNACIO', 'VARGAS', 'FREITES', '1976-01-29 00:00:00', 15, 1, 1131, NULL, '2018-10-18 00:00:00', NULL, 1),
(35, '11466', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'SHELCIE', 'ESTHER', 'PAZ', '', '1997-03-22 00:00:00', 5, 1, 1131, NULL, '2018-11-08 00:00:00', NULL, 1),
(36, '11467', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'LADYMAR', '', 'MORETT', 'RONDON', '1983-03-18 00:00:00', 12, 1, 1131, NULL, '2018-11-20 00:00:00', NULL, 1),
(37, '11469', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ANTHONY', 'ROBERT', 'GARCIA', 'CHAPARRO', '1991-06-26 00:00:00', 7, 1, 1131, NULL, '2018-11-12 00:00:00', NULL, 1),
(38, '11480', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'SOLMARY', 'DEL VALLE', 'MARTINEZ', 'MARCHAN', '1983-08-03 00:00:00', 12, 1, 1131, NULL, '2018-12-17 00:00:00', NULL, 1),
(39, '11481', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JACKELINE', 'ZULEYMA MILAGROS', 'RAMOS', 'PEÑA', '1989-06-02 00:00:00', 4, 1, 1131, NULL, '2018-12-18 00:00:00', NULL, 1),
(40, '11484', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'BELKIS', 'EDICTA', 'VAZQUEZ', 'MORALES', '1984-07-17 00:00:00', 6, 1, 1131, NULL, '2019-01-07 00:00:00', NULL, 1),
(41, '11487', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YUZLEIBBY', 'ANGELICA', 'MALDONADO', 'ROSALES', '1996-10-08 00:00:00', 4, 1, 1131, NULL, '2019-01-21 00:00:00', NULL, 1),
(42, '11490', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'GIOVANNI', 'JESUS', 'CORREDOR', 'SANOJA', '1996-07-07 00:00:00', 5, 1, 1131, NULL, '2019-01-24 00:00:00', NULL, 1),
(43, '11493', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'KLEIVER', 'JOSE', 'CADENAS', 'QUIÑONEZ', '1995-05-02 00:00:00', 4, 1, 1131, NULL, '2019-02-04 00:00:00', NULL, 1),
(44, '11494', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'IVETTE', 'ALEJANDRA', 'OROZCO', 'FLORES', '1994-02-23 00:00:00', 12, 1, 1131, NULL, '2019-02-04 00:00:00', NULL, 1),
(45, '11497', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ZUNAYA', 'ESTHER', 'WILCHES', 'OLAVE', '1996-12-05 00:00:00', 4, 1, 1131, NULL, '2019-02-07 00:00:00', NULL, 1),
(46, '11499', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JESUS', 'ALBERTO', 'ABRAHAM', 'CORONADO', '1994-06-21 00:00:00', 6, 1, 1131, NULL, '2019-02-21 00:00:00', NULL, 1),
(47, '11503', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JOSE', 'MIGUEL', 'PEROZO', 'HERRERA', '1994-10-04 00:00:00', 8, 1, 1131, NULL, '2019-03-07 00:00:00', NULL, 1),
(48, '11504', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ROBERTO', 'RAFAEL', 'VILLEGAS', 'GONZALEZ', '1988-09-26 00:00:00', 7, 1, 1131, NULL, '2019-03-20 00:00:00', NULL, 1),
(49, '11507', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'SANDRO', 'YOEL', 'MAYORA', '', '1973-09-17 00:00:00', 10, 1, 1131, NULL, '2019-04-01 00:00:00', NULL, 1),
(50, '11519', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'EDUARDO', '', 'BASTOS', 'RICCIO', '1989-06-27 00:00:00', 3, 1, 1131, NULL, '2019-07-10 00:00:00', NULL, 1),
(51, '11520', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'VANESSA', 'VALENTINA', 'ROJAS', 'MORALES', '1987-12-23 00:00:00', 3, 1, 1131, NULL, '2019-07-16 00:00:00', NULL, 1),
(52, '11527', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'CARLOS', 'ALBERTO', 'REVETE', 'CARVALLO', '1994-09-18 00:00:00', 4, 1, 1131, NULL, '2019-12-09 00:00:00', NULL, 1),
(53, '11528', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'VIANNEY', 'DEL VALLE', 'RUGELES', 'MANTILLA', '1972-01-08 00:00:00', 4, 1, 1131, NULL, '2019-12-09 00:00:00', NULL, 1),
(54, '11529', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'EDWIN', 'JESUS', 'BURGOS', 'GOMEZ', '1987-12-06 00:00:00', 4, 1, 1131, NULL, '2019-12-09 00:00:00', NULL, 1),
(55, '11535', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ENIL', 'ALEJANDRO', 'MOLINA', 'YDROGO', '2002-02-16 00:00:00', 2, 1, 1131, NULL, '2020-03-09 00:00:00', NULL, 1),
(56, '22', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'FREDDY', 'RODOLFO', 'VARGAS', 'HERNANDEZ', '1969-10-22 00:00:00', 15, 1, 1131, NULL, '2000-08-01 00:00:00', NULL, 1),
(57, '6060', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YORMAN', 'ISMAEL', 'RANGEL', 'GONZALEZ', '1983-08-15 00:00:00', 14, 1, 1131, NULL, '2014-07-01 00:00:00', NULL, 1),
(58, '10783', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JOSE', 'MIGUEL', 'UTRERA', 'ROJAS', '1975-04-02 00:00:00', 17, 2, 1131, NULL, '2012-07-16 00:00:00', NULL, 1),
(59, '11485', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ALEJANDRO', 'ENRIQUE', 'LIRA', 'TOVAR', '1995-06-27 00:00:00', 6, 2, 1131, NULL, '2019-01-09 00:00:00', NULL, 1),
(60, '11505', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YORDALIS', 'GABRIELA', 'ECHARRYS', 'CABRILES', '1993-08-02 00:00:00', 4, 2, 1131, NULL, '2019-04-01 00:00:00', NULL, 1),
(61, '11506', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ELIANA', 'MARIA', 'PONCE', 'VARGAS', '1971-03-14 00:00:00', 14, 2, 1131, NULL, '2019-04-08 00:00:00', NULL, 1),
(62, '11514', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'STEFANY', 'YANETH', 'GONZALEZ', 'MIJARES', '1995-02-22 00:00:00', 4, 2, 1131, NULL, '2019-06-03 00:00:00', NULL, 1),
(63, '11521', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'NAIVELYS', 'GABRIELA', 'ALTUVE', 'TORRES', '1991-06-20 00:00:00', 12, 2, 1131, NULL, '2019-09-02 00:00:00', NULL, 1),
(64, '11522', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'GABRIELA', 'DEL VALLE', 'GIL', 'LA PIETRA', '1996-05-09 00:00:00', 3, 2, 1131, NULL, '2019-09-02 00:00:00', NULL, 1),
(65, '11526', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ORIANNA', 'DESSIREE', 'ALEJOS', 'FIGUEREDO', '1996-05-23 00:00:00', 3, 2, 1131, NULL, '2019-11-18 00:00:00', NULL, 1),
(66, '11533', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'MARYNES', 'DEL VALLE', 'GONZALEZ', 'MENDOZA', '1997-03-06 00:00:00', 3, 2, 1131, NULL, '2020-03-09 00:00:00', NULL, 1),
(67, '10794', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ELIGIO', 'HORACIO', 'MENDOZA', 'ODREMAN', '1970-10-23 00:00:00', 15, 4, 1131, NULL, '2012-08-01 00:00:00', NULL, 1),
(68, '10838', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'MARIELVI', '', 'OLLER', 'MENDOZA', '1986-07-11 00:00:00', 12, 4, 1131, NULL, '2013-01-23 00:00:00', NULL, 1),
(69, '111426', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ALBA', 'JEANNETH', 'NAVIA', 'BERMUDEZ', '1976-07-22 00:00:00', 12, 4, 1131, NULL, '2018-05-01 00:00:00', NULL, 1),
(70, '11344', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'NATHASHA', 'ESTEFANIA', 'FRANCO', 'BERMUDEZ', '1996-02-03 00:00:00', 9, 4, 1131, NULL, '2017-10-13 00:00:00', NULL, 1),
(71, '11353', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YESSICA', 'LAURA', 'RIVAS', 'TURMERO', '1990-11-26 00:00:00', 10, 4, 1131, NULL, '2017-11-13 00:00:00', NULL, 1),
(72, '11366', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'FRAYNER', 'ALEXANDER', 'RANGEL', 'VALERO', '1993-04-17 00:00:00', 8, 4, 1131, NULL, '2017-12-04 00:00:00', NULL, 1),
(73, '11374', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YDA', 'MERCEDES', 'CHIRINOS', 'VILORIA', '1983-09-28 00:00:00', 9, 4, 1131, NULL, '2017-12-04 00:00:00', NULL, 1),
(74, '11411', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'GENESIS', 'GABRIELA', 'BARRIOS', 'VILORIA', '1998-07-25 00:00:00', 9, 4, 1131, NULL, '2018-03-06 00:00:00', NULL, 1),
(75, '11458', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'RUDDY', 'ISAMAR', 'PINTO', 'COLMENARES', '1990-05-06 00:00:00', 8, 4, 1131, NULL, '2018-10-16 00:00:00', NULL, 1),
(76, '11459', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'CARLOS', 'EDUARDO', 'RODRIGUEZ', '', '1966-03-15 00:00:00', 9, 4, 1131, NULL, '2018-10-16 00:00:00', NULL, 1),
(77, '11471', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'CARMEN', 'ELENA', 'BERRIOS', 'BASTIDAS', '1989-07-16 00:00:00', 8, 4, 1131, NULL, '2018-11-15 00:00:00', NULL, 1),
(78, '11472', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'GERALDINE', 'DESIREE', 'RUIZ', 'HENRIQUEZ', '1975-10-09 00:00:00', 11, 4, 1131, NULL, '2018-11-26 00:00:00', NULL, 1),
(79, '11482', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'NAHOMY', 'NAZARETH', 'QUINTERO', 'MARTINEZ', '1998-08-13 00:00:00', 7, 4, 1131, NULL, '2018-12-17 00:00:00', NULL, 1),
(80, '11510', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'MARIA', 'ISABEL', 'ESPINA', 'URBINA', '1966-12-09 00:00:00', 9, 4, 1131, NULL, '2019-04-29 00:00:00', NULL, 1),
(81, '11513', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ANGELO', 'ALFONSO', 'MARTINEZ', 'BERROTERAN', '1990-02-05 00:00:00', 6, 4, 1131, NULL, '2019-06-03 00:00:00', NULL, 1),
(82, '11523', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'MANUEL', 'ALEJANDRO', 'DA SILVA', 'VILLAMISIL', '1984-12-04 00:00:00', 8, 4, 1131, NULL, '2019-10-01 00:00:00', NULL, 1),
(83, '111431', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'GLENDER', 'JESUS', 'CORTEZ', '', '1990-11-05 00:00:00', 9, 5, 1131, NULL, '2018-06-25 00:00:00', NULL, 1),
(84, '11267', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ALBERTO', 'JOSE', 'EVIES', 'GONZALEZ', '1965-11-04 00:00:00', 13, 5, 1131, NULL, '2016-10-03 00:00:00', NULL, 1),
(85, '11291', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ANGELA', 'LEONOR', 'ARANEA', 'CHICA', '1976-01-30 00:00:00', 13, 5, 1131, NULL, '2016-12-12 00:00:00', NULL, 1),
(86, '11346', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ARTURO', 'ARMANDO', 'SOSA', 'HERRERA', '1962-08-27 00:00:00', 12, 5, 1131, NULL, '2017-11-01 00:00:00', NULL, 1),
(87, '11414', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ADRIAN', 'ALEXANDER', 'PEREZ', 'RODRIGUEZ', '1994-04-19 00:00:00', 7, 5, 1131, NULL, '2018-03-16 00:00:00', NULL, 1),
(88, '11443', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ELISA', 'MARIBEL', 'PASERO', 'MARIÑO', '1979-08-25 00:00:00', 6, 5, 1131, NULL, '2018-07-19 00:00:00', NULL, 1),
(89, '11463', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'OMAR', 'ALFONSO', 'MARQUEZ', 'RODRIGUEZ', '2000-03-04 00:00:00', 4, 5, 1131, NULL, '2018-11-05 00:00:00', NULL, 1),
(90, '11474', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ANGELICA', 'ESTEFANIA', 'FUNES', 'OLOYOLA', '1995-06-27 00:00:00', 4, 5, 1131, NULL, '2018-11-26 00:00:00', NULL, 1),
(91, '11492', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ESLYN', 'MILEYDIS', 'ROJAS', 'ROMERO', '1989-03-25 00:00:00', 6, 5, 1131, NULL, '2019-02-11 00:00:00', NULL, 1),
(92, '10135', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'CARMEN', 'VESTALIA', 'OCHOA', '', '1941-01-09 00:00:00', 19, 18, 1131, NULL, '2005-01-24 00:00:00', NULL, 1),
(93, '10446', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'LAURA', 'YAMILET', 'ROJAS', 'LIZARRAGA', '1974-09-28 00:00:00', 21, 10, 1131, NULL, '2008-07-23 00:00:00', NULL, 1),
(94, '10466', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ANTONIO', 'JOSE', 'RUBIO', 'HERNANDEZ', '1967-12-11 00:00:00', 22, 19, 1131, NULL, '2008-10-03 00:00:00', NULL, 1),
(95, '10559', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'RUBEN', 'DARIO', 'VERA', 'PATIÑO', '1983-01-19 00:00:00', 37, 19, 1131, NULL, '2010-01-18 00:00:00', NULL, 1),
(96, '10568', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'LUISA', 'ESTHER', 'TOVAR', '', '1964-04-09 00:00:00', 24, 11, 1131, NULL, '2010-01-18 00:00:00', NULL, 1),
(97, '10589', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JOSE', 'ANTONIO', 'MACHADO', 'PEREZ', '1967-08-19 00:00:00', 13, 19, 1131, NULL, '2010-02-22 00:00:00', NULL, 1),
(98, '10775', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'DULY', 'YOSMILA', 'RINCONES', '', '1980-09-12 00:00:00', 38, 19, 1131, NULL, '2012-04-30 00:00:00', NULL, 1),
(99, '10776', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YENNIFER', 'MARIANA', 'VILLA', 'ANGEL', '1988-11-24 00:00:00', 19, 19, 1131, NULL, '2012-05-08 00:00:00', NULL, 1),
(100, '10777', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ANA', 'CECILIA', 'CASTAÑO', 'ESCOBAR', '1946-10-10 00:00:00', 19, 19, 1131, NULL, '2012-05-16 00:00:00', NULL, 1),
(101, '10896', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'AMAYOISBI', 'LIDSAY', 'GARCIA', 'CHACIN', '1972-07-12 00:00:00', 12, 12, 1131, NULL, '2013-08-08 00:00:00', NULL, 1),
(102, '10897', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JENNIFER', 'LETICIA', 'CHACON', 'ZAMBRANO', '1985-02-21 00:00:00', 26, 12, 1131, NULL, '2013-08-19 00:00:00', NULL, 1),
(103, '10977', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'IGNAYARI', 'KATHERINE', 'MENDOZA', 'LUZARDO', '1991-06-11 00:00:00', 28, 7, 1131, NULL, '2014-06-05 00:00:00', NULL, 1),
(104, '11145', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'REINA', 'MARIA', 'FAJARDO', 'GUERRERO', '1998-03-10 00:00:00', 31, 7, 1131, NULL, '2015-11-25 00:00:00', NULL, 1),
(105, '11159', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YOLYMER', 'ALICIA', 'MENDOZA', 'GARCIA', '1973-10-29 00:00:00', 13, 7, 1131, NULL, '2015-12-18 00:00:00', NULL, 1),
(106, '11208', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ROSA', 'ESMERALDA', 'LUZARDO', 'CARDENAS', '1965-08-28 00:00:00', 24, 11, 1131, NULL, '2016-03-14 00:00:00', NULL, 1),
(107, '11292', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ADRIANA', '', 'GUZMAN', 'LA CRUZ', '1982-06-18 00:00:00', 26, 12, 1131, NULL, '2016-12-12 00:00:00', NULL, 1),
(108, '11423', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JOSE', 'LUZARDO', 'ESTABA', 'MOTA', '1988-04-08 00:00:00', 12, 13, 1131, NULL, '2018-04-09 00:00:00', NULL, 1),
(109, '11438', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'KARINA', '', 'PEREZ', 'MARQUES', '1993-08-09 00:00:00', 33, 19, 1131, NULL, '2018-07-09 00:00:00', NULL, 1),
(110, '11455', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ZONNY', 'EDUARDO', 'GARCIA', 'OJEDA', '1993-08-30 00:00:00', 35, 13, 1131, NULL, '2018-08-21 00:00:00', NULL, 1),
(111, '11473', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YAINE', 'ALEXANDER', 'MACHADO', 'PEREZ', '1981-06-12 00:00:00', 31, 11, 1131, NULL, '2018-11-26 00:00:00', NULL, 1),
(112, '11498', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ANTONIO', 'ALEXANDER', 'FARIA', 'EXPOSITO', '1983-08-28 00:00:00', 31, 11, 1131, NULL, '2019-02-18 00:00:00', NULL, 1),
(113, '11524', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'LEONARDO', 'ANTONIO', 'LOPEZ', 'AGURTO', '2001-10-29 00:00:00', 32, 19, 1131, NULL, '2019-10-01 00:00:00', NULL, 1),
(114, '11525', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JOSE', 'ARTURO', 'MADRIZ', 'MALAVE', '1996-06-07 00:00:00', 2, 19, 1131, NULL, '2019-11-04 00:00:00', NULL, 1),
(115, '11530', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'LILIANA', 'IBETH', 'PARRA', 'PEREZ', '1980-05-21 00:00:00', 25, 19, 1131, NULL, '2020-01-29 00:00:00', NULL, 1),
(116, '11531', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ANTONIO', 'JOSE', 'REYES', 'SEQUERA', '1959-12-31 00:00:00', 15, 19, 1131, NULL, '2020-02-03 00:00:00', NULL, 1),
(117, '11532', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'DUVAN', 'RAFAEL', 'PINTO', 'JAIMES', '2000-02-07 00:00:00', 2, 19, 1131, NULL, '2020-02-26 00:00:00', NULL, 1),
(118, '11534', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'FREDDY', 'FRANCISCO', 'PERDOMO', 'MOLINA', '1986-03-03 00:00:00', 22, 19, 1131, NULL, '2020-03-01 00:00:00', NULL, 1),
(119, '11536', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'FERNANDO', 'JOSE', 'RANGEL', 'KUIPPERS', '1992-12-12 00:00:00', 28, 19, 1131, NULL, '2020-03-16 00:00:00', NULL, 1),
(120, '11537', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'GELEN', 'DEL ROSARIO', 'CARDENAS', 'MARQUEZ', '1958-03-08 00:00:00', 23, 11, 1131, NULL, '2020-06-01 00:00:00', NULL, 1),
(121, '11538', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'FREDDY', 'ANTONIO', 'BORRERO', 'CONTRERAS', '1989-08-09 00:00:00', 24, 11, 1131, NULL, '2020-06-01 00:00:00', NULL, 1),
(122, '11539', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'AURA', 'MARIA', 'CONTRERAS', 'PASTRAN', '1968-07-01 00:00:00', 24, 11, 1131, NULL, '2020-06-01 00:00:00', NULL, 1),
(123, '36', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JESUS', 'SALVADOR', 'MORILLO', 'QUINTANA', '1960-03-02 00:00:00', 12, 11, 1131, NULL, '2000-01-17 00:00:00', NULL, 1),
(124, '49', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'AMELIA', 'JOSEFINA', 'DIAZ', 'MENDOZA', '1956-03-19 00:00:00', 20, 19, 1131, NULL, '2004-11-01 00:00:00', NULL, 1),
(125, '10195', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'EMILIO', 'JOSE', 'LEON', 'FARIAS', '1965-06-28 00:00:00', 15, 3, 1131, NULL, '2005-11-01 00:00:00', NULL, 1),
(126, '11265', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'GUSTAVO', 'ADOLFO', 'PUCHI', 'MEDINA', '1963-09-12 00:00:00', 13, 3, 1131, NULL, '2016-10-03 00:00:00', NULL, 1),
(127, '11376', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ALFIO', 'FILIPPO', 'SAGLIMBENI', 'MUSCOLINO', '1967-08-03 00:00:00', 12, 3, 1131, NULL, '2017-12-20 00:00:00', NULL, 1),
(128, '11397', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ARIANNA', 'ELENA', 'MATOS', 'IACOBELLIS', '1995-08-21 00:00:00', 9, 3, 1131, NULL, '2018-02-20 00:00:00', NULL, 1),
(129, '11450', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ANA', 'VIRGINIA', 'BLANDIN', 'ARZOLA', '1981-04-08 00:00:00', 12, 3, 1131, NULL, '2018-08-07 00:00:00', NULL, 1),
(130, '10262', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'OSCAR', 'AUGUSTO', 'PIÑA', 'ALBUJAR', '1946-01-06 00:00:00', 15, 14, 1131, NULL, '2006-01-02 00:00:00', NULL, 1),
(131, '11278', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'YOSBER', 'ALEJANDRO', 'GOMEZ', 'LANDAETA', '1997-12-02 00:00:00', 41, 15, 1131, NULL, '2016-11-01 00:00:00', NULL, 1),
(132, '11280', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'DUGLIMAR', 'YOLEIDA', 'MENDEZ', 'RIVAS', '1999-07-02 00:00:00', 41, 15, 1131, NULL, '2016-11-16 00:00:00', NULL, 1),
(133, '11312', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'SOL', 'PATRICIA', 'VIANA', 'CONSUEGRA', '1997-09-23 00:00:00', 41, 15, 1131, NULL, '2017-03-20 00:00:00', NULL, 1),
(134, '11063', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'DOUGLAS', 'EDUARDO', 'TORREALBA', 'SANCHEZ', '1975-10-28 00:00:00', 42, 16, 1131, NULL, '2015-06-02 00:00:00', NULL, 1),
(135, '11064', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'DARWING', 'JOSE', 'CORDOVA', '', '1980-08-04 00:00:00', 40, 16, 1131, NULL, '2015-06-02 00:00:00', NULL, 1),
(136, '11066', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JEFERSON', 'JESUS', 'YANEZ', 'VILLEGAS', '1995-10-12 00:00:00', 40, 16, 1131, NULL, '2015-06-02 00:00:00', NULL, 1),
(137, '11068', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JOSE', 'ANTONIO', 'ARAUJO', 'RODRIGUEZ', '1989-05-30 00:00:00', 40, 16, 1131, NULL, '2015-06-02 00:00:00', NULL, 1),
(138, '11236', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ANGEL', 'EDUARDO', 'APARICIO', 'ROMERO', '1970-08-02 00:00:00', 40, 16, 1131, NULL, '2016-05-20 00:00:00', NULL, 1),
(139, '11237', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'JESUS', 'ANTONIO', 'ROJAS', 'CRUZ', '1984-07-18 00:00:00', 40, 16, 1131, NULL, '2016-05-20 00:00:00', NULL, 1),
(140, '10508', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'FREDY', 'SAMUEL', 'BAUTISTA', 'VILLEGAS', '1950-05-14 00:00:00', 18, 17, 1131, NULL, '2005-08-01 00:00:00', NULL, 1),
(141, '10689', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'ELLEN', 'KATIUSKA', 'FUENTES', 'RIOS', '1966-03-16 00:00:00', 33, 18, 1131, NULL, '2007-02-26 00:00:00', NULL, 1),
(142, '11451', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'BARBARA', 'CAROLINA', 'ZAMBRANO', 'AGUINALDE', '1996-11-19 00:00:00', 6, 18, 1131, NULL, '2018-08-01 00:00:00', NULL, 1),
(143, '11476', 'eyJpdiI6IktFQWRPTFdWRjBkRzRuR2hUSlwvV1hBPT0iLCJ2YWx1ZSI6Iktza04wQytPRnlLbmRETWJHQmdxaHc9PSIsIm1hYyI6IjExMTAzMTU4YjY3MDQzMDA4NjI2NjZjZDNiYjlmNzJkYmY2N2JiYmZlZjQwODVmMzE2ZjUxMWMwYjYyMmM1ZjcifQ==', 'MARY', '', 'CRUZ', 'SALAZAR', '1989-09-20 00:00:00', 12, 18, 1131, NULL, '2018-12-03 00:00:00', NULL, 1);

--
-- Disparadores `tbl_usuario`
--
DELIMITER $$
CREATE TRIGGER `tbl_usuario_AI` AFTER INSERT ON `tbl_usuario` FOR EACH ROW INSERT INTO logs.tbl_usuario(codigo, nombre_1_nuevo, nombre_2_nuevo, apellido_1_nuevo, apellido_2_nuevo, fecha_nacimiento_nuevo, id_cargo_nuevo, id_division_nuevo, id_parroquia_nuevo, fecha_ingreso_nuevo, id_estatus_nuevo) VALUES (NEW.codigo, NEW.nombre_1, NEW.nombre_2, NEW.apellido_1, NEW.apellido_2, NEW.fecha_nacimiento, NEW.id_cargo, NEW.id_division, NEW.id_parroquia, NEW.fecha_ingreso, NEW.id_estatus)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tbl_usuario_BU` BEFORE UPDATE ON `tbl_usuario` FOR EACH ROW INSERT INTO logs.tbl_usuario(codigo, nombre_1, nombre_2, apellido_1, apellido_2, fecha_nacimiento, id_cargo, id_division, id_parroquia, fecha_ingreso, fecha_egreso, id_estatus,  nombre_1_nuevo, nombre_2_nuevo, apellido_1_nuevo, apellido_2_nuevo, fecha_nacimiento_nuevo, id_cargo_nuevo, id_division_nuevo, id_parroquia_nuevo, fecha_ingreso_nuevo, fecha_egreso_nuevo, id_estatus_nuevo) VALUES (NEW.codigo, OLD.nombre_1, OLD.nombre_2, OLD.apellido_1, OLD.apellido_2, OLD.fecha_nacimiento, OLD.id_cargo, OLD.id_division, OLD.id_parroquia, OLD.fecha_ingreso, OLD.fecha_egreso, OLD.id_estatus, NEW.nombre_1, NEW.nombre_2, NEW.apellido_1, NEW.apellido_2, NEW.fecha_nacimiento, NEW.id_cargo, NEW.id_division, NEW.id_parroquia, NEW.fecha_ingreso, NEW.fecha_egreso, NEW.id_estatus)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_usuario_documento_identidad`
--

CREATE TABLE `tbl_usuario_documento_identidad` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_tipo_documento_identidad` int(11) NOT NULL,
  `documento` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbl_usuario_documento_identidad`
--

INSERT INTO `tbl_usuario_documento_identidad` (`id`, `id_usuario`, `id_tipo_documento_identidad`, `documento`) VALUES
(1, 1, 1, '17671373'),
(2, 2, 1, '10380904'),
(3, 3, 1, '14451068'),
(4, 4, 1, '5597044'),
(5, 5, 1, '6550673'),
(6, 6, 1, '16430295'),
(7, 7, 1, '4166638'),
(8, 8, 1, '13161322'),
(9, 9, 1, '15394238'),
(10, 10, 1, '18934893'),
(11, 11, 1, '19227558'),
(12, 12, 1, '22025009'),
(13, 13, 1, '18760647'),
(14, 14, 1, '21281041'),
(15, 15, 1, '19156081'),
(16, 16, 1, '22904461'),
(17, 17, 1, '20756114'),
(18, 18, 1, '21582423'),
(19, 19, 1, '25304811'),
(20, 20, 1, '19852149'),
(21, 21, 1, '17693239'),
(22, 22, 1, '25518550'),
(23, 23, 1, '24314826'),
(24, 24, 1, '22964636'),
(25, 25, 1, '26774496'),
(26, 26, 1, '21131036'),
(27, 27, 1, '13950180'),
(28, 28, 1, '9968146'),
(29, 29, 1, '15074166'),
(30, 30, 1, '25839093'),
(31, 31, 1, '24205870'),
(32, 32, 1, '15040556'),
(33, 33, 1, '7884245'),
(34, 34, 1, '12749844'),
(35, 35, 1, '25231809'),
(36, 36, 1, '15235084'),
(37, 37, 1, '20114596'),
(38, 38, 1, '16661412'),
(39, 39, 2, '83024489'),
(40, 40, 1, '16316832'),
(41, 41, 1, '25504426'),
(42, 42, 1, '26252073'),
(43, 43, 1, '25327986'),
(44, 44, 1, '21115694'),
(45, 45, 1, '25533060'),
(46, 46, 1, '25037878'),
(47, 47, 1, '23817163'),
(48, 48, 1, '18329799'),
(49, 49, 1, '12067262'),
(50, 50, 1, '20229883'),
(51, 51, 1, '18366468'),
(52, 52, 1, '25019411'),
(53, 53, 1, '10801033'),
(54, 54, 1, '18898447'),
(55, 55, 1, '30776341'),
(56, 56, 1, '11072019'),
(57, 57, 1, '15759106'),
(58, 58, 1, '12377736'),
(59, 59, 1, '23607795'),
(60, 60, 1, '20780395'),
(61, 61, 1, '6730914'),
(62, 62, 1, '24367675'),
(63, 63, 1, '21090653'),
(64, 64, 1, '24981788'),
(65, 65, 1, '25203717'),
(66, 66, 1, '25367199'),
(67, 67, 1, '9961190'),
(68, 68, 1, '17498402'),
(69, 69, 1, '13586696'),
(70, 70, 1, '24999590'),
(71, 71, 1, '20629350'),
(72, 72, 1, '22350446'),
(73, 73, 1, '17482637'),
(74, 74, 1, '26217602'),
(75, 75, 1, '20026779'),
(76, 76, 1, '6310314'),
(77, 77, 1, '19185045'),
(78, 78, 1, '11899658'),
(79, 79, 1, '26396073'),
(80, 80, 1, '6868874'),
(81, 81, 1, '20638141'),
(82, 82, 1, '16472039'),
(83, 83, 1, '19966508'),
(84, 84, 1, '6168455'),
(85, 85, 1, '12831730'),
(86, 86, 1, '7219655'),
(87, 87, 1, '21283384'),
(88, 88, 1, '14048174'),
(89, 89, 1, '26902642'),
(90, 90, 1, '24723575'),
(91, 91, 1, '18485819'),
(92, 92, 1, '2898759'),
(93, 93, 1, '12161715'),
(94, 94, 1, '6793120'),
(95, 95, 1, '15574739'),
(96, 96, 1, '6243475'),
(97, 97, 1, '6270987'),
(98, 98, 1, '16512408'),
(99, 99, 1, '18460301'),
(100, 100, 1, '6294031'),
(101, 101, 1, '11487234'),
(102, 102, 1, '17139681'),
(103, 103, 1, '19581420'),
(104, 104, 1, '26911669'),
(105, 105, 1, '10812350'),
(106, 106, 1, '6182144'),
(107, 107, 1, '16461316'),
(108, 108, 1, '18406483'),
(109, 109, 1, '20493477'),
(110, 110, 1, '22041443'),
(111, 111, 1, '14742504'),
(112, 112, 1, '16413136'),
(113, 113, 1, '28484899'),
(114, 114, 1, '25209317'),
(115, 115, 1, '13884698'),
(116, 116, 1, '5597900'),
(117, 117, 1, '27120587'),
(118, 118, 1, '18190765'),
(119, 119, 1, '22776760'),
(120, 120, 1, '6009195'),
(121, 121, 1, '19371690'),
(122, 122, 1, '10178751'),
(123, 123, 1, '5894672'),
(124, 124, 1, '4085309'),
(125, 125, 1, '3979230'),
(126, 126, 1, '6826643'),
(127, 127, 1, '6823443'),
(128, 128, 1, '24069076'),
(129, 129, 1, '14471989'),
(130, 130, 1, '3157447'),
(131, 131, 1, '26282952'),
(132, 132, 1, '27344553'),
(133, 133, 1, '25915845'),
(134, 134, 1, '13823055'),
(135, 135, 1, '15150576'),
(136, 136, 1, '25225060'),
(137, 137, 1, '19753133'),
(138, 138, 1, '10351263'),
(139, 139, 1, '16474809'),
(140, 140, 1, '3180748'),
(141, 141, 1, '8957263'),
(142, 142, 1, '26332830'),
(143, 143, 1, '18514042');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_cargo_empleado`
--
ALTER TABLE `tbl_cargo_empleado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_cargo_supervisa`
--
ALTER TABLE `tbl_cargo_supervisa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cargo` (`id_cargo`),
  ADD KEY `id_cargo_supervisor` (`id_cargo_supervisor`);

--
-- Indices de la tabla `tbl_ciudades`
--
ALTER TABLE `tbl_ciudades`
  ADD PRIMARY KEY (`id_ciudad`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indices de la tabla `tbl_cliente`
--
ALTER TABLE `tbl_cliente`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_cliente_facturacion`
--
ALTER TABLE `tbl_cliente_facturacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `tbl_concepto_horas_no_cargables`
--
ALTER TABLE `tbl_concepto_horas_no_cargables`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_configuracion`
--
ALTER TABLE `tbl_configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_contacto_usuario`
--
ALTER TABLE `tbl_contacto_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `tbl_division`
--
ALTER TABLE `tbl_division`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_estados`
--
ALTER TABLE `tbl_estados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_estatus`
--
ALTER TABLE `tbl_estatus`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_horas_cargables`
--
ALTER TABLE `tbl_horas_cargables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_proy_analista` (`id_proy_analista`);

--
-- Indices de la tabla `tbl_horas_no_cargables`
--
ALTER TABLE `tbl_horas_no_cargables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_concepto` (`id_concepto`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `aprobado_por` (`aprobado_por`),
  ADD KEY `id_division` (`id_division`);

--
-- Indices de la tabla `tbl_menu`
--
ALTER TABLE `tbl_menu`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_menu_usuario`
--
ALTER TABLE `tbl_menu_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `tbl_monedas`
--
ALTER TABLE `tbl_monedas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_municipios`
--
ALTER TABLE `tbl_municipios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indices de la tabla `tbl_parroquias`
--
ALTER TABLE `tbl_parroquias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_municipio` (`id_municipio`);

--
-- Indices de la tabla `tbl_proyecto`
--
ALTER TABLE `tbl_proyecto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_proyecto_analista`
--
ALTER TABLE `tbl_proyecto_analista`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_proyecto` (`id_proyecto`);

--
-- Indices de la tabla `tbl_proyecto_divisiones`
--
ALTER TABLE `tbl_proyecto_divisiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_division` (`id_division`),
  ADD KEY `id_proyecto` (`id_proyecto`);

--
-- Indices de la tabla `tbl_tipo_contacto`
--
ALTER TABLE `tbl_tipo_contacto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_tipo_documento_identidad`
--
ALTER TABLE `tbl_tipo_documento_identidad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tbl_usuario`
--
ALTER TABLE `tbl_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cargo` (`id_cargo`),
  ADD KEY `id_division` (`id_division`),
  ADD KEY `id_parroquia` (`id_parroquia`);

--
-- Indices de la tabla `tbl_usuario_documento_identidad`
--
ALTER TABLE `tbl_usuario_documento_identidad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_tipo_documento_identidad` (`id_tipo_documento_identidad`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `logs_auditoria`
--
ALTER TABLE `logs_auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `tbl_cargo_empleado`
--
ALTER TABLE `tbl_cargo_empleado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `tbl_cargo_supervisa`
--
ALTER TABLE `tbl_cargo_supervisa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `tbl_ciudades`
--
ALTER TABLE `tbl_ciudades`
  MODIFY `id_ciudad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=523;

--
-- AUTO_INCREMENT de la tabla `tbl_cliente`
--
ALTER TABLE `tbl_cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_cliente_facturacion`
--
ALTER TABLE `tbl_cliente_facturacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_concepto_horas_no_cargables`
--
ALTER TABLE `tbl_concepto_horas_no_cargables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tbl_configuracion`
--
ALTER TABLE `tbl_configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_contacto_usuario`
--
ALTER TABLE `tbl_contacto_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT de la tabla `tbl_division`
--
ALTER TABLE `tbl_division`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `tbl_estados`
--
ALTER TABLE `tbl_estados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `tbl_estatus`
--
ALTER TABLE `tbl_estatus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `tbl_horas_cargables`
--
ALTER TABLE `tbl_horas_cargables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_horas_no_cargables`
--
ALTER TABLE `tbl_horas_no_cargables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_menu`
--
ALTER TABLE `tbl_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `tbl_menu_usuario`
--
ALTER TABLE `tbl_menu_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tbl_monedas`
--
ALTER TABLE `tbl_monedas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tbl_municipios`
--
ALTER TABLE `tbl_municipios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=463;

--
-- AUTO_INCREMENT de la tabla `tbl_parroquias`
--
ALTER TABLE `tbl_parroquias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1139;

--
-- AUTO_INCREMENT de la tabla `tbl_proyecto`
--
ALTER TABLE `tbl_proyecto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_proyecto_analista`
--
ALTER TABLE `tbl_proyecto_analista`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_proyecto_divisiones`
--
ALTER TABLE `tbl_proyecto_divisiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbl_tipo_contacto`
--
ALTER TABLE `tbl_tipo_contacto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tbl_tipo_documento_identidad`
--
ALTER TABLE `tbl_tipo_documento_identidad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_usuario`
--
ALTER TABLE `tbl_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT de la tabla `tbl_usuario_documento_identidad`
--
ALTER TABLE `tbl_usuario_documento_identidad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=256;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tbl_cargo_supervisa`
--
ALTER TABLE `tbl_cargo_supervisa`
  ADD CONSTRAINT `tbl_cargo_supervisa_ibfk_1` FOREIGN KEY (`id_cargo`) REFERENCES `tbl_cargo_empleado` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tbl_cargo_supervisa_ibfk_2` FOREIGN KEY (`id_cargo_supervisor`) REFERENCES `tbl_cargo_empleado` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `tbl_ciudades`
--
ALTER TABLE `tbl_ciudades`
  ADD CONSTRAINT `tbl_ciudades_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `tbl_estados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_cliente_facturacion`
--
ALTER TABLE `tbl_cliente_facturacion`
  ADD CONSTRAINT `tbl_cliente_facturacion_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_cliente` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `tbl_contacto_usuario`
--
ALTER TABLE `tbl_contacto_usuario`
  ADD CONSTRAINT `FK_ID_USUARIO` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuario` (`id`);

--
-- Filtros para la tabla `tbl_horas_cargables`
--
ALTER TABLE `tbl_horas_cargables`
  ADD CONSTRAINT `tbl_horas_cargables_ibfk_1` FOREIGN KEY (`id_proy_analista`) REFERENCES `tbl_usuario` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `tbl_horas_no_cargables`
--
ALTER TABLE `tbl_horas_no_cargables`
  ADD CONSTRAINT `tbl_horas_no_cargables_ibfk_1` FOREIGN KEY (`id_concepto`) REFERENCES `tbl_concepto_horas_no_cargables` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tbl_horas_no_cargables_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuario` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tbl_horas_no_cargables_ibfk_3` FOREIGN KEY (`aprobado_por`) REFERENCES `tbl_usuario` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tbl_horas_no_cargables_ibfk_4` FOREIGN KEY (`id_division`) REFERENCES `tbl_division` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `tbl_menu_usuario`
--
ALTER TABLE `tbl_menu_usuario`
  ADD CONSTRAINT `tbl_menu_usuario_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `tbl_menu` (`id`),
  ADD CONSTRAINT `tbl_menu_usuario_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuario` (`id`);

--
-- Filtros para la tabla `tbl_municipios`
--
ALTER TABLE `tbl_municipios`
  ADD CONSTRAINT `tbl_municipios_ibfk_1` FOREIGN KEY (`id_estado`) REFERENCES `tbl_estados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_parroquias`
--
ALTER TABLE `tbl_parroquias`
  ADD CONSTRAINT `tbl_parroquias_ibfk_1` FOREIGN KEY (`id_municipio`) REFERENCES `tbl_municipios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tbl_proyecto_analista`
--
ALTER TABLE `tbl_proyecto_analista`
  ADD CONSTRAINT `tbl_proyecto_analista_ibfk_1` FOREIGN KEY (`id_proyecto`) REFERENCES `tbl_proyecto` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `tbl_proyecto_divisiones`
--
ALTER TABLE `tbl_proyecto_divisiones`
  ADD CONSTRAINT `tbl_proyecto_divisiones_ibfk_1` FOREIGN KEY (`id_division`) REFERENCES `tbl_division` (`id`),
  ADD CONSTRAINT `tbl_proyecto_divisiones_ibfk_2` FOREIGN KEY (`id_proyecto`) REFERENCES `tbl_proyecto` (`id`);

--
-- Filtros para la tabla `tbl_usuario`
--
ALTER TABLE `tbl_usuario`
  ADD CONSTRAINT `tbl_usuario_ibfk_1` FOREIGN KEY (`id_cargo`) REFERENCES `tbl_cargo_empleado` (`id`),
  ADD CONSTRAINT `tbl_usuario_ibfk_2` FOREIGN KEY (`id_division`) REFERENCES `tbl_division` (`id`),
  ADD CONSTRAINT `tbl_usuario_ibfk_3` FOREIGN KEY (`id_parroquia`) REFERENCES `tbl_parroquias` (`id`);

--
-- Filtros para la tabla `tbl_usuario_documento_identidad`
--
ALTER TABLE `tbl_usuario_documento_identidad`
  ADD CONSTRAINT `tbl_usuario_documento_identidad_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_usuario` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `tbl_usuario_documento_identidad_ibfk_2` FOREIGN KEY (`id_tipo_documento_identidad`) REFERENCES `tbl_tipo_documento_identidad` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

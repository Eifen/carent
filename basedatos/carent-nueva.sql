-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-04-2023 a las 19:47:32
-- Versión del servidor: 10.4.27-MariaDB
-- Versión de PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `carent-nueva`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_InsertLog` (IN `p_bitacora_accion` INT, IN `p_descripcion` TEXT, IN `p_ipResponsable` VARCHAR(255), IN `p_idResponsable` INT, IN `p_tablaAfectada` TEXT, IN `p_sqlRealizado` MEDIUMTEXT, IN `p_valorAnterior` TEXT, IN `p_valorNuevo` TEXT, IN `p_fechaModificacion` DATETIME, OUT `p_jsonResponse` TEXT)   BEGIN
DECLARE v_customErrorMessage TEXT;
DECLARE v_customError CONDITION FOR SQLSTATE '45000';
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_msg = MESSAGE_TEXT;
        SET v_customErrorMessage = CONCAT("Ha ocurrido un error en el registro de datos en la bitacora: (",@code,") ",@error_msg);
        ROLLBACK;
        #Guardamos la data en p_jsonResponse
        CALL sp_SQLException(1,1,"sp_InsertLog",v_customErrorMessage,1,@response);
        SET p_jsonResponse = (SELECT @response);
    END;
#Error Personalizado
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	#Registramos el error
        CALL sp_SQLException(1,1,"sp_InsertLog",v_customErrorMessage,1,@responseCustom);
        SET p_jsonResponse = (SELECT @responseCustom);
    END;
#Verificación de errores internos
SET @ExistTipoAccion = (SELECT COUNT(LA.Id) FROM tbl_control_logs_bitacora_accion LA WHERE LA.Id = p_bitacora_accion);
SET @ExistUser = (SELECT COUNT(U.Id) FROM tbl_usuarios U WHERE U.Id = p_idResponsable);

IF @ExistTipoAccion = 0 AND @ExistUser = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message": "Error 0008: El ID_ACCION_BITACORA(',p_bitacora_accion,'), 
                                      y el ID_USER(',p_idResponsable,') no existen"}');
    SIGNAL SQLSTATE '45000'; #Disparo el error	
END IF;

IF @ExistTipoAccion = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message": "Error 0009: El ID_ACCION_BITACORA(',p_bitacora_accion,') no existe"}');
    SIGNAL SQLSTATE '45000'; #Disparo el error
END IF;

IF @ExistUser = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message": "Error 0010: El ID_USER(',p_idResponsable,') no existe"}');
    SIGNAL SQLSTATE '45000'; #Disparo el error
END IF;

##En caso que logren pasar todas las validaciones
INSERT INTO `tbl_control_logs_bitacora`(`Id_bitacora_accion`, `Descripcion_accion`, `Ip_responsable`, `Id_usuario_responsable`, `Tabla_afectada`, `Sql_realizado`, `Valor_anterior`, `Nuevo_valor`, `Fecha_Registro`) 
VALUES (p_bitacora_accion,p_descripcion,p_ipResponsable,p_idResponsable,
        p_tablaAfectada,p_sqlRealizado,p_valorAnterior,p_valorNuevo,p_fechaModificacion);
        
#Si no genero SQLEXCEPTION DEVOLVEMOS UN JSON
SET p_jsonResponse = CONCAT('{"response":true, "message": "Se han modificado algunas tablas y se han registrado en la bitacora"}');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_Login` (IN `p_userCode` VARCHAR(6), IN `p_userPassword` TEXT, IN `p_ipUser` VARCHAR(39), OUT `p_jsonResponse` TEXT)   BEGIN
#Manejo de errores
DECLARE v_customErrorMessage TEXT;
DECLARE v_customError CONDITION FOR SQLSTATE '45000';
#Error de SQL
DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_string = MESSAGE_TEXT;
        ROLLBACK;
        SET v_customErrorMessage = CONCAT("Se ha producido un error en el inicio de sesión: (",@code,") ",@error_string);
        #Guardamos el error
        CALL sp_SQLException(1,1,"sp_Login",v_customErrorMessage,1,@responseError);
        SET p_jsonResponse = (SELECT @responseError);
    END;
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	CALL sp_SQLException(1,1,"sp_Login",v_customErrorMessage,1,@responseError);
        SET p_jsonResponse = (SELECT @responseError);
    END;
#Validaciones de usuario
SET @ExistUser = (SELECT COUNT(Us.Id) FROM tbl_usuarios Us WHERE Us.Codigo = p_userCode AND Us.Id_estatus = 1 LIMIT 1);
IF @ExistUser = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message":"Error 0011: El ID_CODE(',p_userCode,') no existe o tiene el acceso denegado"}');
    SIGNAL SQLSTATE '45000'; #Disparabamos el error si no existe el usuario
END IF;
#Validacion de llave de Encrypt
SET @KeyEncrypt = (SELECT EK.EncryptKey FROM tbl_control_encryptkey EK WHERE EK.Id_estatus = 1 LIMIT 1);
IF @KeyEncrypt = 0 OR @KeyEncrypt IS NULL THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message":"Error 0012: No existen Key Encrypt registradas"}');
    SIGNAL SQLSTATE '45000'; #Disparamos el error si no existe Key Encrypt
END IF;

#Validamos la contraseña
SET @TruePassword =(SELECT CAST(AES_DECRYPT(Us.Clave,@KeyEncrypt) AS CHAR) FROM tbl_usuarios Us WHERE Us.Codigo = p_userCode AND Us.Id_estatus = 1);
IF p_userPassword != @TruePassword OR @TruePassword IS NULL THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message":"Error 0013: La contraseña no coincide con la registrada en el sistema"}');
    SIGNAL SQLSTATE '45000'; #Disparamos el error si las contraseñas no coinciden
END IF;

#Validamos cambio de la clave
SET @ChangePass = (SELECT IF(Us.Fecha_cambio_clave > CURDATE(), false, true) FROM tbl_usuarios Us WHERE Us.Codigo = p_userCode AND Us.Id_estatus = 1);
IF @ChangePass IS NULL THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message":"Error 0014: el ID_CODE(',p_userCode,') no se encuentra activo"}');
    SIGNAL SQLSTATE '45000'; #Disparamos el error si no devuelve nada la consulta
END IF;

#Capturamos el ultimo login del usuario
SET @IdUser = (SELECT Us.Id FROM tbl_usuarios Us WHERE Us.Codigo = p_userCode LIMIT 1);
SET @LastInsert = (SELECT MAX(BL2.Id) FROM tbl_control_logs_bitacora BL2 WHERE BL2.Id_usuario_responsable = @IdUser 
                   AND BL2.Descripcion_accion LIKE "login%" LIMIT 1); #Almacena el ultimo insert de login
SET @LastLogin = (SELECT Us.Fecha_login FROM tbl_usuarios Us WHERE Us.Id = @IdUser LIMIT 1); #Almacena la ultima fecha de login
SET @LastIp = (SELECT IFNULL(BL.Ip_responsable,"0.0.0.0") FROM tbl_control_logs_bitacora BL WHERE BL.Id = @LastInsert); #Almacena la ultima IP

#Actualizamos el ultimo login
SET @FechaActual = (SELECT SYSDATE());
UPDATE tbl_usuarios u SET u.Fecha_login = @FechaActual WHERE u.Codigo = p_userCode;
SET @SQLRealizado = CONCAT("UPDATE tbl_usuarios u SET u.Fecha_login = ",@FechaActual," WHERE u.Codigo = ",p_userCode,";");

#Acomodamos el ultimo valor y el nuevo
SET @LastValue = CONCAT('{"fecha_ultimo_login": "',@LastLogin,'","ultima_ip": "',@LastIp,'"}');
SET @NewValue = CONCAT('{"fecha_ultimo_login": "',@FechaActual,'","ultima_ip": "',p_ipUser,'"}');

#Preparamos las variables para response
SET @CargoId = (SELECT IFNULL(us.Id_jerarquia_cargo,0) FROM tbl_usuarios us WHERE us.Id = @IdUser LIMIT 1);
SET @DivisionId = (SELECT IFNULL(us.Id_jerarquia_division,0) FROM tbl_usuarios us WHERE us.Id = @IdUser LIMIT 1);
SET @EmailId = (SELECT IFNULL(Ub.Correo_principal,Ub.Correo_secundario) FROM tbl_usuarios_contacto Ub WHERE Ub.Id_usuario = @IdUser LIMIT 1);

CALL sp_InsertLog(2,"login",p_ipUser,@IdUser,"tbl_usuarios",@SQLRealizado,@LastValue,@NewValue,@FechaActual,@jsonResponse);
#Extraemos el JSON a una variable
SET @ResponseJson = JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.response'));

#Verificamos si registró efectivamente en la bitacora
IF @ResponseJson != "true" THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message":"Error 0015: ha ocurrido un error en la bitacora"}');
    SIGNAL SQLSTATE '45000'; #Si no ha podido registrar nada, dispara el error
END IF;

#Si paso todas las validaciones procedemos a registrar
SET p_jsonResponse = CONCAT('{"passwordChange": ',@ChangePass,',"idCargo": ',@CargoId,', 
                            "idDivision": ',@DivisionID,',"idUsuario": ',@IdUser,',
                            "emailUser": "',@EmailId,'","message": "Bienvenido. En breves momentos será redireccionado...",
                            "response": true}');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_QueryPagination` (IN `tableTarget` TEXT, OUT `p_jsonResponse` TEXT)   query_select: BEGIN
DECLARE v_CustomMessage TEXT;
DECLARE v_CustomError CONDITION FOR SQLSTATE '45000';
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_msj = MESSAGE_TEXT;
        ROLLBACK;
        SET v_CustomMessage = CONCAT("Se ha producido un error en la consulta: (",@code,") ",@error_msj);
        #Guardamos el error
        CALL sp_SQLException(1,1,"sp_QueryPagination",v_CustomMessage,1,@responseError);
        SET p_jsonResponse = (SELECT @responseError);
    END;
DECLARE EXIT HANDLER FOR v_CustomError
	BEGIN
    	#Guardamos el error
        CALL sp_SQLException(1,1,"sp_QueryPagination",v_CustomMessage,1,@responseError);
        SET p_jsonResponse = (SELECT @responseError);
    END;
#Validaciones Custom
IF tableTarget = "" OR tableTarget is NULL THEN
	SET v_CustomMessage = CONCAT('{"response":false,"message":"Error 0016: La selección de tabla está vacia (',tableTarget,')"}');
    SIGNAL SQLSTATE '45000'; #Activamos el error
END IF;

#Validaciones para los Select
IF tableTarget = 'users' THEN
	#Creamos un JSON CON la informacion de la consulta
	SET @JsonSelect = (SELECT CONCAT("[",GROUP_CONCAT('{"codigo":"',Us.Codigo,'","cedula":"',Ud.Descripcion,'","nombre":"',CONCAT(Us.Primer_nombre," ",Us.Segundo_nombre," ",Us.Primer_apellido," ",Us.Segundo_apellido),'","correo":"',Uc.Correo_principal,'","estatus":',Us.Id_estatus,'}'),"]") FROM tbl_usuarios Us INNER JOIN tbl_usuarios_documentoidentidad Ud ON Ud.Id_usuario = Us.Id INNER JOIN tbl_usuarios_contacto Uc ON Uc.Id_usuario = Us.Id);
    SET p_jsonResponse = CONCAT('{"response":true,"message":',@JsonSelect,'}');
    LEAVE query_select;
END IF;

#Si no entra en ninguna condición
SET v_CustomMessage = CONCAT('{"response":false,"message":"Error 0017: La selección de tabla no coincide con ninguna tabla (',tableTarget,')"}');
SIGNAL SQLSTATE '45000'; #Activamos el error
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_SQLException` (IN `p_Id_objeto_afectado` INT, IN `p_Id_tipo_mensaje` INT, IN `p_objeto_afectado` VARCHAR(255), IN `p_mensaje_error` TEXT, IN `p_estatus_error` INT, OUT `p_error_response` TEXT)   BEGIN
DECLARE v_CustomErrorMessage TEXT;
DECLARE v_CustomError CONDITION FOR SQLSTATE '45000';
#Gestión de errores
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_string = MESSAGE_TEXT;
        SELECT CONCAT('{"response": false, "message": "Error con el código ',@code,':'
                      ,@error_string,'"}') INTO p_error_response;
    END;
#Error personalizado
DECLARE EXIT HANDLER FOR v_CustomError
	BEGIN
    	SET p_error_response = v_CustomErrorMessage;
    END;

#Init de errores
SET @ExistObject = (SELECT COUNT(CT.Id) FROM tbl_control_error_tipoobjeto CT WHERE CT.Id = p_Id_objeto_afectado LIMIT 1);
SET @ExistMensaje = (SELECT COUNT(CM.Id) FROM tbl_control_error_tipomensaje CM WHERE CM.Id = p_Id_tipo_mensaje LIMIT 1);
SET @ExistStatus = (SELECT COUNT(CE.Id) FROM tbl_control_estatus CE WHERE CE.Id = p_estatus_error LIMIT 1);

#Condicionales de error
#Verifica si mas de uno no existe
IF @ExistObject = 0 AND @ExistMensaje = 0 AND @ExistStatus = 0 THEN
	SET v_CustomErrorMessage = CONCAT('{"response":false,"message": "Error 0001: El
                                 ID_OBJETO(',p_Id_objeto_afectado,'),
                                 ID_TIPO_MENSAJE(',p_Id_tipo_mensaje,') y El
                                 ID_ESTATUS(',p_estatus_error,') no existen"}');
    SIGNAL SQLSTATE '45000'; #Dispara el custom error
END IF;
IF @ExistMensaje = 0 AND @ExistStatus = 0 THEN
	SET v_CustomErrorMessage = CONCAT('{"response":false,"message": "Error 0002:
                                 ID_TIPO_MENSAJE(',p_Id_tipo_mensaje,') 
                                 y El ID_ESTATUS(',p_estatus_error,') no existen"}');
    SIGNAL SQLSTATE '45000'; #Dispara el custom error
END IF;
IF @ExistObject = 0 AND @ExistMensaje = 0 THEN
	SET v_CustomErrorMessage = CONCAT('{"response":false,"message": "Error 0003: El
                                 ID_OBJETO(',p_Id_objeto_afectado,') y 
                                 ID_TIPO_MENSAJE(',p_Id_tipo_mensaje,') no existen"}');
    SIGNAL SQLSTATE '45000'; #Dispara el custom error
END IF;
IF @ExistObject = 0 AND @ExistStatus = 0 THEN
	SET v_CustomErrorMessage = CONCAT('{"response":false,"message": "Error 0004: El
                                 ID_OBJETO(',p_Id_objeto_afectado,')
                                 y El ID_ESTATUS(',p_estatus_error,') no existen"}');
    SIGNAL SQLSTATE '45000'; #Dispara el custom error
END IF;
#Verifica si no existen por separado
IF @ExistObject = 0 THEN
	SET v_CustomErrorMessage = CONCAT('{"response":false,"message": "Error 0005: El
                                 ID_OBJETO(',p_Id_objeto_afectado,') no existe"}');
    SIGNAL SQLSTATE '45000'; #Dispara el custom error
END IF;
IF @ExistMensaje = 0 THEN
	SET v_CustomErrorMessage = CONCAT('{"response":false,"message": "Error 0006: El
                                 ID_TIPO_MENSAJE(',p_Id_tipo_mensaje,') no existe"}');
    SIGNAL SQLSTATE '45000'; #Dispara el custom error
END IF;
IF @ExistStatus = 0 THEN
	SET v_CustomErrorMessage = CONCAT('{"response":false,"message": "Error 0007: El
                                 ID_ESTATUS(',p_estatus_error,') no existe"}');
    SIGNAL SQLSTATE '45000'; #Dispara el custom error
END IF;

#Indicamos la fecha actual a una variable
SET @FechaActual = (SELECT SYSDATE());

#Registramos el error y cuando se produjo
INSERT INTO `tbl_control_error`(`Id_error_tipomensaje`, `Id_error_tipoobjeto`, `Objeto_afectado`, `Error_mensaje`, `Fecha`, `Id_estatus`) 
VALUES (p_Id_tipo_mensaje,p_Id_objeto_afectado,p_objeto_afectado,p_mensaje_error,@FechaActual,p_estatus_error);

#Si pasa todos los controles devuelve otro json
SET p_error_response = CONCAT('{"response":false,"message": "Se ha producido un error en SQL, porfavor pongase en contacto con el administrador del sistema"}');

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_control_encryptkey`
--

CREATE TABLE `tbl_control_encryptkey` (
  `Id` int(11) NOT NULL,
  `EncryptKey` mediumtext NOT NULL,
  `EncryptIv` mediumtext NOT NULL,
  `Id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_control_encryptkey`
--

INSERT INTO `tbl_control_encryptkey` (`Id`, `EncryptKey`, `EncryptIv`, `Id_estatus`) VALUES
(1, '0123456789abcdef0123456789abcdef', 'abcdef9876543210abcdef9876543210', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_control_error`
--

CREATE TABLE `tbl_control_error` (
  `Id` int(11) NOT NULL,
  `Id_error_tipomensaje` int(11) NOT NULL,
  `Id_error_tipoobjeto` int(11) NOT NULL,
  `Objeto_afectado` varchar(50) NOT NULL,
  `Error_mensaje` text NOT NULL,
  `Fecha` datetime NOT NULL,
  `Id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_control_error`
--

INSERT INTO `tbl_control_error` (`Id`, `Id_error_tipomensaje`, `Id_error_tipoobjeto`, `Objeto_afectado`, `Error_mensaje`, `Fecha`, `Id_estatus`) VALUES
(1, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-05 09:39:56', 1),
(2, 1, 1, 'sp_InsertLog', 'Ha ocurrido un error en el registro de datos en la bitacora: (23000) Column \'Valor_anterior\' cannot be null', '2023-04-05 09:47:30', 1),
(3, 1, 1, 'sp_Login', 'Se ha producido un error en el inicio de sesión: (HY000) Syntax error in JSON text in argument 1 to function \'json_extract\' at position 13', '2023-04-05 09:49:46', 1),
(4, 1, 1, 'sp_Login', 'Se ha producido un error en el inicio de sesión: (HY000) Syntax error in JSON text in argument 1 to function \'json_extract\' at position 13', '2023-04-05 09:56:21', 1),
(5, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-05 10:04:19', 1),
(6, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-05 10:09:50', 1),
(7, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-05 13:56:49', 1),
(8, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0016: La selección de tabla está vacia ()\"}', '2023-04-10 11:00:10', 1),
(9, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-10 11:03:11', 1),
(10, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0016: La selección de tabla está vacia ()\"}', '2023-04-10 11:03:45', 1),
(11, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0017: La selección de tabla no coincide con ninguna tabla (users2)\"}', '2023-04-10 11:30:14', 1),
(12, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0016: La selección de tabla está vacia ()\"}', '2023-04-10 11:30:21', 1),
(13, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0017: La selección de tabla no coincide con ninguna tabla (users2)\"}', '2023-04-10 11:35:01', 1),
(14, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0017: La selección de tabla no coincide con ninguna tabla (users2)\"}', '2023-04-10 11:35:08', 1),
(15, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0016: La selección de tabla está vacia ()\"}', '2023-04-10 11:35:33', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_control_error_tipomensaje`
--

CREATE TABLE `tbl_control_error_tipomensaje` (
  `Id` int(11) NOT NULL,
  `Descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_control_error_tipomensaje`
--

INSERT INTO `tbl_control_error_tipomensaje` (`Id`, `Descripcion`) VALUES
(1, 'Advertencia'),
(2, 'Error');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_control_error_tipoobjeto`
--

CREATE TABLE `tbl_control_error_tipoobjeto` (
  `Id` int(11) NOT NULL,
  `NombreObjeto` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_control_error_tipoobjeto`
--

INSERT INTO `tbl_control_error_tipoobjeto` (`Id`, `NombreObjeto`) VALUES
(1, 'Procedimiento Almacenado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_control_estatus`
--

CREATE TABLE `tbl_control_estatus` (
  `Id` int(11) NOT NULL,
  `Descripcion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_control_estatus`
--

INSERT INTO `tbl_control_estatus` (`Id`, `Descripcion`) VALUES
(1, 'Activo'),
(2, 'Inactivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_control_logs_bitacora`
--

CREATE TABLE `tbl_control_logs_bitacora` (
  `Id` int(11) NOT NULL,
  `Id_bitacora_accion` int(11) NOT NULL COMMENT 'Tipo de acción que se realizo usando la nomenclatura CRUD',
  `Descripcion_accion` text NOT NULL,
  `Ip_responsable` varchar(39) NOT NULL COMMENT 'IPV4 O IPV6 del responsable de la acción realizada.',
  `Id_usuario_responsable` int(11) NOT NULL,
  `Tabla_afectada` text NOT NULL,
  `Sql_realizado` text DEFAULT NULL,
  `Valor_anterior` text DEFAULT NULL,
  `Nuevo_valor` text DEFAULT NULL,
  `Fecha_Registro` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_control_logs_bitacora`
--

INSERT INTO `tbl_control_logs_bitacora` (`Id`, `Id_bitacora_accion`, `Descripcion_accion`, `Ip_responsable`, `Id_usuario_responsable`, `Tabla_afectada`, `Sql_realizado`, `Valor_anterior`, `Nuevo_valor`, `Fecha_Registro`) VALUES
(1, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 09:49:45 WHERE u.Codigo = 0001;', NULL, '{\"fecha_ultimo_login\": \"2023-04-05 09:49:45\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 09:49:45'),
(2, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 09:56:21 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 09:49:45\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 09:56:21\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 09:56:21'),
(3, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 09:59:32 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 09:56:21\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 09:59:32\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 09:59:32'),
(4, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 09:59:57 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 09:59:32\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 09:59:57\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 09:59:57'),
(5, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 10:02:56 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 09:59:57\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 10:02:56\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 10:02:56'),
(6, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 10:04:00 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 10:02:56\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 10:04:00\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 10:04:00'),
(7, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 10:04:28 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 10:04:00\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 10:04:28\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 10:04:28'),
(8, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 10:07:41 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 10:04:28\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 10:07:41\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 10:07:41'),
(9, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 10:08:29 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 10:07:41\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 10:08:29\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 10:08:29'),
(10, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 10:08:47 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 10:08:29\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 10:08:47\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 10:08:47'),
(11, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 10:10:30 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 10:08:47\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 10:10:30\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 10:10:30'),
(12, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 10:14:16 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 10:10:30\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 10:14:16\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 10:14:16'),
(13, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 10:14:32 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 10:14:16\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 10:14:32\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 10:14:32'),
(14, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 10:40:08 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 10:14:32\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 10:40:08\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 10:40:08'),
(15, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 13:52:27 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 10:40:08\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 13:52:27\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 13:52:27'),
(16, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 13:53:11 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 13:52:27\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 13:53:11\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 13:53:11'),
(17, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 13:56:03 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 13:53:11\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 13:56:03\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 13:56:03'),
(18, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-05 14:00:02 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 13:56:03\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-05 14:00:02\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-05 14:00:02'),
(19, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-10 11:02:34 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 14:00:02\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-10 11:02:34\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-10 11:02:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_control_logs_bitacora_accion`
--

CREATE TABLE `tbl_control_logs_bitacora_accion` (
  `Id` int(11) NOT NULL,
  `Accion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_control_logs_bitacora_accion`
--

INSERT INTO `tbl_control_logs_bitacora_accion` (`Id`, `Accion`) VALUES
(1, 'INSERT'),
(2, 'UPDATE'),
(3, 'DELETE'),
(4, 'SELECT');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_control_tipocargo`
--

CREATE TABLE `tbl_control_tipocargo` (
  `Id` int(11) NOT NULL,
  `TipoCargo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_control_tipocargo`
--

INSERT INTO `tbl_control_tipocargo` (`Id`, `TipoCargo`) VALUES
(1, 'Profesional'),
(2, 'Administrativo'),
(3, 'Profesional y Administrativo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_usuarios`
--

CREATE TABLE `tbl_usuarios` (
  `Id` int(11) NOT NULL,
  `Codigo` varchar(6) DEFAULT NULL,
  `Clave` blob DEFAULT NULL,
  `Fecha_cambio_clave` date DEFAULT NULL,
  `Primer_nombre` varchar(20) DEFAULT NULL,
  `Segundo_nombre` varchar(20) DEFAULT NULL,
  `Primer_apellido` varchar(20) DEFAULT NULL,
  `Segundo_apellido` varchar(20) DEFAULT NULL,
  `Fecha_nacimiento` date DEFAULT NULL,
  `Id_jerarquia_cargo` int(11) DEFAULT NULL,
  `Id_jerarquia_division` int(11) DEFAULT NULL,
  `Id_direccion_parroquia` int(11) DEFAULT NULL,
  `Fecha_ingreso` datetime DEFAULT NULL,
  `Fecha_egreso` datetime DEFAULT NULL,
  `Fecha_login` datetime DEFAULT NULL,
  `Id_estatus` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_usuarios`
--

INSERT INTO `tbl_usuarios` (`Id`, `Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES
(1, '0001', 0x9f824ad4b17be95f20aa30c5eef9d8f6, '2023-05-08', 'DAVID', 'LEONARDO', 'MOLINA', 'RUÍZ', '1986-08-05', 16, 16, 1131, '2020-01-01 00:00:00', NULL, '2023-04-10 11:02:34', 1),
(2, '10', 0xcb4dc5daf4d8865eb1bd01d6c898c269, '2023-03-09', 'NATHALIE', 'YAMILET', 'LOPEZ', 'TREJO', '1972-08-20', 17, 1, 1131, '2000-02-21 00:00:00', NULL, '2023-03-08 23:41:42', 1),
(3, '10092', 0x10e54efb266e50c523273c638cb690c5, '2023-03-09', 'YESENIA', 'BEATRIZ', 'MARTINEZ', 'GALLARDO', '1979-06-01', 15, 1, 1131, '2004-09-01 00:00:00', NULL, '2023-03-07 08:27:55', 1),
(4, '10141', 0x383155d3ec475bf8ace4b67bf0aaba8d, '2023-03-09', 'JESUS', 'ERASMO', 'PEREZ', 'ERASMO', '1959-11-09', 17, 1, 1131, '2005-02-02 00:00:00', NULL, '2023-02-06 09:52:51', 1),
(5, '10168', 0xde6d03f3e06cba9372848683f12ff10a, '2023-03-09', 'CAROL', 'JOSEFINA', 'LOPEZ', 'CAMPOS', '1962-11-07', 15, 5, 1131, '2005-06-06 00:00:00', NULL, '2023-03-02 10:25:00', 1),
(6, '10367', 0x647c2b55cae32aa42154baeeb313ad40, '2023-03-09', 'LUZ', 'AMANDA', 'FONSECA', 'GARCIA', '1985-01-13', 15, 1, 1131, '2007-10-29 00:00:00', NULL, '2023-03-07 09:22:39', 1),
(7, '10473', 0x97a25906ea5c15bc553e06ec4eaed009, '2023-03-09', 'ARTURO', 'LORENZO', 'MADRIZ', 'VARGAS', '1954-12-16', 17, 1, 1131, '2008-10-14 00:00:00', NULL, '2023-02-17 14:47:21', 1),
(8, '10509', 0xb3cd6c38fa4b39dde440eff4b3bc5a22, '2023-03-09', 'ROMAN', 'ALBERTO', 'SCOTT', '', '1975-07-16', 12, 1, 1131, '2009-05-06 00:00:00', NULL, '2023-03-08 23:39:34', 1),
(9, '10572', 0x9b830d819df904d95147340117e70d44, '2023-03-09', 'OLIVER', 'JOSE', 'PAEZ', 'RANGEL', '1982-10-16', 14, 1, 1131, '2010-01-18 00:00:00', NULL, '2023-03-06 17:46:40', 1),
(10, '10721', 0x2e29e47b1ce3ad1182512298dd26328d, '2023-03-09', 'JORGE', 'ALEJANDRO', 'GONZALEZ', 'MORALES', '1990-05-19', 13, 1, 1131, '2011-11-15 00:00:00', NULL, '2023-03-08 16:07:41', 1),
(11, '10786', 0xdb3fe107f261880fdeced74b00281558, '2023-03-09', 'MARIA', 'ANDREINA', 'SEQUEDA', 'BANDES', '1990-05-30', 13, 1, 1131, '2012-07-20 00:00:00', NULL, '2023-03-08 09:13:49', 1),
(12, '10968', 0xf945dd6534898bbb32c583a4f9a58a0e, '2023-03-09', 'YODELINA', '', 'TORRES', 'MORALES', '1994-09-15', 12, 1, 1131, '2014-02-24 00:00:00', NULL, '2023-03-06 12:00:02', 1),
(13, '11030', 0x41273542b44edd82eec1262012051a0e, '2023-03-09', 'KATHERINE', 'BETHZABEL', 'ZURITA', 'CHACON', '1989-06-08', 13, 1, 1131, '2015-01-13 00:00:00', NULL, '2023-03-08 15:16:07', 1),
(14, '11044', 0x4844e22ab49ce7972d99f6ac664e58d9, '2023-03-09', 'MILEIDIS', 'ALEXANDRA', 'MORENO', 'MATUZALEM', '1992-05-30', 11, 1, 1131, '2015-01-21 00:00:00', NULL, '2023-03-07 09:12:06', 1),
(15, '11116', 0x9b5e21866d59e8c4a93c5770dbaef19b, '2023-03-09', 'FRANCIA', 'CAROLINA', 'MEDINA', 'TINEDO', '1987-03-15', 11, 1, 1131, '2015-11-04 00:00:00', NULL, '2023-03-09 08:03:58', 1),
(16, '11220', 0x9f824ad4b17be95f20aa30c5eef9d8f6, '2023-05-13', 'ASTRID', 'CAROLINA', 'MENDOZA', 'GIL', '1992-07-31', 11, 1, 1131, '2016-04-25 00:00:00', NULL, '2023-03-14 14:50:55', 1),
(17, '11314', 0x7d88c658f3db0526ec6540dbaab56a39, '2023-03-09', 'MARIA', 'GABRIELA', 'TOVAR', 'CARDENAS', '1991-11-28', 8, 1, 1131, '2017-03-27 00:00:00', NULL, '2023-03-08 09:33:10', 1),
(18, '11352', 0x0e6d3b64fffda77f987a1ca0b33692e0, '2023-03-09', 'MARIANA', 'ALEXANDRA', 'BRITO', 'SIFONTES', '1995-02-19', 11, 1, 1131, '2017-11-13 00:00:00', NULL, '2023-03-03 11:46:32', 1),
(19, '11354', 0x093bfe3fa4bf619574f56a3f4a26a7e7, '2023-03-09', 'BELKIS', 'KATERIN', 'CORTINA', 'RUIZ', '1996-12-08', 8, 1, 1131, '2017-11-13 00:00:00', NULL, '2023-03-03 18:35:27', 1),
(20, '11364', 0x3a5f20da401e227e6710acbb25ff97fc, '2023-03-09', 'LUCRECIA', 'DISNORA', 'SILVA', 'APONTE', '1989-03-15', 8, 1, 1131, '2017-12-04 00:00:00', '2021-03-02 00:00:00', NULL, 2),
(21, '11369', 0xc425904da30c5cb2dcd022ed07388c16, '2023-03-09', 'NORMEDY', 'ZORIBETH', 'PARRA', 'TOVAR', '1986-08-22', 8, 1, 1131, '2017-12-04 00:00:00', NULL, '2023-02-28 10:47:05', 1),
(22, '11371', 0xae865fdcd1ec90e2fda0a87137b009f2, '2023-03-09', 'JOSVELIS', 'YETSIMAR', 'CASTILLO', 'GIL', '1997-07-14', 9, 1, 1131, '2017-12-04 00:00:00', NULL, '2023-03-07 12:17:25', 1),
(23, '11391', 0xdfb8f58ab34354191004d62ab7308044, '2023-03-09', 'LUIS', 'ANTONIO', 'RUSSIAN', 'REQUENA', '1996-01-10', 11, 1, 1131, '2018-02-15 00:00:00', '2022-06-03 00:00:00', '2022-06-03 14:29:26', 2),
(24, '11401', 0x7e23d16c8f37bba5fbe9d04480852181, '2023-03-09', 'JONATHAN', 'JOSE', 'AZOCAR', 'RODRIGUEZ', '1994-08-24', 8, 1, 1131, '2018-02-26 00:00:00', NULL, '2023-03-03 19:06:17', 1),
(25, '11403', 0x10dae5b466840d2e283185227765dda2, '2023-03-09', 'YERLENIS', 'DELYET', 'VALDERRAMA', 'ROSALES', '1998-09-14', 11, 1, 1131, '2018-03-06 00:00:00', NULL, '2023-03-03 11:48:31', 1),
(26, '11410', 0xd2b99b2de2f70be230bc9594c37a0c61, '2023-03-09', 'KLEIVER', 'JOHANA', 'CORRO', 'GUDIÑO', '1991-02-13', 8, 1, 1131, '2018-03-06 00:00:00', NULL, '2023-02-14 14:39:57', 1),
(27, '11421', 0xe17d18eb6f7ca9b5fdd6795efe996446, '2023-03-09', 'MARYURI', 'NAILET', 'BARAZARTE', 'VALERA', '1979-09-13', 7, 1, 1131, '2018-03-26 00:00:00', '2021-04-15 00:00:00', NULL, 2),
(28, '11437', 0x7fe50132c05f1bdd77e6a4dd6d11dd7d, '2023-03-09', 'PEDRO', 'ALEXANDER', 'BENITEZ', 'MELENDEZ', '1968-06-05', 15, 1, 1131, '2018-07-01 00:00:00', NULL, '2023-02-24 17:23:36', 1),
(29, '11440', 0xc12cd8cd82284a11fb0613e0d69b9bbc, '2023-03-09', 'DENNYS', 'RAMON', 'FLORES', 'MORALES', '1981-04-20', 7, 1, 1131, '2018-07-17 00:00:00', NULL, '2023-02-28 14:51:45', 1),
(30, '11446', 0x6fe7030bcbddb93c36dbbb7ec2f31f85, '2023-03-09', 'GENESIS', 'VANESSA', 'MARCANO', 'RANGEL', '1997-10-05', 9, 1, 1131, '2018-07-25 00:00:00', '2022-07-29 00:00:00', '2022-07-23 21:55:02', 2),
(31, '11448', 0x1354569ce26ccfd4e3bebd109b44cd11, '2023-03-09', 'KEILIMAR', 'YULISET', 'SUAREZ', 'LARES', '1996-05-12', 6, 1, 1131, '2018-07-31 00:00:00', NULL, '2023-03-07 10:50:50', 1),
(32, '11452', 0x2c6f8320e663f56932482e2c34bfba3f, '2023-03-09', 'JOHANNE', 'FRANCIS', 'MUÑOZ', 'MARTINEZ', '1981-07-22', 13, 1, 1131, '2018-08-15 00:00:00', NULL, '2023-03-06 20:50:16', 1),
(33, '11453', 0x88437b100005a2c216a35b10ac06ac89, '2023-03-09', 'ALFREDO', 'JOSE', 'HERNANDEZ', 'TORO', '1969-03-14', 9, 1, 1131, '2018-08-14 00:00:00', NULL, '2023-03-08 16:17:07', 1),
(34, '11457', 0x73dd6433a0272bf8446bc3267e7d3f12, '2023-03-09', 'RAUL', 'IGNACIO', 'VARGAS', 'FREITES', '1976-01-29', 17, 1, 1131, '2018-10-18 00:00:00', NULL, '2023-02-23 14:51:55', 1),
(35, '11466', 0xb0bd668ff196e3be8ba74828d811dc57, '2023-03-09', 'SHELCIE', 'ESTHER', 'PAZ', '', '1997-03-22', 7, 1, 1131, '2018-11-08 00:00:00', NULL, '2023-02-24 14:04:45', 1),
(36, '11467', 0xe2639ee3113fd0cc23992b5b24daf64b, '2023-03-09', 'LADYMAR', '', 'MORETT', 'RONDON', '1983-03-18', 12, 1, 1131, '2018-11-20 00:00:00', '2022-12-29 00:00:00', '2022-12-29 13:40:52', 2),
(37, '11469', 0xc36653810196652a8c5255973c9784a4, '2023-03-09', 'ANTHONY', 'ROBERT', 'GARCIA', 'CHAPARRO', '1991-06-26', 7, 1, 1131, '2018-11-12 00:00:00', NULL, NULL, 2),
(38, '11480', 0xa577eb8769d5d83cddcd25fae3cab295, '2023-03-09', 'SOLMARY', 'DEL VALLE', 'MARTINEZ', 'MARCHAN', '1983-08-03', 12, 1, 1131, '2018-12-17 00:00:00', NULL, '2023-03-03 11:59:13', 1),
(39, '11481', 0x39c32f5b07662274e35c4246a978d69f, '2023-03-09', 'JACKELINE', 'ZULEYMA MILAGROS', 'RAMOS', 'PEÑA', '1989-06-02', 6, 1, 1131, '2018-12-18 00:00:00', NULL, '2023-03-02 12:34:38', 1),
(40, '11484', 0x67328548d73e014e6b5c5ab14ed5b9c1, '2023-03-09', 'BELKIS', 'EDICTA', 'VAZQUEZ', 'MORALES', '1984-07-17', 6, 1, 1131, '2019-01-07 00:00:00', '2020-09-30 00:00:00', NULL, 2),
(41, '11487', 0x75939b253f15aad914fdf356bed1d590, '2023-03-09', 'YUZLEIBBY', 'ANGELICA', 'MALDONADO', 'ROSALES', '1996-10-08', 7, 1, 1131, '2019-01-21 00:00:00', NULL, '2023-03-01 09:58:01', 1),
(42, '11490', 0x95a11af2e293e1ebe91c1cb2342f1af5, '2023-03-09', 'GIOVANNI', 'JESUS', 'CORREDOR', 'SANOJA', '1996-07-07', 10, 1, 1131, '2019-01-24 00:00:00', NULL, '2023-03-02 13:26:42', 1),
(43, '11493', 0xf4be463bfe681a054e9c37164a42879b, '2023-03-09', 'KLEIVER', 'JOSE', 'CADENAS', 'QUIÑONEZ', '1995-05-02', 9, 1, 1131, '2019-02-04 00:00:00', NULL, '2023-03-08 10:57:21', 1),
(44, '11494', 0x372914d41de86dd92703cd0dee53bc62, '2023-03-09', 'IVETTE', 'ALEJANDRA', 'OROZCO', 'FLORES', '1994-02-23', 12, 1, 1131, '2019-02-04 00:00:00', '2021-03-16 00:00:00', NULL, 2),
(45, '11497', 0xbc51997c6750d92815ec3bd08f4c241c, '2023-03-09', 'ZUNAYA', 'ESTHER', 'WILCHES', 'OLAVE', '1996-12-05', 4, 1, 1131, '2019-02-07 00:00:00', '2021-04-16 00:00:00', NULL, 2),
(46, '11499', 0x1b42160c619d522204261b5be95cb8c6, '2023-03-09', 'JESUS', 'ALBERTO', 'ABRAHAM', 'CORONADO', '1994-06-21', 10, 1, 1131, '2019-02-21 00:00:00', '2022-05-05 00:00:00', '2022-05-05 15:33:10', 2),
(47, '11503', 0x06e477466a371d1eacd75ae15905d43d, '2023-03-09', 'JOSE', 'MIGUEL', 'PEROZO', 'HERRERA', '1994-10-04', 9, 1, 1131, '2019-03-07 00:00:00', '2022-01-19 00:00:00', '2022-01-18 11:19:04', 2),
(48, '11504', 0x6aa697bff06db4b84bdbc82311fffc2e, '2023-03-09', 'ROBERTO', 'RAFAEL', 'VILLEGAS', 'GONZALEZ', '1988-09-26', 8, 1, 1131, '2019-03-20 00:00:00', '2021-12-03 00:00:00', '2021-12-03 11:16:58', 2),
(49, '11507', 0x3a2c0ef6056afb94c39299ab96d4cd94, '2023-03-09', 'SANDRO', 'YOEL', 'MAYORA', '', '1973-09-17', 11, 6, 1131, '2019-04-01 00:00:00', NULL, '2023-03-02 10:07:29', 1),
(50, '11519', 0xd18f8bd03a2fe1a887614b386c66c450, '2023-03-09', 'EDUARDO', '', 'BASTOS', 'RICCIO', '1989-06-27', 6, 1, 1131, '2019-07-10 00:00:00', '2021-12-01 00:00:00', '2021-11-30 23:49:08', 2),
(51, '11520', 0x913bfe28eb609fcaa63aecb1e18f4c67, '2023-03-09', 'VANESSA', 'VALENTINA', 'ROJAS', 'MORALES', '1987-12-23', 7, 1, 1131, '2019-07-16 00:00:00', NULL, '2023-03-03 10:08:01', 1),
(52, '11527', 0x9831d9046b6a2d18bd625df5a5d3ed45, '2023-03-09', 'CARLOS', 'ALBERTO', 'REVETE', 'CARVALLO', '1994-09-18', 7, 1, 1131, '2019-12-09 00:00:00', NULL, '2023-02-13 09:06:55', 1),
(53, '11528', 0x7c589ad8221957631d159f5b6350a950, '2023-03-09', 'VIANNEY', 'DEL VALLE', 'RUGELES', 'MANTILLA', '1972-01-08', 8, 1, 1131, '2019-12-09 00:00:00', '2022-10-28 00:00:00', '2022-10-25 17:08:43', 2),
(54, '11529', 0x8b7592142278c43d1c235569d6a0bea2, '2023-03-09', 'EDWIN', 'JESUS', 'BURGOS', 'GOMEZ', '1987-12-06', 4, 1, 1131, '2019-12-09 00:00:00', '2021-01-11 00:00:00', NULL, 2),
(55, '11535', 0x9592acdded4437d994ee5f1a14a7d4b1, '2023-03-09', 'ENIL', 'ALEJANDRO', 'MOLINA', 'YDROGO', '2002-02-16', 7, 1, 1131, '2020-03-09 00:00:00', NULL, '2023-03-07 13:47:54', 1),
(56, '22', 0x09ccd8b97d37abf3003cb7bdb7fee97c, '2023-03-09', 'FREDDY', 'RODOLFO', 'VARGAS', 'HERNANDEZ', '1969-10-22', 15, 1, 1131, '2000-08-01 00:00:00', NULL, '2023-03-07 20:26:21', 1),
(57, '6060', 0xe628f6988aba3b5c31751f4d5d521522, '2023-03-09', 'YORMAN', 'ISMAEL', 'RANGEL', 'GONZALEZ', '1983-08-15', 14, 1, 1131, '2014-07-01 00:00:00', '2023-01-31 00:00:00', '2023-01-31 15:10:28', 2),
(58, '10783', 0x75fd9de136418348f0c0a53a98a51181, '2023-03-09', 'JOSE', 'MIGUEL', 'UTRERA', 'ROJAS', '1975-04-02', 16, 2, 1131, '2012-07-16 00:00:00', NULL, '2023-03-09 08:06:36', 1),
(59, '11485', 0xa9ce0f2bf56800d4cb028ddbc2b6b829, '2023-03-09', 'ALEJANDRO', 'ENRIQUE', 'LIRA', 'TOVAR', '1995-06-27', 7, 2, 1131, '2019-01-09 00:00:00', '2021-05-21 00:00:00', '2021-04-29 20:13:20', 2),
(60, '11505', 0xa33fb3c2617b1e2cc5d944c0b4c0859d, '2023-03-09', 'YORDALIS', 'GABRIELA', 'ECHARRYS', 'CABRILES', '1993-08-02', 5, 2, 1131, '2019-04-01 00:00:00', '2021-01-15 00:00:00', NULL, 2),
(61, '11506', 0x2684acb76beb6afaa2e468c39fd814bb, '2023-03-09', 'ELIANA', 'MARIA', 'PONCE', 'VARGAS', '1971-03-14', 14, 2, 1131, '2019-04-08 00:00:00', '2022-02-15 00:00:00', '2022-02-16 11:08:46', 2),
(62, '11514', 0x103eef319babf7b08f06a819046edfec, '2023-03-09', 'STEFANY', 'YANETH', 'GONZALEZ', 'MIJARES', '1995-02-22', 6, 2, 1131, '2019-06-03 00:00:00', NULL, '2021-12-09 07:56:47', 2),
(63, '11521', 0x3bd7662f26d22e0c32de54ef0569807a, '2023-03-09', 'NAIVELYS', 'GABRIELA', 'ALTUVE', 'TORRES', '1991-06-20', 13, 2, 1131, '2019-09-02 00:00:00', NULL, '2023-02-23 21:40:01', 1),
(64, '11522', 0xf663e5da56b6035105e428560a01ff4d, '2023-03-09', 'GABRIELA', 'DEL VALLE', 'GIL', 'LA PIETRA', '1996-05-09', 6, 2, 1131, '2019-09-02 00:00:00', '2022-06-08 00:00:00', '2022-06-07 16:12:15', 2),
(65, '11526', 0x442ce2f8fe72b1614fb2bcfe9cd6919b, '2023-03-09', 'ORIANNA', 'DESSIREE', 'ALEJOS', 'FIGUEREDO', '1996-05-23', 4, 2, 1131, '2019-11-18 00:00:00', '2022-01-14 00:00:00', '2022-01-18 16:00:14', 2),
(66, '11533', 0x5bd05cb4c18042365c51faf72a87af3e, '2023-03-09', 'MARYNES', 'DEL VALLE', 'GONZALEZ', 'MENDOZA', '1997-03-06', 3, 2, 1131, '2020-03-09 00:00:00', NULL, NULL, 2),
(67, '10794', 0x29f82578b0c53e954eed0b971a75b555, '2023-03-09', 'ELIGIO', 'HORACIO', 'MENDOZA', 'ODREMAN', '1970-10-23', 15, 4, 1131, '2012-08-01 00:00:00', NULL, NULL, 2),
(68, '10838', 0xf3ba845c6f332ecf550b6ffd0b524ba2, '2023-03-09', 'MARIELVI', '', 'OLLER', 'MENDOZA', '1986-07-11', 12, 4, 1131, '2013-01-23 00:00:00', NULL, NULL, 2),
(69, '111426', 0xaa924ae19b4789f2d1989149f718cbfb, '2023-03-09', 'ALBA', 'JEANNETH', 'NAVIA', 'BERMUDEZ', '1976-07-22', 12, 4, 1131, '2018-05-01 00:00:00', '2023-01-10 00:00:00', NULL, 2),
(70, '11344', 0x16fdfb50b2e08cd162dcb4b2b59904b4, '2023-03-09', 'NATHASHA', 'ESTEFANIA', 'FRANCO', 'BERMUDEZ', '1996-02-03', 9, 4, 1131, '2017-10-13 00:00:00', '2020-11-06 00:00:00', NULL, 2),
(71, '11353', 0x5621af456fefd10655d92835e1bb1fc9, '2023-03-09', 'YESSICA', 'LAURA', 'RIVAS', 'TURMERO', '1990-11-26', 11, 4, 1131, '2017-11-13 00:00:00', NULL, NULL, 2),
(72, '11366', 0xa8b0fb715bbcda315cc265b24eb0f913, '2023-03-09', 'FRAYNER', 'ALEXANDER', 'RANGEL', 'VALERO', '1993-04-17', 8, 4, 1131, '2017-12-04 00:00:00', '2021-01-11 00:00:00', NULL, 2),
(73, '11374', 0x3f5dc1ab52521d40452ee0d1e487124f, '2023-03-09', 'YDA', 'MERCEDES', 'CHIRINOS', 'VILORIA', '1983-09-28', 8, 1, 1131, '2017-12-04 00:00:00', '2022-03-15 00:00:00', '2022-03-16 12:29:26', 2),
(74, '11411', 0xecb2621b72dfb2d72b3f056cf39bcd92, '2023-03-09', 'GENESIS', 'GABRIELA', 'BARRIOS', 'VILORIA', '1998-07-25', 11, 4, 1131, '2018-03-06 00:00:00', NULL, NULL, 2),
(75, '11458', 0xdc40f17aaba2b35d115af903eafe41d5, '2023-03-09', 'RUDDY', 'ISAMAR', 'PINTO', 'COLMENARES', '1990-05-06', 10, 4, 1131, '2018-10-16 00:00:00', NULL, NULL, 2),
(76, '11459', 0x02372563f1ba55068035f4ae232d7865, '2023-03-09', 'CARLOS', 'EDUARDO', 'RODRIGUEZ', '', '1966-03-15', 9, 4, 1131, '2018-10-16 00:00:00', '2021-09-03 00:00:00', NULL, 2),
(77, '11471', 0xbf9a856ccb6329e806ad2a89a212c663, '2023-03-09', 'CARMEN', 'ELENA', 'BERRIOS', 'BASTIDAS', '1989-07-16', 9, 4, 1131, '2018-11-15 00:00:00', '2022-10-14 00:00:00', NULL, 2),
(78, '11472', 0x54d8b1a8bf708d1d3ff92d0b1da54fc9, '2023-03-09', 'GERALDINE', 'DESIREE', 'RUIZ', 'HENRIQUEZ', '1975-10-09', 11, 4, 1131, '2018-11-26 00:00:00', '2021-03-01 00:00:00', NULL, 2),
(79, '11482', 0xd3caab46800986a021e4a8894ec560a9, '2023-03-09', 'NAHOMY', 'NAZARETH', 'QUINTERO', 'MARTINEZ', '1998-08-13', 7, 4, 1131, '2018-12-17 00:00:00', '2022-09-16 00:00:00', NULL, 2),
(80, '11510', 0x0559d0ac9c93cd019ab82ff19bd88135, '2023-03-09', 'MARIA', 'ISABEL', 'ESPINA', 'URBINA', '1966-12-09', 11, 4, 1131, '2019-04-29 00:00:00', NULL, NULL, 2),
(81, '11513', 0xa01a6a426395dee14f270d10c8e4bbde, '2023-03-09', 'ANGELO', 'ALFONSO', 'MARTINEZ', 'BERROTERAN', '1990-02-05', 21, 19, 1131, '2019-06-03 00:00:00', '2021-08-16 00:00:00', '2021-06-30 10:40:47', 2),
(82, '11523', 0x87afef94bfbedb9c0b9384d051b57ae4, '2023-03-09', 'MANUEL', 'ALEJANDRO', 'DA SILVA', 'VILLAMISIL', '1984-12-04', 9, 4, 1131, '2019-10-01 00:00:00', '2021-09-02 00:00:00', NULL, 2),
(83, '111431', 0x387232dbac8c4712b93902412b45116b, '2023-03-09', 'GLENDER', 'JESUS', 'CORTEZ', '', '1990-11-05', 12, 6, 1131, '2018-06-25 00:00:00', '2021-08-18 00:00:00', '2021-08-18 11:14:42', 2),
(84, '11267', 0xb42d3da5f0495e13078d701bdb3139c5, '2023-03-09', 'ALBERTO', 'JOSE', 'EVIES', 'GONZALEZ', '1965-11-04', 13, 5, 1131, '2016-10-03 00:00:00', NULL, '2021-12-27 13:15:54', 1),
(85, '11291', 0x9f824ad4b17be95f20aa30c5eef9d8f6, '2023-05-13', 'ANGELA', 'LEONOR', 'ARANEA', 'CHICA', '1976-01-30', 14, 6, 1131, '2016-12-12 00:00:00', NULL, '2023-03-20 10:52:06', 1),
(86, '11346', 0x3741710cf81161c3f808ab6763e6dd46, '2023-03-09', 'ARTURO', 'ARMANDO', 'SOSA', 'HERRERA', '1962-08-27', 12, 1, 1131, '2017-11-01 00:00:00', NULL, '2023-03-08 13:23:34', 1),
(87, '11414', 0xfc273bdbdb272d4d8242dc839fe574c0, '2023-03-09', 'ADRIAN', 'ALEXANDER', 'PEREZ', 'RODRIGUEZ', '1994-04-19', 11, 5, 1131, '2018-03-16 00:00:00', NULL, '2023-03-01 08:31:34', 1),
(88, '11443', 0x10345a029cfad2c3590a1b9a451d1f1d, '2023-03-09', 'ELISA', 'MARIBEL', 'PASERO', 'MARIÑO', '1979-08-25', 8, 1, 1131, '2018-07-19 00:00:00', NULL, '2023-03-07 12:16:33', 1),
(89, '11463', 0x35940ed319e855068bde5ef0cfce1223, '2023-03-09', 'OMAR', 'ALFONSO', 'MARQUEZ', 'RODRIGUEZ', '2000-03-04', 9, 6, 1131, '2018-11-05 00:00:00', NULL, '2023-03-03 14:05:02', 1),
(90, '11474', 0x8ddc468ceb92806447b051cf861ebc35, '2023-03-09', 'ANGELICA', 'ESTEFANIA', 'FUNES', 'OLOYOLA', '1995-06-27', 9, 6, 1131, '2018-11-26 00:00:00', '2022-12-02 00:00:00', '2022-12-02 10:12:19', 2),
(91, '11492', 0xdb97f3afbcc4d25a1430ceba77fa6a62, '2023-03-09', 'ESLYN', 'MILEYDIS', 'ROJAS', 'ROMERO', '1989-03-25', 8, 5, 1131, '2019-02-11 00:00:00', '2019-02-11 00:00:00', '2022-02-10 11:34:30', 2),
(92, '10135', 0x02c9275997d54158c4167be77a6630ce, '2023-03-09', 'CARMEN', 'VESTALIA', 'OCHOA', '', '1941-01-09', 19, 7, 1131, '2005-01-24 00:00:00', NULL, '2023-03-07 12:27:30', 1),
(93, '10446', 0x4d1824cf8b7a77fed908c76ab6489714, '2023-03-09', 'LAURA', 'YAMILET', 'ROJAS', 'LIZARRAGA', '1974-09-28', 12, 10, 1131, '2008-07-23 00:00:00', NULL, '2023-03-08 10:38:18', 1),
(94, '10466', 0xa1198b470a81e90dac0bed1b309401a8, '2023-03-09', 'ANTONIO', 'JOSE', 'RUBIO', 'HERNANDEZ', '1967-12-11', 22, 7, 1131, '2008-10-03 00:00:00', NULL, '2023-03-07 12:30:43', 1),
(95, '10559', 0x0085f93347f0189fcb05c3a2c63cfb37, '2023-03-09', 'RUBEN', 'DARIO', 'VERA', 'PATIÑO', '1983-01-19', 37, 11, 1131, '2010-01-18 00:00:00', NULL, '2023-03-07 13:11:36', 1),
(96, '10568', 0xdc1728d6fab31e62277d87cb0baef850, '2023-03-09', 'LUISA', 'ESTHER', 'TOVAR', '', '1964-04-09', 24, 7, 1131, '2010-01-18 00:00:00', NULL, '2023-03-07 12:32:46', 1),
(97, '10589', 0x0e68f89affeeb43d6c64e3c45142cdf4, '2023-03-09', 'JOSE', 'ANTONIO', 'MACHADO', 'PEREZ', '1967-08-19', 14, 9, 1131, '2010-02-22 00:00:00', NULL, '2023-03-02 14:51:16', 1),
(98, '10775', 0x21099c58c60dbd911af236bab605382f, '2023-03-09', 'DULY', 'YOSMILA', 'RINCONES', '', '1980-09-12', 25, 7, 1131, '2012-04-30 00:00:00', NULL, '2023-03-07 12:34:59', 1),
(99, '10776', 0x007d9490d21daffd11ed0ee4545799d7, '2023-03-09', 'YENNIFER', 'MARIANA', 'VILLA', 'ANGEL', '1988-11-24', 19, 7, 1131, '2012-05-08 00:00:00', NULL, '2023-03-07 13:04:44', 1),
(100, '10777', 0x72885907032c6a6a16024d7b6d6bda9a, '2023-03-09', 'ANA', 'CECILIA', 'CASTAÑO', 'ESCOBAR', '1946-10-10', 19, 2, 1131, '2012-05-16 00:00:00', NULL, '2023-03-01 08:30:46', 1),
(101, '10896', 0xf0c9f1a5e0ceba8ff174aee2e655b8d6, '2023-03-09', 'AMAYOISBI', 'LIDSAY', 'GARCIA', 'CHACIN', '1972-07-12', 12, 12, 1131, '2013-08-08 00:00:00', NULL, '2023-03-08 10:58:13', 1),
(102, '10897', 0x622f783e5cb68f8539a07d2e72fc5181, '2023-03-09', 'JENNIFER', 'LETICIA', 'CHACON', 'ZAMBRANO', '1985-02-21', 26, 12, 1131, '2013-08-19 00:00:00', NULL, '2023-03-08 10:44:05', 1),
(103, '10977', 0x1917192b32463935bdb37b5424da609a, '2023-03-09', 'IGNAYARI', 'KATHERINE', 'MENDOZA', 'LUZARDO', '1991-06-11', 29, 7, 1131, '2014-06-05 00:00:00', NULL, '2023-03-07 15:48:08', 1),
(104, '11145', 0x251a3d0f0933d817a00a114a54306788, '2023-03-09', 'REINA', 'MARIA', 'FAJARDO', 'GUERRERO', '1998-03-10', 31, 7, 1131, '2015-11-25 00:00:00', '2021-07-23 00:00:00', '2021-06-23 12:27:39', 2),
(105, '11159', 0x39f7a594369157e26555f4e0d7a1a659, '2023-03-09', 'YOLYMER', 'ALICIA', 'MENDOZA', 'GARCIA', '1973-10-29', 14, 7, 1131, '2015-12-18 00:00:00', NULL, '2023-03-09 08:37:39', 1),
(106, '11208', 0x7758b701cf34e5dd05b290c5902eb3dc, '2023-03-09', 'ROSA', 'ESMERALDA', 'LUZARDO', 'CARDENAS', '1965-08-28', 24, 7, 1131, '2016-03-14 00:00:00', NULL, '2023-03-07 13:05:55', 1),
(107, '11292', 0x9834a2cdd6f9db0a0d250bffac8c7ade, '2023-03-09', 'ADRIANA', '', 'GUZMAN', 'LA CRUZ', '1982-06-18', 26, 12, 1131, '2016-12-12 00:00:00', '2020-08-24 00:00:00', NULL, 2),
(108, '11423', 0x82ecb7a4d460cbd9b5fccb7bba696837, '2023-03-09', 'JOSE', 'LUZARDO', 'ESTABA', 'MOTA', '1988-04-08', 12, 13, 1131, '2018-04-09 00:00:00', NULL, '2023-03-09 09:05:27', 1),
(109, '11438', 0xe1f58ad538cd74ec3753e32286efb835, '2023-03-09', 'KARINA', '', 'PEREZ', 'MARQUES', '1993-08-09', 27, 19, 1131, '2018-07-09 00:00:00', NULL, '2023-02-24 09:39:49', 1),
(110, '11455', 0x54dc3edc08196bdd90fedc8827492cfa, '2023-03-09', 'ZONNY', 'EDUARDO', 'GARCIA', 'OJEDA', '1993-08-30', 35, 13, 1131, '2018-08-21 00:00:00', '2021-03-03 00:00:00', NULL, 2),
(111, '11473', 0x52e0d97d3cc24e1500df52296fdc3338, '2023-03-09', 'YAINE', 'ALEXANDER', 'MACHADO', 'PEREZ', '1981-06-12', 31, 11, 1131, '2018-11-26 00:00:00', '2022-01-24 00:00:00', '2022-01-21 12:51:42', 2),
(112, '11498', 0x48a6a5ef107959d068a87c41dd289746, '2023-03-09', 'ANTONIO', 'ALEXANDER', 'FARIA', 'EXPOSITO', '1983-08-28', 31, 11, 1131, '2019-02-18 00:00:00', '2021-08-02 00:00:00', '2021-07-06 11:41:46', 2),
(113, '11524', 0xb88c861c64fbca0f3ed2921c92dc2d58, '2023-03-09', 'LEONARDO', 'ANTONIO', 'LOPEZ', 'AGURTO', '2001-10-29', 32, 9, 1131, '2019-10-01 00:00:00', NULL, '2023-03-09 08:59:15', 1),
(114, '11525', 0xeb3607196b0db72c9d8b9c7cdefe7175, '2023-03-09', 'JOSE', 'ARTURO', 'MADRIZ', 'MALAVE', '1996-06-07', 2, 19, 1131, '2019-11-04 00:00:00', NULL, '2021-04-25 20:53:48', 2),
(115, '11530', 0x3fd6b034156ce56e3063c7aceda2129b, '2023-03-09', 'LILIANA', 'IBETH', 'PARRA', 'PEREZ', '1980-05-21', 25, 7, 1131, '2020-01-29 00:00:00', '2021-11-30 00:00:00', '2021-11-16 14:18:04', 2),
(116, '11531', 0x11d4863f0228a0f15971811d319899c8, '2023-03-09', 'ANTONIO', 'JOSE', 'REYES', 'SEQUERA', '1959-12-31', 15, 19, 1131, '2020-02-03 00:00:00', NULL, '2023-03-08 10:49:51', 1),
(117, '11532', 0xd04d0184b0216d42ec38d51300a5fb0e, '2023-03-09', 'DUVAN', 'RAFAEL', 'PINTO', 'JAIMES', '2000-02-07', 3, 1, 1131, '2020-02-26 00:00:00', NULL, '2023-03-07 14:38:19', 1),
(118, '11534', 0xf3143b800ced9f5b915d90b9cc7ae737, '2023-03-09', 'FREDDY', 'FRANCISCO', 'PERDOMO', 'MOLINA', '1986-03-03', 22, 7, 1131, '2020-03-01 00:00:00', NULL, NULL, 2),
(119, '11536', 0xd995664289ab091b92900d8c3d725211, '2023-03-09', 'FERNANDO', 'JOSE', 'RANGEL', 'KUIPPERS', '1992-12-12', 12, 19, 1131, '2020-03-16 00:00:00', NULL, '2023-03-08 10:48:33', 1),
(120, '11537', 0xdac7c278f04cd6bcfc2e8cd1327ba186, '2023-03-09', 'GELEN', 'DEL ROSARIO', 'CARDENAS', 'MARQUEZ', '1958-03-08', 23, 7, 1131, '2020-06-01 00:00:00', NULL, '2023-03-09 09:29:51', 1),
(121, '11538', 0x5f6cefe297d88422fd26fc327927c50c, '2023-03-09', 'FREDDY', 'ANTONIO', 'BORRERO', 'CONTRERAS', '1989-08-09', 24, 11, 1131, '2020-06-01 00:00:00', NULL, NULL, 2),
(122, '11539', 0xf3a4e6e99f2351d7d8d7dbe13a8613c6, '2023-03-09', 'AURA', 'MARIA', 'CONTRERAS', 'PASTRAN', '1968-07-01', 24, 7, 1131, '2020-06-01 00:00:00', NULL, '2023-03-07 13:07:06', 1),
(123, '36', 0xaff067db4d01053de5e9b63e3acad0f3, '2023-03-09', 'JESUS', 'SALVADOR', 'MORILLO', 'QUINTANA', '1960-03-02', 12, 11, 1131, '2000-01-17 00:00:00', NULL, '2023-03-07 13:12:35', 1),
(124, '49', 0x691bc08a9481d285f219ba498b40c2d8, '2023-03-09', 'AMELIA', 'JOSEFINA', 'DIAZ', 'MENDOZA', '1956-03-19', 20, 7, 1131, '2004-11-01 00:00:00', NULL, '2023-03-09 09:29:05', 1),
(125, '10195', 0xb9ce83958d6a23a8336406193a64ff01, '2023-03-09', 'EMILIO', 'JOSE', 'LEON', 'FARIAS', '1965-06-28', 15, 3, 1131, '2005-11-01 00:00:00', NULL, '2023-03-08 10:55:19', 1),
(126, '11265', 0xa0bd6599e6e683811b6372401e231dd8, '2023-03-09', 'GUSTAVO', 'ADOLFO', 'PUCHI', 'MEDINA', '1963-09-12', 14, 3, 1131, '2016-10-03 00:00:00', NULL, '2023-02-07 22:21:36', 1),
(127, '11376', 0xdf4810c7bef897842d6da0a50067246d, '2023-03-09', 'ALFIO', 'FILIPPO', 'SAGLIMBENI', 'MUSCOLINO', '1967-08-03', 13, 3, 1131, '2017-12-20 00:00:00', NULL, '2023-03-07 14:54:06', 1),
(128, '11397', 0xe5ca561acfe5b7e6d08fe901bc7949f5, '2023-03-09', 'ARIANNA', 'ELENA', 'MATOS', 'IACOBELLIS', '1995-08-21', 12, 3, 619, '2018-02-20 00:00:00', NULL, '2023-03-09 09:05:36', 1),
(129, '11450', 0xbae93c1b60db15e5865266fd0d24633a, '2023-03-09', 'ANA', 'VIRGINIA', 'BLANDIN', 'ARZOLA', '1981-04-08', 12, 3, 1131, '2018-08-07 00:00:00', NULL, '2023-03-03 14:51:42', 1),
(130, '10262', 0xb30631929b9c596b6b7c93bc0107a2f9, '2023-03-09', 'OSCAR', 'AUGUSTO', 'PIÑA', 'ALBUJAR', '1946-01-06', 15, 14, 1131, '2006-01-02 00:00:00', NULL, '2021-05-18 10:10:44', 1),
(131, '11278', 0x610144861cabaf4a843655e1382b4ba1, '2023-03-09', 'YOSBER', 'ALEJANDRO', 'GOMEZ', 'LANDAETA', '1997-12-02', 41, 15, 1131, '2016-11-01 00:00:00', NULL, NULL, 2),
(132, '11280', 0x2cad3164e6c307fdcb776bd810cb7581, '2023-03-09', 'DUGLIMAR', 'YOLEIDA', 'MENDEZ', 'RIVAS', '1999-07-02', 7, 1, 1131, '2016-11-16 00:00:00', '2022-06-30 00:00:00', '2022-07-11 12:14:10', 2),
(133, '11312', 0xa7c857f013892adc7f221598f2e89d13, '2023-03-09', 'SOL', 'PATRICIA', 'VIANA', 'CONSUEGRA', '1997-09-23', 5, 17, 1131, '2017-03-20 00:00:00', NULL, '2023-03-09 10:17:23', 1),
(134, '11063', 0x3b313b352d415598974619e53a931dab, '2023-03-09', 'DOUGLAS', 'EDUARDO', 'TORREALBA', 'SANCHEZ', '1975-10-28', 42, 10, 1131, '2015-06-02 00:00:00', NULL, '2023-02-02 10:13:31', 1),
(135, '11064', 0xf8d7bcf36c42372b56e93e294887f37f, '2023-03-09', 'DARWING', 'JOSE', 'CORDOVA', '', '1980-08-04', 40, 16, 1131, '2015-06-02 00:00:00', NULL, NULL, 1),
(136, '11066', 0x41399ac78c24e3559f7726613d12ebd7, '2023-03-09', 'JEFERSON', 'JESUS', 'YANEZ', 'VILLEGAS', '1995-10-12', 40, 16, 1131, '2015-06-02 00:00:00', NULL, NULL, 1),
(137, '11068', 0x5a0c077e3b5d51173617b9505e5096a0, '2023-03-09', 'JOSE', 'ANTONIO', 'ARAUJO', 'RODRIGUEZ', '1989-05-30', 40, 16, 1131, '2015-06-02 00:00:00', NULL, NULL, 1),
(138, '11236', 0x9539088bb755d49549bae8545139afef, '2023-03-09', 'ANGEL', 'EDUARDO', 'APARICIO', 'ROMERO', '1970-08-02', 40, 16, 1131, '2016-05-20 00:00:00', NULL, NULL, 1),
(139, '11237', 0xe9ab85bfeff30e5d550382afc6080220, '2023-03-09', 'JESUS', 'ANTONIO', 'ROJAS', 'CRUZ', '1984-07-18', 40, 16, 1131, '2016-05-20 00:00:00', NULL, NULL, 1),
(140, '10508', 0x2d56c9afa7ed653edb2aaa6ae8f27296, '2023-03-09', 'FREDY', 'SAMUEL', 'BAUTISTA', 'VILLEGAS', '1950-05-14', 15, 17, 1131, '2005-08-01 00:00:00', NULL, '2023-03-09 10:23:26', 1),
(141, '10689', 0x405c1a039a181f42019aa2982793c531, '2023-03-09', 'ELLEN', 'KATIUSKA', 'FUENTES', 'RIOS', '1966-03-16', 33, 18, 1131, '2007-02-26 00:00:00', NULL, NULL, 2),
(142, '11451', 0x5721015667f69f6abe55a7234ee28807, '2023-03-09', 'BARBARA', 'CAROLINA', 'ZAMBRANO', 'AGUINALDE', '1996-11-19', 6, 18, 1131, '2018-08-01 00:00:00', '2021-07-01 00:00:00', NULL, 2),
(143, '11476', 0xbe3ff23ed8054d2728572e354edf7671, '2023-03-09', 'MARY', '', 'CRUZ', 'SALAZAR', '1989-09-20', 12, 18, 1131, '2018-12-03 00:00:00', '2021-07-01 00:00:00', NULL, 2),
(144, '10863', 0x63ed7aeb031c9314dd620e19e77c79e2, '2023-03-09', 'SERGIO', 'FREDDYS', 'MÁRQUEZ', 'TOVAR', '1971-12-31', 16, 1, 1131, '2013-05-02 00:00:00', NULL, '2023-03-08 12:28:02', 1),
(145, '29', 0x479f7317068099dcf2a111b30db251df, '2023-03-09', 'NELSON', 'JOSE', 'MARCANO', '', '1969-09-20', 16, 1, 1131, '2000-10-26 00:00:00', NULL, '2023-03-08 12:26:18', 1),
(146, '5002', 0xaef4634cdd03090997802d1839eb6c5a, '2023-03-09', 'SAMUEL', 'ALEJANDRO', 'MARQUEZ', 'TOVAR', '1966-06-28', 16, 1, 1131, '1999-07-01 00:00:00', NULL, '2023-03-07 12:23:29', 1),
(147, '5014', 0x75a1d6a76e48987599d8e331c08d260b, '2023-05-19', 'ANTONIO', 'JOSE', 'DUGARTE', 'LOBO', '1964-07-23', 16, 2, 1131, '2011-03-16 00:00:00', NULL, '2023-03-20 10:58:28', 1),
(148, '107', 0x4487799a782e07416359afcb8a42fb84, '2023-03-09', 'MIRNANGELA', 'LARISKA', 'SALAYA', 'GARCIA', '1977-11-08', 16, 1, 1131, '2000-08-08 00:00:00', NULL, '2023-03-08 11:15:29', 1),
(149, '5003', 0x9d880ffd30f5f080be310c589ad7d227, '2023-03-09', 'JOSE', 'NICOLAS', 'MARQUEZ', 'CEJAS', '1962-02-11', 16, 1, 1131, '2007-08-01 00:00:00', NULL, '2023-03-01 14:33:55', 1),
(150, '5007', 0xc371ab40bb31c34e95ae4516d646a82f, '2023-03-09', 'FREDDY', 'FRANCISCO', 'PERDOMO', '', '1949-05-17', 16, 1, 1131, '2008-08-01 00:00:00', NULL, '2023-03-08 15:20:24', 1),
(151, '6146', 0x9dae052e8459779324727042c745d5c0, '2023-03-09', 'ROBINSON', 'JOSE', 'ARANGUREN', 'MAESTRE', '1970-11-15', 1, 1, 1131, '2019-09-02 00:00:00', NULL, '2022-05-29 16:47:14', 1),
(152, '6128', 0x03cb2670612f39a496a117e7b4132624, '2023-03-09', 'JOSE', 'ANTONIO', 'ECKER', 'RANGEL', '1968-10-19', 37, 11, 1131, '2018-07-02 00:00:00', '2019-11-02 00:00:00', NULL, 1),
(153, '6145', 0xb8fc0ae4e80a2cbdf90ba796266bc350, '2023-03-09', 'JHON', 'EDUARDO', 'RONDON', 'BARRERA', '1969-08-19', 1, 1, 648, '2019-08-14 00:00:00', NULL, NULL, 2),
(154, '11540', 0x63ed7aeb031c9314dd620e19e77c79e2, '2023-03-09', 'SERGIO', 'FREDDYS', 'MÁRQUEZ', 'TOVAR', '1971-12-31', 16, 10, 612, '2013-05-01 00:00:00', NULL, NULL, 1),
(155, '6149', 0x56ebeace0d852918899efd37d698c006, '2023-03-09', 'ANA', 'KAYRET', 'PETIT', 'URBINA', '1982-11-04', 1, 1, 647, '2020-10-20 00:00:00', NULL, NULL, 2),
(156, '6150', 0x20aa455bdb16ddb4b36faf828c69bc8f, '2023-03-09', 'ENRIQUE', 'RAFAEL', 'CHIQUITO', 'SOSA', '1971-04-23', 1, 1, 647, '2020-10-01 00:00:00', '2021-01-28 00:00:00', NULL, 2),
(157, '11541', 0x7308e004e6ddbca18c8ef22b13a43adc, '2023-03-09', 'LEIDY', 'KASANDRA', 'SUESCUM', 'TAVIO', '1997-10-10', 3, 2, 647, '2020-11-09 00:00:00', '2021-01-22 00:00:00', NULL, 2),
(158, '6151', 0xf2a22cb545a9ea9ead1ad548eaeff6b7, '2023-03-09', 'MARYARIT', 'MARIANA', 'MEO', 'YANEZ', '1996-08-05', 1, 2, 644, '2020-12-01 00:00:00', NULL, NULL, 2),
(159, '11542', 0x89d39180296383654a46b43d86ad6d5c, '2023-03-09', 'FRANKLIN', 'ALBERTO', 'PACHECO', 'ACOSTA', '1980-10-18', 13, 3, 647, '2020-12-08 00:00:00', NULL, '2023-03-07 09:08:23', 1),
(160, '11543', 0x7bd1029606af4239490e64bb994df604, '2023-03-09', 'ORIANA', 'ELIZABETH', 'GRATEROL', 'GONZALEZ', '1997-09-17', 3, 2, 647, '2020-12-21 00:00:00', '2021-10-18 00:00:00', '2021-10-18 13:54:32', 2),
(161, '11544', 0xa3ec41d3844cef761c9d545f3a69c891, '2023-03-09', 'ALFREDO', 'DAVID', 'CONQUISTA', 'RODRIGUEZ', '1995-06-05', 7, 2, 1128, '2021-02-01 00:00:00', NULL, '2023-02-08 08:30:12', 1),
(162, '6152', 0xefa3a73f7ca913640d7834fc5a77ded9, '2023-03-09', 'EDGAR', 'WILMER', 'ANTON', 'MOLINA', '1965-11-15', 1, 1, 1121, '2021-02-22 00:00:00', NULL, '2021-07-26 17:23:41', 2),
(163, '11545', 0xef68ab6f518658282d5d8a4cfc741956, '2023-03-09', 'FREDY', 'DARIO', 'BAUTISTA', 'QUIJADA', '1990-10-30', 8, 1, 647, '2021-02-11 00:00:00', '2021-10-15 00:00:00', NULL, 2),
(164, '11546', 0x55299672e753dee7d23e6553d867ab30, '2023-03-09', 'IRIS', 'LUCYMAR', 'ESCORCHA', 'RONDON', '1978-05-05', 26, 12, 644, '2021-02-22 00:00:00', NULL, '2023-03-06 08:48:41', 1),
(173, '11547', 0xd5afc9de8704bf23bfc792f632ffc33c, '2023-03-09', 'CARLOS', 'EDUARDO', 'BASTIDAS', 'HERNANDEZ', '1991-11-24', 9, 6, 1119, '2021-03-22 00:00:00', '2022-01-21 00:00:00', '2022-01-21 14:12:14', 2),
(174, '11548', 0xa1e1400e80130b354a92794f2a2c4d8b, '2023-03-09', 'MARYSABEL', '', 'DOS SANTOS', 'CONTRERAS', '1986-06-20', 9, 5, 1133, '2021-04-07 00:00:00', NULL, '2023-02-28 16:16:21', 1),
(175, '6153', 0x372914d41de86dd92703cd0dee53bc62, '2023-03-09', 'IVETTE', 'ALEJANDRA', 'OROZCO', 'FLORES', '1994-02-23', 1, 1, 647, '2021-04-16 00:00:00', NULL, '2021-05-16 20:42:46', 2),
(176, '11549', 0x88f71d576b9b9a7eb02e7cdd3a50f189, '2023-03-09', 'WINNEY', 'JOHANA', 'BARRIENTOS', 'MC PHAIL', '1999-08-10', 5, 2, 647, '2021-05-03 00:00:00', '2022-12-05 00:00:00', '2022-11-21 13:19:12', 2),
(177, '11550', 0x00540a991feca08385be8c6e8cce1487, '2023-03-09', 'LEONELA', 'MICHELE', 'ZAMBELLA', 'OMAÑA', '1998-09-06', 2, 1, 647, '2021-05-10 00:00:00', '2021-11-01 00:00:00', '2021-11-02 18:50:00', 2),
(178, '11551', 0x523a178facc19f171d266721bfbd81ae, '2023-03-09', 'JUNEISY', 'ANIUSKA', 'BENITEZ', 'MACHADO', '1997-01-30', 2, 1, 647, '2021-05-10 00:00:00', '2021-09-03 00:00:00', '2021-07-02 14:37:35', 2),
(179, '11552', 0x885c2c2836ecc293b882ce6c349b479c, '2023-03-09', 'YESENIA', 'YULIMAR DEL VALLE', 'CASARES', 'PEROZO', '1996-04-09', 3, 2, 647, '2021-05-10 00:00:00', '2022-02-25 00:00:00', '2022-02-22 10:29:37', 2),
(180, '11553', 0xcd2cd819b3320262d69c7e6fddacf24d, '2023-03-09', 'OLIVER', 'IGNACIO', 'TOVAR', 'BENITEZ', '1999-08-20', 39, 1, 647, '2021-06-30 00:00:00', '2022-05-05 00:00:00', '2022-05-05 12:20:25', 2),
(181, '11554', 0x5f2991af2868d42d179591bec69d6776, '2023-03-09', 'RITCELIS', 'DEL VALLE', 'RUIZ', 'DIAZ', '1993-12-04', 21, 9, 647, '2021-07-01 00:00:00', '2021-07-30 00:00:00', NULL, 2),
(182, '1555', 0xc2d5d29ebe10d25ac764e28d1c9e0450, '2023-03-09', 'JENNY', 'LIS', 'SEGOVIA', 'ZAMBRANO', '1983-08-02', 12, 9, 647, '2021-07-01 00:00:00', NULL, '2023-03-08 15:48:18', 1),
(183, '11556', 0xc97e95a81b5bdf8ad3920e6feffcae91, '2023-03-09', 'CESAR', 'AUGUSTO', 'DIAZ', 'JARAMILLO', '1985-03-28', 12, 3, 647, '2021-07-01 00:00:00', NULL, '2023-03-09 09:06:41', 1),
(184, '11557', 0x4bf6b7f14a4f739f30e7fbbb58c7c5aa, '2023-03-09', 'DANALETH', 'DEL CARMEN', 'HERNANDEZ', 'MONASTERIO', '1999-08-11', 3, 2, 647, '2021-07-19 00:00:00', '2021-10-14 00:00:00', '2021-10-07 10:21:05', 2),
(185, '11558', 0x4243db303abf01eb84843b823bf020e5, '2023-03-09', 'JOHANNA', 'DE LA CRUZ', 'TRUJILLO', 'REVETE', '1981-07-01', 5, 6, 647, '2021-07-26 00:00:00', NULL, '2023-03-08 09:26:43', 1),
(186, '11559', 0x9b6418bd0e01cf1b747c12363ebbe592, '2023-03-09', 'MELANIE', 'ALEXANDRA', 'MARQUEZ', 'BAPTISTA', '2000-09-29', 2, 13, 647, '2021-07-19 00:00:00', NULL, '2023-03-07 15:53:14', 1),
(187, '11560', 0x167b7aadb75cbcc8034da842e0360171, '2023-03-09', 'ESCARLET', 'MAYERLINE', 'GUILLEN', 'GUILLEN', '1997-07-10', 6, 2, 647, '2021-09-01 00:00:00', NULL, '2023-02-02 10:27:46', 1),
(188, '11561', 0xa4dc3ebef91c2b91dde9e818e5514d38, '2023-03-09', 'NORBELIS', 'ALEJANDRA', 'MORRINSON', 'CORTEZ', '1997-06-04', 4, 2, 647, '2021-10-04 00:00:00', NULL, '2023-03-08 15:57:47', 1),
(189, '11562', 0xb57c626ac91c0c8cca36243dfc5c5070, '2023-03-09', 'ELEANA', 'GABRIELA', 'ROJAS', 'CUNYA', '1985-10-17', 11, 2, 647, '2021-10-04 00:00:00', '2022-01-19 00:00:00', '2022-01-19 10:23:42', 2),
(190, '11563', 0x6bf72610d23d278861e40c7cad6b76db, '2023-03-09', 'ANTHONI', 'CARLOS', 'FREITES', 'QUIROZ', '1977-06-30', 31, 11, 647, '2021-10-04 00:00:00', NULL, '2023-03-09 09:45:26', 1),
(191, '6154', 0x24021e390ff9b2f57b798c4b8abf26d0, '2023-03-09', 'JESUS', 'ALBERTO', 'LAYA', 'JIMENEZ', '1978-12-16', 1, 2, 647, '2021-09-15 00:00:00', NULL, '2023-03-08 16:57:44', 1),
(192, '11564', 0x4e7c83cf9599a3969f6c9ba397455a69, '2023-03-09', 'ANDREA', 'GABRIELA', 'GARCIA', 'GRANADOS', '1989-07-08', 12, 17, 647, '2021-11-01 00:00:00', NULL, '2023-03-06 10:02:02', 1),
(193, '11565', 0x1e28845a33ec486b78b33124ff3c51d9, '2023-03-09', 'OSCAR AUGUSTO', 'AUGUSTO', 'ROJO', 'SUAREZ', '1999-09-03', 33, 19, 647, '2021-11-01 00:00:00', NULL, '2023-03-09 08:59:40', 1),
(194, '11566', 0xd842c98458ea9c6ade77d1dbc41eada8, '2023-03-09', 'JOSE', 'JOEL', 'BOLIVAR', 'SIERRA', '1974-05-28', 27, 19, 647, '2021-11-01 00:00:00', NULL, '2023-03-09 09:06:12', 1),
(195, '11567', 0xefa3a73f7ca913640d7834fc5a77ded9, '2023-03-09', 'EDGAR', 'WILMER', 'ANTON', 'MOLINA', '1965-11-15', 12, 1, 647, '2021-11-22 00:00:00', NULL, '2023-03-06 09:13:25', 1),
(196, '11568', 0x970fcb0fbe4183e5401fd12b13f83342, '2023-03-09', 'BEYKER', 'ANDRES', 'LOYO', 'GONZALEZ', '1990-03-01', 31, 4, 647, '2021-11-29 00:00:00', '2022-08-30 00:00:00', '2022-08-23 13:14:21', 2),
(197, '11569', 0x19d67bb546785ba4cf0a4846589d6d11, '2023-03-09', 'PABLO', 'SAMUEL', 'MATA', 'HERNANDEZ', '1997-09-17', 6, 1, 647, '2021-12-06 00:00:00', '2021-12-07 00:00:00', NULL, 2),
(198, '11570', 0x36b9f98f9530546685fd0d79b1334907, '2023-03-09', 'BRANDON', 'ENMANUEL', 'RIVERA', 'HERNANDEZ', '1997-06-01', 5, 2, 647, '2021-12-06 00:00:00', NULL, '2022-02-10 13:59:07', 2),
(199, '11571', 0xfa3d9af2b86645168a4b41f7535bcde2, '2023-03-09', 'YANIX', 'XINAY', 'MONSALVE', 'MACHADO', '1995-10-03', 25, 7, 647, '2021-12-06 00:00:00', NULL, '2023-03-07 15:44:01', 1),
(200, '11572', 0x04c9bbac36c3b5f4c43b0fe680373e27, '2023-03-09', 'JUAN', 'PABLO', 'PEÑALOZA', 'DIAZ', '1994-01-19', 5, 1, 647, '2021-12-07 00:00:00', NULL, '2023-03-07 13:48:47', 1),
(201, '11573', 0x85909ab093c06e673fff7e5fdafb123c, '2023-03-09', 'GABRIEL', 'ALEJANDRO', 'ROJAS', 'RICO', '1987-01-25', 4, 2, 647, '2022-01-10 00:00:00', '2022-07-29 00:00:00', '2022-08-02 11:56:17', 2),
(202, '11574', 0xa1c4215f75bad02b8df90a6147ec01c7, '2023-03-09', 'JOSNELY', 'JHOVANNA', 'CASTILLO', 'GIL', '1999-12-22', 4, 6, 647, '2022-01-17 00:00:00', NULL, '2023-03-03 14:47:25', 1),
(203, '11575', 0x83188453dc3444ee4825d9ff7b2ab7a6, '2023-03-09', 'CHRISTHOPHER', 'EDUARDO', 'CABRERA', 'BAPTISTA', '1993-06-27', 12, 2, 647, '2022-02-01 00:00:00', NULL, '2023-03-08 16:46:15', 1),
(204, '11576', 0x36b9f98f9530546685fd0d79b1334907, '2023-03-09', 'BRANDON', 'ENMANUEL', 'RIVERA', 'HERNANDEZ', '1997-06-01', 5, 2, 647, '2021-12-06 00:00:00', NULL, '2023-03-08 16:54:55', 1),
(205, '6155', 0xad7b0fe3134e037387a7fc2742792df7, '2023-03-09', 'TOMAS', 'ANTONIO', 'MERIDA', 'GALINDO', '1956-02-19', 1, 10, 647, '2022-02-16 00:00:00', NULL, '2022-08-18 09:49:37', 2),
(206, '11578', 0x270f8aacfe8b59d553285073de46e171, '2023-03-09', 'GABRIEL', 'ALEJANDRO', 'MORA', 'CARVAJAL', '1996-09-29', 3, 1, 647, '2022-02-21 00:00:00', NULL, '2022-09-21 10:26:14', 2),
(207, '11577', 0x1e3d59a448dd78b09bf962402842d96b, '2023-03-09', 'JHON', 'JOSE', 'MARTINEZ', 'LONDIZA', '1990-01-28', 7, 4, 647, '2022-02-08 00:00:00', NULL, NULL, 1),
(208, '11579', 0xa1d5e8d025bd62b31b55b1761b283b5d, '2023-03-09', 'KEIBI', 'RAFAEL', 'MORENO', 'CAÑIZALES', '1995-05-09', 9, 6, 647, '2022-03-02 00:00:00', NULL, '2022-04-21 09:27:53', 2),
(209, '11580', 0xb25f3df715172d3c050d19cdc436f6b6, '2023-03-09', 'DEIRIANA', 'ANDREINA', 'PORTA', 'MENESES', '1997-05-15', 5, 2, 647, '2022-03-02 00:00:00', NULL, '2023-03-08 14:47:21', 1),
(210, '6156', 0x919482975a53e20fffe20a6efb3de610, '2023-03-09', 'LUIS', 'GIOVANNY', 'CARDENAS', 'RODRIGUEZ', '1972-02-24', 1, 1, 647, '2022-03-11 00:00:00', NULL, '2022-06-15 14:46:01', 2),
(211, '11581', 0x2ab195024686baaf25768e2065bc9ae4, '2023-03-09', 'GUILLERMO', 'ENRIQUE', 'LOAIZA', 'DIAZ', '2001-05-17', 31, 17, 647, '2022-03-21 00:00:00', '2022-05-25 00:00:00', '2022-05-16 08:27:05', 2),
(212, '11582', 0x2cfa81903478c0c02ffa1d25a47a5e9c, '2023-03-09', 'CESAR', 'TADEO', 'UBAN', 'BALZA', '1996-12-04', 8, 1, 647, '2022-04-04 00:00:00', '2022-08-30 00:00:00', '2022-08-26 11:50:10', 2),
(213, '11583', 0x0c1e4fd7c8ac773b9a9151cb111761a6, '2023-03-09', 'DINEXY', 'ANDREINA', 'PORTA', 'MENESES', '1993-12-02', 8, 2, 647, '2022-04-04 00:00:00', NULL, '2023-03-08 16:55:28', 1),
(214, '11584', 0x28de8fbe3a4fdbb7f6d7a6dac2b64216, '2023-03-09', 'RICARDO', 'ERNESTO', 'LEON', 'PIRELA', '2001-04-02', 33, 9, 647, '2022-04-20 00:00:00', NULL, '2023-03-02 15:01:36', 1),
(215, '11585', 0xa1d5e8d025bd62b31b55b1761b283b5d, '2023-03-09', 'KEIBI', 'RAFAEL', 'MORENO', 'CAÑIZALES', '1995-05-09', 9, 6, 647, '2022-03-02 00:00:00', NULL, '2023-03-03 14:45:10', 1),
(216, '11586', 0x33d71d2ac998727b52a71646e6b0b7fc, '2023-03-09', 'BARBARA', 'PAOLA', 'BETANCOURT', 'VAZQUEZ', '2002-09-27', 3, 1, 647, '2022-05-16 00:00:00', '2022-09-19 00:00:00', '2022-09-12 10:07:04', 2),
(217, '11587', 0x61c13eba8b8132974bc5608b31156ae9, '2023-03-09', 'KEIVER', 'DUVAN', 'AVILA', 'PEREZ', '1997-09-28', 7, 4, 647, '2022-05-23 00:00:00', '2023-01-31 00:00:00', NULL, 2),
(218, '11588', 0x3a0f2884ff1f4c417a37d5b7cb1c7491, '2023-03-09', 'KATHERINE', 'ESTHEFANIA', 'HERNANDEZ', 'GOMEZ', '1992-07-28', 10, 4, 647, '2022-05-23 00:00:00', '2023-02-28 00:00:00', NULL, 2),
(219, '11589', 0x358a7cc7d966e52a33f6f01e357d5ca2, '2023-03-09', 'YULIMAR', '', 'DIAZ', 'QUINTERO', '1978-07-21', 9, 4, 647, '2022-06-01 00:00:00', NULL, NULL, 1),
(220, '11590', 0x3ae6ae9251707b633e41f330f6b5351b, '2023-03-09', 'YURI', 'ANDREA', 'CHACON', 'MILLAN', '1990-06-11', 5, 1, 647, '2022-06-01 00:00:00', NULL, '2023-02-24 14:22:44', 1),
(221, '11591', 0x3ad57219988deea7bc7f6c706cca7e16, '2023-03-09', 'JOSMARLY', 'YOHANA', 'MALDONADO', 'MEDINA', '1994-11-26', 4, 1, 647, '2022-06-01 00:00:00', '2022-06-02 00:00:00', NULL, 2),
(222, '11592', 0xc13e19eb0be1fcd876dc98bb869f2628, '2023-03-09', 'WILBER', 'MOISES', 'ALGUETA', 'TORRES', '2000-10-27', 3, 1, 647, '2022-06-01 00:00:00', NULL, NULL, 2),
(223, '11593', 0x578a31baf9dc7435fdc4073125db7548, '2023-03-09', 'JOSE', 'GREGORIO', 'CASTELLANOS', 'QUINTO', '1987-07-25', 6, 3, 647, '2022-06-02 00:00:00', NULL, '2023-03-03 13:21:41', 1),
(224, '11594', 0x1f4f0e36be6624b89905a17e1bf64fdd, '2023-03-09', 'BELKIS', 'MARIA', 'FLOREAN', 'LAGUNA', '1981-12-11', 24, 7, 647, '2022-06-01 00:00:00', NULL, '2023-03-07 13:10:21', 1),
(225, '11595', 0x7f7f9c080befe0f50280fd2f13caff30, '2023-03-09', 'KEYBERT', 'EDUARDO', 'APARICIO', 'GONZALEZ', '1994-06-13', 3, 2, 647, '2022-06-16 00:00:00', NULL, '2023-03-08 21:19:04', 1),
(226, '11596', 0x062fdba11fcb6ba786cbb20ddee683ef, '2023-03-09', 'DOUGLENIS', 'DE LOS ANGELES', 'TABASQUEZ', 'MORALES', '1997-06-13', 9, 4, 647, '2022-07-18 00:00:00', '2022-09-02 00:00:00', NULL, 2),
(227, '11597', 0xd400af68e6a66f2485d927ed68c1c401, '2023-03-09', 'IVANA', 'CARIDAD', 'GUILARTE', 'PINTO', '1999-10-31', 39, 6, 647, '2022-07-19 00:00:00', '2022-12-02 00:00:00', '2022-12-06 09:57:18', 2),
(228, '11598', 0xd4e8ba398f297fed9566fa97f8425f24, '2023-03-09', 'ALEJANDRA', 'MARINA', 'SANCHEZ', 'CANCHICA', '1991-07-01', 3, 1, 647, '2022-08-01 00:00:00', NULL, '2023-03-07 10:16:00', 1),
(229, '11599', 0x5c4b6c5d07f43a01506dc573e0a15b1e, '2023-03-09', 'JOSE', 'ANDRES', 'HERNANDEZ', 'RUIZ', '1999-03-16', 3, 2, 647, '2022-08-10 00:00:00', NULL, '2023-03-09 09:03:43', 1),
(230, '11600', 0x06e477466a371d1eacd75ae15905d43d, '2023-03-09', 'JOSE', 'MIGUEL', 'PEROZO', 'HERRERA', '1994-10-04', 9, 1, 647, '2022-08-15 00:00:00', NULL, '2023-03-09 09:26:22', 1),
(231, '11601', 0x18a089795f781c551411046258024a92, '2023-03-09', 'RAINIER', 'HELY', 'ROJAS', 'HADDAD', '1996-10-03', 8, 19, 647, '2022-08-15 00:00:00', NULL, '2023-03-06 09:01:11', 1),
(232, '11602', 0x6d563507ea9b4aaeb43a6c5b03debed4, '2023-03-09', 'ANGELICA', 'MARIA', 'LUGO', 'RAMIREZ', '2000-07-11', 39, 6, 647, '2022-10-10 00:00:00', '2023-01-03 00:00:00', '2023-01-03 01:26:19', 2),
(233, '11610', 0x2fe75e25db5950d7f3192bd9a567c437, '2023-03-09', 'MARIA', 'LAURA', 'GARCIA', 'JUAREZ', '1999-12-15', 4, 4, 647, '2022-10-17 00:00:00', '2022-10-28 00:00:00', NULL, 2),
(234, '11611', 0x59edc43aa33ee663acbfa31a3c083b0f, '2023-03-09', 'JENNY', 'LUCIA', 'LIMA', 'HAMILTON', '1967-08-05', 10, 4, 647, '2022-11-07 00:00:00', '2022-12-02 00:00:00', NULL, 2),
(235, '11612', 0xba881c64c8046bcca8225185fff51e8d, '2023-03-09', 'CRISBET', 'YOHANNI', 'BARCELO', 'CASTRO', '1993-12-19', 9, 4, 647, '2022-11-07 00:00:00', '2023-03-03 00:00:00', NULL, 2),
(236, '11613', 0x208e6671311570c0cbcbf76b6f772a97, '2023-03-09', 'ARLEANNY', 'ALEXARI', 'MARRERO', 'QUINTERO', '2002-07-16', 3, 2, 647, '2022-11-07 00:00:00', NULL, '2023-03-08 16:14:21', 1),
(237, '11614', 0x4fd02b9a12f2b1a08392566ab29ba9ad, '2023-03-09', 'JOSE', 'LUIS', 'DIAZ', 'HERRERA', '1997-02-26', 39, 6, 647, '2022-11-08 00:00:00', NULL, '2023-02-09 09:37:02', 1),
(238, '11615', 0x6896b72aa59eeaaa40242214739a1bce, '2023-03-09', 'ORLAIMY', 'SAIR', 'MUÑOZ', 'JAIMES', '2001-06-28', 39, 6, 647, '2022-12-12 00:00:00', NULL, '2023-03-09 09:30:48', 1),
(239, '11616', 0x9362ccc5dcb88e9f44ca6ef99a9b3cc3, '2023-03-09', 'JOSMAN', 'JOSUE', 'FUENTES', 'GRIMAN', '2000-07-17', 3, 2, 647, '2023-01-09 00:00:00', NULL, '2023-03-08 17:00:33', 1),
(240, '11617', 0x988bf2793315f1ba3fbdee9d3e89b5cd, '2023-03-09', 'JORGENIS', 'JOSE', 'GUERRA', 'LEZAMA', '1996-11-28', 39, 6, 647, '2023-01-23 00:00:00', NULL, '2023-03-09 09:23:18', 1),
(241, '11618', 0x7d038db3c2a8022d9d8c23c9b36a2077, '2023-03-09', 'CESAR', 'LEANDRO', 'GARCIA', 'AULAR', '2001-08-14', 39, 6, 647, '2023-01-23 00:00:00', NULL, '2023-03-09 09:36:13', 1),
(242, '6157', 0x59a218968d4072da6231f2280bddaf7a, '2023-03-09', 'MAYERLING', 'KARINA', 'VALERA', 'RIVAS', '1975-10-19', 1, 2, 647, '2022-10-17 00:00:00', NULL, NULL, 1),
(243, '6159', 0x8dc9c35a2bba3db28169f52d28b5f527, '2023-03-09', 'YULITZA', 'DEL VALLE', 'ESPARRAGOZA', 'CASTILLEJO', '2023-02-06', 1, 1, 647, '2023-02-06 00:00:00', NULL, NULL, 1),
(244, '11619', 0xcbcdd03242d775e13741b2308aca4db6, '2023-03-09', 'RAUL', 'HUMBERTO', 'BRICEÑO', 'CORREA', '1991-02-23', 10, 4, 647, '2023-02-13 00:00:00', NULL, NULL, 1),
(245, '11620', 0xbfc5e4293bf330bde6ace6bfd37f234e, '2023-03-09', 'MARY', 'ISABEL', 'ROJAS', '', '1967-08-16', 6, 1, 647, '2023-02-13 00:00:00', NULL, NULL, 1),
(246, '11621', 0xa679acfe3d0deef0b6ff5b7e553b2b47, '2023-03-09', 'RUBI', 'YABISAY', 'RAMIREZ', 'LOPEZ', '1974-01-06', 6, 1, 647, '2023-02-14 00:00:00', NULL, '2023-03-07 16:06:08', 1),
(247, '11622', 0x038e973a488134d8436c8bae5b50d66c, '2023-03-09', 'CARLOS', 'EDUARDO', 'NAVAS', 'EDUARDO', '2023-02-13', 39, 13, 647, '2023-02-13 00:00:00', NULL, NULL, 1),
(248, '11623', 0x900ddc90e683652dd185b78eb9554e81, '2023-03-09', 'MIRIAM', 'DESIREE', 'HIDALGO', 'BRICEÑO', '1983-07-11', 10, 4, 647, '2023-03-07 00:00:00', '2023-03-07 00:00:00', NULL, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_usuarios_contacto`
--

CREATE TABLE `tbl_usuarios_contacto` (
  `Id` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Correo_principal` varchar(255) DEFAULT NULL,
  `Correo_secundario` varchar(255) DEFAULT NULL,
  `Telefono_principal` varchar(30) DEFAULT NULL,
  `Telefono_secundario` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_usuarios_contacto`
--

INSERT INTO `tbl_usuarios_contacto` (`Id`, `Id_usuario`, `Correo_principal`, `Correo_secundario`, `Telefono_principal`, `Telefono_secundario`) VALUES
(1, 1, 'dmolina101@gmail.com', '', '(0424) - 446 3739', ''),
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
(13, 13, 'katherine.zurita@crowe.com.ve', '', '(0424) - 190 7404', '(0424) - 317 0363'),
(14, 14, 'mileidis.moreno@crowe.com.ve', '', '(0424) - 171 8118', ''),
(15, 15, 'francia.medina@crowe.com.ve', '', '(0416) - 694 7046', ''),
(16, 16, 'astrid.mendoza@crowe.com.ve', '', '(0424) - 165 2571', ''),
(17, 17, 'maria.tovar@crowe.com.ve', '', '02124829623', '04242473031'),
(18, 18, 'mariana.brito@crowe.com.ve', '', '(0424) - 290 2167', ''),
(19, 19, 'belkis.cortina@crowe.com.ve', '', '(0212) - 415 9553', '(0416) - 407 9713'),
(20, 20, 'lucrecia.silva@crowe.com.ve', '', '(0426) - 419 9217', ''),
(21, 21, 'normedy.parra@crowe.com.ve', '', '(0212) - 524 1716', '(0412) - 012 5384'),
(22, 22, 'josvelis.castillo@crowe.com.ve', '', '(0414) - 835 0920', '(0212) - 344 0542'),
(23, 23, 'luis.russian@crowe.com.ve', '', '(0424) - 260 2227', '(0212) - 339 5206'),
(24, 24, 'jonathan.azocar@crowe.com.ve', '', '(0212) - 377 4758', '(0426) - 637 3419'),
(25, 25, 'yerlenis.valderrama@crowe.com.ve', '', '(0424) - 295 0201', ''),
(26, 26, 'kleiver.corro@crowe.com.ve', '', '(0212) - 419 0028', '(0412) - 949 6868'),
(27, 27, 'maryuri.barazarte@crowe.com.ve', '', '(0212) - 244 4894', '(0424) - 183 9221'),
(28, 28, 'pedro.benitez@crowe.com.ve', '', '', ''),
(29, 29, 'dennys.flores@crowe.com.ve', '', '(0416) - 019 2302', ''),
(30, 30, 'genesis.marcano@crowe.com.ve', '', '(0239) - 248 2117', '(0414) - 020 9137'),
(31, 31, 'keilimar.suarez@crowe.com.ve', '', '(0416) - 928 1259', '(0212) - 745 9838'),
(32, 32, 'johanne.muñoz@crowe.com.ve', '', '(0414) - 315 5147', ''),
(33, 33, 'alfredo.hernandez@crowe.com.ve', '', '(0412) - 711 6777', ''),
(34, 34, 'raul.vargas@crowe.com.ve', '', '', ''),
(35, 35, 'shelcie.paz@crowe.com.ve', '', '(0212) - 258 3241', '(0414) - 908 4285'),
(36, 36, 'ladymar.morett@crowe.com.ve', '', '(0212) - 451 2556', '(0426) - 119 7245'),
(37, 37, 'anthony.garcia@crowe.com.ve', '', '(0426) - 213 0363', '(0412) - 384 0786'),
(38, 38, 'solmary.martinez@crowe.com.ve', '', '02123397992', '04129904281'),
(39, 39, 'jackeline.ramos@crowe.com.ve', '', '(0212) - 641 2375', '(0416) - 823 3236'),
(40, 40, 'belkis.vazquez@crowe.com.ve', '', '(0426) - 215 7178', ''),
(41, 41, 'yuzleibby.maldonado@crowe.com.ve', '', '(0424) - 219 4508', '(0212) - 870 3102'),
(42, 42, 'giovanni.corredor@crowe.com.ve', '', '(0412) - 010 2693', '(0212) - 347 2038'),
(43, 43, 'kleiver.cadenas@crowe.com.ve', '', '(0414) - 319 6616', '(0212) - 267 8468'),
(44, 44, 'ivette.orozco@crowe.com.ve', '', '(0424) - 261 3215', '(0212) - 434 1107'),
(45, 45, 'zunaya.wilches@crowe.com.ve', '', '(0414) - 031 6013', '(0212) - 613 5612'),
(46, 46, 'jesus.abraham@crowe.com.ve', '', '(0212) - 372 7075', '(0424) - 214 7829'),
(47, 47, 'jose.perozo@dominio.com', '', '(0426) - 253 9113', ''),
(48, 48, 'roberto.villegas@crowe.com.ve', '', '(0212) - 870 3830', '(0424) - 176 2670'),
(49, 49, 'sandro.mayora@crowe.com.ve', '', '(0412) - 367 5678', '(0212) - 516 3034'),
(50, 50, 'eduardo.bastos@crowe.com.ve', '', '(0212) - 987 5898', '(0424) - 130 4353'),
(51, 51, 'vanessa.rojas@crowe.com.ve', '', '(0414) - 782 6035', ''),
(52, 52, 'carlos.revete@crowe.com.ve', '', '(0424) - 259 1419', ''),
(53, 53, 'vianney.rugeles@crowe.com.ve', '', '(0212) - 443 4371', '(0412) - 998 7473'),
(54, 54, 'edwin.burgos@crowe.com.ve', '', '(0414) - 287 1671', ''),
(55, 55, 'nombre.apellido@dominio.com', '', '(0212) - 363 7192', '(0412) - 858 4022'),
(56, 56, 'freddy.vargas@crowe.com.ve', '', '04241292285', ''),
(57, 57, 'yorman.rangel@crowe.com.ve', '', '', ''),
(58, 58, 'jose.utrera@crowe.com.ve', '', '', ''),
(59, 59, 'alejandro.lira@crowe.com.ve', '', '(0212) - 672 4819', '(0414) - 246 0103'),
(60, 60, 'yordalis.echarrys@crowe.com.ve', '', '(0412) - 293 2692', ''),
(61, 61, 'eliana.ponce@crowe.com.ve', '', '(0212) - 576 1138', '(0414) - 911 3335'),
(62, 62, 'stefany.gonzalez@crowe.com.ve', '', '(0424) - 208 5444', ''),
(63, 63, 'naivelys.altuve@crowe.com.ve', '', '(0414) - 791 4010', ''),
(64, 64, 'gabriela.gil@crowe.com.ve', '', '(0212) - 662 1812', '(0426) - 287 4127'),
(65, 65, 'orianna.alejos@crowe.com.ve', '', '(0212) - 668 9284', '(0426) - 315 8428'),
(66, 66, 'marynes.gonzalez@crowe.com.ve', '', '(0212) - 492 9084', '(0424) - 262 8459'),
(67, 67, 'eligio.mendoza@crowe.com.ve', '', '', ''),
(68, 68, 'marielvi.oller@crowe.com.ve', '', '', ''),
(69, 69, 'alba.navia@crowe.com.ve', '', '(0212) - 762 5333', '(0424) - 298 4865'),
(70, 70, 'nombre.apellido@dominio.com', '', '(0414) - 126 6489', '(0212) - 861 4414'),
(71, 71, 'yessica.rivas@crowe.com.ve', '', '(0212) - 875 0733', '(0424) - 267 7331'),
(72, 72, 'nombre.apellido@dominio.com', '', '(0414) - 211 9162', ''),
(73, 73, 'yda.chirinos@crowe.com.ve', '', '(0212) - 515 9794', '(0424) - 136 0393'),
(74, 74, 'nombre.apellido@dominio.com', '', '(0212) - 614 9790', '(0414) - 326 0002'),
(75, 75, 'nombre.apellido@dominio.com', '', '(0212) - 324 3797', '(0412) - 249 3721'),
(76, 76, 'nombre.apellido@dominio.com', '', '(0412) - 574 6284', ''),
(77, 77, 'nombre.apellido@dominio.com', '', '(0412) - 256 4514', ''),
(78, 78, 'nombre.apellido@dominio.com', '', '(0414) - 267 8216', ''),
(79, 79, 'nahomy.quintero@crowe.com.ve', '', '(0212) - 744 6051', '(0424) - 174 3888'),
(80, 80, 'maria.espina@crowe.com.ve', '', '(0127) - 304 196', '(0426) - 513 1381'),
(81, 81, 'nombre.apellido@dominio.com', '', '(0424) - 269 6996', '(0412) - 921 7899'),
(82, 82, 'nombre.apellido@dominio.com', '', '(0412) - 709 8992', ''),
(83, 83, 'glender.cortez@crowe.com.ve', '', '(0414) - 219 0677', '(0212) - 532 1810'),
(84, 84, 'alberto.evies@crowe.com.ve', '', '02124335180', '04141057605'),
(85, 85, 'angela.aranea@crowe.com.ve', '', '(0212) - 515 3658', '(0426) - 304 6685'),
(86, 86, 'arturo.sosa@crowe.com.ve', '', '(0424) - 134 0102', ''),
(87, 87, 'adrian.perez@crowe.com.ve', '', '(0212) - 861 3428', '(0412) - 804 5133'),
(88, 88, 'elisa.pasero@crowe.com.ve', '', '(0412) - 368 8968', ''),
(89, 89, 'omar.marquez@crowe.com.ve', '', '', ''),
(90, 90, 'angelica.funes@crowe.com.ve', '', '(0212) - 858 3253', '(0426) - 290 5898'),
(91, 91, 'eslyn.rojas@crowe.com.ve', '', '(0424) - 344 3594', '(0212) - 808 4209'),
(92, 92, 'carmen.ochoa@crowe.com.ve', '', '(0424) - 149 5523', ''),
(93, 93, 'laura.rojas@crowe.com.ve', '', '', ''),
(94, 94, 'nombre.apellido@dominio.com', '', '(0424) - 225 8139', ''),
(95, 95, 'nombre.apellido@dominio.com', '', '', ''),
(96, 96, 'nombre.apellido@dominio.com', '', '(0416) - 939 7195', ''),
(97, 97, 'jose.machado@crowe.com.ve', '', '', ''),
(98, 98, 'nombre.apellido@dominio.com', '', '', ''),
(99, 99, 'jennifer.villa@crowe.com.ve', '', '', ''),
(100, 100, 'anacecilia.castano@crowe.com.ve', '', '(0212) - 571 6504', ''),
(101, 101, 'amayoisbi.garcia@crowe.com.ve', '', '04127013435', ''),
(102, 102, 'jennifer.chacon@crowe.com.ve', '', '04125897240', ''),
(103, 103, 'ignayari.mendoza@crowe.com.ve', '', '(0412) - 928 9923', ''),
(104, 104, 'reina.fajardo@crowe.com.ve', '', '(0416) - 426 9965', ''),
(105, 105, 'yolimer.mendoza@crowe.com.ve', '', '(0414) - 901 8276', '(0212) - 681 3348'),
(106, 106, 'ignayari.mendoza@crowe.com.ve', '', '(0412) - 976 2870', ''),
(107, 107, 'adriana.guzman@crowe.com.ve', '', '(0212) - 941 2882', '(0414) - 454 9562'),
(108, 108, 'jose.estaba@crowe.com.ve', '', '02128602803', '04243389487'),
(109, 109, 'karina.perez@crowe.com.ve', '', '(0426) - 592 0655', ''),
(110, 110, 'zonny.garcia@crowe.com.ve', '', '(0424) - 313 8868', '(0239) - 225 2293'),
(111, 111, 'nombre.apellido@dominio.com', '', '(0426) - 887 0548', ''),
(112, 112, 'nombre.apellido@dominio.com', '', '(0426) - 216 6223', ''),
(113, 113, 'leonardo.alopez21@gmail.com', '', '(0414) - 259 8750', ''),
(114, 114, 'josearturo0706@gmail.com', '', '(0212) - 481 8970', '(0412) - 825 9076'),
(115, 115, 'nombre.apellido@dominio.com', '', '(0212) - 451 8087', '(0412) - 957 6671'),
(116, 116, 'antonio.reyes@crowe.com.ve', '', '02122425335', '04141626367'),
(117, 117, 'duvan.pinto@crowe.com.ve', '', '(0424) - 184 2688', ''),
(118, 118, 'freddy.perdomo@crowe.com.ve', '', '(0212) - 976 6425', '(0414) - 446 6147'),
(119, 119, 'fernando.rangel@crowe.com.ve', '', '(0414) - 178 2596', ''),
(120, 120, 'gelen.cardenas@crowe.com.ve', '', '(0212) - 576 7453', '(0416) - 465 4993'),
(121, 121, 'nombre.apellido@dominio.com', '', '(0212) - 237 3113', ''),
(122, 122, 'nombre.apellido@dominio.com', '', '(0212) - 237 3113', '(0414) - 208 1976'),
(123, 123, 'laura.rojas@crowe.com.ve', '', '(0416) - 932 2811', ''),
(124, 124, 'amelia.diaz@crowe.com.ve', '', '', ''),
(125, 125, 'emilio.leon@crowe.com.ve', '', '(0416) - 608 4971', '(0424) - 118 0197'),
(126, 126, 'gustavo.puchi@crowe.com.ve', '', '(0212) - 483 4655', '(0412) - 220 6492'),
(127, 127, 'alfio.saglimbeni@crowe.com.ve', '', '(0416) - 827 2679', ''),
(128, 128, 'arianna.matos@crowe.com.ve', '', '(0212) - 323 8208', '(0412) - 600 0531'),
(129, 129, 'ana.blandin@crowe.com.ve', '', '02124329839', '04241624237'),
(130, 130, 'oscar.piña@crowe.com.ve', '', '', ''),
(131, 131, 'nombre.apellido@dominio.com', '', '(0212) - 808 4742', '(0524) - 704 2110'),
(132, 132, 'duglimar.mendez@crowe.com.ve', '', '(0416) - 206 2192', ''),
(133, 133, 'sol.viana@crowe.com.ve', '', '(0212) - 631 6797', '(0424) - 146 9101'),
(134, 134, 'douglas.torrealba@crowe.com.ve', '', '(0416) - 209 4874', '(0416) - 800 0868'),
(135, 135, 'nombre.apellido@dominio.com', '', '04267528235', '02128715756'),
(136, 136, 'nombre.apellido@dominio.com', '', '04261396926', ''),
(137, 137, 'nombre.apellido@dominio.com', '', '04126305629', ''),
(138, 138, 'nombre.apellido@dominio.com', '', '04162139037', '02124909126'),
(139, 139, 'nombre.apellido@dominio.com', '', '02124329566', '04168175614'),
(140, 140, 'fredy.bautista@crowe.com.ve', '', '', ''),
(141, 141, 'nombre.apellido@dominio.com', '', '', ''),
(142, 142, 'barbara.zambrano@crowe.com.ve', '', '', ''),
(143, 143, 'mary.cruz@crowe.com.ve', '', '(0424) - 968 6614', '(0286) - 934 1430'),
(144, 144, 'sergio.marquez@crowe.com.ve', 'sergiofmarquezt@gmail.com', '(0414) - 907 0900', ''),
(145, 145, 'nelson.marcano@crowe.com.ve', '', '(0412) - 019 5573', ''),
(146, 146, 'alfio.saglimbeni@crowe.com.ve', 'smarquezt66@gmail.com', '', ''),
(147, 147, 'antonio.dugarte@crowe.com.ve', '', '(0424) - 226 5723', ''),
(148, 148, 'mirnangela.salaya@crowe.com.ve', '', '(0424) - 151 1028', ''),
(149, 149, 'jose.marquez@crowe.com.ve', '', '(0414) - 255 4850', ''),
(150, 150, 'freddyperdomo17@gmail.com', '', '', ''),
(151, 151, 'robison.aranguren@crowe.com.ve', '', '(0414) - 134 9727', ''),
(152, 152, 'joseecker51@gmail.com', '', '(0414) - 101 7189', '(0414) - 263 8949'),
(153, 153, 'jhon.rondon@crowe.com.ve', 'eduardobarrera69@gmail.com', '(0412) - 318 6673', ''),
(154, 154, 'contraloriacrowe@gmail.com', '', '(0414) - 907 0900', '(0212) - 235 0147'),
(155, 155, 'anapetit04@gmail.com', '', '(0414) - 933 0573', ''),
(156, 156, 'enrique.chiquito@gmail.com', '', '(0412) - 208 7873', '(0426) - 533 2319'),
(157, 157, 'suscumleidy@gmail.com', '', '(0412) - 293 5740', '(0212) - 581 4635'),
(158, 158, 'mary050896@gmail.com', '', '(0414) - 325 3136', '(0212) - 342 4248'),
(159, 159, 'franklin.pacheco@crowe.com.ve', '', '(0424) - 156 5718', ''),
(160, 160, 'oriana.graterol@crowe.com.ve', '', '(0412) - 571 7905', '(0212) - 862 9219'),
(161, 161, 'alfredo.conquista@crowe.com.ve', '', '(0424) - 110 6550', '(0212) - 416 0138'),
(162, 162, 'wilmeranton65@gmail.com', '', '(0414) - 314 1335', '(0424) - 154 1595'),
(163, 163, 'freddy.bautista@crowe.com.ve', '', '(0414) - 109 3990', '(0212) - 976 3028'),
(164, 164, 'iris.escorcha@crowe.com.ve', '', '(0426) - 406 3883', '(0212) - 362 2056'),
(173, 173, 'carlos.bastidas@crowe.com.ve', 'clbastidas91@gmail.com', '(0412) - 639 4216', ''),
(174, 174, 'marysabel.dossantos@crowe.com.ve', '', '(0426) - 510 1377', ''),
(175, 175, 'ivetteorozco1994@gmail.com', '', '(0424) - 261 3215', ''),
(176, 176, 'winney.barrientos@crowe.com.ve', 'winneyphail18@gmail.com', '(0426) - 704 2706', '(0414) - 241 5947'),
(177, 177, 'leonela.zambella@crowe.com.ve', 'leonelaz1998@gmail.com', '(0212) - 471 5309', '(0424) - 236 4607'),
(178, 178, 'juneisy.benitez@crowe.com.ve', 'june.abm@gmail.com', '(0412) - 295 9794', '(0426) - 407 3835'),
(179, 179, 'yesenia.casares@crowe.com.ve', '', '(0424) - 143 3149', '(0412) - 728 5524'),
(180, 180, 'oliver.tovar@crowe.com.ve', 'tovaroliver22@gmail.com', '(0424) - 272 1414', ''),
(181, 181, 'ritcelis.ruiz@crowe.com.ve', '', '(0412) - 309 9563', ''),
(182, 182, 'jenny.lis@crowe.com.ve', '', '(0414) - 206 3611', '(0212) - 763 1690'),
(183, 183, 'cesar.diaz@crowe.com.ve', '', '(0414) - 113 6672', ''),
(184, 184, 'danaleth.hernandez@crowe.com.ve', 'danaleth@gmail.com', '(0426) - 415 7175', '(0412) - 962 9136'),
(185, 185, 'johanna.trujillo@crowe.com.ve', 'jcrevette_@hotmail.com', '(0414) - 286 2057', ''),
(186, 186, 'melanie.marquez@crowe.com.ve', 'melaniealexandra.m@gmail.com', '(0212) - 369 7554', '(0424) - 260 8583'),
(187, 187, 'escarlet.guillen@crowe.com.ve', 'escarletguillen@gmail.com', '(0414) - 917 7892', ''),
(188, 188, 'morrinsonn@gmail.com', 'morrinsonn@gmail.com', '(0424) - 161 6129', '(0212) - 585 8604'),
(189, 189, 'gabigabi175@gmail.com', '', '(0424) - 277 9956', ''),
(190, 190, 'anthoni.freites@gmail.com', '', '(0412) - 209 3659', ''),
(191, 191, 'layajesus@gmail.com', '', '', ''),
(192, 192, 'andrea.garcia@crowe.com.ve', '', '(0424) - 106 1875', ''),
(193, 193, 'oscarrojo999@gmail.com', '', '(0414) - 133 2793', ''),
(194, 194, 'bolivarsierrajose@gmail.com', '', '', ''),
(195, 195, 'wilmer.anton@crowe.com.ve', '', '(0414) - 314 1335', ''),
(196, 196, 'beiker.loyo@crowe.com.ve', '', '(0416) - 053 3153', ''),
(197, 197, 'pablo.mata@crowe.com.ve', '', '(0412) - 639 0605', '(0212) - 714 5134'),
(198, 198, 'brandon.rivera@crowe.com.ve', '', '(0414) - 258 0055', '(0212) - 858 5721'),
(199, 199, 'laura.rojas@crowe.com.ve', '', '(0414) - 160 5224', ''),
(200, 200, 'juan.peñaloza@crowe.com.ve', '', '(0424) - 150 9619', '(0212) - 662 5836'),
(201, 201, 'gabriel.rojas@crowe.com.ve', '', '(0414) - 135 4747', ''),
(202, 202, 'josnely.castillo@crowe.com.ve', '', '(0416) - 836 6572', ''),
(203, 203, 'christhopher.cabrera@crowe.com.ve', '', '(0414) - 186 2719', ''),
(204, 204, 'brandon.rivea@crowe.com.ve', 'brandon.rivea@crowe.com.ve', '(0414) - 258 0055', ''),
(205, 205, 'tomega9120@hotmail.com', 'tomega9120@hotmail.com', '(0414) - 246 6825', '(0212) - 241 3316'),
(206, 206, 'gabriel.mora@crowe.com.ve', '', '(0424) - 147 9638', ''),
(207, 207, 'jhon.martinez@crowe.com.ve', '', '(0424) - 202 4245', ''),
(208, 208, 'keibimoreno@crowehowart.com', '', '(0414) - 126 9931', ''),
(209, 209, 'deiriana.porta@crowe.com.ve', '', '(0424) - 123 5742', ''),
(210, 210, 'ignayari.mendoza@crowe.com.ve', 'cardenaslg2000@yahoo.com', '(0412) - 960 1010', ''),
(211, 211, 'guillermo.loaiza@crowe.com.ve', 'guillermoloaiza2001@gmail.com', '(0424) - 136 5019', ''),
(212, 212, 'cesar.uban@crowe.com.ve', '', '(0414) - 926 7484', ''),
(213, 213, 'dinexy.porta@crowe.com.ve', '', '(0412) - 737 7145', ''),
(214, 214, 'ricardo.leon@crowe.com.ve', '', '(0412) - 211 2830', ''),
(215, 215, 'keibi.moreno@crowe.com.ve', '', '(0414) - 126 9931', ''),
(216, 216, 'barbara.betancourt@crowe.com.ve', '', '(0412) - 637 1772', ''),
(217, 217, 'keiver.avila@crowe.com', '', '(0424) - 148 7560', ''),
(218, 218, 'katherine.hernandez@crowe.com.ve', '', '(0414) - 021 3336', '(0212) - 631 0289'),
(219, 219, 'yulimar.diaz@crowe.com.ve', '', '(0414) - 907 7239', ''),
(220, 220, 'yuri.chacon@crowe.com.ve', '', '(0414) - 466 3156', ''),
(221, 221, 'josmarly.maldonado@crowe.com.ve', '', '(0424) - 279 1966', ''),
(222, 222, 'wilber.algueta@crowe.com.ve', '', '(0424) - 197 8449', ''),
(223, 223, 'jose.castellanos@crowe.com.ve', '', '(0412) - 938 4012', ''),
(224, 224, 'belkis.florean@crowe.com.ve', '', '(0424) - 164 2116', ''),
(225, 225, 'keybert.aparicio@crowe.com.ve', '', '(0412) - 911 8289', ''),
(226, 226, 'douglenis.tabasquez@crowe.com.ve', '', '(0424) - 168 8418', ''),
(227, 227, 'ivana.guilarte@crowe.com.ve', '', '(0414) - 215 5483', ''),
(228, 228, 'alejandra.sanchez@crowe.com.ve', '', '(0424) - 775 5090', ''),
(229, 229, 'jose.hernandez@crowe.com.ve', '', '(0424) - 201 6719', ''),
(230, 230, 'jose.perozo@crowe.com.ve', '', '(0424) - 128 8112', '(0412) - 338 9072'),
(231, 231, 'fernando.rangel@crowe.com.ve', '', '(0424) - 165 3758', ''),
(232, 232, 'angelica.lugo@crowe.com.ve', '', '(0412) - 581 1373', ''),
(233, 233, 'maria.garcia@crowe.com.ve', '', '(0414) - 274 1686', ''),
(234, 234, 'jenny.lima@crowe.com.ve', '', '(0424) - 160 3064', '(0212) - 693 2351'),
(235, 235, 'crisbet.barcelo@crowe.com.ve', '', '(0412) - 731 8638', '(0212) - 753 2952'),
(236, 236, 'arleanny.marrero@crowe.com.ve', '', '(0412) - 335 1501', ''),
(237, 237, 'jose.diaz@crowe.com.ve', '', '(0424) - 298 5622', '(0414) - 990 2681'),
(238, 238, 'orlaimy.muÑoz@gmail.com', '', '(0412) - 714 5942', ''),
(239, 239, 'josman.fuentes@crowe.com.ve', '', '(0414) - 220 4960', ''),
(240, 240, 'jorgenis.guerra@crowe.com.ve', '', '(0412) - 013 6708', '(0212) - 352 7361'),
(241, 241, 'cesar.garcia@crowe.com.ve', '', '(0424) - 238 8485', ''),
(242, 242, 'mays_krv@gmail.com', '', '(0412) - 090 0323', '(0212) - 365 5132'),
(243, 243, 'yulitzaesparragoza@hotmail.com', '', '(0412) - 997 4260', ''),
(244, 244, 'raul.briceño@crowe.com.ve', '', '(0414) - 289 5119', ''),
(245, 245, 'mary.rojas@crowe.com.ve', '', '(0412) - 610 0692', ''),
(246, 246, 'rubi.ramirez@crowe.com.ve', '', '(0412) - 910 1603', ''),
(247, 247, 'carlosnavased@gmail.com', '', '(0424) - 281 3276', ''),
(248, 248, 'miriam.hidalgo@crowe.com.ve', '', '(0412) - 541 1409', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_usuarios_direccion_estado`
--

CREATE TABLE `tbl_usuarios_direccion_estado` (
  `Id` int(11) NOT NULL,
  `NombreEstado` text NOT NULL,
  `Iso3166-2` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_usuarios_direccion_estado`
--

INSERT INTO `tbl_usuarios_direccion_estado` (`Id`, `NombreEstado`, `Iso3166-2`) VALUES
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
(21, 'La Guaira', 'VE-W'),
(22, 'Yaracuy', 'VE-U'),
(23, 'Zulia', 'VE-V'),
(24, 'Distrito Capital', 'VE-A'),
(25, 'Dependencias Federales', 'VE-Z');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_usuarios_direccion_municipio`
--

CREATE TABLE `tbl_usuarios_direccion_municipio` (
  `Id` int(11) NOT NULL,
  `Id_direccion_estado` int(11) NOT NULL,
  `NombreMunicipio` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_usuarios_direccion_municipio`
--

INSERT INTO `tbl_usuarios_direccion_municipio` (`Id`, `Id_direccion_estado`, `NombreMunicipio`) VALUES
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
-- Estructura de tabla para la tabla `tbl_usuarios_direccion_parroquia`
--

CREATE TABLE `tbl_usuarios_direccion_parroquia` (
  `Id` int(11) NOT NULL,
  `Id_direccion_municipio` int(11) NOT NULL,
  `NombreParroquia` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_usuarios_direccion_parroquia`
--

INSERT INTO `tbl_usuarios_direccion_parroquia` (`Id`, `Id_direccion_municipio`, `NombreParroquia`) VALUES
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
-- Estructura de tabla para la tabla `tbl_usuarios_documentoidentidad`
--

CREATE TABLE `tbl_usuarios_documentoidentidad` (
  `Id` int(11) NOT NULL,
  `Id_usuario` int(11) NOT NULL,
  `Id_tipo_documento` int(11) NOT NULL,
  `Descripcion` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_usuarios_documentoidentidad`
--

INSERT INTO `tbl_usuarios_documentoidentidad` (`Id`, `Id_usuario`, `Id_tipo_documento`, `Descripcion`) VALUES
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
(143, 143, 1, '18514042'),
(144, 144, 1, '10785418'),
(145, 145, 1, '10199496'),
(146, 146, 1, '6223515'),
(147, 147, 1, '6848046'),
(148, 148, 1, '12563973'),
(149, 149, 1, '6469179'),
(150, 150, 1, '3627793'),
(151, 151, 1, '10793321'),
(152, 152, 1, '7927670'),
(153, 153, 1, '633451'),
(154, 154, 1, '10785418'),
(155, 155, 1, '15199375'),
(156, 156, 1, '11070302'),
(157, 157, 1, '26478278'),
(158, 158, 1, '25235222'),
(159, 159, 1, '14196784'),
(160, 160, 1, '25565600'),
(161, 161, 1, '24220165'),
(162, 162, 1, '6854119'),
(163, 163, 1, '19814106'),
(164, 164, 1, '13847048'),
(173, 173, 1, '21072946'),
(174, 174, 1, '17441357'),
(175, 175, 1, '21115694'),
(176, 176, 1, '26868454'),
(177, 177, 1, '26281391'),
(178, 178, 1, '25489425'),
(179, 179, 1, '24803900'),
(180, 180, 1, '27439433'),
(181, 181, 1, '22026352'),
(182, 182, 1, '15801257'),
(183, 183, 1, '16971805'),
(184, 184, 1, '27686406'),
(185, 185, 1, '15049858'),
(186, 186, 1, '27776757'),
(187, 187, 1, '25871022'),
(188, 188, 1, '25207065'),
(189, 189, 2, '82216573'),
(190, 190, 1, '14142307'),
(191, 191, 1, '13309819'),
(192, 192, 1, '19395321'),
(193, 193, 1, '26818220'),
(194, 194, 1, '12056264'),
(195, 195, 1, '6854119'),
(196, 196, 1, '18760146'),
(197, 197, 1, '26283747'),
(198, 198, 1, '26435330'),
(199, 199, 1, '23634205'),
(200, 200, 1, '24210460'),
(201, 201, 1, '18002943'),
(202, 202, 1, '27807228'),
(203, 203, 1, '22671474'),
(204, 204, 1, '26435330'),
(205, 205, 1, '4220335'),
(206, 206, 1, '24934009'),
(207, 207, 1, '19065366'),
(208, 208, 1, '24211107'),
(209, 209, 1, '25626353'),
(210, 210, 1, '13712129'),
(211, 211, 1, '28448828'),
(212, 212, 1, '25846761'),
(213, 213, 1, '22671805'),
(214, 214, 1, '28155996'),
(215, 215, 1, '24211107'),
(216, 216, 1, '30328073'),
(217, 217, 1, '25751852'),
(218, 218, 1, '21623432'),
(219, 219, 1, '13735588'),
(220, 220, 1, '20225308'),
(221, 221, 1, '25217291'),
(222, 222, 1, '28481236'),
(223, 223, 1, '17850961'),
(224, 224, 2, '83032310'),
(225, 225, 1, '22670339'),
(226, 226, 1, '26104063'),
(227, 227, 1, '27678318'),
(228, 228, 1, '21418901'),
(229, 229, 1, '26740424'),
(230, 230, 1, '23817163'),
(231, 231, 1, '24440938'),
(232, 232, 1, '28101506'),
(233, 233, 1, '28345876'),
(234, 234, 1, '6266290'),
(235, 235, 1, '19205729'),
(236, 236, 1, '29529359'),
(237, 237, 1, '26180223'),
(238, 238, 1, '27773891'),
(239, 239, 1, '27318975'),
(240, 240, 1, '24806247'),
(241, 241, 1, '29593134'),
(242, 242, 1, '12834734'),
(243, 243, 1, '15931607'),
(244, 244, 1, '20304026'),
(245, 245, 1, '7927516'),
(246, 246, 1, '11784700'),
(247, 247, 1, '22667607'),
(248, 248, 1, '15759452');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_usuarios_documentoidentidad_tipo`
--

CREATE TABLE `tbl_usuarios_documentoidentidad_tipo` (
  `Id` int(11) NOT NULL,
  `Abreviatura` varchar(5) NOT NULL,
  `Descripcion` varchar(20) NOT NULL,
  `Id_estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_usuarios_documentoidentidad_tipo`
--

INSERT INTO `tbl_usuarios_documentoidentidad_tipo` (`Id`, `Abreviatura`, `Descripcion`, `Id_estatus`) VALUES
(1, 'V', 'Cédula Venezolana', 1),
(2, 'E', 'Cédula Extranjera', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbl_usuarios_jerarquia_cargo`
--

CREATE TABLE `tbl_usuarios_jerarquia_cargo` (
  `Id` int(11) NOT NULL,
  `Descripcion` text NOT NULL,
  `Id_TipoCargo` int(11) NOT NULL,
  `Jerarquia` int(11) NOT NULL,
  `Id_Estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_usuarios_jerarquia_cargo`
--

INSERT INTO `tbl_usuarios_jerarquia_cargo` (`Id`, `Descripcion`, `Id_TipoCargo`, `Jerarquia`, `Id_Estatus`) VALUES
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
-- Estructura de tabla para la tabla `tbl_usuarios_jerarquia_division`
--

CREATE TABLE `tbl_usuarios_jerarquia_division` (
  `Id` int(11) NOT NULL,
  `Descripcion` text NOT NULL,
  `Id_Estatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tbl_usuarios_jerarquia_division`
--

INSERT INTO `tbl_usuarios_jerarquia_division` (`Id`, `Descripcion`, `Id_Estatus`) VALUES
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
(19, 'Administración', 1),
(20, 'Crowe Anzoátegui (PLC)', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tbl_control_encryptkey`
--
ALTER TABLE `tbl_control_encryptkey`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_encrypt_status` (`Id_estatus`);

--
-- Indices de la tabla `tbl_control_error`
--
ALTER TABLE `tbl_control_error`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_error_tipomensaje` (`Id_error_tipomensaje`),
  ADD KEY `FK_error_tipoobjeto` (`Id_error_tipoobjeto`),
  ADD KEY `FK_error_estatus` (`Id_estatus`);

--
-- Indices de la tabla `tbl_control_error_tipomensaje`
--
ALTER TABLE `tbl_control_error_tipomensaje`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `tbl_control_error_tipoobjeto`
--
ALTER TABLE `tbl_control_error_tipoobjeto`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `tbl_control_estatus`
--
ALTER TABLE `tbl_control_estatus`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `tbl_control_logs_bitacora`
--
ALTER TABLE `tbl_control_logs_bitacora`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `tbl_control_logs_bitacora_accion`
--
ALTER TABLE `tbl_control_logs_bitacora_accion`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `tbl_control_tipocargo`
--
ALTER TABLE `tbl_control_tipocargo`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `tbl_usuarios`
--
ALTER TABLE `tbl_usuarios`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_cargo_usuario` (`Id_jerarquia_cargo`),
  ADD KEY `FK_division_usuario` (`Id_jerarquia_division`),
  ADD KEY `FK_parroquia_usuario` (`Id_direccion_parroquia`),
  ADD KEY `FK_estado_usuario` (`Id_estatus`);

--
-- Indices de la tabla `tbl_usuarios_contacto`
--
ALTER TABLE `tbl_usuarios_contacto`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_contacto_usuario` (`Id_usuario`);

--
-- Indices de la tabla `tbl_usuarios_direccion_estado`
--
ALTER TABLE `tbl_usuarios_direccion_estado`
  ADD PRIMARY KEY (`Id`);

--
-- Indices de la tabla `tbl_usuarios_direccion_municipio`
--
ALTER TABLE `tbl_usuarios_direccion_municipio`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_municipio_estado` (`Id_direccion_estado`);

--
-- Indices de la tabla `tbl_usuarios_direccion_parroquia`
--
ALTER TABLE `tbl_usuarios_direccion_parroquia`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_parroquia_municipio` (`Id_direccion_municipio`);

--
-- Indices de la tabla `tbl_usuarios_documentoidentidad`
--
ALTER TABLE `tbl_usuarios_documentoidentidad`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_usuario_documento` (`Id_usuario`),
  ADD KEY `FK_tipo_documento` (`Id_tipo_documento`);

--
-- Indices de la tabla `tbl_usuarios_documentoidentidad_tipo`
--
ALTER TABLE `tbl_usuarios_documentoidentidad_tipo`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_tipo_estatus` (`Id_estatus`);

--
-- Indices de la tabla `tbl_usuarios_jerarquia_cargo`
--
ALTER TABLE `tbl_usuarios_jerarquia_cargo`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_usuarios_tipo_cargo` (`Id_TipoCargo`),
  ADD KEY `FK_usuarios_cargo_estado` (`Id_Estatus`);

--
-- Indices de la tabla `tbl_usuarios_jerarquia_division`
--
ALTER TABLE `tbl_usuarios_jerarquia_division`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `FK_usuarios_division_estado` (`Id_Estatus`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbl_control_encryptkey`
--
ALTER TABLE `tbl_control_encryptkey`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tbl_control_error`
--
ALTER TABLE `tbl_control_error`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tbl_control_error_tipomensaje`
--
ALTER TABLE `tbl_control_error_tipomensaje`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_control_error_tipoobjeto`
--
ALTER TABLE `tbl_control_error_tipoobjeto`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tbl_control_estatus`
--
ALTER TABLE `tbl_control_estatus`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_control_logs_bitacora`
--
ALTER TABLE `tbl_control_logs_bitacora`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `tbl_control_logs_bitacora_accion`
--
ALTER TABLE `tbl_control_logs_bitacora_accion`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tbl_control_tipocargo`
--
ALTER TABLE `tbl_control_tipocargo`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tbl_usuarios`
--
ALTER TABLE `tbl_usuarios`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=249;

--
-- AUTO_INCREMENT de la tabla `tbl_usuarios_contacto`
--
ALTER TABLE `tbl_usuarios_contacto`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=249;

--
-- AUTO_INCREMENT de la tabla `tbl_usuarios_direccion_estado`
--
ALTER TABLE `tbl_usuarios_direccion_estado`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `tbl_usuarios_direccion_municipio`
--
ALTER TABLE `tbl_usuarios_direccion_municipio`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=463;

--
-- AUTO_INCREMENT de la tabla `tbl_usuarios_direccion_parroquia`
--
ALTER TABLE `tbl_usuarios_direccion_parroquia`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1139;

--
-- AUTO_INCREMENT de la tabla `tbl_usuarios_documentoidentidad`
--
ALTER TABLE `tbl_usuarios_documentoidentidad`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=249;

--
-- AUTO_INCREMENT de la tabla `tbl_usuarios_documentoidentidad_tipo`
--
ALTER TABLE `tbl_usuarios_documentoidentidad_tipo`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbl_usuarios_jerarquia_cargo`
--
ALTER TABLE `tbl_usuarios_jerarquia_cargo`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `tbl_usuarios_jerarquia_division`
--
ALTER TABLE `tbl_usuarios_jerarquia_division`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tbl_control_encryptkey`
--
ALTER TABLE `tbl_control_encryptkey`
  ADD CONSTRAINT `FK_encrypt_status` FOREIGN KEY (`Id_estatus`) REFERENCES `tbl_control_estatus` (`Id`);

--
-- Filtros para la tabla `tbl_control_error`
--
ALTER TABLE `tbl_control_error`
  ADD CONSTRAINT `FK_error_estatus` FOREIGN KEY (`Id_estatus`) REFERENCES `tbl_control_estatus` (`Id`),
  ADD CONSTRAINT `FK_error_tipomensaje` FOREIGN KEY (`Id_error_tipomensaje`) REFERENCES `tbl_control_error_tipomensaje` (`Id`),
  ADD CONSTRAINT `FK_error_tipoobjeto` FOREIGN KEY (`Id_error_tipoobjeto`) REFERENCES `tbl_control_error_tipoobjeto` (`Id`);

--
-- Filtros para la tabla `tbl_usuarios`
--
ALTER TABLE `tbl_usuarios`
  ADD CONSTRAINT `FK_cargo_usuario` FOREIGN KEY (`Id_jerarquia_cargo`) REFERENCES `tbl_usuarios_jerarquia_cargo` (`Id`),
  ADD CONSTRAINT `FK_division_usuario` FOREIGN KEY (`Id_jerarquia_division`) REFERENCES `tbl_usuarios_jerarquia_division` (`Id`),
  ADD CONSTRAINT `FK_estado_usuario` FOREIGN KEY (`Id_estatus`) REFERENCES `tbl_control_estatus` (`Id`),
  ADD CONSTRAINT `FK_parroquia_usuario` FOREIGN KEY (`Id_direccion_parroquia`) REFERENCES `tbl_usuarios_direccion_parroquia` (`Id`);

--
-- Filtros para la tabla `tbl_usuarios_contacto`
--
ALTER TABLE `tbl_usuarios_contacto`
  ADD CONSTRAINT `FK_contacto_usuario` FOREIGN KEY (`Id_usuario`) REFERENCES `tbl_usuarios` (`Id`);

--
-- Filtros para la tabla `tbl_usuarios_direccion_municipio`
--
ALTER TABLE `tbl_usuarios_direccion_municipio`
  ADD CONSTRAINT `FK_municipio_estado` FOREIGN KEY (`Id_direccion_estado`) REFERENCES `tbl_usuarios_direccion_estado` (`Id`);

--
-- Filtros para la tabla `tbl_usuarios_direccion_parroquia`
--
ALTER TABLE `tbl_usuarios_direccion_parroquia`
  ADD CONSTRAINT `FK_parroquia_municipio` FOREIGN KEY (`Id_direccion_municipio`) REFERENCES `tbl_usuarios_direccion_municipio` (`Id`);

--
-- Filtros para la tabla `tbl_usuarios_documentoidentidad`
--
ALTER TABLE `tbl_usuarios_documentoidentidad`
  ADD CONSTRAINT `FK_tipo_documento` FOREIGN KEY (`Id_tipo_documento`) REFERENCES `tbl_usuarios_documentoidentidad_tipo` (`Id`),
  ADD CONSTRAINT `FK_usuario_documento` FOREIGN KEY (`Id_usuario`) REFERENCES `tbl_usuarios` (`Id`);

--
-- Filtros para la tabla `tbl_usuarios_documentoidentidad_tipo`
--
ALTER TABLE `tbl_usuarios_documentoidentidad_tipo`
  ADD CONSTRAINT `FK_tipo_estatus` FOREIGN KEY (`Id_estatus`) REFERENCES `tbl_control_estatus` (`Id`);

--
-- Filtros para la tabla `tbl_usuarios_jerarquia_cargo`
--
ALTER TABLE `tbl_usuarios_jerarquia_cargo`
  ADD CONSTRAINT `FK_usuarios_cargo_estado` FOREIGN KEY (`Id_Estatus`) REFERENCES `tbl_control_estatus` (`Id`),
  ADD CONSTRAINT `FK_usuarios_tipo_cargo` FOREIGN KEY (`Id_TipoCargo`) REFERENCES `tbl_control_tipocargo` (`Id`);

--
-- Filtros para la tabla `tbl_usuarios_jerarquia_division`
--
ALTER TABLE `tbl_usuarios_jerarquia_division`
  ADD CONSTRAINT `FK_usuarios_division_estado` FOREIGN KEY (`Id_Estatus`) REFERENCES `tbl_control_estatus` (`Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

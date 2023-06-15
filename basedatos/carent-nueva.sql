-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-06-2023 a las 15:33:22
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

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
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insert_logs` (IN `p_action_id` INT, IN `p_log_description` TEXT, IN `p_user_responsible_ip` VARCHAR(255), IN `p_user_responsible_id` INT, IN `p_affected_table` TEXT, IN `p_query_sql` MEDIUMTEXT, IN `p_old_value` TEXT, IN `p_new_value` TEXT, IN `p_register_date` DATETIME, OUT `p_json_response` TEXT)   BEGIN
DECLARE v_customErrorMessage TEXT;
DECLARE v_customError CONDITION FOR SQLSTATE '45000';
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_msg = MESSAGE_TEXT;
        SET v_customErrorMessage = CONCAT("Ha ocurrido un error en el registro de datos en la bitacora: (",@code,") ",@error_msg);
        ROLLBACK;
        #Guardamos la data en p_jsonResponse
        CALL sp_sql_exceptions(1,1,"sp_insert_Log",v_customErrorMessage,@response);
        SET p_json_response = (SELECT @response);
    END;
#Error Personalizado
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	#Registramos el error
        CALL sp_sql_exceptions(1,1,"sp_insert_Log",v_customErrorMessage,@responseCustom);
        SET p_json_response = (SELECT @responseCustom);
    END;
#Verificación de errores internos
SET @existActionType = (SELECT COUNT(la.action_id) FROM control_logs_action la WHERE la.action_id = p_action_id);
SET @existUser = (SELECT COUNT(u.user_id) FROM users u WHERE u.user_id = p_user_responsible_id);

IF @existActionType = 0 AND @existUser = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message": "Error 0004: El ID_ACCION_BITACORA(',p_action_id,'), 
                                      and ID_USER(',p_user_responsible_id,') does not exist"}');
    SIGNAL SQLSTATE '45000'; #Disparo el error	
END IF;

IF @existActionType = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message": "Error 0005: ID_ACCION_BITACORA(',p_action_id,') does not exist"}');
    SIGNAL SQLSTATE '45000'; #Disparo el error
END IF;

IF @existUser = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message": "Error 0006: ID_USER(',p_user_responsible_id,') does not exist"}');
    SIGNAL SQLSTATE '45000'; #Disparo el error
END IF;

##En caso que logren pasar todas las validaciones
INSERT INTO `control_logs`(`action_id`, `log_description`, `user_responsible_ip`, `user_responsible_id`, `affected_table`, `query_sql`, `old_value`, `new_value`, `register_date`) 
VALUES (p_action_id,p_log_description,p_user_responsible_ip,p_user_responsible_id,
        p_affected_table,p_query_sql,p_old_value,p_new_value,p_register_date);
        
#Si no genero SQLEXCEPTION DEVOLVEMOS UN JSON
SET p_json_response = CONCAT('{"response":true, "message": "Se han modificado algunas tablas y se han registrado en la bitacora"}');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_login` (IN `p_user_code` VARCHAR(6), IN `p_user_password` TEXT, IN `p_user_ip` VARCHAR(39), OUT `p_json_response` TEXT)   BEGIN
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
        CALL sp_sql_exceptions(1,1,"sp_login",v_customErrorMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	CALL sp_sql_exceptions(1,1,"sp_login",v_customErrorMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
#Validaciones de usuario
SET @existUser = (SELECT COUNT(us.user_id) FROM users us WHERE us.user_code = p_user_code AND us.status_id = 1 LIMIT 1);
IF @existUser = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message":"Error 0007: ID_CODE(',p_user_code,') does not exist or have access denied"}');
    SIGNAL SQLSTATE '45000'; #Disparabamos el error si no existe el usuario
END IF;
#Validacion de llave de Encrypt
SET @keyEncrypt = (SELECT ek.encrypt_key FROM control_encrypts ek WHERE ek.status_id = 1 LIMIT 1);
IF @keyEncrypt = 0 OR @keyEncrypt IS NULL THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message":"Error 0008: there are no registered encryption keys"}');
    SIGNAL SQLSTATE '45000'; #Disparamos el error si no existe Key Encrypt
END IF;

#Validamos la contraseña
SET @truePassword =(SELECT CAST(AES_DECRYPT(us.password,@keyEncrypt) AS CHAR) FROM users us WHERE us.user_code = p_user_code AND us.status_id = 1);
IF CAST(p_user_password AS CHAR) != @truePassword OR @truePassword IS NULL THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message":"Error 0009: Password incorrect, insert again"}');
    SIGNAL SQLSTATE '45000'; #Disparamos el error si las contraseñas no coinciden
END IF;

#Validamos cambio de la clave
SET @changePass = (SELECT IF(us.time_change_password > CURDATE(), false, true) FROM users us WHERE us.user_code = p_user_code AND us.status_id = 1);
IF @changePass IS NULL THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message":"Error 0010: ID_CODE(',p_user_code,') it is inactive"}');
    SIGNAL SQLSTATE '45000'; #Disparamos el error si no devuelve nada la consulta
END IF;

#Capturamos el ultimo login del usuario
SET @idUser = (SELECT us.user_id FROM users Us WHERE us.user_code = p_user_code LIMIT 1);
SET @lastInsert = (SELECT MAX(cl2.log_id) FROM control_logs cl2 WHERE cl2.user_responsible_id = @idUser 
                   AND cl2.log_description LIKE "login%" LIMIT 1); #Almacena el ultimo insert de login
SET @lastLogin = (SELECT us.login_date FROM users us WHERE us.user_id = @IdUser LIMIT 1); #Almacena la ultima fecha de login
SET @lastIp = (SELECT IFNULL(cl.user_responsible_ip,"0.0.0.0") FROM control_logs cl WHERE cl.log_id = @lastInsert); #Almacena la ultima IP

#Actualizamos el ultimo login
SET @dateNow = (SELECT SYSDATE());
UPDATE users u SET u.login_date = @dateNow WHERE u.user_code = p_user_code;
SET @querySql = CONCAT("UPDATE users u SET u.login_date = ",@dateNow," WHERE u.user_code = ",p_user_code,";");

#Acomodamos el ultimo valor y el nuevo
SET @lastValue = CONCAT('{"date_last_login": "',@lastLogin,'","last_ip": "',@lastIp,'"}');
SET @newValue = CONCAT('{"date_last_login": "',@dateNow,'","last_ip": "',p_user_ip,'"}');

#Preparamos las variables para response
SET @positionId = (SELECT IFNULL(us.position_id,0) FROM users us WHERE us.user_id = @idUser LIMIT 1);
SET @departmentId = (SELECT IFNULL(us.department_id,0) FROM users us WHERE us.user_id = @idUser LIMIT 1);
SET @emailId = (SELECT IFNULL(uc.primary_email,uc.secondary_email) FROM users_contact uc WHERE uc.user_id = @idUser LIMIT 1);

CALL sp_insert_logs(2,"login",p_user_ip,@idUser,"users",@querySql,@lastValue,@newValue,@dateNow,@jsonResponse);
#Extraemos el JSON a una variable
SET @responseJson = JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.response'));

#Verificamos si registró efectivamente en la bitacora
IF @responseJson != "true" THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message":"Error 0011: Insert log has failed (',JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.message')),')"}');
    SIGNAL SQLSTATE '45000'; #Si no ha podido registrar nada, dispara el error
END IF;

#Si paso todas las validaciones procedemos a registrar
SET p_json_response = CONCAT('{"passwordChange": ',@changePass,',"positionId": ',@positionId,', 
                            "departmentId": ',@departmentId,',"userId": ',@idUser,',
                            "emailUser": "',@emailId,'","message": "Bienvenido. En breves momentos será redireccionado...",
                            "response": true}');
END$$

CREATE DEFINER=`root`@`127.0.0.1` PROCEDURE `sp_new_clients` (IN `p_partner_user_id` INT, IN `p_rif` VARCHAR(30), IN `p_nit` INT, IN `p_bussiness_name` VARCHAR(60), IN `p_country_id` INT, IN `p_address` TEXT, IN `p_tax_phone` VARCHAR(30), IN `p_website` VARCHAR(150), IN `p_tax_email` VARCHAR(100), IN `p_sector_id` INT, IN `p_service_id` INT, IN `p_user_id` INT, IN `p_user_ip` VARCHAR(40), OUT `p_json_response` TEXT)   BEGIN
DECLARE v_customMessage TEXT;
DECLARE v_CustomError CONDITION FOR SQLSTATE '45000';
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_msj = MESSAGE_TEXT;
        ROLLBACK;
        SET v_customMessage = CONCAT("Se ha producido un error en la consulta: (",@code,") ",@error_msj);
        #Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_new_clients",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	#Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_new_clients",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
#Validacion del Usuario Socio
SET @isExist = (SELECT COUNT(us.user_id) FROM users us WHERE us.user_id = p_partner_user_id LIMIT 1);
IF @isExist = 0 THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0014: partner does not exist (',p_partner_user_id,')"}');
    #Activamos el error
    SIGNAL SQLSTATE '45000';
END IF;

#Capturamos el ultimo login del usuario quien esta registrando el nuevo cliente
SET @lastInsert = (SELECT MAX(cl2.log_id) FROM control_logs cl2 WHERE cl2.user_responsible_id = p_user_id 
                   AND cl2.log_description LIKE "createClient%" LIMIT 1); #Almacena el ultimo insert de creacion de Cliente
SET @lastLogin = (SELECT us.login_date FROM users us WHERE us.user_id = p_user_id LIMIT 1); #Almacena la ultima fecha de login
SET @lastIp = (SELECT IFNULL(cl.user_responsible_ip,"0.0.0.0") FROM control_logs cl WHERE cl.log_id = @lastInsert); #Almacena la ultima IP

#Si existe el usuario socio, procedemos a crear al cliente
SET @dateNow = (SELECT SYSDATE());

#Acomodamos el ultimo valor y el nuevo
SET @lastValue = CONCAT('{"ultima_ip":"',@lastIp,'"}');
SET @newValue = CONCAT('{"ultima_ip":"',p_user_ip,'"}');

#Obtenemos el ultimo codigo registrado
SET @lastInsertId = (SELECT MAX(client_id) FROM clients);
SET @lastCodeClient = (SELECT cs.client_code FROM clients cs WHERE cs.client_id = @lastInsertId) + 1;

#Creamos el nuevo cliente
INSERT INTO `clients`(`partner_user_id`, `client_code`, `rif`, `nit`, `bussiness_name`, `country_id`, `client_address`, `tax_phone`, `website`, `tax_email`, `sector_id`, `service_id`, `status_id`) VALUES (p_partner_user_id,@lastCodeClient,p_rif,p_nit,p_bussiness_name,p_country_id,p_address,p_tax_phone,p_website,p_tax_email,p_sector_id,p_service_id,1);

SET @querySql = CONCAT('INSERT INTO `clients`(`partner_user_id`, `client_code`, `rif`, `nit`, `bussiness_name`, `country_id`, `client_address`, `tax_phone`, `website`, `tax_email`, `sector_id`, `service_id`, `status_id`) VALUES (p_partner_user_id,',@lastCodeClient,',p_rif,p_nit,p_bussiness_name,p_country_id,p_address,p_tax_phone,p_website,p_tax_email,p_sector_id,p_servicio_id,1);');

CALL sp_insert_logs(1,"createClient",p_user_ip,p_user_id,"clients",@querySql,@lastValue,@newValue,@dateNow,@jsonResponse);
#Extraemos el JSON a una variable
SET @responseJson = JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.response'));

#Verificamos si registró efectivamente en la bitacora
IF @responseJson != "true" THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0011: Insert log has failed (',JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.message')),')"}');
    SIGNAL SQLSTATE '45000'; #Si no ha podido registrar nada, dispara el error
END IF;

#Si paso toda las validaciones
SET p_json_response = CONCAT('{"response":true,"message":"Client ',p_bussiness_name,' created. Code: ',@lastCodeClient,'"}');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_new_contact_users` (IN `p_user_code` VARCHAR(6), IN `p_primary_email` VARCHAR(255), IN `p_secondary_email` VARCHAR(255), IN `p_primary_phone` VARCHAR(30), IN `p_secondary_phone` VARCHAR(30), IN `p_identity_type` VARCHAR(5), IN `p_identity_number` VARCHAR(255), OUT `p_json_response` TEXT)   BEGIN
DECLARE v_customMessage TEXT;
DECLARE v_customError CONDITION FOR SQLSTATE '45000';
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_msj = MESSAGE_TEXT;
        ROLLBACK;
        SET v_customMessage = CONCAT("Se ha producido un error en la consulta: (",@code,") ",@error_msj);
        #Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_new_contact_users",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
DECLARE EXIT HANDLER FOR v_CustomError
	BEGIN
    	#Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_new_contact_users",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
#Validacion del Usuario
SET @isExist = (SELECT COUNT(us.user_id) FROM users us WHERE us.user_code = p_user_code LIMIT 1);
IF @isExist = 0 THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0016: This users is already registered (',p_user_code,')"}');
    #Activamos el error
    SIGNAL SQLSTATE '45000';
END IF;

#Si existe procedemos a crearle contacto
SET @idUser = (SELECT us.user_id FROM users us WHERE us.user_code = p_user_code LIMIT 1);
#Validacion de la cedula
SET @identityType = (SELECT ut.identity_type_id FROM users_identity_type ut WHERE ut.identity_abbreviation = p_identity_type LIMIT 1);
SET @documentExist = (SELECT COUNT(ui.identity_id) FROM users_identity ui WHERE ui.identity_number = p_identity_number AND ui.identity_type_id = @identityType LIMIT 1);
IF @documentExist != 0 THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0017: This identity is already registered (',p_identity_number,')"}');
    DELETE FROM users WHERE user_id = @idUser;
	SIGNAL SQLSTATE '45000';
END IF;
#Contacto
INSERT INTO `users_contact`(`user_id`, `primary_email`, `secondary_email`, `primary_phone`, `secondary_phone`) VALUES (@idUser,p_primary_email,p_secondary_email,p_primary_phone,p_secondary_phone);
#Documento
INSERT INTO `users_identity`(`user_id`, `identity_type_id`, `identity_number`) VALUES (@idUser,@identityType,p_identity_number);

#Devolvemos el response si no hay ningun SQL Error
SET p_json_response = CONCAT('{"response":true,"message":"Contact users registered succesfully ',p_user_code,'"}');
END$$

CREATE DEFINER=`root`@`127.0.0.1` PROCEDURE `sp_new_projects` (IN `p_description` TEXT, IN `p_client_id` INT, IN `p_status_id` INT, IN `p_manager_id` INT, IN `p_partner_id` INT, IN `p_quality_partner_id` INT, IN `p_currency_id` INT, IN `p_company_id` INT, IN `p_hiring_date` DATE, IN `p_project_value` DECIMAL(25,2), IN `p_user_id` INT, IN `p_user_ip` VARCHAR(255), OUT `p_json_response` TEXT)   BEGIN
DECLARE v_customMessage TEXT;
DECLARE v_customError CONDITION FOR SQLSTATE '45000';
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_msj = MESSAGE_TEXT;
        ROLLBACK;
        SET v_customMessage = CONCAT("Se ha producido un error en la consulta: (",@code,") ",@error_msj);
        #Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_new_projects",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	#Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_new_projects",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
#Validacion del Cliente
SET @isExist = (SELECT COUNT(cs.client_id) FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
IF @isExist = 0 THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0016: client does not exist (',p_client_id,')"}');
    #Activamos el error
    SIGNAL SQLSTATE '45000';
END IF;

#Capturamos el ultimo login del usuario quien esta registrando el nuevo proyecto
SET @lastInsert = (SELECT MAX(cl2.log_id) FROM control_logs cl2 WHERE cl2.user_responsible_id = p_user_id 
                   AND cl2.log_description LIKE "createProject%" LIMIT 1); #Almacena el ultimo insert de creacion de Proyecto
SET @lastLogin = (SELECT us.login_date FROM users us WHERE us.user_id = p_user_id LIMIT 1); #Almacena la ultima fecha de login
SET @lastIp = (SELECT IFNULL(cl.user_responsible_ip,"0.0.0.0") FROM control_logs cl WHERE cl.log_id = @lastInsert); #Almacena la ultima IP

#Si existe el usuario socio, procedemos a crear al cliente
SET @dateNow = (SELECT SYSDATE());

#Acomodamos el ultimo valor y el nuevo
SET @lastValue = CONCAT('{"ultima_ip":"',@lastIp,'"}');
SET @newValue = CONCAT('{"ultima_ip":"',p_user_ip,'"}');

#Creamos el nuevo proyecto
INSERT INTO `projects`(`project_description`, `client_id`, `partner_id`, `quality_partner_id`, `manager_id`, `hiring_date`, `project_value`, `currency_id`, `company_id`, `status_id`) VALUES (p_description,p_client_id,p_partner_id,p_quality_partner_id,p_manager_id,p_hiring_date,p_project_value,p_currency_id,p_company_id,p_status_id);

SET @querySql = CONCAT('INSERT INTO `projects`(`project_description`, `client_id`, `partner_id`, `quality_partner_id`, `manager_id`, `hiring_date`, `project_value`, `currency_id`, `company_id`, `status_id`) VALUES (p_description,',p_client_id,',p_partner_id,p_quality_partner_id,p_manager_id,p_hiring_date,p_project_value,p_currency_id,p_company_id,p_status_id);');

CALL sp_insert_logs(1,"createProject",p_user_ip,p_user_id,"projects",@querySql,@lastValue,@newValue,@dateNow,@jsonResponse);
#Extraemos el JSON a una variable
SET @responseJson = JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.response'));

#Verificamos si registró efectivamente en la bitacora
IF @responseJson != "true" THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0011: Insert log has failed (',JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.message')),')"}');
    SIGNAL SQLSTATE '45000'; #Si no ha podido registrar nada, dispara el error
END IF;

#Si paso toda las validaciones
SET p_json_response = CONCAT('{"response":true,"message":"Project ',p_description,' has been created."}');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_new_users` (IN `p_first_name` VARCHAR(20), IN `p_second_name` VARCHAR(20), IN `p_first_surname` VARCHAR(20), IN `p_second_surname` VARCHAR(20), IN `p_user_code` VARCHAR(6), IN `p_birthday` DATE, IN `p_admission_date` DATE, IN `p_identity_number` VARCHAR(255), IN `p_parish_id` INT, IN `p_position_id` INT, IN `p_department_id` INT, IN `p_user_id` INT, IN `p_user_ip` VARCHAR(39), OUT `p_json_response` TEXT)   BEGIN
DECLARE v_customMessage TEXT;
DECLARE v_customError CONDITION FOR SQLSTATE '45000';
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_msj = MESSAGE_TEXT;
        ROLLBACK;
        SET v_customMessage = CONCAT("Se ha producido un error en la consulta: (",@code,") ",@error_msj);
        #Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_new_users",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	#Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_new_users",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
#Validacion del Usuario
SET @isExist = (SELECT COUNT(us.user_id) FROM users us WHERE us.user_code = p_user_code LIMIT 1);
IF @isExist = 1 THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0016: This user is already registered (',p_user_code,')"}');
    #Activamos el error
    SIGNAL SQLSTATE '45000';
END IF;

#Capturamos el ultimo login del usuario quien esta registrando el nuevo miembro
SET @lastInsert = (SELECT MAX(cl.log_id) FROM control_logs cl WHERE cl.user_responsible_id = p_user_id
                   AND cl.log_description LIKE "createUser%" LIMIT 1); #Almacena el ultimo insert de creacion de Usuario
SET @lastLogin = (SELECT us.login_date FROM users us WHERE us.user_id = p_user_id LIMIT 1); #Almacena la ultima fecha de login
SET @lastIp = (SELECT IFNULL(cl.user_responsible_ip,"0.0.0.0") FROM control_logs cl WHERE cl.log_id = @lastInsert); #Almacena la ultima IP

#Si no existe procedemos a registrar al usuario
SET @key = (SELECT ek.encrypt_key FROM control_encrypts ek WHERE ek.status_id = 1 LIMIT 1);
SET @dateNow = (SELECT SYSDATE());
SET @dateChange = (SELECT DATE(SYSDATE()+90));

INSERT INTO `users`(`user_code`, `password`, `time_change_password`, `first_name`, `second_name`, `first_surname`, `second_surname`, `birthday`, `position_id`, `department_id`, `parish_id`, `admission_date`, `departure_date`, `login_date`, `status_id`)
VALUES(p_user_code,AES_ENCRYPT(p_identity_number,@key),@dateChange,p_first_name,p_second_name,p_first_surname,p_second_surname,p_birthday,p_position_id,p_department_id,p_parish_id,p_admission_date,NULL,NULL,1);

#Guardamos el SQL
SET @querySql = CONCAT('INSERT INTO `users`(`user_code`, `password`, `time_change_password`, `first_name`, `second_name`, `first_surname`, `second_surname`, `birthday`, `position_id`, `department_id`, `parish_id`, `admission_date`, `departure_date`, `login_date`, `status_id`)
VALUES(',p_user_code,',AES_ENCRYPT(p_identity_number,@key),@dateChange,p_first_name,p_second_name,p_first_surname,p_second_surname,p_birthday,p_position_id,p_department_id,p_parish_id,p_admission_date,NULL,NULL,1);');

#Acomodamos el ultimo valor y el nuevo
SET @lastValue = CONCAT('{"ultima_ip":"',@lastIp,'"}');
SET @newValue = CONCAT('{"ultima_ip":"',p_user_ip,'"}');

CALL sp_insert_logs(1,"createUser",p_user_ip,p_user_id,"users",@querySql,@lastValue,@newValue,@dateNow,@jsonResponse);
#Extraemos el JSON a una variable
SET @responseJson = JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.response'));

#Verificamos si registró efectivamente en la bitacora
IF @responseJson != "true" THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0011: Insert log has failed (',JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.message')),')"}');
    SIGNAL SQLSTATE '45000'; #Si no ha podido registrar nada, dispara el error
END IF;

#Si paso toda las validaciones
SET p_json_response = CONCAT('{"response":true,"message":"User ',p_user_code,' created succesfully"}');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_sql_exceptions` (IN `p_type_object_id` INT, IN `p_type_message_id` INT, IN `p_affected_object` VARCHAR(255), IN `p_error_message` TEXT, OUT `p_error_response` TEXT)   BEGIN
DECLARE v_customErrorMessage TEXT;
DECLARE v_customError CONDITION FOR SQLSTATE '45000';
#Gestión de errores
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_string = MESSAGE_TEXT;
        SELECT CONCAT('{"response": false, "message": "Error con el código ',@code,':'
                      ,@error_string,'"}') INTO p_error_response;
    END;
#Error personalizado
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	SET p_error_response = v_customErrorMessage;
    END;

#Init de errores
SET @existObject = (SELECT COUNT(eo.type_object_id) FROM control_errors_type_object eo WHERE eo.type_object_id = p_type_object_id LIMIT 1);
SET @existMessage = (SELECT COUNT(em.type_message_id) FROM control_errors_type_messages em WHERE em.type_message_id = p_type_message_id LIMIT 1);

#Condicionales de error
#Verifica si mas de uno no existe
IF @existObject = 0 AND @existMessage = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message": "Error 0001:
                                 ID_OBJETO(',p_type_object_id,'), and
                                 ID_TIPO_MENSAJE(',p_type_message_id,') does not exist"}');
    SIGNAL SQLSTATE '45000'; #Dispara el custom error
END IF;
#Verifica si no existen por separado
IF @existObject = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message": "Error 0002:
                                 ID_OBJETO(',p_Id_objeto_afectado,') does not exist"}');
    SIGNAL SQLSTATE '45000'; #Dispara el custom error
END IF;
IF @existMensaje = 0 THEN
	SET v_customErrorMessage = CONCAT('{"response":false,"message": "Error 0003:
                                 ID_TIPO_MENSAJE(',p_Id_tipo_mensaje,') does not exist"}');
    SIGNAL SQLSTATE '45000'; #Dispara el custom error
END IF;

#Indicamos la fecha actual a una variable
SET @dateNow = (SELECT SYSDATE());

#Registramos el error y cuando se produjo
INSERT INTO `control_errors`(`type_message_id`, `type_object_id`, `affected_object`, `error_message`, `error_date`) 
VALUES (p_type_message_id,p_type_object_id,p_affected_object,p_error_message,@dateNow);

SET @errorMessage = JSON_UNQUOTE(JSON_EXTRACT(p_error_message,'$.message'));
#Si pasa todos los controles devuelve otro json
SET p_error_response = CONCAT('{"response":false,"message": "',@errorMessage,'"}');

END$$

CREATE DEFINER=`root`@`127.0.0.1` PROCEDURE `sp_update_clients` (IN `p_partner_user_id` INT, IN `p_rif` VARCHAR(30), IN `p_nit` INT, IN `p_bussiness_name` VARCHAR(60), IN `p_country_id` INT, IN `p_address` TEXT, IN `p_tax_phone` VARCHAR(30), IN `p_website` VARCHAR(150), IN `p_tax_email` VARCHAR(100), IN `p_sector_id` INT, IN `p_service_id` INT, IN `p_user_id` INT, IN `p_user_ip` VARCHAR(40), IN `p_client_id` INT, IN `p_status_id` INT, OUT `p_json_response` TEXT)   BEGIN
DECLARE v_customMessage TEXT;
DECLARE v_customError CONDITION FOR SQLSTATE '45000';
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_msj = MESSAGE_TEXT;
        ROLLBACK;
        SET v_customMessage = CONCAT("Se ha producido un error en la consulta: (",@code,") ",@error_msj);
        #Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_update_clients",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	#Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_update_clients",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
#Validacion del Usuario Socio
SET @isExist = (SELECT COUNT(us.user_id) FROM users us WHERE us.user_id = p_partner_user_id LIMIT 1);
IF @isExist = 0 THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0014: partner does not exist (',p_partner_user_id,')"}');
    #Activamos el error
    SIGNAL SQLSTATE '45000';
END IF;

#Validacion del Id Client
SET @isExistClient = (SELECT COUNT(cs.client_id) FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
IF @isExistClient = 0 THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0015: This client does not exist (',p_client_id,')"}');
    SIGNAL SQLSTATE '45000';
END IF;

#Capturamos el ultimo login del usuario quien esta actualizando al cliente
SET @lastInsert = (SELECT MAX(cl2.log_id) FROM control_logs cl2 WHERE cl2.user_responsible_id = p_user_id
                   AND cl2.log_description LIKE "updateClient%" LIMIT 1); #Almacena el ultimo insert de actualizacion de Cliente
SET @lastLogin = (SELECT us.login_date FROM users us WHERE us.user_id = p_user_id LIMIT 1); #Almacena la ultima fecha de login
SET @lastIp = (SELECT IFNULL(cl.user_responsible_ip,"0.0.0.0") FROM control_logs cl WHERE cl.log_id = @lastInsert); #Almacena la ultima IP

#Si existe el usuario socio, procedemos a crear al cliente
SET @dateNow = (SELECT SYSDATE());

#Acomodamos el ultimo valor y el nuevo
SET @lastPartner = (SELECT cs.partner_user_id FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastRif = (SELECT cs.rif FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastNit = (SELECT cs.nit FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastBussinessName = (SELECT cs.bussiness_name FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastCountry = (SELECT cs.country_id FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastAddress = (SELECT cs.client_address FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastPhone = (SELECT cs.tax_phone FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastWeb = (SELECT cs.website FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastEmail = (SELECT cs.tax_email FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastSector = (SELECT cs.sector_id FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastService = (SELECT cs.service_id FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);
SET @lastStatus = (SELECT cs.status_id FROM clients cs WHERE cs.client_id = p_client_id LIMIT 1);

SET @LastValue = CONCAT('{"socio":"',@lastPartner,'",
                        "rif":"',@lastRif,'",
                        "nit":"',@lastNit,'",
                        "razon":"',@lastBussinessName,'",
                        "pais":"',@lastCountry,'",
                        "direccion":"',@lastAddress,'",
                        "telefono":"',@lastPhone,'",
                        "pagina":"',@lastWeb,'",
                        "email":"',@lastEmail,'",
                        "sector":"',@lastSector,'",
                        "servicio":"',@lastService,'",
                        "status":"',@lastStatus,'",
                        "ultima_ip":"',@lastIp,'"}');
#Nuevos valores
SET @NewValue = CONCAT('{"socio":"',p_partner_user_id,'",
                        "rif":"',p_rif,'",
                        "nit":"',p_nit,'",
                        "razon":"',p_bussiness_name,'",
                        "pais":"',p_country_id,'",
                        "direccion":"',p_address,'",
                        "telefono":"',p_tax_phone,'",
                        "pagina":"',p_website,'",
                        "email":"',p_tax_email,'",
                        "sector":"',p_sector_id,'",
                        "servicio":"',p_service_id,'",
                        "status":"',p_status_id,'",
                        "ultima_ip":"',p_user_ip,'"}');

#Actualizamos el cliente
UPDATE `clients` SET `partner_user_id`= p_partner_user_id,`rif`=p_rif,`nit`=p_nit,`bussiness_name`=p_bussiness_name,`country_id`=p_country_id,`client_address`=p_address,`tax_phone`=p_tax_phone,`website`=p_website,`tax_email`=p_tax_email,`sector_id`=p_sector_id,`service_id`=p_service_id,`status_id`=p_status_id WHERE `client_id` = p_client_id;

SET @querySql = CONCAT('UPDATE `clients` SET `partner_user_id`= ',p_partner_user_id,',`rif`=p_rif,`nit`=p_nit,`bussiness_name`=p_bussiness_name,`country_id`=p_country_id,`client_address`=p_address,`tax_phone`=p_tax_phone,`website`=p_website,`tax_email`=p_tax_email,`sector_id`=p_sector_id,`service_id`=p_service_id,`status_id`=p_status_id WHERE `client_id` = ',p_client_id,';');

CALL sp_insert_logs(2,"updateClient",p_user_ip,p_user_id,"clients",@querySql,@lastValue,@newValue,@dateNow,@jsonResponse);
#Extraemos el JSON a una variable
SET @responseJson = JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.response'));

#Verificamos si registró efectivamente en la bitacora
IF @responseJson != "true" THEN
	SET v_CustomMessage = CONCAT('{"response":false,"message":"Error 0011: Insert log has failed (',JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.message')),')"}');
    SIGNAL SQLSTATE '45000'; #Si no ha podido registrar nada, dispara el error
END IF;

#Si paso toda las validaciones
SET p_json_response = CONCAT('{"response":true,"message":"Client ',p_bussiness_name,' updated."}');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_contact_users` (IN `p_user_code` VARCHAR(6), IN `p_primary_email` VARCHAR(255), IN `p_secondary_email` VARCHAR(255), IN `p_primary_phone` VARCHAR(30), IN `p_secondary_phone` VARCHAR(30), IN `p_identity_type` VARCHAR(5), IN `p_identity_number` VARCHAR(255), OUT `p_json_response` TEXT)   BEGIN
DECLARE v_customMessage TEXT;
DECLARE v_customError CONDITION FOR SQLSTATE '45000';
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_msj = MESSAGE_TEXT;
        ROLLBACK;
        SET v_customMessage = CONCAT("Se ha producido un error en la consulta: (",@code,") ",@error_msj);
        #Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_update_contact_users",v_CustomMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	#Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_UpdateContactUser",v_CustomMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
#Validacion del Usuario
SET @isExist = (SELECT COUNT(us.user_id) FROM users us WHERE us.user_code = p_user_code LIMIT 1);
IF @isExist = 0 THEN
	SET v_CustomMessage = CONCAT('{"response":false,"message":"Error 0018: This users does not exist (',p_user_code,')"}');
    #Activamos el error
    SIGNAL SQLSTATE '45000';
END IF;

#Si existe procedemos a actualizar el contacto
SET @idUser = (SELECT us.user_id FROM users us WHERE us.user_code = p_user_code LIMIT 1);
SET @identityType = (SELECT ut.identity_type_id FROM users_identity_type ut WHERE ut.identity_abbreviation = p_identity_type LIMIT 1);
#Validacion de la cedula
SET @documentExist = (SELECT COUNT(ui.identity_id) FROM users_identity ui WHERE ui.identity_number = p_identity_number AND ui.identity_type_id = @identityType AND ui.user_id != @idUser LIMIT 1);
IF @documentExist != 0 THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0017: This identity is already registered (',p_identity_number,')"}');
	SIGNAL SQLSTATE '45000';
END IF;
#Contacto
UPDATE `users_contact` SET `primary_email`=p_primary_email,`secondary_email`=p_secondary_email,`primary_phone`=p_primary_phone,`secondary_phone`=p_secondary_phone WHERE `user_id` = @idUser;

#Documento
UPDATE `users_identity` SET `identity_type_id`=@identityType,`identity_number`=p_identity_number WHERE  `user_id` = @idUser;

#Devolvemos el response si no hay ningun SQL Error
SET p_json_response = CONCAT('{"response":true,"message":"Contact user update succesfully for user ',p_user_Code,'"}');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_users` (IN `p_first_name` VARCHAR(20), IN `p_second_name` VARCHAR(20), IN `p_first_surname` VARCHAR(20), IN `p_second_surname` VARCHAR(20), IN `p_user_code` VARCHAR(6), IN `p_birthday` DATE, IN `p_admission_date` DATE, IN `p_identity_number` VARCHAR(255), IN `p_parish_id` INT, IN `p_position_id` INT, IN `p_department_id` INT, IN `p_user_id` INT, IN `p_user_ip` VARCHAR(39), IN `p_user_update_id` INT, IN `p_status_id` INT, IN `p_departure_date` DATE, OUT `p_json_response` TEXT)   BEGIN
DECLARE v_customMessage TEXT;
DECLARE v_customError CONDITION FOR SQLSTATE '45000';
DECLARE EXIT HANDLER FOR SQLEXCEPTION, SQLWARNING
	BEGIN
    	GET DIAGNOSTICS CONDITION 1 @code = RETURNED_SQLSTATE, @error_msj = MESSAGE_TEXT;
        ROLLBACK;
        SET v_customMessage = CONCAT("Se ha producido un error en la consulta: (",@code,") ",@error_msj);
        #Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_update_users",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
DECLARE EXIT HANDLER FOR v_customError
	BEGIN
    	#Guardamos el error
        CALL sp_sql_exceptions(1,1,"sp_update_users",v_customMessage,@responseError);
        SET p_json_response = (SELECT @responseError);
    END;
#Validacion del Usuario
SET @isExist = (SELECT COUNT(us.user_id) FROM users us WHERE us.user_id = p_user_id LIMIT 1);
IF @isExist != 1 THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0018: This users does not exist (',p_user_id,')"}');
    #Activamos el error
    SIGNAL SQLSTATE '45000';
END IF;

#Capturamos el ultimo login del usuario quien esta registrando el nuevo miembro
SET @lastInsert = (SELECT MAX(cl2.log_id) FROM control_logs cl2 WHERE cl2.user_responsible_id = p_user_id 
                   AND cl2.log_description LIKE "updateUser%" LIMIT 1); #Almacena el ultimo insert de creacion de Usuario
SET @lastLogin = (SELECT us.login_date FROM users us WHERE us.user_id = p_user_id LIMIT 1); #Almacena la ultima fecha de login
SET @lastIp = (SELECT IFNULL(cl.user_responsible_ip,"0.0.0.0") FROM control_logs cl WHERE cl.log_id = @lastInsert); #Almacena la ultima IP

#Si existe el usuario quien esta actualizando procedemos a actualizar al usuario
SET @dateNow = (SELECT SYSDATE());
SET @dateChange = (SELECT DATE(SYSDATE()+90));

#Acomodamos el ultimo valor
SET @lastNameAll = (SELECT CONCAT(us.first_name," ",us.second_name,", ",us.first_surname," ",us.second_surname) from users us WHERE us.user_id = p_user_update_id);
SET @lastBirthday = (SELECT us.birthday from users us WHERE us.user_id = p_user_update_id);
SET @lastPosition= (SELECT us.position_id from users us WHERE us.user_id = p_user_update_id);
SET @lastDepartment= (SELECT us.department_id from users us WHERE us.user_id = p_user_update_id);
SET @lastParish= (SELECT us.parish_id from users us WHERE us.user_id = p_user_update_id);
SET @lastAdmission= (SELECT us.admission_date from users us WHERE us.user_id = p_user_update_id);
SET @lastDeparture= (SELECT us.departure_date from users us WHERE us.user_id = p_user_update_id);
SET @lastStatus= (SELECT us.status_id from users us WHERE us.user_id = p_user_update_id);

SET @lastValue = CONCAT('{"name":"',@lastNameAll,'",
                        "birthday":"',@lastBirthday,'",
                        "cargo":',@lastPosition,',
                        "division":',@lastDepartment,',
                        "parroquia":',@lastParish,',
                        "fecha_ingreso":"',@lastAdmission,'",
                        "fecha_egreso":"',@lastDeparture,'",
                        "status":',@lastStatus,',
                        "ultima_ip":"',@lastIp,'"}');

UPDATE `users` SET `user_code`=p_user_code,`time_change_password`=@dateChange,`first_name`=p_first_name,`second_name`=p_second_name,`first_surname`=p_first_surname,`second_surname`=p_second_surname,`birthday`=p_birthday,`position_id`=p_position_id,`department_id`=p_department_id,`parish_id`=p_parish_id,`admission_date`=p_admission_date,`departure_date`=p_departure_date,`status_id`=p_status_id WHERE `user_id` = p_user_update_id;

#Guardamos el SQL
SET @querySql = CONCAT('UPDATE `users` SET `user_code`=',p_user_code,',`time_change_password`=@dateChange,`first_name`=p_first_name,`second_name`=p_second_name,`first_surname`=p_first_surname,`second_surname`=p_second_surname,`birthday`=p_birthday,`position_id`=p_position_id,`department_id`=p_department_id,`parish_id`=p_parish_id,`admission_date`=p_admission_date,`departure_date`=p_departure_date,`status_id`=p_status_id WHERE `user_id` = p_user_update_id;');

#Acomodamos el nuevo valor
SET @newValue = CONCAT('{"name":"',CONCAT(p_first_name," ",p_second_name,", ",p_first_surname," ",p_second_surname),'",
                        "birthday":"',p_birthday,'",
                        "cargo":',p_position_id,',
                        "division":',p_department_id,',
                        "parroquia":',p_parish_id,',
                        "fecha_ingreso":"',p_admission_date,'",
                        "fecha_egreso":"',p_departure_date,'",
                        "status":',p_status_id,',
                        "ultima_ip":"',p_user_ip,'"}');
CALL sp_insert_logs(2,"updateUser",p_user_ip,p_user_id,"users",@querySql,@lastValue,@newValue,@dateNow,@jsonResponse);
#Extraemos el JSON a una variable
SET @responseJson = JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.response'));

#Verificamos si registró efectivamente en la bitacora
IF @responseJson != "true" THEN
	SET v_customMessage = CONCAT('{"response":false,"message":"Error 0011: Insert log has failed (',JSON_UNQUOTE(JSON_EXTRACT(@jsonResponse,'$.message')),')"}');
    SIGNAL SQLSTATE '45000'; #Si no ha podido registrar nada, dispara el error
END IF;

#Si paso toda las validaciones
SET p_json_response = CONCAT('{"response":true,"message":"User ',p_user_code,' update succesfully"}');
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `partner_user_id` int(11) NOT NULL,
  `client_code` int(11) NOT NULL,
  `rif` varchar(15) NOT NULL,
  `nit` int(11) NOT NULL,
  `bussiness_name` varchar(500) NOT NULL,
  `country_id` int(11) NOT NULL,
  `client_address` text NOT NULL,
  `tax_phone` varchar(20) NOT NULL,
  `website` varchar(250) NOT NULL,
  `tax_email` varchar(100) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clients`
--

INSERT INTO `clients` (`client_id`, `partner_user_id`, `client_code`, `rif`, `nit`, `bussiness_name`, `country_id`, `client_address`, `tax_phone`, `website`, `tax_email`, `sector_id`, `service_id`, `status_id`) VALUES
(14, 146, 1451, 'J314087754', 0, 'A.C. CONSULTORES UCAB', 240, 'Universidad Católica Andrés Bello Centro Loyola, Piso 2, Caracas. Dtto. Capital', '+ 5822122356047', '', 'acconsultores@com.ve', 0, 0, 1),
(15, 150, 1534, 'J000998345', 0, 'ADMINISTRADORA CCCT', 240, 'Av.la Estancia con calle Ernesto Blochn C.C.C.T nivel PB OF Administracion Urb. Chuao', '+ 582129593604', '', 'administradoraccct@gmail.com', 0, 0, 1),
(16, 150, 1020, 'J0002474327', 0, 'ADMINISTRADORA CENTRO FINANCIERO LATINO, C.A.', 240, 'Av. Urdaneta, Esq de animas Plaza España, Edif Centro financieros latino,piso 8, ofic A-C Caracas', '+ 582125618212', '', 'centro@gmail.com.ve', 0, 0, 1),
(17, 149, 1164, 'J002639632', 0, 'ADMINISTRADORA HOTAL', 240, 'av. whashintong hotel avila san  bernardino caracas', '+ 582125522243', '', 'administradorahotal@gmail.com', 0, 0, 1),
(18, 149, 2188, 'J308240486', 0, 'AEROCLOSET, C.A.', 240, 'Av. la estancia ccct torre a nivel p2 urb chuao', '+ 58', '', 'aerocloset@gmail.com', 0, 0, 1),
(19, 149, 2131, 'J075433548', 0, 'AGENCIA GENERALES CONAVEN, C.A.', 240, 'Av. Orinoco Edif. Torre Uno Piso 4 Urb. Las Mercedes Caracas, Edo. Miranda Zona Postal 1060', '+ 58', '', 'conaven@gmail.com', 0, 0, 1),
(20, 147, 2184, 'J312492414', 0, 'AGROBIGOTT,C.A.', 240, 'Urb. Los Ruices  AV. Fco de Miranda EDIF Bigott  Caracas, (PETARE)  MIRANDA', '+ 58', '', 'agrobigott@gmail.com', 0, 0, 1),
(21, 144, 1868, 'J000599009', 0, 'ANAYANSI, C.A.', 240, 'Calle Final calle 4 Local Galpon N°03 Urb. Terrinca Guatire Edo. Miranda', '+ 58', '', 'anayansi@gmail.com', 0, 0, 1),
(22, 150, 1906, 'J306991719', 0, 'ARROBA SEGUROS SOCIEDAD DE CORRETAJE DE SEGUROS,C.A.', 240, 'Av. Don Diego Cisnero Edif. Siemens Torre norte, Piso 4,Ofc Arroba Urb. Los Ruices, Caracas- Miranda', '+ 58', '', 'aarrobaseguros@gmail.com', 0, 0, 1),
(23, 146, 1618, 'J001256970', 0, 'AVICOLA DEL CENTRO C.A.', 240, 'Carretera Cua-San Casimiro Local  Nro. 49-B Sector La Cienega San Casimiro Estado Aragua-Zona Postal 2338', '+ 58', '', 'avicola@gmail.com', 0, 0, 1),
(24, 146, 1617, 'J306515933', 0, 'AVICOLA SANTA CRUZ, C.A.', 240, 'Calle Hernandez Nadal Local Nro 03 Sector  Frente a Urbanización el Remanso Vía Turagua Santa Cruz -Aragua Zona Postal 2123', '+ 58', '', 'avicolasantacruz@gmail.com', 0, 0, 1),
(25, 150, 1064, 'J001708472', 0, 'AVILA RAYOS X, C.A.', 240, 'Av.San Juan Bosco con  Sexta Transversal, Edif. Clinica el Avila, Piso 2 Altamira, Caracas.', '+ 582122761030', '', 'avilarayosx@gmail.com', 0, 0, 1),
(26, 150, 1065, 'J000121940', 0, 'AVILA SERVICIOS MEDICOS, C.A.', 240, 'Sexta Transversal de Altamira con Av.San Juan Bosco, Edif. Clinica Avila Altamira, Caracas.', '+ 582122081026', '', 'aviserme@gmail.com', 0, 0, 1),
(27, 149, 1409, 'J080066227', 0, 'BANCO ACTIVO BANCO UNIVERSAL', 240, 'Torre Europa', '+ 58', '', 'correo@dominio.com', 0, 0, 2),
(28, 146, 1738, 'G200057955', 0, 'BANCO AGRICOLA DE VENEZUELA', 240, 'Avenida Francisco de Miranda  Edificio Cavendes Piso 17  Chacao-Caracas', '+ 58', '', 'bancoagricola@gmail.com', 0, 0, 1),
(29, 145, 1266, 'J304742029', 0, 'BANCO DE COMERCIO EXTERIOR, C.A.', 240, 'Calle los Chaguaramos Centro Gerencial Mohedano Piso 1 La Castellana-Caracas', '+ 582122651433', '', 'bancoex@gmail.com', 0, 0, 1),
(30, 146, 1448, 'J313413615', 0, 'BANCO DE EXPORTACION Y COMERCIO', 240, 'Av. Casanova con Av. Las Acacias Torre Banhorient, Piso 11. Oficina 11A. Plaza Venezuela Caracas, Dtto Capital', '+ 582127935249', '', 'bancodeexportacion@gmail.com', 0, 0, 1),
(31, 146, 1654, 'G200099976', 0, 'BANCO DE VENEZUELA, BANCO UNIVERSAL', 240, 'Avenida Universidad Esquina de Sociedad  Torre Banco de Venezuela, Parroquia Catedral  Caracas, Dtto Capital', '+ 58', '', 'bancodevenezuela@gmail.com', 0, 0, 1),
(32, 146, 1359, 'G200051876', 0, 'BANCO DEL TESORO, C.A.', 240, 'Calle Guaicaipuro, Torre Banco del Tesoro Urbanización El Rosal Caracas, Dtto Capital', '+ 58', '', 'bancodeltesoro@gmail.com', 0, 0, 1),
(33, 145, 1375, 'J002853018', 0, 'BANESCO HOLDING, C.A.', 240, 'centro ciudad banesco', '+ 58', '', 'correo@dominio.com', 0, 0, 2),
(34, 149, 2138, 'G200096772', 0, 'BOLSA PUBLICA DE VALORES BICENTENARIA', 240, 'Av.  Francisco de Miranda Edif. Torre Europa Piso Nivel C Ofic S/N  Urb. El Rosal Caracas Chacao Zona Postal 1060', '+ 58', '', 'bolsapublica@gmail.com', 0, 0, 1),
(35, 147, 2182, 'J000067481', 0, 'C.A. CIGARRERA BIGOTT SUCS', 240, 'Urb. Los Ruices  AV. Fco de Miranda EDIF Bigott  Caracas, (PETARE)  MIRANDA', '+ 58', '', 'cigarrerabigott@gmail.com', 0, 0, 1),
(36, 149, 1874, 'J000048398', 0, 'CAJA DE AHORRO BANVENEZ', 240, 'Esquina Veroes a Santa Capilla Av. Urdaneta Edif Veroes Piso 4 Ofic Caja de Ahorro Caracas', '+ 58', '', 'banvenez@gmail.com', 0, 0, 1),
(37, 144, 1962, 'J294280986', 0, 'CALIFORNIA HOME FASHION, C.A.', 240, 'Av. Andres Bello calle 4 con calle 5 Qta. Vera Urb. Los Palos Grandes  Urb. Los Palos Grandes Caracas (Chacao) Miranda ZP 1060', '+ 58', '', 'californiahomefashion@gmail.com', 0, 0, 1),
(38, 150, 1158, 'J001168281', 0, 'CENTRO MEDICO LOIRA, C.A.', 240, 'Av. Loira, Edif. Centro Medico Loira, Piso 1  Oficina de Presidencia  El Paraiso, Caracas, Dtto Capital', '+ 582124052095', '', 'centromedicoloira@gmail.com', 0, 0, 1),
(39, 149, 2187, 'J001147853', 0, 'CERAMICAS CARIBE, C.A.', 240, 'Chivacoa  Ctra.Autopista Centro Occidental,Dtb Chivacoa Via Nirgua Local Nro S/N Sector Sabana Larga-Yaracuy', '+ 582127000224', '', 'ceramicascaribe@gmail.com', 0, 0, 1),
(40, 146, 1019, 'J002726598', 0, 'CONDOMINIO CENTRO FINANCIERO LATINO', 240, 'Avenida Urdaneta, Esquina de Animas a Plaza España, Edificio Centro Financiero Latino, Piso 8, Oficina A-C Caracas. Dtto. Capital', '+ 582125618212', '', 'condomcentrofianlatino@gmail.com', 0, 0, 1),
(41, 147, 2109, 'J095003264', 0, 'CONPROIND', 240, 'Av. Rio Caura con Rio Paragua. Ubr.Prados del Este-La Piramide Piso 03 Ofc 307 Caracas.', '+ 582129776309', '', 'conproind@gmail.com', 0, 0, 1),
(42, 147, 2154, 'J003636916', 0, 'CONSTRUCTORA NORBERTO ODEBRECHT, S.A', 240, 'Urb. Prados del Este Av. Rio Caura Edif. Torre Humboldt Torre Humboldt Piso 10, Of.10-13', '+ 58', '', 'odebrecth@gmail.com', 0, 0, 1),
(43, 149, 1978, 'J000963142', 0, 'CONTINENTAL DE SISTEMAS Y MAQUINAS. C.A., CONTIMACA', 240, 'Av. Milan Edificio CONTIMACA Piso 1 Urb. Los Ruices Sur.', '+ 58', '', 'contimaca@gmail.com', 0, 0, 1),
(44, 150, 1689, 'J001340769', 0, 'CORAL', 240, 'Calle La Limonera, Edificio LARCO Piso 1, Urbanización La Trinidad', '+ 582129442777', '', 'coral@gmail.com', 0, 0, 1),
(45, 147, 2166, 'J003082902', 0, 'CORPORACION DELCOP, C.A.', 240, 'Calle Las Vegas Edificio DELCOP  Piso 1 Local DELCOP Zona Industrial de la Trinidad Caracas-Estado Miranda Zona Postal 1080', '+ 58', '', 'corpodelcop@gmail.com', 0, 0, 1),
(46, 147, 2189, 'J409590577', 0, 'DISTRIBUIDORA AMAZONIA 1134, C.A.', 240, 'Calle Santa Ana Edificio Centro Empresarial Boleita Piso 5 Oficina 5-B Urbanización Boleita', '+ 58', '', 'amazonia@gmail.com', 0, 0, 1),
(47, 147, 2183, 'J302385490', 0, 'DISTRIBUIDORA BIGOTT C.A.', 240, 'Urb. Los Ruices  AV. Fco de Miranda EDIF Bigott Caracas, (PETARE) MIRANDA', '+ 58', '', 'distribigott@gmal.com', 0, 0, 1),
(48, 150, 2151, 'J409554635', 0, 'DISTRIBUIDORA MACONDO 333, C.A.', 240, 'Calle Santa Ana Edif Centro Empresarial Boleita, Piso 5 Ofic 5-B Ubr. Boleita Sur Caracas-Miranda', '+ 58', '', 'macondo@gmail.com', 0, 0, 1),
(49, 150, 2178, 'J407514571', 0, 'DP DELTA SERVICIOS, C.A.', 240, 'Av. Los Samanes Final Norte  Edificio Clinico La Florida PB  Urb. La Florida-Caracas', '+ 58', '', 'estudiomedicotomograf@gmail.com', 0, 0, 1),
(50, 146, 1983, 'J300388840', 0, 'ESTUDIO MEDICO TOMOGRAF, C.A.', 240, '', '+ 58', '', '', 0, 0, 1),
(51, 150, 2171, 'J302455413', 0, 'FARMACIA MEDITOTAL, C.A.', 240, 'Av. Costanera Centro Empresarial Athenas,PB Barcelona. Edo. Anzoategui', '+ 58', '', 'famaciameditotal@gmail.com', 0, 0, 1),
(52, 146, 1018, 'J002901322', 0, 'FINANCORP VALORES CASA DE BOLSA, C.A.', 240, '', '+ 582120000000', '', 'correo@dominio.com', 0, 0, 1),
(53, 150, 2097, 'J000143676', 0, 'FSVEN INDUSTRIAL SOLUTIONS, S.A', 240, 'Av. Rio Caura con Rio Paragua. Ubr.Prados del Este-La Piramide Piso 03 Ofc 301 Caracas.', '+ 58', '', 'fsvenindustrial@gmail.com', 0, 0, 1),
(54, 146, 2181, 'J315820439', 0, 'FUNDACIÓN ESPAÑA SALUD', 240, 'Av. El Parque con Esquina Av. Andres Bello Edificio Oficentro Piso 4 Oficina 4-A y 4-B Urb. San Bernandino-Caracas', '+ 582125785802', '', 'fundacionespaña@gmail.com', 0, 0, 1),
(55, 150, 2159, 'J000762627', 0, 'GENIA CARE PHARMACEUTICAL, S.A', 240, 'Calle Callejon Gutierrez Edid EUROCIENCIA piso 1local EDIFICIO URB La California Norte Caracas Miranda  Zona Postal 1070', '+ 58', '', 'geniacarepharma@gmail.com', 0, 0, 1),
(56, 150, 2160, 'J000681716', 0, 'GENIA CARE, C.A.', 240, 'Av. Francisco de Miranda Esq. Con Avenida El Parque Edif Torre Country Piso 4 y 5 Of. S/N URB El Bosque Caracas  (CHACAO) MIRANDA ZONA POSTAL 1060', '+ 58', '', 'geniacare@gmail.com', 0, 0, 1),
(57, 150, 1648, 'J309264613', 0, 'GLOBAL LEASING, C.A.', 240, 'Av. Diego Cisneros, Calle Los Laboratorios Edificio Otinca, Piso 5 Oficina 47 Los Ruices-Caracas', '+ 58', '', 'globalleasing@gmail.com', 0, 0, 1),
(58, 146, 2186, 'J401443001', 0, 'GPB NEFTEGAZ SERVICES B.V. SUCURSAL, C.A.', 240, '', '+ 58', '', '', 0, 0, 1),
(59, 146, 1614, 'J075168186', 0, 'GRANJA ALCONCA, C.A.', 240, 'Avenida 3 Parcela A5-1 Urbanización Industrial Santa Cruz Estado Aragua.', '+ 02433000143', '', 'granjaalconca@gmail.com', 0, 0, 1),
(60, 146, 1615, 'J303834817', 0, 'GRANJA MUCURITA, C.A.', 240, 'Carretera Asentamiento Campesino Mucura I Local Nro 34 Sector Mucura Villa de Cura Estado Aragua-Zona Postal 2126', '+ 58', '', 'geanjamucurita@gmail.com', 0, 0, 1),
(61, 146, 1616, 'J075791215', 0, 'GRUPO AVICOLA INTEGRADO DEL CENTRO,C.A.', 240, 'Calle Hernandez Nadal Local Nro 06 Sector Frente a Urbanización el Remanso Vía Turagua Santa Cruz -Aragua Zona Postal 2123', '+ 58', '', 'grupoavicola@gmail.com', 0, 0, 1),
(62, 150, 1283, 'J000683778', 0, 'GRUPO MEDICO VARGAS, C.A.', 240, 'Av. Ppal, Urb. Santa Sofia, El Cafetal. Quinta los Abuelos Caracas- Dtto. Capital', '+ 582129811369', '', 'grupomedicovargas@gmail.com', 0, 0, 1),
(63, 146, 1675, 'J296479461', 0, 'GRUPO TUTICKET.COM,VE, C.A.', 240, 'Av.Sur, Centro Empresarial Lagunita, Piso 2,  Ofinina 208, Urb.La Lagunita', '+ 582123195353', '', 'grupotuticket@gmail.com', 0, 0, 1),
(64, 147, 1920, 'J295320795', 0, 'IMAN GROUP, C.A.', 240, 'Av. Don Diego Cisneros Edif. Siemens Torre Norte Piso4 Ofic.Iman Group Urb. Los Ruices , Edo. Miranda Zona Postal 1071', '+ 58', '', 'imangroup@gmail.com', 0, 0, 1),
(65, 150, 1996, 'J301183185', 0, 'IMPORTADORA ACIPROSALUD, C.A.', 240, 'Calle Alameda Av. Venezuela Edif. Folgana Piso 1 Apto 1-A  El Rosal Chacao-Caracas', '+ 58', '', 'aciprosalud@gmail.com', 0, 0, 1),
(66, 146, 1496, 'J312605316', 0, 'INDUSTRIA CONSOLIDADAS DE GASES ICG C,A', 240, 'Urbanización Industrial Terrinca Av. Principal, Galpon Nº 5 Guatire, Estado Miranda', '+ 582129511921', '', 'icg@gmail.com', 0, 0, 1),
(67, 146, 1179, 'J300706206', 0, 'INDUSTRIAS CORPAÑAL,C.A.', 240, 'Urb. Industrial Guayabal, Parcelas Nº 12 y 13, Edificio Corpañal', '+ 582123612812', '', 'induscorpañal@gmail.com', 0, 0, 1),
(68, 147, 2091, 'J296507570', 0, 'INDUSTRIAS DE CARTONAJE CRD, C.A.', 240, 'Santa Teresa del Tuy Av. San Antonio. Sector Tumuso, Galpón C-2A Caracas  - Miranda', '+ 582129490900', '', 'induscartonaje@gmail.com', 0, 0, 1),
(69, 150, 1948, 'J000003890', 0, 'INSTITUTO CLINICO LA FLORIDA', 240, 'Av. Los Samanes Final Norte  Edificio Clinico La Florida PB  Urb. La Florida-Caracas', '+ 58', '', 'insticlinicolafloria@gmail.com', 0, 0, 1),
(70, 150, 1127, 'J307107030', 0, 'INVERSIONES SERCAVILA 51, C.A.', 240, 'Av.San Juan Bosco con Sexta Transversal, Edif. Clinica el Avila, Piso 2 Altamira, Caracas', '+ 58', '', 'sercavila@gmail.com', 0, 0, 1),
(71, 149, 1554, 'J313305278', 0, 'INVERSIONES VISTALPARQUE, C.A.', 240, '1era. Av, Urbanización Santa Eduvigis 1070. Caracas.', '+ 582122081900', '', 'invervistalparque@gmail.com', 0, 0, 1),
(72, 150, 1066, 'J001798170', 0, 'LABORATORIO AVILAB, C.A.', 240, 'Av.San Juan Bosco con  Sexta Transversal, Edif. Clinica el Avila, Piso 2 Altamira, Caracas.', '+ 582122640116', '', 'labavilab@gmail.com', 0, 0, 1),
(73, 150, 1982, 'J003240001', 0, 'LABORATORIO INSTITUTO CLINICO LA FLORIDA, C.A.', 240, 'Av. Los Samanes Final Norte Edificio Clinico La Florida PB Urb. La Florida-Caracas', '+ 58', '', 'labinstitutoclinicolaflorida@gmail.com', 0, 0, 1),
(74, 147, 1873, 'J312694491', 0, 'LACTEOS ANANKE, C.A.', 240, 'Calle Bolivar Edificio Quenetyl Local 8 PB S/N Urbanización La Trinidad Caracas-Miranda', '+ 58', '', 'lacteosanake@gmail.com', 0, 0, 1),
(75, 144, 1960, 'J313169986', 0, 'LENCERIA EL RECREO, C.A.', 240, 'Av. Venezuela con Av. Bracamonte y Av. Crispulo Benites C.C. Sambil nivel P/B local L-169 Sector Este Barquisimeto Edo. Lara ZP 3001', '+ 58', '', 'lenceriaelrecreo@gmail.com', 0, 0, 1),
(76, 144, 1954, 'J400129346', 0, 'LENCERIA HOGAR TORBES, C.A.', 240, 'Av. Antonio José de Sucre CC Centro Sambil nivel feria Forum Shops  Local FN-3 Sector Las Lomas  San CristobalEdo. Tachira ZP 5001', '+ 58', '', 'lenceriahogartorbes@gmail.com', 0, 0, 1),
(77, 144, 1956, 'J299204846', 0, 'LENCERIA VIÑA VALENCIA, C.A.', 240, 'Av. Carabobo con calle Uslar Qta. Chispita Nro. 141-121 Urb. La Viña  Valencia Edo. Carabobo ZP 2001', '+ 58', '', 'viñavalecnia@gmail.com', 0, 0, 1),
(78, 150, 1904, 'J312911972', 0, 'MAKLER ADMINISTRADORA DE RIESGOS, S.A.', 240, 'Av. Don Diego Cisnero Edif. Siemens Torre norte, Piso 4,Ofc Makler  Urb. Los Ruices, Caracas- Miranda', '+ 58', '', 'makleradmideriesgos@gmail.com', 0, 0, 1),
(79, 150, 1905, 'J302911478', 0, 'MAKLER SOCIEDAD DE CORRETAJE DE SEGUROS, C.A.', 240, 'Av. Don Diego Cisnero Edif. Siemens Torre norte, Piso 4,Ofc Makler  Urb. Los Ruices, Caracas- Miranda', '+ 58', '', 'maklersociedad@gmail.com', 0, 0, 1),
(80, 147, 1881, 'J300254925', 0, 'MAXIMIZA CASA DE BOLSA, C.A.', 240, 'Av. Francisco de Miranda Torre Europa Piso 3 Oficina 3B3  El Rosal Caracas', '+ 58', '', 'maximizacasadebolsa@gmail.com', 0, 0, 1),
(82, 146, 1357, 'G200035919', 0, 'MERCADOS DE ALIMENTOS , C.A.(MERCAL)', 240, 'Av. Fuerzas Armadas Edif CVAL Piso 3 Sector La Candelaria Caracas-Dtto Capital  Zona Postal 1010', '+ 582125051177', '', 'mercal@gmail.com', 0, 0, 1),
(84, 144, 1530, 'J001097449', 0, 'OPTICA CARONI, C.A.', 240, 'Urb. Boleita Norte, Calle Santa Clara Edif. Bertolini Piso nº 02 Caracas-Dtto.Capital', '+ 582122384233', '', 'opticacaroni@gmail.com', 0, 0, 1),
(85, 146, 1505, 'J000950369', 0, 'PETROLEOS DE VENEZUELA, S.A.', 240, 'Av Bolivar con calle el Empalme. Torre Este, PH Urb. La Campiña. PDVSA.', '+ 582127083281', '', 'petroleosdevenezuela@gmail.com', 0, 0, 1),
(86, 150, 1604, 'J000297908', 0, 'POLICLINICA LAS MERCEDES, C.A.', 240, 'Avenida Principal  Las Mercedes-Caracas', '+ 582129932911', '', 'policlinicalasmercedes@gmail.com', 0, 0, 1),
(87, 147, 1817, 'J302202531', 0, 'PROSEGUROS, S.A.', 240, 'Avenida Francisco de Miranda Con 4ta. Avenida Edificio Torre PROSEGUROS Piso 6 Oficina 6-A,6-B,6-C,6-D 6-E y 6-F, UrbanizaciónLos Palos Grandes Caracas(Chacao) Miranda Zona Postal 1060', '+ 58', '', 'proseguros@gmail.com', 0, 0, 1),
(88, 150, 1984, 'J003390062', 0, 'RADIODIAGNOSTICO LA FLORIDA, C.A.', 240, '', '+ 58', '', '', 0, 0, 1),
(89, 150, 1690, 'J000908320', 0, 'REFRIMET INDUSTRIAL,C.A.', 240, 'Calle La Limonera, Edificio LARCO Piso 1, Urbanización La Trinidad', '+ 582129442777', '', 'refrimet@gmail.com', 0, 0, 1),
(90, 149, 2114, 'J003285536', 0, 'REPRESENTACIONES LABIN VE, S.A.', 240, 'Calle Los Laboratorios  Edificio Torre Beta Piso 1 Oficina 105 Los Ruices Estado Miranda', '+ 58', '', 'representacioneslabinve@gmail.com', 0, 0, 1),
(91, 149, 1285, 'J306649590', 0, 'SERVICIOS GZ', 240, 'Edif. Ofinca, PISO 2 Oficina 24, Los Cortijos de Lourdes, Av. Los Laboratorios.', '+ 582122325626', '', 'serviciosgz@gmail.com', 0, 0, 1),
(92, 149, 1938, 'J403483302', 0, 'SETA NAVIERA, C.A.', 240, 'Esquina Puente Yanez Edificio Beco Piso 8 La Candelaria Distrito Capital', '+ 58', '', 'setanaviera@gmail.com', 0, 0, 1),
(93, 145, 1095, 'G200041315', 0, 'SOCIEDAD NACIONAL DE GARANTIAS RECIPROCAS PARA LA MEDIANA Y PEQUEÑA INDUSTRIA, S.A. (SOGAMPI)', 240, 'Av.Principal de los Cortijos de Lourdes Centro Los Cortijos, PH-Caracas.', '+ 582122385323', '', 'sogampi@gmail.com', 0, 0, 1),
(94, 146, 2083, 'J300766454', 0, 'STATERA CASA DE BOLSA', 240, 'AV.LAS MERCEDES ENTRE GUAICAIPURO Y CARABOBO TORRE FORUM PISO 15 OFIC 15-B  EL ROSAL-EDO. MIRANDA', '+ 58', '', 'stateracasadebolsa@gmail.com', 0, 0, 1),
(95, 150, 1613, 'J314853813', 0, 'SUPER MAX, C.A.', 240, 'Calle Santa Ana Edificio Centro Empresarial Boleita piso 5 Oficina 5_B Urb. Boleita Sur Caracas Edo. Miranda', '+ 2560765', '', 'supermax@gmail.com', 0, 0, 1),
(96, 149, 1439, 'J000359148', 0, 'TAUREL & CÍA SUCRS', 240, 'Final Av.Milan, Edif. Taurel  Los Ruices Sur  Estado Miranda', '+ 582122050128', '', 'taurelsucrs@gmail.com', 0, 0, 1),
(97, 146, 1605, 'J000122555', 0, 'UNIVERSIDAD CATOLICA ANDRES BELLO', 240, 'AV. Intercomunal de Antimano Caracas.', '+ 58', '', 'universidadcatolica@gmail.com', 0, 0, 1),
(98, 146, 1936, 'J312309385', 0, 'VALORALTA CASA DE BOLSA', 240, '', '+ 582120000000', '', 'corre@dominio.com', 0, 0, 1),
(99, 150, 1067, 'J000381976', 0, 'VEN AMERICAN, C.A.', 240, 'Av. Sur 4, Edif. Ven American  Quinta Crespo, Caracas.', '+ 582124810921', '', 'venamerica@gmail.com', 0, 0, 1),
(100, 144, 1958, 'J315056429', 0, 'VENEZUELAN HOME FASHION HQ C.A.', 240, 'Calle principal de Caricuao Edif. Telares de Palo Grande  piso PB Of. 1 Urb. Caricuao Caracas distrito Capital ZP 1100', '+ 58', '', 'venezuelanhome@gmail.com', 0, 0, 1),
(101, 144, 1454, 'G200109963', 0, 'C. A. VENEZOLANA DE INDUSTRIAS MILITARES, CAVIM', 240, 'Urb. Las Mercedes, Calle Jalisco, Edif. CAVIM. Caracas, Las Mercedes  Distrito Capital.', '+ 58', '', 'cavim@gmail.com', 0, 0, 1),
(102, 149, 2190, 'J301288670', 0, 'AGB PANAMERICANA DE VENEZUELA MEDICIÓN, S.A.', 240, '', '+ 582129184100', 'valerie.prado@nielsen.com', 'valerie.prado@nielsen.com', 0, 0, 1),
(103, 146, 2191, 'J000122555', 0, 'UNIVERSIDAD CATÓLICA ANDRÉS BELLO (UCAB)', 240, '', '+ 582124074490', 'www.ucab.edu.ve', 'jmayo@ucab.edu.ve', 0, 0, 1),
(104, 58, 2192, 'J000067481', 0, 'C.A. CIGARRERA BIGOTT, SUCS', 240, '', '+ 582122037769', 'abigail_mendoza@bat.com', 'stefania_tassinari@bat.com', 0, 0, 1),
(105, 58, 2193, 'J302385490', 0, 'DISTRIBUIDORA BIGOTT, C.A.', 240, '', '+ 582122037769', 'abigail_mendoza@bat.com', 'stefania_tassinari@bat.com', 0, 0, 1),
(106, 58, 2194, 'J312492414', 0, 'AGROBIGOTT, C.A.', 240, '', '+ 582122037769', 'abigail_mendoza@bat.com', 'stefania_tassinari@bat.com', 0, 0, 1),
(107, 58, 2195, 'J002980265', 0, 'ATRIO SEGUROS, S.A.', 240, '', '+ 58126298000', 'hbustamante@atrioseguros.com', 'hbustamante@atrioseguros.com', 0, 0, 1),
(108, 149, 2196, 'J075102240', 0, 'SERVINAVE, C.A.', 240, '', '+ 582125729622', '', 'correo@dominio.com', 0, 0, 1),
(109, 144, 2197, 'J001682104', 0, 'DISTEPAL INDUSTRIAL, S.A.', 240, '', '+ 582129931975', 'faltuve@amadecasa.com', 'faltuve@amadecasa.com', 0, 0, 1),
(110, 144, 2198, 'J00', 0, 'GRF CPA&ADVISORS', 240, '', '+ 58212500500', 'GRF CPA&ADVISORS', 'rtrujillo@grfcpa.com', 0, 0, 1),
(111, 146, 2199, 'J302406641', 0, 'CORPORACIÓN TELEMIC, C.A.', 240, '', '+ 582513355250', 'dennys.camacaro@inter.com.ve', 'dennys.camacaro@inter.com.ve', 0, 0, 1),
(112, 146, 2200, 'J305737622', 0, 'VENCO SERVICE, C.A.', 240, '', '+ 582513355250', 'dennys.camacaro@inter.com.ve', 'dennys.camacaro@inter.com.ve', 0, 0, 1),
(113, 146, 2201, 'J308748994', 0, 'INTER BUILDING, C.A.', 240, '', '+ 582513355250', 'elizabeth.mohen@inter.com.ve', 'elizabeth.mohen@inter.com.ve', 0, 0, 1),
(114, 146, 2202, 'J305737657', 0, 'INFOCABLE, C.A.', 240, '', '+ 582513355250', 'dennys.camacaro@inter.com.ve', 'dennys.camacaro@inter.com.ve', 0, 0, 1),
(115, 144, 2203, 'J306373969', 0, 'ORICA VENEZUELA', 240, 'Torre Humbolt Piso 5 Oficina 5-14\nBaruta.', '+ 4144434478', '', 'jose.colmenares@orica.com', 0, 0, 1),
(116, 145, 2204, 'J300619460', 0, 'BANCO OCCIDENTAL DE DESCUENTO BANCO UNIVERSAL, C.A.', 240, 'Avenida 17 con Calle 77 avenida Cinco de Julio Torre BOD, sector Paraiso MARACAIBO Estado Zulia', '+ 587502974', 'www.bodinternet.com', 'itirado@bod.com.ve', 0, 0, 1),
(117, 4, 2205, 'G200051876', 0, 'BANCO DEL TESORO, BANCO UNIVERSAL, C.A', 240, 'Calle Guaicapuro. Edificio BAnco del Tesoro El Rosal Caracas', '+ 589999429', '', 'atencionc@bt.gob.ve', 0, 0, 2),
(118, 144, 2206, 'J300558053', 0, 'SOS ALDEAS INFANTILES VENEZUELA', 240, 'AV DIEGO CISNEROS EDIF CENTRO EMPRESARIAL LOS\nRUICES PISO 5 OF 503 URB LOS RUICES CARACAS MIRANDA ZONA POSTAL 1070', '+ 582122391514', 'www.aldeasinfantiles.org.ve', 'tanny.valero@aldeasinfantiles.org.ve', 0, 0, 1),
(119, 67, 2207, 'J312692740', 0, 'TRANSPORTE PAKPLAZA,C.A.', 240, 'Av. Principal de la  Lomas Lagunita,  Irb. Lomas de la Lagunita', '+ 5804143355077', '', 'pakplaza@gmail.com', 0, 0, 1),
(120, 67, 2208, 'J00019575', 0, 'INDUSTRIAS FARCOMETICAS ASOCIADOS (INDUFARAS)', 240, 'Calle las Vegas, Zona Industrial  la Trinidad', '+ 5802129454711', '', 'indufaras@gmail.com', 0, 0, 1),
(121, 67, 2209, 'J000900957', 0, 'DISTRIBUIDORA IFA, C.A.', 240, 'Calle Marcano E/ Narvaez y Amador Hernandez', '+ 5804123234132', '', 'ifamercadeo@gmail.com', 0, 0, 1),
(122, 67, 2210, 'J400065399', 0, 'REPRESENTACIONES COSPER, S.A', 240, 'Calle las Vegas, Zona Industrial la Trinidad', '+ 5804147934132', '', 'ifamercadeo@gmail.com', 0, 0, 1),
(123, 67, 2211, 'J400518008', 0, 'FONDO GLOBAL DE LA CONTRUCCION, C.A.', 240, 'Av. Tamanaco y Francisco de Miranda, El Rosal', '+ 5802129572005', '', 'aguillermo@fgdcve.com', 0, 0, 1),
(124, 67, 2212, 'J401501001', 0, 'M2 PANELES DE CONSTRUCCION, C.A.', 240, 'Carretera Nacional Los Guayos Sector Mozanquita, Valencia', '+ 5802129572005', '', 'aguillermo@fgdcve.com', 0, 0, 1),
(125, 67, 2213, 'J400626412', 0, 'CONSTRUCTORA JAAR, C.A.', 240, 'Av. Tamanaco y Francisco de Miranda, El Rosal', '+ 5802129572005', '', 'aguillermo@fgdcve.com', 0, 0, 1),
(126, 67, 2214, 'J402997655', 0, 'CONSORCIO ESTRUCTURA METALICAS MODERNAS', 240, 'Av. Tamanaco y Francisco de Miranda, El Rosal', '+ 5802129572005', '', 'aguillermo@fgdcve.com', 0, 0, 1),
(127, 67, 2215, 'J313550388', 0, 'NIDEC MOTORS VENEZUELA, S.A.', 240, 'Zona Industrial los Tanques, parcela 1, Villa de Cura', '+ 5804141267638', '', 'ricardo.reyes@nidec-motor.com', 0, 0, 1),
(128, 67, 2216, 'J306842675', 0, 'MERCADOLIBRE VENEZUELA, S.R.L.', 240, 'AV. Eugenio Mendoza de la Castellana', '+ 5802126306000', '', 'ext_gbarrio@mercadolibre.com', 0, 0, 1),
(129, 67, 2217, 'J294782914', 0, 'VENPROCA VENEZOLANA DE PROYECTOS, C.A.', 240, 'Av. Francisco de Miranda, El Rosal', '+ 5804162392655', '', 'jgil@venproca.com', 0, 0, 1),
(130, 67, 2218, 'J411660752', 0, 'IMPORTADORA DIVINOS & DESTILADOS, C.A.', 240, 'Calle Bolivar, Urb. Baruta', '+ 5804142708935', '', 'divinosydestilados@gmail.com', 0, 0, 1),
(131, 67, 2219, 'J315546302', 0, 'FUNDACION FONDO DE PREVISION SOCIAL CENTRO DE ESPECIALIDADES ANZOATEGUI', 240, 'Av. Principal de Lecheria, Centro de especialidades Anzoategui, Nucleo E CC Edif. Yariku, Nucleo E Piso 3, Oficina 9, Lecheria - Edo. Anzoategui', '+ 58', '', 'fpscea@gmail.com', 0, 0, 1),
(132, 67, 2219, 'J315546302', 0, 'FUNDACION FONDO DE PREVISION SOCIAL CENTRO DE ESPECIALIDADES ANZOATEGUI', 240, 'Av. Principal de Lecheria, Centro de especialidades Anzoategui, Nucleo E CC Edif. Yariku, Nucleo E Piso 3, Oficina 9, Lecheria - Edo. Anzoategui', '+ 58', '', 'fpscea@gmail.com', 0, 0, 1),
(133, 67, 2219, 'J315546302', 0, 'FUNDACION FONDO DE PREVISION SOCIAL CENTRO DE ESPECIALIDADES ANZOATEGUI', 240, 'Av. Principal de Lecheria, Centro de especialidades Anzoategui, Nucleo E CC Edif. Yariku, Nucleo E Piso 3, Oficina 9, Lecheria - Edo. Anzoategui', '+ 58', '', 'fpscea@gmail.com', 0, 0, 1),
(134, 58, 2220, 'J296507570', 0, 'INDUSTRIA DE CARTONAJE CRD, C.A.', 240, 'Avenida San Antonio, Sector El Tumuso, Galpón Nro. C-2A, Santa Teresa del Tuy Miranda', '+ 5802129490900', '', 'correo@dominio.com', 0, 0, 1),
(135, 146, 2221, 'J306241809', 0, 'TELEMIC INDUSTRIAS VENEZOLANAS PARA LAS TELECOMUNICACIONES, C.A.', 240, 'Zona Industrial II, Calle 3 entre Calle 4 y 6, Galpon N° 259, Barquisimeto	Estado Lara', '+ 02513355250', '', 'dennys.camacaro@inter.com.ve', 0, 0, 1),
(136, 147, 2222, 'J310886229', 0, 'HERRENKNECHT, S.A.', 240, 'Avenida Tamanaco, Edificio La Unión, piso 3 3-A Urbanización El Rosal', '+ 5812345678', '', 'correo@dominio.com', 0, 0, 1),
(137, 146, 2223, 'J311821414', 0, 'FONDO DE AHORROS DEL GRUPO SERVIQUIM', 240, 'Av. Francisco de Miranda Edif. Parque Cristal Torre Este -Piso 7 Piso 7 Los Palos Grandes. Caracas', '+ 5555555', 'MZambrano@serviquim.com', 'mzambrano@serviquim.com', 0, 0, 1),
(138, 144, 2224, 'G200053577', 0, 'FONDO DE DESARROLLO NACIONAL FONDEN, S.A.', 240, 'Avenida Urdaneta, Esquina de Carmelitas a Esquina Altagracia.Edificio Norte del Ministerio del Poder Popular para la Banca y Finanza. Parroquia Altagracia. Piso 1 Caracas', '+ 5802128025248', 'jleon@fonden.gob.ve', 'jleon@fonden.gob.ve', 0, 0, 1),
(139, 149, 2225, 'J315259311', 0, 'AC NIELSEN DE VENEZUELA, S.A', 240, 'Av. José María Vargas,Edf. Torre del Colegio Médico,Urb. Santa Fe Norte.Piso 10. Caracas', '+ 5802129184100', 'valerie.prado@nielseniq.com', 'valerie.prado@nielseniq.com', 0, 0, 1),
(140, 7, 2226, 'J305366128', 0, 'CORPORACIÓN IADIEXPORT, C.A.', 240, 'URBANIZACIÓN INDUSTRIAL EL CUJIAL.CALLE PPAL.PARCELA 45,SANTA TERESA DEL TUY,ESTADO MIRANDA.', '+ 5802392313755', 'elsymaestre@gmail.com', 'elsymaestre@gmail.com', 0, 0, 1),
(141, 58, 2227, 'J310370109', 0, 'INVERSIONES 9954, C.A.', 240, 'Av. Río Caura,CC Concresa,Nivel 1 Oficina FM Center,Caracas Prados del Este', '+ 58', 'milagroslopez@fmcenter.com.ve', 'jesussilva2709@gmail.com', 0, 0, 1),
(142, 58, 2228, 'J303045057', 0, 'CHARTER COMMUNICATIONS INTERNATIONAL DE VENEZUELA, C.A.', 240, 'Urb. Parque Sebucán,Av. Los Chorros con Av. Parque Sebucán,Edif. C-Com, piso 1. Oficina Único,Caracas', '+ 5802122860438', 'info@internetccom.net', 'info@internetccom.net', 0, 0, 1),
(143, 147, 2229, 'C5014115060', 0, 'ALCON PHARMACEUTICAL,C.A', 240, 'URB LOS RUICES CARACAS MIRANDA,CALLE TERCERA TRANSVERSAL,EDIF NOVARTIS PISO 2 OF\nALA NOROESTE. Piso 2 Caracas, Miranda', '+ 582031011', 'www.alcon.com', 'leyva.lugo_toledo@alcon.com', 0, 0, 1),
(144, 146, 2230, 'J309798421', 0, 'SC MÁRQUEZ,PERDOMO Y ASOCIADOS', 240, 'CALLE LOS LABORATORIOSEDIF OFINCA PISO 4 OFIC 43 LOS RUICES, CARACAS', '+ 5802122350147', 'WWW.CROWEVENEZUELA.COM', 'auditoria@crowe.com', 0, 0, 1),
(145, 150, 2231, 'J002664436', 0, 'CONSORCIO CREDICARD, C.A.', 240, 'Av Prinicpal del Bosque, Av. Santa Isabel y Santa Lucia.Torre Credicard. piso 19 caracas', '+ 581802129559849', '', 'correo@dominio.com', 0, 0, 1),
(146, 149, 2232, 'J000596620', 0, 'FUNDACION BIGOTT', 240, 'CENTRO HISTORICO DE PETARE EL VIGIA. MIRANDA', '+ 5802122037511', 'GINANNINA_RODRIGUEZ@BAT.COM', 'ginannina_rodriguez@bat.com', 0, 0, 1),
(147, 146, 2233, 'J300292223', 0, 'GRUPO FINANCORP, C.A.', 240, 'Caracas. venezuella', '+ 582125555555', '', 'correo@domino.com', 0, 0, 1),
(148, 147, 2234, 'J070133805', 0, 'BANESCO, BANCO UNIVERSAL, C.A', 240, 'Avenida Princcipal de Bello Monte entre calle  Lincoln y Soborna. Edificio Ciudad Banesco. Bello Monte, Piso 3 Cuadrante C. Caracas, Distrito Capital', '+ 5802125017496', 'www.banesco.com', 'correo@dominio.com', 0, 0, 1),
(149, 58, 2235, 'J310079463', 0, 'SOPORTE S.P.I, C.A', 240, 'Avenida Principal de los Dos camnos Centro Comercial Milenium, Los Dos Caminos. Miranda Nivel 11 11-1', '+ 582126271220', 'www.spi.com.ve', 'lmaldonado@spi.com.ve', 0, 0, 1),
(150, 58, 2236, 'J000468621', 0, 'DESARROLLO E INVERSIONES S.A.', 240, 'Avenida Francisco de Miranda. Edificio CAVENDES. Urbanizacion los Palos Grandes. Piso 9 oficina 905. Miranda', '+ 580212000000', '', 'sintegram@hotmail.com', 0, 0, 1),
(151, 146, 2237, 'J500373646', 0, 'ACCIONA SOCIEDAD DE CORRETAJE DE VALORES S.A.', 240, 'Centro San Ignacio, Torre Copernico Piso 2 Oficina 203 y 204 Caracas', '+ 5802122345678', 'acciona.com.ve', 'leonela.oviedo@accionavalores.com', 0, 0, 1),
(152, 150, 2238, 'J293958717', 0, 'SERVICIOS CLÍNICOS SANTA MÓNIICA C.A.', 240, 'Avenida Teresa de la Parra Edificio San Nicolas Santa Monica Caracas', '+ 580259474', '', 'correo@dominio.com', 0, 0, 1),
(153, 150, 2239, 'J000423865', 0, 'QUESOLANDIA S.A.', 240, 'Calle Chicago entre Av. Milan y Trieste. Edificio Industrial Quesolandia .Los Ruices. caracas', '+ 58', 'www.elpaisa.net', 'jguerra@elpaisa.com.ve', 0, 0, 1),
(154, 150, 2240, 'J070014733', 0, 'PASTEURIZADORA TÁCHIRA C.A.', 240, 'calle 8, Edificio 9-13.Sector La Concordia. PB.N° 9-13. San Cristobal. Estado Tachira', '+ 5802763491488', 'www.elpaisa.net', 'jguerra@paisa.com.ve', 0, 0, 1),
(155, 58, 2241, 'J002591200', 0, 'MODUSISTEMA C.A.', 240, 'Ctra. Petare Santa Lucía, Edificio Centro Industrial Viana, Galpón 6-A, PB Caracas', '+ 5802129930721', 'www.crowe.com.ve', 'sistema.carent@crowe.com.ve', 0, 0, 1),
(156, 146, 2242, 'J413183463', 0, 'FINTECH VALORES CASA DE BOLSA, C.A.', 240, 'Calle 149, Edificio Centro A1 Local LA 149 Valencia Estado carabobo', '+ 5804146634484', 'nmedina@fintechvalores.com', 'nmedina@fintechvalores.com', 0, 0, 1),
(158, 58, 2243, 'J002376090', 0, 'ELECTRÓNICA DE FÁBRICAS FACTRONICS, C.A.', 240, 'Av. Rómulo Gallegos, Edificio Centro Gerencial Los Andes, Sector Boleíta Norte, PH-1B caracas', '+ 5802122222222', 'www.ejemplo.com.ve', 'mbmqueen@gmail.com', 0, 0, 1),
(159, 149, 2244, 'J001370510', 0, 'MÉDICOS UNIDOS LOS JABILLOS, C.A.', 240, 'AV. ANDRES BELLO ENTRE LAS CALLES LAS PALMAS Y LOS MANOLOS. EDIFICIO POLICLINICA MENDEZ GIMON. CARACAS', '+ 5802121234567', 'WWW.CROER.COM.VE', 'bechacin@gmail.com', 0, 0, 1),
(161, 149, 2245, 'J001630678', 0, 'RESTOVEN DE VENEZUELA, C.A', 240, 'AV ABRAHAN LINCOLN. EDIF SELEMAR. BOULEVARD DE SABANA GRANDE', '+ 5802127628010', 'www.crowe.com.ve', 'CVILLALBA@RESTOVEN.COM', 0, 0, 1),
(162, 144, 2246, 'J500373646', 0, 'SOCIEDAD CIVIL ALAN ALDANA ABOGADOS Y ASOCIADOS', 240, 'Av. LUIS ROCHE. Edif. HELENA. P2 Ofic. 16. Altamira', '+ 5802128239390', 'contacto@aldanayabogados.com', 'aldanayabogados.com', 0, 0, 1),
(163, 146, 2247, 'J00000000', 0, 'Campomarino S.A.', 176, 'Aeropuerto Marcos A Gelabert Albrook. Av Canfield.Hangar 24b', '+ 5073150595', 'www.campomarino.ws', 'info@campomarino.ws', 0, 0, 1),
(164, 147, 2248, 'J301037464', 0, 'CBPO Ingenieria de Venezuela, C.A.', 240, 'Avenida Rio Caura.Edif. Torre Humboldt.Piso 10 10-13 Urbanización Prados del Este', '+ 5802122111100', 'vepinto@oec-eng.com', 'vepinto@oec-eng.com', 0, 0, 1),
(166, 146, 2249, 'J314087754', 0, 'AC UCAB Servicios', 240, 'AV TEHERAN.UNIVERSIDAD CATOLICA ANDRES BELLO EDIF LOYOLA .PISO 1. OFIC. UNICA.URB.MONTALBAN. CARACAS', '+ 581234567', 'mmanzanilla@ucab.edu.ve', 'rcastill@ucab.edu.ve', 0, 0, 1),
(167, 147, 2250, 'J314229001', 0, 'MPF ASESORIA Y CONSULTORIA', 240, 'Avenida Principal de Los Ruices. Edificio Monaca P1 Caracas', '+ 5802122350147', 'www.crowe.com.ve', 'mpf@crowe.com', 0, 0, 1),
(173, 146, 2251, 'J500194552', 0, 'PIDEYUMMY, S.A.', 240, 'La Floresta avenida Francisco de Miranda Torre Banco del Orinoco PH 1, Chavcao Caracas', '+ 584164099083', 'https://yummydelivery.com.ve/', 'tucontacto@yummydelivery.com.ve', 0, 0, 1),
(202, 150, 2252, 'J309901303', 0, 'Messangi Venezuela, C.A.', 240, 'Av. Francisco de Miranda y Av. Libertador  Multicentro Empresarial del Este Ofic. A-91. Piso 9 Edif. Miranda Torre A Chacao', '+ 5802122666510', 'www.crowe.com.ve', 'admin-ve@messangi.com', 0, 0, 1),
(203, 150, 2253, 'J003398268', 0, 'Geosinteticos Trical, C.A.', 240, 'Esquina de Calle Nirgua, con calle Socuy Quinta: Ursulina PB La Trinidad Caracas', '+ 5802129422968', 'irojas@trical.net', 'sistema.carten@crowe.com.ve', 0, 0, 1),
(204, 150, 2254, 'J310885850', 0, 'CENTRO MÉDICO PROFESIONAL LAS MERCEDES C.A.', 240, 'Avenida José Martí con calle Mucuchies, Centro Médico Profesional Las Mercedes, Piso 5, Oficina Administración. Urb. Las Mercedes. Municipio Baruta, Edo. Miranda.', '+ 5802129929591', 'www.crowe.com.ve', 'sistema. carent@crowe.com.ve', 0, 0, 1),
(205, 146, 2255, 'J000126518', 0, 'COMPAÑÍA ANÓNIMA CINES UNIDOS', 240, 'Urbanización Prados del Este.Av. Río Caura y Paragua.Edificio Núcleo Ejecutivo La Pirámide PA Ofic 01. Caracas', '+ 5802126207454', 'ygonzalez@cinesunidos.com', 'ygonzalez@cinesunidos.com', 0, 0, 1),
(206, 146, 2256, 'J302626471', 0, 'MULTICINE LAS TRINITARIAS, C.A.', 240, 'Urbanización Prados del Este.Av. Río Caura y Paragua.Edificio Núcleo Ejecutivo La Pirámide.PA Ofic 01. Caracas', '+ 582126207454', 'ygonzalez@cinesunidos.com', 'ygonzalez@cinesunidos.com', 0, 0, 1),
(207, 150, 2257, 'J000654042', 0, 'TRICAL DE VENEZUELA.C.A.', 240, 'ESQUINA CALLE DE NIRGUA, CON CALLE SOCUY.QUINTA LA URSULINAPB LA TRINIDAD. CARACAS', '+ 5824122748336', 'JROJAS@TRICAL.NET', 'JROJAS@TRICAL.NET', 0, 0, 1),
(208, 150, 2258, 'J295904690', 0, '1001 Propiedades, C.A.', 240, 'Av. Principal C/C Mucuhies Edfi. Centro Profosional las Mercedes Piso 1 Ofic. 15 Centro Profesional las Mercedes Caracas', '+ 5802129932911', 'annalisaherrera@yahoo.com', 'annalisaherrera@yahoo.com', 0, 0, 1),
(209, 144, 2259, 'G200001607', 0, 'gobernacion del estado tachira', 240, 'calle 4 y 5con carrera 11 local sede del ejecutivodel estado táchira piso 6 sector centro san cristobal. san crsitobal. Estado Táchira', '+ 582121234567', 'www.crowe.com.ve', 'sistema.carent@crowe.com', 0, 0, 1),
(210, 146, 2260, 'E398974', 0, 'UNIQUE INTERNATIONAL BANK, INC', 183, 'MIRAMAR PLAZA 954\nPONCE DE LEON AVENUE\nSTE 209\nSAN JUAN PR 00907', '+ 17879452012', 'www.crowe.com0ve', 'sistema.carent@crowe.com.ve', 0, 0, 1),
(211, 58, 2261, 'J297981705', 0, 'BELIZE INVESTMENTS C.A.', 240, 'AV PPAL CC CONCRESA NIVEL 1 OF 10 URB PRADOS DEL\nESTE CARACAS DISTRITO CAPITAL ZONA POSTAL 1061', '+ 5802122222222', 'WWW.CROWE.COM.VE', 'SISTEMAS.CARENT@CROWE.COM.VE', 0, 0, 1),
(212, 146, 2262, 'J95048551', 0, 'BANCO CARONI, BANCO UNIVERSAL', 240, 'AVENIDA VIA VENEZUELA EDIFICIO MULTICENTRO OFICINA PB.SCETRO VILAL COLOMBIA.PUERTO ORDAZ CIUDAD GUAYANA.', '+ 58212123456', 'https://www.crowe.com/ve', 'sistema.carent@crowe.com.ve', 0, 0, 1),
(213, 58, 2263, 'J000067481', 0, 'CLÍNICA SANATRIX, C.A.', 240, 'AV 4TA COB 2DA CALLE .EDF HIGEA. OB.CAMPO ALEGRE. CARACAS', '+ 582016625', 'https://www.crowe.com/ve', 'sistema.carent@crowe.com.ve', 0, 0, 1),
(214, 146, 2264, 'J12345678', 0, 'OPERADORA C.C., S.S. DE C.V', 90, 'HONDURAS', '+ 509123456', 'WWW.CROWE.COM.HN', 'SISTEMA.CARENT@CROWE.COM.VE', 0, 0, 1),
(215, 146, 2265, 'J123456789', 0, 'CONSTRUCTORA C.C., S.A. DE C.V.', 90, 'HONDURAS', '+ 504123456789', 'WWW.CROWE.COM.VE', 'SISTEMA.CARENT@CROWE.COM.VE', 0, 0, 1),
(216, 146, 2266, 'J12345678', 0, 'El palacio del niño', 90, 'honduras', '+ 5041234567', 'www.crowe.com.ve', 'sistema.carent@com.ve', 0, 0, 1),
(217, 150, 2267, 'J000146608', 0, 'LAPREVEN, S.A.', 240, 'Avenida Bethoven Torre Financiera.Oficina A y B Piso 1 Bello Monte. Caracas', '+ 5801412844526', 'https//www.crowe.com.ve', 'marlene.villasinda@geniacare.com', 0, 0, 1),
(218, 144, 2268, 'J123456789', 0, 'ceramicasa', 90, 'honduras', '+ 50412345678', 'www.crowe.com.ve', 'sistema.carent@crowe.com.ve', 0, 0, 1),
(219, 146, 2269, 'J123456789', 0, 'EDIFICA INMOBILIARIA', 90, 'honduras', '+ 504123456789', 'www.crowe.com.ve', 'sistema.carent@crowe.com.ve', 0, 0, 1),
(220, 146, 2270, 'J12345678', 0, 'EDIFICA INMOBILIARIA', 90, 'honduras', '+ 504123456789', 'WWW.CROWE.COM.VE', 'SISTEMAS.CARENT@CROWE.COM.VE', 0, 0, 1),
(221, 144, 2271, 'G200001704', 0, 'SUPERINTENDENCIA NACIONAL DE VALORES (SUNAVAL)', 240, 'AVENIDA FRANCISCO SOLANO LOPEZ EDIFICIO SUNAVAL. SABANA GRANDE. CARACAS', '+ 5804142219729', 'https://www.sunaval.gob.ve', 'javierslh@gmail.com', 0, 0, 1),
(222, 146, 2272, 'J316374173', 0, 'BANCRECER S.A.  BANCO MICROFINANCIERO', 240, 'AVENIDA FRANCISCO DE MIRANDA.TORRE BAZAR BOLIVAR. PB. CARACAS', '+ 5802126108902', 'WWW.BANCRECER.COM.VE', 'RAUL.RONDON@BANCRECER.COM.VE', 0, 0, 1),
(223, 58, 2273, 'J294989063', 0, 'CASA MONTORO FG C.A.', 240, 'Parcelamiento los Dolores.Calle principal La Yerba Buena.Quinta Santa Barbara Carrizal Estado Miranda', '+ 5804122353639', 'www.crowe.com.ve', 'graceg16@gmail.com', 0, 0, 1),
(224, 58, 2274, 'J413002507', 0, 'SEQUOIA SOLUCIONES INTEGRALES, C.A.', 240, 'Sector el Pueblo Calle Rivas Edif Juan XXIII Los Teques', '+ 5804122353639', 'www.crowe.com.ve', 'graceg16@gmail.com', 0, 0, 1),
(225, 58, 2275, 'J410003405', 0, 'LA NUECERY`S, C.A.', 240, 'Sector el Pueblo, Calle Rivas,Edif Juan XXIII. Miranda,. Los Teques', '+ 5804122353639', 'www.crowe.com.ve', 'graceg16@gmail.com', 0, 0, 1),
(226, 146, 2276, 'J501835705', 0, 'MULTIPLICAS SOCIEDAD DE CORRETAJE DE VALORES C.A.', 240, 'Av. Blandin Centro San Ignacio PISO 9 TE-P09-01 Caracas (Chacao) Centro San Ignacio', '+ 5804149277584', 'info@multiplicas.com.ve', 'tramirez@multiplicas.com.ve', 0, 0, 1),
(227, 147, 2277, 'J00000000', 0, 'INVERSIONES CRUZANG', 240, 'CARACAS', '+ 5884241908561', 'info@cruzang.net', 'info@cruzang.net', 0, 0, 1),
(228, 147, 2278, 'J316358968', 0, 'INVERSIONES PROTECA, C.A', 240, 'Calle 1 con calle 2 Edif. Cyanamid ofic31 P3 LA Urbina Caracasa', '+ 582122420562', 'www.crowe.com.ve', 'inversionesproteca@gmail.com', 0, 0, 1),
(229, 147, 2279, 'J311960414', 0, 'INTERVIT, C.A.', 240, 'AVENIDA JALISCO. EDIFICIOLA COLONIA P3 3-A LAS MERCEDES CARACAS', '+ 5802129934794', 'WWW.INTERVIT.COM.VE', 'CF@BOSTO.GROUP', 0, 0, 1),
(230, 150, 2280, 'J412545400', 0, 'MONEYWAYS CORP ITFB, C.A', 240, 'AV. VENEZUELA  EDIFICIO PLATINUM II PISO 8 OF 8-B URBANIZACION EL ROSAL CARACAS', '+ 5802129529861', 'WWW.CROWE.COM.VE', 'jvan@moneywayscorp.com', 0, 0, 1),
(231, 7, 2281, 'J306036849', 0, 'ALMACENADORA VENEZUELA, C.A.', 240, 'CALLE LONDRES PLAZA C PISO 5 OFICINA 5-B URBANIZACIÓN LAS MERCEDES LAS MERCEDES', '+ 5802129931803', 'n.medouze@almaven.com', 'n.medouze@almaven.com', 0, 0, 1),
(232, 146, 2282, 'J000701466', 0, 'Camara Venezolana Americana de Comercio e Industria', 240, '2da Av. de Campo Alegre Torre Credival Piso 10, Chacao. Caracas', '+ 5821211111111', 'www.venamcham.org', 'sistema.carent@carent.com.ve', 0, 0, 1),
(233, 146, 2283, 'J296963274', 0, 'Banco del Alba', 240, 'Av. Francisco Solano López con calle San Gerónimo Edificio Los Llanos Sabana Grande.Caracas', '+ 02129059300', 'www.bt.gob.ve', 'contacto@bancodelalba.org', 0, 0, 1),
(234, 145, 2284, 'J095170993', 0, 'VENEZUELAN EXPRESS CASA DE CAMBIO, C.A.', 240, 'AV. LIBERTADOR  EDIFICIO NUEVO CENTRO  PB A Y B HACAO CARACAS', '+ 58123456', 'WWW.CROWE.COM.VE', 'SISTEMA.CARENT@CROWE.COM.VE', 0, 0, 1),
(235, 58, 2285, 'J412732510', 0, 'INVERSIONES THEODORA 11-11, C.A.', 240, 'Carretera Petare Guarenas km 14 Galpon S/N Caucaguita Miranda', '+ 5802127313802', 'www.crowe.com.ve', 'sistema.carent@crowe.com.ve', 0, 0, 1),
(236, 58, 2286, 'J001280669', 0, 'BAUMER BAVE, S.A.', 240, 'Avenida principal Lebrum Galpon  41-A Industrial Lebrum, Parroquia Petare', '+ 5802122569336', 'www.crowe.com.ve', 'sistema.carent@cowe.com.ve', 0, 0, 1),
(240, 146, 2287, 'J294969976', 0, 'petrodelta, s.a.', 240, 'CALLE EL EMPALME URBANIZACION LA CAMPIÑA EDIFICIO PETROLEOS DE VENEZUELA CARACAS', '+ 5821230070312', 'https://www.crowe.com/ve', 'GONZALEZLMR@PETRODELTA.PDVSA..COM', 0, 0, 1),
(241, 58, 2288, 'J300522369', 0, 'SEGUROS ALTAMIRA C.A.', 240, 'AV LIBERTADOR  CC CENTRO COMERCIAL LIBERTADOR  NIVEL\nPH OF 3-5 CARACAS', '+ 582122222222', 'http://www.segurosaltamira.com', 'ja.legal@segurosaltamira.com', 0, 0, 1),
(242, 146, 2289, 'J411877891', 0, 'PETROSERVICIOS MORICHAL, S.A.', 240, 'CTRA NACIONAL VIA  VIBORAL CC VIRGEN DEL VALLE RB TIPURO PLANTA ALTA	OF 18-A MATURIN ESTADO MONAGAS', '+ 5804265945537', 'WWW.CROWE.COM.VE', 'petroserviciosmorichal@gmail.com', 0, 0, 1),
(243, 147, 2290, 'J000355789', 0, 'PMI, C.A.', 240, 'Avenida Francisco de Miranda Torre KPMG, piso 6 Chacao Caracas D.C.', '+ 581234567', 'WWW.CROWE.COM.VE', 'SISTEMAS.CARENT@CROWE.COM.VE', 0, 0, 1),
(244, 146, 2291, 'J000303614', 0, 'C.A. Telares de Palo Grande', 240, 'Zona Industrial Ruiz Pineda Carretera Vieja de los Teques Edif. Telares de Palo Grande Caracas', '+ 8402124033620362', 'http://telaresdepalogrande.com', 'asanchez@telaresdepalogrande.com', 0, 0, 1),
(245, 146, 2292, 'J000040206', 0, 'H. Blohm, S.A.', 240, 'Zona Industrial Ruiz Pineda Carretera Vieja de los Teques Edif. Telares de Palo Grande Caracas', '+ 582124033620', 'www.crowe.com.ve', 'asanchez@telaresdepalogrande.com', 0, 0, 1),
(246, 149, 2293, 'J000846774', 0, 'Fundicion Pacifico, C.A.', 240, 'Sector Hacienda Las Mercedes, Filas de Mariche Carretera Petare-Santa Lucia, KM 12, Calle El Desvio Local Galpon FP Caracas Miranda', '+ 582127186800', 'https://www.fundicionpacifico.com', 'ecommerce@fundicionpacifico.com', 0, 0, 1),
(247, 146, 2294, 'J501835705', 0, 'MULTIPLICAS CASA DE BOLSA C.A.', 240, 'Av. Blandin Centro San Ignacio PISO 9 TE-P09-01 Caracas (Chacao) Miranda', '+ 5804149277584', 'info@multiplicas.com.ve', 'tramirez@multiplicas.com.ve', 0, 0, 1),
(248, 146, 2295, 'J3315701677', 0, 'SUMA CASA DE BOLSA C.A.', 240, 'Av. Paseo Eraso C/Calle Chivacoa Edificio Tamanaco PISO 11 11-C 11-D Caracas', '+ 5802127507200', 'www.sumavalores.com', 'info@sumavalores.com', 0, 0, 1),
(249, 146, 2296, 'J000467382', 0, 'SEGUROS LA FE, C.A.', 240, 'AVENIDA SOLANO LÓPEZ QUINTA SAN GERMAN PISO 1 OFIC 1-B SABANA GRANDE CARACAS', '+ 5804129954575', 'WWW.CROWE.COM.VE', 'CARENT@CROWE.COM.VE', 0, 0, 1),
(250, 146, 2297, 'J406095737', 0, 'WITTY GROWTH C.A.', 240, 'Av Bolivar Norte Edificio CC Home Shopping Nivel 2 Local L-2-7 Valencia Estado Carabobo', '+ 5804141362723', 'www.crowe.com.ve', 'sistema.carent@crow.com.ve', 0, 0, 1),
(251, 146, 2298, 'J297324518', 0, 'Financorp la Castellana, C.A.', 240, 'AV. MOHEDANO Y LOS CHAGUARAMOS CENTRO COMERCIAL MOHEDANO p12 OFC A LA CASTELLANA', '+ 582122662366', 'WWW.CROWE.COM.VE', 'yvonnet.figueras@financorp.com', 0, 0, 1),
(252, 144, 2299, 'J085100580', 0, 'INTERBANK SEGUROS, S.A.', 240, 'Torre Alianza Av. Guaicaipuro, entre Av. Pichincha y Av. Principal de Las Mercedes, Urb. El Rosal  PB Caracas', '+ 582129512711', 'www.crowe.com.vew', 'sistema.carent@crowe.com.ve', 0, 0, 1),
(253, 147, 2300, 'J293877334', 0, 'ONGC VIDESH LIMITED', 240, 'Av. Francisco de Miranda. Edificio TORRE KPMG P Caraasiso 3 Ofic 32B Caracas', '+ 582122222222', 'www.crowe.com.ve', 'sistema.carent@crow.com.ve', 0, 0, 1),
(254, 145, 2301, 'J1111111', 0, 'MWM INVESTMENT LTD', 22, 'S/I GEORGE STREET THE PHOENIX CENTRE ST. MICHAEL BRIDGETOWN', '+ 124602127507204', 'WWW.CROWE.COM.VE', 'SISTEMA.CARENT@CROWE.COM.VE', 0, 0, 1),
(257, 144, 2302, 'J295369956', 0, 'LAGOPETROL, S.A.', 240, 'Av. Libertador con Calle Empalme, Edificio Petroleos de Venezuela  Torre Oeste  Oficina 2B  Caracas', '+ 5804126882736', 'www.crowe.com.ve', 'rasanchez@integraoil.com.ve', 0, 0, 1),
(258, 150, 2303, 'J296792933', 121212, 'Corporación Kanata, S.A.', 240, 'Av Don Eugenio Mendoza. Av San Felipe CC Centro Letonia Planta Galeria  PB  Chacao', '+ 5802122631412', 'www,crowe.com.ve', 'sistema.carent@crowe.com.ve', 1, 13, 1),
(259, 149, 2304, 'J401260411', 1234, 'GLOBAL R, C.A.', 240, 'CALLE 11 ENTRE CARRERAS 24 Y 25 DE BARRIO OBRERO URB PEDRO MARIA MORANTES QUINTA DONA OLGA SAN CRISTOBAL', '+ 02122645376', 'WWW.CROWE.COM.VE', 'INFO@GLOBALR.NET', 1, 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients_countries`
--

CREATE TABLE `clients_countries` (
  `country_id` int(11) NOT NULL,
  `country_name` varchar(300) NOT NULL,
  `iso3` varchar(300) NOT NULL,
  `phone_code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clients_countries`
--

INSERT INTO `clients_countries` (`country_id`, `country_name`, `iso3`, `phone_code`) VALUES
(0, 'No se ha seleccionado un pais', '0', '0'),
(1, 'AfganistÃ¡n', 'AFG', '93'),
(2, 'Albania', 'ALB', '355'),
(3, 'Alemania', 'DEU', '49'),
(4, 'Algeria', 'DZA', '213'),
(5, 'Andorra', 'AND', '376'),
(6, 'Angola', 'AGO', '244'),
(7, 'Anguila', 'AIA', '1264'),
(8, 'AntÃ¡rtida', 'ATA', '672'),
(9, 'Antigua y Barbuda', 'ATG', '1268'),
(10, 'Antillas Neerlandesas', 'ANT', '599'),
(11, 'Arabia Saudita', 'SAU', '966'),
(12, 'Argentina', 'ARG', '54'),
(13, 'Armenia', 'ARM', '374'),
(14, 'Aruba', 'ABW', '297'),
(15, 'Australia', 'AUS', '61'),
(16, 'Austria', 'AUT', '43'),
(17, 'AzerbayÃ¡n', 'AZE', '994'),
(18, 'BÃ©lgica', 'BEL', '32'),
(19, 'Bahamas', 'BHS', '1242'),
(20, 'Bahrein', 'BHR', '973'),
(21, 'Bangladesh', 'BGD', '880'),
(22, 'Barbados', 'BRB', '1246'),
(23, 'Belice', 'BLZ', '501'),
(24, 'BenÃ­n', 'BEN', '229'),
(25, 'BhutÃ¡n', 'BTN', '975'),
(26, 'Bielorrusia', 'BLR', '375'),
(27, 'Birmania', 'MMR', '95'),
(28, 'Bolivia', 'BOL', '591'),
(29, 'Bosnia y Herzegovina', 'BIH', '387'),
(30, 'Botsuana', 'BWA', '267'),
(31, 'Brasil', 'BRA', '55'),
(32, 'BrunÃ©i', 'BRN', '673'),
(33, 'Bulgaria', 'BGR', '359'),
(34, 'Burkina Faso', 'BFA', '226'),
(35, 'Burundi', 'BDI', '257'),
(36, 'Cabo Verde', 'CPV', '238'),
(37, 'Camboya', 'KHM', '855'),
(38, 'CamerÃºn', 'CMR', '237'),
(39, 'CanadÃ¡', 'CAN', '1'),
(40, 'Chad', 'TCD', '235'),
(41, 'Chile', 'CHL', '56'),
(42, 'China', 'CHN', '86'),
(43, 'Chipre', 'CYP', '357'),
(44, 'Ciudad del Vaticano', 'VAT', '39'),
(45, 'Colombia', 'COL', '57'),
(46, 'Comoras', 'COM', '269'),
(47, 'Congo', 'COG', '242'),
(48, 'Congo', 'COD', '243'),
(49, 'Corea del Norte', 'PRK', '850'),
(50, 'Corea del Sur', 'KOR', '82'),
(51, 'Costa de Marfil', 'CIV', '225'),
(52, 'Costa Rica', 'CRI', '506'),
(53, 'Croacia', 'HRV', '385'),
(54, 'Cuba', 'CUB', '53'),
(55, 'Dinamarca', 'DNK', '45'),
(56, 'Dominica', 'DMA', '1767'),
(57, 'Ecuador', 'ECU', '593'),
(58, 'Egipto', 'EGY', '20'),
(59, 'El Salvador', 'SLV', '503'),
(60, 'Emiratos Ãrabes Unidos', 'ARE', '971'),
(61, 'Eritrea', 'ERI', '291'),
(62, 'Eslovaquia', 'SVK', '421'),
(63, 'Eslovenia', 'SVN', '386'),
(64, 'EspaÃ±a', 'ESP', '34'),
(65, 'Estados Unidos de AmÃ©rica', 'USA', '1'),
(66, 'Estonia', 'EST', '372'),
(67, 'EtiopÃ­a', 'ETH', '251'),
(68, 'Filipinas', 'PHL', '63'),
(69, 'Finlandia', 'FIN', '358'),
(70, 'Fiyi', 'FJI', '679'),
(71, 'Francia', 'FRA', '33'),
(72, 'GabÃ³n', 'GAB', '241'),
(73, 'Gambia', 'GMB', '220'),
(74, 'Georgia', 'GEO', '995'),
(75, 'Ghana', 'GHA', '233'),
(76, 'Gibraltar', 'GIB', '350'),
(77, 'Granada', 'GRD', '1473'),
(78, 'Grecia', 'GRC', '30'),
(79, 'Groenlandia', 'GRL', '299'),
(80, 'Guadalupe', 'GLP', ''),
(81, 'Guam', 'GUM', '1671'),
(82, 'Guatemala', 'GTM', '502'),
(83, 'Guayana Francesa', 'GUF', ''),
(84, 'Guernsey', 'GGY', ''),
(85, 'Guinea', 'GIN', '224'),
(86, 'Guinea Ecuatorial', 'GNQ', '240'),
(87, 'Guinea-Bissau', 'GNB', '245'),
(88, 'Guyana', 'GUY', '592'),
(89, 'HaitÃ­', 'HTI', '509'),
(90, 'Honduras', 'HND', '504'),
(91, 'Hong kong', 'HKG', '852'),
(92, 'HungrÃ­a', 'HUN', '36'),
(93, 'India', 'IND', '91'),
(94, 'Indonesia', 'IDN', '62'),
(95, 'IrÃ¡n', 'IRN', '98'),
(96, 'Irak', 'IRQ', '964'),
(97, 'Irlanda', 'IRL', '353'),
(98, 'Isla Bouvet', 'BVT', ''),
(99, 'Isla de Man', 'IMN', '44'),
(100, 'Isla de Navidad', 'CXR', '61'),
(101, 'Isla Norfolk', 'NFK', ''),
(102, 'Islandia', 'ISL', '354'),
(103, 'Islas Bermudas', 'BMU', '1441'),
(104, 'Islas CaimÃ¡n', 'CYM', '1345'),
(105, 'Islas Cocos (Keeling)', 'CCK', '61'),
(106, 'Islas Cook', 'COK', '682'),
(107, 'Islas de Ã…land', 'ALA', ''),
(108, 'Islas Feroe', 'FRO', '298'),
(109, 'Islas Georgias del Sur y Sandwich del Sur', 'SGS', ''),
(110, 'Islas Heard y McDonald', 'HMD', ''),
(111, 'Islas Maldivas', 'MDV', '960'),
(112, 'Islas Malvinas', 'FLK', '500'),
(113, 'Islas Marianas del Norte', 'MNP', '1670'),
(114, 'Islas Marshall', 'MHL', '692'),
(115, 'Islas Pitcairn', 'PCN', '870'),
(116, 'Islas SalomÃ³n', 'SLB', '677'),
(117, 'Islas Turcas y Caicos', 'TCA', '1649'),
(118, 'Islas Ultramarinas Menores de Estados Unidos', 'UMI', ''),
(119, 'Islas VÃ­rgenes BritÃ¡nicas', 'VG', '1284'),
(120, 'Islas VÃ­rgenes de los Estados Unidos', 'VIR', '1340'),
(121, 'Israel', 'ISR', '972'),
(122, 'Italia', 'ITA', '39'),
(123, 'Jamaica', 'JAM', '1876'),
(124, 'JapÃ³n', 'JPN', '81'),
(125, 'Jersey', 'JEY', ''),
(126, 'Jordania', 'JOR', '962'),
(127, 'KazajistÃ¡n', 'KAZ', '7'),
(128, 'Kenia', 'KEN', '254'),
(129, 'KirgizstÃ¡n', 'KGZ', '996'),
(130, 'Kiribati', 'KIR', '686'),
(131, 'Kuwait', 'KWT', '965'),
(132, 'LÃ­bano', 'LBN', '961'),
(133, 'Laos', 'LAO', '856'),
(134, 'Lesoto', 'LSO', '266'),
(135, 'Letonia', 'LVA', '371'),
(136, 'Liberia', 'LBR', '231'),
(137, 'Libia', 'LBY', '218'),
(138, 'Liechtenstein', 'LIE', '423'),
(139, 'Lituania', 'LTU', '370'),
(140, 'Luxemburgo', 'LUX', '352'),
(141, 'MÃ©xico', 'MEX', '52'),
(142, 'MÃ³naco', 'MCO', '377'),
(143, 'Macao', 'MAC', '853'),
(144, 'MacedÃ´nia', 'MKD', '389'),
(145, 'Madagascar', 'MDG', '261'),
(146, 'Malasia', 'MYS', '60'),
(147, 'Malawi', 'MWI', '265'),
(148, 'Mali', 'MLI', '223'),
(149, 'Malta', 'MLT', '356'),
(150, 'Marruecos', 'MAR', '212'),
(151, 'Martinica', 'MTQ', ''),
(152, 'Mauricio', 'MUS', '230'),
(153, 'Mauritania', 'MRT', '222'),
(154, 'Mayotte', 'MYT', '262'),
(155, 'Micronesia', 'FSM', '691'),
(156, 'Moldavia', 'MDA', '373'),
(157, 'Mongolia', 'MNG', '976'),
(158, 'Montenegro', 'MNE', '382'),
(159, 'Montserrat', 'MSR', '1664'),
(160, 'Mozambique', 'MOZ', '258'),
(161, 'Namibia', 'NAM', '264'),
(162, 'Nauru', 'NRU', '674'),
(163, 'Nepal', 'NPL', '977'),
(164, 'Nicaragua', 'NIC', '505'),
(165, 'Niger', 'NER', '227'),
(166, 'Nigeria', 'NGA', '234'),
(167, 'Niue', 'NIU', '683'),
(168, 'Noruega', 'NOR', '47'),
(169, 'Nueva Caledonia', 'NCL', '687'),
(170, 'Nueva Zelanda', 'NZL', '64'),
(171, 'OmÃ¡n', 'OMN', '968'),
(172, 'PaÃ­ses Bajos', 'NLD', '31'),
(173, 'PakistÃ¡n', 'PAK', '92'),
(174, 'Palau', 'PLW', '680'),
(175, 'Palestina', 'PSE', ''),
(176, 'PanamÃ¡', 'PAN', '507'),
(177, 'PapÃºa Nueva Guinea', 'PNG', '675'),
(178, 'Paraguay', 'PRY', '595'),
(179, 'PerÃº', 'PER', '51'),
(180, 'Polinesia Francesa', 'PYF', '689'),
(181, 'Polonia', 'POL', '48'),
(182, 'Portugal', 'PRT', '351'),
(183, 'Puerto Rico', 'PRI', '1'),
(184, 'Qatar', 'QAT', '974'),
(185, 'Reino Unido', 'GBR', '44'),
(186, 'RepÃºblica Centroafricana', 'CAF', '236'),
(187, 'RepÃºblica Checa', 'CZE', '420'),
(188, 'RepÃºblica Dominicana', 'DOM', '1809'),
(189, 'ReuniÃ³n', 'REU', ''),
(190, 'Ruanda', 'RWA', '250'),
(191, 'RumanÃ­a', 'ROU', '40'),
(192, 'Rusia', 'RUS', '7'),
(193, 'Sahara Occidental', 'ESH', ''),
(194, 'Samoa', 'WSM', '685'),
(195, 'Samoa Americana', 'ASM', '1684'),
(196, 'San BartolomÃ©', 'BLM', '590'),
(197, 'San CristÃ³bal y Nieves', 'KNA', '1869'),
(198, 'San Marino', 'SMR', '378'),
(199, 'San MartÃ­n (Francia)', 'MAF', '1599'),
(200, 'San Pedro y MiquelÃ³n', 'SPM', '508'),
(201, 'San Vicente y las Granadinas', 'VCT', '1784'),
(202, 'Santa Elena', 'SHN', '290'),
(203, 'Santa LucÃ­a', 'LCA', '1758'),
(204, 'Santo TomÃ© y PrÃ­ncipe', 'STP', '239'),
(205, 'Senegal', 'SEN', '221'),
(206, 'Serbia', 'SRB', '381'),
(207, 'Seychelles', 'SYC', '248'),
(208, 'Sierra Leona', 'SLE', '232'),
(209, 'Singapur', 'SGP', '65'),
(210, 'Siria', 'SYR', '963'),
(211, 'Somalia', 'SOM', '252'),
(212, 'Sri lanka', 'LKA', '94'),
(213, 'SudÃ¡frica', 'ZAF', '27'),
(214, 'SudÃ¡n', 'SDN', '249'),
(215, 'Suecia', 'SWE', '46'),
(216, 'Suiza', 'CHE', '41'),
(217, 'SurinÃ¡m', 'SUR', '597'),
(218, 'Svalbard y Jan Mayen', 'SJM', ''),
(219, 'Swazilandia', 'SWZ', '268'),
(220, 'TadjikistÃ¡n', 'TJK', '992'),
(221, 'Tailandia', 'THA', '66'),
(222, 'TaiwÃ¡n', 'TWN', '886'),
(223, 'Tanzania', 'TZA', '255'),
(224, 'Territorio BritÃ¡nico del OcÃ©ano Ãndico', 'IOT', ''),
(225, 'Territorios Australes y AntÃ¡rticas Franceses', 'ATF', ''),
(226, 'Timor Oriental', 'TLS', '670'),
(227, 'Togo', 'TGO', '228'),
(228, 'Tokelau', 'TKL', '690'),
(229, 'Tonga', 'TON', '676'),
(230, 'Trinidad y Tobago', 'TTO', '1868'),
(231, 'Tunez', 'TUN', '216'),
(232, 'TurkmenistÃ¡n', 'TKM', '993'),
(233, 'TurquÃ­a', 'TUR', '90'),
(234, 'Tuvalu', 'TUV', '688'),
(235, 'Ucrania', 'UKR', '380'),
(236, 'Uganda', 'UGA', '256'),
(237, 'Uruguay', 'URY', '598'),
(238, 'UzbekistÃ¡n', 'UZB', '998'),
(239, 'Vanuatu', 'VUT', '678'),
(240, 'Venezuela', 'VEN', '58'),
(241, 'Vietnam', 'VNM', '84'),
(242, 'Wallis y Futuna', 'WLF', '681'),
(243, 'Yemen', 'YEM', '967'),
(244, 'Yibuti', 'DJI', '253'),
(245, 'Zambia', 'ZMB', '260'),
(246, 'Zimbabue', 'ZWE', '263');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients_sectors`
--

CREATE TABLE `clients_sectors` (
  `sector_id` int(11) NOT NULL,
  `sector_name` varchar(255) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clients_sectors`
--

INSERT INTO `clients_sectors` (`sector_id`, `sector_name`, `status_id`) VALUES
(0, 'Sector not registered', 1),
(1, 'Privado', 1),
(2, 'Publico', 1),
(3, 'Mixto', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients_services`
--

CREATE TABLE `clients_services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clients_services`
--

INSERT INTO `clients_services` (`service_id`, `service_name`, `status_id`) VALUES
(0, 'Service not registered', 1),
(1, 'Financiero', 1),
(2, 'Manufactura', 1),
(3, 'Energético', 1),
(4, 'Salud', 1),
(5, 'Seguros', 1),
(6, 'Agroalimentario', 1),
(7, 'Telecomunicaciones', 1),
(8, 'Fintech', 1),
(9, 'Construcción', 1),
(10, 'Automotriz', 1),
(11, 'Servicios', 1),
(12, 'Agrícola', 1),
(13, 'Distribución', 1),
(14, 'Logística', 1),
(15, 'Almacenaje', 1),
(16, 'ONG/Fundaciones', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_companies`
--

CREATE TABLE `control_companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(30) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `control_companies`
--

INSERT INTO `control_companies` (`company_id`, `company_name`, `status_id`) VALUES
(1, 'Marquez, Perdomo y Asociados', 1),
(2, 'MPF', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_currencies`
--

CREATE TABLE `control_currencies` (
  `currency_id` int(11) NOT NULL,
  `currency_name` varchar(25) NOT NULL,
  `currency_symbol` text NOT NULL,
  `currency_order` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `control_currencies`
--

INSERT INTO `control_currencies` (`currency_id`, `currency_name`, `currency_symbol`, `currency_order`, `status_id`) VALUES
(1, 'Bolívar', 'Bs', 2, 1),
(2, 'Dólar', '$', 1, 1),
(3, 'Euro', '€', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_encrypts`
--

CREATE TABLE `control_encrypts` (
  `encrypt_id` int(11) NOT NULL,
  `encrypt_key` mediumtext NOT NULL,
  `encrypt_iv` mediumtext NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `control_encrypts`
--

INSERT INTO `control_encrypts` (`encrypt_id`, `encrypt_key`, `encrypt_iv`, `status_id`) VALUES
(1, '0123456789abcdef0123456789abcdef', 'abcdef9876543210abcdef9876543210', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_errors`
--

CREATE TABLE `control_errors` (
  `error_id` int(11) NOT NULL,
  `type_message_id` int(11) NOT NULL,
  `type_object_id` int(11) NOT NULL,
  `affected_object` varchar(50) NOT NULL,
  `error_message` text NOT NULL,
  `error_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `control_errors`
--

INSERT INTO `control_errors` (`error_id`, `type_message_id`, `type_object_id`, `affected_object`, `error_message`, `error_date`) VALUES
(1, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-05 09:39:56'),
(2, 1, 1, 'sp_InsertLog', 'Ha ocurrido un error en el registro de datos en la bitacora: (23000) Column \'Valor_anterior\' cannot be null', '2023-04-05 09:47:30'),
(3, 1, 1, 'sp_Login', 'Se ha producido un error en el inicio de sesión: (HY000) Syntax error in JSON text in argument 1 to function \'json_extract\' at position 13', '2023-04-05 09:49:46'),
(4, 1, 1, 'sp_Login', 'Se ha producido un error en el inicio de sesión: (HY000) Syntax error in JSON text in argument 1 to function \'json_extract\' at position 13', '2023-04-05 09:56:21'),
(5, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-05 10:04:19'),
(6, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-05 10:09:50'),
(7, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-05 13:56:49'),
(8, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0016: La selección de tabla está vacia ()\"}', '2023-04-10 11:00:10'),
(9, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-10 11:03:11'),
(10, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0016: La selección de tabla está vacia ()\"}', '2023-04-10 11:03:45'),
(11, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0017: La selección de tabla no coincide con ninguna tabla (users2)\"}', '2023-04-10 11:30:14'),
(12, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0016: La selección de tabla está vacia ()\"}', '2023-04-10 11:30:21'),
(13, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0017: La selección de tabla no coincide con ninguna tabla (users2)\"}', '2023-04-10 11:35:01'),
(14, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0017: La selección de tabla no coincide con ninguna tabla (users2)\"}', '2023-04-10 11:35:08'),
(15, 1, 1, 'sp_QueryPagination', '{\"response\":false,\"message\":\"Error 0016: La selección de tabla está vacia ()\"}', '2023-04-10 11:35:33'),
(16, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-14 11:04:39'),
(17, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-14 13:34:24'),
(18, 1, 1, 'sp_NewUser', 'Se ha producido un error en la consulta: (21S01) El número de columnas no corresponde al número en la línea 1', '2023-04-16 00:01:33'),
(19, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-04-16 00:12:34'),
(20, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0011: El ID_CODE(999999) no existe o tiene el acceso denegado\"}', '2023-04-16 00:21:34'),
(21, 1, 1, 'sp_NewContactUser', '{\"response\":false,\"message\":\"Error 0019: Este usuario no existe (999999)\"}', '2023-04-16 13:07:25'),
(22, 1, 1, 'sp_NewUser', 'Se ha producido un error en la consulta: (22007) Truncated incorrect datetime value: \'20230416132394\'', '2023-04-16 13:23:04'),
(23, 1, 1, 'sp_NewUser', 'Se ha producido un error en la consulta: (23000) Column \'Id_usuario\' cannot be null', '2023-04-16 13:29:50'),
(24, 1, 1, 'sp_NewContactUser', 'Se ha producido un error en la consulta: (23000) Column \'Id_usuario\' cannot be null', '2023-04-16 13:42:16'),
(25, 1, 1, 'sp_NewUser', 'Se ha producido un error en la consulta: (23000) Cannot add or update a child row: a foreign key constraint fails (`carent-nueva`.`tbl_usuarios`, CONSTRAINT `FK_division_usuario` FOREIGN KEY (`Id_jerarquia_division`) REFERENCES `tbl_usuarios_jerarquia_division` (`Id`))', '2023-04-16 14:42:33'),
(26, 1, 1, 'sp_NewUser', 'Se ha producido un error en la consulta: (23000) Cannot add or update a child row: a foreign key constraint fails (`carent-nueva`.`tbl_usuarios`, CONSTRAINT `FK_division_usuario` FOREIGN KEY (`Id_jerarquia_division`) REFERENCES `tbl_usuarios_jerarquia_division` (`Id`))', '2023-04-16 14:46:53'),
(27, 1, 1, 'sp_NewUser', 'Se ha producido un error en la consulta: (22007) Truncated incorrect datetime value: \'20230416144892\'', '2023-04-16 14:48:02'),
(28, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 15:23:42'),
(29, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 15:25:33'),
(30, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 15:26:54'),
(31, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 15:28:50'),
(32, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 15:58:02'),
(33, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:00:01'),
(34, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:00:16'),
(35, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:00:30'),
(36, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:00:55'),
(37, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:03:02'),
(38, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:06:55'),
(39, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:06:57'),
(40, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:07:29'),
(41, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:09:55'),
(42, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:10:37'),
(43, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:16:48'),
(44, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:19:17'),
(45, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-04-16 16:19:22'),
(46, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-05-01 10:10:22'),
(47, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-05-01 10:14:29'),
(48, 1, 1, 'sp_NewContactUser', 'Se ha producido un error en la consulta: (42S22) Unknown column \'UT.Abreviatura\' in \'where clause\'', '2023-05-01 10:15:16'),
(49, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-05-01 10:15:30'),
(50, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (1201)\"}', '2023-05-01 10:15:42'),
(51, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (1201)\"}', '2023-05-01 10:15:52'),
(52, 1, 1, 'sp_Login', '{\"response\":false,\"message\":\"Error 0013: La contraseña no coincide con la registrada en el sistema\"}', '2023-05-03 21:20:55'),
(53, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-05-03 21:48:29'),
(54, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-05-04 13:47:20'),
(55, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-05-09 14:43:55'),
(56, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-05-09 14:44:19'),
(57, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (0001)\"}', '2023-05-10 11:09:49'),
(58, 1, 1, 'sp_NewContactUser', 'Se ha producido un error en la consulta: (42S22) Unknown column \'US.Id\' in \'field list\'', '2023-05-10 11:22:44'),
(59, 1, 1, 'sp_NewUser', 'Se ha producido un error en la consulta: (22007) Truncated incorrect datetime value: \'20230510112891\'', '2023-05-10 11:28:01'),
(60, 1, 1, 'sp_NewUser', '{\"response\":false,\"message\":\"Error 0018: Este usuario ya existe (909090)\"}', '2023-05-10 11:46:36'),
(61, 1, 1, 'sp_NewContactUser', '{\"response\":false,\"message\":\"Error 0021: Esta cedula ya está registrada (22667607)\"}', '2023-05-10 11:46:51'),
(62, 1, 1, 'sp_NewUser', 'Se ha producido un error en la consulta: (22007) Truncated incorrect datetime value: \'20230510115496\'', '2023-05-10 11:54:06'),
(63, 1, 1, 'sp_NewContactUser', '{\"response\":false,\"message\":\"Error 0021: Esta cedula ya está registrada (22667607)\"}', '2023-05-10 12:00:27'),
(64, 1, 1, 'sp_UpdateContactUser', '{\"response\":false,\"message\":\"Error 0021: Esta cedula ya está registrada (22667607)\"}', '2023-05-10 12:03:43'),
(65, 1, 1, 'sp_UpdateContactUser', '{\"response\":false,\"message\":\"Error 0021: Esta cedula ya está registrada (22667607)\"}', '2023-05-10 12:05:41'),
(66, 1, 1, 'sp_NewClient', 'Se ha producido un error en la consulta: (42S22) La columna \'p_Codigo\' en field list es desconocida', '2023-05-10 15:13:29'),
(67, 1, 1, 'sp_UpdateContactUser', '{\"response\":false,\"message\":\"Error 0021: Esta cedula ya está registrada (17671373)\"}', '2023-05-10 15:28:51'),
(68, 1, 1, 'sp_UpdateUser', 'Se ha producido un error en la consulta: (22007) Truncated incorrect datetime value: \'20230510153293\'', '2023-05-10 15:32:03'),
(69, 1, 1, 'sp_UpdateContactUser', '{\"response\":false,\"message\":\"Error 0021: Esta cedula ya está registrada (17671373)\"}', '2023-05-10 15:32:32'),
(70, 1, 1, 'sp_UpdateContactUser', '{\"response\":false,\"message\":\"Error 0021: Esta cedula ya está registrada (17671373)\"}', '2023-05-10 15:32:41'),
(71, 1, 1, 'sp_UpdateContactUser', '{\"response\":false,\"message\":\"Error 0021: Esta cedula ya está registrada (22667607)\"}', '2023-05-10 15:35:13'),
(72, 1, 1, 'sp_NewClient', '{\"response\":false,\"message\":\"Error 0022: Este socio no existe (1)\"}', '2023-05-10 16:05:27'),
(73, 1, 1, 'sp_NewClient', '{\"response\":false,\"message\":\"Error 0022: Este socio no existe (1)\"}', '2023-05-10 16:06:24'),
(74, 1, 1, 'sp_NewClient', '{\"response\":false,\"message\":\"Error 0022: Este socio no existe (1)\"}', '2023-05-10 16:12:32'),
(75, 1, 1, 'sp_NewClient', 'Se ha producido un error en la consulta: (23000) Column \'Nit\' cannot be null', '2023-05-10 16:20:53'),
(76, 1, 1, 'sp_NewClient', 'Se ha producido un error en la consulta: (23000) Column \'Nit\' cannot be null', '2023-05-10 16:21:39'),
(77, 1, 1, 'sp_NewClient', 'Se ha producido un error en la consulta: (23000) Column \'Nit\' cannot be null', '2023-05-10 16:21:41'),
(78, 1, 1, 'sp_NewClient', 'Se ha producido un error en la consulta: (23000) Column \'Nit\' cannot be null', '2023-05-10 16:22:23'),
(79, 1, 1, 'sp_NewClient', 'Se ha producido un error en la consulta: (23000) Column \'Nit\' cannot be null', '2023-05-10 16:23:42'),
(80, 1, 1, 'sp_NewClient', 'Se ha producido un error en la consulta: (23000) Column \'Nit\' cannot be null', '2023-05-10 16:23:46'),
(81, 1, 1, 'sp_NewClient', 'Se ha producido un error en la consulta: (23000) Column \'Nit\' cannot be null', '2023-05-10 16:25:05'),
(82, 1, 1, 'sp_NewClient', 'Se ha producido un error en la consulta: (23000) Column \'Nit\' cannot be null', '2023-05-10 16:28:40'),
(83, 1, 1, 'sp_NewClient', 'Se ha producido un error en la consulta: (23000) Column \'Nit\' cannot be null', '2023-05-10 16:31:03'),
(84, 1, 1, 'sp_UpdateClient', '{\"response\":false,\"message\":\"Error 0024: Este RIF ya esta registrado (J314087754)\"}', '2023-05-13 15:31:10'),
(85, 1, 1, 'sp_test', '{\"response\":false,\"message\":\"prueba de errores\"}', '2023-05-16 15:29:27'),
(86, 1, 1, 'sp_login', 'Se ha producido un error en el inicio de sesión: (HY000) Ilegal mezcla de collations (utf8mb4_general_ci,IMPLICIT) y (utf8mb4_unicode_ci,IMPLICIT) para operación \'<>\'', '2023-05-16 16:10:06'),
(87, 1, 1, 'sp_login', 'Se ha producido un error en el inicio de sesión: (HY000) Ilegal mezcla de collations (utf8mb4_general_ci,IMPLICIT) y (utf8mb4_unicode_ci,IMPLICIT) para operación \'<>\'', '2023-05-16 16:13:00'),
(88, 1, 1, 'sp_login', 'Se ha producido un error en el inicio de sesión: (HY000) Ilegal mezcla de collations (utf8mb4_general_ci,IMPLICIT) y (utf8mb4_unicode_ci,IMPLICIT) para operación \'<>\'', '2023-05-16 16:26:04'),
(89, 1, 1, 'sp_login', 'Se ha producido un error en el inicio de sesión: (HY000) Ilegal mezcla de collations (utf8mb4_general_ci,IMPLICIT) y (utf8mb4_unicode_ci,IMPLICIT) para operación \'<>\'', '2023-05-16 16:27:24'),
(90, 1, 1, 'sp_login', '{\"response\":false,\"message\":\"Error 0009: Password incorrect, insert again\"}', '2023-05-16 16:29:24'),
(91, 1, 1, 'sp_login', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed\"}', '2023-05-16 16:29:47'),
(92, 1, 1, 'sp_query_pagination', '{\"response\":false,\"message\":\"Error 0013: Table target does not match any table in the database (a)\"}', '2023-05-16 16:55:30'),
(93, 1, 1, 'sp_new_clients', 'Se ha producido un error en la consulta: (42S22) La columna \'US.Id\' en field list es desconocida', '2023-05-16 17:47:13'),
(94, 1, 1, 'sp_new_clients', 'Se ha producido un error en la consulta: (42S22) La columna \'p_servicio_id\' en field list es desconocida', '2023-05-16 17:49:12'),
(95, 1, 1, 'sp_new_clients', 'Se ha producido un error en la consulta: (23000) La columna \'client_code\' no puede ser nula', '2023-05-16 17:50:33'),
(96, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed\"}', '2023-05-16 17:57:31'),
(97, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed\"}', '2023-05-16 17:58:01'),
(98, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed\"}', '2023-05-16 18:00:45'),
(99, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed\"}', '2023-05-16 18:01:18'),
(100, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed (Error con el código 23000:Column \'error_message\' cannot be null)\"}', '2023-05-16 18:03:04'),
(101, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed (Error con el código 23000:Column \'error_message\' cannot be null)\"}', '2023-05-16 18:04:12'),
(102, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed (Error con el código 23000:Column \'error_message\' cannot be null)\"}', '2023-05-16 18:04:16'),
(103, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed (Error con el código 23000:Column \'error_message\' cannot be null)\"}', '2023-05-16 18:04:20'),
(104, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed (Error con el código 23000:Column \'error_message\' cannot be null)\"}', '2023-05-16 18:04:29'),
(105, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed (Error con el código 23000:Column \'error_message\' cannot be null)\"}', '2023-05-16 18:04:33'),
(106, 1, 1, 'sp_new_clients', '{\"response\":false,\"message\":\"Error 0011: Insert log has failed (Error con el código 23000:Column \'error_message\' cannot be null)\"}', '2023-05-16 18:04:37'),
(107, 1, 1, 'sp_update_clients', 'Se ha producido un error en la consulta: (42S22) Unknown column \'p_rid\' in \'field list\'', '2023-05-16 19:10:40'),
(108, 1, 1, 'sp_update_clients', 'Se ha producido un error en la consulta: (42000) PROCEDURE carent-nueva.sp_InsertLog does not exist', '2023-05-16 19:12:38'),
(109, 1, 1, 'sp_new_users', '{\"response\":false,\"message\":\"Error 0016: This user is already registered (0001)\"}', '2023-05-17 01:16:27'),
(110, 1, 1, 'sp_new_users', 'Se ha producido un error en la consulta: (22007) Truncated incorrect DOUBLE value: \'127.0.0.1\'', '2023-05-17 01:16:55'),
(111, 1, 1, 'sp_new_users', 'Se ha producido un error en la consulta: (22007) Truncated incorrect DOUBLE value: \'127.0.0.1\'', '2023-05-17 01:17:28'),
(112, 1, 1, 'sp_new_users', 'Se ha producido un error en la consulta: (22007) Truncated incorrect datetime value: \'20230517012292\'', '2023-05-17 01:22:02'),
(113, 1, 1, 'sp_new_users', 'Se ha producido un error en la consulta: (42000) PROCEDURE carent-nueva.sp_InsertLog does not exist', '2023-05-17 01:22:25'),
(114, 1, 1, 'sp_new_users', '{\"response\":false,\"message\":\"Error 0016: This user is already registered (888888)\"}', '2023-05-17 01:24:40'),
(115, 1, 1, 'sp_new_contact_users', '{\"response\":false,\"message\":\"Error 0017: This identity is already registered (22667607)\"}', '2023-05-17 01:34:42'),
(116, 1, 1, 'sp_new_contact_users', '{\"response\":false,\"message\":\"Error 0017: This identity is already registered (22667607)\"}', '2023-05-17 01:35:59'),
(117, 1, 1, 'sp_new_users', '{\"response\":false,\"message\":\"Error 0016: This user is already registered (0001)\"}', '2023-05-17 01:36:18'),
(118, 1, 1, 'sp_new_users', 'Se ha producido un error en la consulta: (22007) Truncated incorrect datetime value: \'20230517024798\'', '2023-05-17 02:47:08'),
(119, 1, 1, 'sp_update_users', 'Se ha producido un error en la consulta: (42S22) Unknown column \'TU.Id\' in \'where clause\'', '2023-05-17 02:49:57'),
(120, 1, 1, 'sp_update_users', 'Se ha producido un error en la consulta: (42S22) Unknown column \'users.user_id\' in \'where clause\'', '2023-05-17 02:52:29'),
(121, 1, 1, 'sp_update_users', 'Se ha producido un error en la consulta: (42S22) Unknown column \'users.user_id\' in \'where clause\'', '2023-05-17 02:52:33'),
(122, 1, 1, 'sp_update_users', 'Se ha producido un error en la consulta: (42S22) Unknown column \'us.partish_id\' in \'field list\'', '2023-05-17 02:53:53'),
(123, 1, 1, 'sp_update_users', 'Se ha producido un error en la consulta: (42S22) Unknown column \'us.partish_id\' in \'field list\'', '2023-05-17 02:53:57'),
(124, 1, 1, 'sp_update_contact_users', 'Se ha producido un error en la consulta: (42S22) Unknown column \'UT.Id\' in \'field list\'', '2023-05-17 02:55:31'),
(125, 1, 1, 'sp_update_contact_users', 'Se ha producido un error en la consulta: (42S22) Unknown column \'UT.Id\' in \'field list\'', '2023-05-17 02:55:35'),
(126, 1, 1, 'sp_update_contact_users', 'Se ha producido un error en la consulta: (42S22) Unknown column \'US.Id\' in \'field list\'', '2023-05-17 02:56:38'),
(127, 1, 1, 'sp_update_contact_users', 'Se ha producido un error en la consulta: (42S22) Unknown column \'US.Id\' in \'field list\'', '2023-05-17 02:56:43'),
(128, 1, 1, 'sp_update_contact_users', 'Se ha producido un error en la consulta: (22007) Incorrect integer value: \'v\' for column `carent-nueva`.`users_identity`.`identity_type_id` at row 1', '2023-05-17 02:57:52'),
(129, 1, 1, 'sp_update_contact_users', 'Se ha producido un error en la consulta: (22007) Incorrect integer value: \'v\' for column `carent-nueva`.`users_identity`.`identity_type_id` at row 1', '2023-05-17 02:57:56'),
(130, 1, 1, 'sp_login', '{\"response\":false,\"message\":\"Error 0009: Password incorrect, insert again\"}', '2023-06-05 11:23:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_errors_type_messages`
--

CREATE TABLE `control_errors_type_messages` (
  `type_message_id` int(11) NOT NULL,
  `message_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `control_errors_type_messages`
--

INSERT INTO `control_errors_type_messages` (`type_message_id`, `message_description`) VALUES
(1, 'Advertencia'),
(2, 'Error');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_errors_type_object`
--

CREATE TABLE `control_errors_type_object` (
  `type_object_id` int(11) NOT NULL,
  `object_name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `control_errors_type_object`
--

INSERT INTO `control_errors_type_object` (`type_object_id`, `object_name`) VALUES
(1, 'Procedimiento Almacenado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_logs`
--

CREATE TABLE `control_logs` (
  `log_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL COMMENT 'Tipo de acción que se realizo usando la nomenclatura CRUD',
  `log_description` text NOT NULL,
  `user_responsible_ip` varchar(39) NOT NULL COMMENT 'IPV4 O IPV6 del responsable de la acción realizada.',
  `user_responsible_id` int(11) NOT NULL,
  `affected_table` text NOT NULL,
  `query_sql` text DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `register_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `control_logs`
--

INSERT INTO `control_logs` (`log_id`, `action_id`, `log_description`, `user_responsible_ip`, `user_responsible_id`, `affected_table`, `query_sql`, `old_value`, `new_value`, `register_date`) VALUES
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
(19, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-10 11:02:34 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-05 14:00:02\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-10 11:02:34\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-10 11:02:34'),
(20, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-14 09:44:49 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-10 11:02:34\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-14 09:44:49\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-14 09:44:49'),
(21, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-14 11:04:45 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-14 09:44:49\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-14 11:04:45\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-14 11:04:45'),
(22, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-14 13:34:29 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-14 11:04:45\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-14 13:34:29\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-14 13:34:29'),
(23, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-14 13:53:14 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-14 13:34:29\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-14 13:53:14\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-14 13:53:14'),
(24, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-14 13:55:41 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-14 13:53:14\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-14 13:55:41\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-14 13:55:41'),
(25, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-14 19:09:17 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-14 13:55:41\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-14 19:09:17\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-14 19:09:17'),
(26, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-14 23:25:59 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-14 19:09:17\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-14 23:25:59\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-14 23:25:59'),
(27, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-15 08:44:55 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-14 23:25:59\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-15 08:44:55\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-15 08:44:55'),
(28, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-15 09:03:20 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-15 08:44:55\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-15 09:03:20\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-15 09:03:20'),
(29, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`)\r\nVALUES(999999,AES_ENCRYPT(12345678,0123456789abcdef0123456789abcdef),2023-04-16,Pepe,ElGrillo,Pillo,Vanillo,1999-11-11,6,2,50,2023-04-16 00:07:29,NULL,NULL,1)', NULL, '{\"ultima_ip\":\"127.0.0.1\"}', '2023-04-16 00:07:29'),
(30, 2, 'login', '127.0.0.1', 249, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:08:31 WHERE u.Codigo = 999999;', NULL, '{\"fecha_ultimo_login\": \"2023-04-16 00:08:31\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:08:31'),
(31, 2, 'login', '127.0.0.1', 249, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:08:54 WHERE u.Codigo = 999999;', '{\"fecha_ultimo_login\": \"2023-04-16 00:08:31\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 00:08:54\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:08:54'),
(32, 2, 'login', '127.0.0.1', 249, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:09:39 WHERE u.Codigo = 999999;', '{\"fecha_ultimo_login\": \"2023-04-16 00:08:54\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 00:09:39\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:09:39'),
(33, 2, 'login', '127.0.0.1', 249, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:11:48 WHERE u.Codigo = 999999;', '{\"fecha_ultimo_login\": \"2023-04-16 00:09:39\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 00:11:48\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:11:48'),
(34, 2, 'login', '127.0.0.1', 249, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:14:49 WHERE u.Codigo = 999999;', '{\"fecha_ultimo_login\": \"2023-04-16 00:11:48\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 00:14:49\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:14:49'),
(35, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:14:59 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-15 09:03:20\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 00:14:59\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:14:59'),
(36, 2, 'login', '127.0.0.1', 247, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:16:02 WHERE u.Codigo = 11622;', NULL, '{\"fecha_ultimo_login\": \"2023-04-16 00:16:02\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:16:02'),
(37, 2, 'login', '127.0.0.1', 249, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:19:08 WHERE u.Codigo = 999999;', '{\"fecha_ultimo_login\": \"2023-04-16 00:14:49\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 00:19:08\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:19:08'),
(38, 2, 'login', '127.0.0.1', 249, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:19:52 WHERE u.Codigo = 11624;', '{\"fecha_ultimo_login\": \"2023-04-16 00:19:08\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 00:19:52\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:19:52'),
(39, 2, 'login', '127.0.0.1', 249, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:22:00 WHERE u.Codigo = 11624;', '{\"fecha_ultimo_login\": \"2023-04-16 00:19:52\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 00:22:00\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:22:00'),
(40, 2, 'login', '127.0.0.1', 249, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:22:07 WHERE u.Codigo = 11624;', '{\"fecha_ultimo_login\": \"2023-04-16 00:22:00\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 00:22:07\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:22:07'),
(41, 2, 'login', '127.0.0.1', 249, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 00:22:55 WHERE u.Codigo = 11624;', '{\"fecha_ultimo_login\": \"2023-04-16 00:22:07\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 00:22:55\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 00:22:55'),
(42, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 09:15:34 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-16 00:14:59\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 09:15:34\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 09:15:34'),
(43, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 11:46:30 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-16 09:15:34\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 11:46:30\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 11:46:30'),
(44, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 12:23:46 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-16 11:46:30\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 12:23:46\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 12:23:46'),
(45, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', NULL, '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-04-16 13:29:49'),
(46, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', NULL, '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-04-16 13:42:16'),
(47, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(120154,AES_ENCRYPT(p_Cedula,,@Key,),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-04-16 13:46:52'),
(48, 2, 'login', '127.0.0.1', 252, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 13:49:13 WHERE u.Codigo = 120154;', NULL, '{\"fecha_ultimo_login\": \"2023-04-16 13:49:13\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 13:49:13'),
(49, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 13:49:28 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-16 12:23:46\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 13:49:28\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 13:49:28'),
(50, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 14:16:43 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-16 13:49:28\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 14:16:43\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 14:16:43'),
(51, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(70663,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-04-16 14:51:13'),
(52, 2, 'login', '127.0.0.1', 255, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 14:51:36 WHERE u.Codigo = 70663;', NULL, '{\"fecha_ultimo_login\": \"2023-04-16 14:51:36\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 14:51:36'),
(53, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-16 14:51:52 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-16 14:16:43\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-16 14:51:52\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-16 14:51:52'),
(54, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(999998,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-04-16 16:21:27'),
(55, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-29 18:20:29 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-16 14:51:52\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-29 18:20:29\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-29 18:20:29'),
(56, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-30 08:56:23 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-29 18:20:29\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-30 08:56:23\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-30 08:56:23'),
(57, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-04-30 11:09:03 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-30 08:56:23\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-04-30 11:09:03\",\"ultima_ip\": \"127.0.0.1\"}', '2023-04-30 11:09:03'),
(58, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-01 10:06:33 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-04-30 11:09:03\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-01 10:06:33\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-01 10:06:33'),
(59, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(1201,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-01 10:15:16'),
(60, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(1201,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-01 10:19:19'),
(61, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-01 20:24:33 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-01 10:06:33\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-01 20:24:33\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-01 20:24:33'),
(62, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-03 21:21:06 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-01 20:24:33\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-03 21:21:06\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-03 21:21:06'),
(63, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(123456,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-04 13:47:31'),
(64, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(654321,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-04 13:51:18'),
(65, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=11622,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-04 13:53:46'),
(66, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=11622,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-04 13:54:33'),
(67, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=11622,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-04 13:54:53'),
(68, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(989898,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-04 14:27:13'),
(69, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=989898,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-04 14:51:30'),
(70, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-05 10:46:23 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-03 21:21:06\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-05 10:46:23\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-05 10:46:23'),
(71, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=123456,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-05 10:50:30'),
(72, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=123456,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-05 11:00:20'),
(73, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-06 21:04:42 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-05 10:46:23\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-06 21:04:42\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-06 21:04:42'),
(74, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-07 11:51:25 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-06 21:04:42\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-07 11:51:25\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-07 11:51:25'),
(75, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-07 19:34:03 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-07 11:51:25\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-07 19:34:03\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-07 19:34:03'),
(76, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-09 09:14:36 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-07 19:34:03\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-09 09:14:36\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-09 09:14:36'),
(77, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-10 10:03:57 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-09 09:14:36\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-10 10:03:57\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-10 10:03:57'),
(78, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(909090,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 11:22:40'),
(79, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(909090,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 11:39:34'),
(80, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(909090,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 11:45:42'),
(81, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(919191,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 11:46:51'),
(82, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(919191,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 11:51:33'),
(83, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(929292,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 11:55:21'),
(84, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(949494,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 12:00:26'),
(85, 1, 'createUser', '127.0.0.1', 1, 'tbl_usuarios', 'INSERT INTO `tbl_usuarios`(`Codigo`, `Clave`, `Fecha_cambio_clave`, `Primer_nombre`, `Segundo_nombre`, `Primer_apellido`, `Segundo_apellido`, `Fecha_nacimiento`, `Id_jerarquia_cargo`, `Id_jerarquia_division`, `Id_direccion_parroquia`, `Fecha_ingreso`, `Fecha_egreso`, `Fecha_login`, `Id_estatus`) VALUES(949494,AES_ENCRYPT(p_Cedula,@Key),@FechaCambio,p_Nombre1,p_Nombre2,p_Apellido1,p_Apellido2,p_FechaNacimiento,p_IdCargo,p_IdDivision,p_IdParroquia,p_FechaIngreso,NULL,NULL,1)', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 12:00:39'),
(86, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=949494,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-10 12:03:43'),
(87, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=909090,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-10 12:05:41'),
(88, 1, 'createClient', '127.0.0.1', 1, 'tbl_clientes', 'INSERT INTO `tbl_clientes`(`Id_usuario_socio`, `Codigo_cliente`, `Rif`, `Nit`, `Razon_social`, `Id_pais`, `Direccion`, `Telefono_fiscal`, `Pagina_web`, `Email_fiscal`, `Id_cliente_sector`, `Id_cliente_servicio`, `Id_estatus`) VALUES (p_IdSocio,2299,p_Rif,p_Nit,p_RazonSocial,p_IdPais,p_Address,p_TelefonoFiscal,p_PaginaWeb,p_EmailFiscal,p_IdSectorAsociado,p_IdServicioAsociado,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 15:13:29'),
(89, 1, 'createClient', '127.0.0.1', 1, 'tbl_clientes', 'INSERT INTO `tbl_clientes`(`Id_usuario_socio`, `Codigo_cliente`, `Rif`, `Nit`, `Razon_social`, `Id_pais`, `Direccion`, `Telefono_fiscal`, `Pagina_web`, `Email_fiscal`, `Id_cliente_sector`, `Id_cliente_servicio`, `Id_estatus`) VALUES (p_IdSocio,2300,p_Rif,p_Nit,p_RazonSocial,p_IdPais,p_Address,p_TelefonoFiscal,p_PaginaWeb,p_EmailFiscal,p_IdSectorAsociado,p_IdServicioAsociado,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 15:18:05'),
(90, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=0001,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-10 15:28:51'),
(91, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=0001,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-10 15:32:32'),
(92, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=0001,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-10 15:32:41'),
(93, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=0001,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-10 15:34:47'),
(94, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=0001,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-10 15:35:13'),
(95, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=0001,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-10 15:35:36'),
(96, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=0001,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-10 15:45:37'),
(97, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=0001,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-10 16:04:13'),
(98, 1, 'createClient', '127.0.0.1', 1, 'tbl_clientes', 'INSERT INTO `tbl_clientes`(`Id_usuario_socio`, `Codigo_cliente`, `Rif`, `Nit`, `Razon_social`, `Id_pais`, `Direccion`, `Telefono_fiscal`, `Pagina_web`, `Email_fiscal`, `Id_cliente_sector`, `Id_cliente_servicio`, `Id_estatus`) VALUES (p_IdSocio,2301,p_Rif,p_Nit,p_RazonSocial,p_IdPais,p_Address,p_TelefonoFiscal,p_PaginaWeb,p_EmailFiscal,p_IdSectorAsociado,p_IdServicioAsociado,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-10 16:34:01'),
(99, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-11 15:59:59 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-10 10:03:57\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-11 15:59:59\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-11 15:59:59'),
(100, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-13 09:40:08 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-11 15:59:59\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-13 09:40:08\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-13 09:40:08'),
(101, 2, 'updateUser', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE `tbl_usuarios` SET `Codigo`=0001,`Fecha_cambio_clave`=@FechaCambio,`Primer_nombre`=p_Nombre1,`Segundo_nombre`=p_Nombr2,`Primer_apellido`=p_Apellido1,`Segundo_apellido`=p_Apellido2,`Fecha_nacimiento`=p_FechaNacimiento,`Id_jerarquia_cargo`=p_IdCargo,`Id_jerarquia_division`=p_IdDivision,`Id_direccion_parroquia`=p_IdParroquia,`Fecha_ingreso`=p_FechaIngreso,`Fecha_egreso`=p_FechaEgreso,`Id_estatus`=p_IdStatus WHERE `Id` = p_IdUpdateUser', NULL, NULL, '2023-05-13 12:06:33'),
(102, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', NULL, '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 14:39:06'),
(103, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 14:47:00'),
(104, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 14:48:48'),
(105, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 14:53:46'),
(106, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 14:57:53');
INSERT INTO `control_logs` (`log_id`, `action_id`, `log_description`, `user_responsible_ip`, `user_responsible_id`, `affected_table`, `query_sql`, `old_value`, `new_value`, `register_date`) VALUES
(107, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:04:06'),
(108, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-13 15:07:32 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-13 09:40:08\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-13 15:07:32\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-13 15:07:32'),
(109, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:08:09'),
(110, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:14:24'),
(111, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:16:17'),
(112, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:17:04'),
(113, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:22:28'),
(114, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:25:33'),
(115, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:26:21'),
(116, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:26:58'),
(117, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:30:46'),
(118, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=146,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 14;', '{\"socio\":\"146\",\r\n                        \"rif\":\"J314087754\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"A.C. CONSULTORES UCAB\",\r\n                        \"pais\":\"240\",\r\n                        \"direccion\":\"Universidad Católica Andrés Bello Centro Loyola, Piso 2, Caracas. Dtto. Capital\",\r\n                        \"telefono\":\"+5822122356047\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"acconsultores@com.ve\",\r\n                        \"sector\":\"2\",\r\n                        \"servicio\":\"2\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"146\",\r\n                        \"rif\":\"J314087754\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"A.C. CONSULTORES UCAB\",\r\n                        \"pais\":\"240\",\r\n                        \"direccion\":\"Universidad Católica Andrés Bello Centro Loyola, Piso 2, Caracas. Dtto. Capital\",\r\n                        \"telefono\":\"+5822122356047\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"acconsultores@com.ve\",\r\n                        \"sector\":\"2\",\r\n                        \"servicio\":\"2\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:38:33'),
(119, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=146,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 14;', '{\"socio\":\"146\",\r\n                        \"rif\":\"J314087754\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"A.C. CONSULTORES UCAB\",\r\n                        \"pais\":\"240\",\r\n                        \"direccion\":\"Universidad Católica Andrés Bello Centro Loyola, Piso 2, Caracas. Dtto. Capital\",\r\n                        \"telefono\":\"+5822122356047\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"acconsultores@com.ve\",\r\n                        \"sector\":\"2\",\r\n                        \"servicio\":\"2\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"146\",\r\n                        \"rif\":\"J314087753\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"A.C. CONSULTORES UCAB\",\r\n                        \"pais\":\"240\",\r\n                        \"direccion\":\"Universidad Católica Andrés Bello Centro Loyola, Piso 2, Caracas. Dtto. Capital\",\r\n                        \"telefono\":\"+5822122356047\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"acconsultores@com.ve\",\r\n                        \"sector\":\"2\",\r\n                        \"servicio\":\"2\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:39:36'),
(120, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=146,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 14;', '{\"socio\":\"146\",\r\n                        \"rif\":\"J314087753\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"A.C. CONSULTORES UCAB\",\r\n                        \"pais\":\"240\",\r\n                        \"direccion\":\"Universidad Católica Andrés Bello Centro Loyola, Piso 2, Caracas. Dtto. Capital\",\r\n                        \"telefono\":\"+5822122356047\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"acconsultores@com.ve\",\r\n                        \"sector\":\"2\",\r\n                        \"servicio\":\"2\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"146\",\r\n                        \"rif\":\"J314087754\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"A.C. CONSULTORES UCAB\",\r\n                        \"pais\":\"240\",\r\n                        \"direccion\":\"Universidad Católica Andrés Bello Centro Loyola, Piso 2, Caracas. Dtto. Capital\",\r\n                        \"telefono\":\"+5822122356047\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"acconsultores@com.ve\",\r\n                        \"sector\":\"2\",\r\n                        \"servicio\":\"2\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:39:57'),
(121, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=145,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 255;', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"145\",\r\n                        \"rif\":\"J13123\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"ASDAS\",\r\n                        \"pais\":\"9\",\r\n                        \"direccion\":\"DIRECCION\",\r\n                        \"telefono\":\"+126812313212\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"asda@adasd.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 15:41:10'),
(122, 2, 'updateClient', '127.0.0.1', 1, 'tbl_clientes', 'UPDATE `tbl_clientes` SET `Id_usuario_socio`=150,`Rif`=p_Rif,`Nit`=p_Nit,`Razon_social`=p_RazonSocial,`Id_pais`=p_IdPais,`Direccion`=p_Address,`Telefono_fiscal`=p_TelefonoFiscal,`Pagina_web`=p_PaginaWeb,`Email_fiscal`=p_EmailFiscal,`Id_cliente_sector`=p_IdSectorAsociado,`Id_cliente_servicio`=p_IdServicioAsociado,`Id_estatus`=p_IdStatus WHERE `Id` = 26;', '{\"socio\":\"150\",\r\n                        \"rif\":\"J000121940\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"AVILA SERVICIOS MEDICOS, C.A.\",\r\n                        \"pais\":\"240\",\r\n                        \"direccion\":\"Sexta Transversal de Altamira con Av.San Juan Bosco, Edif. Clinica Avila Altamira, Caracas.\",\r\n                        \"telefono\":\"+582122081026\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"aviserme@gmail.com\",\r\n                        \"sector\":\"0\",\r\n                        \"servicio\":\"0\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"150\",\r\n                        \"rif\":\"J000121940\",\r\n                        \"nit\":\"0\",\r\n                        \"razon\":\"AVILA SERVICIOS MEDICOS, C.A.\",\r\n                        \"pais\":\"240\",\r\n                        \"direccion\":\"Sexta Transversal de Altamira con Av.San Juan Bosco, Edif. Clinica Avila Altamira, Caracas.\",\r\n                        \"telefono\":\"+582122081026\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"aviserme@gmail.com\",\r\n                        \"sector\":\"2\",\r\n                        \"servicio\":\"4\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-13 17:05:07'),
(123, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-13 23:57:22 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-13 15:07:32\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-13 23:57:22\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-13 23:57:22'),
(124, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-14 10:38:16 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-13 23:57:22\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-14 10:38:16\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-14 10:38:16'),
(125, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-14 15:41:35 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-14 10:38:16\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-14 15:41:35\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-14 15:41:35'),
(126, 2, 'login', '127.0.0.1', 1, 'tbl_usuarios', 'UPDATE tbl_usuarios u SET u.Fecha_login = 2023-05-15 12:53:01 WHERE u.Codigo = 0001;', '{\"fecha_ultimo_login\": \"2023-05-14 15:41:35\",\"ultima_ip\": \"127.0.0.1\"}', '{\"fecha_ultimo_login\": \"2023-05-15 12:53:01\",\"ultima_ip\": \"127.0.0.1\"}', '2023-05-15 12:53:01'),
(127, 1, 'test', '127.0.0.1', 1, 'ninguna', 'ningun query', 'pepe', 'papa', '2022-07-01 00:00:00'),
(128, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-05-16 16:29:46 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-05-15 12:53:01\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-05-16 16:29:46\",\"last_ip\": \"127.0.0.1\"}', '2023-05-16 16:29:46'),
(129, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-05-16 16:31:14 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-05-16 16:29:46\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-05-16 16:31:14\",\"last_ip\": \"127.0.0.1\"}', '2023-05-16 16:31:14'),
(130, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-05-16 16:32:18 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-05-16 16:31:14\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-05-16 16:32:18\",\"last_ip\": \"127.0.0.1\"}', '2023-05-16 16:32:18'),
(131, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-05-16 16:41:47 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-05-16 16:32:18\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-05-16 16:41:47\",\"last_ip\": \"127.0.0.1\"}', '2023-05-16 16:41:47'),
(132, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-05-16 16:57:53 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-05-16 16:41:47\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-05-16 16:57:53\",\"last_ip\": \"127.0.0.1\"}', '2023-05-16 16:57:53'),
(133, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-05-16 16:58:31 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-05-16 16:57:53\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-05-16 16:58:31\",\"last_ip\": \"127.0.0.1\"}', '2023-05-16 16:58:31'),
(134, 1, 'createClient', '127.0.0.1', 1, 'clients', 'INSERT INTO `clients`(`partner_user_id`, `client_code`, `rif`, `nit`, `bussiness_name`, `country_id`, `client_address`, `tax_phone`, `website`, `tax_email`, `sector_id`, `service_id`, `status_id`) VALUES (p_partner_user_id,2298,p_rif,p_nit,p_bussiness_name,p_country_id,p_address,p_tax_phone,p_website,p_tax_email,p_sector_id,p_servicio_id,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-16 17:52:01'),
(135, 1, 'createClient', '127.0.0.1', 1, 'clients', 'INSERT INTO `clients`(`partner_user_id`, `client_code`, `rif`, `nit`, `bussiness_name`, `country_id`, `client_address`, `tax_phone`, `website`, `tax_email`, `sector_id`, `service_id`, `status_id`) VALUES (p_partner_user_id,2313,p_rif,p_nit,p_bussiness_name,p_country_id,p_address,p_tax_phone,p_website,p_tax_email,p_sector_id,p_servicio_id,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-16 18:08:14'),
(136, 2, 'updateClient', '127.0.0.1', 1, 'clients', 'UPDATE `clients` SET `partner_user_id`= 144,`rif`=p_rif,`nit`=p_nit,`bussiness_name`=p_bussiness_name,`country_id`=p_country_id,`client_address`=p_address,`tax_phone`=p_tax_phone,`website`=p_website,`tax_email`=p_tax_email,`sector_id`=p_sector_id,`service_id`=p_service_id,`status_id`=p_status_id WHERE `client_id` = 251;', '{\"socio\":\"144\",\r\n                        \"rif\":\"J123\",\r\n                        \"nit\":\"2568\",\r\n                        \"razon\":\"TESTING\",\r\n                        \"pais\":\"240\",\r\n                        \"direccion\":\"Testing Address\",\r\n                        \"telefono\":\"+584245555555\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"test@testing.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"socio\":\"144\",\r\n                        \"rif\":\"J123\",\r\n                        \"nit\":\"2568\",\r\n                        \"razon\":\"TESTING\",\r\n                        \"pais\":\"240\",\r\n                        \"direccion\":\"Testing Address\",\r\n                        \"telefono\":\"+584245555555\",\r\n                        \"pagina\":\"\",\r\n                        \"email\":\"test@testing.com\",\r\n                        \"sector\":\"1\",\r\n                        \"servicio\":\"1\",\r\n                        \"status\":\"1\",\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-16 19:13:52'),
(137, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-05-16 22:45:52 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-05-16 16:58:31\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-05-16 22:45:52\",\"last_ip\": \"127.0.0.1\"}', '2023-05-16 22:45:52'),
(138, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-05-17 01:15:37 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-05-16 22:45:52\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-05-17 01:15:37\",\"last_ip\": \"127.0.0.1\"}', '2023-05-17 01:15:37'),
(139, 1, 'createUser', '127.0.0.1', 1, 'users', 'INSERT INTO `users`(`user_code`, `password`, `time_change_password`, `first_name`, `second_name`, `first_surname`, `second_surname`, `birthday`, `position_id`, `department_id`, `parish_id`, `admission_date`, `departure_date`, `login_date`, `status_id`)\r\nVALUES(888888,AES_ENCRYPT(p_identity_number,@key),@dateChange,p_first_name,p_second_name,p_first_surname,p_second_surname,p_birthday,p_position_id,p_department_id,p_parish_id,p_admission_date,NULL,NULL,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 01:27:30'),
(140, 1, 'createUser', '127.0.0.1', 1, 'users', 'INSERT INTO `users`(`user_code`, `password`, `time_change_password`, `first_name`, `second_name`, `first_surname`, `second_surname`, `birthday`, `position_id`, `department_id`, `parish_id`, `admission_date`, `departure_date`, `login_date`, `status_id`)\r\nVALUES(888888,AES_ENCRYPT(p_identity_number,@key),@dateChange,p_first_name,p_second_name,p_first_surname,p_second_surname,p_birthday,p_position_id,p_department_id,p_parish_id,p_admission_date,NULL,NULL,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 01:32:59'),
(141, 1, 'createUser', '127.0.0.1', 1, 'users', 'INSERT INTO `users`(`user_code`, `password`, `time_change_password`, `first_name`, `second_name`, `first_surname`, `second_surname`, `birthday`, `position_id`, `department_id`, `parish_id`, `admission_date`, `departure_date`, `login_date`, `status_id`)\r\nVALUES(888888,AES_ENCRYPT(p_identity_number,@key),@dateChange,p_first_name,p_second_name,p_first_surname,p_second_surname,p_birthday,p_position_id,p_department_id,p_parish_id,p_admission_date,NULL,NULL,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 01:34:42'),
(142, 1, 'createUser', '127.0.0.1', 1, 'users', 'INSERT INTO `users`(`user_code`, `password`, `time_change_password`, `first_name`, `second_name`, `first_surname`, `second_surname`, `birthday`, `position_id`, `department_id`, `parish_id`, `admission_date`, `departure_date`, `login_date`, `status_id`)\r\nVALUES(888888,AES_ENCRYPT(p_identity_number,@key),@dateChange,p_first_name,p_second_name,p_first_surname,p_second_surname,p_birthday,p_position_id,p_department_id,p_parish_id,p_admission_date,NULL,NULL,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 01:35:59'),
(143, 1, 'createUser', '127.0.0.1', 1, 'users', 'INSERT INTO `users`(`user_code`, `password`, `time_change_password`, `first_name`, `second_name`, `first_surname`, `second_surname`, `birthday`, `position_id`, `department_id`, `parish_id`, `admission_date`, `departure_date`, `login_date`, `status_id`)\r\nVALUES(888888,AES_ENCRYPT(p_identity_number,@key),@dateChange,p_first_name,p_second_name,p_first_surname,p_second_surname,p_birthday,p_position_id,p_department_id,p_parish_id,p_admission_date,NULL,NULL,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 01:36:28'),
(144, 1, 'createUser', '127.0.0.1', 1, 'users', 'INSERT INTO `users`(`user_code`, `password`, `time_change_password`, `first_name`, `second_name`, `first_surname`, `second_surname`, `birthday`, `position_id`, `department_id`, `parish_id`, `admission_date`, `departure_date`, `login_date`, `status_id`)\r\nVALUES(888888,AES_ENCRYPT(p_identity_number,@key),@dateChange,p_first_name,p_second_name,p_first_surname,p_second_surname,p_birthday,p_position_id,p_department_id,p_parish_id,p_admission_date,NULL,NULL,1);', '{\"ultima_ip\":\"127.0.0.1\"}', '{\"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 02:47:54'),
(145, 2, 'updateUser', '127.0.0.1', 1, 'users', 'UPDATE `users` SET `user_code`=888888,`time_change_password`=@dateChange,`first_name`=p_first_name,`second_name`=p_second_name,`first_surname`=p_first_surname,`second_surname`=p_second_surname,`birthday`=p_birthday,`position_id`=p_position_id,`department_id`=p_department_id,`parish_id`=p_parish_id,`admission_date`=p_admission_date,`departure_date`=p_departure_date,`status_id`=p_status_id WHERE `user_id` = p_user_update_id;', NULL, '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 02:55:31'),
(146, 2, 'updateUser', '127.0.0.1', 1, 'users', 'UPDATE `users` SET `user_code`=888888,`time_change_password`=@dateChange,`first_name`=p_first_name,`second_name`=p_second_name,`first_surname`=p_first_surname,`second_surname`=p_second_surname,`birthday`=p_birthday,`position_id`=p_position_id,`department_id`=p_department_id,`parish_id`=p_parish_id,`admission_date`=p_admission_date,`departure_date`=p_departure_date,`status_id`=p_status_id WHERE `user_id` = p_user_update_id;', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 02:55:35'),
(147, 2, 'updateUser', '127.0.0.1', 1, 'users', 'UPDATE `users` SET `user_code`=888888,`time_change_password`=@dateChange,`first_name`=p_first_name,`second_name`=p_second_name,`first_surname`=p_first_surname,`second_surname`=p_second_surname,`birthday`=p_birthday,`position_id`=p_position_id,`department_id`=p_department_id,`parish_id`=p_parish_id,`admission_date`=p_admission_date,`departure_date`=p_departure_date,`status_id`=p_status_id WHERE `user_id` = p_user_update_id;', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 02:56:38'),
(148, 2, 'updateUser', '127.0.0.1', 1, 'users', 'UPDATE `users` SET `user_code`=888888,`time_change_password`=@dateChange,`first_name`=p_first_name,`second_name`=p_second_name,`first_surname`=p_first_surname,`second_surname`=p_second_surname,`birthday`=p_birthday,`position_id`=p_position_id,`department_id`=p_department_id,`parish_id`=p_parish_id,`admission_date`=p_admission_date,`departure_date`=p_departure_date,`status_id`=p_status_id WHERE `user_id` = p_user_update_id;', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 02:56:43'),
(149, 2, 'updateUser', '127.0.0.1', 1, 'users', 'UPDATE `users` SET `user_code`=888888,`time_change_password`=@dateChange,`first_name`=p_first_name,`second_name`=p_second_name,`first_surname`=p_first_surname,`second_surname`=p_second_surname,`birthday`=p_birthday,`position_id`=p_position_id,`department_id`=p_department_id,`parish_id`=p_parish_id,`admission_date`=p_admission_date,`departure_date`=p_departure_date,`status_id`=p_status_id WHERE `user_id` = p_user_update_id;', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 02:57:52'),
(150, 2, 'updateUser', '127.0.0.1', 1, 'users', 'UPDATE `users` SET `user_code`=888888,`time_change_password`=@dateChange,`first_name`=p_first_name,`second_name`=p_second_name,`first_surname`=p_first_surname,`second_surname`=p_second_surname,`birthday`=p_birthday,`position_id`=p_position_id,`department_id`=p_department_id,`parish_id`=p_parish_id,`admission_date`=p_admission_date,`departure_date`=p_departure_date,`status_id`=p_status_id WHERE `user_id` = p_user_update_id;', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 02:57:56'),
(151, 2, 'updateUser', '127.0.0.1', 1, 'users', 'UPDATE `users` SET `user_code`=888888,`time_change_password`=@dateChange,`first_name`=p_first_name,`second_name`=p_second_name,`first_surname`=p_first_surname,`second_surname`=p_second_surname,`birthday`=p_birthday,`position_id`=p_position_id,`department_id`=p_department_id,`parish_id`=p_parish_id,`admission_date`=p_admission_date,`departure_date`=p_departure_date,`status_id`=p_status_id WHERE `user_id` = p_user_update_id;', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '{\"name\":\"TESTING TESTING 2, TESTING 3 TESTING 4\",\r\n                        \"birthday\":\"1994-01-01\",\r\n                        \"cargo\":17,\r\n                        \"division\":18,\r\n                        \"parroquia\":753,\r\n                        \"fecha_ingreso\":\"2023-05-01\",\r\n                        \"fecha_egreso\":\"2023-05-18\",\r\n                        \"status\":2,\r\n                        \"ultima_ip\":\"127.0.0.1\"}', '2023-05-17 03:00:28'),
(152, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-05-31 11:53:04 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-05-17 01:15:37\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-05-31 11:53:04\",\"last_ip\": \"127.0.0.1\"}', '2023-05-31 11:53:04'),
(153, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-05 11:23:30 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-05-31 11:53:04\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-05 11:23:30\",\"last_ip\": \"127.0.0.1\"}', '2023-06-05 11:23:30'),
(154, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-05 14:19:45 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-05 11:23:30\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-05 14:19:45\",\"last_ip\": \"127.0.0.1\"}', '2023-06-05 14:19:45'),
(155, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-05 15:09:18 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-05 14:19:45\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-05 15:09:18\",\"last_ip\": \"127.0.0.1\"}', '2023-06-05 15:09:18'),
(156, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-05 15:26:05 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-05 15:09:18\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-05 15:26:05\",\"last_ip\": \"127.0.0.1\"}', '2023-06-05 15:26:05'),
(157, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-06 10:34:48 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-05 15:26:05\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-06 10:34:48\",\"last_ip\": \"127.0.0.1\"}', '2023-06-06 10:34:48'),
(158, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-07 09:30:24 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-06 10:34:48\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-07 09:30:24\",\"last_ip\": \"127.0.0.1\"}', '2023-06-07 09:30:24'),
(159, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-08 09:49:08 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-07 09:30:24\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-08 09:49:08\",\"last_ip\": \"127.0.0.1\"}', '2023-06-08 09:49:08'),
(160, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-08 13:15:15 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-08 09:49:08\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-08 13:15:15\",\"last_ip\": \"127.0.0.1\"}', '2023-06-08 13:15:15'),
(161, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-08 14:57:56 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-08 13:15:15\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-08 14:57:56\",\"last_ip\": \"127.0.0.1\"}', '2023-06-08 14:57:56'),
(162, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-09 09:35:18 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-08 14:57:56\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-09 09:35:18\",\"last_ip\": \"127.0.0.1\"}', '2023-06-09 09:35:18'),
(163, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-09 15:56:27 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-09 09:35:18\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-09 15:56:27\",\"last_ip\": \"127.0.0.1\"}', '2023-06-09 15:56:27'),
(164, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-12 09:42:12 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-09 15:56:27\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-12 09:42:12\",\"last_ip\": \"127.0.0.1\"}', '2023-06-12 09:42:12');
INSERT INTO `control_logs` (`log_id`, `action_id`, `log_description`, `user_responsible_ip`, `user_responsible_id`, `affected_table`, `query_sql`, `old_value`, `new_value`, `register_date`) VALUES
(165, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-13 08:57:02 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-12 09:42:12\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-13 08:57:02\",\"last_ip\": \"127.0.0.1\"}', '2023-06-13 08:57:02'),
(166, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-14 10:36:36 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-13 08:57:02\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-14 10:36:36\",\"last_ip\": \"127.0.0.1\"}', '2023-06-14 10:36:36'),
(167, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-14 17:00:00 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-14 10:36:36\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-14 17:00:00\",\"last_ip\": \"127.0.0.1\"}', '2023-06-14 17:00:00'),
(168, 2, 'login', '127.0.0.1', 1, 'users', 'UPDATE users u SET u.login_date = 2023-06-14 17:19:27 WHERE u.user_code = 0001;', '{\"date_last_login\": \"2023-06-14 17:00:00\",\"last_ip\": \"127.0.0.1\"}', '{\"date_last_login\": \"2023-06-14 17:19:27\",\"last_ip\": \"127.0.0.1\"}', '2023-06-14 17:19:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_logs_action`
--

CREATE TABLE `control_logs_action` (
  `action_id` int(11) NOT NULL,
  `action_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `control_logs_action`
--

INSERT INTO `control_logs_action` (`action_id`, `action_description`) VALUES
(1, 'INSERT'),
(2, 'UPDATE'),
(3, 'DELETE'),
(4, 'SELECT');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_status`
--

CREATE TABLE `control_status` (
  `status_id` int(11) NOT NULL,
  `status_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `control_status`
--

INSERT INTO `control_status` (`status_id`, `status_description`) VALUES
(1, 'Activo'),
(2, 'Inactivo'),
(3, 'De reposo'),
(4, 'De Vacaciones');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `project_description` text NOT NULL,
  `client_id` int(11) NOT NULL,
  `partner_id` int(11) NOT NULL,
  `quality_partner_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `hiring_date` date NOT NULL,
  `project_value` decimal(25,2) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `projects`
--

INSERT INTO `projects` (`project_id`, `project_description`, `client_id`, `partner_id`, `quality_partner_id`, `manager_id`, `hiring_date`, `project_value`, `currency_id`, `company_id`, `status_id`) VALUES
(1, 'AUDITORIA A LOS ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2020', 98, 146, 144, 3, '2020-07-01', 2750.00, 2, 1, 1),
(2, 'AUDITORIA A LOS ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2020', 28, 145, 144, 3, '2020-07-01', 2600000000.00, 1, 1, 1),
(3, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2020', 52, 146, 144, 3, '2020-07-01', 308154753.17, 1, 1, 1),
(4, 'COMPILACIóN DE INFORMACIóN FINANCIERA AL 30 DE JUNIO DE 2020 Y 31 DE DICIEMBRE DE 2019 Y 2018', 102, 149, 144, 9, '2020-07-01', 4200.00, 2, 1, 1),
(5, 'AUDITORíA A LOS ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2020', 94, 146, 144, 3, '2020-07-01', 750.00, 2, 1, 1),
(6, 'DIAGNOSTICO DE LOS PROCESOS ADMINISTRATIVOS Y VERIFICACION SALDOS FINALES', 56, 150, 144, 28, '2020-07-01', 900000000.00, 1, 1, 1),
(7, 'AUDITORIA AL 31 DE DICIEMBRE DE 2020', 19, 149, 144, 9, '2020-07-01', 11900.00, 2, 1, 1),
(8, 'AUDITORIA AL 30-06-2020', 31, 146, 144, 148, '2020-07-01', 132000.00, 2, 1, 1),
(9, 'AUDITORIA AL 30-06-2020', 27, 145, 144, 148, '2020-07-01', 8000.00, 2, 1, 1),
(10, 'SERVICIOS PRESTADOS DE CONTABILIDAD INSOURCING PROYECTO CONTINUO', 103, 146, 144, 5, '2020-07-01', 500000000.00, 1, 1, 1),
(11, 'LIBRO DE COMPRAS', 104, 58, 144, 61, '2020-07-01', 346500000.00, 1, 1, 1),
(12, 'LIBRO DE COMPRAS', 47, 58, 144, 61, '2020-07-01', 138600000.00, 1, 1, 1),
(13, 'LIBRO DE COMPRAS', 106, 58, 144, 61, '2020-07-01', 69300000.00, 1, 1, 1),
(14, 'PDT 2020', 71, 58, 144, 61, '2020-07-01', 2000.00, 2, 1, 1),
(15, 'DDR ISLR 2020', 74, 58, 144, 61, '2020-07-01', 700.00, 2, 1, 1),
(16, 'CONTINGENCIA IGTF', 107, 58, 144, 61, '2020-07-01', 720.00, 2, 1, 1),
(17, 'AUDITORIA FISCAL 2018 - 2019', 53, 58, 144, 61, '2020-07-01', 1800.00, 2, 1, 1),
(18, 'AUDITORIA FISCAL 2018 - 2019', 41, 58, 144, 61, '2020-07-01', 1200.00, 2, 1, 1),
(19, 'DDR ISLR 2019', 29, 58, 144, 61, '2020-07-01', 1500.00, 2, 1, 1),
(20, 'ELABORACIÓN DEL AJUSTE POR INFLACIÓN Y NOTAS DEL INFORME AL 31 DE DICIEMBRE DE 2018', 41, 150, 144, 34, '2020-07-01', 1500.00, 2, 1, 1),
(21, 'EXAMEN DE LOS ESTADOS FINACIEROS AL 31 DE DICIEMBRE DE 2019', 53, 150, 144, 34, '2020-07-01', 2860.00, 2, 1, 1),
(22, 'AUDITORíA AL 30 DE JUNIO DEL 2020', 108, 149, 144, 9, '2020-09-02', 12000.00, 2, 1, 1),
(23, 'EXAMEN DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2019', 41, 150, 144, 34, '2020-09-03', 2860.00, 2, 1, 1),
(24, 'AUDITORÍA DE LOS ESTADOS FINANCIEROS 31 DE DICIEMBRE DE 2019', 109, 144, 144, 144, '2020-09-07', 2700.00, 2, 1, 1),
(25, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL SEMESTRE TERMINADO EL 30 DE JUNIO DE 2020', 30, 4, 144, 6, '2020-08-24', 210000000.00, 1, 1, 1),
(28, 'AUDITORIA AL 31 DE DICIEMBRE DE 2020', 90, 149, 144, 9, '2020-08-03', 7000.00, 2, 1, 1),
(29, 'AUDITORIA AL 31 DE MAYO DE 2020', 84, 144, 144, 3, '2020-09-23', 3680.00, 3, 1, 1),
(30, 'DIAGNóSTICO Y EVALUACIóN DE RIESGO DEL PROCESO COBRO DE SERVICIOS EN DIVISAS', 62, 150, 144, 2, '2020-07-08', 4000.00, 2, 2, 1),
(31, 'IDENTIFICACIóN DE PROCESOS ADMINISTRATIVOS Y FINANCIEROS VIOLENTADOS EN HIAS VENEZUELA', 110, 144, 144, 36, '2020-09-30', 6000.00, 2, 1, 1),
(32, 'AUDITORIA DE ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2019', 111, 146, 144, 57, '2020-08-19', 16192.00, 2, 1, 1),
(33, 'AUDITORIA DE ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2019', 112, 146, 144, 57, '2020-08-19', 1632.00, 2, 1, 1),
(34, 'AUDITORIA DE ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2019', 113, 146, 144, 57, '2020-08-19', 1088.00, 2, 1, 1),
(35, 'AUDITORIA DE ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2019', 114, 146, 144, 57, '2020-08-19', 2448.00, 2, 1, 1),
(36, 'REVISIóN DE LOS CONTRATOS DE ALIANZAS Y EJECUCIóN FINANCIERA', 115, 144, 144, 34, '2020-10-18', 7500.00, 2, 1, 1),
(37, 'IMPUESTO A LOS GRANDES PATRIMONIOS AL 30/09/2020 E IMPUESTO SOBRE LA RENTA AL 31/12/2020', 66, 147, 144, 61, '2020-10-21', 1170.00, 2, 1, 1),
(38, 'IMPUESTO A LOS GRANDES PATRIMONIOS AL 30/09/2020', 71, 147, 144, 61, '2020-10-20', 730.00, 2, 1, 1),
(39, 'IGP 2020', 53, 58, 144, 61, '2020-10-22', 350.00, 2, 1, 1),
(40, 'IGP 2020', 41, 58, 144, 58, '2020-10-22', 350.00, 2, 1, 1),
(41, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL SEMESTRE FINALIZADO EL 31 DE DICIEMBRE DE 2019', 116, 145, 144, 2, '2020-10-22', 142000.00, 2, 1, 1),
(42, 'AUDITORIA  PARA EL SEMESTRE TERMINADO EL 31 DE DICIEMBRE DE 2020', 32, 4, 144, 6, '2020-10-16', 133000.00, 2, 1, 1),
(43, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020.', 118, 144, 144, 36, '2020-10-28', 5800.00, 2, 1, 1),
(44, 'IMPUESTO A LOS GRANDES PATRIMONIOS AL 30/09/2020', 95, 147, 144, 61, '2020-10-27', 120.00, 2, 1, 1),
(45, 'IMPUESTO A LOS GRANDES PATRIMONIOS AL 30/09/2020', 48, 147, 144, 61, '2020-10-27', 120.00, 2, 1, 1),
(46, 'DIAGNóSTICO Y EVALUACIóN DE RIESGO DEL PROCESO COBRO DE SERVICIOS EN DIVISAS', 62, 150, 144, 2, '2020-07-08', 4000.00, 2, 2, 2),
(47, 'EVALUACION NOMINA EMPLEADOS Y GERENTES', 62, 150, 144, 2, '2020-10-26', 5000.00, 2, 2, 1),
(48, 'AUDITORIA AL 30 DE ABRIL DEL 2020', 91, 149, 144, 9, '2020-11-06', 2040.00, 2, 1, 1),
(49, 'AUDITORIA AL 31 DE DICIEMBRE DE 2020', 43, 149, 144, 9, '2020-11-06', 2990.00, 2, 1, 1),
(50, 'AUDITORIA AL 31 DE DICIEMBRE DE 2020', 39, 149, 144, 9, '2020-11-06', 8000.00, 2, 1, 1),
(51, 'IGP 2020', 74, 58, 144, 61, '2020-11-10', 600.00, 2, 1, 1),
(52, 'AUDITORIA DE ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 86, 150, 144, 57, '2020-11-12', 961225152.00, 1, 1, 1),
(53, 'IGP 2020', 79, 58, 144, 61, '2020-11-16', 296.00, 2, 1, 1),
(54, 'IGP 2020', 134, 58, 144, 61, '2020-11-13', 400.00, 2, 1, 1),
(55, 'IGP 2020', 78, 58, 144, 61, '2020-11-17', 376.00, 2, 1, 1),
(56, 'IGP 2020', 45, 58, 144, 61, '2020-11-17', 500.00, 2, 1, 1),
(57, 'IGP 2020', 64, 58, 144, 61, '2020-11-17', 118.00, 2, 1, 1),
(58, 'AUDITORIA DE ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2019', 135, 146, 144, 57, '2020-08-19', 1632.00, 2, 1, 1),
(59, 'EXáMEN  DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 38, 150, 144, 34, '2020-10-15', 7596.00, 2, 1, 1),
(60, 'ASESORIA Y REVISION DE LOS EF SEGúN LA NIC 29 AL 31 DE DICIEMBRE DE 2020', 38, 150, 144, 34, '2020-11-18', 550.00, 2, 1, 1),
(61, 'ISLR 2018, 2019 Y 2020 / IGP 2019 Y 2020 /PT 2018 Y 2019', 136, 147, 144, 61, '2020-11-23', 4000.00, 2, 1, 1),
(62, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 67, 146, 144, 3, '2020-11-24', 3391.00, 2, 1, 1),
(63, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 37, 144, 144, 144, '2020-11-26', 8600.00, 2, 1, 1),
(64, 'PREPARACIóN DE ESTADOS FINANCIEROS AL 31 DICIEMBRE DE 2017 AL 2019 Y  2020 BAJO NISR 4410', 136, 150, 144, 57, '2020-12-01', 1380.00, 2, 1, 1),
(65, 'LIBROS DE COMPRA 2020 2021', 47, 58, 144, 61, '2020-12-09', 3024.00, 2, 1, 1),
(66, 'LIBROS DE COMPRA 2020 2021', 35, 58, 144, 61, '2020-12-09', 7560.00, 2, 1, 1),
(67, 'LIBROS DE COMPRA 2020 2021', 106, 58, 144, 61, '2020-12-11', 1512.00, 2, 1, 1),
(68, 'AUDITORIA DE ESTADOS FINANCIEROS AL 30 DE SEPTIEMBRE DE 2020', 24, 7, 144, 56, '2020-12-14', 1792.00, 2, 1, 1),
(69, 'AUDITORIA DE ESTADOS FINANCIEROS AL 30 DE SEPTIEMBRE DE 2020', 59, 7, 144, 56, '2020-12-14', 3883.00, 2, 1, 1),
(70, 'AUDITORIA DE ESTADOS FINANCIEROS AL 30 DE SEPTIEMBRE DE 2020', 60, 7, 144, 56, '2020-12-14', 1195.00, 2, 1, 1),
(71, 'AUDITORIA DE ESTADOS FINANCIEROS AL 30 DE SEPTIEMBRE DE 2020', 23, 7, 144, 56, '2020-12-14', 1792.00, 2, 1, 1),
(72, 'AUDITORIA DE ESTADOS FINANCIEROS AL 30 DE SEPTIEMBRE DE 2020', 61, 7, 144, 56, '2020-12-14', 2390.00, 2, 1, 1),
(73, 'EXAMEN DE LOS EF AL 30 DE JUNIO DE 2020 Y EL AñO QUE TERMINARá EL 31 DE DICIEMBRE DE 2020.', 99, 150, 144, 28, '2020-12-17', 4629.00, 2, 1, 1),
(74, 'AUDITORIA DE LOS EF PARA EL SEMESTRE QUE FINALIZARA EL 31 DE DICIEMBRE 2020', 27, 145, 144, 148, '2020-12-21', 11000.00, 2, 1, 1),
(75, 'AUDITORIA AL 31 DE AGOSTO DE 2020', 14, 146, 7, 56, '2020-12-22', 1800.00, 2, 1, 1),
(76, 'AUDITORIA AL 31 DE DICIEMBRE DE 2020', 137, 146, 146, 56, '2020-12-22', 200000000.00, 1, 1, 1),
(77, 'AUDITORIA DE LOS ESTADOS FINANCIEROS Y EMISION DE INFORME PARA EL SEMESTRE TERMINADO EL 31 DE DICIEMBRE DE 2020', 30, 4, 146, 6, '2021-01-07', 340000000.00, 1, 1, 1),
(78, 'AUDITORíA AL 31 DE DICIEMBRE DE 2020', 98, 146, 146, 3, '2021-01-07', 3000.00, 2, 1, 1),
(79, 'AUDITORíA AL 31 DE DICIEMBRE DEL 2020', 92, 149, 149, 9, '2021-01-08', 3120.00, 2, 1, 1),
(81, 'AUDITORíA A LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 28, 4, 146, 3, '2021-01-11', 3260000000.00, 1, 1, 1),
(82, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 52, 146, 146, 3, '2021-01-11', 1379.00, 2, 1, 1),
(83, 'ESCRITO SENIAT REPARO', 118, 58, 144, 58, '2021-01-01', 350.00, 2, 1, 1),
(84, 'DETERMINACIÓN DE CONTINGENCIAS SENIAT', 118, 58, 144, 58, '2021-01-01', 250.00, 2, 1, 1),
(85, 'DDR 2020', 87, 58, 58, 58, '2021-01-01', 450.00, 2, 1, 1),
(86, 'REVISIÓN DDR 2021', 96, 58, 58, 58, '2021-01-01', 950.00, 2, 1, 1),
(87, 'DDR 2020', 17, 58, 58, 58, '2021-01-01', 600.00, 2, 1, 1),
(88, 'AUDITORIA DE ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2018', 138, 4, 144, 57, '2021-01-21', 13500.00, 2, 1, 1),
(89, 'AUDITORIA AL 31 DE DICIEMBRE DE 2020', 36, 149, 146, 56, '2021-01-25', 200000000.00, 1, 1, 1),
(90, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL SEMESTRE FINALIZADO EL 30 DE JUNIO DE 2020', 116, 145, 146, 2, '2021-01-29', 154164.00, 2, 1, 1),
(91, 'COMPILACIóN DE INFORMACIóN FINANCIERA AL 31 DE ENERO DE 2021', 139, 149, 146, 9, '2021-01-22', 1000.00, 2, 1, 1),
(92, 'AUDITORIA ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 54, 146, 146, 57, '2021-02-03', 8800.00, 2, 1, 1),
(93, 'AUDITORIA AL 31 DEDICIEMBRE DE 2020', 140, 7, 146, 56, '2021-02-03', 2500.00, 2, 1, 1),
(94, 'DDR 2020', 141, 58, 147, 61, '2021-02-03', 525.00, 2, 1, 1),
(95, 'DDR 2020', 142, 58, 147, 61, '2021-02-04', 1800.00, 2, 1, 1),
(96, 'DDR 2020', 29, 58, 58, 61, '2021-02-10', 2000.00, 2, 2, 1),
(97, 'SERVICIOS PRESTADOS DE CONTABILIDAD INSOURCING PROYECTO CONTINUO FEBRERO A DICIEMBRE 2021', 103, 146, 146, 5, '2021-02-18', 5000.00, 2, 1, 1),
(98, 'EXAMEN DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 89, 150, 150, 34, '2021-02-17', 1050.00, 2, 1, 1),
(99, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2020.', 62, 150, 150, 2, '2021-02-25', 17654.00, 2, 1, 1),
(100, 'DDR 2020', 22, 58, 58, 61, '2021-03-01', 467.00, 2, 1, 1),
(101, 'DDR 2020', 64, 58, 58, 61, '2021-03-01', 467.00, 2, 1, 1),
(102, 'DDR 2020', 79, 58, 58, 61, '2021-03-01', 1198.00, 2, 1, 1),
(103, 'DDR 2020', 78, 58, 58, 61, '2021-03-01', 1501.00, 2, 1, 1),
(104, 'LOANG STAFF', 143, 147, 147, 61, '2021-03-01', 6240.00, 2, 1, 1),
(105, 'DDR 2020', 80, 58, 58, 61, '2021-03-02', 350.00, 2, 1, 1),
(106, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 25, 150, 150, 34, '2021-03-02', 7500.00, 2, 1, 1),
(107, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 72, 150, 150, 34, '2021-03-02', 7500.00, 2, 1, 1),
(108, 'AUDITORIA DE ESTADOS FINANCIEROS DE BANCO DE VENEZUELA, S.A. BANCO UNIVERSAL.', 31, 148, 146, 148, '2021-03-04', 96150621571.04, 1, 1, 1),
(109, 'REVISIÓN DE DEBERES FORMALES EJERCICIOS 2019 - 2021', 144, 147, 146, 61, '2021-03-05', 4800.00, 2, 1, 1),
(110, 'DDR 2020', 71, 147, 147, 61, '2021-03-05', 985.00, 2, 1, 1),
(111, 'EXAMEN DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 145, 4, 150, 34, '2021-03-09', 75000.00, 2, 1, 1),
(112, 'DDR 2020 DIRECTORAS', 74, 58, 58, 61, '2021-03-12', 300.00, 2, 1, 2),
(113, 'DDR 2020', 100, 58, 58, 61, '2021-03-12', 1800.00, 2, 1, 1),
(114, 'AUDITORIA AL 31 DECIEMBRE DE 2020', 66, 7, 146, 56, '2021-03-12', 2500.00, 2, 1, 1),
(115, 'API DICIEMBRE 2018 Y 2017', 146, 149, 149, 9, '2021-03-18', 2000.00, 2, 1, 1),
(116, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 147, 146, 146, 3, '2021-03-19', 1940.00, 2, 1, 1),
(117, 'DDR 2020', 95, 147, 147, 61, '2021-03-19', 250.00, 2, 1, 2),
(118, 'DDR 2020', 46, 147, 147, 61, '2021-03-19', 250.00, 2, 1, 2),
(119, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL SEMESTRE FINALIZADO EL 31 DE DICIEMBRE DE 2020.', 116, 145, 146, 2, '2021-03-22', 119775.00, 2, 1, 1),
(120, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2020.', 22, 150, 58, 2, '2021-03-25', 2520.00, 2, 1, 1),
(121, 'AUDITORíA DE LOS ESTADOS FINANCIEROS  PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2020.', 78, 150, 58, 2, '2021-03-25', 3060.00, 2, 1, 1),
(122, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2020.', 79, 150, 58, 2, '2021-03-25', 3420.00, 2, 1, 1),
(123, 'AUDITORIA DE LOS ESTADOS FINANCIEROS PARA EL SEMESTRE FINALIZADO EL 30 DE JUNIO DE 2020', 29, 4, 146, 6, '2021-03-30', 22650.00, 2, 1, 1),
(124, 'ASESORIA CONTINUA LEGAL, TRIBUTARIA E IMPOSITIVA', 148, 147, 147, 61, '2021-03-16', 11520.00, 2, 1, 1),
(125, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 3O DE ABRIL 2021', 91, 149, 146, 9, '2021-04-19', 2100.00, 2, 1, 1),
(126, 'AUDITORIA AL 31 DE DICIEMBRE 2020', 26, 150, 150, 28, '2021-04-19', 7500.00, 2, 1, 1),
(127, 'AUDITORIA AL 31 DE DICIEMBRE 2020', 70, 150, 150, 28, '2021-04-19', 6000.00, 2, 1, 1),
(128, 'ACTUALIZACIóN Y ADECUACIóN DE LA DOCUMENTACIóN EN MATERIA DE PREVENCIóN Y CONTROL DE LC/FT/FPADM', 29, 4, 146, 85, '2021-04-21', 24000.00, 3, 2, 1),
(129, 'AUDITORíA EMPRESAS MIXTAS AL 31 DE DICIEMBRE DE 2017', 85, 145, 146, 9, '2021-04-30', 1.00, 1, 1, 1),
(130, 'AUDITORIA DE LOS ESTADOS FINANCIEROS DEL BANCO DE VENEZUELA, S.A. AL  30 DE JUNIO DE 2021', 31, 148, 146, 11, '2021-05-13', 400000000000.00, 1, 1, 1),
(131, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL SEMESTRE TERMINADO EL 30 DE JUNIO 2021', 32, 4, 146, 6, '2021-05-17', 179500.00, 2, 1, 1),
(132, 'AUDITORíA DE ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2021', 27, 145, 146, 32, '2021-05-17', 9000.00, 2, 1, 1),
(133, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL SEMESTRE TERMINADO EL 31 DE DICIEMBRE DE 2020', 29, 4, 146, 6, '2021-05-18', 40100.00, 3, 1, 1),
(134, 'AUDITORíA DE LOS ESTADOS FINANCIEROS  PARA EL SEMESTRE FINALIZADO EL 30 DE JUNIO DE 2021', 116, 145, 146, 2, '2021-06-02', 135320.00, 2, 1, 1),
(135, 'AUDITORIA DE LOS ESTADOS FINANCEIROS AL 31 DE MAYO 2021', 84, 144, 146, 13, '2021-06-08', 6500.00, 3, 1, 1),
(136, 'DDR 2021', 74, 58, 144, 61, '2021-06-16', 870.00, 2, 1, 1),
(137, 'ASESORIA TRIBUTARIA', 54, 58, 146, 61, '2021-06-16', 150.00, 2, 1, 1),
(138, 'REVISION DE DEBERES FORMALES EJERCICIO 2020', 149, 58, 58, 61, '2021-06-22', 800.00, 2, 1, 1),
(139, 'AUDITORíA AL SEMESTRE TERMINADO EL 30 DE JUNIO DE 2021', 98, 146, 146, 3, '2021-07-05', 3440.00, 2, 1, 1),
(140, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL SEMESTRE TERMINADO EL 30 DE JUNIO DE 2021', 52, 146, 146, 3, '2021-07-05', 1760.00, 2, 1, 1),
(141, 'CONSULTA DIFERENCIA EN CAMBIO', 150, 58, 58, 61, '2021-07-07', 550.00, 2, 1, 1),
(142, 'REVISIóN DE LOS CONTROLES ADMINISTRATIVOS, CONTABLES Y FINANCIEROS AñO 2020', 149, 144, 58, 36, '2021-07-08', 1800.00, 2, 1, 1),
(143, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2021', 151, 146, 146, 3, '2021-07-12', 500.00, 2, 1, 1),
(144, 'AUDITORíA AL SEMESTRE TERMINADO EL 30 DE JUNIO DE 2021', 28, 146, 146, 3, '2021-07-12', 9000.00, 2, 1, 1),
(145, 'IGP 2021', 118, 58, 144, 61, '2021-07-16', 1200.00, 2, 1, 1),
(146, 'DIAGNóSTICO Y EVALUACIóN DE RIESGO DEL CICLO DE INGRESOS, CICLO DE EGRESOS Y TESORERíA', 152, 150, 150, 85, '2021-08-06', 6750.00, 2, 1, 1),
(147, 'AUDITORíA DE ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2021', 108, 149, 149, 9, '2021-08-06', 15000.00, 2, 1, 1),
(148, 'CONSULTA DIFERENCIA EN CAMBIO', 153, 58, 150, 61, '2021-08-30', 600.00, 2, 1, 1),
(149, 'CONSULTA DIFERENCIA EN CAMBIO', 154, 58, 150, 61, '2021-08-30', 600.00, 2, 1, 1),
(150, 'ASESORÍA FISCAL 2021 - 2022', 155, 58, 58, 61, '2021-09-01', 1270.00, 2, 1, 1),
(151, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2021', 156, 146, 146, 3, '2021-09-02', 2800.00, 2, 1, 1),
(152, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 118, 144, 144, 36, '2021-09-01', 14970.00, 2, 1, 1),
(153, 'PRECIOS DE TRANSFERENCIA 2019 - 2020', 158, 58, 58, 61, '2021-09-03', 2160.00, 2, 1, 1),
(154, 'AUDITORIA AL 31 DE AGOSTO DE 2021', 159, 149, 149, 56, '2021-09-13', 7000.00, 2, 1, 1),
(155, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 161, 149, 149, 9, '2021-09-13', 6000.00, 2, 1, 1),
(156, 'EXAMEN DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 53, 150, 150, 34, '2021-09-20', 2860.00, 2, 1, 1),
(157, 'TOMA FISICA DE INVENTARIOS', 56, 150, 150, 28, '2021-09-21', 3264.00, 2, 1, 1),
(158, 'AUDITORIA DE LOS ESTADOS FINANCIEROS PARA EL SEMESTRE TERMINADO EL 30 DE JUNIO DE 2021', 29, 4, 146, 6, '2021-09-21', 41500.00, 3, 1, 1),
(159, 'AUDITORIA AL 31 DE DICIEMBRE DE 2021', 19, 149, 149, 9, '2021-09-21', 11900.00, 2, 1, 1),
(160, 'AJUSTE POR INFLACION DE LOS EEFF  AL 31/12/2020', 145, 4, 150, 34, '2021-09-21', 6750.00, 2, 2, 1),
(161, 'INFORME ESPECIAL PERIODO INTERMEDIO VIñA VALENCIA', 37, 144, 144, 144, '2021-09-06', 750.00, 2, 1, 1),
(162, 'AUDITORIA DE LOS ESTADOS FINANCIEROS Y ASESORIA EN MATERIA FISCAL AL 31 DE DICIEMBRE DE 2021', 90, 149, 149, 9, '2021-09-29', 7700.00, 2, 1, 1),
(163, 'AUDITORIA PERIODO DE CINCO (5) AñOS, COMPRENDIDOS ENTRE EL 01 DE ENERO DE 2015 AL 31 DE DICIEMBRE DE 2020', 162, 144, 144, 36, '2021-09-29', 6120.00, 2, 1, 1),
(164, 'LIBROS DE COMPRA 2021 2022', 35, 58, 58, 58, '2021-10-04', 29280.00, 1, 1, 1),
(165, 'LIBROS DE COMPRA 2021 2022', 106, 58, 58, 58, '2021-10-04', 12810.00, 1, 1, 1),
(166, 'LIBROS DE COMPRA 2021 2022', 47, 58, 58, 58, '2021-10-04', 14640.00, 1, 1, 1),
(167, 'EXAMEN DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2019 Y 2020', 163, 146, 146, 34, '2021-10-04', 16000.00, 2, 1, 1),
(168, 'AUDITORíA DE LOS ESTADOS FINANCIEROS DEL GRUPO AMA DE CASA AñO FINALIZADO EL 31 DE DICIEMBRE DE 2021', 37, 144, 144, 36, '2021-10-19', 10000.00, 2, 1, 1),
(169, 'AJUSTE POR INFLACION DE LOS EEFF DE CONSORCIO CREDICARD AL 31/12/2021', 145, 150, 150, 34, '2021-10-19', 8100.00, 2, 1, 1),
(170, 'AUDITORIA AL 31 DE DICIEMBRE DE 2019 (CONTINUIDAD)', 17, 149, 149, 56, '2021-10-20', 8800.00, 2, 1, 1),
(171, 'IGP 2019, 2020, 2021', 57, 58, 58, 61, '2021-10-22', 880.00, 2, 1, 1),
(172, 'ASESORÍA ANUAL 2021 - 2022', 41, 58, 58, 61, '2021-10-22', 2480.00, 2, 1, 1),
(173, 'ASESORÍA ANUAL 2021 - 2022', 79, 58, 58, 61, '2021-10-22', 5300.00, 2, 1, 1),
(174, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 69, 150, 150, 57, '2021-10-25', 5086.02, 2, 1, 1),
(175, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 73, 150, 150, 57, '2021-10-26', 2365.59, 2, 1, 1),
(176, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 50, 150, 150, 57, '2021-10-25', 1774.19, 2, 1, 1),
(177, 'EXAMEN DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 145, 150, 150, 34, '2021-10-26', 109350.00, 2, 1, 1),
(178, 'ASESORÍA FISCAL 2021', 159, 58, 58, 61, '2021-10-27', 1600.00, 2, 1, 1),
(179, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 88, 150, 150, 57, '2021-10-28', 1774.19, 2, 1, 1),
(180, 'PREPARACIÓN DE LA DECLARACIÓN DE IMPUESTO A LOS GRANDES PATRIMONIOS AL 30-09-2019', 164, 147, 147, 63, '2021-10-29', 3314.00, 2, 1, 1),
(181, 'PREPARACIÓN DE LA DECLARACIÓN DE IMPUESTO A LOS GRANDES PATRIMONIOS AL 30-09-2020', 164, 147, 147, 63, '2021-10-29', 3267.00, 2, 1, 1),
(182, 'ESTUDIO Y DECLARACIÓN DE PRECIOS DE TRANSFERENCIA AL 31-12-2019', 164, 147, 147, 63, '2021-10-29', 5500.00, 2, 1, 1),
(183, 'ESTUDIO Y DECLARACIÓN DE PRECIOS DE TRANSFERENCIA AL 31-12-2020', 164, 147, 147, 63, '2021-10-29', 2660.00, 2, 1, 1),
(184, 'IMPUESTO A LOS GRANDES PATRIMONIOS AL 30-09-2021', 164, 147, 147, 63, '2021-10-29', 1288.00, 2, 1, 1),
(185, 'AUDITORIA AL 31 DE AGOSTO DE 2021', 166, 7, 146, 56, '2021-11-01', 2160.00, 2, 1, 1),
(186, 'AUDITORIA DE LOS ESTADOS FINANCIEROS PARA EL SEMESTRE TERMINADO EL 31 DE DICIEMBRE DE 2021', 32, 4, 146, 6, '2021-11-05', 179550.00, 2, 1, 1),
(187, 'IGP 2021', 74, 58, 58, 61, '2021-11-08', 650.00, 2, 1, 1),
(188, 'AUDITORíA DE LOS ESTADOS FINANCIEROS  PARA EL SEMESTRE FINALIZADO EL 31 DE DICIEMBRE DE 2021', 116, 145, 146, 2, '2021-11-09', 152000.00, 2, 1, 1),
(189, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 101, 144, 146, 34, '2021-11-11', 7500.00, 2, 1, 1),
(190, 'IGP AL 30-09-2021 PROYECIÓN AL 30-09-2021 Y CALCULO DEL ISLR AL 31-12-2021', 66, 147, 146, 63, '2021-11-12', 1495.00, 2, 1, 1),
(191, 'IGP  AL 30-09-2021 Y CALCULO DEL ISLR AL 31-12-2021', 167, 147, 147, 63, '2021-11-12', 300.00, 2, 1, 1),
(192, 'IGP  AL 30-09-2021', 144, 147, 147, 63, '2021-11-14', 300.00, 2, 1, 1),
(193, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 86, 150, 150, 28, '2021-11-15', 3060.00, 2, 1, 1),
(194, 'EXAMEN DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 38, 150, 150, 36, '2021-11-15', 1085.00, 2, 1, 1),
(195, 'REVISION DE LOS ESTADOS FINANCIEROS SEGúN LA NIC 29 AL 31 DE DICIEMBRE DE 2021', 38, 150, 150, 36, '2021-11-19', 1320.00, 2, 1, 1),
(196, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 39, 149, 149, 9, '2021-11-25', 14400.00, 2, 1, 1),
(197, 'AUDITORíA AL 31 DE DICIEMBRE DE 2021.', 62, 150, 150, 2, '2021-11-25', 20278.00, 2, 1, 1),
(198, 'AUDITORíA DE ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 27, 145, 146, 32, '2021-11-29', 13800.00, 2, 1, 1),
(199, 'ASISTENCIA EN LA REVISIóN DE LA DECLARACIóN DEFINITIVA DE RENTAS AL 31/12/2021', 96, 58, 149, 61, '2021-12-01', 1100.00, 2, 1, 1),
(200, 'REDACTAR Y TRAMITAR ANTE LAS AUTORIDADES NOTARIALES DOS DOCUMENTOS DE REPRESENTACIóN, DENOMINADOS PODERES.', 158, 58, 58, 140, '2021-12-01', 180.00, 2, 1, 1),
(201, 'EXAMEN DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRDE DE 2020 Y 2021', 173, 146, 146, 9, '2021-12-07', 29880.00, 2, 1, 1),
(202, 'EEXAMEN DE LOS ESTADOS FINANCIEROS DE VEN-AMERICAN, C.A. Y SUBSIDIARIA PARA EL PERIODO TERMINADO EL 30 DE JUNIO DE 2021 Y EL AñO QUE TERMINARá EL 31 DE DICIEMBRE DE 2021', 99, 150, 150, 28, '2021-12-07', 5000.00, 2, 1, 1),
(203, 'EXAMEN DE LOS ESTADOS FINANCIEROS AL 31 DE OCTUBRE DE 2021', 56, 150, 150, 28, '2021-12-08', 5400.00, 2, 1, 1),
(204, 'DDR 2021', 87, 58, 58, 61, '2021-12-09', 450.00, 2, 1, 1),
(205, 'DDR 2021', 30, 58, 58, 61, '2021-12-09', 2500.00, 2, 2, 2),
(206, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 67, 146, 146, 3, '2021-12-12', 6750.00, 2, 1, 1),
(207, 'TALLER ACTUALIZACIóN TRIBUTARIA', 173, 58, 146, 61, '2021-12-17', 450.00, 2, 1, 1),
(208, 'DDR 2021', 29, 58, 146, 58, '2021-12-17', 2500.00, 2, 2, 2),
(209, 'AUDITORíA AL 31 DE DICIEMBRE DE 2021', 98, 146, 146, 3, '2021-12-20', 3440.00, 2, 1, 1),
(210, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 52, 146, 146, 3, '2021-12-28', 1760.00, 2, 1, 1),
(211, 'AUDITORIA DE ESTADOS FINANCIEROS DE BANCO DE VENEZUELA, S.A. BANCO UNIVERSAL Y SUCURSAL CURACAO AL 31-12-2021', 31, 148, 146, 11, '2021-12-28', 200866.00, 2, 1, 1),
(212, 'AUDITORIA AL 31 DE DICIEMBRE DE 2021', 36, 149, 146, 56, '2022-01-04', 434.00, 2, 1, 1),
(213, 'AUDITORIA DE LOS ESTADOS FINANCIEROS PARA EL SEMESTRE TERMINADO EL 31 DE DICIEMBRE DE 2021', 29, 4, 146, 6, '2022-01-06', 36000.00, 3, 1, 1),
(214, 'DDR 2021', 17, 58, 58, 61, '2022-01-11', 800.00, 2, 1, 1),
(215, 'DDR 2021', 142, 58, 58, 61, '2022-01-11', 2650.00, 2, 1, 1),
(216, 'PRESENTACIóN DE LOS EF AJUSTADOS POR EFECTOS DE LA INFLACIóN BAJO PROCEDIMIENTOS  CONVENIDOS RELATIVOS A INFORMACIóN FINANCIERA (NISR 4400),', 142, 154, 144, 36, '2022-01-18', 625.00, 2, 1, 1),
(217, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 156, 146, 146, 3, '2022-01-20', 2800.00, 2, 1, 1),
(218, 'ELABORACIÓN DEL MANUAL DEL BUEN GOBIERNO CORPORATIVO', 144, 146, 146, 3, '2022-01-21', 1.00, 2, 1, 1),
(219, 'AUDITORÍA AL SEMESTRE TERMINADO EL  31 DE DICIEMBRE DE 2021', 28, 4, 146, 3, '2022-01-20', 11500.00, 2, 1, 1),
(220, 'AUDITORIA A LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 202, 150, 150, 28, '2022-01-27', 7294.00, 2, 1, 1),
(221, 'ASESORÍA FISCAL 2022', 17, 58, 58, 61, '2022-01-27', 720.00, 2, 1, 2),
(222, 'ASESORÍA FISCAL 2022', 162, 58, 144, 61, '2022-01-27', 720.00, 2, 1, 1),
(223, 'AUDITORIA A LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 203, 150, 150, 28, '2022-01-26', 1000.00, 2, 1, 1),
(224, 'REVISION Y  ANáLISIS JURíDICO DEL CONTENIDO DEL ACTA ENTREGADA POR EL SENIAT', 204, 147, 150, 63, '2022-01-26', 2520.00, 2, 1, 1),
(225, 'PLAN DE CONTINUIDAD DE NEGOCIOS', 29, 4, 146, 125, '2022-01-31', 42000.00, 3, 2, 1),
(226, 'ASESORÍA JURIDICO TRIBUTARIA EJERCICIO 2021.', 142, 147, 58, 63, '2022-01-31', 4850.00, 2, 1, 1),
(227, 'PRECIOS DE TRANSFERENCIA 2017, 2018, 2019, 2020 Y 2021', 206, 58, 146, 61, '2022-02-01', 1040.00, 2, 1, 1),
(228, 'PRECIOS DE TRANSFERENCIA 2017, 2018, 2019, 2020 Y 2021', 205, 58, 146, 61, '2022-02-01', 3120.00, 2, 1, 1),
(229, 'ASISTENCIA REVISIóN DDR 2021', 173, 58, 146, 58, '2022-02-01', 1300.00, 2, 1, 1),
(230, 'ASISTENCIA EN LA PREPARACIóN DISLR DEFINITVA, PARA EL EJERCICIO ECONóMICO QUE FINALIZó EL 31 DE DICIEMBRE DE 2021', 86, 147, 150, 63, '2022-02-01', 749.00, 2, 1, 1),
(231, 'LOANG STAFF', 143, 147, 150, 63, '2022-02-01', 6252.00, 2, 1, 1),
(232, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 207, 150, 150, 28, '2022-02-01', 7200.00, 2, 1, 1),
(233, 'INFORMES DE COMPILACIóN DE INFORMACIóN FINANCIERA (NISR 4410) Y PREPARACIóN DEL AJUSTE POR INFLACIóN DE LOS EF DESDE EL AñO 2008 HASTA EL 2020', 208, 150, 150, 28, '2022-02-02', 1800.00, 2, 1, 1),
(234, 'DDR 2021 DIRECTORAS', 74, 58, 144, 58, '2022-02-07', 300.00, 2, 1, 1),
(235, 'DDR 2021 + DIRECTORES', 149, 58, 58, 58, '2022-02-07', 1450.00, 2, 1, 1),
(236, 'PROYECTO CONTINUO ENTES GUBERNAMENTALES', 35, 58, 58, 58, '2022-02-07', 14400.00, 2, 1, 1),
(237, 'DDR 2021', 146, 58, 58, 58, '2022-02-11', 900.00, 2, 1, 1),
(238, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 151, 146, 146, 3, '2022-02-14', 500.00, 2, 1, 1),
(239, 'TRABAJO ESPECIAL AUDITORIA ACTAS DE ENTREGA', 209, 144, 144, 7, '2022-02-14', 89000.00, 2, 1, 2),
(240, 'AUDITORI FINANCIERA AL 31 DE DICIEMBRE DE 2021', 210, 146, 146, 36, '2022-02-15', 1225.00, 2, 1, 1),
(241, 'AUDITORíA AL 31 DE DICIEMBRE DEL 2021', 92, 149, 149, 9, '2022-02-18', 3120.00, 2, 1, 1),
(242, 'ASESORAMIENTO EN MATERIA TRIBUTARIA PARA EL EJERCICIO FISCAL QUE FINALIZó EL MES DE DICIEMBRE DE 2021', 211, 58, 58, 58, '2022-02-16', 350.00, 2, 1, 1),
(243, 'AUDITORIA BANCO CARONI', 212, 144, 146, 144, '2022-03-01', 1500.00, 2, 1, 1),
(244, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 147, 146, 146, 3, '2022-02-28', 1940.00, 2, 1, 1),
(245, 'CESIÓN DE CRÉDITOS FISCALES 2022', 116, 58, 58, 147, '2022-03-01', 52036.40, 1, 1, 1),
(246, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2021', 22, 150, 150, 2, '2022-03-03', 2800.00, 2, 1, 1),
(247, 'AUDITORíA PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2021', 78, 150, 150, 2, '2022-03-03', 3400.00, 2, 1, 1),
(248, 'AUDITORíA PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2021', 79, 150, 150, 2, '2022-03-03', 3800.00, 2, 1, 1),
(249, 'CESION DE CREDITOS FISCALES 2022', 213, 58, 58, 58, '2022-03-02', 52036.40, 1, 1, 1),
(250, 'PREPARACIóN DE LA DECLARACIóN DEFINITIVA DE IMPUESTO SOBRE LA RENTA, PARA EL PERíODO FISCAL FINALIZADO EL 31 DE DICIEMBRE 2021', 80, 58, 146, 58, '2022-03-07', 600.00, 2, 1, 1),
(251, 'AUDITORIA AL 31 DE DICIEMBRE DE 2021', 137, 146, 146, 56, '2022-03-09', 300.00, 2, 1, 1),
(252, 'AUDITORIA DE ESTADOS FINANCIEROS AL 31 DE OCTUBRE DE 2020', 15, 150, 150, 57, '2022-03-10', 3250.00, 2, 1, 1),
(253, 'AUDITORIA ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 54, 146, 146, 144, '2022-03-10', 10120.00, 2, 1, 1),
(254, 'DDR 2021', 57, 58, 58, 58, '2022-03-14', 700.00, 2, 1, 1),
(255, 'AUDITORIA DE ESTADOS FINANCIEROS AL 31 DE OCTUBRE DE 2021 E INFORME NISR 4410 SOBRE OPERACIONES EN MONEDA EXTRANJERA', 15, 150, 150, 57, '2022-03-15', 4420.00, 2, 1, 1),
(256, 'REVISIÓN DEL PASIVO MÁXIMO TEORICO DE LOS EMPLEADOS Y EVALUACIÓN DE ASPECTOS LEGALES.', 84, 144, 146, 13, '2022-03-17', 2300.00, 2, 1, 1),
(257, 'AUDITORíA DE LOS ESTADOS FINANCIEROS DE AVILA RAYOS X, C.A, POR EL AñO TERMINADO EL 31 DE DICIEMBRE DE 2021', 25, 150, 150, 28, '2022-03-18', 8500.00, 2, 1, 1),
(258, 'AUDITORíA DE LOS ESTADOS FINANCIEROS POR EL AñO TERMINADO EL 31 DE DICIEMBRE DE 2021', 26, 150, 150, 28, '2022-03-18', 8500.00, 2, 1, 1),
(259, 'AUDITORíA DE LOS ESTADOS FINANCIEROS POR EL AñO TERMINADO EL 31 DE DICIEMBRE DE 2021', 70, 150, 150, 28, '2022-03-18', 6800.00, 2, 1, 1),
(260, 'AUDITORíA DE LOS ESTADOS FINANCIEROS POR EL AñO TERMINADO EL 31 DE DICIEMBRE DE 2021', 72, 150, 150, 28, '2022-03-18', 8500.00, 2, 1, 1),
(261, 'AUDITORIAS OPERATIVAS Y LEVANTAMIENTO DE CONTROLES INTERNOS A LAS SUCURSALES', 84, 144, 146, 36, '2022-03-09', 2850.00, 2, 1, 1),
(262, 'AUDITORIA AL 31 DE DICIEMBRE DE 2021', 66, 7, 146, 56, '2022-03-31', 3000.00, 2, 1, 1),
(263, 'AUDITORíA AL 30 DE ABRIL DE 2022', 91, 149, 146, 9, '2022-04-27', 2550.00, 2, 1, 1),
(264, 'ASESORÍA TRIBUTARÍA CONTÍNUA 2022-2023', 148, 147, 147, 63, '2022-04-28', 11520.00, 2, 1, 1),
(265, 'REVISIóN Y ANáLISIS ESTRUCTURAL Y FUNCIONAL', 144, 146, 146, 85, '2022-04-28', 1.00, 2, 1, 1),
(266, 'REVISIóN DEL CUMPLIMIENTO DE LOS DEBERES FORMALES DE LOS AñOS 2020 Y 2021.', 86, 147, 150, 63, '2022-04-28', 1620.00, 2, 1, 1),
(267, 'SERVICIOS DE PROCEDIMIENTOS PREVIAMENTE CONVENIDOS SOBRE INFORMACIóN FINANCIERA', 29, 4, 146, 6, '2022-05-02', 29800.00, 2, 2, 1),
(268, 'ASESORIA FISCAL 2021', 102, 58, 58, 58, '2022-05-02', 1800.00, 2, 1, 1),
(269, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 215, 146, 146, 32, '2022-05-09', 1.00, 2, 1, 1),
(270, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 214, 146, 146, 12, '2022-05-09', 1.00, 2, 1, 1),
(271, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 151, 146, 146, 3, '2022-05-15', 580.00, 2, 1, 2),
(272, 'AUDITORÍA AL 31 DE DICIEMBRE DE 2021', 216, 144, 146, 12, '2022-05-12', 1.00, 2, 1, 1),
(273, 'AUDITORIA DE LOS EF AL 31 DE DICIEMBRE DE 2021', 217, 150, 150, 28, '2022-05-16', 800.00, 2, 1, 1),
(274, 'AUDITORIA AL 31 DE DICIEMBRE DE 2021', 218, 144, 146, 57, '2022-05-18', 1.00, 2, 1, 1),
(275, 'AUDITORIA  AL 31 DE DICIEMBRE DE 2020', 219, 144, 146, 57, '2022-05-18', 1.00, 2, 1, 1),
(276, 'AUDITORIA AL 31 DE DICIEMBRE DE 2021', 219, 144, 146, 57, '2022-05-18', 1.00, 2, 1, 1),
(277, 'CONSULTA EN MATERIA DE IGTF', 164, 147, 147, 63, '2022-05-20', 1800.00, 2, 1, 1),
(278, 'REVISIÓN DE DEBERES FORMALES DEL 01-01-2018 AL 31-12-2021', 167, 147, 147, 63, '2022-05-20', 1.00, 2, 1, 1),
(279, 'EVALUACION EXPEDIENTES DE PROVEEDORES Y SU CONTRATO', 29, 4, 146, 85, '2022-05-20', 80000.00, 2, 2, 1),
(280, 'AUDITORIA DE ESTADOS FINANCIEROS DE BANCO DE VENEZUELA Y SUCURSAL CURACAO AL 30-06-2022.', 31, 148, 146, 11, '2022-05-25', 922320.00, 1, 1, 1),
(281, 'AUDITORIA DE LOS ESTADOS FINANCIEROS 2017 AL 2021', 221, 144, 144, 13, '2022-05-26', 11950.00, 1, 1, 1),
(282, 'REVISION DE CARTERA DE CREDITO ACTIVA', 29, 4, 146, 6, '2022-05-27', 20000.00, 2, 1, 1),
(283, 'AJUSTE POR INFLACIóN ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2020', 173, 150, 146, 34, '2022-05-31', 2240.00, 2, 2, 1),
(284, 'AUDITORIA PARA EL SEMESTRE TERMINADO AL 30 DE JUNIO DE 2022', 222, 145, 146, 38, '2022-06-09', 20000.00, 2, 1, 1),
(285, 'AUDITORIA FINANCIERA AL SEMESTRE FINALIZADO EL 30 DE JUNIO DE 2022', 116, 145, 146, 2, '2022-06-09', 70000.00, 2, 1, 1),
(286, 'ASESORAMIENTO EN MATERIA DE REGISTRO Y CONTROLES ASOCIADOS A LOS COSTOS DE PRODUCCIóN', 56, 144, 150, 12, '2022-06-14', 4600.00, 2, 1, 1),
(287, 'PRECIOS DE TRANSFERENCIA PT99 2020 / 2021', 173, 58, 146, 58, '2022-06-17', 4670.00, 2, 1, 1),
(288, 'ASESORIA CONTINUA TRIBUTARIA 2022 / 2023', 150, 58, 58, 58, '2022-06-17', 1600.00, 2, 1, 1),
(289, 'AUDITORIA DE RIESGOS EN CUANTO AL CUMPLIMIENTO DE LA RESOLUCION 136.03 Y 136.15', 212, 146, 146, 85, '2022-06-21', 1.00, 2, 1, 1),
(290, 'DECLARACIóN Y ESTUDIO PT EJERCICIO 2021', 164, 147, 147, 63, '2022-06-22', 2660.00, 2, 1, 1),
(291, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2022', 52, 146, 146, 3, '2022-06-21', 1760.00, 2, 1, 1),
(292, 'AUDITORIA AL 31 DE AGOSTO DE 2022', 159, 149, 58, 56, '2022-06-26', 10000.00, 2, 1, 1),
(293, 'ASESORIA CONTINUA 2022-2023', 141, 58, 58, 58, '2022-06-26', 1920.00, 2, 1, 1),
(294, 'ASESORIA INTEGRAL 2022/2023', 158, 58, 58, 58, '2022-06-26', 1800.00, 2, 1, 1),
(295, 'AUDITORIA DE LOS ESTADOS FINANCIEROS  PARA EL SEMESTRE TERMINADO EL 30 DE JUNIO DE 2022', 32, 4, 146, 6, '2022-06-27', 179550.00, 2, 1, 1),
(296, 'AUDITORIA AL 31 DE DICIEMBRE DE 2020 Y 2021', 16, 146, 146, 56, '2022-07-04', 600.00, 2, 1, 1),
(297, 'AUDITORIA AL 31 DE DICIEMBRE DE 2020 Y 2021', 40, 146, 146, 56, '2022-07-04', 1400.00, 2, 1, 1),
(298, 'ASESORIA CONTINUA TRIBUTARIA 2022 / 2023', 150, 58, 58, 58, '2022-07-06', 1600.00, 2, 1, 2),
(300, 'ASESORIA  2022/2023', 100, 58, 144, 58, '2022-07-06', 1000.00, 2, 1, 1),
(301, 'ASESORIA INTEGRAL PERMANENTE 2022-2023', 223, 58, 58, 58, '2022-07-08', 840.00, 2, 1, 1),
(302, 'ASESORIA INTEGRAL PERMANENTE 2022-2023', 224, 58, 58, 58, '2022-07-07', 1680.00, 2, 1, 1),
(303, 'ASESORIA INTEGRAL PERMANENTE 2022-2023', 225, 58, 58, 58, '2022-07-08', 1680.00, 2, 1, 1),
(304, 'ASESORIA INTEGRAL PERMANENTE 2022-2023', 118, 58, 144, 58, '2022-07-08', 1800.00, 2, 1, 1),
(305, 'AUDITORIA AL 31 DE DICIEMBRE DE 2021', 140, 7, 146, 56, '2022-07-13', 3000.00, 2, 1, 1),
(306, 'AUDITORIA AL 31 D DICIEMBRE DE 2021 Y 2022', 69, 150, 150, 56, '2022-07-14', 20800.00, 2, 1, 1),
(307, 'AUDITORIA DE LOS ESTADOS FINANCIEROS PARA EL SEMESTRE TERMINADO EL 30 DE JUNIO 2022', 29, 4, 146, 6, '2022-07-18', 80000.00, 2, 1, 1),
(308, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2022', 156, 146, 146, 13, '2022-07-25', 3500.00, 2, 1, 1),
(309, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2022', 151, 146, 146, 3, '2022-07-25', 800.00, 2, 1, 1),
(310, 'AUDITORIA AL SEMESTRE TERMINADO EL  30 DE JUNIO DE 2022', 212, 145, 145, 145, '2022-08-02', 1.00, 2, 1, 1),
(311, 'UDITORíA DE LOS ESTADOS FINANCIEROS AL 30 DE JUNIO DE 2022', 226, 145, 146, 3, '2022-08-05', 800.00, 2, 1, 1),
(312, 'AUDITORÍA AL 30 DE JUNIO DE 2022', 28, 4, 146, 3, '2022-08-05', 7000.00, 2, 1, 1),
(313, 'REVISION OUTSOURCING CRUZANG', 167, 147, 147, 63, '2022-08-09', 0.00, 2, 1, 1),
(314, 'PREPARACIóN DE ESCRITO DE DESCARGOS ADMINISTRATIVOS ANTE EL SENIAT', 228, 147, 147, 63, '2022-08-09', 2800.00, 2, 1, 1),
(315, 'REVISIÓN RÁPIDA DEBERES FORMALES DE CLIENTES DE OUTSOURCING', 167, 147, 147, 63, '2022-08-09', 0.00, 1, 2, 1),
(316, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 19, 149, 149, 9, '2022-08-12', 14900.00, 2, 1, 1),
(317, 'PREPARACION DE CONTRATOS CP, REVISION L.G.P, E I.S.L.R ACOMPAñAMIENTO', 229, 147, 147, 63, '2022-08-17', 13130.00, 2, 1, 1),
(318, 'PROYECTO OUTSOURCING CONTINUO 2022-2023', 97, 146, 146, 5, '2022-08-17', 12000.00, 2, 1, 1),
(319, 'AUDITORIA DE LOS ESTADOS FINANCIEROS DE MONEYWAYS CORP ITB, C.A., PARA EL PERíODO TERMINADO AL 31 DE DICIEMBRE DE 2021', 230, 150, 150, 36, '2022-08-18', 3950.00, 2, 1, 1),
(320, 'AUDITORIA AL 31 DE DICIEMBRE DE 2020 Y 2019', 231, 7, 7, 56, '2022-08-22', 9000.00, 2, 1, 1),
(321, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 161, 149, 149, 9, '2022-08-24', 9500.00, 2, 1, 1),
(322, 'AUDITORíA DE LOS ESTADOS FINANCIEROS  PARA EL SEMESTRE FINALIZADO EL 30 DE JUNIO DE 2022.', 116, 145, 146, 2, '2022-08-24', 56500.00, 2, 1, 1),
(323, 'AUDITORIA  AL 31 DE OCTUBRE DE 2022 Y REVISIóN PRELIMINAR AL 31 DE MARZO DE 2022', 15, 150, 150, 57, '2022-08-26', 6000.00, 2, 1, 1),
(324, 'PROPUESTA PARA LA ASISTENCIA EN LA PREPARACIóN DE LA DDR  E IGP 2022.', 159, 58, 58, 58, '2022-08-29', 1850.00, 2, 1, 1),
(325, 'ASESORIA INTEGRAL PERMANENTE 2022-2023', 74, 58, 144, 58, '2022-08-29', 2050.00, 2, 1, 1),
(326, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 57, 150, 150, 28, '2022-09-01', 2550.00, 2, 1, 1),
(327, 'REVISION Y EVALUACION DEL PROCESO DE CUENTAS POR PAGAR A LOS AFILIADOS', 232, 146, 146, 85, '2022-09-07', 1.00, 2, 1, 1),
(328, 'AUDITORIA DE LOS ESTADOS FINANCIEROS PARA EL AñO 2021', 233, 4, 146, 6, '2022-09-12', 19000.00, 2, 1, 1),
(329, 'DOCUMENTACION DE PROCESOS (MANUALES,NORMATIVAS, FLUJOGRAMAS Y FORMATOS)', 144, 146, 146, 85, '2022-09-16', 1.00, 2, 1, 1),
(330, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 118, 144, 144, 56, '2022-09-20', 21000.00, 2, 1, 1),
(331, 'AUDITORIA DE ESTADOS FINANCIEROS PARA LOS AÑOS FINALIZADOS AL 31 DE CICIEMBRE DE 2016, 2017, 2018, 2019, 2020 Y 2021.', 234, 145, 148, 11, '2022-09-21', 6000.00, 2, 1, 1),
(332, 'ASESORíA TRIBUTARIA 2022-2023', 235, 58, 58, 58, '2022-09-26', 2100.00, 2, 1, 1),
(333, 'PRECIO DE TRANSFERENCIA 2021-2022', 236, 58, 58, 58, '2022-09-26', 1990.00, 2, 1, 1),
(334, 'AUDITORÍA AL 31 DE DICIEMBRE DE 2021', 88, 150, 150, 56, '2022-09-28', 2650.00, 2, 1, 1),
(335, 'AUDITORÍA AL 31 DE DICIEMBRE DE 2022', 88, 150, 150, 56, '2022-09-28', 2650.00, 2, 1, 1),
(336, 'AUDITORIA AL 31 DE DICIEMBRE DE 2021', 53, 150, 150, 36, '2022-10-04', 2340.00, 2, 1, 1),
(337, 'PREPARACIóN DE LA ESTRUCTURA DE COSTOS', 227, 144, 144, 12, '2022-10-05', 1400.00, 2, 1, 2),
(338, 'EXAMEN DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 145, 150, 150, 34, '2022-10-10', 120350.00, 2, 1, 1),
(339, 'AUDITORIA AL 31 DE OCTUBRE DE 2022', 56, 150, 150, 28, '2022-10-13', 5400.00, 2, 1, 1),
(340, 'ASISTENCIA EN LA PREPARACIóN DE LA DECLARACIóN DE IGP AL 30 DE SEPTIEMBRE DE 2022', 146, 58, 58, 58, '2022-10-18', 400.00, 2, 1, 1),
(341, 'AUDITORIA FINANCIERA  AL 31 DE DICIEMBRE DE 2019 Y 2018', 240, 145, 146, 9, '2022-10-18', 110000.00, 2, 1, 1),
(342, 'ASISTENCIA EN LA REVISIóN DE LOS DEBERES FORMALES TRIBUTARIOS 2021-2022', 166, 58, 58, 58, '2022-10-19', 2400.00, 2, 1, 1),
(343, 'SERVICIOS PROFESIONALES TRIBUTARIOS ASESORíA ANUAL 2022-2023', 78, 58, 58, 58, '2022-10-19', 7000.00, 2, 1, 1),
(344, 'AX COMPLIANCE 2022-2023', 235, 58, 58, 58, '2022-10-19', 7520.00, 2, 1, 1),
(345, 'REVISION RRHH', 149, 58, 58, 58, '2022-10-20', 1200.00, 2, 1, 1),
(346, 'ASISTENCIA EN LA DETERMINACIóN DE MULTAS E INTERESES DE MORA', 241, 58, 58, 58, '2022-10-21', 480.00, 2, 1, 1),
(347, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 207, 150, 150, 28, '2022-10-25', 7200.00, 2, 1, 1),
(348, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 203, 150, 150, 28, '2022-10-25', 1500.00, 2, 1, 1),
(349, 'REVISION, ANALISIS Y ACTUALIZACION  DE TODOA  LA DOCUMENTACION LEGAL DE ACUERDO CON EL C.COM.', 158, 58, 58, 140, '2022-10-26', 1850.00, 2, 1, 1),
(350, 'AUDITORíA DE LOS ESTADOS FINANCIEROS DE PETROSERVICIOS MORICHAL S.A., PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2021.', 242, 145, 146, 5, '2022-11-02', 1120.00, 2, 1, 1),
(351, 'IGP AL 30-09-2022 PROYECIÓN AL 31-10-2022 Y CALCULO DEL ISLR AL 31-12-2022', 66, 147, 146, 63, '2022-11-08', 1830.00, 2, 1, 1),
(352, 'GP 2019/2020/2021/2022-RDF 2022-DDR 2022', 228, 147, 147, 63, '2022-11-08', 3300.00, 2, 1, 1),
(353, 'ASISTENCIA EN LA PREPARACIÓN DE LA DECLARACIÓN DE IMPUESTO A LOS GRANDES PATRIOMNIOS AL 30/09/2022', 42, 147, 147, 63, '2022-11-08', 3300.00, 2, 1, 1),
(354, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 66, 7, 146, 56, '2022-11-08', 4000.00, 2, 1, 1),
(355, 'AUDITORIA DE ESTADOS FINANCIEROS DE BANCO DE VENEZUELA, S.A. BANCO UNIVERSAL Y SUCURSAL CURACAO AL 31-12-2022', 31, 148, 145, 11, '2022-11-08', 1920000.00, 1, 1, 1),
(356, 'ACTUALIZACION Y ANALISIS DE LA CONTABILIDAD Y LOS IMPUESTOS DEL AÑO 2022', 158, 58, 58, 119, '2022-11-10', 1.00, 2, 2, 1),
(357, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE AGOSTO DE 2022', 166, 7, 146, 56, '2022-11-11', 4000.00, 2, 1, 1),
(358, 'INFORMES DE COMPILACIóN DE INFORMACIóN FINANCIERA (NISR 4410) AL 31 DE DICIEMBRE DE 2021', 208, 150, 150, 28, '2022-11-13', 1100.00, 2, 1, 1),
(359, 'SERVICIOS TRIBUTARIOS/ IGP 2022/ PROYECCIÓN DE ISLR 2022- ASISTENCIA CONTINUA Y PERMANENTE EN MATERIA TRIBUTARIA-ISLR DIFERIDO', 243, 147, 147, 63, '2022-11-14', 6070.00, 2, 1, 1),
(360, 'AUDITORIA DE LOS ESTADOS FINANCIEROS  PARA EL SEMESTRE TERMINADO EL 31 DE DICIEMBRE DE 2022', 32, 4, 146, 6, '2022-11-14', 179550.00, 2, 1, 1),
(361, 'SERVICIOS PROFESIONALES EN MATERIA JURíDICO TRIBUTARIA PARA EL ACOMPAñAMIENTO EN LA IMPLEMENTACIóN DE UN CONTRATO DE ASOCIACIóN EN PARTICIPACIóN', 56, 147, 150, 63, '2022-11-14', 500.00, 2, 1, 1),
(362, 'IMPUESTO A LOS GRANDES PATRIMONIOS AL 30/09/22 Y CALCULO DEL ISLR AL 31-12-2022', 167, 147, 147, 63, '2022-11-14', 300.00, 2, 1, 1),
(363, 'ASISTENCIA EN LA PREPARACIóN DE LA DECLARACIóN DE IMPUESTO A LOS GRANDES PATRIMONIOS AL 30 DE SEPTIEMBRE DE 2022', 144, 147, 147, 63, '2022-11-14', 400.00, 2, 1, 1),
(364, 'ASISTENCIA EN LA PREPARACIóN DE LA DECLARACIóN DE IMPUESTO A LOS GRANDES PATRIMONIOS 2022', 102, 58, 58, 203, '2022-11-15', 300.00, 2, 1, 1),
(365, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2022', 62, 150, 146, 2, '2022-11-17', 25050.00, 2, 1, 1),
(366, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2022', 22, 150, 145, 2, '2022-11-18', 3360.00, 2, 1, 1),
(367, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2022', 78, 150, 145, 2, '2022-11-18', 4080.00, 2, 1, 1),
(368, 'AUDITORíA DE LOS ESTADOS FINANCIEROS , PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2022', 79, 150, 145, 2, '2022-11-18', 4560.00, 2, 1, 1),
(369, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 202, 150, 150, 56, '2022-11-25', 8400.00, 3, 1, 1),
(370, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 67, 146, 150, 3, '2022-11-25', 7750.00, 2, 1, 1),
(371, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 29, 145, 146, 6, '2022-11-25', 120000.00, 2, 1, 1),
(372, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 222, 145, 146, 38, '2022-11-28', 28000.00, 2, 1, 1),
(373, 'REVISIóN E INFORME DEL COMISARIO', 127, 146, 146, 57, '2022-11-29', 6000.00, 2, 1, 1),
(374, 'ASISTENCIA EN LA PREPARACIóN DE LA DECLARACIóN DEFINITIVA DE RENTAS 2022', 80, 149, 147, 58, '2022-12-02', 2000.00, 2, 1, 1),
(375, 'REVISIóN DDR 2022', 96, 58, 147, 58, '2022-12-02', 1300.00, 2, 1, 1),
(376, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 30 DE SEPTIEMBRE DE 2022', 70, 150, 150, 56, '2022-12-06', 8000.00, 2, 1, 1),
(377, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 39, 149, 145, 9, '2022-12-05', 16000.00, 2, 1, 1),
(378, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 101, 144, 146, 12, '2022-12-07', 17000.00, 2, 1, 1),
(379, 'OUTSOURCING DE CUMPLIMIENTO TRIBUTARIO 2022-2023', 104, 58, 58, 58, '2022-12-13', 19102.00, 2, 1, 1),
(380, 'ASISTENCIA EN LA PREPARACIóN DE LA DECLARACIóN DE IMPUESTO A LOS GRANDES PATRIMONIOS AL 30-09-2022', 227, 147, 58, 63, '2022-12-13', 400.00, 2, 1, 1),
(381, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 244, 144, 146, 9, '2022-12-13', 19600.00, 2, 1, 1),
(382, 'ASISTENCIA EN LA PREPARACIóN DE LA DECLARACIóN DE IMPUESTO A LOS GRANDES PATRIMONIOS 2021', 146, 58, 147, 58, '2022-12-13', 400.00, 2, 1, 1),
(383, 'AUDITORIA DE LOS ESTADOS FINANCIEROS DEL GRUPO AMA DE CASA Y DISTEPAL AL 31 DE DICIEMBRE DE 2022', 37, 144, 146, 13, '2022-12-13', 9500.00, 2, 1, 1),
(384, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 86, 150, 146, 28, '2022-12-15', 3200.00, 2, 1, 1),
(385, 'ASESORíA TRIBUTARIA 2022-2023', 53, 58, 147, 58, '2022-12-15', 1180.00, 2, 1, 1),
(386, 'OUTSOURCING PAYROLL 2023', 235, 58, 58, 58, '2022-12-16', 1800.00, 2, 2, 1),
(387, 'ASISTENCIA EN LA PREPARACIóN DE LA DECLARACIóN DEFINITIVA DE RENTAS, PARA EL EJERCICIO FISCAL QUE FINALIZARá EL 31 DE DICIEMBRE DE 2022.', 87, 58, 147, 58, '2022-12-19', 550.00, 2, 1, 1),
(388, 'AUDITORIA DE RIESGOS EN CUANTO AL CUMPLIMIENTO DE LA RESOLUCIóN N° 136,03 Y N° 136.15', 212, 145, 146, 85, '2022-12-19', 1.00, 2, 1, 1),
(389, 'ASESORÍA TRIBUTARIA 2022-2023', 57, 58, 147, 58, '2022-12-20', 900.00, 2, 1, 1),
(390, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 28, 145, 146, 3, '2022-12-21', 7000.00, 2, 1, 1),
(391, 'AUDITORÍA A LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 52, 146, 145, 13, '2022-12-21', 1760.00, 2, 1, 1),
(392, 'AUDITORÍA A LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 151, 146, 145, 3, '2022-12-21', 1000.00, 2, 1, 1),
(393, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 36, 149, 150, 56, '2022-12-22', 6000.00, 1, 1, 1),
(394, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 245, 144, 146, 9, '2022-12-22', 3900.00, 2, 1, 1),
(395, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 72, 150, 149, 56, '2022-12-27', 10000.00, 2, 1, 1),
(396, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 26, 150, 149, 56, '2022-12-27', 12000.00, 2, 1, 1),
(397, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 25, 150, 149, 56, '2022-12-27', 10000.00, 2, 1, 1),
(398, 'PAQUETE DE REPORTE CONSORCIO CREDICARD', 145, 150, 146, 34, '2023-01-03', 12180.00, 2, 1, 1),
(399, 'AJUSTE POR INFLACIóN ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 173, 150, 150, 34, '2023-01-09', 3840.00, 2, 2, 1),
(400, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2021', 163, 146, 146, 34, '2023-01-09', 9000.00, 2, 1, 1),
(401, 'ASISTENCIA EN LA PREPARACIóN DE LA DECLARACIóN DEFINITIVA DE RENTAS 2022', 17, 58, 147, 58, '2023-01-11', 1200.00, 2, 1, 1),
(402, 'ASISTENCIA EN LA PREPARACIóN DE LA DECLARACIóN DEFINITIVA DE RENTAS, PARA EL EJERCICIO FISCAL QUE FINALIZARá EL 31 DE DICIEMBRE DE 2022.', 29, 58, 147, 58, '2023-01-17', 3000.00, 2, 1, 1),
(403, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 246, 149, 146, 9, '2023-01-19', 16600.00, 2, 1, 1),
(404, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 92, 149, 148, 9, '2023-01-25', 2860.00, 2, 1, 1),
(405, 'PROYECCIÓN POSIBLE GASTO DE ISLR AL 31-12-22/ REVISIÓN DE LA DECLARACIÓN DEFINTIVA DE RENTAS AL 31-12-2022/ ASESORÍA CONTINUA', 207, 147, 147, 63, '2023-01-31', 4128.00, 2, 1, 1),
(406, 'PREPARACIóN DE LA DECLARACIóN DEFINITIVA DE RENTAS PARA EL EJERCICIO FINALIZADO EL 31 DE DICIEMBRE DE 2022', 95, 147, 147, 63, '2023-02-01', 520.00, 2, 1, 1),
(407, 'SERVICIOS PROFESIONALES EN MATERIA DE AUDITORIA FORENSE', 85, 144, 146, 144, '2020-02-03', 1.00, 3, 1, 1),
(408, 'AUDITORIA  DE PCLC/FT/FPADM AL 31 DE DICIEMBRE DE 2022', 248, 146, 145, 3, '2023-02-16', 900.00, 2, 1, 1),
(409, 'AUDITORíA PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2022 / INFORME DE PREVENCIóN  LC/FT/FPADM', 249, 145, 146, 2, '2023-02-17', 9500.00, 2, 1, 1),
(410, 'AUDITORíA  AL 31 DE DICIEMBRE DE 2022', 247, 145, 148, 3, '2023-02-22', 1200.00, 2, 1, 1),
(411, 'CONSULTA EN MATERIA TRIBUTARIA IMPUESTO AL VALOR AGREGADO.', 88, 147, 150, 63, '2023-02-23', 800.00, 2, 1, 1),
(412, 'REVISIóN DE LO DISPUESTO EN LA RESOLUCION 18/12/01 DEL BANCO CENTRAL DE VENEZUELA', 250, 149, 146, 34, '2023-02-23', 6700.00, 2, 1, 1),
(413, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 52, 146, 145, 13, '2023-03-03', 1940.00, 2, 1, 2),
(414, 'AUDITORÍA A LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 212, 145, 145, 2, '2023-03-07', 2520.00, 2, 1, 1),
(415, 'AUDITORíA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 251, 146, 145, 13, '2023-03-09', 1940.00, 2, 1, 1),
(416, 'AUDITORíA PARA EL AñO FINALIZADO EL 31 DE DICIEMBRE DE 2022 / INFORME DE PREVENCIóN  LC/FT/FPADM', 252, 144, 146, 2, '2023-03-09', 4800.00, 2, 1, 1),
(417, 'CONSULTA EN MATERIA TRIBUTARIA', 253, 147, 147, 63, '2023-03-13', 4000.00, 2, 1, 1),
(418, 'AUDITORíA DE ESTADOS FINANCIEROS ALL 30 DE JUNIO DE 2022, 2021 Y 2020.', 254, 145, 148, 10, '2023-03-15', 30000.00, 2, 1, 1),
(419, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 54, 146, 4, 3, '2023-03-20', 13180.00, 2, 1, 1),
(420, 'AUDITORÍA AL 31/12/2022 SOBRE LA EFICACIA DEL SISTEMA DE ADMINISTRACIÓN DE RIESGOS DE LC/FT/FPADM Y OTROS ILICITOS', 52, 146, 145, 3, '2023-03-20', 600.00, 2, 1, 1),
(421, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022', 140, 150, 146, 56, '2023-03-28', 3500.00, 2, 1, 1),
(422, 'OUTSOURCING DE CONTABILIDAD E IMPUESTOS DURANTE EL  AñO 2023', 158, 58, 58, 116, '2023-03-28', 5700.00, 2, 2, 1),
(423, 'LOAN STAFF 2023', 143, 147, 147, 63, '2023-04-11', 6250.00, 2, 1, 1),
(424, 'AUDITORIA DE LOS ESTADOS FINANCIEROS Y EMISION DE INFORME PARA EL SEMESTRE TERMINADO EL 30 DE JUNIO DE 2023', 32, 146, 145, 6, '2023-04-21', 179550.00, 2, 1, 1),
(425, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 31 DE DICIEMBRE DE 2022', 99, 150, 146, 28, '2023-04-20', 4000.00, 2, 1, 1),
(426, 'ENCARGO DE PROCEDIMEINTOS ACORDADOS REQUERIDOS POR CONSORCIO CREDICARD, C.A. DE ACUERDO CON LA NORMA INTERNACIONAL DE SERVICIOS RELACIONADOS (NISR 4400)', 145, 150, 146, 34, '2023-05-02', 11000.00, 2, 1, 1),
(427, 'EXAMEN DE LOS ESTADOS FINANCIEROS PARA LOS PERíODOS TERMINADOS AL 31 DE DICIEMBRE DE 2018, 2019, 2020, 2021 Y 2022', 257, 144, 146, 9, '2023-05-03', 70622.00, 2, 1, 1),
(428, 'ENTES GUBERNAMENTALES 2023-2024', 104, 58, 147, 58, '2023-05-03', 16548.00, 2, 1, 1),
(429, 'TRABAJO ESPECIAL DE ADOPCIÓN, DEL PLAN DE CUENTA VEN-NIF', 56, 144, 150, 28, '2023-05-05', 1680.00, 2, 2, 1),
(430, 'ASESORÍA TRIBUTARIA CONTINUA 2023-2024', 148, 147, 147, 63, '2023-05-11', 5760.00, 2, 1, 1),
(431, 'ASISTENCIA EN LA PREPARACIóN DE LA DECLARACIóN DEFINITIVA DE RENTAS 2022', 227, 147, 58, 63, '2023-05-15', 450.00, 2, 1, 1),
(432, 'REVISIÓN DE DEBERES FORMALES DEL 01 DE ENERO DEL 2022 AL 31 DE MARZO DE 2023', 144, 147, 58, 63, '2023-05-15', 0.00, 2, 1, 1),
(433, 'REVISIÓN DE DEBERES FORMALES DEL 01 DE ENERO DEL 2022 AL 31 DE MARZO DE 2023', 167, 147, 58, 63, '2023-05-15', 0.00, 2, 2, 1),
(434, 'AUDITORIA DE LOS ESTADOS FINANCIEROS AL 30 DE ABRIL DE 2023', 91, 149, 145, 9, '2023-05-16', 2750.00, 2, 1, 1),
(435, 'UDITORIA DE ESTADOS FINANCIEROS DE BANCO DE VENEZUELA, S.A. BANCO UNIVERSAL Y SUCURSAL CURACAO AL 30-06-2023.', 31, 145, 148, 11, '2023-05-23', 165000.00, 2, 1, 1),
(436, 'AUDITORíA DE LOS ESTADOS FINANCIEROS PARA EL SEMESTRE QUE FINALIZARá EL 30 DE JUNIO DE 2023', 222, 145, 146, 38, '2023-05-24', 33600.00, 2, 1, 1),
(437, 'EXAMEN DE LOS ESTADOS FINANCIEROS DE MONEYWAYS CORP ITFB, C.A. AL 31 DE DICIEMBRE DE 2022', 230, 150, 146, 34, '2023-05-24', 12000.00, 2, 1, 1),
(438, 'ENCARGO DE PROCEDIMIENTOS ACORDADOS NIA 4400', 258, 150, 146, 34, '2023-05-25', 6400.00, 2, 1, 1),
(439, 'CONSULTA TRIBUTARIA IVA IGTF', 54, 58, 147, 58, '2023-05-30', 280.00, 2, 1, 1),
(440, 'AUDITORIA AL 31 DE DICIEMBRE DE 2022 Y 2021', 231, 7, 146, 56, '2023-05-30', 4955.00, 2, 1, 1),
(441, 'REVISIÓN DE DE LOS ESTADOS FIANANCIEROS AL 31 DE DICIEMBRE DE 2022 (NISR 4400)', 259, 149, 146, 56, '2023-05-30', 2750.00, 2, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `projects_departments_assigned`
--

CREATE TABLE `projects_departments_assigned` (
  `department_assigned_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `hours_assigned` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `projects_departments_assigned`
--

INSERT INTO `projects_departments_assigned` (`department_assigned_id`, `department_id`, `project_id`, `manager_id`, `hours_assigned`) VALUES
(1, 1, 1, 3, 244),
(2, 1, 2, 3, 2352),
(3, 3, 2, 125, 200),
(4, 2, 2, 58, 200),
(5, 6, 2, 85, 200),
(6, 17, 2, 140, 8),
(7, 1, 3, 3, 274),
(8, 1, 4, 9, 350),
(9, 1, 5, 3, 238),
(10, 1, 6, 5, 600),
(11, 1, 7, 9, 500),
(12, 1, 8, 148, 7630),
(13, 3, 8, 125, 350),
(15, 2, 8, 58, 250),
(16, 1, 9, 148, 430),
(17, 6, 9, 85, 150),
(18, 3, 9, 125, 120),
(19, 5, 10, 84, 4000),
(20, 2, 11, 58, 544),
(21, 2, 12, 58, 150),
(22, 2, 13, 58, 50),
(23, 2, 14, 58, 254),
(24, 2, 15, 58, 140),
(25, 2, 16, 58, 108),
(26, 2, 17, 58, 240),
(27, 2, 18, 58, 240),
(28, 2, 19, 58, 315),
(29, 1, 20, 58, 100),
(30, 1, 21, 34, 224),
(31, 1, 22, 58, 1300),
(32, 1, 23, 58, 224),
(33, 1, 24, 58, 180),
(34, 1, 25, 6, 300),
(35, 2, 25, 58, 60),
(36, 3, 25, 125, 40),
(37, 1, 28, 9, 300),
(38, 2, 28, 58, 50),
(39, 1, 29, 3, 1000),
(40, 6, 30, 85, 400),
(41, 1, 31, 36, 500),
(42, 1, 32, 57, 1012),
(43, 1, 33, 57, 102),
(44, 1, 34, 57, 68),
(45, 1, 35, 57, 153),
(46, 17, 36, 140, 150),
(47, 1, 36, 34, 600),
(48, 2, 37, 58, 98),
(49, 2, 38, 58, 62),
(50, 2, 39, 58, 100),
(51, 2, 40, 58, 65),
(52, 1, 41, 2, 3735),
(53, 2, 41, 58, 560),
(54, 6, 41, 85, 550),
(55, 3, 41, 125, 420),
(56, 1, 42, 6, 4490),
(57, 2, 42, 58, 400),
(58, 3, 42, 125, 400),
(59, 6, 42, 85, 400),
(60, 17, 42, 140, 10),
(61, 1, 43, 36, 600),
(62, 2, 44, 58, 36),
(63, 2, 45, 58, 36),
(64, 6, 46, 85, 400),
(65, 7, 47, 105, 250),
(66, 1, 48, 9, 204),
(67, 1, 49, 9, 230),
(68, 1, 50, 9, 800),
(69, 2, 51, 58, 100),
(70, 1, 52, 57, 170),
(71, 2, 53, 58, 90),
(72, 2, 54, 58, 55),
(73, 2, 55, 58, 90),
(74, 2, 56, 58, 55),
(75, 2, 57, 58, 90),
(76, 1, 58, 57, 102),
(77, 1, 59, 34, 844),
(78, 1, 60, 34, 60),
(79, 2, 61, 58, 266),
(80, 2, 62, 58, 30),
(81, 1, 62, 3, 360),
(82, 1, 63, 144, 1500),
(83, 1, 64, 57, 92),
(84, 2, 65, 58, 528),
(85, 2, 66, 58, 1296),
(86, 2, 67, 58, 288),
(87, 1, 68, 56, 300),
(88, 1, 69, 56, 650),
(89, 1, 70, 56, 300),
(90, 1, 71, 56, 300),
(91, 1, 72, 56, 300),
(92, 1, 73, 28, 324),
(93, 1, 74, 148, 400),
(94, 3, 74, 125, 50),
(95, 6, 74, 85, 150),
(96, 1, 75, 56, 300),
(97, 1, 76, 56, 40),
(98, 1, 77, 6, 300),
(99, 2, 77, 58, 60),
(100, 3, 77, 125, 40),
(101, 1, 78, 3, 214),
(102, 2, 78, 58, 30),
(103, 1, 79, 9, 260),
(109, 1, 81, 3, 1192),
(110, 2, 81, 58, 200),
(111, 3, 81, 125, 200),
(112, 6, 81, 85, 200),
(113, 17, 81, 140, 8),
(114, 1, 82, 3, 215),
(115, 2, 82, 58, 30),
(116, 2, 83, 58, 24),
(117, 2, 84, 58, 48),
(118, 2, 85, 58, 80),
(119, 2, 86, 58, 80),
(120, 2, 87, 58, 80),
(121, 1, 88, 57, 650),
(122, 1, 89, 56, 200),
(123, 1, 90, 2, 3110),
(124, 2, 90, 58, 320),
(125, 6, 90, 85, 470),
(126, 3, 90, 125, 320),
(127, 1, 91, 9, 80),
(128, 1, 92, 57, 330),
(129, 1, 93, 56, 300),
(130, 3, 93, 125, 50),
(131, 2, 93, 63, 50),
(132, 2, 94, 58, 160),
(133, 2, 95, 58, 270),
(134, 2, 96, 58, 400),
(135, 5, 97, 5, 5000),
(136, 1, 98, 34, 180),
(137, 1, 99, 2, 613),
(138, 2, 100, 58, 40),
(139, 2, 101, 58, 40),
(140, 2, 102, 58, 100),
(141, 2, 103, 58, 120),
(142, 2, 104, 63, 674),
(143, 2, 105, 58, 50),
(144, 1, 106, 34, 500),
(145, 1, 107, 34, 500),
(146, 2, 108, 58, 300),
(147, 1, 108, 148, 7430),
(148, 3, 108, 125, 400),
(149, 6, 108, 85, 100),
(150, 2, 109, 63, 400),
(151, 2, 110, 58, 48),
(152, 1, 111, 34, 2330),
(153, 3, 111, 125, 250),
(154, 2, 111, 147, 180),
(155, 6, 111, 85, 240),
(156, 2, 112, 58, 25),
(157, 2, 113, 58, 400),
(158, 1, 114, 56, 400),
(159, 1, 115, 9, 100),
(160, 1, 116, 3, 190),
(161, 2, 116, 58, 30),
(162, 2, 117, 58, 36),
(163, 2, 118, 58, 30),
(164, 1, 119, 145, 2485),
(165, 2, 119, 58, 340),
(166, 3, 119, 125, 300),
(167, 6, 119, 85, 300),
(168, 1, 120, 2, 112),
(169, 1, 121, 2, 136),
(170, 1, 122, 2, 152),
(171, 1, 123, 6, 1520),
(172, 2, 123, 58, 180),
(173, 3, 123, 125, 150),
(174, 6, 123, 85, 140),
(175, 17, 123, 140, 10),
(176, 2, 124, 58, 461),
(177, 1, 125, 9, 204),
(178, 1, 126, 28, 500),
(179, 1, 127, 28, 400),
(180, 6, 128, 85, 1520),
(181, 3, 128, 125, 80),
(182, 1, 129, 9, 180),
(183, 3, 88, 125, 100),
(184, 2, 92, 58, 70),
(185, 1, 130, 11, 7430),
(186, 2, 130, 63, 300),
(187, 3, 130, 125, 400),
(188, 6, 130, 85, 100),
(189, 1, 131, 6, 4990),
(190, 3, 131, 125, 450),
(191, 6, 131, 85, 600),
(192, 17, 131, 140, 10),
(193, 2, 131, 58, 450),
(194, 1, 132, 32, 400),
(195, 6, 132, 85, 150),
(196, 3, 132, 125, 50),
(197, 1, 133, 6, 1920),
(198, 2, 133, 58, 180),
(199, 3, 133, 125, 150),
(200, 6, 133, 85, 140),
(201, 17, 133, 140, 10),
(202, 1, 134, 2, 2485),
(203, 2, 134, 58, 340),
(204, 3, 134, 125, 300),
(205, 6, 134, 85, 300),
(206, 1, 135, 13, 720),
(207, 2, 136, 58, 124),
(208, 2, 137, 58, 5),
(209, 2, 138, 58, 120),
(210, 1, 139, 3, 224),
(211, 1, 140, 3, 80),
(212, 2, 141, 58, 30),
(213, 1, 142, 36, 123),
(214, 3, 142, 129, 80),
(215, 1, 143, 3, 80),
(216, 2, 135, 58, 80),
(217, 1, 144, 3, 1192),
(218, 2, 144, 58, 200),
(219, 3, 144, 125, 200),
(220, 6, 144, 85, 200),
(221, 17, 144, 140, 8),
(222, 2, 145, 58, 100),
(223, 2, 139, 58, 16),
(224, 6, 146, 85, 450),
(225, 1, 147, 9, 1300),
(226, 2, 148, 58, 30),
(227, 2, 149, 58, 30),
(228, 2, 150, 58, 120),
(229, 1, 151, 3, 160),
(230, 1, 152, 36, 1530),
(231, 2, 152, 58, 100),
(232, 2, 153, 58, 160),
(233, 1, 154, 56, 440),
(234, 2, 154, 58, 40),
(235, 6, 154, 85, 80),
(236, 1, 155, 9, 500),
(237, 1, 156, 34, 285),
(238, 1, 157, 28, 272),
(239, 1, 158, 6, 1920),
(240, 2, 158, 58, 180),
(241, 3, 158, 125, 150),
(242, 6, 158, 85, 140),
(243, 17, 158, 140, 10),
(244, 1, 159, 9, 496),
(245, 1, 160, 34, 300),
(246, 1, 161, 144, 100),
(247, 1, 162, 9, 310),
(248, 2, 162, 63, 75),
(249, 1, 163, 36, 750),
(250, 2, 163, 58, 100),
(251, 2, 164, 58, 800),
(252, 2, 165, 58, 350),
(253, 2, 166, 58, 400),
(254, 1, 167, 34, 1000),
(255, 1, 168, 36, 1600),
(256, 1, 169, 34, 300),
(257, 1, 170, 56, 420),
(258, 2, 171, 58, 90),
(259, 2, 172, 58, 250),
(260, 2, 173, 58, 500),
(261, 1, 174, 57, 430),
(262, 1, 175, 57, 200),
(263, 1, 176, 57, 150),
(264, 1, 177, 34, 3140),
(265, 2, 177, 63, 220),
(266, 6, 177, 85, 340),
(267, 3, 177, 125, 200),
(268, 17, 177, 140, 150),
(269, 2, 178, 58, 120),
(270, 1, 179, 57, 150),
(271, 2, 180, 63, 160),
(272, 2, 181, 63, 274),
(273, 2, 182, 63, 316),
(274, 2, 183, 63, 190),
(275, 2, 184, 63, 108),
(276, 1, 185, 56, 310),
(277, 6, 185, 85, 40),
(278, 1, 186, 6, 5290),
(279, 2, 186, 58, 400),
(280, 3, 186, 125, 400),
(281, 17, 186, 140, 10),
(282, 6, 186, 85, 400),
(283, 2, 187, 58, 60),
(284, 1, 188, 2, 2485),
(285, 2, 188, 58, 340),
(286, 6, 188, 85, 300),
(287, 3, 188, 125, 300),
(288, 1, 189, 34, 650),
(289, 2, 189, 58, 130),
(290, 2, 190, 63, 128),
(291, 2, 191, 63, 40),
(292, 2, 192, 63, 20),
(293, 1, 193, 28, 170),
(294, 1, 194, 36, 844),
(295, 1, 195, 36, 110),
(296, 1, 196, 9, 800),
(297, 1, 197, 2, 613),
(298, 1, 198, 32, 400),
(299, 6, 198, 85, 150),
(300, 3, 198, 125, 50),
(301, 2, 199, 58, 80),
(302, 6, 163, 85, 150),
(303, 17, 200, 192, 10),
(304, 1, 201, 9, 1200),
(305, 2, 201, 58, 80),
(306, 6, 201, 85, 320),
(307, 3, 201, 125, 60),
(308, 1, 202, 28, 344),
(309, 1, 203, 28, 600),
(310, 2, 204, 58, 80),
(311, 2, 205, 58, 400),
(312, 1, 206, 3, 360),
(313, 2, 206, 63, 30),
(314, 2, 207, 58, 16),
(315, 2, 208, 58, 400),
(316, 1, 209, 3, 210),
(317, 2, 209, 58, 30),
(318, 1, 210, 3, 150),
(319, 2, 210, 58, 30),
(320, 1, 211, 11, 7230),
(321, 2, 211, 63, 270),
(322, 6, 211, 85, 100),
(323, 3, 211, 125, 400),
(324, 1, 212, 56, 250),
(325, 1, 213, 6, 1920),
(326, 2, 213, 58, 180),
(327, 3, 213, 125, 150),
(328, 6, 213, 85, 140),
(329, 17, 213, 140, 10),
(330, 2, 214, 58, 70),
(331, 2, 215, 58, 200),
(332, 1, 216, 36, 25),
(333, 1, 217, 3, 240),
(334, 2, 217, 63, 30),
(335, 1, 218, 3, 200),
(336, 17, 218, 192, 200),
(337, 1, 219, 3, 1392),
(338, 2, 219, 58, 200),
(339, 3, 219, 125, 200),
(340, 6, 219, 85, 200),
(341, 17, 219, 140, 8),
(342, 1, 220, 28, 482),
(343, 2, 221, 58, 720),
(344, 2, 222, 58, 40),
(345, 1, 223, 28, 67),
(346, 2, 224, 63, 100),
(347, 3, 225, 125, 260),
(348, 2, 226, 63, 240),
(349, 2, 227, 58, 70),
(350, 2, 228, 58, 208),
(351, 2, 229, 58, 100),
(352, 2, 230, 63, 50),
(353, 2, 231, 63, 674),
(354, 1, 232, 28, 400),
(355, 1, 233, 28, 100),
(356, 2, 234, 58, 20),
(357, 2, 235, 58, 100),
(358, 2, 236, 58, 480),
(359, 7, 236, 105, 480),
(360, 2, 237, 58, 50),
(361, 1, 238, 3, 270),
(362, 1, 239, 7, 2600),
(363, 2, 239, 58, 600),
(364, 3, 239, 125, 600),
(365, 6, 239, 85, 600),
(366, 17, 239, 140, 600),
(367, 1, 240, 36, 125),
(368, 3, 240, 125, 16),
(369, 17, 240, 192, 16),
(370, 1, 241, 9, 260),
(371, 2, 242, 58, 40),
(372, 3, 243, 125, 50),
(373, 6, 243, 85, 30),
(374, 1, 243, 145, 40),
(375, 1, 244, 3, 418),
(376, 2, 244, 63, 30),
(377, 2, 245, 147, 520),
(378, 1, 246, 2, 140),
(379, 1, 247, 2, 170),
(380, 1, 248, 2, 190),
(381, 2, 249, 58, 520),
(382, 2, 250, 58, 40),
(383, 1, 251, 56, 40),
(384, 1, 252, 57, 250),
(385, 1, 253, 144, 350),
(386, 2, 253, 58, 50),
(387, 2, 254, 58, 50),
(388, 1, 255, 57, 340),
(389, 1, 256, 36, 12),
(390, 7, 256, 105, 116),
(391, 1, 257, 28, 500),
(392, 1, 258, 28, 500),
(393, 1, 259, 28, 400),
(394, 1, 260, 28, 500),
(395, 1, 261, 36, 280),
(396, 1, 262, 56, 500),
(397, 1, 263, 9, 212),
(398, 2, 264, 63, 600),
(399, 6, 265, 85, 560),
(400, 2, 266, 63, 120),
(401, 1, 267, 6, 600),
(402, 2, 268, 58, 160),
(403, 1, 269, 32, 200),
(404, 1, 270, 12, 200),
(405, 1, 271, 3, 270),
(406, 1, 272, 12, 200),
(407, 1, 273, 28, 40),
(408, 1, 274, 57, 200),
(409, 1, 275, 57, 200),
(410, 1, 276, 57, 200),
(411, 2, 277, 63, 90),
(412, 2, 278, 63, 304),
(413, 6, 279, 85, 790),
(414, 17, 279, 140, 490),
(415, 1, 279, 4, 20),
(416, 1, 280, 11, 7090),
(417, 2, 280, 147, 340),
(418, 6, 280, 85, 220),
(419, 3, 280, 125, 350),
(420, 1, 281, 13, 220),
(421, 2, 281, 58, 80),
(422, 1, 282, 6, 400),
(423, 2, 238, 63, 30),
(424, 1, 283, 34, 140),
(425, 1, 284, 38, 1140),
(426, 2, 284, 58, 150),
(427, 3, 284, 125, 150),
(428, 6, 284, 85, 150),
(429, 17, 284, 140, 10),
(430, 1, 285, 2, 1719),
(431, 2, 285, 58, 150),
(432, 3, 285, 125, 130),
(433, 6, 285, 85, 130),
(434, 1, 225, 4, 40),
(435, 1, 286, 12, 400),
(436, 2, 287, 58, 310),
(437, 2, 288, 58, 96),
(438, 6, 289, 85, 120),
(439, 2, 290, 63, 172),
(440, 1, 291, 13, 210),
(441, 1, 292, 56, 620),
(442, 2, 292, 58, 40),
(444, 2, 293, 58, 120),
(445, 2, 294, 58, 150),
(446, 1, 295, 6, 5190),
(447, 2, 295, 58, 400),
(448, 3, 295, 125, 400),
(449, 6, 295, 85, 500),
(450, 17, 295, 140, 10),
(451, 1, 296, 56, 400),
(452, 1, 297, 56, 600),
(453, 2, 298, 58, 96),
(454, 2, 300, 58, 30),
(455, 2, 301, 58, 84),
(456, 2, 302, 58, 168),
(457, 2, 303, 58, 168),
(458, 2, 304, 58, 520),
(459, 1, 305, 56, 450),
(460, 17, 305, 140, 50),
(461, 1, 306, 56, 1314),
(462, 3, 306, 125, 16),
(463, 1, 307, 6, 1820),
(464, 2, 307, 58, 180),
(465, 3, 307, 125, 150),
(466, 6, 307, 85, 240),
(467, 17, 307, 140, 10),
(468, 1, 308, 13, 386),
(469, 1, 309, 3, 220),
(470, 1, 310, 8, 35),
(471, 3, 310, 125, 100),
(472, 6, 310, 85, 120),
(473, 17, 310, 192, 100),
(474, 1, 311, 3, 240),
(475, 1, 312, 3, 1592),
(476, 6, 312, 85, 200),
(477, 3, 312, 125, 200),
(478, 2, 312, 58, 200),
(479, 17, 312, 140, 8),
(480, 2, 313, 63, 120),
(481, 2, 314, 63, 186),
(482, 2, 315, 63, 160),
(483, 1, 316, 9, 497),
(484, 2, 317, 63, 875),
(485, 5, 318, 5, 2880),
(486, 1, 319, 36, 370),
(487, 1, 320, 56, 800),
(488, 1, 321, 9, 760),
(489, 1, 322, 2, 1335),
(490, 6, 322, 85, 60),
(491, 2, 322, 58, 70),
(492, 1, 323, 57, 450),
(493, 2, 324, 58, 180),
(494, 2, 325, 58, 250),
(495, 1, 326, 28, 150),
(496, 6, 327, 85, 100),
(497, 1, 328, 6, 830),
(498, 3, 328, 125, 120),
(499, 6, 328, 85, 120),
(500, 17, 328, 140, 120),
(501, 2, 328, 58, 10),
(502, 6, 329, 85, 3000),
(503, 1, 330, 56, 1330),
(504, 2, 330, 58, 100),
(505, 1, 331, 11, 420),
(506, 2, 332, 58, 140),
(507, 2, 333, 58, 130),
(508, 1, 334, 56, 221),
(509, 3, 334, 125, 5),
(510, 1, 335, 56, 226),
(513, 1, 336, 36, 260),
(514, 1, 337, 12, 200),
(515, 1, 338, 34, 4280),
(516, 2, 338, 63, 220),
(517, 3, 338, 125, 150),
(518, 6, 338, 85, 150),
(519, 17, 338, 140, 150),
(520, 1, 339, 28, 548),
(521, 2, 340, 58, 30),
(522, 1, 341, 9, 2000),
(523, 2, 342, 58, 200),
(524, 2, 343, 58, 600),
(525, 2, 344, 58, 480),
(526, 7, 345, 105, 120),
(527, 2, 346, 58, 24),
(528, 1, 347, 28, 500),
(529, 1, 348, 28, 103),
(530, 17, 349, 192, 120),
(531, 5, 350, 5, 50),
(532, 2, 351, 63, 137),
(533, 2, 352, 63, 275),
(534, 2, 353, 63, 125),
(535, 1, 354, 56, 600),
(536, 1, 355, 11, 7210),
(537, 2, 355, 63, 340),
(538, 3, 355, 125, 300),
(539, 6, 355, 85, 150),
(540, 19, 356, 119, 1216),
(541, 1, 357, 56, 600),
(542, 1, 358, 28, 60),
(543, 2, 359, 63, 405),
(544, 1, 360, 6, 5080),
(545, 2, 360, 58, 400),
(546, 3, 360, 125, 440),
(547, 17, 360, 140, 80),
(548, 6, 360, 85, 500),
(549, 2, 361, 63, 33),
(550, 2, 362, 63, 60),
(551, 2, 363, 63, 30),
(552, 2, 364, 58, 25),
(553, 1, 365, 2, 1000),
(554, 1, 366, 2, 210),
(555, 1, 367, 2, 255),
(556, 1, 368, 2, 285),
(557, 1, 369, 56, 520),
(558, 2, 369, 63, 80),
(559, 1, 370, 3, 360),
(560, 2, 370, 63, 30),
(561, 1, 371, 6, 2010),
(562, 2, 371, 58, 180),
(563, 3, 371, 125, 150),
(564, 6, 371, 85, 240),
(565, 17, 371, 140, 20),
(566, 1, 372, 38, 1340),
(567, 6, 372, 85, 290),
(568, 2, 372, 58, 210),
(569, 3, 372, 125, 150),
(570, 17, 372, 140, 10),
(571, 1, 373, 57, 360),
(572, 2, 374, 58, 180),
(573, 2, 375, 58, 80),
(574, 1, 376, 56, 500),
(575, 1, 377, 9, 800),
(576, 1, 378, 12, 1100),
(577, 2, 378, 58, 100),
(578, 2, 379, 58, 2592),
(579, 2, 380, 63, 40),
(580, 1, 381, 9, 880),
(581, 2, 382, 58, 40),
(582, 1, 383, 13, 1300),
(583, 2, 383, 58, 100),
(584, 1, 384, 28, 300),
(585, 2, 385, 58, 120),
(586, 2, 386, 58, 10),
(587, 7, 386, 105, 180),
(588, 2, 387, 58, 90),
(589, 6, 388, 85, 100),
(590, 2, 389, 58, 90),
(591, 2, 390, 58, 200),
(592, 6, 390, 85, 300),
(593, 3, 390, 125, 200),
(594, 17, 390, 140, 8),
(595, 1, 390, 3, 1492),
(596, 1, 391, 13, 210),
(597, 2, 391, 147, 30),
(598, 1, 392, 3, 220),
(599, 2, 392, 147, 30),
(600, 1, 393, 56, 300),
(601, 1, 394, 9, 195),
(602, 1, 395, 56, 600),
(603, 1, 396, 56, 800),
(604, 1, 397, 56, 600),
(605, 1, 398, 34, 860),
(606, 2, 398, 63, 80),
(607, 3, 398, 125, 40),
(608, 6, 398, 85, 40),
(610, 1, 400, 34, 750),
(611, 1, 399, 34, 240),
(612, 2, 401, 58, 120),
(613, 2, 402, 58, 200),
(614, 1, 403, 9, 960),
(615, 1, 404, 9, 260),
(616, 2, 405, 63, 276),
(617, 2, 406, 63, 104),
(618, 1, 407, 144, 2000),
(619, 1, 408, 3, 250),
(620, 1, 409, 2, 600),
(621, 2, 409, 63, 100),
(622, 3, 409, 125, 100),
(623, 1, 410, 3, 240),
(624, 2, 410, 58, 30),
(625, 2, 411, 63, 80),
(626, 1, 412, 34, 420),
(627, 1, 413, 13, 418),
(628, 2, 413, 63, 30),
(629, 1, 414, 2, 30),
(630, 3, 414, 125, 80),
(631, 6, 414, 85, 90),
(632, 17, 414, 192, 80),
(633, 1, 415, 13, 310),
(634, 2, 415, 63, 30),
(635, 1, 416, 2, 420),
(636, 2, 417, 63, 400),
(637, 1, 418, 10, 403),
(638, 1, 419, 3, 490),
(639, 2, 419, 58, 50),
(640, 1, 420, 3, 50),
(641, 17, 420, 192, 150),
(642, 3, 419, 125, 60),
(645, 2, 381, 58, 50),
(646, 3, 381, 125, 50),
(647, 1, 421, 56, 500),
(648, 19, 422, 116, 2288),
(649, 2, 423, 63, 780),
(650, 2, 403, 203, 40),
(651, 1, 424, 6, 5120),
(652, 2, 424, 58, 400),
(653, 3, 424, 125, 400),
(654, 6, 424, 85, 500),
(655, 17, 424, 140, 80),
(656, 1, 425, 28, 500),
(657, 1, 426, 34, 380),
(658, 1, 427, 9, 950),
(659, 2, 427, 58, 250),
(660, 2, 428, 58, 600),
(661, 7, 428, 105, 400),
(662, 1, 429, 28, 120),
(663, 2, 430, 63, 480),
(664, 2, 431, 147, 40),
(665, 2, 432, 63, 140),
(666, 2, 433, 63, 100),
(667, 1, 434, 9, 212),
(668, 1, 435, 11, 7040),
(669, 2, 435, 147, 340),
(670, 3, 435, 125, 400),
(671, 6, 435, 85, 220),
(672, 1, 436, 38, 1340),
(673, 2, 436, 58, 150),
(674, 3, 436, 125, 150),
(675, 6, 436, 85, 350),
(676, 17, 436, 140, 10),
(677, 1, 437, 34, 880),
(678, 3, 437, 125, 60),
(679, 6, 437, 85, 60),
(680, 1, 438, 34, 280),
(681, 6, 438, 85, 120),
(682, 2, 439, 58, 16),
(683, 1, 440, 56, 700),
(684, 1, 441, 56, 310),
(685, 2, 441, 58, 40);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_code` varchar(6) DEFAULT NULL,
  `password` blob DEFAULT NULL,
  `time_change_password` date DEFAULT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `second_name` varchar(20) DEFAULT NULL,
  `first_surname` varchar(20) DEFAULT NULL,
  `second_surname` varchar(20) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `parish_id` int(11) DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `login_date` datetime DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`user_id`, `user_code`, `password`, `time_change_password`, `first_name`, `second_name`, `first_surname`, `second_surname`, `birthday`, `position_id`, `department_id`, `parish_id`, `admission_date`, `departure_date`, `login_date`, `status_id`) VALUES
(1, '0001', 0x9f824ad4b17be95f20aa30c5eef9d8f6, '2023-05-13', 'DAVID', 'LEONARDO', 'MOLINA', 'RUÍZ', '1986-08-05', 16, 16, 1131, '2020-01-01', NULL, '2023-06-14 17:19:27', 1),
(2, '10', 0xcb4dc5daf4d8865eb1bd01d6c898c269, '2023-03-09', 'NATHALIE', 'YAMILET', 'LOPEZ', 'TREJO', '1972-08-20', 17, 1, 1131, '2000-02-21', NULL, '2023-03-08 23:41:42', 1),
(3, '10092', 0x10e54efb266e50c523273c638cb690c5, '2023-03-09', 'YESENIA', 'BEATRIZ', 'MARTINEZ', 'GALLARDO', '1979-06-01', 15, 1, 1131, '2004-09-01', NULL, '2023-03-07 08:27:55', 1),
(4, '10141', 0x383155d3ec475bf8ace4b67bf0aaba8d, '2023-03-09', 'JESUS', 'ERASMO', 'PEREZ', 'ERASMO', '1959-11-09', 17, 1, 1131, '2005-02-02', NULL, '2023-02-06 09:52:51', 1),
(5, '10168', 0xde6d03f3e06cba9372848683f12ff10a, '2023-03-09', 'CAROL', 'JOSEFINA', 'LOPEZ', 'CAMPOS', '1962-11-07', 15, 5, 1131, '2005-06-06', NULL, '2023-03-02 10:25:00', 1),
(6, '10367', 0x647c2b55cae32aa42154baeeb313ad40, '2023-03-09', 'LUZ', 'AMANDA', 'FONSECA', 'GARCIA', '1985-01-13', 15, 1, 1131, '2007-10-29', NULL, '2023-03-07 09:22:39', 1),
(7, '10473', 0x97a25906ea5c15bc553e06ec4eaed009, '2023-03-09', 'ARTURO', 'LORENZO', 'MADRIZ', 'VARGAS', '1954-12-16', 17, 1, 1131, '2008-10-14', NULL, '2023-02-17 14:47:21', 1),
(8, '10509', 0xb3cd6c38fa4b39dde440eff4b3bc5a22, '2023-03-09', 'ROMAN', 'ALBERTO', 'SCOTT', '', '1975-07-16', 12, 1, 1131, '2009-05-06', NULL, '2023-03-08 23:39:34', 1),
(9, '10572', 0x9b830d819df904d95147340117e70d44, '2023-03-09', 'OLIVER', 'JOSE', 'PAEZ', 'RANGEL', '1982-10-16', 14, 1, 1131, '2010-01-18', NULL, '2023-03-06 17:46:40', 1),
(10, '10721', 0x2e29e47b1ce3ad1182512298dd26328d, '2023-03-09', 'JORGE', 'ALEJANDRO', 'GONZALEZ', 'MORALES', '1990-05-19', 13, 1, 1131, '2011-11-15', NULL, '2023-03-08 16:07:41', 1),
(11, '10786', 0xdb3fe107f261880fdeced74b00281558, '2023-03-09', 'MARIA', 'ANDREINA', 'SEQUEDA', 'BANDES', '1990-05-30', 13, 1, 1131, '2012-07-20', NULL, '2023-03-08 09:13:49', 1),
(12, '10968', 0xf945dd6534898bbb32c583a4f9a58a0e, '2023-03-09', 'YODELINA', '', 'TORRES', 'MORALES', '1994-09-15', 12, 1, 1131, '2014-02-24', NULL, '2023-03-06 12:00:02', 1),
(13, '11030', 0x41273542b44edd82eec1262012051a0e, '2023-03-09', 'KATHERINE', 'BETHZABEL', 'ZURITA', 'CHACON', '1989-06-08', 13, 1, 1131, '2015-01-13', NULL, '2023-03-08 15:16:07', 1),
(14, '11044', 0x4844e22ab49ce7972d99f6ac664e58d9, '2023-03-09', 'MILEIDIS', 'ALEXANDRA', 'MORENO', 'MATUZALEM', '1992-05-30', 11, 1, 1131, '2015-01-21', NULL, '2023-03-07 09:12:06', 1),
(15, '11116', 0x9b5e21866d59e8c4a93c5770dbaef19b, '2023-03-09', 'FRANCIA', 'CAROLINA', 'MEDINA', 'TINEDO', '1987-03-15', 11, 1, 1131, '2015-11-04', NULL, '2023-03-09 08:03:58', 1),
(16, '11220', 0x9f824ad4b17be95f20aa30c5eef9d8f6, '2023-05-13', 'ASTRID', 'CAROLINA', 'MENDOZA', 'GIL', '1992-07-31', 11, 1, 1131, '2016-04-25', NULL, '2023-03-14 14:50:55', 1),
(17, '11314', 0x7d88c658f3db0526ec6540dbaab56a39, '2023-03-09', 'MARIA', 'GABRIELA', 'TOVAR', 'CARDENAS', '1991-11-28', 8, 1, 1131, '2017-03-27', NULL, '2023-03-08 09:33:10', 1),
(18, '11352', 0x0e6d3b64fffda77f987a1ca0b33692e0, '2023-03-09', 'MARIANA', 'ALEXANDRA', 'BRITO', 'SIFONTES', '1995-02-19', 11, 1, 1131, '2017-11-13', NULL, '2023-03-03 11:46:32', 1),
(19, '11354', 0x093bfe3fa4bf619574f56a3f4a26a7e7, '2023-03-09', 'BELKIS', 'KATERIN', 'CORTINA', 'RUIZ', '1996-12-08', 8, 1, 1131, '2017-11-13', NULL, '2023-03-03 18:35:27', 1),
(20, '11364', 0x3a5f20da401e227e6710acbb25ff97fc, '2023-03-09', 'LUCRECIA', 'DISNORA', 'SILVA', 'APONTE', '1989-03-15', 8, 1, 1131, '2017-12-04', '2021-03-02', NULL, 2),
(21, '11369', 0xc425904da30c5cb2dcd022ed07388c16, '2023-03-09', 'NORMEDY', 'ZORIBETH', 'PARRA', 'TOVAR', '1986-08-22', 8, 1, 1131, '2017-12-04', NULL, '2023-02-28 10:47:05', 1),
(22, '11371', 0xae865fdcd1ec90e2fda0a87137b009f2, '2023-03-09', 'JOSVELIS', 'YETSIMAR', 'CASTILLO', 'GIL', '1997-07-14', 9, 1, 1131, '2017-12-04', NULL, '2023-03-07 12:17:25', 1),
(23, '11391', 0xdfb8f58ab34354191004d62ab7308044, '2023-03-09', 'LUIS', 'ANTONIO', 'RUSSIAN', 'REQUENA', '1996-01-10', 11, 1, 1131, '2018-02-15', '2022-06-03', '2022-06-03 14:29:26', 2),
(24, '11401', 0x7e23d16c8f37bba5fbe9d04480852181, '2023-03-09', 'JONATHAN', 'JOSE', 'AZOCAR', 'RODRIGUEZ', '1994-08-24', 8, 1, 1131, '2018-02-26', NULL, '2023-03-03 19:06:17', 1),
(25, '11403', 0x10dae5b466840d2e283185227765dda2, '2023-03-09', 'YERLENIS', 'DELYET', 'VALDERRAMA', 'ROSALES', '1998-09-14', 11, 1, 1131, '2018-03-06', NULL, '2023-03-03 11:48:31', 1),
(26, '11410', 0xd2b99b2de2f70be230bc9594c37a0c61, '2023-03-09', 'KLEIVER', 'JOHANA', 'CORRO', 'GUDIÑO', '1991-02-13', 8, 1, 1131, '2018-03-06', NULL, '2023-02-14 14:39:57', 1),
(27, '11421', 0xe17d18eb6f7ca9b5fdd6795efe996446, '2023-03-09', 'MARYURI', 'NAILET', 'BARAZARTE', 'VALERA', '1979-09-13', 7, 1, 1131, '2018-03-26', '2021-04-15', NULL, 2),
(28, '11437', 0x7fe50132c05f1bdd77e6a4dd6d11dd7d, '2023-03-09', 'PEDRO', 'ALEXANDER', 'BENITEZ', 'MELENDEZ', '1968-06-05', 15, 1, 1131, '2018-07-01', NULL, '2023-02-24 17:23:36', 1),
(29, '11440', 0xc12cd8cd82284a11fb0613e0d69b9bbc, '2023-03-09', 'DENNYS', 'RAMON', 'FLORES', 'MORALES', '1981-04-20', 7, 1, 1131, '2018-07-17', NULL, '2023-02-28 14:51:45', 1),
(30, '11446', 0x6fe7030bcbddb93c36dbbb7ec2f31f85, '2023-03-09', 'GENESIS', 'VANESSA', 'MARCANO', 'RANGEL', '1997-10-05', 9, 1, 1131, '2018-07-25', '2022-07-29', '2022-07-23 21:55:02', 2),
(31, '11448', 0x1354569ce26ccfd4e3bebd109b44cd11, '2023-03-09', 'KEILIMAR', 'YULISET', 'SUAREZ', 'LARES', '1996-05-12', 6, 1, 1131, '2018-07-31', NULL, '2023-03-07 10:50:50', 1),
(32, '11452', 0x2c6f8320e663f56932482e2c34bfba3f, '2023-03-09', 'JOHANNE', 'FRANCIS', 'MUÑOZ', 'MARTINEZ', '1981-07-22', 13, 1, 1131, '2018-08-15', NULL, '2023-03-06 20:50:16', 1),
(33, '11453', 0x88437b100005a2c216a35b10ac06ac89, '2023-03-09', 'ALFREDO', 'JOSE', 'HERNANDEZ', 'TORO', '1969-03-14', 9, 1, 1131, '2018-08-14', NULL, '2023-03-08 16:17:07', 1),
(34, '11457', 0x73dd6433a0272bf8446bc3267e7d3f12, '2023-03-09', 'RAUL', 'IGNACIO', 'VARGAS', 'FREITES', '1976-01-29', 17, 1, 1131, '2018-10-18', NULL, '2023-02-23 14:51:55', 1),
(35, '11466', 0xb0bd668ff196e3be8ba74828d811dc57, '2023-03-09', 'SHELCIE', 'ESTHER', 'PAZ', '', '1997-03-22', 7, 1, 1131, '2018-11-08', NULL, '2023-02-24 14:04:45', 1),
(36, '11467', 0xe2639ee3113fd0cc23992b5b24daf64b, '2023-03-09', 'LADYMAR', '', 'MORETT', 'RONDON', '1983-03-18', 12, 1, 1131, '2018-11-20', '2022-12-29', '2022-12-29 13:40:52', 2),
(37, '11469', 0xc36653810196652a8c5255973c9784a4, '2023-03-09', 'ANTHONY', 'ROBERT', 'GARCIA', 'CHAPARRO', '1991-06-26', 7, 1, 1131, '2018-11-12', NULL, NULL, 2),
(38, '11480', 0xa577eb8769d5d83cddcd25fae3cab295, '2023-03-09', 'SOLMARY', 'DEL VALLE', 'MARTINEZ', 'MARCHAN', '1983-08-03', 12, 1, 1131, '2018-12-17', NULL, '2023-03-03 11:59:13', 1),
(39, '11481', 0x39c32f5b07662274e35c4246a978d69f, '2023-03-09', 'JACKELINE', 'ZULEYMA MILAGROS', 'RAMOS', 'PEÑA', '1989-06-02', 6, 1, 1131, '2018-12-18', NULL, '2023-03-02 12:34:38', 1),
(40, '11484', 0x67328548d73e014e6b5c5ab14ed5b9c1, '2023-03-09', 'BELKIS', 'EDICTA', 'VAZQUEZ', 'MORALES', '1984-07-17', 6, 1, 1131, '2019-01-07', '2020-09-30', NULL, 2),
(41, '11487', 0x75939b253f15aad914fdf356bed1d590, '2023-03-09', 'YUZLEIBBY', 'ANGELICA', 'MALDONADO', 'ROSALES', '1996-10-08', 7, 1, 1131, '2019-01-21', NULL, '2023-03-01 09:58:01', 1),
(42, '11490', 0x95a11af2e293e1ebe91c1cb2342f1af5, '2023-03-09', 'GIOVANNI', 'JESUS', 'CORREDOR', 'SANOJA', '1996-07-07', 10, 1, 1131, '2019-01-24', NULL, '2023-03-02 13:26:42', 1),
(43, '11493', 0xf4be463bfe681a054e9c37164a42879b, '2023-03-09', 'KLEIVER', 'JOSE', 'CADENAS', 'QUIÑONEZ', '1995-05-02', 9, 1, 1131, '2019-02-04', NULL, '2023-03-08 10:57:21', 1),
(44, '11494', 0x372914d41de86dd92703cd0dee53bc62, '2023-03-09', 'IVETTE', 'ALEJANDRA', 'OROZCO', 'FLORES', '1994-02-23', 12, 1, 1131, '2019-02-04', '2021-03-16', NULL, 2),
(45, '11497', 0xbc51997c6750d92815ec3bd08f4c241c, '2023-03-09', 'ZUNAYA', 'ESTHER', 'WILCHES', 'OLAVE', '1996-12-05', 4, 1, 1131, '2019-02-07', '2021-04-16', NULL, 2),
(46, '11499', 0x1b42160c619d522204261b5be95cb8c6, '2023-03-09', 'JESUS', 'ALBERTO', 'ABRAHAM', 'CORONADO', '1994-06-21', 10, 1, 1131, '2019-02-21', '2022-05-05', '2022-05-05 15:33:10', 2),
(47, '11503', 0x06e477466a371d1eacd75ae15905d43d, '2023-03-09', 'JOSE', 'MIGUEL', 'PEROZO', 'HERRERA', '1994-10-04', 9, 1, 1131, '2019-03-07', '2022-01-19', '2022-01-18 11:19:04', 2),
(48, '11504', 0x6aa697bff06db4b84bdbc82311fffc2e, '2023-03-09', 'ROBERTO', 'RAFAEL', 'VILLEGAS', 'GONZALEZ', '1988-09-26', 8, 1, 1131, '2019-03-20', '2021-12-03', '2021-12-03 11:16:58', 2),
(49, '11507', 0x3a2c0ef6056afb94c39299ab96d4cd94, '2023-03-09', 'SANDRO', 'YOEL', 'MAYORA', '', '1973-09-17', 11, 6, 1131, '2019-04-01', NULL, '2023-03-02 10:07:29', 1),
(50, '11519', 0xd18f8bd03a2fe1a887614b386c66c450, '2023-03-09', 'EDUARDO', '', 'BASTOS', 'RICCIO', '1989-06-27', 6, 1, 1131, '2019-07-10', '2021-12-01', '2021-11-30 23:49:08', 2),
(51, '11520', 0x913bfe28eb609fcaa63aecb1e18f4c67, '2023-03-09', 'VANESSA', 'VALENTINA', 'ROJAS', 'MORALES', '1987-12-23', 7, 1, 1131, '2019-07-16', NULL, '2023-03-03 10:08:01', 1),
(52, '11527', 0x9831d9046b6a2d18bd625df5a5d3ed45, '2023-03-09', 'CARLOS', 'ALBERTO', 'REVETE', 'CARVALLO', '1994-09-18', 7, 1, 1131, '2019-12-09', NULL, '2023-02-13 09:06:55', 1),
(53, '11528', 0x7c589ad8221957631d159f5b6350a950, '2023-03-09', 'VIANNEY', 'DEL VALLE', 'RUGELES', 'MANTILLA', '1972-01-08', 8, 1, 1131, '2019-12-09', '2022-10-28', '2022-10-25 17:08:43', 2),
(54, '11529', 0x8b7592142278c43d1c235569d6a0bea2, '2023-03-09', 'EDWIN', 'JESUS', 'BURGOS', 'GOMEZ', '1987-12-06', 4, 1, 1131, '2019-12-09', '2021-01-11', NULL, 2),
(55, '11535', 0x9592acdded4437d994ee5f1a14a7d4b1, '2023-03-09', 'ENIL', 'ALEJANDRO', 'MOLINA', 'YDROGO', '2002-02-16', 7, 1, 1131, '2020-03-09', NULL, '2023-03-07 13:47:54', 1),
(56, '22', 0x09ccd8b97d37abf3003cb7bdb7fee97c, '2023-03-09', 'FREDDY', 'RODOLFO', 'VARGAS', 'HERNANDEZ', '1969-10-22', 15, 1, 1131, '2000-08-01', NULL, '2023-03-07 20:26:21', 1),
(57, '6060', 0xe628f6988aba3b5c31751f4d5d521522, '2023-03-09', 'YORMAN', 'ISMAEL', 'RANGEL', 'GONZALEZ', '1983-08-15', 14, 1, 1131, '2014-07-01', '2023-01-31', '2023-01-31 15:10:28', 2),
(58, '10783', 0x75fd9de136418348f0c0a53a98a51181, '2023-03-09', 'JOSE', 'MIGUEL', 'UTRERA', 'ROJAS', '1975-04-02', 16, 2, 1131, '2012-07-16', NULL, '2023-03-09 08:06:36', 1),
(59, '11485', 0xa9ce0f2bf56800d4cb028ddbc2b6b829, '2023-03-09', 'ALEJANDRO', 'ENRIQUE', 'LIRA', 'TOVAR', '1995-06-27', 7, 2, 1131, '2019-01-09', '2021-05-21', '2021-04-29 20:13:20', 2),
(60, '11505', 0xa33fb3c2617b1e2cc5d944c0b4c0859d, '2023-03-09', 'YORDALIS', 'GABRIELA', 'ECHARRYS', 'CABRILES', '1993-08-02', 5, 2, 1131, '2019-04-01', '2021-01-15', NULL, 2),
(61, '11506', 0x2684acb76beb6afaa2e468c39fd814bb, '2023-03-09', 'ELIANA', 'MARIA', 'PONCE', 'VARGAS', '1971-03-14', 14, 2, 1131, '2019-04-08', '2022-02-15', '2022-02-16 11:08:46', 2),
(62, '11514', 0x103eef319babf7b08f06a819046edfec, '2023-03-09', 'STEFANY', 'YANETH', 'GONZALEZ', 'MIJARES', '1995-02-22', 6, 2, 1131, '2019-06-03', NULL, '2021-12-09 07:56:47', 2),
(63, '11521', 0x3bd7662f26d22e0c32de54ef0569807a, '2023-03-09', 'NAIVELYS', 'GABRIELA', 'ALTUVE', 'TORRES', '1991-06-20', 13, 2, 1131, '2019-09-02', NULL, '2023-02-23 21:40:01', 1),
(64, '11522', 0xf663e5da56b6035105e428560a01ff4d, '2023-03-09', 'GABRIELA', 'DEL VALLE', 'GIL', 'LA PIETRA', '1996-05-09', 6, 2, 1131, '2019-09-02', '2022-06-08', '2022-06-07 16:12:15', 2),
(65, '11526', 0x442ce2f8fe72b1614fb2bcfe9cd6919b, '2023-03-09', 'ORIANNA', 'DESSIREE', 'ALEJOS', 'FIGUEREDO', '1996-05-23', 4, 2, 1131, '2019-11-18', '2022-01-14', '2022-01-18 16:00:14', 2),
(66, '11533', 0x5bd05cb4c18042365c51faf72a87af3e, '2023-03-09', 'MARYNES', 'DEL VALLE', 'GONZALEZ', 'MENDOZA', '1997-03-06', 3, 2, 1131, '2020-03-09', NULL, NULL, 2),
(67, '10794', 0x29f82578b0c53e954eed0b971a75b555, '2023-03-09', 'ELIGIO', 'HORACIO', 'MENDOZA', 'ODREMAN', '1970-10-23', 15, 4, 1131, '2012-08-01', NULL, NULL, 2),
(68, '10838', 0xf3ba845c6f332ecf550b6ffd0b524ba2, '2023-03-09', 'MARIELVI', '', 'OLLER', 'MENDOZA', '1986-07-11', 12, 4, 1131, '2013-01-23', NULL, NULL, 2),
(69, '111426', 0xaa924ae19b4789f2d1989149f718cbfb, '2023-03-09', 'ALBA', 'JEANNETH', 'NAVIA', 'BERMUDEZ', '1976-07-22', 12, 4, 1131, '2018-05-01', '2023-01-10', NULL, 2),
(70, '11344', 0x16fdfb50b2e08cd162dcb4b2b59904b4, '2023-03-09', 'NATHASHA', 'ESTEFANIA', 'FRANCO', 'BERMUDEZ', '1996-02-03', 9, 4, 1131, '2017-10-13', '2020-11-06', NULL, 2),
(71, '11353', 0x5621af456fefd10655d92835e1bb1fc9, '2023-03-09', 'YESSICA', 'LAURA', 'RIVAS', 'TURMERO', '1990-11-26', 11, 4, 1131, '2017-11-13', NULL, NULL, 2),
(72, '11366', 0xa8b0fb715bbcda315cc265b24eb0f913, '2023-03-09', 'FRAYNER', 'ALEXANDER', 'RANGEL', 'VALERO', '1993-04-17', 8, 4, 1131, '2017-12-04', '2021-01-11', NULL, 2),
(73, '11374', 0x3f5dc1ab52521d40452ee0d1e487124f, '2023-03-09', 'YDA', 'MERCEDES', 'CHIRINOS', 'VILORIA', '1983-09-28', 8, 1, 1131, '2017-12-04', '2022-03-15', '2022-03-16 12:29:26', 2),
(74, '11411', 0xecb2621b72dfb2d72b3f056cf39bcd92, '2023-03-09', 'GENESIS', 'GABRIELA', 'BARRIOS', 'VILORIA', '1998-07-25', 11, 4, 1131, '2018-03-06', NULL, NULL, 2),
(75, '11458', 0xdc40f17aaba2b35d115af903eafe41d5, '2023-03-09', 'RUDDY', 'ISAMAR', 'PINTO', 'COLMENARES', '1990-05-06', 10, 4, 1131, '2018-10-16', NULL, NULL, 2),
(76, '11459', 0x02372563f1ba55068035f4ae232d7865, '2023-03-09', 'CARLOS', 'EDUARDO', 'RODRIGUEZ', '', '1966-03-15', 9, 4, 1131, '2018-10-16', '2021-09-03', NULL, 2),
(77, '11471', 0xbf9a856ccb6329e806ad2a89a212c663, '2023-03-09', 'CARMEN', 'ELENA', 'BERRIOS', 'BASTIDAS', '1989-07-16', 9, 4, 1131, '2018-11-15', '2022-10-14', NULL, 2),
(78, '11472', 0x54d8b1a8bf708d1d3ff92d0b1da54fc9, '2023-03-09', 'GERALDINE', 'DESIREE', 'RUIZ', 'HENRIQUEZ', '1975-10-09', 11, 4, 1131, '2018-11-26', '2021-03-01', NULL, 2),
(79, '11482', 0xd3caab46800986a021e4a8894ec560a9, '2023-03-09', 'NAHOMY', 'NAZARETH', 'QUINTERO', 'MARTINEZ', '1998-08-13', 7, 4, 1131, '2018-12-17', '2022-09-16', NULL, 2),
(80, '11510', 0x0559d0ac9c93cd019ab82ff19bd88135, '2023-03-09', 'MARIA', 'ISABEL', 'ESPINA', 'URBINA', '1966-12-09', 11, 4, 1131, '2019-04-29', NULL, NULL, 2),
(81, '11513', 0xa01a6a426395dee14f270d10c8e4bbde, '2023-03-09', 'ANGELO', 'ALFONSO', 'MARTINEZ', 'BERROTERAN', '1990-02-05', 21, 19, 1131, '2019-06-03', '2021-08-16', '2021-06-30 10:40:47', 2),
(82, '11523', 0x87afef94bfbedb9c0b9384d051b57ae4, '2023-03-09', 'MANUEL', 'ALEJANDRO', 'DA SILVA', 'VILLAMISIL', '1984-12-04', 9, 4, 1131, '2019-10-01', '2021-09-02', NULL, 2),
(83, '111431', 0x387232dbac8c4712b93902412b45116b, '2023-03-09', 'GLENDER', 'JESUS', 'CORTEZ', '', '1990-11-05', 12, 6, 1131, '2018-06-25', '2021-08-18', '2021-08-18 11:14:42', 2),
(84, '11267', 0xb42d3da5f0495e13078d701bdb3139c5, '2023-03-09', 'ALBERTO', 'JOSE', 'EVIES', 'GONZALEZ', '1965-11-04', 13, 5, 1131, '2016-10-03', NULL, '2021-12-27 13:15:54', 1),
(85, '11291', 0x9f824ad4b17be95f20aa30c5eef9d8f6, '2023-05-13', 'ANGELA', 'LEONOR', 'ARANEA', 'CHICA', '1976-01-30', 14, 6, 1131, '2016-12-12', NULL, '2023-03-20 10:52:06', 1),
(86, '11346', 0x3741710cf81161c3f808ab6763e6dd46, '2023-03-09', 'ARTURO', 'ARMANDO', 'SOSA', 'HERRERA', '1962-08-27', 12, 1, 1131, '2017-11-01', NULL, '2023-03-08 13:23:34', 1),
(87, '11414', 0xfc273bdbdb272d4d8242dc839fe574c0, '2023-03-09', 'ADRIAN', 'ALEXANDER', 'PEREZ', 'RODRIGUEZ', '1994-04-19', 11, 5, 1131, '2018-03-16', NULL, '2023-03-01 08:31:34', 1),
(88, '11443', 0x10345a029cfad2c3590a1b9a451d1f1d, '2023-03-09', 'ELISA', 'MARIBEL', 'PASERO', 'MARIÑO', '1979-08-25', 8, 1, 1131, '2018-07-19', NULL, '2023-03-07 12:16:33', 1),
(89, '11463', 0x35940ed319e855068bde5ef0cfce1223, '2023-03-09', 'OMAR', 'ALFONSO', 'MARQUEZ', 'RODRIGUEZ', '2000-03-04', 9, 6, 1131, '2018-11-05', NULL, '2023-03-03 14:05:02', 1),
(90, '11474', 0x8ddc468ceb92806447b051cf861ebc35, '2023-03-09', 'ANGELICA', 'ESTEFANIA', 'FUNES', 'OLOYOLA', '1995-06-27', 9, 6, 1131, '2018-11-26', '2022-12-02', '2022-12-02 10:12:19', 2),
(91, '11492', 0xdb97f3afbcc4d25a1430ceba77fa6a62, '2023-03-09', 'ESLYN', 'MILEYDIS', 'ROJAS', 'ROMERO', '1989-03-25', 8, 5, 1131, '2019-02-11', '2019-02-11', '2022-02-10 11:34:30', 2),
(92, '10135', 0x02c9275997d54158c4167be77a6630ce, '2023-03-09', 'CARMEN', 'VESTALIA', 'OCHOA', '', '1941-01-09', 19, 7, 1131, '2005-01-24', NULL, '2023-03-07 12:27:30', 1),
(93, '10446', 0x4d1824cf8b7a77fed908c76ab6489714, '2023-03-09', 'LAURA', 'YAMILET', 'ROJAS', 'LIZARRAGA', '1974-09-28', 12, 10, 1131, '2008-07-23', NULL, '2023-03-08 10:38:18', 1),
(94, '10466', 0xa1198b470a81e90dac0bed1b309401a8, '2023-03-09', 'ANTONIO', 'JOSE', 'RUBIO', 'HERNANDEZ', '1967-12-11', 22, 7, 1131, '2008-10-03', NULL, '2023-03-07 12:30:43', 1),
(95, '10559', 0x0085f93347f0189fcb05c3a2c63cfb37, '2023-03-09', 'RUBEN', 'DARIO', 'VERA', 'PATIÑO', '1983-01-19', 37, 11, 1131, '2010-01-18', NULL, '2023-03-07 13:11:36', 1),
(96, '10568', 0xdc1728d6fab31e62277d87cb0baef850, '2023-03-09', 'LUISA', 'ESTHER', 'TOVAR', '', '1964-04-09', 24, 7, 1131, '2010-01-18', NULL, '2023-03-07 12:32:46', 1),
(97, '10589', 0x0e68f89affeeb43d6c64e3c45142cdf4, '2023-03-09', 'JOSE', 'ANTONIO', 'MACHADO', 'PEREZ', '1967-08-19', 14, 9, 1131, '2010-02-22', NULL, '2023-03-02 14:51:16', 1),
(98, '10775', 0x21099c58c60dbd911af236bab605382f, '2023-03-09', 'DULY', 'YOSMILA', 'RINCONES', '', '1980-09-12', 25, 7, 1131, '2012-04-30', NULL, '2023-03-07 12:34:59', 1),
(99, '10776', 0x007d9490d21daffd11ed0ee4545799d7, '2023-03-09', 'YENNIFER', 'MARIANA', 'VILLA', 'ANGEL', '1988-11-24', 19, 7, 1131, '2012-05-08', NULL, '2023-03-07 13:04:44', 1),
(100, '10777', 0x72885907032c6a6a16024d7b6d6bda9a, '2023-03-09', 'ANA', 'CECILIA', 'CASTAÑO', 'ESCOBAR', '1946-10-10', 19, 2, 1131, '2012-05-16', NULL, '2023-03-01 08:30:46', 1),
(101, '10896', 0xf0c9f1a5e0ceba8ff174aee2e655b8d6, '2023-03-09', 'AMAYOISBI', 'LIDSAY', 'GARCIA', 'CHACIN', '1972-07-12', 12, 12, 1131, '2013-08-08', NULL, '2023-03-08 10:58:13', 1),
(102, '10897', 0x622f783e5cb68f8539a07d2e72fc5181, '2023-03-09', 'JENNIFER', 'LETICIA', 'CHACON', 'ZAMBRANO', '1985-02-21', 26, 12, 1131, '2013-08-19', NULL, '2023-03-08 10:44:05', 1),
(103, '10977', 0x1917192b32463935bdb37b5424da609a, '2023-03-09', 'IGNAYARI', 'KATHERINE', 'MENDOZA', 'LUZARDO', '1991-06-11', 29, 7, 1131, '2014-06-05', NULL, '2023-03-07 15:48:08', 1),
(104, '11145', 0x251a3d0f0933d817a00a114a54306788, '2023-03-09', 'REINA', 'MARIA', 'FAJARDO', 'GUERRERO', '1998-03-10', 31, 7, 1131, '2015-11-25', '2021-07-23', '2021-06-23 12:27:39', 2),
(105, '11159', 0x39f7a594369157e26555f4e0d7a1a659, '2023-03-09', 'YOLYMER', 'ALICIA', 'MENDOZA', 'GARCIA', '1973-10-29', 14, 7, 1131, '2015-12-18', NULL, '2023-03-09 08:37:39', 1),
(106, '11208', 0x7758b701cf34e5dd05b290c5902eb3dc, '2023-03-09', 'ROSA', 'ESMERALDA', 'LUZARDO', 'CARDENAS', '1965-08-28', 24, 7, 1131, '2016-03-14', NULL, '2023-03-07 13:05:55', 1),
(107, '11292', 0x9834a2cdd6f9db0a0d250bffac8c7ade, '2023-03-09', 'ADRIANA', '', 'GUZMAN', 'LA CRUZ', '1982-06-18', 26, 12, 1131, '2016-12-12', '2020-08-24', NULL, 2),
(108, '11423', 0x82ecb7a4d460cbd9b5fccb7bba696837, '2023-03-09', 'JOSE', 'LUZARDO', 'ESTABA', 'MOTA', '1988-04-08', 12, 13, 1131, '2018-04-09', NULL, '2023-03-09 09:05:27', 1),
(109, '11438', 0xe1f58ad538cd74ec3753e32286efb835, '2023-03-09', 'KARINA', '', 'PEREZ', 'MARQUES', '1993-08-09', 27, 19, 1131, '2018-07-09', NULL, '2023-02-24 09:39:49', 1),
(110, '11455', 0x54dc3edc08196bdd90fedc8827492cfa, '2023-03-09', 'ZONNY', 'EDUARDO', 'GARCIA', 'OJEDA', '1993-08-30', 35, 13, 1131, '2018-08-21', '2021-03-03', NULL, 2),
(111, '11473', 0x52e0d97d3cc24e1500df52296fdc3338, '2023-03-09', 'YAINE', 'ALEXANDER', 'MACHADO', 'PEREZ', '1981-06-12', 31, 11, 1131, '2018-11-26', '2022-01-24', '2022-01-21 12:51:42', 2),
(112, '11498', 0x48a6a5ef107959d068a87c41dd289746, '2023-03-09', 'ANTONIO', 'ALEXANDER', 'FARIA', 'EXPOSITO', '1983-08-28', 31, 11, 1131, '2019-02-18', '2021-08-02', '2021-07-06 11:41:46', 2),
(113, '11524', 0xb88c861c64fbca0f3ed2921c92dc2d58, '2023-03-09', 'LEONARDO', 'ANTONIO', 'LOPEZ', 'AGURTO', '2001-10-29', 32, 9, 1131, '2019-10-01', NULL, '2023-03-09 08:59:15', 1),
(114, '11525', 0xeb3607196b0db72c9d8b9c7cdefe7175, '2023-03-09', 'JOSE', 'ARTURO', 'MADRIZ', 'MALAVE', '1996-06-07', 2, 19, 1131, '2019-11-04', NULL, '2021-04-25 20:53:48', 2),
(115, '11530', 0x3fd6b034156ce56e3063c7aceda2129b, '2023-03-09', 'LILIANA', 'IBETH', 'PARRA', 'PEREZ', '1980-05-21', 25, 7, 1131, '2020-01-29', '2021-11-30', '2021-11-16 14:18:04', 2),
(116, '11531', 0x11d4863f0228a0f15971811d319899c8, '2023-03-09', 'ANTONIO', 'JOSE', 'REYES', 'SEQUERA', '1959-12-31', 15, 19, 1131, '2020-02-03', NULL, '2023-03-08 10:49:51', 1),
(117, '11532', 0xd04d0184b0216d42ec38d51300a5fb0e, '2023-03-09', 'DUVAN', 'RAFAEL', 'PINTO', 'JAIMES', '2000-02-07', 3, 1, 1131, '2020-02-26', NULL, '2023-03-07 14:38:19', 1),
(118, '11534', 0xf3143b800ced9f5b915d90b9cc7ae737, '2023-03-09', 'FREDDY', 'FRANCISCO', 'PERDOMO', 'MOLINA', '1986-03-03', 22, 7, 1131, '2020-03-01', NULL, NULL, 2),
(119, '11536', 0xd995664289ab091b92900d8c3d725211, '2023-03-09', 'FERNANDO', 'JOSE', 'RANGEL', 'KUIPPERS', '1992-12-12', 12, 19, 1131, '2020-03-16', NULL, '2023-03-08 10:48:33', 1),
(120, '11537', 0xdac7c278f04cd6bcfc2e8cd1327ba186, '2023-03-09', 'GELEN', 'DEL ROSARIO', 'CARDENAS', 'MARQUEZ', '1958-03-08', 23, 7, 1131, '2020-06-01', NULL, '2023-03-09 09:29:51', 1),
(121, '11538', 0x5f6cefe297d88422fd26fc327927c50c, '2023-03-09', 'FREDDY', 'ANTONIO', 'BORRERO', 'CONTRERAS', '1989-08-09', 24, 11, 1131, '2020-06-01', NULL, NULL, 2),
(122, '11539', 0xf3a4e6e99f2351d7d8d7dbe13a8613c6, '2023-03-09', 'AURA', 'MARIA', 'CONTRERAS', 'PASTRAN', '1968-07-01', 24, 7, 1131, '2020-06-01', NULL, '2023-03-07 13:07:06', 1),
(123, '36', 0xaff067db4d01053de5e9b63e3acad0f3, '2023-03-09', 'JESUS', 'SALVADOR', 'MORILLO', 'QUINTANA', '1960-03-02', 12, 11, 1131, '2000-01-17', NULL, '2023-03-07 13:12:35', 1),
(124, '49', 0x691bc08a9481d285f219ba498b40c2d8, '2023-03-09', 'AMELIA', 'JOSEFINA', 'DIAZ', 'MENDOZA', '1956-03-19', 20, 7, 1131, '2004-11-01', NULL, '2023-03-09 09:29:05', 1),
(125, '10195', 0xb9ce83958d6a23a8336406193a64ff01, '2023-03-09', 'EMILIO', 'JOSE', 'LEON', 'FARIAS', '1965-06-28', 15, 3, 1131, '2005-11-01', NULL, '2023-03-08 10:55:19', 1),
(126, '11265', 0xa0bd6599e6e683811b6372401e231dd8, '2023-03-09', 'GUSTAVO', 'ADOLFO', 'PUCHI', 'MEDINA', '1963-09-12', 14, 3, 1131, '2016-10-03', NULL, '2023-02-07 22:21:36', 1),
(127, '11376', 0xdf4810c7bef897842d6da0a50067246d, '2023-03-09', 'ALFIO', 'FILIPPO', 'SAGLIMBENI', 'MUSCOLINO', '1967-08-03', 13, 3, 1131, '2017-12-20', NULL, '2023-03-07 14:54:06', 1),
(128, '11397', 0xe5ca561acfe5b7e6d08fe901bc7949f5, '2023-03-09', 'ARIANNA', 'ELENA', 'MATOS', 'IACOBELLIS', '1995-08-21', 12, 3, 619, '2018-02-20', NULL, '2023-03-09 09:05:36', 1),
(129, '11450', 0xbae93c1b60db15e5865266fd0d24633a, '2023-03-09', 'ANA', 'VIRGINIA', 'BLANDIN', 'ARZOLA', '1981-04-08', 12, 3, 1131, '2018-08-07', NULL, '2023-03-03 14:51:42', 1),
(130, '10262', 0xb30631929b9c596b6b7c93bc0107a2f9, '2023-03-09', 'OSCAR', 'AUGUSTO', 'PIÑA', 'ALBUJAR', '1946-01-06', 15, 14, 1131, '2006-01-02', NULL, '2021-05-18 10:10:44', 1),
(131, '11278', 0x610144861cabaf4a843655e1382b4ba1, '2023-03-09', 'YOSBER', 'ALEJANDRO', 'GOMEZ', 'LANDAETA', '1997-12-02', 41, 15, 1131, '2016-11-01', NULL, NULL, 2),
(132, '11280', 0x2cad3164e6c307fdcb776bd810cb7581, '2023-03-09', 'DUGLIMAR', 'YOLEIDA', 'MENDEZ', 'RIVAS', '1999-07-02', 7, 1, 1131, '2016-11-16', '2022-06-30', '2022-07-11 12:14:10', 2),
(133, '11312', 0xa7c857f013892adc7f221598f2e89d13, '2023-03-09', 'SOL', 'PATRICIA', 'VIANA', 'CONSUEGRA', '1997-09-23', 5, 17, 1131, '2017-03-20', NULL, '2023-03-09 10:17:23', 1),
(134, '11063', 0x3b313b352d415598974619e53a931dab, '2023-03-09', 'DOUGLAS', 'EDUARDO', 'TORREALBA', 'SANCHEZ', '1975-10-28', 42, 10, 1131, '2015-06-02', NULL, '2023-02-02 10:13:31', 1),
(135, '11064', 0xf8d7bcf36c42372b56e93e294887f37f, '2023-03-09', 'DARWING', 'JOSE', 'CORDOVA', '', '1980-08-04', 40, 16, 1131, '2015-06-02', NULL, NULL, 1),
(136, '11066', 0x41399ac78c24e3559f7726613d12ebd7, '2023-03-09', 'JEFERSON', 'JESUS', 'YANEZ', 'VILLEGAS', '1995-10-12', 40, 16, 1131, '2015-06-02', NULL, NULL, 1),
(137, '11068', 0x5a0c077e3b5d51173617b9505e5096a0, '2023-03-09', 'JOSE', 'ANTONIO', 'ARAUJO', 'RODRIGUEZ', '1989-05-30', 40, 16, 1131, '2015-06-02', NULL, NULL, 1),
(138, '11236', 0x9539088bb755d49549bae8545139afef, '2023-03-09', 'ANGEL', 'EDUARDO', 'APARICIO', 'ROMERO', '1970-08-02', 40, 16, 1131, '2016-05-20', NULL, NULL, 1),
(139, '11237', 0xe9ab85bfeff30e5d550382afc6080220, '2023-03-09', 'JESUS', 'ANTONIO', 'ROJAS', 'CRUZ', '1984-07-18', 40, 16, 1131, '2016-05-20', NULL, NULL, 1),
(140, '10508', 0x2d56c9afa7ed653edb2aaa6ae8f27296, '2023-03-09', 'FREDY', 'SAMUEL', 'BAUTISTA', 'VILLEGAS', '1950-05-14', 15, 17, 1131, '2005-08-01', NULL, '2023-03-09 10:23:26', 1),
(141, '10689', 0x405c1a039a181f42019aa2982793c531, '2023-03-09', 'ELLEN', 'KATIUSKA', 'FUENTES', 'RIOS', '1966-03-16', 33, 18, 1131, '2007-02-26', NULL, NULL, 2),
(142, '11451', 0x5721015667f69f6abe55a7234ee28807, '2023-03-09', 'BARBARA', 'CAROLINA', 'ZAMBRANO', 'AGUINALDE', '1996-11-19', 6, 18, 1131, '2018-08-01', '2021-07-01', NULL, 2),
(143, '11476', 0xbe3ff23ed8054d2728572e354edf7671, '2023-03-09', 'MARY', '', 'CRUZ', 'SALAZAR', '1989-09-20', 12, 18, 1131, '2018-12-03', '2021-07-01', NULL, 2),
(144, '10863', 0x63ed7aeb031c9314dd620e19e77c79e2, '2023-03-09', 'SERGIO', 'FREDDYS', 'MÁRQUEZ', 'TOVAR', '1971-12-31', 16, 1, 1131, '2013-05-02', NULL, '2023-03-08 12:28:02', 1),
(145, '29', 0x479f7317068099dcf2a111b30db251df, '2023-03-09', 'NELSON', 'JOSE', 'MARCANO', '', '1969-09-20', 16, 1, 1131, '2000-10-26', NULL, '2023-03-08 12:26:18', 1),
(146, '5002', 0xaef4634cdd03090997802d1839eb6c5a, '2023-03-09', 'SAMUEL', 'ALEJANDRO', 'MARQUEZ', 'TOVAR', '1966-06-28', 16, 1, 1131, '1999-07-01', NULL, '2023-03-07 12:23:29', 1),
(147, '5014', 0x75a1d6a76e48987599d8e331c08d260b, '2023-05-19', 'ANTONIO', 'JOSE', 'DUGARTE', 'LOBO', '1964-07-23', 16, 2, 1131, '2011-03-16', NULL, '2023-03-20 10:58:28', 1),
(148, '107', 0x4487799a782e07416359afcb8a42fb84, '2023-03-09', 'MIRNANGELA', 'LARISKA', 'SALAYA', 'GARCIA', '1977-11-08', 16, 1, 1131, '2000-08-08', NULL, '2023-03-08 11:15:29', 1),
(149, '5003', 0x9d880ffd30f5f080be310c589ad7d227, '2023-03-09', 'JOSE', 'NICOLAS', 'MARQUEZ', 'CEJAS', '1962-02-11', 16, 1, 1131, '2007-08-01', NULL, '2023-03-01 14:33:55', 1),
(150, '5007', 0xc371ab40bb31c34e95ae4516d646a82f, '2023-03-09', 'FREDDY', 'FRANCISCO', 'PERDOMO', '', '1949-05-17', 16, 1, 1131, '2008-08-01', NULL, '2023-03-08 15:20:24', 1),
(151, '6146', 0x9dae052e8459779324727042c745d5c0, '2023-03-09', 'ROBINSON', 'JOSE', 'ARANGUREN', 'MAESTRE', '1970-11-15', 1, 1, 1131, '2019-09-02', NULL, '2022-05-29 16:47:14', 1),
(152, '6128', 0x03cb2670612f39a496a117e7b4132624, '2023-03-09', 'JOSE', 'ANTONIO', 'ECKER', 'RANGEL', '1968-10-19', 37, 11, 1131, '2018-07-02', '2019-11-02', NULL, 1),
(153, '6145', 0xb8fc0ae4e80a2cbdf90ba796266bc350, '2023-03-09', 'JHON', 'EDUARDO', 'RONDON', 'BARRERA', '1969-08-19', 1, 1, 648, '2019-08-14', NULL, NULL, 2),
(154, '11540', 0x63ed7aeb031c9314dd620e19e77c79e2, '2023-03-09', 'SERGIO', 'FREDDYS', 'MÁRQUEZ', 'TOVAR', '1971-12-31', 16, 10, 612, '2013-05-01', NULL, NULL, 1),
(155, '6149', 0x56ebeace0d852918899efd37d698c006, '2023-03-09', 'ANA', 'KAYRET', 'PETIT', 'URBINA', '1982-11-04', 1, 1, 647, '2020-10-20', NULL, NULL, 2),
(156, '6150', 0x20aa455bdb16ddb4b36faf828c69bc8f, '2023-03-09', 'ENRIQUE', 'RAFAEL', 'CHIQUITO', 'SOSA', '1971-04-23', 1, 1, 647, '2020-10-01', '2021-01-28', NULL, 2),
(157, '11541', 0x7308e004e6ddbca18c8ef22b13a43adc, '2023-03-09', 'LEIDY', 'KASANDRA', 'SUESCUM', 'TAVIO', '1997-10-10', 3, 2, 647, '2020-11-09', '2021-01-22', NULL, 2),
(158, '6151', 0xf2a22cb545a9ea9ead1ad548eaeff6b7, '2023-03-09', 'MARYARIT', 'MARIANA', 'MEO', 'YANEZ', '1996-08-05', 1, 2, 644, '2020-12-01', NULL, NULL, 2),
(159, '11542', 0x89d39180296383654a46b43d86ad6d5c, '2023-03-09', 'FRANKLIN', 'ALBERTO', 'PACHECO', 'ACOSTA', '1980-10-18', 13, 3, 647, '2020-12-08', NULL, '2023-03-07 09:08:23', 1),
(160, '11543', 0x7bd1029606af4239490e64bb994df604, '2023-03-09', 'ORIANA', 'ELIZABETH', 'GRATEROL', 'GONZALEZ', '1997-09-17', 3, 2, 647, '2020-12-21', '2021-10-18', '2021-10-18 13:54:32', 2),
(161, '11544', 0xa3ec41d3844cef761c9d545f3a69c891, '2023-03-09', 'ALFREDO', 'DAVID', 'CONQUISTA', 'RODRIGUEZ', '1995-06-05', 7, 2, 1128, '2021-02-01', NULL, '2023-02-08 08:30:12', 1),
(162, '6152', 0xefa3a73f7ca913640d7834fc5a77ded9, '2023-03-09', 'EDGAR', 'WILMER', 'ANTON', 'MOLINA', '1965-11-15', 1, 1, 1121, '2021-02-22', NULL, '2021-07-26 17:23:41', 2),
(163, '11545', 0xef68ab6f518658282d5d8a4cfc741956, '2023-03-09', 'FREDY', 'DARIO', 'BAUTISTA', 'QUIJADA', '1990-10-30', 8, 1, 647, '2021-02-11', '2021-10-15', NULL, 2),
(164, '11546', 0x55299672e753dee7d23e6553d867ab30, '2023-03-09', 'IRIS', 'LUCYMAR', 'ESCORCHA', 'RONDON', '1978-05-05', 26, 12, 644, '2021-02-22', NULL, '2023-03-06 08:48:41', 1),
(173, '11547', 0xd5afc9de8704bf23bfc792f632ffc33c, '2023-03-09', 'CARLOS', 'EDUARDO', 'BASTIDAS', 'HERNANDEZ', '1991-11-24', 9, 6, 1119, '2021-03-22', '2022-01-21', '2022-01-21 14:12:14', 2),
(174, '11548', 0xa1e1400e80130b354a92794f2a2c4d8b, '2023-03-09', 'MARYSABEL', '', 'DOS SANTOS', 'CONTRERAS', '1986-06-20', 9, 5, 1133, '2021-04-07', NULL, '2023-02-28 16:16:21', 1),
(175, '6153', 0x372914d41de86dd92703cd0dee53bc62, '2023-03-09', 'IVETTE', 'ALEJANDRA', 'OROZCO', 'FLORES', '1994-02-23', 1, 1, 647, '2021-04-16', NULL, '2021-05-16 20:42:46', 2),
(176, '11549', 0x88f71d576b9b9a7eb02e7cdd3a50f189, '2023-03-09', 'WINNEY', 'JOHANA', 'BARRIENTOS', 'MC PHAIL', '1999-08-10', 5, 2, 647, '2021-05-03', '2022-12-05', '2022-11-21 13:19:12', 2),
(177, '11550', 0x00540a991feca08385be8c6e8cce1487, '2023-03-09', 'LEONELA', 'MICHELE', 'ZAMBELLA', 'OMAÑA', '1998-09-06', 2, 1, 647, '2021-05-10', '2021-11-01', '2021-11-02 18:50:00', 2),
(178, '11551', 0x523a178facc19f171d266721bfbd81ae, '2023-03-09', 'JUNEISY', 'ANIUSKA', 'BENITEZ', 'MACHADO', '1997-01-30', 2, 1, 647, '2021-05-10', '2021-09-03', '2021-07-02 14:37:35', 2),
(179, '11552', 0x885c2c2836ecc293b882ce6c349b479c, '2023-03-09', 'YESENIA', 'YULIMAR DEL VALLE', 'CASARES', 'PEROZO', '1996-04-09', 3, 2, 647, '2021-05-10', '2022-02-25', '2022-02-22 10:29:37', 2),
(180, '11553', 0xcd2cd819b3320262d69c7e6fddacf24d, '2023-03-09', 'OLIVER', 'IGNACIO', 'TOVAR', 'BENITEZ', '1999-08-20', 39, 1, 647, '2021-06-30', '2022-05-05', '2022-05-05 12:20:25', 2),
(181, '11554', 0x5f2991af2868d42d179591bec69d6776, '2023-03-09', 'RITCELIS', 'DEL VALLE', 'RUIZ', 'DIAZ', '1993-12-04', 21, 9, 647, '2021-07-01', '2021-07-30', NULL, 2),
(182, '1555', 0xc2d5d29ebe10d25ac764e28d1c9e0450, '2023-03-09', 'JENNY', 'LIS', 'SEGOVIA', 'ZAMBRANO', '1983-08-02', 12, 9, 647, '2021-07-01', NULL, '2023-03-08 15:48:18', 1),
(183, '11556', 0xc97e95a81b5bdf8ad3920e6feffcae91, '2023-03-09', 'CESAR', 'AUGUSTO', 'DIAZ', 'JARAMILLO', '1985-03-28', 12, 3, 647, '2021-07-01', NULL, '2023-03-09 09:06:41', 1),
(184, '11557', 0x4bf6b7f14a4f739f30e7fbbb58c7c5aa, '2023-03-09', 'DANALETH', 'DEL CARMEN', 'HERNANDEZ', 'MONASTERIO', '1999-08-11', 3, 2, 647, '2021-07-19', '2021-10-14', '2021-10-07 10:21:05', 2),
(185, '11558', 0x4243db303abf01eb84843b823bf020e5, '2023-03-09', 'JOHANNA', 'DE LA CRUZ', 'TRUJILLO', 'REVETE', '1981-07-01', 5, 6, 647, '2021-07-26', NULL, '2023-03-08 09:26:43', 1),
(186, '11559', 0x9b6418bd0e01cf1b747c12363ebbe592, '2023-03-09', 'MELANIE', 'ALEXANDRA', 'MARQUEZ', 'BAPTISTA', '2000-09-29', 2, 13, 647, '2021-07-19', NULL, '2023-03-07 15:53:14', 1),
(187, '11560', 0x167b7aadb75cbcc8034da842e0360171, '2023-03-09', 'ESCARLET', 'MAYERLINE', 'GUILLEN', 'GUILLEN', '1997-07-10', 6, 2, 647, '2021-09-01', NULL, '2023-02-02 10:27:46', 1),
(188, '11561', 0xa4dc3ebef91c2b91dde9e818e5514d38, '2023-03-09', 'NORBELIS', 'ALEJANDRA', 'MORRINSON', 'CORTEZ', '1997-06-04', 4, 2, 647, '2021-10-04', NULL, '2023-03-08 15:57:47', 1),
(189, '11562', 0xb57c626ac91c0c8cca36243dfc5c5070, '2023-03-09', 'ELEANA', 'GABRIELA', 'ROJAS', 'CUNYA', '1985-10-17', 11, 2, 647, '2021-10-04', '2022-01-19', '2022-01-19 10:23:42', 2),
(190, '11563', 0x6bf72610d23d278861e40c7cad6b76db, '2023-03-09', 'ANTHONI', 'CARLOS', 'FREITES', 'QUIROZ', '1977-06-30', 31, 11, 647, '2021-10-04', NULL, '2023-03-09 09:45:26', 1),
(191, '6154', 0x24021e390ff9b2f57b798c4b8abf26d0, '2023-03-09', 'JESUS', 'ALBERTO', 'LAYA', 'JIMENEZ', '1978-12-16', 1, 2, 647, '2021-09-15', NULL, '2023-03-08 16:57:44', 1),
(192, '11564', 0x4e7c83cf9599a3969f6c9ba397455a69, '2023-03-09', 'ANDREA', 'GABRIELA', 'GARCIA', 'GRANADOS', '1989-07-08', 12, 17, 647, '2021-11-01', NULL, '2023-03-06 10:02:02', 1),
(193, '11565', 0x1e28845a33ec486b78b33124ff3c51d9, '2023-03-09', 'OSCAR AUGUSTO', 'AUGUSTO', 'ROJO', 'SUAREZ', '1999-09-03', 33, 19, 647, '2021-11-01', NULL, '2023-03-09 08:59:40', 1),
(194, '11566', 0xd842c98458ea9c6ade77d1dbc41eada8, '2023-03-09', 'JOSE', 'JOEL', 'BOLIVAR', 'SIERRA', '1974-05-28', 27, 19, 647, '2021-11-01', NULL, '2023-03-09 09:06:12', 1),
(195, '11567', 0xefa3a73f7ca913640d7834fc5a77ded9, '2023-03-09', 'EDGAR', 'WILMER', 'ANTON', 'MOLINA', '1965-11-15', 12, 1, 647, '2021-11-22', NULL, '2023-03-06 09:13:25', 1),
(196, '11568', 0x970fcb0fbe4183e5401fd12b13f83342, '2023-03-09', 'BEYKER', 'ANDRES', 'LOYO', 'GONZALEZ', '1990-03-01', 31, 4, 647, '2021-11-29', '2022-08-30', '2022-08-23 13:14:21', 2),
(197, '11569', 0x19d67bb546785ba4cf0a4846589d6d11, '2023-03-09', 'PABLO', 'SAMUEL', 'MATA', 'HERNANDEZ', '1997-09-17', 6, 1, 647, '2021-12-06', '2021-12-07', NULL, 2),
(198, '11570', 0x36b9f98f9530546685fd0d79b1334907, '2023-03-09', 'BRANDON', 'ENMANUEL', 'RIVERA', 'HERNANDEZ', '1997-06-01', 5, 2, 647, '2021-12-06', NULL, '2022-02-10 13:59:07', 2),
(199, '11571', 0xfa3d9af2b86645168a4b41f7535bcde2, '2023-03-09', 'YANIX', 'XINAY', 'MONSALVE', 'MACHADO', '1995-10-03', 25, 7, 647, '2021-12-06', NULL, '2023-03-07 15:44:01', 1),
(200, '11572', 0x04c9bbac36c3b5f4c43b0fe680373e27, '2023-03-09', 'JUAN', 'PABLO', 'PEÑALOZA', 'DIAZ', '1994-01-19', 5, 1, 647, '2021-12-07', NULL, '2023-03-07 13:48:47', 1),
(201, '11573', 0x85909ab093c06e673fff7e5fdafb123c, '2023-03-09', 'GABRIEL', 'ALEJANDRO', 'ROJAS', 'RICO', '1987-01-25', 4, 2, 647, '2022-01-10', '2022-07-29', '2022-08-02 11:56:17', 2),
(202, '11574', 0xa1c4215f75bad02b8df90a6147ec01c7, '2023-03-09', 'JOSNELY', 'JHOVANNA', 'CASTILLO', 'GIL', '1999-12-22', 4, 6, 647, '2022-01-17', NULL, '2023-03-03 14:47:25', 1),
(203, '11575', 0x83188453dc3444ee4825d9ff7b2ab7a6, '2023-03-09', 'CHRISTHOPHER', 'EDUARDO', 'CABRERA', 'BAPTISTA', '1993-06-27', 12, 2, 647, '2022-02-01', NULL, '2023-03-08 16:46:15', 1),
(204, '11576', 0x36b9f98f9530546685fd0d79b1334907, '2023-03-09', 'BRANDON', 'ENMANUEL', 'RIVERA', 'HERNANDEZ', '1997-06-01', 5, 2, 647, '2021-12-06', NULL, '2023-03-08 16:54:55', 1),
(205, '6155', 0xad7b0fe3134e037387a7fc2742792df7, '2023-03-09', 'TOMAS', 'ANTONIO', 'MERIDA', 'GALINDO', '1956-02-19', 1, 10, 647, '2022-02-16', NULL, '2022-08-18 09:49:37', 2),
(206, '11578', 0x270f8aacfe8b59d553285073de46e171, '2023-03-09', 'GABRIEL', 'ALEJANDRO', 'MORA', 'CARVAJAL', '1996-09-29', 3, 1, 647, '2022-02-21', NULL, '2022-09-21 10:26:14', 2),
(207, '11577', 0x1e3d59a448dd78b09bf962402842d96b, '2023-03-09', 'JHON', 'JOSE', 'MARTINEZ', 'LONDIZA', '1990-01-28', 7, 4, 647, '2022-02-08', NULL, NULL, 1),
(208, '11579', 0xa1d5e8d025bd62b31b55b1761b283b5d, '2023-03-09', 'KEIBI', 'RAFAEL', 'MORENO', 'CAÑIZALES', '1995-05-09', 9, 6, 647, '2022-03-02', NULL, '2022-04-21 09:27:53', 2),
(209, '11580', 0xb25f3df715172d3c050d19cdc436f6b6, '2023-03-09', 'DEIRIANA', 'ANDREINA', 'PORTA', 'MENESES', '1997-05-15', 5, 2, 647, '2022-03-02', NULL, '2023-03-08 14:47:21', 1),
(210, '6156', 0x919482975a53e20fffe20a6efb3de610, '2023-03-09', 'LUIS', 'GIOVANNY', 'CARDENAS', 'RODRIGUEZ', '1972-02-24', 1, 1, 647, '2022-03-11', NULL, '2022-06-15 14:46:01', 2),
(211, '11581', 0x2ab195024686baaf25768e2065bc9ae4, '2023-03-09', 'GUILLERMO', 'ENRIQUE', 'LOAIZA', 'DIAZ', '2001-05-17', 31, 17, 647, '2022-03-21', '2022-05-25', '2022-05-16 08:27:05', 2),
(212, '11582', 0x2cfa81903478c0c02ffa1d25a47a5e9c, '2023-03-09', 'CESAR', 'TADEO', 'UBAN', 'BALZA', '1996-12-04', 8, 1, 647, '2022-04-04', '2022-08-30', '2022-08-26 11:50:10', 2),
(213, '11583', 0x0c1e4fd7c8ac773b9a9151cb111761a6, '2023-03-09', 'DINEXY', 'ANDREINA', 'PORTA', 'MENESES', '1993-12-02', 8, 2, 647, '2022-04-04', NULL, '2023-03-08 16:55:28', 1),
(214, '11584', 0x28de8fbe3a4fdbb7f6d7a6dac2b64216, '2023-03-09', 'RICARDO', 'ERNESTO', 'LEON', 'PIRELA', '2001-04-02', 33, 9, 647, '2022-04-20', NULL, '2023-03-02 15:01:36', 1),
(215, '11585', 0xa1d5e8d025bd62b31b55b1761b283b5d, '2023-03-09', 'KEIBI', 'RAFAEL', 'MORENO', 'CAÑIZALES', '1995-05-09', 9, 6, 647, '2022-03-02', NULL, '2023-03-03 14:45:10', 1),
(216, '11586', 0x33d71d2ac998727b52a71646e6b0b7fc, '2023-03-09', 'BARBARA', 'PAOLA', 'BETANCOURT', 'VAZQUEZ', '2002-09-27', 3, 1, 647, '2022-05-16', '2022-09-19', '2022-09-12 10:07:04', 2),
(217, '11587', 0x61c13eba8b8132974bc5608b31156ae9, '2023-03-09', 'KEIVER', 'DUVAN', 'AVILA', 'PEREZ', '1997-09-28', 7, 4, 647, '2022-05-23', '2023-01-31', NULL, 2),
(218, '11588', 0x3a0f2884ff1f4c417a37d5b7cb1c7491, '2023-03-09', 'KATHERINE', 'ESTHEFANIA', 'HERNANDEZ', 'GOMEZ', '1992-07-28', 10, 4, 647, '2022-05-23', '2023-02-28', NULL, 2),
(219, '11589', 0x358a7cc7d966e52a33f6f01e357d5ca2, '2023-03-09', 'YULIMAR', '', 'DIAZ', 'QUINTERO', '1978-07-21', 9, 4, 647, '2022-06-01', NULL, NULL, 1),
(220, '11590', 0x3ae6ae9251707b633e41f330f6b5351b, '2023-03-09', 'YURI', 'ANDREA', 'CHACON', 'MILLAN', '1990-06-11', 5, 1, 647, '2022-06-01', NULL, '2023-02-24 14:22:44', 1),
(221, '11591', 0x3ad57219988deea7bc7f6c706cca7e16, '2023-03-09', 'JOSMARLY', 'YOHANA', 'MALDONADO', 'MEDINA', '1994-11-26', 4, 1, 647, '2022-06-01', '2022-06-02', NULL, 2),
(222, '11592', 0xc13e19eb0be1fcd876dc98bb869f2628, '2023-03-09', 'WILBER', 'MOISES', 'ALGUETA', 'TORRES', '2000-10-27', 3, 1, 647, '2022-06-01', NULL, NULL, 2),
(223, '11593', 0x578a31baf9dc7435fdc4073125db7548, '2023-03-09', 'JOSE', 'GREGORIO', 'CASTELLANOS', 'QUINTO', '1987-07-25', 6, 3, 647, '2022-06-02', NULL, '2023-03-03 13:21:41', 1),
(224, '11594', 0x1f4f0e36be6624b89905a17e1bf64fdd, '2023-03-09', 'BELKIS', 'MARIA', 'FLOREAN', 'LAGUNA', '1981-12-11', 24, 7, 647, '2022-06-01', NULL, '2023-03-07 13:10:21', 1),
(225, '11595', 0x7f7f9c080befe0f50280fd2f13caff30, '2023-03-09', 'KEYBERT', 'EDUARDO', 'APARICIO', 'GONZALEZ', '1994-06-13', 3, 2, 647, '2022-06-16', NULL, '2023-03-08 21:19:04', 1),
(226, '11596', 0x062fdba11fcb6ba786cbb20ddee683ef, '2023-03-09', 'DOUGLENIS', 'DE LOS ANGELES', 'TABASQUEZ', 'MORALES', '1997-06-13', 9, 4, 647, '2022-07-18', '2022-09-02', NULL, 2),
(227, '11597', 0xd400af68e6a66f2485d927ed68c1c401, '2023-03-09', 'IVANA', 'CARIDAD', 'GUILARTE', 'PINTO', '1999-10-31', 39, 6, 647, '2022-07-19', '2022-12-02', '2022-12-06 09:57:18', 2),
(228, '11598', 0xd4e8ba398f297fed9566fa97f8425f24, '2023-03-09', 'ALEJANDRA', 'MARINA', 'SANCHEZ', 'CANCHICA', '1991-07-01', 3, 1, 647, '2022-08-01', NULL, '2023-03-07 10:16:00', 1),
(229, '11599', 0x5c4b6c5d07f43a01506dc573e0a15b1e, '2023-03-09', 'JOSE', 'ANDRES', 'HERNANDEZ', 'RUIZ', '1999-03-16', 3, 2, 647, '2022-08-10', NULL, '2023-03-09 09:03:43', 1),
(230, '11600', 0x06e477466a371d1eacd75ae15905d43d, '2023-03-09', 'JOSE', 'MIGUEL', 'PEROZO', 'HERRERA', '1994-10-04', 9, 1, 647, '2022-08-15', NULL, '2023-03-09 09:26:22', 1),
(231, '11601', 0x18a089795f781c551411046258024a92, '2023-03-09', 'RAINIER', 'HELY', 'ROJAS', 'HADDAD', '1996-10-03', 8, 19, 647, '2022-08-15', NULL, '2023-03-06 09:01:11', 1),
(232, '11602', 0x6d563507ea9b4aaeb43a6c5b03debed4, '2023-03-09', 'ANGELICA', 'MARIA', 'LUGO', 'RAMIREZ', '2000-07-11', 39, 6, 647, '2022-10-10', '2023-01-03', '2023-01-03 01:26:19', 2),
(233, '11610', 0x2fe75e25db5950d7f3192bd9a567c437, '2023-03-09', 'MARIA', 'LAURA', 'GARCIA', 'JUAREZ', '1999-12-15', 4, 4, 647, '2022-10-17', '2022-10-28', NULL, 2),
(234, '11611', 0x59edc43aa33ee663acbfa31a3c083b0f, '2023-03-09', 'JENNY', 'LUCIA', 'LIMA', 'HAMILTON', '1967-08-05', 10, 4, 647, '2022-11-07', '2022-12-02', NULL, 2),
(235, '11612', 0xba881c64c8046bcca8225185fff51e8d, '2023-03-09', 'CRISBET', 'YOHANNI', 'BARCELO', 'CASTRO', '1993-12-19', 9, 4, 647, '2022-11-07', '2023-03-03', NULL, 2),
(236, '11613', 0x208e6671311570c0cbcbf76b6f772a97, '2023-03-09', 'ARLEANNY', 'ALEXARI', 'MARRERO', 'QUINTERO', '2002-07-16', 3, 2, 647, '2022-11-07', NULL, '2023-03-08 16:14:21', 1),
(237, '11614', 0x4fd02b9a12f2b1a08392566ab29ba9ad, '2023-03-09', 'JOSE', 'LUIS', 'DIAZ', 'HERRERA', '1997-02-26', 39, 6, 647, '2022-11-08', NULL, '2023-02-09 09:37:02', 1),
(238, '11615', 0x6896b72aa59eeaaa40242214739a1bce, '2023-03-09', 'ORLAIMY', 'SAIR', 'MUÑOZ', 'JAIMES', '2001-06-28', 39, 6, 647, '2022-12-12', NULL, '2023-03-09 09:30:48', 1),
(239, '11616', 0x9362ccc5dcb88e9f44ca6ef99a9b3cc3, '2023-03-09', 'JOSMAN', 'JOSUE', 'FUENTES', 'GRIMAN', '2000-07-17', 3, 2, 647, '2023-01-09', NULL, '2023-03-08 17:00:33', 1),
(240, '11617', 0x988bf2793315f1ba3fbdee9d3e89b5cd, '2023-03-09', 'JORGENIS', 'JOSE', 'GUERRA', 'LEZAMA', '1996-11-28', 39, 6, 647, '2023-01-23', NULL, '2023-03-09 09:23:18', 1),
(241, '11618', 0x7d038db3c2a8022d9d8c23c9b36a2077, '2023-03-09', 'CESAR', 'LEANDRO', 'GARCIA', 'AULAR', '2001-08-14', 39, 6, 647, '2023-01-23', NULL, '2023-03-09 09:36:13', 1),
(242, '6157', 0x59a218968d4072da6231f2280bddaf7a, '2023-03-09', 'MAYERLING', 'KARINA', 'VALERA', 'RIVAS', '1975-10-19', 1, 2, 647, '2022-10-17', NULL, NULL, 1),
(243, '6159', 0x8dc9c35a2bba3db28169f52d28b5f527, '2023-03-09', 'YULITZA', 'DEL VALLE', 'ESPARRAGOZA', 'CASTILLEJO', '2023-02-06', 1, 1, 647, '2023-02-06', NULL, NULL, 1),
(244, '11619', 0xcbcdd03242d775e13741b2308aca4db6, '2023-03-09', 'RAUL', 'HUMBERTO', 'BRICEÑO', 'CORREA', '1991-02-23', 10, 4, 647, '2023-02-13', NULL, NULL, 1),
(245, '11620', 0xbfc5e4293bf330bde6ace6bfd37f234e, '2023-03-09', 'MARY', 'ISABEL', 'ROJAS', '', '1967-08-16', 6, 1, 647, '2023-02-13', NULL, NULL, 1),
(246, '11621', 0xa679acfe3d0deef0b6ff5b7e553b2b47, '2023-03-09', 'RUBI', 'YABISAY', 'RAMIREZ', 'LOPEZ', '1974-01-06', 6, 1, 647, '2023-02-14', NULL, '2023-03-07 16:06:08', 1),
(247, '11622', 0x038e973a488134d8436c8bae5b50d66c, '2023-05-04', 'CARLOS', 'EDUARDO', 'NAVAS', 'SALAZAR', '1994-11-22', 39, 13, 647, '2023-02-13', NULL, '2023-04-16 00:16:02', 1),
(248, '11623', 0x900ddc90e683652dd185b78eb9554e81, '2023-03-09', 'MIRIAM', 'DESIREE', 'HIDALGO', 'BRICEÑO', '1983-07-11', 10, 4, 647, '2023-03-07', '2023-03-07', NULL, 2),
(249, '11624', 0xb6ef6411736091f6c4a585e5f1044712, '2023-07-25', 'CHEGEL', 'ARIANNY', 'ROJAS', 'GONZALEZ', '1999-09-01', 3, 6, 647, '2023-05-08', NULL, '2023-06-02 14:44:42', 1),
(250, '11625', 0x0bf800383dde5676eae74830c5188010, '2023-07-25', 'PAOLA', 'ALEXANDRA', 'BRACAMONTE', 'HERNANDEZ', '1998-05-12', 3, 6, 647, '2023-05-09', NULL, '2023-06-05 09:54:32', 1),
(251, '11626', 0x62a39a6bd3033933086aab1cc25fa40f, NULL, 'ROBIN', 'ANTONIO', 'QUINTERO', 'COLMENARES', '1993-07-04', 11, 4, 647, '2023-05-09', NULL, NULL, 1),
(252, '11627', 0x9a298fffe450373d8de2af5bc8222b19, NULL, 'ORLANDO', 'DAVID', 'LUGO', 'BARRIOS', '1998-06-18', 5, 4, 647, '2023-05-15', NULL, NULL, 1),
(253, '11628', 0xd01da29e00321ebb654b7367745424be, '2023-07-31', 'KISLEV', 'ZARONSKY', 'ZAMBRANO', 'MELENDEZ', '1996-11-08', 2, 13, 647, '2023-05-22', NULL, '2023-06-05 09:40:44', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_address_municipalities`
--

CREATE TABLE `users_address_municipalities` (
  `municipality_id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `municipality_name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users_address_municipalities`
--

INSERT INTO `users_address_municipalities` (`municipality_id`, `state_id`, `municipality_name`) VALUES
(0, 0, 'Municipality not registered'),
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
-- Estructura de tabla para la tabla `users_address_parishes`
--

CREATE TABLE `users_address_parishes` (
  `parish_id` int(11) NOT NULL,
  `municipality_id` int(11) NOT NULL,
  `parish_name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users_address_parishes`
--

INSERT INTO `users_address_parishes` (`parish_id`, `municipality_id`, `parish_name`) VALUES
(0, 0, 'Parish not registered'),
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
-- Estructura de tabla para la tabla `users_address_states`
--

CREATE TABLE `users_address_states` (
  `state_id` int(11) NOT NULL,
  `state_name` text NOT NULL,
  `iso_31662` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users_address_states`
--

INSERT INTO `users_address_states` (`state_id`, `state_name`, `iso_31662`) VALUES
(0, 'State not registered', '0'),
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
-- Estructura de tabla para la tabla `users_contact`
--

CREATE TABLE `users_contact` (
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `primary_email` varchar(255) DEFAULT NULL,
  `secondary_email` varchar(255) DEFAULT NULL,
  `primary_phone` varchar(30) DEFAULT NULL,
  `secondary_phone` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users_contact`
--

INSERT INTO `users_contact` (`contact_id`, `user_id`, `primary_email`, `secondary_email`, `primary_phone`, `secondary_phone`) VALUES
(1, 1, 'dmolina101@gmail.com', '', '04244463739', ''),
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
(47, 47, 'jose.perozo@dominio.com', '', '04262539113', ''),
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
(99, 99, 'jennifer.villa@crowe.com.ve', '', '', ''),
(100, 100, 'anacecilia.castano@crowe.com.ve', '', '02125716504', ''),
(101, 101, 'amayoisbi.garcia@crowe.com.ve', '', '04127013435', ''),
(102, 102, 'jennifer.chacon@crowe.com.ve', '', '04125897240', ''),
(103, 103, 'ignayari.mendoza@crowe.com.ve', '', '04129289923', ''),
(104, 104, 'reina.fajardo@crowe.com.ve', '', '04164269965', ''),
(105, 105, 'yolimer.mendoza@crowe.com.ve', '', '04149018276', '02126813348'),
(106, 106, 'ignayari.mendoza@crowe.com.ve', '', '04129762870', ''),
(107, 107, 'adriana.guzman@crowe.com.ve', '', '02129412882', '04144549562'),
(108, 108, 'jose.estaba@crowe.com.ve', '', '02128602803', '04243389487'),
(109, 109, 'karina.perez@crowe.com.ve', '', '04265920655', ''),
(110, 110, 'zonny.garcia@crowe.com.ve', '', '04243138868', '02392252293'),
(111, 111, 'nombre.apellido@dominio.com', '', '04268870548', ''),
(112, 112, 'nombre.apellido@dominio.com', '', '04262166223', ''),
(113, 113, 'leonardo.alopez21@gmail.com', '', '04142598750', ''),
(114, 114, 'josearturo0706@gmail.com', '', '02124818970', '04128259076'),
(115, 115, 'nombre.apellido@dominio.com', '', '02124518087', '04129576671'),
(116, 116, 'antonio.reyes@crowe.com.ve', '', '02122425335', '04141626367'),
(117, 117, 'duvan.pinto@crowe.com.ve', '', '04241842688', ''),
(118, 118, 'freddy.perdomo@crowe.com.ve', '', '02129766425', '04144466147'),
(119, 119, 'fernando.rangel@crowe.com.ve', '', '04141782596', ''),
(120, 120, 'gelen.cardenas@crowe.com.ve', '', '02125767453', '04164654993'),
(121, 121, 'nombre.apellido@dominio.com', '', '02122373113', ''),
(122, 122, 'nombre.apellido@dominio.com', '', '02122373113', '04142081976'),
(123, 123, 'laura.rojas@crowe.com.ve', '', '04169322811', ''),
(124, 124, 'amelia.diaz@crowe.com.ve', '', '', ''),
(125, 125, 'emilio.leon@crowe.com.ve', '', '04166084971', '04241180197'),
(126, 126, 'gustavo.puchi@crowe.com.ve', '', '02124834655', '04122206492'),
(127, 127, 'alfio.saglimbeni@crowe.com.ve', '', '04168272679', ''),
(128, 128, 'arianna.matos@crowe.com.ve', '', '02123238208', '04126000531'),
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
(143, 143, 'mary.cruz@crowe.com.ve', '', '04249686614', '02869341430'),
(144, 144, 'sergio.marquez@crowe.com.ve', 'sergiofmarquezt@gmail.com', '04149070900', ''),
(145, 145, 'nelson.marcano@crowe.com.ve', '', '04120195573', ''),
(146, 146, 'alfio.saglimbeni@crowe.com.ve', 'smarquezt66@gmail.com', '', ''),
(147, 147, 'antonio.dugarte@crowe.com.ve', '', '04242265723', ''),
(148, 148, 'mirnangela.salaya@crowe.com.ve', '', '04241511028', ''),
(149, 149, 'jose.marquez@crowe.com.ve', '', '04142554850', ''),
(150, 150, 'freddyperdomo17@gmail.com', '', '', ''),
(151, 151, 'robison.aranguren@crowe.com.ve', '', '04141349727', ''),
(152, 152, 'joseecker51@gmail.com', '', '04141017189', '04142638949'),
(153, 153, 'jhon.rondon@crowe.com.ve', 'eduardobarrera69@gmail.com', '04123186673', ''),
(154, 154, 'contraloriacrowe@gmail.com', '', '04149070900', '02122350147'),
(155, 155, 'anapetit04@gmail.com', '', '04149330573', ''),
(156, 156, 'enrique.chiquito@gmail.com', '', '04122087873', '04265332319'),
(157, 157, 'suscumleidy@gmail.com', '', '04122935740', '02125814635'),
(158, 158, 'mary050896@gmail.com', '', '04143253136', '02123424248'),
(159, 159, 'franklin.pacheco@crowe.com.ve', '', '04241565718', ''),
(160, 160, 'oriana.graterol@crowe.com.ve', '', '04125717905', '02128629219'),
(161, 161, 'alfredo.conquista@crowe.com.ve', '', '04241106550', '02124160138'),
(162, 162, 'wilmeranton65@gmail.com', '', '04143141335', '04241541595'),
(163, 163, 'freddy.bautista@crowe.com.ve', '', '04141093990', '02129763028'),
(164, 164, 'iris.escorcha@crowe.com.ve', '', '04264063883', '02123622056'),
(173, 173, 'carlos.bastidas@crowe.com.ve', 'clbastidas91@gmail.com', '04126394216', ''),
(174, 174, 'marysabel.dossantos@crowe.com.ve', '', '04265101377', ''),
(175, 175, 'ivetteorozco1994@gmail.com', '', '04242613215', ''),
(176, 176, 'winney.barrientos@crowe.com.ve', 'winneyphail18@gmail.com', '04267042706', '04142415947'),
(177, 177, 'leonela.zambella@crowe.com.ve', 'leonelaz1998@gmail.com', '02124715309', '04242364607'),
(178, 178, 'juneisy.benitez@crowe.com.ve', 'june.abm@gmail.com', '04122959794', '04264073835'),
(179, 179, 'yesenia.casares@crowe.com.ve', '', '04241433149', '04127285524'),
(180, 180, 'oliver.tovar@crowe.com.ve', 'tovaroliver22@gmail.com', '04242721414', ''),
(181, 181, 'ritcelis.ruiz@crowe.com.ve', '', '04123099563', ''),
(182, 182, 'jenny.lis@crowe.com.ve', '', '04142063611', '02127631690'),
(183, 183, 'cesar.diaz@crowe.com.ve', '', '04141136672', ''),
(184, 184, 'danaleth.hernandez@crowe.com.ve', 'danaleth@gmail.com', '04264157175', '04129629136'),
(185, 185, 'johanna.trujillo@crowe.com.ve', 'jcrevette_@hotmail.com', '04142862057', ''),
(186, 186, 'melanie.marquez@crowe.com.ve', 'melaniealexandra.m@gmail.com', '02123697554', '04242608583'),
(187, 187, 'escarlet.guillen@crowe.com.ve', 'escarletguillen@gmail.com', '04149177892', ''),
(188, 188, 'morrinsonn@gmail.com', 'morrinsonn@gmail.com', '04241616129', '02125858604'),
(189, 189, 'gabigabi175@gmail.com', '', '04242779956', ''),
(190, 190, 'anthoni.freites@gmail.com', '', '04122093659', ''),
(191, 191, 'layajesus@gmail.com', '', '', ''),
(192, 192, 'andrea.garcia@crowe.com.ve', '', '04241061875', ''),
(193, 193, 'oscarrojo999@gmail.com', '', '04141332793', ''),
(194, 194, 'bolivarsierrajose@gmail.com', '', '', ''),
(195, 195, 'wilmer.anton@crowe.com.ve', '', '04143141335', ''),
(196, 196, 'beiker.loyo@crowe.com.ve', '', '04160533153', ''),
(197, 197, 'pablo.mata@crowe.com.ve', '', '04126390605', '02127145134'),
(198, 198, 'brandon.rivera@crowe.com.ve', '', '04142580055', '02128585721'),
(199, 199, 'laura.rojas@crowe.com.ve', '', '04141605224', ''),
(200, 200, 'juan.peñaloza@crowe.com.ve', '', '04241509619', '02126625836'),
(201, 201, 'gabriel.rojas@crowe.com.ve', '', '04141354747', ''),
(202, 202, 'josnely.castillo@crowe.com.ve', '', '04168366572', ''),
(203, 203, 'christhopher.cabrera@crowe.com.ve', '', '04141862719', ''),
(204, 204, 'brandon.rivea@crowe.com.ve', 'brandon.rivea@crowe.com.ve', '04142580055', ''),
(205, 205, 'tomega9120@hotmail.com', 'tomega9120@hotmail.com', '04142466825', '02122413316'),
(206, 206, 'gabriel.mora@crowe.com.ve', '', '04241479638', ''),
(207, 207, 'jhon.martinez@crowe.com.ve', '', '04242024245', ''),
(208, 208, 'keibimoreno@crowehowart.com', '', '04141269931', ''),
(209, 209, 'deiriana.porta@crowe.com.ve', '', '04241235742', ''),
(210, 210, 'ignayari.mendoza@crowe.com.ve', 'cardenaslg2000@yahoo.com', '04129601010', ''),
(211, 211, 'guillermo.loaiza@crowe.com.ve', 'guillermoloaiza2001@gmail.com', '04241365019', ''),
(212, 212, 'cesar.uban@crowe.com.ve', '', '04149267484', ''),
(213, 213, 'dinexy.porta@crowe.com.ve', '', '04127377145', ''),
(214, 214, 'ricardo.leon@crowe.com.ve', '', '04122112830', ''),
(215, 215, 'keibi.moreno@crowe.com.ve', '', '04141269931', ''),
(216, 216, 'barbara.betancourt@crowe.com.ve', '', '04126371772', ''),
(217, 217, 'keiver.avila@crowe.com', '', '04241487560', ''),
(218, 218, 'katherine.hernandez@crowe.com.ve', '', '04140213336', '02126310289'),
(219, 219, 'yulimar.diaz@crowe.com.ve', '', '04149077239', ''),
(220, 220, 'yuri.chacon@crowe.com.ve', '', '04144663156', ''),
(221, 221, 'josmarly.maldonado@crowe.com.ve', '', '04242791966', ''),
(222, 222, 'wilber.algueta@crowe.com.ve', '', '04241978449', ''),
(223, 223, 'jose.castellanos@crowe.com.ve', '', '04129384012', ''),
(224, 224, 'belkis.florean@crowe.com.ve', '', '04241642116', ''),
(225, 225, 'keybert.aparicio@crowe.com.ve', '', '04129118289', ''),
(226, 226, 'douglenis.tabasquez@crowe.com.ve', '', '04241688418', ''),
(227, 227, 'ivana.guilarte@crowe.com.ve', '', '04142155483', ''),
(228, 228, 'alejandra.sanchez@crowe.com.ve', '', '04247755090', ''),
(229, 229, 'jose.hernandez@crowe.com.ve', '', '04242016719', ''),
(230, 230, 'jose.perozo@crowe.com.ve', '', '04241288112', '04123389072'),
(231, 231, 'fernando.rangel@crowe.com.ve', '', '04241653758', ''),
(232, 232, 'angelica.lugo@crowe.com.ve', '', '04125811373', ''),
(233, 233, 'maria.garcia@crowe.com.ve', '', '04142741686', ''),
(234, 234, 'jenny.lima@crowe.com.ve', '', '04241603064', '02126932351'),
(235, 235, 'crisbet.barcelo@crowe.com.ve', '', '04127318638', '02127532952'),
(236, 236, 'arleanny.marrero@crowe.com.ve', '', '04123351501', ''),
(237, 237, 'jose.diaz@crowe.com.ve', '', '04242985622', '04149902681'),
(238, 238, 'orlaimy.muÑoz@gmail.com', '', '04127145942', ''),
(239, 239, 'josman.fuentes@crowe.com.ve', '', '04142204960', ''),
(240, 240, 'jorgenis.guerra@crowe.com.ve', '', '04120136708', '02123527361'),
(241, 241, 'cesar.garcia@crowe.com.ve', '', '04242388485', ''),
(242, 242, 'mays_krv@gmail.com', '', '04120900323', '02123655132'),
(243, 243, 'yulitzaesparragoza@hotmail.com', '', '04129974260', ''),
(244, 244, 'raul.briceño@crowe.com.ve', '', '04142895119', ''),
(245, 245, 'mary.rojas@crowe.com.ve', '', '04126100692', ''),
(246, 246, 'rubi.ramirez@crowe.com.ve', '', '04129101603', ''),
(247, 247, 'carlosnavased@gmail.com', '', '04242813274', ''),
(248, 248, 'miriam.hidalgo@crowe.com.ve', '', '04125411409', ''),
(249, 249, 'chegel.rojas@crowe.com.ve', '', NULL, NULL),
(250, 250, 'paola.bracamonte@crowe.com.ve', '', '(0424) - 133 8563', NULL),
(251, 251, 'robin.quintero@crowe.com.ve', '', '(0424) - 672 2940', NULL),
(252, 252, 'orlando.lugo@crowe.com.ve', '', '(0412) - 098 1954', '(0424) - 127 8790'),
(253, 253, 'kislev.zambrano@crowe.com.ve', '', '(0412) - 933 6309', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_hierarchy_departments`
--

CREATE TABLE `users_hierarchy_departments` (
  `department_id` int(11) NOT NULL,
  `department_name` text NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users_hierarchy_departments`
--

INSERT INTO `users_hierarchy_departments` (`department_id`, `department_name`, `status_id`) VALUES
(0, 'Department not registered', 1),
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_hierarchy_positions`
--

CREATE TABLE `users_hierarchy_positions` (
  `position_id` int(11) NOT NULL,
  `position_name` text NOT NULL,
  `position_type_id` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users_hierarchy_positions`
--

INSERT INTO `users_hierarchy_positions` (`position_id`, `position_name`, `position_type_id`, `order`, `status_id`) VALUES
(0, 'Position not registered', 3, 0, 1),
(1, 'Contratado por horas', 1, 17, 1),
(2, 'Pasantes', 1, 1, 1),
(3, 'Asistente I', 1, 2, 1),
(4, 'Asistente II', 1, 2, 1),
(5, 'Asistente III', 1, 2, 1),
(6, 'Semi-Senior I', 1, 3, 1),
(7, 'Semi-Senior II', 1, 3, 1),
(8, 'Semi-Senior III', 1, 3, 1),
(9, 'Senior I', 1, 4, 1),
(10, 'Senior II', 1, 4, 1),
(11, 'Senior III', 1, 4, 1),
(12, 'Supervisor', 3, 5, 1),
(13, 'Gerente', 3, 6, 1),
(14, 'Gerente Senior', 3, 6, 1),
(15, 'Director', 3, 7, 1),
(16, 'Socio', 3, 9, 1),
(17, 'Acting Partner', 1, 8, 1),
(18, 'Asesor Legal', 1, 7, 1),
(19, 'Asistente de Socios', 2, 10, 1),
(20, 'Asistente de Gerentes', 2, 10, 1),
(21, 'Analista', 2, 12, 1),
(22, 'Chofer', 2, 15, 1),
(23, 'Supervisor de Mantenimiento', 2, 5, 1),
(24, 'Operaria de Mantenimiento', 2, 16, 1),
(25, 'Recepcionista', 2, 11, 1),
(26, 'Editora', 2, 14, 1),
(27, 'Analista Senior I', 2, 12, 1),
(28, 'Analista Senior II', 2, 12, 1),
(29, 'Analista Senior III', 2, 12, 1),
(30, 'Editora', 2, 14, 2),
(31, 'Asistente', 2, 10, 1),
(32, 'Asistente de Facturación y Cobranza', 2, 10, 1),
(33, 'Asistente Administrativo', 2, 10, 1),
(34, 'Soporte Técnico I', 2, 13, 1),
(35, 'Soporte Técnico II', 2, 13, 1),
(36, 'Soporte Técnico III', 2, 13, 1),
(37, 'Mensajero', 2, 15, 1),
(38, 'Recepcionista', 2, 11, 2),
(39, 'Pasante', 2, 1, 1),
(40, 'Trabajador Social', 2, 14, 1),
(41, 'Pasante Inces', 2, 1, 1),
(42, 'Asistente de Proyecto', 2, 10, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_identity`
--

CREATE TABLE `users_identity` (
  `identity_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `identity_type_id` int(11) NOT NULL,
  `identity_number` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users_identity`
--

INSERT INTO `users_identity` (`identity_id`, `user_id`, `identity_type_id`, `identity_number`) VALUES
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
(248, 248, 1, '15759452'),
(249, 249, 1, '111111111111');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_identity_type`
--

CREATE TABLE `users_identity_type` (
  `identity_type_id` int(11) NOT NULL,
  `identity_abbreviation` varchar(5) NOT NULL,
  `identity_description` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users_identity_type`
--

INSERT INTO `users_identity_type` (`identity_type_id`, `identity_abbreviation`, `identity_description`) VALUES
(1, 'V', 'Cédula Venezolana'),
(2, 'E', 'Cédula Extranjera');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users_position_type`
--

CREATE TABLE `users_position_type` (
  `position_type_id` int(11) NOT NULL,
  `position_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `users_position_type`
--

INSERT INTO `users_position_type` (`position_type_id`, `position_description`) VALUES
(1, 'Profesional'),
(2, 'Administrativo'),
(3, 'Profesional y Administrativo');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_clients_preview`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_clients_preview` (
`codigo` int(11)
,`socio encargado` varchar(83)
,`razon social` varchar(500)
,`correo` varchar(100)
,`estatus` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_control_status_all`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_control_status_all` (
`status_id` int(11)
,`status_description` text
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_projects_preview`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_projects_preview` (
`codigo` int(11)
,`proyecto` text
,`horas contratadas` decimal(32,0)
,`fecha contratacion` date
,`cliente` varchar(500)
,`socio` varchar(83)
,`gerente` varchar(83)
,`estatus` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_users_preview`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_users_preview` (
`codigo` varchar(6)
,`cedula` varchar(255)
,`nombre` varchar(83)
,`correo` varchar(255)
,`estatus` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_users_status`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_users_status` (
`status_id` int(11)
,`status_description` text
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_users_update`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_users_update` (
`user_id` int(11)
,`user_code` varchar(6)
,`status_id` int(11)
,`first_name` varchar(20)
,`second_name` varchar(20)
,`first_surname` varchar(20)
,`second_surname` varchar(20)
,`birthday` date
,`admission_date` date
,`departure_date` date
,`identity_abbreviation` varchar(5)
,`identity_number` varchar(255)
,`primary_email` varchar(255)
,`secondary_email` varchar(255)
,`primary_phone` varchar(30)
,`secondary_phone` varchar(30)
,`parish_id` int(11)
,`municipality_id` int(11)
,`state_id` int(11)
,`position_id` int(11)
,`department_id` int(11)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_clients_preview`
--
DROP TABLE IF EXISTS `vw_clients_preview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_clients_preview`  AS SELECT `cs`.`client_code` AS `codigo`, concat(`us`.`first_name`,' ',`us`.`second_name`,' ',`us`.`first_surname`,' ',`us`.`second_surname`) AS `socio encargado`, `cs`.`bussiness_name` AS `razon social`, `cs`.`tax_email` AS `correo`, `us`.`status_id` AS `estatus` FROM (`clients` `cs` join `users` `us` on(`cs`.`partner_user_id` = `us`.`user_id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_control_status_all`
--
DROP TABLE IF EXISTS `vw_control_status_all`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_control_status_all`  AS SELECT `control_status`.`status_id` AS `status_id`, `control_status`.`status_description` AS `status_description` FROM `control_status` WHERE `control_status`.`status_id` between 1 and 2 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_projects_preview`
--
DROP TABLE IF EXISTS `vw_projects_preview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_projects_preview`  AS SELECT `ps`.`project_id` AS `codigo`, `ps`.`project_description` AS `proyecto`, (select sum(`psda`.`hours_assigned`) from `projects_departments_assigned` `psda` where `psda`.`project_id` = `ps`.`project_id`) AS `horas contratadas`, `ps`.`hiring_date` AS `fecha contratacion`, `cs`.`bussiness_name` AS `cliente`, concat(`us1`.`first_name`,' ',`us1`.`second_name`,' ',`us1`.`first_surname`,' ',`us1`.`second_surname`) AS `socio`, concat(`us2`.`first_name`,' ',`us2`.`second_name`,' ',`us2`.`first_surname`,' ',`us2`.`second_surname`) AS `gerente`, `ps`.`status_id` AS `estatus` FROM (((`projects` `ps` join `clients` `cs` on(`ps`.`client_id` = `cs`.`client_id`)) join `users` `us1` on(`ps`.`partner_id` = `us1`.`user_id`)) join `users` `us2` on(`ps`.`manager_id` = `us2`.`user_id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_users_preview`
--
DROP TABLE IF EXISTS `vw_users_preview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_users_preview`  AS SELECT `us`.`user_code` AS `codigo`, `ui`.`identity_number` AS `cedula`, concat(`us`.`first_name`,' ',`us`.`second_name`,' ',`us`.`first_surname`,' ',`us`.`second_surname`) AS `nombre`, `uc`.`primary_email` AS `correo`, `us`.`status_id` AS `estatus` FROM ((`users` `us` join `users_identity` `ui` on(`ui`.`user_id` = `us`.`user_id`)) join `users_contact` `uc` on(`uc`.`user_id` = `us`.`user_id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_users_status`
--
DROP TABLE IF EXISTS `vw_users_status`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_users_status`  AS SELECT `control_status`.`status_id` AS `status_id`, `control_status`.`status_description` AS `status_description` FROM `control_status` WHERE `control_status`.`status_id` between 1 and 4 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_users_update`
--
DROP TABLE IF EXISTS `vw_users_update`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_users_update`  AS SELECT `us`.`user_id` AS `user_id`, `us`.`user_code` AS `user_code`, `us`.`status_id` AS `status_id`, `us`.`first_name` AS `first_name`, `us`.`second_name` AS `second_name`, `us`.`first_surname` AS `first_surname`, `us`.`second_surname` AS `second_surname`, `us`.`birthday` AS `birthday`, `us`.`admission_date` AS `admission_date`, `us`.`departure_date` AS `departure_date`, `ut`.`identity_abbreviation` AS `identity_abbreviation`, `ui`.`identity_number` AS `identity_number`, `uc`.`primary_email` AS `primary_email`, `uc`.`secondary_email` AS `secondary_email`, `uc`.`primary_phone` AS `primary_phone`, `uc`.`secondary_phone` AS `secondary_phone`, `us`.`parish_id` AS `parish_id`, `um`.`municipality_id` AS `municipality_id`, `uas`.`state_id` AS `state_id`, `us`.`position_id` AS `position_id`, `us`.`department_id` AS `department_id` FROM ((((((`users` `us` join `users_identity` `ui` on(`ui`.`user_id` = `us`.`user_id`)) join `users_identity_type` `ut` on(`ut`.`identity_type_id` = `ui`.`identity_type_id`)) join `users_contact` `uc` on(`uc`.`user_id` = `us`.`user_id`)) join `users_address_parishes` `up` on(`up`.`parish_id` = `us`.`parish_id`)) join `users_address_municipalities` `um` on(`um`.`municipality_id` = `up`.`municipality_id`)) join `users_address_states` `uas` on(`uas`.`state_id` = `um`.`state_id`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD KEY `FK_clients_countries_id` (`country_id`),
  ADD KEY `FK_clients_sectors_id` (`sector_id`),
  ADD KEY `FK_clients_partner_users_id` (`partner_user_id`),
  ADD KEY `FK_clients_services_id` (`service_id`),
  ADD KEY `FK_clients_status_id` (`status_id`);

--
-- Indices de la tabla `clients_countries`
--
ALTER TABLE `clients_countries`
  ADD PRIMARY KEY (`country_id`);

--
-- Indices de la tabla `clients_sectors`
--
ALTER TABLE `clients_sectors`
  ADD PRIMARY KEY (`sector_id`),
  ADD KEY `FK_sector_status_id` (`status_id`);

--
-- Indices de la tabla `clients_services`
--
ALTER TABLE `clients_services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `FK_services_status_id` (`status_id`);

--
-- Indices de la tabla `control_companies`
--
ALTER TABLE `control_companies`
  ADD PRIMARY KEY (`company_id`),
  ADD KEY `FK_companies_status_id` (`status_id`);

--
-- Indices de la tabla `control_currencies`
--
ALTER TABLE `control_currencies`
  ADD PRIMARY KEY (`currency_id`),
  ADD KEY `FK_currencies_status_id` (`status_id`);

--
-- Indices de la tabla `control_encrypts`
--
ALTER TABLE `control_encrypts`
  ADD PRIMARY KEY (`encrypt_id`),
  ADD KEY `FK_encrypt_status` (`status_id`) USING BTREE;

--
-- Indices de la tabla `control_errors`
--
ALTER TABLE `control_errors`
  ADD PRIMARY KEY (`error_id`),
  ADD KEY `FK_errors_type_message_id` (`type_message_id`),
  ADD KEY `FK_errors_type_object_id` (`type_object_id`);

--
-- Indices de la tabla `control_errors_type_messages`
--
ALTER TABLE `control_errors_type_messages`
  ADD PRIMARY KEY (`type_message_id`);

--
-- Indices de la tabla `control_errors_type_object`
--
ALTER TABLE `control_errors_type_object`
  ADD PRIMARY KEY (`type_object_id`);

--
-- Indices de la tabla `control_logs`
--
ALTER TABLE `control_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `FK_logs_action_id` (`action_id`);

--
-- Indices de la tabla `control_logs_action`
--
ALTER TABLE `control_logs_action`
  ADD PRIMARY KEY (`action_id`);

--
-- Indices de la tabla `control_status`
--
ALTER TABLE `control_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indices de la tabla `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `FK_project_client_id` (`client_id`),
  ADD KEY `FK_project_partner_id` (`partner_id`),
  ADD KEY `FK_project_quality_partner_id` (`quality_partner_id`),
  ADD KEY `FK_project_manager_id` (`manager_id`),
  ADD KEY `FK_project_currency_id` (`currency_id`),
  ADD KEY `FK_project_company_id` (`company_id`),
  ADD KEY `FK_project_status_id` (`status_id`);

--
-- Indices de la tabla `projects_departments_assigned`
--
ALTER TABLE `projects_departments_assigned`
  ADD PRIMARY KEY (`department_assigned_id`),
  ADD KEY `FK_projects_department_id` (`department_id`),
  ADD KEY `FK_projects_assigned_id` (`project_id`),
  ADD KEY `FK_projects_assigned_manager_id` (`manager_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `FK_department_users_id` (`department_id`),
  ADD KEY `FK_parish_users_id` (`parish_id`),
  ADD KEY `FK_position_users_id` (`position_id`),
  ADD KEY `FK_status_users_id` (`status_id`);

--
-- Indices de la tabla `users_address_municipalities`
--
ALTER TABLE `users_address_municipalities`
  ADD PRIMARY KEY (`municipality_id`),
  ADD KEY `FK_municipality_state_id` (`state_id`);

--
-- Indices de la tabla `users_address_parishes`
--
ALTER TABLE `users_address_parishes`
  ADD PRIMARY KEY (`parish_id`),
  ADD KEY `FK_parish_municipality_id` (`municipality_id`);

--
-- Indices de la tabla `users_address_states`
--
ALTER TABLE `users_address_states`
  ADD PRIMARY KEY (`state_id`);

--
-- Indices de la tabla `users_contact`
--
ALTER TABLE `users_contact`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `FK_contact_users_id` (`user_id`);

--
-- Indices de la tabla `users_hierarchy_departments`
--
ALTER TABLE `users_hierarchy_departments`
  ADD PRIMARY KEY (`department_id`),
  ADD KEY `FK_department_status_id` (`status_id`);

--
-- Indices de la tabla `users_hierarchy_positions`
--
ALTER TABLE `users_hierarchy_positions`
  ADD PRIMARY KEY (`position_id`),
  ADD KEY `FK_position_type_id` (`position_type_id`),
  ADD KEY `FK_position_status_id` (`status_id`);

--
-- Indices de la tabla `users_identity`
--
ALTER TABLE `users_identity`
  ADD PRIMARY KEY (`identity_id`),
  ADD KEY `FK_identity_users_id` (`user_id`),
  ADD KEY `FK_identity_type_id` (`identity_type_id`);

--
-- Indices de la tabla `users_identity_type`
--
ALTER TABLE `users_identity_type`
  ADD PRIMARY KEY (`identity_type_id`);

--
-- Indices de la tabla `users_position_type`
--
ALTER TABLE `users_position_type`
  ADD PRIMARY KEY (`position_type_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT de la tabla `clients_countries`
--
ALTER TABLE `clients_countries`
  MODIFY `country_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT de la tabla `clients_sectors`
--
ALTER TABLE `clients_sectors`
  MODIFY `sector_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clients_services`
--
ALTER TABLE `clients_services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `control_companies`
--
ALTER TABLE `control_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `control_currencies`
--
ALTER TABLE `control_currencies`
  MODIFY `currency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `control_encrypts`
--
ALTER TABLE `control_encrypts`
  MODIFY `encrypt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `control_errors`
--
ALTER TABLE `control_errors`
  MODIFY `error_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT de la tabla `control_errors_type_messages`
--
ALTER TABLE `control_errors_type_messages`
  MODIFY `type_message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `control_errors_type_object`
--
ALTER TABLE `control_errors_type_object`
  MODIFY `type_object_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `control_logs`
--
ALTER TABLE `control_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT de la tabla `control_logs_action`
--
ALTER TABLE `control_logs_action`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `control_status`
--
ALTER TABLE `control_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=442;

--
-- AUTO_INCREMENT de la tabla `projects_departments_assigned`
--
ALTER TABLE `projects_departments_assigned`
  MODIFY `department_assigned_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=686;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=254;

--
-- AUTO_INCREMENT de la tabla `users_address_municipalities`
--
ALTER TABLE `users_address_municipalities`
  MODIFY `municipality_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=463;

--
-- AUTO_INCREMENT de la tabla `users_address_parishes`
--
ALTER TABLE `users_address_parishes`
  MODIFY `parish_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1139;

--
-- AUTO_INCREMENT de la tabla `users_address_states`
--
ALTER TABLE `users_address_states`
  MODIFY `state_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `users_contact`
--
ALTER TABLE `users_contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=254;

--
-- AUTO_INCREMENT de la tabla `users_hierarchy_departments`
--
ALTER TABLE `users_hierarchy_departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `users_hierarchy_positions`
--
ALTER TABLE `users_hierarchy_positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `users_identity`
--
ALTER TABLE `users_identity`
  MODIFY `identity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=250;

--
-- AUTO_INCREMENT de la tabla `users_identity_type`
--
ALTER TABLE `users_identity_type`
  MODIFY `identity_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users_position_type`
--
ALTER TABLE `users_position_type`
  MODIFY `position_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `FK_clients_countries_id` FOREIGN KEY (`country_id`) REFERENCES `clients_countries` (`country_id`),
  ADD CONSTRAINT `FK_clients_partner_users_id` FOREIGN KEY (`partner_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `FK_clients_sectors_id` FOREIGN KEY (`sector_id`) REFERENCES `clients_sectors` (`sector_id`),
  ADD CONSTRAINT `FK_clients_services_id` FOREIGN KEY (`service_id`) REFERENCES `clients_services` (`service_id`),
  ADD CONSTRAINT `FK_clients_status_id` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`);

--
-- Filtros para la tabla `clients_sectors`
--
ALTER TABLE `clients_sectors`
  ADD CONSTRAINT `FK_sector_status_id` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`);

--
-- Filtros para la tabla `clients_services`
--
ALTER TABLE `clients_services`
  ADD CONSTRAINT `FK_services_status_id` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`);

--
-- Filtros para la tabla `control_companies`
--
ALTER TABLE `control_companies`
  ADD CONSTRAINT `FK_companies_status_id` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`);

--
-- Filtros para la tabla `control_currencies`
--
ALTER TABLE `control_currencies`
  ADD CONSTRAINT `FK_currencies_status_id` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`);

--
-- Filtros para la tabla `control_encrypts`
--
ALTER TABLE `control_encrypts`
  ADD CONSTRAINT `FK_encrypt_status` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`);

--
-- Filtros para la tabla `control_errors`
--
ALTER TABLE `control_errors`
  ADD CONSTRAINT `FK_error_tipomensaje` FOREIGN KEY (`type_message_id`) REFERENCES `control_errors_type_messages` (`type_message_id`),
  ADD CONSTRAINT `FK_error_tipoobjeto` FOREIGN KEY (`type_object_id`) REFERENCES `control_errors_type_object` (`type_object_id`),
  ADD CONSTRAINT `FK_errors_type_message_id` FOREIGN KEY (`type_message_id`) REFERENCES `control_errors_type_messages` (`type_message_id`),
  ADD CONSTRAINT `FK_errors_type_object_id` FOREIGN KEY (`type_object_id`) REFERENCES `control_errors_type_object` (`type_object_id`);

--
-- Filtros para la tabla `control_logs`
--
ALTER TABLE `control_logs`
  ADD CONSTRAINT `FK_logs_action_id` FOREIGN KEY (`action_id`) REFERENCES `control_logs_action` (`action_id`);

--
-- Filtros para la tabla `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `FK_project_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `FK_project_company_id` FOREIGN KEY (`company_id`) REFERENCES `control_companies` (`company_id`),
  ADD CONSTRAINT `FK_project_currency_id` FOREIGN KEY (`currency_id`) REFERENCES `control_currencies` (`currency_id`),
  ADD CONSTRAINT `FK_project_manager_id` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `FK_project_partner_id` FOREIGN KEY (`partner_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `FK_project_quality_partner_id` FOREIGN KEY (`quality_partner_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `FK_project_status_id` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`);

--
-- Filtros para la tabla `projects_departments_assigned`
--
ALTER TABLE `projects_departments_assigned`
  ADD CONSTRAINT `FK_projects_assigned_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  ADD CONSTRAINT `FK_projects_assigned_manager_id` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `FK_projects_department_id` FOREIGN KEY (`department_id`) REFERENCES `users_hierarchy_departments` (`department_id`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `FK_department_users_id` FOREIGN KEY (`department_id`) REFERENCES `users_hierarchy_departments` (`department_id`),
  ADD CONSTRAINT `FK_parish_users_id` FOREIGN KEY (`parish_id`) REFERENCES `users_address_parishes` (`parish_id`),
  ADD CONSTRAINT `FK_position_users_id` FOREIGN KEY (`position_id`) REFERENCES `users_hierarchy_positions` (`position_id`),
  ADD CONSTRAINT `FK_status_users_id` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`);

--
-- Filtros para la tabla `users_address_municipalities`
--
ALTER TABLE `users_address_municipalities`
  ADD CONSTRAINT `FK_municipality_state_id` FOREIGN KEY (`state_id`) REFERENCES `users_address_states` (`state_id`),
  ADD CONSTRAINT `FK_municipio_estado` FOREIGN KEY (`state_id`) REFERENCES `users_address_states` (`state_id`);

--
-- Filtros para la tabla `users_address_parishes`
--
ALTER TABLE `users_address_parishes`
  ADD CONSTRAINT `FK_parish_municipality_id` FOREIGN KEY (`municipality_id`) REFERENCES `users_address_municipalities` (`municipality_id`),
  ADD CONSTRAINT `FK_parroquia_municipio` FOREIGN KEY (`municipality_id`) REFERENCES `users_address_municipalities` (`municipality_id`);

--
-- Filtros para la tabla `users_contact`
--
ALTER TABLE `users_contact`
  ADD CONSTRAINT `FK_contact_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `FK_contacto_usuario` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Filtros para la tabla `users_hierarchy_departments`
--
ALTER TABLE `users_hierarchy_departments`
  ADD CONSTRAINT `FK_department_status_id` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`),
  ADD CONSTRAINT `FK_usuarios_division_estado` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`);

--
-- Filtros para la tabla `users_hierarchy_positions`
--
ALTER TABLE `users_hierarchy_positions`
  ADD CONSTRAINT `FK_position_status_id` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`),
  ADD CONSTRAINT `FK_position_type_id` FOREIGN KEY (`position_type_id`) REFERENCES `users_position_type` (`position_type_id`),
  ADD CONSTRAINT `FK_usuarios_cargo_estado` FOREIGN KEY (`status_id`) REFERENCES `control_status` (`status_id`),
  ADD CONSTRAINT `FK_usuarios_tipo_cargo` FOREIGN KEY (`position_type_id`) REFERENCES `users_position_type` (`position_type_id`);

--
-- Filtros para la tabla `users_identity`
--
ALTER TABLE `users_identity`
  ADD CONSTRAINT `FK_identity_type_id` FOREIGN KEY (`identity_type_id`) REFERENCES `users_identity_type` (`identity_type_id`),
  ADD CONSTRAINT `FK_identity_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `FK_tipo_documento` FOREIGN KEY (`identity_type_id`) REFERENCES `users_identity_type` (`identity_type_id`),
  ADD CONSTRAINT `FK_usuario_documento` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

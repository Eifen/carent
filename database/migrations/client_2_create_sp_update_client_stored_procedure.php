<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $procedure = "
DROP PROCEDURE IF EXISTS sp_update_client;
CREATE PROCEDURE `sp_update_client`(
	IN `p_IdCliente` INT, 
    IN `p_IdSocio` INT, 
    IN `p_CodigoCliente` INT, 
    IN `p_Rif` VARCHAR(30), 
    IN `p_Nit` INT, 
    IN `p_RazonSocial` VARCHAR(60), 
    IN `p_IdPais` INT, 
    IN `p_Address` TEXT, 
    IN `p_TelefonoFiscal` VARCHAR(30), 
    IN `p_PaginaWeb` VARCHAR(150), 
    IN `p_EmailFiscal` VARCHAR(100), 
    IN `p_IpAction` VARCHAR(40), 
    IN `p_Status` INT, 
    IN `p_SectorAsociado` INT, 
    IN `p_ServicioAsociado` INT, 
    OUT `p_Response` TEXT
) BEGIN

	/*DECLARACION DE VARIABLES*/
	DECLARE v_LastUpdate DATETIME;
	DECLARE v_SqlQuery TEXT;
	DECLARE v_TemporalWeb TEXT;
	DECLARE v_TemporalNit INT;
    
	/*Variable de errores*/
    
	DECLARE v_ErrorMsj TEXT DEFAULT NULL;
	DECLARE v_ErrorControl CONDITION FOR SQLSTATE '45000';  #Variable para customizar el error SQL 45000
    
	/*MANEJADOR DE ERRORES EN PRODUCCION*/
	DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING
		BEGIN
        
			#Almacenar mensaje de error
			GET DIAGNOSTICS CONDITION 1 @err_code = RETURNED_SQLSTATE, @err_msj = MESSAGE_TEXT;
            
			#Creamos un string mensaje del error y lo almacenamos en una de las variables
			SELECT CONCAT('Se ha producido un error, de codigo: ',@err_code,' (SQL): ',@err_msj) INTO v_ErrorMsj;
			ROLLBACK; #Cancelamos cualquier query que se hubiese ejecutado

			#Registramos el error en el procedure
			CALL sp_mensaje_bd(1, 1, \"sp_update_client\", v_ErrorMsj, NULL, 1, @response);

			#Producimos en la salida un JSON de error
			SET p_Response = '{
				\"message\": \"Se ha producido un error en la consulta SQL al momento de actualizar el cliente. ' + v_ErrorMsj + '\",
				\"response\": false
			}';

			COMMIT; #Guardamos los cambios
		END;
        
	#Errores no registrados
	DECLARE EXIT HANDLER FOR v_ErrorControl
		BEGIN
        
			ROLLBACK;
            
			IF v_ErrorMsj IS NOT NULL THEN
				CALL sp_mensaje_bd(1, 1, \"sp_update_client\", v_ErrorMsj, NULL, 1, @response);
			END IF;
            
			IF p_Response = \"\" OR p_Response IS NULL THEN
				SET p_Response = '{\"message\": \"Ocurrió un error a la hora tratar de actualizar!\", \"response\": false}';
			END IF;
            
		    COMMIT;
          
		END;
		
	#Tras manejar los errores en SQL, procedemos a ejecutar las consultas
	START TRANSACTION;
    
	#Creamos la consulta
	UPDATE tbl_cliente 
    SET id_usuario_socio = p_IdSocio,
		codigo = p_CodigoCliente,
		rif = p_Rif,
		nit = p_Nit,
		razon_social = p_RazonSocial,
		id_pais = p_IdPais,
		direccion = p_Address,
		telefono_fiscal = p_TelefonoFiscal,
		pagina_web = p_PaginaWeb,
		email_fiscal = p_EmailFiscal,
		id_estatus = p_Status,
		SectorAsociado = p_SectorAsociado,
		ServicioAsociado = p_ServicioAsociado
	WHERE id = p_IdCliente;

	#Almacenamos la Consulta
	SET v_SqlQuery = CONCAT('
						UPDATE tbl_cliente p 
                        SET p.id_usuario_socio = ',p_IdSocio,',
						    p.codigo = ',p_CodigoCliente,',
							p.rif = ',p_Rif,',
							p.razon_social = ',p_RazonSocial,',
							p.id_pais = ',p_IdPais,',
							p.direccion = ',p_Address,',
							p.telefono_fiscal = ',p_TelefonoFiscal,',
							p.pagina_web = ',p_PaginaWeb,',
							p.email_fiscal = ',p_EmailFiscal,',
							p.id_estatus = ',p_Status,',
							p.SectorAsociado = ',p_SectorAsociado,',
							p.ServicioAsociado = ',p_ServicioAsociado,'
						WHERE p.id = ',p_IdCliente
					);

	#Creamos un Json de la información a almacenar
	SET @NewData = CONCAT('
		{
			\"p.id_usuario_socio\" = \"',p_IdSocio,'\",
			\"p.codigo\" = \"',p_CodigoCliente,'\",
			\"p.rif\" = \"',p_Rif,'\",
			\"p.razon_social\" = \"',p_RazonSocial,'\",
			\"p.id_pais\" = \"',p_IdPais,'\",
			\"p.direccion\" = \"',p_Address,'\",
			\"p.telefono_fiscal\" = \"',p_TelefonoFiscal,'\",
			\"p.pagina_web\" = \"',p_PaginaWeb,'\",
			\"p.email_fiscal\" = \"',p_EmailFiscal,'\",
			\"p.id_estatus\" = \"',p_Status,'\",
			\"p.SectorAsociado\" = \"',p_SectorAsociado,'\",
			\"p.ServicioAsociado\" = \"',p_ServicioAsociado,'\"
		}
	');

	#Almacenamos el Procedure en la bitacora
	CALL sp_bitacora(2, \"update_cliente\", p_IdSocio, p_IdSocio, \"tbl_cliente\", v_SqlQuery, null, @NewData, p_IpAction, @response);

	#Capturamos la respuesta del procedure
	SET @v_Request = JSON_UNQUOTE(JSON_EXTRACT(@response,'$.response'));
	SET @v_Message = JSON_UNQUOTE(JSON_EXTRACT(@response,'$.message'));
    
	IF @v_Request = \"true\" THEN
    
		SET p_Response = CONCAT('{\"message\": \"Cliente: ',p_RazonSocial,' actualizado con exito\", \"response\": true}');
		COMMIT; #Guardamos Cambio
        
	ELSE
    
		#Error en caso de fallo al upgradear
		SET p_Response = CONCAT('{\"message\": \"Error al actualizar. Mensaje: ',@v_Message,'. Id: ',p_IdSocio,'\", \"response\": false}');
		ROLLBACK;
        
	END IF;

END;";

        DB::unprepared($procedure);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sp_update_client');
    }

};

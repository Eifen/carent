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
DROP PROCEDURE IF EXISTS sp_create_client;
CREATE PROCEDURE `sp_create_client`(IN `p_IdUsuario` INT, IN `p_IdSocio` INT, IN `p_CodigoCliente` INT, IN `p_Rif` VARCHAR(30), IN `p_Nit` INT, IN `p_RazonSocial` VARCHAR(60), IN `p_IdPais` INT, IN `p_Address` TEXT, IN `p_TelefonoFiscal` VARCHAR(30), IN `p_PaginaWeb` VARCHAR(150), IN `p_EmailFiscal` VARCHAR(100), IN `p_IdSectorAsociado` INT, IN `p_IdServicioAsociado` INT, IN `p_IpAction` VARCHAR(40), OUT `p_Response` TEXT)
BEGIN

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
			CALL sp_mensaje_bd(1,1,\"sp_create_client\",v_ErrorMsj,NULL,1,@response); 
			
			#Producimos en la salida un JSON de error
			SET p_Response = '{
				\"message\": \"Se ha producido un error en la consulta SQL al momento de crear el cliente. ' + v_ErrorMsj + '\",
				\"response\": false
			}';
			
			COMMIT; #Guardamos los cambios
		END;
        
	#Errores no registrados
	DECLARE EXIT HANDLER FOR v_ErrorControl 
		BEGIN
        
			ROLLBACK;
			IF v_ErrorMsj IS NOT NULL THEN
			  CALL sp_mensaje_bd(1,1,\"sp_create_client\",v_ErrorMsj,NULL,1,@response);
			END IF;
			IF p_Response = \"\" OR p_Response IS NULL THEN
			  SET p_Response = '{\"message\": \"Ocurrió un error a la hora tratar de crear!\",\"response\": false}';
			END IF;
		  COMMIT;
          
		END;
        
	#Tras manejar los errores en SQL, procedemos a ejecutar las consultas
	START TRANSACTION;
    
	#Creamos la consulta
	INSERT INTO 
	tbl_cliente (id, 
                 id_usuario_socio, 
                 codigo, 
                 rif, 
                 nit, 
                 razon_social, 
                 id_pais, 
                 direccion, 
                 telefono_fiscal, 
                 pagina_web, 
                 email_fiscal, 
                 id_estatus,
                 SectorAsociado,
                 ServicioAsociado) 
	VALUES(null,
		   p_IdSocio,
		   p_CodigoCliente,
		   p_Rif,
		   p_Nit,
		   p_RazonSocial,
		   p_IdPais,
		   p_Address,
		   p_TelefonoFiscal,
		   p_PaginaWeb,
		   p_EmailFiscal,
		   1, p_IdSectorAsociado, p_IdServicioAsociado);

	#Almacenamos la Consulta
	SET v_SqlQuery = CONCAT('INSERT INTO tbl_cliente(id_usuario_socio, codigo, rif, nit, razon_social, id_pais, direccion, telefono_fiscal, pagina_web, email_fiscal, id_estatus,SectorAsociado,ServicioAsociado) 
	VALUES(',p_IdSocio,',',p_CodigoCliente,',',p_Rif,',',p_RazonSocial,',',p_IdPais,',',p_Address,',',p_TelefonoFiscal,',',p_PaginaWeb,',',p_EmailFiscal,',',1,',',p_IdSectorAsociado,',',p_IdServicioAsociado,')');

	#Creamos un Json de la información a almacenar
	SET @NewData = CONCAT('{\"p.id_usuario_socio\" = \"',p_IdSocio,'\",
							\"p.codigo\" = \"',p_CodigoCliente,'\",
							\"p.rif\" = \"',p_Rif,'\",
							\"p.razon_social\" = \"',p_RazonSocial,'\",
							\"p.id_pais\" = \"',p_IdPais,'\",
							\"p.direccion\" = \"',p_Address,'\",
							\"p.telefono_fiscal\" = \"',p_TelefonoFiscal,'\",
							\"p.pagina_web\" = \"',p_PaginaWeb,'\",
							\"p.email_fiscal\" = \"',p_EmailFiscal,'\",
							\"p.id_estatus\" = \"',p_IpAction,'\",
							\"p.SectorAsociado\" = \"',p_IdSectorAsociado,'\",
							\"p.ServicioAsociado\" = \"',p_IdServicioAsociado,'\"}');

	#Almacenamos el Procedure en la bitacora
	CALL sp_bitacora(1,\"nuevo_cliente\",p_IdUsuario,p_IdUsuario,\"tbl_cliente\",v_SqlQuery,null,@NewData,p_IpAction,@response);

	#Capturamos la respuesta del procedure
	SET @v_Request = JSON_UNQUOTE(JSON_EXTRACT(@response,'$.response'));
	IF @v_Request = \"true\" THEN
    
		SET p_Response = CONCAT('{\"message\": \"Columna actualizada sin problemas\",\"response\":true}');
		COMMIT; #Guardamos Cambio
        
	ELSE
    
		#Error en caso de fallo al upgradear
		SET p_Response = '{\"message\":\"Ocurrió un error al momento de registrar la Actualización\",\"response\":false}';
		ROLLBACK;
        
	END IF;
END;
        ";

        DB::unprepared($procedure);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sp_create_client');
    }
};

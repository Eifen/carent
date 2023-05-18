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
DROP PROCEDURE IF EXISTS sp_create_project;
CREATE PROCEDURE `sp_create_project`(
	IN `p_description` TEXT, 
    IN `p_IdCliente` INT, 
    IN `p_IdSocio` INT, 
    IN `p_IdSocioCalidad` INT, 
    IN `p_IdGerente` INT, 
    IN `p_FechaContratacion` VARCHAR(10), 
    IN `p_Monto` DECIMAL(25,2), 
    IN `p_IdMoneda` INT, 
    IN `p_IdEmpresa` INT, 
    IN `p_IdEstatus` INT, 
    IN `p_IpAction` VARCHAR(255), 
    IN `p_IdUsuario` INT, 
    OUT `p_Response` TEXT
) BEGIN

	/*DECLARACION DE VARIABLES*/
	DECLARE v_LastUpdate DATETIME;
	DECLARE v_SqlQuery TEXT;
    
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
			CALL sp_mensaje_bd(1, 1, \"sp_create_project\", v_ErrorMsj, NULL, 1, @response); 
			
			#Producimos en la salida un JSON de error
			SET p_Response = CONCAT('
				{
					\"message\": \"Se ha producido un error en la consulta SQL al momento de crear el proyecto ',v_ErrorMsj,'\",
					\"response\": false
				}
			');
			
			COMMIT; #Guardamos los cambios
            
		END;
        
	#Errores no registrados
	DECLARE EXIT HANDLER FOR v_ErrorControl 
		BEGIN
        
			ROLLBACK;
            
			IF v_ErrorMsj IS NOT NULL THEN
				CALL sp_mensaje_bd(1, 1, \"sp_create_project\", v_ErrorMsj, NULL, 1, @response);
			END IF;
            
			IF p_Response = \"\" OR p_Response IS NULL THEN
				SET p_Response = '{\"message\": \"Ocurrió un error a la hora tratar de crear el proyecto\", \"response\": false}';
			END IF;
            
			COMMIT;
          
		END;
    
	#Tras manejar los errores en SQL, procedemos a ejecutar las consultas
	START TRANSACTION;
    
	#Creamos la consulta
	INSERT INTO tbl_proyecto(
		descripcion, 
        id_cliente, 
        id_socio, 
        id_socio_calidad, 
        id_gerente, 
        fecha_contratacion, 
        monto, 
        id_moneda, 
        id_empresa, 
        id_estatus
	) VALUES (
		p_description,
		p_IdCliente,
		p_IdSocio,
		p_IdSocioCalidad,
		p_IdGerente,
		p_FechaContratacion,
		p_Monto,
		p_IdMoneda,
		p_IdEmpresa,
		p_IdEstatus
	);

	#Almacenamos la Consulta
	SET v_SqlQuery = CONCAT(
		'INSERT INTO `tbl_proyecto`(
			`id`, 
			`descripcion`, 
			`id_cliente`, 
			`id_socio`, 
			`id_socio_calidad`, 
			`id_gerente`, 
			`fecha_contratacion`, 
			`monto`, 
			`id_moneda`, 
			`id_empresa`, 
			`id_estatus`
		) VALUES (
			null,',
			p_description,
			p_IdCliente,
			p_IdSocio,
			p_IdSocioCalidad,
			p_IdGerente,
			p_FechaContratacion,
			p_Monto,
			p_IdMoneda,
			p_IdEmpresa,
			p_IdEstatus,
		')
    ');

	#Creamos un Json de la información a almacenar
	SET @NewData = CONCAT('
		{ 
			\"descripcion\" : \"',p_description,'\",
			\"cliente\": \"',p_IdCliente,'\",
			\"socio\": \"',p_IdSocio,'\",
			\"socioCalidad\": \"',p_IdSocioCalidad,'\",
			\"Gerente\": \"',p_IdGerente,'\",
			\"FechaContratacion\": \"',p_FechaContratacion,'\",
			\"Monto\": \"',p_Monto,'\",
			\"Moneda\": \"',p_IdMoneda,'\",
			\"Empresa\": \"',p_IdEmpresa,'\",
			\"Estatus\": \"',p_IdEstatus,'\"
		}'
	);

	#Almacenamos el Procedure en la bitacora
	CALL sp_bitacora(1, \"nuevo_proyecto\", p_IdUsuario, p_IdUsuario, \"tbl_proyecto\", v_SqlQuery, null, @NewData, p_IpAction, @response);

	#Capturamos la respuesta del procedure
	SET @v_Request = JSON_UNQUOTE(JSON_EXTRACT(@response, '$.response'));

	IF @v_Request = \"true\" THEN

		#Guardamos la variable del last insert
		SELECT @last := MAX(id) FROM tbl_proyecto;

		SET p_Response = CONCAT('
			{
				\"message\": \"Se ha creado el proyecto ',p_description,'\",
				\"response\": true,
				\"ultimoInsert\": ',@last,',
				\"cliente\": \"',p_IdCliente,'\"
			}'
		);

		COMMIT; #Guardamos Cambio

	ELSE

		#Error en caso de fallo al upgradear
		SET p_Response = '
			{
				\"message\": \"Ocurrió un error al momento de registrar el proyecto\",
				\"response\": false
			}';

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
        Schema::dropIfExists('sp_create_project');
    }

};

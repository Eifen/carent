<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ClienteModel extends Model
{

 function buscarUsuarios($opcionBusqueda, $dato, $cargo){

  switch ((int) $opcionBusqueda) {
    case 1:
      $condicion = "WHERE u.codigo LIKE '%".$dato."%'";
      break;
    case 2:
        $condicion = "WHERE u.cedula LIKE '%".$dato."%'";
        break;
    case 3:
        $condicion = "WHERE (u.nombre_1 LIKE '%".$dato."%' OR u.nombre_2 LIKE '%".$dato."%')";
        break;
    case 4:
        $condicion = "WHERE (u.apellido_1 LIKE '%".$dato."%' OR u.apellido_2 LIKE '%".$dato."%')";
        break;
    default:
      $condicion = "WHERE u.codigo LIKE %'".$dato."'%";
      break;
  }

  $usuarios = DB::select('SELECT u.id,
                                 u.codigo,
                                 u.avatar,
                                 u.cedula,
                                 u.id_cargo,
                                 CONCAT(u.nombre_1," ",u.nombre_2," ",u.apellido_1," ",u.apellido_2) AS nombre,
                                 e.descripcion AS estatus,
                                 cu.correo_principal
                         FROM tbl_usuario u,
                              tbl_estatus e,
                              tbl_contacto_usuario cu
                         '.$condicion.'
                         AND u.id_cargo = '.$cargo.'
                         AND e.tabla = "tbl_usuario"
                         AND e.valor = u.id_estatus
                         AND u.id = cu.id_usuario');
  if(count($usuarios) > 0){

    return $usuarios;

  }else{

    return array();

  }

}
function detalleUsuario($id_usuario){

      $info = DB::select('SELECT u.id,
                                 u.codigo,
                                 u.avatar,
                                 u.cedula,
                                 u.nombre_1,
                                 u.nombre_2,
                                 u.apellido_1,
                                 u.apellido_2,
                                 u.fecha_nacimiento,
                                 e.descripcion AS estatus,
                                 cu.correo_principal,
                                 cu.correo_secundario,
                                 cu.telefono_principal,
                                 cu.telefono_secundario,
                                 (SELECT d.descripcion FROM tbl_division d WHERE d.id = u.id_division) division,
                                 (SELECT ce.descripcion FROM tbl_cargo_empleado ce WHERE ce.id = u.id_cargo) cargo,
                                 (SELECT p.parroquia FROM tbl_parroquias p WHERE p.id = u.id_parroquia) parroquia,
                                 (SELECT m.municipio
                                  FROM tbl_municipios m
                                  WHERE m.id = (SELECT p.id_municipio FROM tbl_parroquias p WHERE p.id = u.id_parroquia)) municipio,
                                 (SELECT e.estado
                                  FROM tbl_estados e
                                  WHERE e.id = (SELECT m.id_estado
                                                FROM tbl_municipios m
                                                WHERE m.id = (SELECT p.id_municipio FROM tbl_parroquias p WHERE p.id = u.id_parroquia))) estado
                          FROM tbl_usuario u,
                               tbl_estatus e,
                               tbl_contacto_usuario cu
                          WHERE u.id = '.$id_usuario.'
                          AND e.tabla = "tbl_usuario"
                          AND e.valor = u.id_estatus
                          AND u.id = cu.id_usuario');

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }

    }
    function estados(){

      $estados = DB::select('SELECT e.id,
                                    e.estado
                             FROM tbl_estados e
                             ORDER BY e.estado ASC');

      return $estados;

    }// Fin estados

    function municipios($id_estado){

      $municipios = DB::select('SELECT m.id,
                                       m.municipio
                             FROM tbl_municipios m
                             WHERE m.id_estado = '.$id_estado.'
                             ORDER BY m.municipio ASC');

      return $municipios;

    }// Fin municipios

    function parroquias($id_municipio){

      $parroquias = DB::select('SELECT p.id,
                                       p.parroquia
                             FROM tbl_parroquias p
                             WHERE p.id_municipio = '.$id_municipio.'
                             ORDER BY p.parroquia ASC');

      return $parroquias;

    }// Fin parroquias

    function buscarCliente($codigo){

      $cliente = DB::select('SELECT id,
                                    codigo,                                    
                                    email_fiscal
                             FROM tbl_cliente
                             WHERE codigo = "'.$codigo.'"');

      if(count($cliente) > 0){

        return $cliente[0];

      }else{

        return array();

      }

    }// Fin buscarCliente
    function buscarEmail($email_fiscal){

      if($email_fiscal === "" ){
        $parametros = '"'.$email_fiscal.'"';
      }else{
        $parametros = '"'.$email_fiscal.'"';
      }

      $email = DB::select('SELECT
                              (SELECT COUNT(*)
                               FROM tbl_cliente cu
                               WHERE cu.email_fiscal IN('.$parametros.')) email_fiscal');

      if((int) $email[0]->email_fiscal === 0){

        return array("response" => false, "message" => "No se encontraron coincidencias");

      }else{

        return array("response" => true, "message" => "Ya se encuentran registrados los correos");

      }

    }// Fin buscarCorreos

    function crearCliente($parametros){

      DB::beginTransaction();

      $data = array("id_usuario" => $parametros["idUsuario"],
                    "codigo" => $parametros["codigoCliente"],
                    "rif" => $parametros["rif"],
                    "nit" => $parametros["nit"],
                    "razon_social" => $parametros["razon_social"],
                    "id_parroquia_fiscal" => $parametros["parroquiafi"],
                    "ciudad_fiscal" => $parametros["ciudad_fiscal"],
                    "avenida_calle_fiscal" => $parametros["avenida_calle_fiscal"],
                    "edificio_quinta_fiscal" => $parametros["edificio_quinta_fiscal"],
                    "piso_fiscal" => $parametros["piso_fiscal"],
                    "numero_fiscal" => $parametros["numero_fiscal"],
                    "telefono_fiscal" => $parametros["telefono_fiscal"],
                    "fax_fiscal" => $parametros["fax_fiscal"],
                    "email_fiscal" => $parametros["email_fiscal"],
                    "descripcion_factura" => $parametros["descripcion_factura"],
                    "id_parroquia_factura" => $parametros["parroquiafa"],
                    "ciudad_factura" => $parametros["ciudad_factura"],
                    "avenida_calle_factura" => $parametros["avenida_calle_factura"],
                    "edificio_quinta_factura" => $parametros["edificio_quinta_factura"],
                    "piso_factura" => $parametros["piso_factura"],
                    "numero_factura" => $parametros["numero_factura"],
                    "telefono_factura" => $parametros["telefono_factura"],
                    "fax_factura" => $parametros["fax_factura"],
                    "correo_factura" => $parametros["correo_factura"]);

      $contacto = DB::table('tbl_cliente')->insert($data);

      if($contacto){

        DB::commit();
        return array("response" => true, "message" => "Cliente Creado con Éxito.");

      }else{

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de crear el cliente.");

      }

    }// Fin crearCliente

    function buscarClientes($buscarPor, $dato){

      switch ((int) $buscarPor) {
        case 1:
            $condicion = "WHERE c.codigo LIKE '%".$dato."%'";
            break;
        case 2:
          $condicion = "WHERE c.razon_social LIKE '%".$dato."%'";
          break;
        case 3:
            $condicion = "WHERE c.descripcion_factura LIKE '%".$dato."%'";
            break;
        default:
          $condicion = "WHERE c.codigo LIKE %'".$dato."'%";
          break;
      }

      $clientes = DB::select('SELECT c.id,
                                     c.codigo,
                                     c.razon_social,                    
                                     c.descripcion_factura
                             FROM tbl_cliente c
                             '.$condicion.'
                              ');

      if(count($clientes) > 0){

        return $clientes;

      }else{

        return array();

      }

    }
    function detalleCliente($id_cliente){

      $info = DB::select('SELECT id,
                                 id_usuario,
                                 (SELECT codigo FROM tbl_usuario WHERE id_usuario = id) codigoU,
                                 (SELECT nombre_1 FROM tbl_usuario WHERE id_usuario = id) nombre_1,
                                 (SELECT nombre_2 FROM tbl_usuario WHERE id_usuario = id) nombre_2,
                                 (SELECT apellido_1 FROM tbl_usuario WHERE id_usuario = id) apellido_1,
                                 codigo,
                                 rif,
                                 nit,
                                 razon_social,
                                 ciudad_fiscal,
                                 avenida_calle_fiscal,
                                 edificio_quinta_fiscal,
                                 piso_fiscal,
                                 numero_fiscal,
                                 telefono_fiscal,
                                 fax_fiscal,
                                 email_fiscal,
                                 descripcion_factura,
                                 id_parroquia_factura,
                                 ciudad_factura,
                                 avenida_calle_factura,
                                 edificio_quinta_factura,
                                 piso_factura,
                                 numero_factura,
                                 telefono_factura,
                                 fax_factura,
                                 correo_factura,
                                 (SELECT p.parroquia FROM tbl_parroquias p WHERE p.id = id_parroquia_fiscal) parroquiafi,
                                 (SELECT m.municipio
                                  FROM tbl_municipios m
                                  WHERE m.id = (SELECT p.id_municipio FROM tbl_parroquias p WHERE p.id = id_parroquia_fiscal)) municipiofi,
                                 (SELECT e.estado
                                  FROM tbl_estados e
                                  WHERE e.id = (SELECT m.id_estado
                                                FROM tbl_municipios m
                                                WHERE m.id = (SELECT p.id_municipio FROM tbl_parroquias p WHERE p.id = id_parroquia_fiscal))) estadofi,
                                  (SELECT p.parroquia FROM tbl_parroquias p WHERE p.id = id_parroquia_factura) parroquiafa,
                                 (SELECT m.municipio
                                  FROM tbl_municipios m
                                  WHERE m.id = (SELECT p.id_municipio FROM tbl_parroquias p WHERE p.id = id_parroquia_factura)) municipiofa,
                                 (SELECT e.estado
                                  FROM tbl_estados e
                                  WHERE e.id = (SELECT m.id_estado
                                                FROM tbl_municipios m
                                                WHERE m.id = (SELECT p.id_municipio FROM tbl_parroquias p WHERE p.id = id_parroquia_factura))) estadofa


                          FROM tbl_cliente 
                          WHERE id = '.$id_cliente.'
                         ');
      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }
    }
    function detalleClienteModificar($id_cliente){

      $info = DB::select('SELECT id,
                                 id_usuario,
                                 (SELECT codigo FROM tbl_usuario WHERE id_usuario = id) codigoU,
                                 (SELECT nombre_1 FROM tbl_usuario WHERE id_usuario = id) nombre_1,
                                 (SELECT nombre_2 FROM tbl_usuario WHERE id_usuario = id) nombre_2,
                                 (SELECT apellido_1 FROM tbl_usuario WHERE id_usuario = id) apellido_1,
                                 codigo,
                                 rif,
                                 nit,
                                 razon_social,
                                 ciudad_fiscal,
                                 avenida_calle_fiscal,
                                 edificio_quinta_fiscal,
                                 piso_fiscal,
                                 numero_fiscal,
                                 telefono_fiscal,
                                 fax_fiscal,
                                 email_fiscal,
                                 descripcion_factura,
                                 ciudad_factura,
                                 avenida_calle_factura,
                                 edificio_quinta_factura,
                                 piso_factura,
                                 numero_factura,
                                 telefono_factura,
                                 fax_factura,
                                 correo_factura,
                                 id_parroquia_fiscal,
                                 (SELECT id
                                  FROM tbl_municipios 
                                  WHERE id = (SELECT id_municipio FROM tbl_parroquias WHERE id =id_parroquia_fiscal)) id_municipio_fiscal,
                                 (SELECT id
                                  FROM tbl_estados
                                  WHERE id = (SELECT id_estado
                                                FROM tbl_municipios 
                                                WHERE id = (SELECT id_municipio FROM tbl_parroquias WHERE id = id_parroquia_fiscal))) id_estado_fiscal,
                                  id_parroquia_factura,
                                 (SELECT id
                                  FROM tbl_municipios 
                                  WHERE id = (SELECT id_municipio FROM tbl_parroquias WHERE id =id_parroquia_factura)) id_municipio_factura,
                                 (SELECT id
                                  FROM tbl_estados
                                  WHERE id = (SELECT id_estado
                                                FROM tbl_municipios 
                                                WHERE id = (SELECT id_municipio FROM tbl_parroquias WHERE id = id_parroquia_factura))) id_estado_factura
                          FROM tbl_cliente
                          WHERE id = '.$id_cliente.'');

      if(count($info) > 0){

        return $info[0];

      }else{

        return array();

      }
    }
    function modificarCliente($parametros){

      DB::beginTransaction();

      try {

        $data = array(
                      "id_usuario" => $parametros["idUsuario"],
                      "rif" => $parametros["rif"],
                      "nit" => $parametros["nit"],
                      "razon_social" => $parametros["razon_social"],
                      "id_parroquia_fiscal" => $parametros["parroquiafi"],
                      "ciudad_fiscal" => $parametros["ciudad_fiscal"],
                      "avenida_calle_fiscal" => $parametros["avenida_calle_fiscal"],
                      "edificio_quinta_fiscal" => $parametros["edificio_quinta_fiscal"],
                      "piso_fiscal" => $parametros["piso_fiscal"],
                      "numero_fiscal" => $parametros["numero_fiscal"],
                      "telefono_fiscal" => $parametros["telefono_fiscal"],
                      "fax_fiscal" => $parametros["fax_fiscal"],
                      "email_fiscal" => $parametros["email_fiscal"],
                      "descripcion_factura" => $parametros["descripcion_factura"],
                      "id_parroquia_factura" => $parametros["parroquiafa"],
                      "ciudad_factura" => $parametros["ciudad_factura"],
                      "avenida_calle_factura" => $parametros["avenida_calle_factura"],
                      "edificio_quinta_factura" => $parametros["edificio_quinta_factura"],
                      "piso_factura" => $parametros["piso_factura"],
                      "numero_factura" => $parametros["numero_factura"],
                      "telefono_factura" => $parametros["telefono_factura"],
                      "fax_factura" => $parametros["fax_factura"],
                      "correo_factura" => $parametros["correo_factura"]);
                    

        $contacto = DB::table('tbl_cliente')->where("id",$parametros["idCliente"])->update($data);

        DB::commit();
        return array("response" => true, "message" => "Cliente actualizado con Éxito!.");

      } catch(\Illuminate\Database\QueryException $ex){

        DB::rollBack();
        return array("response" => false, "message" => "Error al tratar de actualizar la información del usuario.");

      }

    }// Fin crearUsuario
}
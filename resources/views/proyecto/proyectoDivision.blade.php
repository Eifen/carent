<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/proyectoDivision.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="proyectoDivision" class="container-fluid" v-on:keypress="keyboard">
        <menu-principal></menu-principal>
        <div class="row align-items-center justify-content-center wrapper-forms">
          <div class="col-12 col-sm-11 col-md-9 wrapper-form" v-if="form.mostrar">
            <h5>búsqueda</h5>
            <form class="row">
              <div class="form-group col-12 col-sm-4">
                <label for="descripcion">Proyecto</label>
                <input aria-describedby="descripcionHelp"
                       class="form-control form-control-sm"
                       id="descripcion"
                       maxlength="250"
                       v-bind:disabled="form.descripcion.disabled"
                       v-model.trim="form.descripcion.value"
                       type="text">
                <small id="descripcionHelp" class="form-text text-muted">Nombre del proyecto</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label for="cliente">Cliente</label>
                <input aria-describedby="clienteHelp"
                       class="form-control form-control-sm"
                       id="cliente"
                       maxlength="250"
                       v-bind:disabled="form.cliente.disabled"
                       v-model.trim="form.cliente.value"
                       type="text">
                <small id="clienteHelp" class="form-text text-muted">Razón Social del cliente</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label for="estatus">Estatus</label>
                <select aria-describedby="estatusHelp"
                        class="form-control form-control-sm"
                        id="estatus"
                        data-validar="true"
                        v-bind:disabled="form.estatus.disabled"
                        v-model="form.estatus.value"
                        v-on:click="limpiarMensajeError">
                  <option value="" selected>Seleccione...</option>
                  <option v-bind:value="estatus.id" v-for="estatus in comboEstatus">@{{ estatus.descripcion }}</option>
                </select>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-3">
                <label>&nbsp;</label>
                <button class="btn filtrar"
                        type="button"
                        v-on:click="buscar"
                        v-bind:disabled="form.btn.filtrar.disabled"
                        v-html="form.btn.filtrar.html"></button>
              </div>
              <div class="form-group col-12 col-sm-3">
                <label>&nbsp;</label>
                <button class="btn limpiar_filtro"
                        type="button"
                        v-on:click="refreshView"> Restablecer</button>
              </div>
            </form>

          </div>
          <div class="col-12 wrapper-form" v-if="form.mostrar">

            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Clientes</th>
                  <th scope="col">Proyecto</th>
                  <th scope="col">Estatus</th>
                  <th scope="col"v-if="permisoVer">Ver Empleados</th>
                  <th scope="col" v-if="permisoActualizar">Asiganar</th>
                  <th scope="col" v-if="permisoCrear">Cargar Horas</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="proyecto in proyectos">
                  <th scope="row">@{{ proyecto.cliente }}</th>
                  <td>@{{ proyecto.proyecto }}</td>
                  <td>@{{ proyecto.estatus }}</td>
                  <td v-if="permisoVer">
                    <i class="fas fa-search-plus" v-on:click="mostrarDetalleDivProyecto(proyecto.id_proyecto, $event)"></i>
                  </td>
                  <td v-if="permisoActualizar">
                       <i class="far fa-edit" v-on:click="asignarAnalistaProyecto(proyecto.id_proyecto, $event)"></i>
                  </td>
                  <td v-if= "permisoCrear">
                    <a v-bind:href="'/formCargarHoras/'+proyecto.id_proy_analista" target="_self">
                    <i class="fas fa-user-edit" v-if= "proyecto.permisoCrear"></i>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7" v-if="alert.mostrar">
            <div class="alert alert-warning text-center" v-html="alert.message"></div>
          </div>
        </div>
        <div id="modal-detalle-Dproyecto" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Detalle del proyecto</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="detalleDproyecto.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar el detalle del cliente, por favor intente nuevamente o comuníquese con el administrador del sistema!
                </div>
                <form class="row" v-for="Dproyecto in detalleDproyecto.data">
                  <div class="form-group col-12 col-sm-6">
                    <label>Cliente</label>
                    <input class="form-control" type="text" disabled v-bind:value="Dproyecto.cliente">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Proyecto</label>
                    <input class="form-control" type="text" disabled v-bind:value="Dproyecto.descripcion">
                  </div>
                </form>
                <h5>Empleados Asigandos</h5>
                <table class="table" >
              <thead>
                <tr>
                  <th scope="col">Empleado</th>
                  <th scope="col">Divison</th>
                  <th scope="col">Cargo</th>
                  <th scope="col">Horas Cargadas</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="Aproyecto in detalleAproyecto.data">
                  <th scope="row">@{{ Aproyecto.nombre }}</th>                  
                  <td>@{{ Aproyecto.division }}</td>
                  <td>@{{ Aproyecto.cargo }}</td>
                  <td>@{{ Aproyecto.suma }}</td>
                </tr>
              </tbody>
            </table>
              </div>
            </div>
          </div>
        </div>
        <div id="modal-asignar-Aproyecto" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Asignacion de Personal al Proyecto</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="detalleAsigproyecto.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar el detalle del cliente, por favor intente nuevamente o comuníquese con el administrador del sistema!
                </div>
                <form class="row" v-for="Asigproyecto in detalleAsigproyecto.data">
                  <div class="form-group col-12 col-sm-6">
                    <label>Cliente</label>
                    <input class="form-control" type="text" disabled v-bind:value="Asigproyecto.cliente">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Proyecto</label>
                    <input class="form-control" type="text" disabled v-bind:value="Asigproyecto.proyecto">
                  </div>
                </form>
                <h5>Asignar</h5>
                <table class="table" v-for="proyecto in detalleAsigproyecto.data">
              <thead>
                <tr>
                  <th scope="col">Nombre</th>
                  <th scope="col">Cargo</th>
                  <th scope="col">Estatus</th>
                  <th scope="col">Horas Cargadas</th>
                  <th scope="col">Ver Horas Cargadas</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="analista in detalleAnalista.data">
                  <th scope="row">@{{ analista.nombre }}</th>
                  <td>@{{ analista.cargo }}</td>
                  <td><input type="checkbox" v-on:change="estados(analista.id,analista.idAnaProy,proyecto.id,proyecto.id_proyecto_division, $event)" v-model="analista.estatus" ></td>
                  <td>@{{ analista.suma }}</td>
                  <td>
                    <a v-bind:href="'/formCargarHoras/'+analista.idAnaProy" target="_self">
                    <i class="fas fa-user-edit" v-if= "analista.permisoCrear"></i>
                  </td>
                </tr>
              </tbody>
            </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script src="{{ mix('/js/proyectoDivision.js') }}"></script>
    </body>
</html>

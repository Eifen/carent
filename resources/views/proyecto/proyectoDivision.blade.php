<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/proyectoDivision.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="proyectoDivision" class="container-fluid" v-on:keypress="keyboard">
        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>
        <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
          <div class="col-12 col-sm-11 col-md-9 wrapper-form" v-if="form.mostrar">
            <h5>Búsqueda</h5>
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
                <small id="descripcionHelp" class="form-text text-muted">Nombre del Proyecto</small>
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
                <small id="clienteHelp" class="form-text text-muted">Razón Social del Cliente</small>
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
                        v-on:click="refreshView">Restablecer</button>
              </div>
            </form>

          </div>
          <div class="col-12 wrapper-form" v-if="form.mostrar">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Cliente</th>
                  <th scope="col">Proyecto</th>
                  <th scope="col">División</th>
                  <th scope="col"v-if="permisoVer">Ver Empleados</th>
                  <th scope="col" v-if="permisoActualizar">Asignar</th>
                  <th scope="col" v-if="permisoCrear">Mis Horas Asignadas</th>
                  <th scope="col" v-if="permisoCrear">Cargar Horas</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="proyecto in proyectos">
                  <th scope="row">@{{ proyecto.cliente }}</th>
                  <td>@{{ proyecto.proyecto }}</td>
                  <td>@{{ proyecto.division }}</td>
                  <td v-if="permisoVer">
                    <i class="fas fa-search-plus" v-on:click="mostrarDetalleDivProyecto(proyecto.id_proyecto, $event)" v-if= "proyecto.permisoVer"></i>
                  </td>
                  <td v-if="permisoActualizar">
                       <i class="far fa-edit" v-on:click="asignarAnalistaProyecto(proyecto.id_proyecto, proyecto.id_proyecto_division, $event)"v-if= "proyecto.permisoActualizar"></i>
                  </td>
                  <td v-if= "permisoCrear">@{{ proyecto.horas_asignadas }}</td>
                  <td v-if= "permisoCrear">
                    <a v-bind:href="'/formCargarHoras/'+proyecto.id_proy_analista" target="_self">
                    <i class="fas fa-user-edit" v-if= "proyecto.permisoCrear"></i>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="row wrapper-alert">
            <div class="col-12">
              <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message"></div>
            </div>
          </div>
        </div>
        <div id="modal-detalle-Dproyecto" class="modal fade" tabindex="-1" role="dialog" v-cloak>
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Detallé del Proyecto</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true" class="CerrarModal_Division">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="detalleDproyecto.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar los detalles, por favor intente nuevamente o comuníquese con el administrador del sistema!
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
                  <div class="form-group col-12 col-sm-6">
                    <label>Horas Contratadas</label>
                    <input class="form-control" type="text" disabled v-bind:value="horas_contratadas">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Total de Horas Cargadas</label>
                    <input class="form-control" type="text" disabled v-bind:value="horas_cargadas">
                  </div>
                </form>
                <h5>Empleados Asigandos</h5>
                <table class="table" >
              <thead>
                <tr>
                  <th scope="col">Empleado</th>
                  <th scope="col">División</th>
                  <th scope="col">Cargo</th>
                  <th scope="col">Horas Cargadas</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="Aproyecto in detalleAproyecto.data">
                  <th scope="row">@{{ Aproyecto.nombre }}</th>
                  <td>@{{ Aproyecto.division }}</td>
                  <td>@{{ Aproyecto.cargo }}</td>
                  <td>@{{ Aproyecto.horas_cargadas }}</td>
                </tr>
              </tbody>
            </table>
              </div>
            </div>
          </div>
        </div>
        <div id="modal-asignar-Aproyecto" class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" v-cloak>
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Asignación de Personal al Proyecto</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true" class="CerrarModal_Division">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="detalleAsigproyecto.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar el detalle, por favor intente nuevamente o comuníquese con el administrador del sistema!
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
                  <div class="form-group col-12 col-sm-6">
                    <label>Horas Asignadas a la División </label>
                    <input class="form-control" type="text" disabled v-bind:value="Asigproyecto.horas_contratadas">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label for="horas">Total de Horas Asignadas</label>
                    <input :disabled="form.camposAtributos.horas.disabled"
                           :state="form.camposAtributos.horas.state"
                           aria-describedby="horasHelp"
                           class="form-control"
                           id="horas"
                           v-bind:disabled="form.horas.disabled"
                           v-model="form.horas.value"
                           type="text">
                    <div class="mensaje"></div>
                  </div>
                  <div class="form-group col-12 col-sm-12">
                    <label for="empleados">Empleados</label>
                    <multiselect @input="asignarEmpleado"
                                 @Open="limpiarMensajeErrorMultiselect"
                                 :clear-on-select="false"
                                 :close-on-select="false"
                                 :disabled="form.camposAtributos.empleados.disabled"
                                 :multiple="true"
                                 :options="comboEmpleados"
                                 :show-labels="false"
                                 clase="form-control form-control-sm"
                                 id="empleados"
                                 label="nombre"
                                 placeholder="Seleccione..."
                                 ref="empleados"
                                 track-by="nombre"
                                 v-model="$v.form.campos.empleados.$model"></multiselect>
                    <div class="mensaje"></div>
                  </div>
                </form>
                <div class="row wrapper-alert">
                  <div class="col-12">
                    <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message">
                    </div>
                  </div>
                </div>
                <h5 v-if="permisoAsignar">Asignar Horas</h5>
                <form class="row">
                  <b-form-group :key="index" class="col-12" v-for="(empleado, index) in form.camposAtributos.empleados.empleados">
                    <b-row>
                    <b-form-group
                      class="col-12 col-sm-4"
                      label="Empleado">
                      <b-form-input
                        :value="empleado.nombre+`:`"
                        plaintext
                        readonly
                        size="sm">
                      </b-form-input>
                    </b-form-group>
                    <b-form-group
                      class="col-12 col-sm-2"
                      label="Cargo">
                      <b-form-input
                        :value="empleado.cargo+``"
                        plaintext
                        readonly
                        size="sm">
                      </b-form-input>
                    </b-form-group>
                    <b-form-group
                    :invalid-feedback="form.camposAtributos.empleados.empleados[index].horas.invalidFeedback"
                    class="col-12 col-sm-2"
                    label="Horas">
                    <b-form-input
                      @input="horaEmpleado(index)"
                      :disabled="form.camposAtributos.empleados.disabled"
                      :formatter="cantidadHora"
                      :number="true"
                      :ref="'hora-'+index"
                      :state="form.camposAtributos.empleados.empleados[index].horas.state"
                      class="form-control hora-asignada"
                      placeholder="0"
                      size="sm"
                      v-model="form.camposAtributos.empleados.empleados[index].horas.value"></b-form-input>
                  </b-form-group>
                  <b-form-group
                    class="col-12 col-sm-3"
                    label="Horas Cargadas">
                    <b-form-input
                      :value="empleado.horas_cargadas+``"
                      plaintext
                      readonly
                      size="sm">
                    </b-form-input>
                  </b-form-group>
                  <b-form-group
                    class="col-12 col-sm-1"
                    label="Ver"
                    v-if= "empleado.permisoCarga">
                    <a v-bind:href="'/formCargarHoras/'+empleado.idAnaProy" target="_self"><i class="fas fa-user-edit" v-if= "empleado.permisoCarga"></i>
                    </a>
                  </b-form-group>
                </form>
              </div>
              <div class="row justify-content-center wrapper-subtmit" v-for="Asigproyecto in detalleAsigproyecto.data">
                <div class="col-12">
                  <div v-bind:class="alertFormA.class" role="alert" v-if="alertFormA.show" v-html="alertFormA.message">
                  </div>
                </div>
                <div class="modal-footer" v-if="permisoAsignar">
                  <button class="btn asignar"
                          type="button"
                          v-on:click= "asignarAnalista(Asigproyecto.horas_contratadas, $event)"
                          v-bind:disabled="form.btn.asignar.disabled"
                          v-html="form.btn.asignar.html"></button>
                </div>
                <div class="modal-footer" v-if="permisoCerrar">
                  <button class="btn cerrar"
                          type="button"
                          v-on:click= "cerrarModal"
                          v-html="form.btn.cerrar.html"></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script src="{{ mix('/js/proyectoDivision.js') }}"></script>
    </body>
</html>

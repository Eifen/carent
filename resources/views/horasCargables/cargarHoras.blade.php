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
        <link href="{{ mix('/css/cargarHoras.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>
      <div id="cargarHoras" class="container-fluid" v-on:keypress="keyboard">
        <menu-principal></menu-principal>
        <div class="row align-items-center justify-content-center wrapper-forms">
          <div class="col-12 col-sm-11 col-md-9 wrapper-form" v-if="form.mostrar">
            <h3>Datos del Proyecto a Cargar Horas</h3> 
            <form class="row" v-for="ProyAnalista in infoProyAnalista">
              <div class="form-group col-12 col-sm-6">
                <label>Cliente</label>
                  <input  style="text-align:center" class="form-control" type="text" disabled v-bind:value="ProyAnalista.cliente">
              </div>
              <div class="form-group col-12 col-sm-6">
                <label>Proyecto</label>
                  <input  style="text-align:center" class="form-control" type="text" disabled v-bind:value="ProyAnalista.descripcion">
              </div>
              <div class="form-group col-12 col-sm-6">
                <label>Nombre</label>
                  <input  style="text-align:center" class="form-control" type="text" disabled v-bind:value="ProyAnalista.nombre">
              </div>
              <div class="form-group col-4 col-sm-2">
                <label>Horas Asignadas</label>
                  <input  style="text-align:center" class="form-control" type="text" disabled v-bind:value="ProyAnalista.horas_asignadas">
              </div>
              <div class="form-group col-4 col-sm-2">
                <label>Horas Cargadas</label>
                  <input  style="text-align:center" class="form-control" type="text" disabled v-bind:value="horas_cargadas">
              </div>
            </form>
            <h3 v-if="permisoCrear">Cargar Horas</h3>
            <form class="row" v-if="permisoCrear">
              <div class="form-group col-2 col-sm-2">
                <label for="fecha">Fecha</label>
                  <datepicker input-class="form-control" 
                              format= "dd/MM/yyyy"    
                              :language="es"
                              id= "fecha"
                              v-bind:disabled="form.fecha.disabled"
                              v-model="form.fecha.value"
                              v-on:keyup="valuesForm">                                 
                  </datepicker>
              </div>
              <div class="form-group col-14 col-sm-8">
                <label for="descripcion">Descripcion de lo Realizado</label>
                  <input class="form-control"
                         id="descripcion"
                         v-bind:disabled="form.descripcion.disabled"
                         v-model="form.descripcion.value"
                         v-on:keyup="valuesForm"
                         type="text">
              </div>
              <div class="form-group col-2 col-sm-2">
                <label for="horas_trabajadas">Horas Trabajadas</label>
                  <input style="text-align:center" 
                         class="form-control"
                         id="horas_trabajadas"
                         v-mask="'##'"
                         v-bind:disabled="form.horas_trabajadas.disabled" 
                         v-model="form.horas_trabajadas.value"
                         v-on:keyup="valuesForm"
                         type="text" >
              </div>
              <div class="form-group col-12 col-sm-3" v-for="ProyAnalista in infoProyAnalista">
                <label>&nbsp;</label>
                <button class="btn filtrar"
                        type="button"
                        v-on:click="crear(horas_cargadas,ProyAnalista.horas_asignadas, $event)"
                        v-bind:disabled="form.btn.Crear.disabled"
                        v-html="form.btn.Crear.html"></button>
              </div>
            </form>
            <div class="row wrapper-alert">
              <div class="col-12">
                <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message"></div>
              </div>
            </div>
            <label>&nbsp;</label>
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Fecha</th>
                  <th scope="col">Descripcion</th>
                  <th scope="col">Horas</th>
                  <th scope="col" v-if="permisoActualizar">Modificar</th>
                  <th scope="col" v-if="permisoEliminar">Eliminar</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cargadas in infoHorasCargadas">
                  <th scope="row">@{{ cargadas.fecha }}</th>
                  <td>@{{ cargadas.descripcion }}</td>
                  <td>@{{ cargadas.horas_trabajadas }}</td>
                  <td v-if="permisoActualizar">
                       <i class="far fa-edit" v-on:click="detalleModHorasCargadas(cargadas.id, $event)"></i>
                  </td>
                  <td v-if="permisoEliminar">
                       <i class="fas fa-trash" v-on:click="detalleHorasEliminar(cargadas.id, $event)"></i>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7" v-if="alert.mostrar">
            <div class="alert alert-warning text-center" v-html="alert.message"></div>
          </div>
        </div>
        <div id="modal-detalle-Hcargadas" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Detalle de las horas cargadas</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="modHorasCargadas.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar el detalle de las horas cargadas, por favor intente nuevamente o comuníquese con el administrador del sistema!
                </div>
                <form class="row" v-for="ProyAnalista in infoProyAnalista">
                  <div class="form-group col-12 col-sm-6">
                    <label>Cliente</label>
                    <input class="form-control" type="text" disabled v-bind:value="ProyAnalista.cliente">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Proyecto</label>
                    <input class="form-control" type="text" disabled v-bind:value="ProyAnalista.descripcion">
                  </div>
                </form>
                <form class="row">
                  <div class="form-group col-12 col-sm-6">
                    <label for="fechaM">Fecha</label>
                    <datepicker input-class="form-control" 
                                format= "dd/MM/yyyy"    
                                :language="es"
                                id= "fechaM"
                                v-bind:disabled="form.fechaM.disabled"
                                v-model="form.fechaM.value"
                                v-on:keyup="valuesForm">                                 
                    </datepicker>
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label for="horas_trabajadasM">Horas Trabajadas</label>
                      <input style="text-align:center" 
                             class="form-control"
                             id="horas_trabajadasM"
                             v-bind:disabled="form.horas_trabajadasM.disabled" 
                             v-model="form.horas_trabajadasM.value"
                             v-on:keyup="valuesForm"
                             type="text">
                  </div>
                  <div class="form-group col-18 col-sm-10">
                      <label for="descripcionM">Descripcion de lo Realizado</label>
                        <input class="form-control"
                               id="descripcionM"
                               v-bind:disabled="form.descripcionM.disabled"
                               v-model="form.descripcionM.value"
                               v-on:keyup="valuesForm"
                               type="text">
                  </div>
                </form>
                <div class="row justify-content-center wrapper-subtmit">
                  <div class="form-group col-12 col-sm-3" v-for="ProyAnalista in infoProyAnalista">
                    <label>&nbsp;</label>
                      <button class="btn btn-primary"
                              type="button"
                              v-on:click="modificar(horas_cargadas,ProyAnalista.horas_asignadas, $event)"
                              v-bind:disabled="form.btn.Modificar.disabled"
                              v-html="form.btn.Modificar.html"
                              data-dismiss="modal"></button>
                  </div>
                </div>
                <div class="row wrapper-alert">
              <div class="col-12">
                <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message"></div>
              </div>
            </div>
              </div>
            </div>
          </div>
        </div>
        <div id="modal-eliminar-Hcargadas" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Detalle de las horas cargadas a eliminar</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="eliHorasCargadas.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar el detalle de las horas cargadas a eliminar, por favor intente nuevamente o comuníquese con el administrador del sistema!
                </div>
                <form class="row">
                  <div class="form-group col-12 col-sm-6">
                    <label>Fecha</label>
                    <input class="form-control" type="text" disabled v-bind:value="infoEliHorasCargadas.fecha">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Horas Trabajadas</label>
                    <input class="form-control" type="text" disabled v-bind:value="infoEliHorasCargadas.horas_trabajadas">
                  </div>
                  <div class="form-group col-18 col-sm-10">
                    <label>Descripcion de lo Realizado</label>
                    <input class="form-control" type="text" disabled v-bind:value="infoEliHorasCargadas.descripcion">
                  </div>
                </form>
                <div class="row justify-content-center wrapper-subtmit">
                  <div class="form-group col-12 col-sm-3">
                    <label>&nbsp;</label>
                      <button class="btn btn-danger"
                              type="button"
                              v-on:click="eliminar"
                              v-bind:disabled="form.btn.Eliminar.disabled"
                              v-html="form.btn.Eliminar.html"
                              data-dismiss="modal"></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script src="{{ mix('/js/cargarHoras.js') }}"></script>
    </body>
</html>

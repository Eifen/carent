<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        @vite('resources/css/fontawesome-free-5.12.0.css')
        @vite('resources/css/buscarRegistro.css')

    </head>
    <body>

      <div id="buscarRegistro" class="container-fluid" v-on:submit.prevent="buscar">
        <menu-principal></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms">
          <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7">
            <form class="row">
              <div class="form-group col-12 col-md-4">
                <select class="form-control"
                        v-bind:disabled="formSearch.select.disabled"
                        v-model="formSearch.select.value"
                        v-on:change="tipoFiltro">
                  <option value="" selected disabled>Consultar...</option>
                  <option value="1">Division</option>
                  <option value="2">Cargo </option>
                </select>
              </div>
              <div class="form-group col-12 col-md-2">
                <button class="btn btn-primary"
                        type="button"
                        v-bind:disabled="formSearch.submit.disabled"
                        v-html="formSearch.submit.html"
                        v-on:click="Crear">
                </button>
              </div>
            </form>
          </div>
          <div class="col-12" v-show="registros.mostrar">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Descripcion</th>
                  <th scope="col">Estatus</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="registro in registros.registros">
                  <th scope="row">@{{ registro.descripcion}}</th>
                  <td>@{{ registro.id_estatus }}</td>
                  <td>
                    <i class="fas fa-search-plus" v-on:click="mostrardetalleRegistro(registro.id, $event)"></i>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7" v-if="alert.mostrar">
            <div class="alert alert-warning text-center" v-html="alert.message"></div>
          </div>
        </div>
        <div id="modal-detalle-registro" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Detalle del registro</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="detalleRegistro.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar el detalle del registro, por favor intente nuevamente o comuníquese con el administrador del sistema!
                </div>
                <form class="row" v-if="!detalleRegistro.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Descripcion</label>
                    <input class="form-control" type="text" v-bind:value="detalleRegistro.data.descripcion">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Estatus</label>
                    <input class="form-control" type="text" v-bind:value="detalleRegistro.data.id_estatus">
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button class="btn btn-primary"
                        data-dismiss="modal"
                        type="button">Ok</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      @vite('resources/js/buscarRegistro.js')
    </body>
</html>

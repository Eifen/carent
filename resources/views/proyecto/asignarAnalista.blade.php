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
        <link href="{{ mix('/css/asignarAnalista.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="asignarAnalista" class="container-fluid" v-on:keypress="keyboard">
        <menu-principal></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms">
          <div class="col-12 col-sm-11 col-md-9 wrapper-form" v-if="form.mostrar">
            <h5>Asignacion de Personal al Proyecto</h5>
              <form class="row" v-for="proyecto in proyectos">
                  <div class="form-group col-12 col-sm-6">
                    <label>Cliente</label>
                    <input class="form-control" type="text" disabled v-bind:value="proyecto.cliente">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Proyecto</label>
                    <input class="form-control" type="text" disabled v-bind:value="proyecto.proyecto">
                  </div>           
              <div class="row justify-content-center wrapper-subtmit">
                <div class="form-group col-12 col-sm-12">
                  <a class="btn filtrar"
                    href="{{ url()->previous() }}"
                    type="button">Regresar</a>
                </div>
              </div>
            </form>

          </div>
          <div class="col-12 wrapper-form" v-if="form.mostrar">
            <table class="table" v-for="proyecto in proyectos">
              <thead>
                <tr>
                  <th scope="col">Nombre</th>
                  <th scope="col">Cargo</th>
                  <th scope="col">Estatus</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="analista in analistas">
                  <th scope="row">@{{ analista.nombre }}</th>
                  <td>@{{ analista.cargo }}</td>
                  <td><input type="checkbox" v-on:change="estados(analista.id,analista.idAnaProy,proyecto.id, $event)" v-model="analista.estatus" ></td>
                </tr>
              </tbody>
            </table>

          </div>

        </div>

      </div>

      <script src="{{ mix('/js/asignarAnalista.js') }}"></script>

    </body>
</html>

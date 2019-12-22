<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>Sofguar</title>
        <link rel="shortcut icon" type="image/png" href="images/favicon.png"/>
        <link href="{{ mix('/css/bootstrap.4.3.1.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/frontend/login.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="login" class="container-fluid">

        <div id="modal-recuperar-clave" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Recupera tu clave</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                FORM
              </div>
              <div class="modal-footer">
                <button type="button" class="btn" v-on:click="showBudgetModal">Recuperar</button>
              </div>
            </div>
          </div>
        </div>

      </div>

      <script src="{{ mix('/js/fontawesome-free-5.8.1.js') }}"></script>
      <script src="{{ mix('/js/frontend/login.js') }}"></script>

    </body>
</html>

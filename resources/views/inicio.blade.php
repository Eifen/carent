<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>.: CARENT :. | Inicio</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/inicio.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="inicio" class="container-fluid">
        <menu-principal></menu-principal>

        <div class="row align-items-center justify-content-center">
          <div class="col-12 col-sm-9 col-md-6 col-lg-4">
            Bienvenido al INICIO {{ session('usuario_id') }}
          </div>
        </div>
      </div>

      <script src="{{ mix('/js/fontawesome-free-5.12.0.js') }}"></script>
      <script src="{{ mix('/js/inicio.js') }}"></script>

    </body>
</html>

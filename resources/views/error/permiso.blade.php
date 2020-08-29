<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>.: CARENT :. | Error</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/error/permiso.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="error" class="container-fluid">
        <menu-principal></menu-principal>

        <div class="row align-items-center justify-content-center">
          <div class="col-12 col-sm-9 col-md-6 col-lg-4">
            <h1 class="text-center">
              <i class="fas fa-exclamation-triangle icon-error"></i>
              <br>
              @if(@isset($mensaje_error))
                {{ $mensaje_error }}
              @else
                No tienes permiso para acceder a está página
              @endif
            </h1>
          </div>
        </div>
      </div>

      <script src="{{ mix('/js/error/permiso.js') }}"></script>

    </body>
</html>

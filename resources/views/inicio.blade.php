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
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/inicio.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="inicio" class="container-fluid">

        <loading :loading="loading" v-show="loading"></loading>

        <menu-principal v-cloak></menu-principal>

        <div class="row align-items-center justify-content-center" v-cloak>
          <div class="col-12 col-sm-9 col-md-6 col-lg-4"></div>
        </div>
      </div>

      <script src="{{ mix('/js/inicio.js') }}"></script>

    </body>
</html>

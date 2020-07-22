<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/ingresosGastos.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="app" class="container-fluid">

        <loading :loading="loading" v-show="loading"></loading>

        <menu-principal></menu-principal>

      </div>

      <script src="{{ mix('/js/ingresosGastos.js') }}"></script>

    </body>
</html>

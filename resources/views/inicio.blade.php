<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>.: CARENT :. | Inicio</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/inicio.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <b-container fluid id="app" v-cloak>

        <loading :loading="loading" v-show="loading"></loading>

        <menu-principal v-cloak></menu-principal>

        <b-row align-h="center" align-v="center" v-cloak>
          <b-col cols="12" sm="9" md="6" lg="4"></b-col>
        </b-row>

      </b-container>

      <script src="{{ mix('/js/inicio.js') }}"></script>

    </body>

</html>

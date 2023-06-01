<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        @vite('resources/css/usuario.css')

    </head>
    <body>

      <div id="app" class="container-fluid">
          <loading :loading="loading" v-show="loading"></loading>
          <menu-principal v-cloak></menu-principal>
          <listing-users></listing-users/>
      </div>

      @vite('resources/js/usuario.js')

    </body>
</html>

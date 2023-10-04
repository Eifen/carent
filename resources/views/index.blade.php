<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="{{ env('META_ROBOT') }}">
    <title>.: CARENT :.</title>
    <link rel="shortcut icon" type="image/png" href="/images/favicon.png" />
    @vite(['resources/less/index.less', 'resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
    @if (!$Session)
        @include('UI.login')
    @else
        @if ($Maintenance == 2)
            @include('UI.menuIndex')
        @else
            @if (Session::get('userId') == 1)
                @include('UI.menuIndex')
            @else
                @include('UI.maintenance')
            @endif
        @endif
    @endif
</body>

</html>

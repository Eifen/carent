@extends('layouts.menuLayout')

{{-- DropDown Menu --}}
@section('usuarios')
    @include('layouts.navBar.usuarios')
@endsection

@section('clientes')
    @include('layouts.navBar.clientes')
@endsection

@section('proyectos')
    @include('layouts.navBar.proyectos')
@endsection

@section('facturacion')
    @include('layouts.navBar.facturacion')
@endsection

@section('reportes')
    @include('layouts.navBar.reportes')
@endsection

@section('miCuenta')
    @include('layouts.navBar.miCuenta')
@endsection

{{-- Body Dashboard --}}
@section('dashboard')
    <section class="dashboard">
        {{-- Redireccion de rutas para Usuarios --}}
        @if (Request::url() === URL::route('users'))
          @include('UI.Users.userIndex')
        @endif

        @if (Request::url() === URL::route('createUser'))
            @include('UI.Users.userCreate')
        @endif

        {{-- Redireccion de rutas para Clientes --}}
    </section>
@endsection

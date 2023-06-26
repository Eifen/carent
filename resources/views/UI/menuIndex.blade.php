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

        @if (Request::url() === URL::route('updateUser'))
            @if (Session::has('dataUpdate'))
                @include('UI.Users.userUpdate')
            @else
                {{-- Redirecciona si dataUpdate no existe --}}
                @php
                    header('Location: /usuarios');
                    exit();
                @endphp
            @endif
        @endif
        {{-- Redireccion de rutas para Clientes --}}
        @if (Request::url() === URL::route('clients'))
            @include('UI.Clients.clientIndex')
        @endif

        @if (Request::url() === URL::route('createClient'))
            @include('UI.Clients.clientCreate')
        @endif

        @if (Request::url() === URL::route('updateClient'))
            @if (Session::has('clientUpdate'))
                @include('UI.Clients.clientUpdate')
            @else
                @php
                    header('Location: /clientes');
                    exit();
                @endphp
            @endif
        @endif
        {{-- Redirección de rutas para Proyectos --}}
        @if (Request::url() === URL::route('projects'))
            @include('UI.Projects.ProjectIndex')
        @endif

        @if (Request::url() === URL::route('createProject'))
            @include('UI.Projects.ProjectCreate')
        @endif

        @if (Request::url() === URL::route('updateProject'))
            @if (Session::has('projectUpdate'))
                @include('UI.Projects.ProjectUpdate')
            @else
                @php
                    header('Location: /projects');
                    exit();
                @endphp
            @endif
        @endif

        {{-- Redirección de rutas para Asignación de proyectos --}}
        @if (Request::url() === URL::route('assign'))
            @include('UI.Projects.ProjectAssign.assignIndex')
        @endif

        {{-- Redirección de rutas para Carga de Horas --}}
        @if (Request::url() === URL::route('register'))
            @include('UI.Projects.ProjectRegister.registerIndex')
        @endif
    </section>
@endsection

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

@section('evaluaciones')
    @include('layouts.navBar.evaluaciones')
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
        {{-- Redireccion de rutas para la pagina principal --}}
        @if (Request::url() === URL::route('home'))
            @include('UI.homePage')
        @endif
        {{-- Redireccion de rutas para control de contrasenas --}}
        @if (Request::url() === URL::route('changePassword'))
            @include('UI.changePassword')
        @endif
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

        {{-- Redireccion para validacion de horas administrativas cargadas --}}
        @if (Request::url() === URL::route('validate'))
            @include('UI.Projects.ProjectValidate.ValidateIndex')
        @endif

        {{-- Redirección de rutas para Carga de Horas --}}
        @if (Request::url() === URL::route('register'))
            @include('UI.Projects.ProjectRegister.registerIndex')
        @endif

        {{-- Redireccion de rutas para cierre de proyectos --}}
        @if (Request::url() === URL::route('closeProjects'))
            @if (Session::has('closeProject'))
                @include('UI.Projects.ProjectClose.CloseIndex')
            @else
                @php
                    header('Location: /projects');
                    exit();
                @endphp
            @endif
        @endif

        {{-- Redireccion de ruta para facturacion --}}
        @if (Request::url() === URL::route('billing'))
            @include('UI.Billings.BillingIndex')
        @endif

        {{-- Redireccion de ruta para control de factura --}}
        @if (Request::url() === URL::route('controlBilling'))
            @if (Session::has('billingProject'))
                @include('UI.Billings.BillingControl')
            @else
                @php
                    header('Location: /billings');
                    exit();
                @endphp
            @endif
        @endif
        {{-- Redireccion de ruta para evaluaciones --}}
        @if (Request::url() === URL::route('evaluations'))
            @include('UI.Evaluations.evaluationsIndex')
        @endif

        {{-- Redirección de rutas para Periodos --}}
        @if (Request::url() === URL::route('periods'))
            @include('UI.Evaluations.EvaluationsPeriod.evaluationsIndexPeriod')
        @endif

        @if (Request::url() === URL::route('createPeriod'))
            @include('UI.Evaluations.EvaluationsPeriod.evaluationsCreatePeriod')
        @endif

        @if (Request::url() === URL::route('updatePeriod'))
            @include('UI.Evaluations.EvaluationsPeriod.evaluationsUpdatePeriod')
        @endif

        {{-- Redirección de rutas para Proyectos para evaluar --}}
        @if (Request::url() === URL::route('evaluationsProject'))
            @include('UI.Evaluations.EvaluationsProject.evaluationsIndexProject')
        @endif

        @if (Request::url() === URL::route('evaluationsForm'))
            @include('UI.Evaluations.EvaluationsProject.evaluationsCreateAutoevaluation')
        @endif
        @if (Request::url() === URL::route('evaluationsInfo'))
            @include('UI.Evaluations.EvaluationsProject.evaluationsInfoAutoevaluation')
        @endif

        {{-- Redirección de rutas para Reporte de evaluaciones --}}
        @if (Request::url() === URL::route('evaluationsReport'))
            @include('UI.Evaluations.EvaluationsReport.evaluationsIndexReport')
        @endif

        {{-- Redirección de rutas para Listado de evaluaciones --}}
        @if (Request::url() === URL::route('evaluationsList'))
            @include('UI.Evaluations.EvaluationsList.evaluationsIndexList')
        @endif
        @if (Request::url() === URL::route('evaluationsEvaluator'))
            @include('UI.Evaluations.EvaluationsList.evaluationsEvaluator')
        @endif

        {{-- Redirección de rutas para Promociones y ascensos --}}
        @if (Request::url() === URL::route('evaluationsPromotion'))
            @include('UI.Evaluations.EvaluationsPromotion.evaluationsIndexPromotion')
        @endif

        {{-- Redirección de rutas para formulario
            @if (Request::url() === URL::route('evaluationsForm'))
                @include('UI.Evaluations.EvaluationsForm.evaluationsIndexForm')
            @endif --}}
        {{-- Redirección de rutas para el segundo formulario --}}
        @if (Request::url() === URL::route('evaluationsFormTwo'))
            @include('UI.Evaluations.EvaluationsForm.evaluationsIndexFormTwo')
        @endif
        {{-- Redireccion de ruta para reportes --}}
        @if (Request::url() === URL::route('reports'))
            @include('UI.Reports.reportsIndex')
        @endif
        {{-- Redireccion de ruta para admin --}}
        @if (Request::url() === URL::route('admin'))
            @include('UI.Admin.adminIndex')
        @endif
    </section>
@endsection

<div>
    <a class="header-nav-links-item-link">Evaluaciones <font-awesome
            string-icon="fa-solid fa-caret-down"></font-awesome></a>
    <div class="header-nav-links-item-dropdown" :style="controlDropdown.evaluations.style"
        @mouseover="openDropDown('evaluations')" @mouseout="closeDropDown('evaluations')">
        @if (
            (Session::has('userPermissions') && Session::get('userPermissions')['projectP'] == 1) ||
                (Session::has('userPermissions') && Session::get('userPermissions')['closeP'] == 1))
            <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('periods') }}">Habilitar
                    Periodo</a></div>
        @endif
        @if (
            (Session::has('userPermissions') && Session::get('userPermissions')['projectP'] == 1) ||
                (Session::has('userPermissions') && Session::get('userPermissions')['closeP'] == 1))
            <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('evaluations') }}">Detalles de
                    Evaluación</a></div>
        @endif

        @if (
            (Session::has('userPermissions') && Session::get('userPermissions')['projectP'] == 1) ||
                (Session::has('userPermissions') && Session::get('userPermissions')['closeP'] == 1) ||
                Session::get('userId') === 14 ||
                Session::get('userId') === 6)
            <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('evaluationsProject') }}">Proyecto
                    para Evaluar</a></div>
        @endif

        @if (
            (Session::has('userPermissions') && Session::get('userPermissions')['projectP'] == 1) ||
                (Session::has('userPermissions') && Session::get('userPermissions')['closeP'] == 1) ||
                Session::get('userId') === 6)
            <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('evaluationsList') }}">Listado
                    del Personal</a></div>
        @endif

        @if (
            (Session::has('userPermissions') && Session::get('userPermissions')['projectP'] == 1) ||
                (Session::has('userPermissions') && Session::get('userPermissions')['closeP'] == 1))
            <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('evaluationsPromotion') }}">
                    Promociones y Ascensos </a></div>
        @endif

        @if (
            (Session::has('userPermissions') && Session::get('userPermissions')['projectP'] == 1) ||
                (Session::has('userPermissions') && Session::get('userPermissions')['closeP'] == 1))
            <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('evaluationsReport') }}">Reporte
                    de Evaluaciones</a></div>
        @endif
    </div>
</div>
</div>

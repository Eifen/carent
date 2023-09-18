<div>
    <a class="header-nav-links-item-link">Proyectos <font-awesome string-icon="fa-solid fa-caret-down"></font-awesome></a>
    <div class="header-nav-links-item-dropdown" :style="controlDropdown.projects.style"
        @mouseover="openDropDown('projects')" @mouseout="closeDropDown('projects')">
        @if (
            (Session::has('userPermissions') && Session::get('userPermissions')['projectP'] == 1) ||
                (Session::has('userPermissions') && Session::get('userPermissions')['closeP'] == 1))
            <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('projects') }}">Control de
                    proyectos</a></div>
        @endif
        @if (Session::has('userPermissions') && Session::get('userPermissions')['assignP'] == 1)
            <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('assign') }}">Asignacion de
                    proyectos</a></div>
        @endif
        @if (Session::has('userPermissions') && Session::get('userPermissions')['adminP'] == 1)
            <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('validate') }}">Control de horas
                    administrativas</a></div>
        @endif
        <div class="header-nav-links-item-dropdown-content"><a href="/projects/register-hours">Carga de horas</a></div>
    </div>
</div>
</div>

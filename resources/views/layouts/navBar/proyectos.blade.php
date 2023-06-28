<div>
    <a class="header-nav-links-item-link">Proyectos <font-awesome string-icon="fa-solid fa-caret-down"></font-awesome></a>
    <div class="header-nav-links-item-dropdown" :style="controlDropdown.projects.style"
        @mouseover="openDropDown('projects')" @mouseout="closeDropDown('projects')">
        <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('projects') }}">Control de
                proyectos</a></div>
        <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('closeProjects') }}">Cierre de
                proyectos</a></div>
        <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('assign') }}">Asignacion de
                proyectos</a></div>
        <div class="header-nav-links-item-dropdown-content"><a href="/projects/register-hours">Carga de horas</a></div>
    </div>
</div>

<div>
    <a class="header-nav-links-item-link">Mi cuenta <font-awesome string-icon="fa-solid fa-caret-down"></font-awesome></a>
    <div class="header-nav-links-item-dropdown" :style="controlDropdown.account.style"
        @mouseover="openDropDown('account')" @mouseout="closeDropDown('account')">
        <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('changePassword') }}">Cambiar
                contraseña</a>
        </div>
        <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('logout') }}">Cerrar Sesión</a></div>
        @if (Session::get('userId') == 1)
            <div class="header-nav-links-item-dropdown-content"><a href="{{ URL::route('maintenance') }}">
                    @if ($Maintenance == 2)
                        Activar mantenimiento
                    @else
                        Desactivar mantenimiento
                    @endif
                </a></div>
        @endif
    </div>
</div>

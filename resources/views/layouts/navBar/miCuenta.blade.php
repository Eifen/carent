<div>
    <a class="header-nav-links-item-link">Mi cuenta <font-awesome string-icon="fa-solid fa-caret-down"></font-awesome></a>
    <div class="header-nav-links-item-dropdown" :style="controlDropdown.account.style" @mouseover="openDropDown('account')" @mouseout="closeDropDown('account')">
        <div class="header-nav-links-item-dropdown-content"><a href="/cuenta/cambiarContra#06">Cambiar contraseña</a></div>
        <div class="header-nav-links-item-dropdown-content"><a href="/logout">Cerrar Sesión</a></div>
    </div>
</div>

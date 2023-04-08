<header class="header" id="header-nav" v-cloak>
    <nav class="header-nav">
        <div class="header-nav-logo"><img @click="goHome" src="/images/logo-carent-menu-expandido.png"/></div>
        <ul class="header-nav-links" :style="hamburgerMenu">
            <li class="header-nav-links-item" id="usuarios">@yield('usuarios')</li>
            <li class="header-nav-links-item" id="clientes">@yield('clientes')</li>
            <li class="header-nav-links-item" id="proyectos">@yield('proyectos')</li>
            <li class="header-nav-links-item" id="facturacion">@yield('facturacion')</li>
            <li class="header-nav-links-item" id="reportes">@yield('reportes')</li>
            <li class="header-nav-links-item" id="cambiarPassword" @mouseover="openDropDown" @mouseout="closeDropDown">@yield('miCuenta')</li>
        </ul>
        <div class="header-nav-toggle" v-if="!open"><font-awesome @click="statusBars" string-icon="fa-solid fa-bars"></font-awesome></div>
        <div class="header-nav-toggle" v-if="open"><font-awesome @click="statusBars" string-icon="fa-solid fa-close"></font-awesome></div>
    </nav>
</header>

@yield('dashboard')

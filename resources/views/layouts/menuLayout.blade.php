<header class="header" id="header-nav" v-cloak>
    <nav class="header-nav">
        <div class="header-nav-logo"><img @click="goHome" src="/images/logo-carent-menu-expandido.png"
                title="CarentLogoMenu" /></div>
        <ul class="header-nav-links" id="selectNav" :style="hamburgerMenu">
            <li class="header-nav-links-item" id="01">@yield('usuarios')</li>
            <li class="header-nav-links-item" id="01">@yield('usuarios')</li>
            <li class="header-nav-links-item" id="02">@yield('clientes')</li>
            <li class="header-nav-links-item" id="03" @mouseover="openDropDown('projects')"
                @mouseout="closeDropDown('projects')">@yield('proyectos')</li>
            <li class="header-nav-links-item" id="04">@yield('facturacion')</li>
            <li class="header-nav-links-item" id="05">@yield('reportes')</li>
            <li class="header-nav-links-item" id="06" @mouseover="openDropDown('account')"
                @mouseout="closeDropDown('account')">@yield('miCuenta')</li>
        </ul>
        <div class="header-nav-toggle" v-if="!open">
            <font-awesome @click="statusBars" string-icon="fa-solid fa-bars"></font-awesome>
        </div>
        <div class="header-nav-toggle" v-if="open">
            <font-awesome @click="statusBars" string-icon="fa-solid fa-close"></font-awesome>
        </div>
    </nav>
</header>

@yield('dashboard')

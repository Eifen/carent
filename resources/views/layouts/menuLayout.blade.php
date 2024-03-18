<header class="header" id="header-nav" v-cloak>
    <nav class="header-nav">
        <div class="header-nav-logo"><img title="logo-expandido" @click="goHome"
                src="/images/logo-carent-menu-expandido.png" title="CarentLogoMenu" /></div>
        <ul class="header-nav-links" id="selectNav" :style="hamburgerMenu">
            <li class="header-nav-links-item" id="user-name">Conectado como, <span>{{ Session::get('userName') }}
                    @if ($Maintenance == 1)
                        (En mantenimiento)
                    @endif
                </span>
            </li>
            @if (Session::has('userPermissions'))
                @yield('usuarios')
                @yield('clientes')
            @endif
            <li class="header-nav-links-item" id="03" @mouseover="openDropDown('projects')"
                @mouseout="closeDropDown('projects')">@yield('proyectos')</li>
            @if (Session::has('userPermissions'))
                @yield('facturacion')
                @yield('reportes')
            @endif
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

@if (Session::has('userId') && Session::get('userId') == 1)
    <li class="header-nav-links-item" id="05"><a class="header-nav-links-item-link"
            href="{{ URL::route('reports') }}">Reportes</a></li>
@endif

@if (Session::has('userPermissions') && Session::get('userPermissions')['clientP'] == 1)
    <li class="header-nav-links-item" id="02"><a class="header-nav-links-item-link"
            href="{{ URL::route('clients') }}">Clientes</a></li>
@endif

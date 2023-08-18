@if (Session::has('userPermissions') && Session::get('userPermissions')['clientP'] == 1)
    <a class="header-nav-links-item-link" href="{{ URL::route('clients') }}">Clientes</a>
@endif

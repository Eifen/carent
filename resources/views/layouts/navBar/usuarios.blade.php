@if (Session::has('userPermissions') && Session::get('userPermissions')['userP'] == 1)
    <a class="header-nav-links-item-link" href="{{ URL::route('users') }}">Usuarios</a>
@endif

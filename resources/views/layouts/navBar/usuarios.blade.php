@if (Session::has('userPermissions') && Session::get('userPermissions')['userP'] == 1)
    <li class="header-nav-links-item" id="01"><a class="header-nav-links-item-link"
            href="{{ URL::route('users') }}">Usuarios</a></li>
@endif

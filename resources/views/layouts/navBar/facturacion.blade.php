@if (Session::has('userPermissions') && Session::get('userPermissions')['billingP'] == 1)
    <li class="header-nav-links-item" id="04"><a class="header-nav-links-item-link"
            href="{{ URL::route('billing') }}">Facturación</a></li>
@endif

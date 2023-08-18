@if (Session::has('userPermissions') && Session::get('userPermissions')['billingP'] == 1)
    <a class="header-nav-links-item-link" href="{{ URL::route('billing') }}">Facturación</a>
@endif

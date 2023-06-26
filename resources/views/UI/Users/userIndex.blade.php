<div id="section-users">
    <loading :active="!isMounted"></loading>
    <listing-crud :table-info="proxyToJson(listData)"
    :pagination-lenght="maxLengthPagination"
    :pagination-limit="lengthColumns"
    :title-object="proxyToJson(usersColumn)"
    :select-search="proxyToJson(selectSearch)"
    title-table="usuarios"
    button-title="usuario"
    v-if="isMounted"
    not-found-message="No existen usuarios registrados"
    view-create
    view-search
    @columnS1Target="editUsuarios"
    @columnS2Target="permisosUsuarios"
    @createButton="crearUsuario()"></listing-crud>
</div>

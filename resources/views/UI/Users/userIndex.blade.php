<div id="section-users">
    <loading :active="!isMounted"></loading>
    <listing-crud :table-info="dataParse()"
    :pagination-lenght="maxLengthPagination"
    :pagination-limit="lengthColumns"
    :title-object="titleParse()"
    :select-search="searchParse()"
    title-table="usuarios"
    button-title="usuario"
    v-if="isMounted"
    @columnS1Target="editUsuarios"
    @columnS2Target="permisosUsuarios"
    @createButton="crearUsuario()"></listing-crud>
</div>
<div id="section-clients">
    <loading :active="!isMounted"></loading>
    <listing-crud  v-if="isMounted"
    :title-object="proxyToJson(clientsColumns)"
    :pagination-lenght="maxLengthPagination"
    :pagination-limit="lengthColumns"
    :table-info="proxyToJson(listData)"
    title-table="clientes"
    button-title="cliente"
    :select-search="proxyToJson(selectSearch)"
    not-found-message="No hay clientes creados"
    view-create
    view-search
    @createButton="createClient()"
    @columns1target="editClient"></listing-crud>
</div>

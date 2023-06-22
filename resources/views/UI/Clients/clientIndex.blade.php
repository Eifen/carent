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
    view-pagination
    @createButton="createClient()"
    @columns1target="editClient"></listing-crud>
</div>

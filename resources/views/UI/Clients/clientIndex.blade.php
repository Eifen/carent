<div id="section-clients">
    <loading :active="!isMounted"></loading>
    <listing-crud  v-if="isMounted"
    :title-object="clientParse(clientsColumns)"
    :pagination-lenght="maxPagination"
    :pagination-limit="paginationLength"
    {{-- TODO: Cargar la data de clientes --}}
    :table-info="clientParse(clientsData)"
    title-table="clientes"
    button-title="cliente"
    :select-search="clientParse(selectSearch)"
    @createButton="createClient()"></listing-crud>
</div>

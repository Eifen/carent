<div id="billing-project">
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(billingsColumns)" :pagination-lenght="maxLengthPagination"
        :pagination-limit="lengthColumns" :table-info="proxyToJson(listData)" title-table="facturacion"
        :select-search="proxyToJson(selectSearch)" not-found-message="No existen proyectos creados" view-search>
    </listing-crud>
</div>

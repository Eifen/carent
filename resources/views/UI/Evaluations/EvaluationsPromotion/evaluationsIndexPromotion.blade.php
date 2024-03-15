<div id="section-evaluations-promotion">
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(evaluationsPromotionColumns)"
        :pagination-lenght="maxLengthPagination" :pagination-limit="lengthColumns" :table-info="proxyToJson(listData)"
        title-table="Promociones y Ascensos" :select-search="proxyToJson(selectSearch)"
        not-found-message="No hay promociones y ascensos creados" view-search>
        {{-- @createButton="createClient()" @columns1target="editClient" @columns2target="clientInfo"> --}}
    </listing-crud>
</div>

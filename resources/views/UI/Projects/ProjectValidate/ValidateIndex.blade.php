<div id="validate-admin">
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(validateColumns)" :pagination-lenght="maxLengthPagination"
        :pagination-limit="lengthColumns" :table-info="proxyToJson(listData)"
        title-table="Aprobación horas administrativas" not-found-message="No posee horas por aprobar"
        :select-search="proxyToJson(selectSearch)" view-search status-table="adminHours"
        @columns1target="controlHours($event,1)" @columns2target="controlHours($event,2)"
        @columns3target="controlHours($event,3)"></listing-crud>
</div>

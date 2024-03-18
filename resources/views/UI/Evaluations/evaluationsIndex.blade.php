{{-- <div id="evaluations-project">
    <loading :active="!isMounted"></loading>
    <evaluations>
        <evaluations />
</div> --}}




<div id="evaluations-project">
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(EvaluationsColumns)"
        :pagination-lenght="maxLengthPagination" :pagination-limit="lengthColumns" :table-info="proxyToJson(listData)"
        title-table="evaluaciones" :select-search="proxyToJson(selectSearch)"
        not-found-message="No hay evaluaciones creadas" view-search>
        {{-- @createButton="createClient()"@columns1target="editClient" @columns2target="clientInfo"> --}}
    </listing-crud>
</div>



{{-- <div id="evaluations-project">
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(EvaluationsColumns)"
        :pagination-lenght="maxLengthPagination" :pagination-limit="lengthColumns" :table-info="proxyToJson(listData)"
        title-table="evaluaciones" not-found-message="No posee evaluaciones creadas"
        :select-search="proxyToJson(selectSearch)" view-search status-table="adminHours"></listing-crud>
</div> --}}

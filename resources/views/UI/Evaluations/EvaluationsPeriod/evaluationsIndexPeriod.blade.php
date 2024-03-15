<div id="evaluations-period-project">
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(PeriodsColumns)" :pagination-lenght="maxLengthPagination"
        :pagination-limit="lengthColumns" :table-info="proxyToJson(listData)"
        title-table="Habilitar Periodo de Evaluación" button-title="Periodo" :select-search="proxyToJson(selectSearch)"
        not-found-message="No hay evaluaciones creadas" view-create view-search @columnS1Target="editPeriod"
                  @createButton="createPeriod()">
        {{-- @createButton="createClient()"@columns1target="editClient" @columns2target="clientInfo"> --}}
    </listing-crud>
</div>

<div id="section-evaluations-project">
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(evaluationsProjectColumns)"
        :pagination-lenght="maxLengthPagination" :pagination-limit="lengthColumns" :table-info="proxyToJson(listData)"
        title-table="Proyecto para evaluar" :select-search="proxyToJson(selectSearch)"
        not-found-message="No hay informacion de evaluaciones creados" view-search @columns1target="infoAutoEvaluation"
        @columns2target="autoEvaluation" @columns3target="infoEvaluation">
        {{-- @createButton="createClient()" @columns1target="editClient" @columns2target="clientInfo"> --}}
    </listing-crud>

    <!-- Modal -->
    <div class="modal fade" id="UserInfoModal" tabindex="-1">

        <loading :active="previewUserInfo === null"></loading>
        <div class="modal-dialog modal-lg">
            <div class="modal-content" v-if="previewUserInfo !== null">
                <div class="modal-header">
                    <h5 class="modal-title">@{{ 'evaluado' }}</h5>
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">
                    <div class="modal-preview">
                        {{-- Encabezados --}}
                        <div class="modal-preview-thead" for="thead-evaluator">Evaluador</div>
                        <div class="modal-preview-thead" for="thead-evaluated-score">Autocalificación (Evaluado)</div>
                        <div class="modal-preview-thead" for="thead-evaluator-score">Calificación (Evaluador)</div>
                        <div class="modal-preview-thead" for="thead-current-position-2">Cargo actual</div>
                        <div class="modal-preview-thead" for="thead-proposed-position-2">Cargo propuesto</div>
                        <div class="modal-preview-thead" for="thead-approved-position-2">Cargo aprobado</div>

                        {{-- Cuerpo del encabezado --}}
                        <div class="modal-preview-tbody" for="tbody-evaluator">@{{ previewUserInfo.evaluador }}</div>
                        <div class="modal-preview-tbody" for="tbody-evaluated-score">@{{ acumu.auto }}</div>
                        <div class="modal-preview-tbody" for="tbody-evaluator-score">@{{ acumu.eva }}</div>
                        <div class="modal-preview-tbody" for="tbody-current-position-2">
                            @{{ previewUserInfo.cargoactual }}
                        </div>
                        <div class="modal-preview-tbody" for="tbody-proposed-position-2">
                            @{{ previewUserInfo.ultipopuesto }}
                        </div>
                        <div class="modal-preview-tbody" for="tbody-approved-position2">@{{ previewUserInfo.ultiasc }}</div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

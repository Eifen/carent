<div id="section-evaluations-list">

        @if (Session::has('nenomessage'))
            <div class="alert alert-success" role="alert">
                Memorandum Cargado!
            </div>
        @endif
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(evaluationsListColumns)"
                  :pagination-lenght="maxLengthPagination" :pagination-limit="lengthColumns"
                  :table-info="proxyToJson(listData)"
                  title-table="Listado del Personal por Evaluar" :select-search="proxyToJson(selectSearch)"
                  not-found-message="No hay un listado de evaluaciones creados" view-search @columns1target="evaluation"
                  @columns2target="evaluation" @columns3target="memoModal">
        {{-- @createButton="createClient()" @columns1target="editClient" @columns3target="clientInfo"> --}}
    </listing-crud>

    <!-- <form action="{{url('evaluaciones/listado-del-personal/uploadproduct')}}" method="post" enctype="multipart/form-data"> -->
    <!-- Modal -->
    <div class="modal fade" id="evamemomodal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-body-title">Cargar Memorandum</h5>
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body-input">
                    <div class="modal-preview">
                        <!-- {{-- Encabezados --}}
                        <div class="modal-preview-thead" for="thead-partner">lalala</div> -->
                        {{-- Cuerpo del encabezado --}}
                        <!-- <div class="modal-preview-tbody" for="tbody-partner">lololo</div> -->
                        <form action="listado-del-personal/EnvioDatos" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="codigo"  :value="controlcodemodal">
                            <div class="mb-3">
                                <label class="form-label" for="inputFile">File:</label>
                                <input
                                    type="file"
                                    name="file"
                                    id="inputFile"
                                    accept=".pdf"
                                    required
                                    class="form-control @error('file') is-invalid @enderror">
                                @error('file')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit">Enviar</button>
                        </form>
                        <div class="modal-footer">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

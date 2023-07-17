<div id="section-clients">
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(clientsColumns)" :pagination-lenght="maxLengthPagination"
        :pagination-limit="lengthColumns" :table-info="proxyToJson(listData)" title-table="clientes"
        button-title="cliente" :select-search="proxyToJson(selectSearch)" not-found-message="No hay clientes creados"
        view-create view-search @createButton="createClient()" @columns1target="editClient" @columns2target="clientInfo">
    </listing-crud>
    <!-- Modal -->
    <div class="modal fade" id="clientInfoModal" tabindex="-1">
        <loading :active="previewClientInfo === null"></loading>
        <div class="modal-dialog">
            <div class="modal-content" v-if="previewClientInfo !== null">
                <div class="modal-header">
                    <h5 class="modal-title">@{{ previewClientInfo.bussiness_name }}</h5>
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">
                    <div class="modal-preview">
                        {{-- Encabezados --}}
                        <div class="modal-preview-thead" for="thead-partner">Socio</div>
                        <div class="modal-preview-thead" for="thead-address">Dirección</div>
                        <div class="modal-preview-thead" for="thead-rif">Rif</div>
                        <div class="modal-preview-thead" for="thead-nit">Nit</div>
                        <div class="modal-preview-thead" for="thead-country">País</div>
                        <div class="modal-preview-thead" for="thead-phone">Teléfono fiscal</div>
                        <div class="modal-preview-thead" for="thead-sector">Sector</div>
                        <div class="modal-preview-thead" for="thead-service">Servicio</div>
                        <div class="modal-preview-thead" for="thead-email">Correo fiscal</div>
                        <div class="modal-preview-thead" for="thead-website">Página Web</div>
                        {{-- Cuerpo del encabezado --}}
                        <div class="modal-preview-tbody" for="tbody-partner">@{{ previewClientInfo.partner_name }}</div>
                        <div class="modal-preview-tbody" for="tbody-address">@{{ previewClientInfo.client_address }}</div>
                        <div class="modal-preview-tbody" for="tbody-rif">@{{ previewClientInfo.rif }}</div>
                        <div class="modal-preview-tbody" for="tbody-nit">@{{ previewClientInfo.nit }}</div>
                        <div class="modal-preview-tbody" for="tbody-country">@{{ previewClientInfo.country_name }}</div>
                        <div class="modal-preview-tbody" for="tbody-phone">@{{ previewClientInfo.tax_phone }}</div>
                        <div class="modal-preview-tbody" for="tbody-sector">@{{ previewClientInfo.sector_name }}</div>
                        <div class="modal-preview-tbody" for="tbody-service">@{{ previewClientInfo.service_name }}</div>
                        <div class="modal-preview-tbody" for="tbody-email">@{{ previewClientInfo.tax_email }}</div>
                        <div class="modal-preview-tbody" for="tbody-website">@{{ previewClientInfo.website }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

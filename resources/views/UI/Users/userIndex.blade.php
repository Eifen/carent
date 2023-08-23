<div id="section-users">
    <loading :active="!isMounted"></loading>
    <listing-crud :table-info="proxyToJson(listData)" :pagination-lenght="maxLengthPagination"
        :pagination-limit="lengthColumns" :title-object="proxyToJson(usersColumn)"
        :select-search="proxyToJson(selectSearch)" title-table="usuarios" button-title="usuario" v-if="isMounted"
        not-found-message="No existen usuarios registrados" view-create view-search @columnS1Target="editUsuarios"
        @columnS2Target="permisosUsuarios" @createButton="crearUsuario()"></listing-crud>
    {{-- Modal --}}
    <div class="modal fade" id="userInfoModal" tabindex="-1">
        <loading :active="previewUserInfo === null"></loading>
        <div class="modal-dialog modal-lg">
            <div class="modal-content" v-if="previewUserInfo !== null">
                <div class="modal-header">
                    <h5 class="modal-title">@{{ previewUserInfo.nombre }} (@{{ previewUserInfo.codigo }})</h5>
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">
                    <div class="modal-preview">
                        {{-- Encabezados --}}
                        <div class="modal-preview-thead" for="thead-code">Cédula</div>
                        <div class="modal-preview-thead" for="thead-address">Correo</div>
                        <div class="modal-preview-thead" for="thead-userM">Menu Usuarios</div>
                        <div class="modal-preview-thead" for="thead-clientM">Menu cliente</div>
                        <div class="modal-preview-thead" for="thead-projectM">Menu proyectos</div>
                        <div class="modal-preview-thead" for="thead-billingM">Menu facturacion</div>
                        {{-- Cuerpo del encabezado --}}
                        <div class="modal-preview-tbody" for="tbody-code">@{{ previewUserInfo.cedula }}</div>
                        <div class="modal-preview-tbody" for="tbody-address">@{{ previewUserInfo.correo }}</div>
                        <div class="modal-preview-tbody" for="tbody-userM">
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.userP" type="checkbox"
                                    :checked="previewUserInfo.userP">
                                <label class="form-check-label">Control de usuario</label>
                            </div>
                        </div>
                        <div class="modal-preview-tbody" for="tbody-clientM">
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.clientP" type="checkbox"
                                    :checked="previewUserInfo.clientP">
                                <label class="form-check-label">Control de cliente</label>
                            </div>
                        </div>
                        <div class="modal-preview-tbody" for="tbody-projectM">
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.projectP" type="checkbox"
                                    :checked="previewUserInfo.projectP">
                                <label class="form-check-label">Control de proyectos</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.assignP" type="checkbox"
                                    :checked="previewUserInfo.assignP">
                                <label class="form-check-label">Assignación de proyectos</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.adminP" type="checkbox"
                                    :checked="previewUserInfo.adminP">
                                <label class="form-check-label">Control de horas administrativas</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.closeP" type="checkbox"
                                    :checked="previewUserInfo.closeP">
                                <label class="form-check-label">Cierre de proyectos</label>
                            </div>
                        </div>
                        <div class="modal-preview-tbody" for="tbody-billingM">
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.billingP" type="checkbox"
                                    :checked="previewUserInfo.billingP">
                                <label class="form-check-label">Control de facturas</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="dashboard-form-container-form-button" v-if="!isClick" @click="updateAccess()">
                        <span v-if="!isClick">Guardar menu</span>
                    </div>
                    <span v-else-if="isClick">
                        <font-awesome string-icon="fa-solid fa-spinner" is-spin></font-awesome>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

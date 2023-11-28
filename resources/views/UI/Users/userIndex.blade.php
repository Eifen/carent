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
                    <h5 class="modal-title">@{{ previewUserInfo.nombre }} (@{{ previewUserInfo.código }})</h5>
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
                        <div class="modal-preview-thead" for="thead-reportM">Menu Reportes</div>
                        {{-- Cuerpo del encabezado --}}
                        <div class="modal-preview-tbody" for="tbody-code">@{{ previewUserInfo.cédula }}</div>
                        <div class="modal-preview-tbody" for="tbody-address">@{{ previewUserInfo.correo }}</div>
                        <div class="modal-preview-tbody" for="tbody-userM">
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.userP" type="checkbox"
                                    :checked="previewUserInfo.userP">
                                <label class="form-check-label">Control de usuario</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.dCargaH" type="checkbox"
                                    :checked="previewUserInfo.dCargaH">
                                <label class="form-check-label">Deshabilitar bloqueo de horas</label>
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
                        <div class="modal-preview-tbody" for="tbody-reportM">
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.reportP" type="checkbox"
                                    :checked="previewUserInfo.reportP">
                                <label class="form-check-label">Menu reportes</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rclosureP" type="checkbox"
                                    :checked="previewUserInfo.rclosureP">
                                <label class="form-check-label">Reporte de cierre de proyectos</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rdirectiveMP"
                                    type="checkbox" :checked="previewUserInfo.rdirectiveMP">
                                <label class="form-check-label">Reporte directivo mensual</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rhorasP" type="checkbox"
                                    :checked="previewUserInfo.rhorasP">
                                <label class="form-check-label">Reporte de horas no cargables</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rdirectiveAP"
                                    type="checkbox" :checked="previewUserInfo.rdirectiveAP">
                                <label class="form-check-label">Reporte directivo acumulado</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rproyectosP" type="checkbox"
                                    :checked="previewUserInfo.rproyectosP">
                                <label class="form-check-label">Reporte bitácora de Proyectos</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rnoRegisterP"
                                    type="checkbox" :checked="previewUserInfo.rnoRegisterP">
                                <label class="form-check-label">Reporte de personas por cargar</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rproyP" type="checkbox"
                                    :checked="previewUserInfo.rproyP">
                                <label class="form-check-label">Reporte de horas a proyectos</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rusersP" type="checkbox"
                                    :checked="previewUserInfo.rusersP">
                                <label class="form-check-label">Reporte de usuarios</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rlogUsersP" type="checkbox"
                                    :checked="previewUserInfo.rlogUsersP">
                                <label class="form-check-label">Reporte histórico de horas</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rQuotasP" type="checkbox"
                                    :checked="previewUserInfo.rQuotasP">
                                <label class="form-check-label">Reporte de quotas y facturas por cobrar</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rClientsP" type="checkbox"
                                    :checked="previewUserInfo.rClientsP">
                                <label class="form-check-label">Reporte de clientes</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" v-model="previewUserInfo.rBillingsP" type="checkbox"
                                    :checked="previewUserInfo.rBillingsP">
                                <label class="form-check-label">Reporte de clientes</label>
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

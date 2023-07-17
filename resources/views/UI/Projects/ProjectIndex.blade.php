<div id="section-projects">
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(projectsColumns)" :pagination-lenght="maxLengthPagination"
        :pagination-limit="lengthColumns" :table-info="proxyToJson(listData)" title-table="proyectos"
        button-title="proyecto" :select-search="proxyToJson(selectSearch)"
        not-found-message="No existen proyectos creados" view-create view-search @createButton="createProject()"
        @columns1target="editProject" @columns2target="infoProject"></listing-crud>
    <!-- Modal -->
    <div class="modal fade" id="projectInfoModal" tabindex="-1">
        <loading :active="previewProjectInfo === null"></loading>
        <div class="modal-dialog">
            <div class="modal-content" v-if="previewProjectInfo !== null">
                <div class="modal-header">
                    <h5 class="modal-title">@{{ previewProjectInfo.project.project_description }}</h5>
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">
                    <div class="modal-preview">
                        {{-- Encabezados --}}
                        <div class="modal-preview-thead" for="thead-partner">Socio</div>
                        <div class="modal-preview-thead" for="thead-client">Cliente</div>
                        <div class="modal-preview-thead" for="thead-manager">Gerente del proyecto</div>
                        <div class="modal-preview-thead" for="thead-quality">Socio de calidad</div>
                        <div class="modal-preview-thead" for="thead-hiring">Fecha de contratación</div>
                        <div class="modal-preview-thead" for="thead-pValue">Monto del proyecto</div>
                        <div class="modal-preview-thead" for="thead-hoursAssigned">Horas asignadas</div>
                        <div class="modal-preview-thead" for="thead-averageRate">Tasa promedio</div>
                        <div class="modal-preview-thead" for="thead-additionalHour">Horas totales adicionales</div>
                        <div class="modal-preview-thead" for="thead-additionalValue">Montos totales adicionales</div>
                        <div class="modal-preview-thead" for="thead-department">División</div>
                        <div class="modal-preview-thead" for="thead-managerDepartment">Gerente</div>
                        <div class="modal-preview-thead" for="thead-progress">Progreso del proyecto</div>
                        {{-- Cuerpo del encabezado --}}
                        <div class="modal-preview-tbody" for="tbody-partner">@{{ previewProjectInfo.project.partner_name }}</div>
                        <div class="modal-preview-tbody" for="tbody-client">@{{ previewProjectInfo.project.bussiness_name }}</div>
                        <div class="modal-preview-tbody" for="tbody-manager">@{{ previewProjectInfo.project.manager_name }}</div>
                        <div class="modal-preview-tbody" for="tbody-quality">@{{ previewProjectInfo.project.quality_partner_name }}</div>
                        <div class="modal-preview-tbody" for="tbody-hiring">@{{ previewProjectInfo.project.hiring_date }}</div>
                        <div class="modal-preview-tbody" for="tbody-pValue">@{{ previewProjectInfo.project.project_value }}
                            @{{ previewProjectInfo.project.currency_symbol }}</div>
                        <div class="modal-preview-tbody" for="tbody-hoursAssigned">@{{ totalHoursAssigned() }}</div>
                        <div class="modal-preview-tbody" for="tbody-additionalHour">@{{ totalAdditionalAssigned(1) }}</div>
                        <div class="modal-preview-tbody" for="tbody-additionalValue">@{{ totalAdditionalAssigned(2) }}</div>
                        <div class="modal-preview-tbody" for="tbody-averageRate">@{{ previewProjectInfo.project.average_rate }}</div>
                        <div class="modal-preview-tbody" for="tbody-departments">
                            <div v-for="(department,position) in previewProjectInfo.departments" :key="position">
                                <span>@{{ department.department_name }}</span>
                                <span>@{{ department.manager_department_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

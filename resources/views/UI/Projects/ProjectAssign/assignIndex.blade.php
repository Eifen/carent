<div id="project-assign">
    <loading :active="!isMounted"></loading>
    <listing-crud v-if="isMounted" :title-object="proxyToJson(assignColumns)" :pagination-lenght="maxLengthPagination"
        :pagination-limit="lengthColumns" :table-info="proxyToJson(listData)" title-table="Asignación de Proyectos"
        not-found-message="No posee proyectos asignados" :select-search="proxyToJson(selectSearch)" view-search
        @columns1target="assignProject"></listing-crud>
</div>

{{-- Modal de asignacion de proyectos --}}
<div class="modal fade" id="asignHourModal" tabindex="-1">
    <loading :active="!isMounted"></loading>
    <div class="modal-dialog modal-lg" v-if="isMounted">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Horas</h5>
                <button type="button" class="btn btn-info" data-bs-dismiss="modal">X</button>
            </div>
            <div class="modal-body">
                <fieldset>
                    {{-- Informacion del gerente --}}
                    <legend class="dashboad-form-container-form-legends">Gerente asignado</legend>
                    <div class="mb-3">
                        <label for="managerAssigned">@{{ managerAssigned }}</label>
                    </div>
                    {{-- Informacion del Cliente --}}
                    <legend class="dashboad-form-container-form-legends">Cliente</legend>
                    <div class="mb-3">
                        <label for="managerAssigned">@{{ clientName }}</label>
                    </div>
                    {{-- Informacion del proyecto --}}
                    <legend class="dashboad-form-container-form-legends">Proyecto Seleccionado</legend>
                    <div class="mb-3">
                        <label for="managerAssigned">@{{ projectName }}</label>
                    </div>
                    {{-- Horas totales para asignar --}}
                    <legend class="dashboad-form-container-form-legends">Horas asignadas al proyecto</legend>
                    <div class="mb-3">
                        <label for="hoursAssigned">@{{ hoursAssigned }}</label>
                    </div>
                    {{-- Horas adicionales --}}
                    <legend class="dashboad-form-container-form-legends">Horas adicionales</legend>
                    <div class="mb-3">
                        <label for="hoursAssigned">@{{ additionalHours }}</label>
                    </div>
                    <!-- Usuarios -->
                    <legend class="dashboad-form-container-form-legends">Usuarios a asignar <span
                            class="dashboard-form-container-form-title-field">*</span></legend>
                    <div class="mb-3">
                        <!-- Select Users -->
                        <Multiselect v-model="inputUsersAssigned" mode="tags" :close-on-select="true"
                            :searchable="true" placeholder="Seleccione uno o más usuarios" openDirection="top"
                            :options="usersPerDepartment" :key="inputUsersAssigned"></Multiselect>
                    </div>
                </fieldset>
                <span class="badge text-bg-info" v-if="inputUsersAssigned != 0 && isMounted">Indique la cantidad de
                    horas para asignar</span>
                <fieldset class="dashboard-form-container-form-fieldset" v-if="inputUsersAssigned != 0 && isMounted"
                    v-for="(select, cursor) in inputUsersAssigned.length" :key="cursor">
                    <!-- Usuario -->
                    <div class="mb-3">
                        <label for="projectDescription">Nombre de analista</label>
                        <div class="input-group">@{{ managerUserAssigned[cursor].userName }}
                        </div>
                    </div>
                    <!-- Horas asignadas -->
                    <div class="mb-3">
                        <label for="Hours">Horas asignadas
                            <span class="dashboard-form-container-form-title-field">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                            </span>
                            <input type="text" class="form-control" id="Hours"
                                v-model="managerUserAssigned[cursor].hoursAssigned" @input="totalHours($event)" />
                        </div>
                    </div>
                    <!-- Horas registradas -->
                    <div class="mb-3">
                        <label for="HoursRegister">Horas registradas</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                            </span>
                            <span class="form-control"> @{{ managerUserAssigned[cursor].hourRegister }}</span>
                        </div>
                    </div>
                </fieldset>
                <fieldset>
                    {{-- Horas restantes por asignar --}}
                    <legend class="dashboad-form-container-form-legends">Horas restantes por asignar a usuarios</legend>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                            </span>
                            <input type="text" class="form-control" v-model="missinHours" :disabled="true" />
                        </div>
                        <!-- Mensajes de error en Horas-->
                        <div class="form-ErrorInput" v-if="hoursAssignedError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            @{{ hoursAssignedError }}
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <div class="dashboard-form-container-form-button" v-if="isValid && !isClick"
                    @click="asignHoursSubmit()">
                    <span v-if="!isClick">Guardar asignación</span>
                </div>
                <span v-else-if="isClick">
                    <font-awesome string-icon="fa-solid fa-spinner" is-spin></font-awesome>
                </span>
            </div>
        </div>
    </div>
</div>

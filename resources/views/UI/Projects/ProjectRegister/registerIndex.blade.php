<div id="hour-register">
    <loading :active="!isMounted"></loading>
    <div :class="listClass" v-if="isMounted" v-cloak>
        <div class="list-container-title register-hour-title" v-cloak>
            Carga de Horas semanal
            <legend class="dashboad-form-container-form-legends">Campos Obligatorios <span
                    class="dashboard-form-container-form-title-field">*</span></legend>
        </div>
        {{-- Años --}}
        <div class="mb-3 register-hour-select-year">
            <label for="Years">Año <span class="dashboard-form-container-form-title-field">*</span></label>
            <div class="input-group">
                <select class="form-select" v-model="inputYearSelect" title="YearSelect">
                    <option v-for="(year, cursor) in inputYearOptions" :key="cursor" :selected="cursor === 0"
                        :disabled="cursor === 0" :value="cursor">
                        <span>@{{ year }}</span>
                    </option>
                </select>
            </div>
        </div>
        {{-- Meses --}}
        <div class="mb-3 register-hour-select-month" v-if="inputYearSelect != 0">
            <label for="Months">Mes <span class="dashboard-form-container-form-title-field">*</span></label>
            <div class="input-group">
                <select class="form-select" v-model="inputMonthSelect" title="MonthSelect">
                    <option v-for="(month, cursor) in inputMonthOptions" :key="cursor" :value="cursor"
                        :selected="cursor === 0" :disabled="cursor === 0">
                        <span>@{{ month }}</span>
                    </option>
                </select>
            </div>
        </div>
        {{-- Semanas --}}
        <div class="mb-3 register-hour-select-week" v-if="inputMonthSelect != 0">
            <label for="Weeks">Semana
                <span class="dashboard-form-container-form-title-field">*</span></label>
            <div class="input-group">
                <select class="form-select" v-model="inputWeekSelect" title="WeekSelect">
                    <option v-for="(week, cursor) in inputWeekOptions" :key="cursor" :value="cursor"
                        :selected="cursor === 0" :disabled="cursor === 0">
                        <span>@{{ week.message }}</span>
                    </option>
                </select>
            </div>
        </div>
        {{-- Selector de proyectos --}}
        <div class="mb-3 register-hour-select-project" v-if="isSelectRange">
            <label for="Weeks">Proyectos asignados para cargar</label>
            <!-- Select Projects -->
            <Multiselect v-model="inputProjectSelect" mode="tags" :close-on-select="true" :searchable="true"
                placeholder="Seleccione o escriba que proyectos desea cargar" openDirection="top"
                :options="inputProjectsMultiSelect" autocomplete="nope">
            </Multiselect>
        </div>
        <div class="table-responsive list-container-table" v-if="inputProjectSelect.length != 0 && isSelectRange"
            v-cloak>
            {{-- Proyectos --}}
            <table class="table table-hover table-bordered">
                <thead class="list-container-table-thead">
                    <tr>
                        <th scope="col" class="col-sm-5 col-md-3 col-lg-1" valign="middle">Proyecto</th>
                        <th scope="col" class="col-sm-5 col-md-3 col-lg-1" valign="middle"
                            v-for="(day,cursor) in listDayData" :key="cursor">
                            @{{ day.name }} <br> (@{{ day.date }})
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(project,position) in gridProjectInfo" :key="position">
                        <td scope="row">
                            @{{ project.description.toUpperCase() }}
                        </td>
                        <td scope="row" align="center" valign="middle" v-for="(day,cursor) in listDayData"
                            :key="cursor">
                            {{-- Carga de horas --}}
                            <load-hours :associated-load-project="project.projectAssignedId"
                                :info-assigned-project="listProjectHourData" :associated-day="day.date"
                                :key="listProjectHourData" load-ref="project"
                                @register-hour="registerDay($event,project.projectAssignedId)">
                            </load-hours>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        {{-- Selector de administracion --}}
        <div class="mb-3 register-hour-select-admin" v-if="isSelectRange">
            <label for="Weeks">Horas administrativas a cargar</label>
            <!-- Select Admin -->
            <Multiselect v-model="inputAdminSelect" mode="tags" :close-on-select="true" :searchable="true"
                placeholder="Seleccione o escriba que horas administrativas desea cargar" openDirection="top"
                :options="inputAdminMultiSelect" autocomplete="nope">
            </Multiselect>
        </div>
        {{-- Administrativo --}}
        <div class="table-responsive list-container-table-2" v-if="inputAdminSelect.length != 0 && isSelectRange"
            v-cloak>
            {{-- Administrativo --}}
            <table class="table table-hover table-bordered">
                <thead class="list-container-table-thead">
                    <tr>
                        <th scope="col" class="col-sm-5 col-md-3 col-lg-1" valign="middle">Administrativo</th>
                        <th scope="col" class="col-sm-5 col-md-3 col-lg-1" valign="middle"
                            v-for="(day,cursor) in listDayData" :key="cursor">
                            @{{ day.name }} <br> (@{{ day.date }})
                        </th>
                    </tr>
                <tbody>
                    <tr v-for="(admin,position) in gridAdminInfo" :key="position">
                        <td scope="row">
                            @{{ admin.description.toUpperCase() }}
                        </td>
                        <td scope="row" align="center" valign="middle" v-for="(day,cursor) in listDayData"
                            :key="cursor">
                            {{-- Carga de horas --}}
                            <load-hours :associated-load-project="admin.adminHourId"
                                :info-assigned-project="listAdminHourData" :associated-day="day.date"
                                :key="listAdminHourData" load-ref="admin"
                                @register-hour="registerAdminDay($event,admin.adminHourId)">
                            </load-hours>
                        </td>
                    </tr>
                </tbody>
                </thead>
            </table>
        </div>
    </div>
</div>

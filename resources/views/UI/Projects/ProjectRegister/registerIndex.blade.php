<div id="hour-register">
    <loading :active="!isMounted"></loading>
    <div :class="listClass" v-if="isMounted" v-cloak>
        <div class="list-container-title register-hour-title" v-cloak>
            Carga de Horas semana
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
        <div class="table-responsive list-container-table" v-if="isSelectRange" v-cloak>
            {{-- Proyectos --}}
            <table class="table table-hover table-bordered">
                <thead class="list-container-table-thead">
                    <tr>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Proyecto</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2" v-for="(day,cursor) in listDayData"
                            :key="cursor">
                            @{{ day.name }} <br> (@{{ day.date }})
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(project,position) in projectAssociatedToCharge" :key="position">
                        <td scope="row">
                            @{{ project.project_description.toUpperCase() }} PARA @{{ project.bussiness_name.toUpperCase() }}
                        </td>
                    </tr>
                </tbody>
            </table>
            {{-- Administrativo --}}
            <table class="table table-hover table-bordered">
                <thead class="list-container-table-thead">
                    <tr>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Concepto</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Lunes</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Martes</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Miércoles</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Jueves</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Viernes</th>
                    </tr>
                <tbody>
                    <tr>
                        <td scope="row">
                            <div class="mb-3">
                                <div class="input-group">
                                    <select class="form-select" v-model="inputConceptSelect" title="ConceptSelect">
                                        <option value=0 disabled>Seleccione el concepto</option>
                                        <option v-for="(concept,position) in conceptHourArray" :key="position"
                                            :value="concept.admin_hours_id">
                                            <span>@{{ concept.concept_description }}</span>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                </thead>
                <tfoot>
                    <tr>
                        <td scope="row" class="col-sm-6 col-md-4 col-lg-2">Total Horas</td>
                        <td scope="row" class="col-sm-6 col-md-4 col-lg-2">0</td>
                        <td scope="row" class="col-sm-6 col-md-4 col-lg-2">0</td>
                        <td scope="row" class="col-sm-6 col-md-4 col-lg-2">0</td>
                        <td scope="row" class="col-sm-6 col-md-4 col-lg-2">0</td>
                        <td scope="row" class="col-sm-6 col-md-4 col-lg-2">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

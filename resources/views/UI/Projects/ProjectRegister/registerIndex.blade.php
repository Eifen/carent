<div id="hour-register">
    <loading :active="!isMounted"></loading>
    <div :class="listClass" v-if="isMounted" v-cloak>
        <div class="list-container-title register-hour-title" v-cloak>Carga de Horas semana </div>
        <div class="table-responsive list-container-table" v-cloak>
            {{-- Proyectos --}}
            <table class="table table-hover table-bordered">
                <thead class="list-container-table-thead">
                    <tr>
                        <th scope="col" colspan="2">Proyecto</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Lunes</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Martes</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Miércoles</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Jueves</th>
                        <th scope="col" class="col-sm-6 col-md-4 col-lg-2">Viernes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(project,position) in projectAssociatedToCharge" :key="position">
                        <td scope="row" colspan="2">
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

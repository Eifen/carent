<template>
    <div>
        <h2 class="close-title"> CIERRE DE PROYECTO </h2>
        <h3 class="close-subtitle"> ASPECTOS FINANCIEROS Y/O INVERSIÓN </h3>
        <div class="close-project">
            <div class="close-project-date">
                <span for="close-project-date-time"> Fecha de cierre </span>
                <input type="date" class="form-control" id="close-project-date-time">
            </div>
            <div class="close-project-label" id="data-info">Datos</div>
            <div class="close-project-label" id="value-proposed">Valores Propuesta</div>
            <div class="close-project-label" id="additional-billing">Facturación Adicional</div>
            <div class="close-project-label" id="final-value-executed">Valores Finales Ejecutados</div>
            <div class="close-project-label" id="value-charged">Valores Realizados Cobrados</div>
            <div class="close-project-label" id="hours-unit">Horas por Unidad</div>
            <div class="close-project-info">
                <span id="project-name">
                    <div for="project-name">Nombre del Proyecto</div>
                    {{ project.name }}
                </span>
                <span id="client-name">
                    <div for="client-name">Nombre del Cliente</div>
                    {{ project.client }}
                </span>
                <span id="partner-name">
                    <div for="partner-name">Nombre del Socio a Cargo</div>
                    {{ project.partner }}
                </span>
            </div>
            <div class="close-project-value">
                <span id="value-hours">
                    <div for="value-hours">Horas Estimadas</div>
                    {{ project.hoursEstimated }}
                </span>
                <span id="value-estimated-hours">
                    <div for="value-hours">Honorarios Estimados </div>
                    {{ project.valueEstimated }}
                </span>
                <span id="value-rate">
                    <div for="value-rate">Tasa Promedio </div>
                    {{ project.average }}
                </span>
                <!-- <div for="value-expense">Gastos Estimados</div>
                <input type="text" class="form-control" id="value-expense" placeholder="Horas Estimadas"> -->
            </div>

            <div class="close-project-billing">
                <div for="hours-billing">Horas Adicionales</div>
                <input type="text" v-model="project.additionalHour" class="form-control" id="hours-billing"
                    placeholder="Horas">

                <div for="hours-additional-billing">Honorarios Adicionales</div>
                <input type="text" v-model="project.hoursExtra" class="form-control" id="hours-additional-billing"
                    placeholder="Horas Adicionales">
                <!-- <div for="value-billing">Gastos Adicionales</div>
                <input type="text" class="form-control" id="value-billing" placeholder="Gastos"> -->
            </div>
            <div class="close-project-executed">
                <div for="hours-executed">Horas Reales</div>
                <input type="text" v-model="project.hoursReal" class="form-control" id="hours-executed" placeholder="Horas">

                <div for="rate-executed">Tasa Promedio Final</div>
                <input type="text" class="form-control" id="rate-executed" placeholder="Horas Adicionales">

                <!-- <div for="expense-executed">Gastos Reales</div>
                <input type="text" class="form-control" id="expense-executed" placeholder="Gastos"> -->
            </div>

            <div class="close-project-general table-responsive">
                <div class="close-project-value-collected table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="col-sm-4 col-lg-4"></th>
                                <th class="col-sm-4 col-lg-4"> Horarios Reales USD</th>
                                <!-- <th class="col-sm-4 col-lg-4">Gastos USD</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="col-sm-4 col-lg-4"></td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Horas”>
                                </td>
                                <!-- <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Gastos”>
                                </td> -->
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="col-sm-4 col-lg-4"> Total A </td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control”
                                        placeholder=“Horas-Totales”>
                                </td>
                                <!-- <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control”
                                        placeholder=“Gastos-Totales”>
                                </td> -->
                            </tr>
                        </tfoot>
                    </table>
                    <div class="close-project-label">Facturación Adicional</div>
                    <table class="table">
                        <thead>
                            <tr>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="col-sm-4 col-lg-4"></td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Horas”>
                                </td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Horas”>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="col-sm-4 col-lg-4"> Total B</td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Total”>
                                </td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Total”>
                                </td>

                            </tr>
                            <tr>
                                <td class="col-sm-4 col-lg-4"> Total A+B</td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Total”>
                                </td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Total”>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="close-project-additional table-responsive">

                </div>
                <div class="close-project-unit-hours table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Unidad</th>
                                <th class="col-sm-2 col-lg-2">Estimadas</th>
                                <th class="col-sm-2 col-lg-2">Reales</th>
                                <th class="col-sm-2 col-lg-2">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Auditoria Financiera</th>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Estimadas” size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Reales”
                                        size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Diferencia” size="14">
                                </td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Business and Process Consulting</th>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Estimadas” size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Reales”
                                        size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Diferencia” size="14">
                                </td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Tributario</th>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Estimadas” size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Reales”
                                        size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Diferencia” size="14">
                                </td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">División Legal Corporativa</th>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Estimadas” size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Reales”
                                        size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Diferencia” size="14">
                                </td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">División de Tecnología</th>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Estimadas” size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Reales”
                                        size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Diferencia” size="14">
                                </td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Outsourcing</th>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Estimadas” size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Reales”
                                        size="14"></td>
                                <td class="col-sm-2 col-lg-2"><input type=“text” class=“form-control”
                                        placeholder=“Diferencia” size="14">
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="col-sm-1 col-lg-1"> Totales </th>
                                <td class="col-sm-1 col-lg-1"><input type=“text” class=“form-control”
                                        placeholder=“Estimadas-Totales” size="14"></td>
                                <td class="col-sm-1 col-lg-1"><input type=“text” class=“form-control”
                                        placeholder=“Reales-Totales” size="14">
                                </td>
                                <td class="col-sm-1 col-lg-1"><input type=“text” class=“form-control”
                                        placeholder=“Diferencia-Total” size="14"></td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="close-project-label">Facturación Adicional al Presupuesto Base</div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="col-sm-2 col-lg-2"></th>
                                <th class="col-sm-2 col-lg-2">Horas</th>
                                <th class="col-sm-2 col-lg-2">Tasa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Exceso de Horas por Facturar</th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Horas”
                                        size="14"></th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Tasa”
                                        size="14"></th>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Valor Monetario a recuperar HP exceso de horas</th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Horas”
                                        size="14"></th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Tasa”
                                        size="14"></th>
                            </tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                    <div class="close-project-label">Valor Monetario Facturado o Pendiente en USD</div>
                    <table class="table">
                        <thead>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Valor monetario sin facturar(con base propuesta original)
                                </th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Horas”
                                        size="14"></th>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Valor Monetario adicional facturado</th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Horas”
                                        size="14"></th>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Valor monetario(defecit para cubrir exceso de horas) o
                                    excedente
                                </th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Horas”
                                        size="14"></th>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Valor Monetario (beneficiario) en gastos por recuperar
                                </th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Horas”
                                        size="14"></th>
                            </tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                    <div class="close-project-label">Facturación Adicional al Presupuesto Base</div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="col-sm-2 col-lg-2"></th>
                                <th class="col-sm-2 col-lg-2">Horas</th>
                                <th class="col-sm-2 col-lg-2">Tasa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Horas Cargadas al CARENT(Maestro de Trabajo)</th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Horas”
                                        size="14"></th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Tasa”
                                        size="14"></th>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Horas Reales Cargadas al CARENT</th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Horas”
                                        size="14"></th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Tasa”
                                        size="14"></th>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2"> Porcentaje de Ejecucion</th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Horas”
                                        size="14"></th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Tasa”
                                        size="14"></th>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2"> (Defecit) Eficiencia de Horas </th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Horas”
                                        size="14"></th>
                                <th class="col-sm-2 col-lg-2"><input type=“text” class=“form-control” placeholder=“Tasa”
                                        size="14"></th>
                            </tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>

                </div>
                <button type="button" class="close-project-button">Enviar</button>
            </div>

        </div>
    </div>
</template>

<script>

import DropdownSelect from "@/Components/DropdownSelect.vue";

export default {

    props: {
        loadInitial: Object, //almacena el objeto para cargar la informacion del proyectos antes de que se cargue el componente
        active: Boolean //Controla la activacion o desactivacion del componente
    },
    data() {
        return {
            dataSelect: [],
            project:
            {
                'name': '',
                'client': '',
                'partner': '',

                'hoursEstimated': 0,
                'valueEstimated': 0,
                'average': 0,

                'additionalHour': 0,
                'hoursExtra': 0,
                'hoursReal': 0,
            },
            closeproject: 'NIRVANA',
            projectclose: true,
        }
    },

    emits: [
        'load-view'
    ],

    //La informacion debe cargar apenas abre el componente
    //Metodo(variable)
    methods: {

        getInfoProject(infoProjectMethods) {
            //Axios
            axios.post('close-projects/get-data-close-project-exp', { id: infoProjectMethods })
                .then(request => {
                })
                .catch(error => {
                    console.error(error);
                });
        },
    },
    computed: {},
    watch: {
        loadInitial() {
            console.log(this.loadInitial)
            this.project.name = this.loadInitial.project.project_description
            this.project.client = this.loadInitial.project.bussiness_name
            this.project.partner = this.loadInitial.project.partner_name
            this.project.valueEstimated = parseFloat(this.loadInitial.project.project_value)
            this.project.average = parseFloat(this.loadInitial.project.average_rate)
            //Sumatoria de las horas totales
            this.loadInitial.departments.forEach(department => {
                this.project.hoursEstimated = parseInt(department.hours_assigned) + parseInt(this.project.hoursEstimated)
            })
            this.loadInitial.additionalHours.forEach(department => {
                this.project.additionalHour = parseInt(department.additional_hour) + parseInt(this.project.additionalHour)
            })
            this.loadInitial.additionalValue.forEach(department => {
                this.project.hoursExtra = parseFloat(department.aditional_project_value) + parseFloat(this.project.hoursExtra)
            })
            this.loadInitial.projectsHours.forEach(department => {
                this.project.hoursReal = parseFloat(department.total_hours) + parseFloat(this.project.hoursReal)
            })
        },
        active(newActive) {
            if (newActive) this.$emit('load-view')
        }
    },
    components: { DropdownSelect },
}
</script>


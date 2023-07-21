<template>
    <div>
        <h2 class="close-title"> Cierre de Proyecto </h2>
        <h3 class="close-subtitle"> Aspectos Financieros y/o Inversión </h3>
        <div class="close-project">
            <div class="close-project-date">
                <span for="close-project-date-time"> Fecha de cierre </span>
                <input type="date" class="form-control">
            </div>
            <div class="close-project-label" id="data-info">Datos</div>
            <div class="close-project-label" id="value-proposed">Valores Propuesta</div>
            <div class="close-project-label" id="additional-billing">Facturación Adicional</div>
            <div class="close-project-label" id="final-value-executed">Valores Finales Ejecutados</div>
            <div class="close-project-label" id="hours-unit">Horas por Unidad</div>
            <!-- <div class="close-project-label" id="management-comments">Comentarios de la Gerencia</div> -->
            <div class="close-project-info">
                <!-- Label  -->
                <div for="project-name">Nombre del Proyecto</div>
                <div for="client-name">Nombre del Cliente</div>
                <div for="partner-name">Nombre del Socio a Cargo</div>
                <!-- Contenido del label -->
                <span id="project-name">{{ project.name }}</span>
                <span id="client-name">{{ project.client }}</span>
                <span id="partner-name">{{ project.partner }}</span>
            </div>
            <div class="close-project-value">
                <!-- Label -->
                <div for="value-hours">Horas Estimadas</div>
                <div for="value-estimated-hours">Honorarios Estimados </div>
                <div for="value-rate">Tasa Promedio </div>
                <!-- Contenido del label -->
                <span id="value-hours">{{ project.hoursEstimated }}</span>
                <span id="value-estimated-hours">{{ project.valueEstimated }}</span>
                <span id="value-rate">{{ project.average }}</span>
            </div>
            <div class="close-project-billing">
                <!-- Label -->
                <div for="hours-billing"> Horas Adicionales </div>
                <div for="hours-additional-billing"> Honorarios Adicionales </div>
                <!-- Contenido del label -->
                <span id="hours-billing">{{ project.additionalHour }}</span>
                <span id="hours-additional-billing">{{ project.valueExtra }}</span>
            </div>
            <div class="close-project-executed">
                 <!-- Label -->
                <div for="hours-executed"> Horas Reales </div>
                <div for="rate-executed"> Tasa Promedio Final </div>
                <!-- Contenido del label -->
                <span id="hours-executed">{{project.hoursReal}}</span>
                <span id="rate-executed">{{project.rateExecuted}}</span>
            </div>
            <div class="close-project-general table-responsive">
                <div class="close-project-value-collected table-responsive">
                    <div class="close-project-label">Valores Realizados Cobrados</div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="col-sm-4 col-lg-4"></th>
                                <th class="col-sm-4 col-lg-4"> Horarios Reales USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="col-sm-4 col-lg-4"></td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Horas”>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="col-sm-4 col-lg-4"> Total A </td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control”
                                        placeholder=“Horas-Totales”>
                                </td>
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
                            <tr v-for="(department, position) in project.tableUnitHours" :key="position">
                                <th class="col-sm-2 col-lg-2" align="center">{{department.department_name}}</th>
                                <td class="col-sm-2 col-lg-2" align="center">{{department.hours_assigned}}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{getRealHours(department.department_id)}}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{department.hours_assigned-getRealHours(department.department_id)}}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="col-sm-1 col-lg-1" align="center"> Totales </th>
                                <td class="col-sm-1 col-lg-1" align="center">{{totalHoursAssigned}}</td>
                                <td class="col-sm-1 col-lg-1" align="center">{{totalRealHours}}</td>
                                <td class="col-sm-1 col-lg-1" align="center">{{totalRealHours-totalHoursAssigned}}</td>
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
                                <td class="col-sm-2 col-lg-2" align="center">{{(totalHoursAssigned-totalRealHours)*-1}}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{project.average}}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Valor Monetario a recuperar HP exceso de horas</th>
                                <td class="col-sm-2 col-lg-2" align="center">
                                    {{((totalHoursAssigned-totalRealHours)*-1)*project.average}}</td>
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
                                <td class="col-sm-2 col-lg-2" align="center">{{project.hoursEstimated}}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{project.average}}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Horas Reales Cargadas al CARENT</th>
                                <td class="col-sm-2 col-lg-2" align="center">{{project.hoursReal}}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{project.rateExecuted}}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2"> Porcentaje de Ejecucion</th>
                                <td class="col-sm-2 col-lg-2" align="center">{{((project.hoursReal/project.hoursEstimated) / 100)}}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{project.rateExecuted === 0 ? 0 :
                                (project.average / project.rateExecuted) / 100}}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2"> (Defecit) Eficiencia de Horas </th>
                                <td class="col-sm-2 col-lg-2" align="center">{{project.hoursEstimated-project.hoursReal}}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>

                </div>
                <div class="close-project-text">
                <div for="first-comment"> 1 Si este Contrato posee Finanza, generar las acciones con el Área....
                <textarea v-model="message" rows="2" cols="120"></textarea>

                <button type="button" class="close-project-button">Enviar</button>
            </div>
            </div>
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
                'valueExtra': 0,
                'hoursReal': 0,
                'rateExecuted' : 0,
                'tableUnitHours' : [],
                'tableRealHours' : [],
                'message': '',
                // 'difference' : [],
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
        /**
         * obtener las horas reales por departamentos
         * @param {int} departmentId captura el id del departamento
         */

        getRealHours(departmentId){
            const key=this.project.tableRealHours.map(returnDepartment => {
                return returnDepartment.department_id
            }).indexOf(departmentId)
            return key===-1 ? 0 : parseFloat(this.project.tableRealHours[key].total_hours)
            //key es el cursor que busca en el array vacio de project.tableRealHours que asigne en la data
            //mapea en el department_id que es la columna y para eso retorno un nuevo valor llamado returnDepartment
            //indexOf ubica la posicion especifica del array ya que no hay forma de saberlo
            //se usa operador ternario para en una sola linea hacer una condicional y asi no usar for
            //key pregunta si es ===-1 es negativo ya que indexOf arroja un -1 cuando no encuenta una posicion
            //sino es 0 entonces realiza la busqueda que se le implemente
        }
    },
    //se utilizan para calcular valores reactivos que dependen de otros datos reactivos en tu componente.
    // Las propiedades calculadas se recalculan automáticamente cuando cambian los datos de los que dependen.
    // Esto significa que si cambia algún valor en el arreglo project.tableUnitHours, la propiedad calculada
    // totalHoursAssigned se actualizará automáticamente para reflejar el nuevo valor total.
    computed: {
        totalHoursAssigned() {
        return this.project.tableUnitHours.reduce((total, department) => total + department.hours_assigned, 0);
    },
        totalRealHours() {
        return this.project.tableRealHours.reduce((total, department) => total + parseFloat(department.total_hours), 0);
    }
    },


    watch: {
        loadInitial() {
            console.log(this.loadInitial)
            this.project.name = this.loadInitial.project.project_description
            this.project.client = this.loadInitial.project.bussiness_name
            this.project.partner = this.loadInitial.project.partner_name
            //horas estimadas de valores propuesta
            this.project.valueEstimated = parseFloat(this.loadInitial.project.project_value)
             //Sumatoria de las honorarios estimadas
            this.loadInitial.departments.forEach(department => {
            this.project.hoursEstimated = parseInt(department.hours_assigned) + parseInt(this.project.hoursEstimated)})
            //Tasa promedio de valores propuesta
            this.project.average = parseFloat(this.loadInitial.project.average_rate)

            //Sumatoria de horas adicionales
            this.loadInitial.additionalHours.forEach(department => {
                this.project.additionalHour = parseInt(department.additional_hour) + parseInt(this.project.additionalHour)
            })
            //Sumatoria de honorarios adicionales
            this.loadInitial.additionalValue.forEach(department => {
                this.project.valueExtra = parseFloat(department.aditional_project_value) + parseFloat(this.project.valueExtra)
            })
            //Sumatorio de horas reales
            this.loadInitial.projectsHours.forEach(department => {
                this.project.hoursReal = parseFloat(department.total_hours) + parseFloat(this.project.hoursReal)
            })
            //Tasa promedio final colocar dos decimales
            if (this.project.hoursReal === 0) {this.project.rateExecuted = 0;}
            else {
            this.project.rateExecuted = ((this.project.valueEstimated + this.project.valueExtra) / this.project.hoursReal).toFixed(3);
            }
            //Horas estimadas de horas por unidad
            this.project.tableUnitHours = this.loadInitial.departments
            //Horas reales de horas por unidad
            this.project.tableRealHours = this.loadInitial.projectsHours

        },

        //Si horas reales es =0 la tasa promedio debe ser 0
        active(newActive) {
            if (newActive) this.$emit('load-view')
        }
    },
    components: { DropdownSelect },
}
</script>


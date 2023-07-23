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
            <div class="close-project-label" id="value-realized-charged">Valores realizados Cobrados</div>
            <!-- Datos -->
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
            <!-- valores propuesta -->
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
            <!-- Facturacion Adicional -->
            <div class="close-project-billing">
                <!-- Label -->
                <div for="hours-billing"> Horas Adicionales </div>
                <div for="hours-additional-billing"> Honorarios Adicionales </div>
                <!-- Contenido del label -->
                <span id="hours-billing">{{ project.additionalHour }}</span>
                <span id="hours-additional-billing">{{ project.valueExtra }}</span>
            </div>
            <!-- Valores finales ejecutados -->
            <div class="close-project-executed">
                <!-- Label -->
                <div for="hours-executed"> Horas Reales </div>
                <div for="rate-executed"> Tasa Promedio Final </div>
                <!-- Contenido del label -->
                <span id="hours-executed">{{ project.hoursReal }}</span>
                <span id="rate-executed">{{ project.rateExecuted }}</span>
            </div>
            <!-- Valores realizados cobrados -->
            <div class="close-project-realized-values-charged">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="col-sm-4 col-lg-4" colspan="2"> Horarios Reales USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="billing in project.billingValue">
                                <td class="col-sm-4 col-lg-4" align="center"></td>
                                <td class="col-sm-4 col-lg-4" align="center">{{ billing }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="col-sm-4 col-lg-4"> Total A </th>
                                <td class="col-sm-4 col-lg-4" align="center">{{ totalRealFees }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    <!-- Facturacion adicional -->
                    <div class="close-project-label">Facturación Adicional</div>
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="col-sm-4 col-lg-4" align="center"></td>
                                <td class="col-sm-4 col-lg-4" align="center">ABDC</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="col-sm-4 col-lg-4">Total B</th>
                                <td class="col-sm-4 col-lg-4" align="center">EFGH</td>

                            </tr>
                            <tr>
                                <th class="col-sm-4 col-lg-4"> Total A+B</th>
                                <td class="col-sm-4 col-lg-4" align="center">Total</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <!-- Horas Por unidad -->
            <div class="close-project-hours-per-unit">
                <div class="table-responsive">
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
                            <!-- Divisiones diversas -->
                            <tr v-for="(department, position) in project.tableUnitHours" :key="position">
                                <th class="col-sm-2 col-lg-2" align="center">{{ department.department_name }}</th>
                                <td class="col-sm-2 col-lg-2" align="center">{{ department.hours_assigned }}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{ getRealHours(department.department_id) }}
                                </td>
                                <td class="col-sm-2 col-lg-2" align="center">
                                    {{ (department.hours_assigned - getRealHours(department.department_id)) * -1 }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="col-sm-1 col-lg-1" align="center"> Totales </th>
                                <td class="col-sm-1 col-lg-1" align="center">{{ totalHoursAssigned }}</td>
                                <td class="col-sm-1 col-lg-1" align="center">{{ totalRealHours }}</td>
                                <td class="col-sm-1 col-lg-1" align="center">{{ (totalHoursAssigned - totalRealHours) * -1
                                }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <!-- Facturacion adicional al presupuesto base -->
                    <div class="close-project-label"> Facturación Adicional al Presupuesto Base</div>
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
                                <td class="col-sm-2 col-lg-2" align="center">{{ (totalHoursAssigned - totalRealHours) * -1
                                }}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{ project.average }}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Valor Monetario a recuperar HP exceso de horas</th>
                                <td class="col-sm-2 col-lg-2" align="center">
                                    {{ ((totalHoursAssigned - totalRealHours) * -1) * project.average }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                    <!-- Valor monetario facturado o pendiente en USD -->
                    <div class="close-project-label"> Valor monetario facturado o pendiente en USD</div>
                    <table class="table">
                        <thead>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="col-sm-6 col-lg-6">Valor monetario sin facturar(con base propuesta original)
                                </th>
                                <td class="col-sm-6 col-lg-6" align="center">Valor</td>
                            </tr>
                            <tr>
                                <th class="col-sm-6 col-lg-6">Valor Monetario adicional facturado</th>
                                <td class="col-sm-6 col-lg-6" align="center">Valor</td>
                            </tr>
                            <tr>
                                <th class="col-sm-6 col-lg-6">Valor monetario(deficit para cubrir exceso de horas) o
                                    excedente
                                </th>
                                <td class="col-sm-6 col-lg-6" align="center">Valor</td>
                            </tr>
                            <tr>
                                <th class="col-sm-6 col-lg-6">Valor Monetario (beneficiario) en gastos por recuperar
                                </th>
                                <td class="col-sm-6 col-lg-6" align="center">Valor</td>
                            </tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                    <!-- Facturación Real del Presupuesto Base -->
                    <div class="close-project-label"> Facturación Real del Presupuesto Base</div>
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
                                <td class="col-sm-2 col-lg-2" align="center">{{ project.hoursEstimated }}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{ project.average }}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Horas Reales Cargadas al CARENT</th>
                                <td class="col-sm-2 col-lg-2" align="center">{{ project.hoursReal }}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{ project.rateExecuted }}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2"> Porcentaje de Ejecucion</th>
                                <td class="col-sm-2 col-lg-2" align="center">{{ (((project.hoursReal /
                                    project.hoursEstimated) *
                                    100)).toFixed(3) }}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{ project.rateExecuted === 0 ? 0 :
                                    (project.average / project.rateExecuted) / 100 }}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2"> (Defecit) Eficiencia de Horas </th>
                                <td class="col-sm-2 col-lg-2" align="center">{{ project.hoursEstimated - project.hoursReal
                                }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>
            </div>
            <!-- Comentarios -->
            <div class="close-project-text-area">
                <div class="close-project-label" id="management-comments">Comentarios de la Gerencia</div>
                <div for="first-comment"> 1-Si este Contrato posee Fianza, generar las acciones con el Área Legal de La
                    Firma para su finiquito. Detallar:
                </div>
                <textarea v-model="message.first" rows="2" cols="120"></textarea>
                <div for="second-comment"> 2-Si la ejecución del Proyecto en cuato a: Horas, Honorarios y Gastos,
                    estuvieron por encima de lo planificado. Cuáles fueron los motivos y las acciones tomadas para:
                </div>
                <textarea v-model="message.second" rows="2" cols="120"></textarea>
                <div for="third-comment"> 2.2-Recuperar la inversión en exceso ante el Cliente si fuese su
                    responsabilidad.
                    Detallar: </div>
                <textarea v-model="message.third" rows="2" cols="120"></textarea>
                <div for="fourth-comment"> 2.3-Si fuese responsabilidad del Equipo en la Planificación y/o ejecución
                    del Trabajo. Detallar:
                </div>
                <textarea v-model="message.fourth" rows="2" cols="120"></textarea>
                <div for="fifth-comment"> 2.4-Otras acciones. Detallar:</div>
                <textarea v-model="message.fifth" rows="2" cols="120"></textarea>
                <div for="sixth-comment"> 3-Si hay Facturas pendientes, mayores a sesenta (60) días.
                    Explique el porque de esa situación, el plan de Recuperación en USD y posible fecha en el Corto
                    Plazo.
                    Detallar:
                </div>
                <textarea v-model="six" rows="2" cols="120"></textarea>
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
                'rateExecuted': 0,
                'tableUnitHours': [],
                'tableRealHours': [],
                'message': '',
                'billingValue': [],
                // 'difference' : [],
            },
            message:
            {
                'first': '',
                'second': '',
                'third': '',
                'fourth': '',
                'fifth': '',
                'six': '',
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

        getRealHours(departmentId) {
            const key = this.project.tableRealHours.map(returnDepartment => {
                return returnDepartment.department_id
            }).indexOf(departmentId)
            return key === -1 ? 0 : parseFloat(this.project.tableRealHours[key].total_hours)
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
        },
        totalRealFees() {
            return this.project.billingValue.reduce((total, billingValue) => total + parseFloat(billingValue), 0);
        }
    },


    watch: {
        loadInitial() {
            console.log(this.loadInitial)
            //Nommbre del proyecto
            this.project.name = this.loadInitial.project.project_description
            //Nommbre del cliente
            this.project.client = this.loadInitial.project.bussiness_name
            //Nommbre del socio a cargo
            this.project.partner = this.loadInitial.project.partner_name
            //horas estimadas de valores propuesta
            this.project.valueEstimated = parseFloat(this.loadInitial.project.project_value)
            //Sumatoria de las honorarios estimadas
            this.loadInitial.departments.forEach(department => {
                this.project.hoursEstimated = parseInt(department.hours_assigned) + parseInt(this.project.hoursEstimated)
            })
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
            if (this.project.hoursReal === 0) { this.project.rateExecuted = 0; }
            else {
                this.project.rateExecuted = ((this.project.valueEstimated + this.project.valueExtra) / this.project.hoursReal).toFixed(3);
            }
            //Horas estimadas de horas por unidad
            this.project.tableUnitHours = this.loadInitial.departments
            //Horas reales de horas por unidad
            this.project.tableRealHours = this.loadInitial.projectsHours
            //Honorarios reales USD
            //Push ingresa un valor en la ultima fila del array
            //el valor de arriba es la propiedad que aparece en el objeto del controlador, esta en el console.log en este caso es billings
            //valor de abajo es el nombre del valor que le asigno a data
            this.loadInitial.billings.forEach(department => {
                this.project.billingValue.push(parseFloat(department.billing_value))
            })

        },

        //Si horas reales es =0 la tasa promedio debe ser 0
        active(newActive) {
            if (newActive) this.$emit('load-view')
        }
    },
    components: { DropdownSelect },
}
</script>


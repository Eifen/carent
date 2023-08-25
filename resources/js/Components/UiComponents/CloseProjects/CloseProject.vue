<template>
    <div>
        <h2 class="close-title"> Cierre de Proyecto </h2>
        <h3 class="close-subtitle"> Aspectos Financieros y/o Inversión </h3>
        <div class="close-project">
            <!-- Boton de regresar -->
            <div class="buttonCRUD" id="button-back" @click="$emit('return')">Regresar</div>
            <div class="close-project-date">
                <span for="close-project-date-time"> Fecha de cierre </span>
                <div class="input-group mb-3">
                    <input disabled type="text" placeholder="DD-MM-AAAA" class="form-control" v-model="project.dateClose"
                        aria-describedby="basic-addon1">
                    <span class="input-group-text" id="basic-addon1">
                        <calendar v-if="this.loadInitial.closureProject === null" @to-input="formatDate">
                        </calendar>
                    </span>
                </div>
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
                <span id="value-estimated-hours">{{ formatNumber(project.valueEstimated) + project.currency_symbols
                }}</span>
                <span id="value-rate">{{ formatNumber(project.average) }}</span>
            </div>
            <!-- Facturacion Adicional -->
            <div class="close-project-billing">
                <!-- Label -->
                <div for="hours-billing"> Horas Adicionales </div>
                <div for="hours-additional-billing"> Honorarios Adicionales </div>
                <!-- Contenido del label -->
                <span id="hours-billing">{{ project.additionalHour }}</span>
                <span id="hours-additional-billing">{{ formatNumber(project.valueExtra) + project.currency_symbols }}</span>
            </div>
            <!-- Valores finales ejecutados -->
            <div class="close-project-executed">
                <!-- Label -->
                <div for="hours-executed"> Horas Reales </div>
                <div for="rate-executed"> Tasa Promedio Final </div>
                <!-- Contenido del label -->
                <span id="hours-executed">{{ project.hoursReal }}</span>
                <!-- //Tasa promedio final = total A+B/horas reales  -->
                <span id="rate-executed">{{ (formatNumber((totalRealFees + totalAditionalBilling) /
                    project.hoursReal))
                }}</span>
            </div>
            <!-- Valores realizados cobrados -->
            <div class="close-project-realized-values-charged">
                <div class="table-responsive">
                    <!-- Honorarios Reales -->
                    <div class="table-royal-fees" id="table-royal-fees">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="col-sm-4 col-lg-4" colspan="2"> Honorarios Reales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="billing in project.billingValue">
                                    <td class="col-sm-4 col-lg-4" align="center">{{ billing.description }}</td>
                                    <td class="col-sm-4 col-lg-4" align="center">{{ formatNumber(billing.value) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="col-sm-4 col-lg-4"> Total A </th>
                                    <td class="col-sm-4 col-lg-4" align="center">{{ formatNumber(totalRealFees) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- Facturacion adicional -->
                    <div class="close-project-label">Facturación Adicional</div>
                    <table class="table">
                        <tbody>
                            <tr v-for="billingAditional in project.billingAditionalValue">
                                <td class="col-sm-4 col-lg-4" align="center">{{ billingAditional.description }}</td>
                                <td class="col-sm-4 col-lg-4" align="center">{{ formatNumber(billingAditional.value) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="col-sm-4 col-lg-4">Total B</th>
                                <td class="col-sm-4 col-lg-4" align="center">{{ formatNumber(totalAditionalBilling) }}</td>

                            </tr>
                            <tr>
                                <th class="col-sm-4 col-lg-4"> Total A+B</th>
                                <td class="col-sm-4 col-lg-4" align="center">{{ formatNumber(totalRealFees +
                                    totalAditionalBilling) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <!-- Horas Por unidad -->
            <div class="close-project-hours-per-unit">
                <div class="table-responsive">
                    <div class="prueba1" id="hours-unit-divisions">
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
                                    <td class="col-sm-2 col-lg-2" align="center">{{ getRealHours(department.department_id)
                                    }}
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
                                    <td class="col-sm-1 col-lg-1" align="center">{{ (totalHoursAssigned - totalRealHours) *
                                        -1
                                    }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
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
                                    {{ formatNumber(monetaryRecover(totalHoursAssigned, totalRealHours)) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                    <!-- Valor monetario facturado o pendiente en USD -->
                    <div class="close-project-label"> Valor monetario facturado o pendiente</div>
                    <table class="table">
                        <thead>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="col-sm-6 col-lg-6">Valor monetario sin facturar(con base propuesta original)
                                </th>
                                <td class="col-sm-6 col-lg-6" align="center">{{ formatNumber(project.valueEstimated -
                                    totalRealFees) }}
                                </td>
                            </tr>
                            <tr>
                                <th class="col-sm-6 col-lg-6">Valor Monetario adicional facturado</th>
                                <td class="col-sm-6 col-lg-6" align="center">{{ formatNumber(totalAditionalBilling) }}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-6 col-lg-6">Valor monetario(deficit para cubrir exceso de horas) o
                                    excedente
                                </th>
                                <td class="col-sm-6 col-lg-6" align="center">{{
                                    formatNumber(monetaryRecover(totalHoursAssigned,
                                        totalRealHours) -
                                        totalAditionalBilling) }}
                                </td>
                                <!-- <td class="col-sm-6 col-lg-6" align="center">{{
                                    getValueDeficit(monetaryRecover(totalHoursAssigned, totalRealHours),
                                        totalAditionalBilling) }}</td> -->
                            </tr>
                            <!-- <tr>
                                <th class="col-sm-6 col-lg-6">Valor Monetario (beneficiario) en gastos por recuperar
                                </th>
                                <td class="col-sm-6 col-lg-6" align="center">Valor</td>
                            </tr> -->
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
                                <td class="col-sm-2 col-lg-2" align="center">{{ formatNumber(project.average) }}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2">Horas Reales Cargadas al CARENT</th>
                                <td class="col-sm-2 col-lg-2" align="center">{{ project.hoursReal }}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{
                                    formatNumber(totalRealFees / project.hoursReal) }}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2"> Porcentaje de Ejecución</th>
                                <td class="col-sm-2 col-lg-2" align="center">{{ formatNumber(((project.hoursReal /
                                    project.hoursEstimated))) + '%' }}</td>
                                <td class="col-sm-2 col-lg-2" align="center">{{ (totalRealFees / project.hoursReal) === 0 ?
                                    0 :
                                    formatNumber(((totalRealFees / project.hoursReal) / project.average)) + '%' }}</td>
                            </tr>
                            <tr>
                                <th class="col-sm-2 col-lg-2"> Exceso (Déficit) de Horas </th>
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
                <textarea :disabled="this.loadInitial.closureProject !== null" v-model="message.first" rows="2"
                    cols="120"></textarea>
                <div for="second-comment"> 2-Si la ejecución del Proyecto en cuato a: Horas, Honorarios y Gastos,
                    estuvieron por encima de lo planificado. Cuáles fueron los motivos y las acciones tomadas para:
                </div>
                <textarea :disabled="this.loadInitial.closureProject !== null" v-model="message.second" rows="2"
                    cols="120"></textarea>
                <div for="third-comment"> 2.2-Recuperar la inversión en exceso ante el Cliente si fuese su
                    responsabilidad.
                    Detallar: </div>
                <textarea :disabled="this.loadInitial.closureProject !== null" v-model="message.third" rows="2"
                    cols="120"></textarea>
                <div for="fourth-comment"> 2.3-Si fuese responsabilidad del Equipo en la Planificación y/o ejecución
                    del Trabajo. Detallar:
                </div>
                <textarea :disabled="this.loadInitial.closureProject !== null" v-model="message.fourth" rows="2"
                    cols="120"></textarea>
                <div for="fifth-comment"> 2.4-Otras acciones. Detallar:</div>
                <textarea :disabled="this.loadInitial.closureProject !== null" v-model="message.fifth" rows="2"
                    cols="120"></textarea>
                <!-- <div for="sixth-comment"> 3-Si hay Facturas pendientes, mayores a sesenta (60) días.
                    Explique el porque de esa situación, el plan de Recuperación en USD y posible fecha en el Corto
                    Plazo.
                    Detallar:
                </div>
                <textarea :disabled="this.loadInitial.closureProject !== null" v-model="message.sixth" rows="2"
                    cols="120"></textarea> -->
            </div>
            <div v-if="loadInitial.closureProject === null && validate.isValid && billingPay()" class="buttonCRUD"
                :class="viewButton ? 'disable' : ''" id="button-crud" @click="emitClose()">Cerrar
                proyecto</div>
            <!-- {{-- Control de errores del boton --}} -->
            <span class="form-ErrorInput" id="button-crud" v-else>
                No se puede cerrar el proyecto si las fechas y comentarios están vacios, o todas las facturas no están
                cobradas.
            </span>
        </div>
    </div>
</template>

<script>

import DropdownSelect from "@/Components/DropdownSelect.vue";
import Calendar from "@/Components/Calendar.vue";

export default {

    props: {
        loadInitial: Object, //almacena el objeto para cargar la informacion del proyectos antes de que se cargue el componente
        active: Boolean, //Controla la activacion o desactivacion del componente
        viewButton: Boolean //Controla el estado  del boton
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
                'currency_symbols': '',
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
                'billingAditionalValue': [],
                'dateClose': '',
                'monetaryReco': 0,
                'totalRealFees': 0,
                // 'difference' : [],
            },
            message:
            {
                'first': '',
                'second': '',
                'third': '',
                'fourth': '',
                'fifth': '',
            },
            projectclose: true,
            validate: {
                commentedValid: false,
                dateValid: false,
                isValid: false
            }
        }
    },

    emits: [
        'load-view',
        'return',
        'close-project'
    ],
    created() {
        //Si ya cargo el componente, llenamos la informacion
        if (this.active) {
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
            //Simbolo de honorarios estimados
            this.project.currency_symbols = this.loadInitial.project.currency_symbol
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
            //Horas estimadas de horas por unidad
            this.project.tableUnitHours = this.loadInitial.departments
            //Horas reales de horas por unidad
            this.project.tableRealHours = this.loadInitial.projectsHours
            //Honorarios reales USD
            //Push ingresa un valor en la ultima fila del array
            //el valor de arriba es la propiedad que aparece en el objeto del controlador, esta en el console.log en este caso es billings
            //valor de abajo es el nombre del valor que le asigno a data
            let countValues = this.project.valueEstimated + this.project.valueExtra
            this.loadInitial.billings.forEach(department => {
                //Valores realizados cobrados
                switch (true) {
                    case department.billing_concept_id === 1 || department.billing_concept_id === 2 || department.billing_concept_id === 4:
                        const getValue = department.billing_concept_id === 4 ? (parseFloat(department.billing_value) * (-1)) : parseFloat(department.billing_value)
                        countValues = countValues - getValue;
                        /**
                         * Evaluamos el monto total y lo restamos con la facturacion entrante del foreach
                         * Si el valor da negativo o 0 lo carga en facturacion adicionales
                         * Si el valor tiene una parte que excede el monto total y la otra no, los separa
                         * La parte que no excede la coloca en honorarios reales y la que excede en facturacion adicional
                         * Si el valor aun entra en el espectro del monto total lo coloca en honorarios reales
                         */
                        if (countValues < 0 && (countValues + getValue) <= 0) {
                            //Valor completo para montos mayores al total
                            this.project.billingAditionalValue.push({
                                "description": department.billing_concept_description,
                                "value": getValue
                            });
                        } else if (countValues < 0) {
                            //Valor completo
                            this.project.billingValue.push({
                                "description": department.billing_concept_description,
                                "value": (getValue + countValues) == 0 ? getValue : getValue + countValues
                            });
                            //Restante
                            this.project.billingAditionalValue.push({
                                "description": department.billing_concept_description,
                                "value": countValues * (-1)
                            });
                        } else {
                            this.project.billingValue.push({
                                "description": department.billing_concept_description,
                                "value": getValue
                            });
                        }
                        break;
                    //Gastos no presupuestados y otros gastos
                    case department.billing_concept_id !== 1 && department.billing_concept_id !== 2 && department.billing_concept_id !== 4:
                        this.project.billingAditionalValue.push({
                            "description": department.billing_concept_description,
                            "value": parseFloat(department.billing_value)
                        })
                        break;
                }
            })

            if (this.loadInitial.closureProject !== null) {
                this.project.dateClose = this.loadInitial.closureProject.close_date;
                this.message.first = this.loadInitial.closureProject.first_comment;
                this.message.second = this.loadInitial.closureProject.second_comment;
                this.message.third = this.loadInitial.closureProject.third_comment;
                this.message.fourth = this.loadInitial.closureProject.fourth_comment;
                this.message.fifth = this.loadInitial.closureProject.fifth_comment;
                this.message.sixth = this.loadInitial.closureProject.sixth_comment;
            }

        }
    },

    //La informacion debe cargar apenas abre el componente
    //Metodo(variable)
    methods: {
        formatDate(dateEmit) {
            this.project.dateClose = `${dateEmit.year}-${dateEmit.month}-${dateEmit.day}`
        },
        /**
         * Metodo que se encarga de revisar si el proyecto tiene todas sus facturas cobradas, caso positivo, activa el boton
         */
        billingPay() {
            let countPay = 0 //Contador que revisa si todas las facturas no anuladas tienen cobro activo
            let countBillings = 0 //Contador que revisa la cantidad de facturas sin concepto 4, es decir, nota de credito
            this.loadInitial.billings.forEach(department => {
                if (department.payment_date != null && department.billing_concept_id != 4 && department.billing_concept_id != 5) {
                    countPay = countPay + 1 //Sumamos el contador en caso de que tenga una factura distinta de null
                }
                //Sacamos la cantidad de facturas sin notas de credito
                if (department.billing_concept_id != 4 && department.billing_concept_id != 5) {
                    countBillings = countBillings + 1;
                }
            });
            //Retornamos en funcion de una comparacion entre las facturas cobradas y las registradas, deben ser iguales
            return countPay == countBillings ? true : false
        },
        /**
         * Formatea un numero a nomenclatura europea
         * @param {float} number Valor numerico a transformar
         */
        formatNumber(number) {
            return number.toLocaleString('de-DE')
        },
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
        },
        getUnbilled(valueEstimated, totalRealFees) {
            const difference = valueEstimated - totalRealFees
            return difference
        },
        getValueDeficit(monetaryReco, totalAditionalBilling) {
            const difference = monetaryReco - totalAditionalBilling
            return difference
        },
        monetaryRecover(totalHoursAssigned, totalRealHours) {
            return ((totalHoursAssigned - totalRealHours) * -1) * this.project.average
        },
        emitClose() {
            const params = {
                projectId: this.loadInitial.project.project_id,
                closeDate: this.project.dateClose,
                firstComment: this.message.first,
                secondComment: this.message.second,
                thirdComment: this.message.third,
                fourthComment: this.message.fourth,
                fifthComment: this.message.fifth,
                sixthComment: this.message.sixth,
            }

            this.$emit('close-project', params);
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
            return this.project.billingValue.reduce((total, billingValue) => total + parseFloat(billingValue.value), 0);
        },
        totalAditionalBilling() {
            return this.project.billingAditionalValue.reduce((total, billingValue) => total + parseFloat(billingValue.value), 0);
        }
    },
    watch: {
        message: {
            handler(newMessage) {
                let constMessage = 0
                for (const message in newMessage) {
                    if (newMessage[message].length != 0) constMessage++;
                }

                //Si el for es mayor al conteo lo activamos
                if (constMessage == Object.keys(newMessage).length) {
                    this.validate.commentedValid = true
                } else {
                    this.validate.commentedValid = false
                }
            },
            deep: true,
        },
        validate: {
            handler(newValidate) {
                let constValidate = 0
                for (const key in newValidate) {
                    if (key.toString() != "isValid" && newValidate[key] === true) {
                        constValidate++;
                    }
                }

                //Si el for es mayor al conteo lo activamos
                if (constValidate == Object.keys(newValidate).length - 1) {
                    this.validate.isValid = true
                } else {
                    this.validate.isValid = false
                }
            },
            deep: true,
        },
        project: {
            handler(newProject) {
                if (newProject.dateClose.length != 0 && newProject.dateClose.length == 10) {
                    this.validate.dateValid = true
                } else {
                    this.validate.dateValid = false
                }
            },
            deep: true
        }
    },
    components: { DropdownSelect, Calendar },

}
</script>


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
                <div class="close-project-border" for="project-name">Nombre del Proyecto</div>
                <input type="text" v-model="project.name" class="form-control" id="project-name"
                    placeholder="Escribe el nombre del proyecto">
                <div for="client-name">Nombre del Cliente</div>
                <input type="text" v-model="project.client" class="form-control" id="client-name"
                    placeholder="Escribe el nombre del cliente">

                <div for="partner-name">Nombre del Socio a Cargo</div>
                <input type="text" v-model="project.partner" class="form-control" id="partner-name"
                    placeholder="Escribe el nombre del socio a cargo">
            </div>
            <div class="close-project-value">
                <div for="division-name">División</div>
                <input type="text" class="form-control" id="division-name" placeholder="Escribe el nombre de la division">

                <div for="value-hours">Horas</div>
                <input type="text" class="form-control" id="value-hours" placeholder="Horas">

                <div for="value-estimated-hours">Horas Estimadas</div>
                <input type="text" class="form-control" id="value-estimated-hours" placeholder="Horas Estimadas">

                <div for="value-rate">Tasa Promedio</div>
                <input type="text" class="form-control" id="value-rate" placeholder="Horas Estimadas">

                <div for="value-expense">Gastos</div>
                <input type="text" class="form-control" id="value-expense" placeholder="Horas Estimadas">
            </div>
            <div class="close-project-billing">
                <div for="hours-billing">Horas</div>
                <input type="text" class="form-control" id="hours-billing" placeholder="Horas">

                <div for="hours-additional-billing">Horarios Adicionales</div>
                <input type="text" class="form-control" id="hours-additional-billing" placeholder="Horas Adicionales">

                <div for="value-billing">Gastos</div>
                <input type="text" class="form-control" id="value-billing" placeholder="Gastos">
            </div>
            <div class="close-project-executed">
                <div for="hours-executed">Horas</div>
                <input type="text" class="form-control" id="hours-executed" placeholder="Horas">

                <div for="rate-executed">Tasa Promedio</div>
                <input type="text" class="form-control" id="rate-executed" placeholder="Horas Adicionales">

                <div for="expense-executed">Gastos</div>
                <input type="text" class="form-control" id="expense-executed" placeholder="Gastos">
            </div>

            <div class="close-project-general table-responsive">
                <div class="close-project-value-collected table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="col-sm-4 col-lg-4"></th>
                                <th class="col-sm-4 col-lg-4"> Horarios Reales USD</th>
                                <th class="col-sm-4 col-lg-4">Gastos USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="col-sm-4 col-lg-4"></td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Horas”>
                                </td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Gastos”>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="col-sm-4 col-lg-4"> Total A </td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control”
                                        placeholder=“Horas-Totales”>
                                </td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control”
                                        placeholder=“Gastos-Totales”>
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
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Gastos”>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="col-sm-4 col-lg-4"> Total B</td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Horas”>
                                </td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Horas”>
                                </td>

                            </tr>
                            <tr>
                                <td class="col-sm-4 col-lg-4"> Total A+B</td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Gastos”>
                                </td>
                                <td class="col-sm-4 col-lg-4"><input type=“text” class=“form-control” placeholder=“Gastos”>
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
                                <th class="col-sm-2 col-lg-2">Valor monetario sin facturar(con base propuesta original)</th>
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
                                <th class="col-sm-2 col-lg-2">Valor Monetario (beneficiario) en gastos por recuperar</th>
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
    data() {
        return {
            dataSelect: [],
            project:
            {
                'name': '',
                'client': '',
                'partner': '',
            },
            closeproject: 'NIRVANA',
            projectclose: true,
        }
    },
    mounted() {

        axios.post('close-projects/get-data-close-project')
            .then(request => {
                // if (request.status !== 200) throw request;
                console.log(request)
            })
            .catch(error => {
                console.error(error);
            });
        this.getInfoProject(100)
    },
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
    watch: {},
    components: { DropdownSelect },
    props:

    {
        active: Boolean
    }
}
</script>


<template>
    <div class="billing-container">
        <div class="billing-container-button" @click="$emit('redirect')">Regresar</div>
        <div class="billing-container-title">Historico de facturación de un proyecto</div>
        <!-- Informacion del proyecto -->
        <div class="billing-container-project">
            <span>
                <font-awesome string-icon="fa-solid fa-folder"></font-awesome>
                <span class="info-title">Proyecto:</span>
                <span class="info-description" id="project-description">{{
                    infoProject.project.project_description.toLowerCase()
                }}</span>
            </span>
            <span>
                <font-awesome string-icon="fa-solid fa-calendar"></font-awesome>
                <span class="info-title">Fecha de contración/apertura:</span>
                <span class="info-description">{{ infoProject.project.hiring_date }}</span>
            </span>
            <span>
                <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                <span class="info-title">Socio:</span>
                <span class="info-description">{{ infoProject.project.partner_name.toLowerCase() }}</span>
            </span>
            <span>
                <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                <span class="info-title">Gerente:</span>
                <span class="info-description">{{ infoProject.project.manager_name.toLowerCase() }}</span>
            </span>
        </div>
        <!-- Informacion de lo facturado -->
        <div class="billing-container-billings">
            <div class="billing-container-billings-content" for="estimated">
                <div>Monto Contratado</div>
                <span>{{ infoProject.project.currency_symbol + getEstimated }}</span>
            </div>
            <div class="billing-container-billings-content" for="billing">
                <div>Monto Facturado</div>
                <span>{{ infoProject.project.currency_symbol + getTotalBilling() }}</span>
            </div>
            <div class="billing-container-billings-content" for="credit">
                <div>Monto Notas de Credito</div>
                <span>{{ infoProject.project.currency_symbol + getBilling(4) }}</span>
            </div>
            <div class="billing-container-billings-content" for="non-bill">
                <div>Monto gastos no facturables </div>
                <span>{{ infoProject.project.currency_symbol + getBilling(3) }}</span>
            </div>
            <div class="billing-container-billings-content" for="other-bill">
                <div>Otros gastos (comisiones)</div>
                <span>{{ infoProject.project.currency_symbol + getBilling(5) }}</span>
            </div>
            <div class="buttonCRUD" @click="$emit('billing-create')" for="create-bill">Agregar Factura</div>
        </div>
        <!-- Historico de facturacion -->
        <div class="table-responsive billing-container-table">
            <table class="table table-hover table-bordered">
                <thead class="billing-container-table-thead">
                    <tr>
                        <th scope="col" valign="middle">#</th>
                        <th scope="col" valign="middle">Nª Factura</th>
                        <th scope="col" valign="middle">Tipo Concepto</th>
                        <th scope="col" valign="middle">Movimiento</th>
                        <th scope="col" valign="middle">Monto</th>
                        <th scope="col" valign="middle">IVA</th>
                        <th scope="col" valign="middle">Retención IVA</th>
                        <th scope="col" valign="middle">Subtotal</th>
                        <th scope="col" valign="middle">ISLR</th>
                        <th scope="col" valign="middle">Neto a cobrar</th>
                        <th scope="col" valign="middle">Fecha Fact.</th>
                        <th scope="col" valign="middle"></th>
                        <th scope="col" valign="middle"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="infoProject.billings.length == 0">
                        <td scope="row" colspan="12">
                            <div class="badge bg-warning text-dark">Este proyecto no posee ninguna factura</div>
                        </td>
                    </tr>
                    <tr v-else v-for="(billing, cursor) in infoProject.billings" :key="billing.billing_id">
                        <td scope="row" align="center">{{ billing.billing_id }}</td>
                        <td scope="row" align="center">{{ billing.billing_number }}</td>
                        <td scope="row" align="center">{{
                            billing.billing_concept_description }}</td>
                        <td scope="row" align="center">
                            <span v-if="billing.type_concept_id == 1" class="badge text-bg-success">{{
                                billing.type_concept_description }}</span>
                            <span v-if="billing.type_concept_id == 2" class="badge text-bg-danger">{{
                                billing.type_concept_description }}</span>
                            <span v-if="billing.type_concept_id == 3" class="badge text-bg-warning">{{
                                billing.type_concept_description }}</span>
                        </td>
                        <td scope="row" align="center">{{ Number(parseFloat(billing.billing_value)).toLocaleString("de-DE")
                        }}</td>
                        <td scope="row" align="center">{{ billing.iva_description }}</td>
                        <td scope="row" align="center">{{ billing.retention_description }}</td>
                        <td scope="row" align="center">{{ Number(getSubTotal(billing.billing_id)).toLocaleString("de-DE") }}
                        </td>
                        <td scope="row" align="center">{{ billing.deduction_description }}</td>
                        <td scope="row" align="center">{{
                            Number(getTotal(getSubTotal(billing.billing_id), billing.billing_id)).toLocaleString("de-DE") }}
                        </td>
                        <td scope="row" align="center">{{ billing.billing_date }}</td>
                        <td scope="row" align="center">
                            <font-awesome class="aLink" string-icon="fa-solid fa-magnifying-glass"
                                @click="$emit('billing-update', billing)"></font-awesome>
                        </td>
                        <td scope="row" align="center">
                            <font-awesome class="aLink" string-icon="fa-solid fa-xmark"
                                @click="$emit('billing-delete', billing)"></font-awesome>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<script>
//Font Awesome
import FontAwesome from '@/Components/FontAwesome/FontAwesome.vue';
export default {
    emits: ["redirect", "billing-create", "billing-update", "billing-delete"],
    props: {
        infoProject: Object, //Informacion del proyecto y sus facturaciones realizadas
    },
    data() {
        return {}
    },
    created() { },
    methods: {
        /**
         * Devuelve un monto en el formato correcto
         * @param {int} typeConcept Captura el id del concepto de la factura, consultar la BD para mas informacion
         */
        getBilling(typeConcept) {
            const billing = this.infoProject.billings.reduce((total, billing) => {
                //Solo hacemos la suma si el concepto es Facturacion, caso contrario retornamos el total hasta el momento
                if (billing.billing_concept_id == typeConcept) return total + parseFloat(billing.billing_value);
                return total
            }, 0);
            return Number(billing).toLocaleString("de-DE");
        },
        /** Devuelve los montos no facturables en el formato correcto */
        getTotalBilling() {
            const totalBilling = this.infoProject.billings.reduce((total, billing) => {
                //Solo hacemos la suma si el concepto es Reembolsable y no rembolsable
                if ((billing.billing_concept_id == 1 || billing.billing_concept_id == 2) && billing.status_id != 2) return total + parseFloat(billing.billing_value);
                return total
            }, 0);
            return Number(totalBilling).toLocaleString("de-DE")
        },
        /**
         * Metodo que devuelve el calcula el subtotal en funcion del iva y de su retencion
         * @param {int} billingId capturamos el id de la factura
         */
        getSubTotal(billingId) {
            const getIndex = this.infoProject.billings.map(billing => billing.billing_id).indexOf(billingId);
            const billingsDTO = this.infoProject.billings //Almacenamos el objeto en una constante

            //Solo hacemos el subotal si encuentra un indice
            if (getIndex != -1) {
                const value = parseFloat(billingsDTO[getIndex].billing_value)
                const ivaValue = (value * parseFloat(billingsDTO[getIndex].iva_value)) / 100
                const retIvaValue = (ivaValue * parseFloat(billingsDTO[getIndex].retention_value)) / 100

                //Retornamos el subtotal
                return (value + ivaValue) - retIvaValue;
            }
            //Caso contrario devolvemos 0
            return 0
        },
        /**
         * Calcula y devuelve el total del monto a pagar en funcion del ISLR
         * @param {float} subTotal capturamos el subtotal del metodo anterior
         * @param {int} billingId capturamos el id de la factura
         */
        getTotal(subTotal, billingId) {
            const getIndex = this.infoProject.billings.map(billing => billing.billing_id).indexOf(billingId);
            const billingsDTO = this.infoProject.billings //Almacenamos el objeto en una constante

            //Solo hacemos el total si encuentra un indice
            if (getIndex != -1) {
                const value = parseFloat(billingsDTO[getIndex].billing_value)
                const islrValue = (value * parseFloat(billingsDTO[getIndex].deduction_value)) / 100

                //Retornamos el subtotal
                return subTotal - islrValue;
            }
            //Caso contrario devolvemos 0
            return 0
        }
    },
    computed: {
        /** Devuelve el monto estimado en el formato correcto*/
        getEstimated() { return Number(parseFloat(this.infoProject.project.project_value)).toLocaleString("de-DE") },
    },
    components: { FontAwesome }
}
</script>

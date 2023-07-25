<div id="billing-control">
    <loading :active="!isMounted"></loading>
    <billing-info v-if="isMounted" :info-project="proxyToJson(updateModel)" @redirect="redirectView('/billings')"
        @billing-create="prepareCreateBilling" @billing-update="prepareUpdateBilling">
    </billing-info>
    <!-- Modal -->
    <div class="modal fade" id="billingModal" tabindex="-1">
        <loading :active="!isMounted"></loading>
        <div class="modal-dialog modal-lg">
            <div class="modal-content" v-if="isMounted">
                <div class="modal-header">
                    <h5 class="modal-title" v-if="!isEdit">Estas creando una nueva factura</h5>
                    <h5 class="modal-title" v-else>
                        @{{ updateBillingInfo.billing_concept_description }}
                        Nº @{{ updateBillingInfo.billing_number }}
                        — Control @{{ updateBillingInfo.control_number }}</h5>
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">Formulario de crear/actualizar/eliminar una factura</div>
            </div>
        </div>
    </div>
</div>

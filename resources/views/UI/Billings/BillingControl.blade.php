<div id="billing-control">
    <loading :active="!isMounted"></loading>
    <billing-info v-if="isMounted" :info-project="proxyToJson(updateModel)" @redirect="redirectView('/billings')"
        @billing-create="prepareCreateBilling" @billing-update="prepareUpdateBilling">
    </billing-info>
    <!-- Modal -->
    <div class="modal fade" id="billingModal" tabindex="-1">
        <loading :active="!isMounted"></loading>
        <div class="modal-dialog modal-lg" v-if="isMounted">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" v-if="!isEdit">Estas creando una nueva factura</h5>
                    <h5 class="modal-title" v-else>Pene</h5>
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">Pene duro rico</div>
            </div>
        </div>
    </div>
</div>

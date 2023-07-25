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
                <div class="modal-body">
                    <form class="billing-form-container">
                        <fieldset class="billing-form-container-fieldset" for="billing-info">
                            <!-- Concepto de factura -->
                            <div class="mb-3">
                                <label for="Concept">Tipo de factura</label>
                                <div class="input-group">
                                    <select class="form-select" v-model="inputConcept" title="ConceptSelect">
                                        <option value=0 disabled selected>Seleccione el tipo de factura</option>
                                        <option v-for="(select, cursor) in billingConceptSelect" :key="cursor"
                                            :value="select.billing_concept_id">
                                            @{{ select.billing_concept_description }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- Numero de factura -->
                            <div class="mb-3" v-if="inputConcept != 0 && inputConcept != 5">
                                <label for="Billing">N° de Factura</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1">
                                        <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                                    </span>
                                    <input type="text" class="form-control" id="Billing"
                                        aria-describedby="basic-addon1" v-model="inputBilling"
                                        placeholder="Ejemplo: AABB0123C-5" />
                                </div>
                                <!-- Mensajes de error en Nombre-->
                                <div class="form-ErrorInput" v-if="errorMessage.billingError != ''">
                                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                                    @{{ errorMessage.billingError }}
                                </div>
                            </div>
                            <!-- Fecha de emision -->
                            <div class="mb-3" v-if="inputConcept != 0 && inputConcept != 5">
                                <label for="Date">Fecha de emisión</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon2">
                                        <calendar @to-input="formatDate"></calendar>
                                    </span>
                                    <input type="text" class="form-control" id="Date"
                                        aria-describedby="basic-addon2" v-model="inputDate"
                                        placeholder="Ejemplo: 1999-12-03" />
                                </div>
                                <!-- Mensajes de error en Nombre-->
                                <div class="form-ErrorInput" v-if="errorMessage.dateError != ''">
                                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                                    @{{ errorMessage.dateError }}
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

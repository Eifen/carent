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
                        {{-- =====================================
                            INFORMACION INICIAL DE LA FACTURA
                        ====================================== --}}
                        <fieldset class="billing-form-container-fieldset" for="billing-info">
                            <!-- Concepto de factura -->
                            <div class="mb-3">
                                <label for="Concept">Tipo de factura</label>
                                <div class="input-group">
                                    <select class="form-select" v-model="inputConcept" title="ConceptSelect">
                                        <option value=0 disabled selected>Seleccione el tipo de factura</option>
                                        <option v-for="(select, cursor) in billingConceptSelect" :key="cursor"
                                            :value="select.billing_concept_id"
                                            :disabled="select.billing_concept_id == 4 && billingNullInfo.length == 0">
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
                                        placeholder="Ejemplo: 1999-12-03" disabled />
                                </div>
                                <!-- Mensajes de error en Nombre-->
                                <div class="form-ErrorInput" v-if="errorMessage.dateError != ''">
                                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                                    @{{ errorMessage.dateError }}
                                </div>
                            </div>
                            <!-- Monto -->
                            <div class="mb-3" v-if="inputConcept != 0 && inputConcept != 4">
                                <label for="Value">Monto Factura</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon3">
                                        @{{ updateModel.project.currency_symbol }}
                                    </span>
                                    <input type="text" class="form-control" id="Value"
                                        aria-describedby="basic-addon3" v-model="inputValue" placeholder="3.000,00" />
                                </div>
                                <!-- Mensajes de error en Nombre-->
                                <div class="form-ErrorInput" v-if="errorMessage.valueError != ''">
                                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                                    @{{ errorMessage.valueError }}
                                </div>
                            </div>
                        </fieldset>
                        {{-- =====================================
                            IVA, RET, E ISLR
                        ====================================== --}}
                        <fieldset class="billing-form-container-fieldset" for="billing-tax">
                            <!-- Valor del iva -->
                            <div class="mb-3" v-if="inputConcept != 0 && inputConcept != 3 && inputConcept != 4">
                                <label for="Iva">IVA</label>
                                <div class="input-group">
                                    <select class="form-select" v-model="inputIva" title="IvaSelect">
                                        <option value=0 disabled selected>% IVA</option>
                                        <option v-for="(select, cursor) in billingIvaSelect" :key="cursor"
                                            :value="select.iva_value">
                                            @{{ select.iva_description }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- Valor de la retencion del IVA-->
                            <div class="mb-3" v-if="inputConcept != 0 && inputConcept != 3 && inputConcept != 4">
                                <label for="Ret">Retención IVA</label>
                                <div class="input-group">
                                    <select class="form-select" v-model="inputRetIva" title="RetSelect">
                                        <option value=0 disabled selected>% Retención</option>
                                        <option v-for="(select, cursor) in billingRetSelect" :key="cursor"
                                            :value="select.retention_value">
                                            @{{ select.retention_description }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <!-- Valor del ISLR-->
                            <div class="mb-3" v-if="inputConcept != 0 && inputConcept != 3 && inputConcept != 4">
                                <label for="Islr">ISLR</label>
                                <div class="input-group">
                                    <select class="form-select" v-model="inputIslr" title="IslrSelect">
                                        <option value=0 disabled selected>% Retención</option>
                                        <option v-for="(select, cursor) in billingIslrSelect" :key="cursor"
                                            :value="select.deduction_value">
                                            @{{ select.deduction_description }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </fieldset>
                        {{-- =====================================
                            SUBTOTAL Y TOTAL
                        ====================================== --}}
                        <fieldset class="billing-form-container-fieldset" for="billing-total">
                            <!-- Subtotal -->
                            <div class="mb-3" v-if="inputValue.length != 0">
                                <label for="subTotal">Subtotal Factura</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon4">
                                        @{{ updateModel.project.currency_symbol }}
                                    </span>
                                    <span class="input-group-text" id="basic-addon5">
                                        subTotal
                                    </span>
                                </div>
                            </div>
                            <!-- Total -->
                            <div class="mb-3" v-if="inputValue.length != 0">
                                <label for="Total">Neto a cobrar</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon4">
                                        @{{ updateModel.project.currency_symbol }}
                                    </span>
                                    <span class="input-group-text" id="basic-addon5">
                                        Total
                                    </span>
                                </div>
                            </div>
                        </fieldset>
                        {{-- =====================================
                            DESCRIPCION
                        ====================================== --}}
                        <fieldset class="billing-form-container-fieldset" for="billing-description">
                            <!-- Descripcion de la factura -->
                            <div class="mb-3" v-if="inputConcept != 0 && inputConcept != 5">
                                <label for="Description">Descripcion de la factura</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon6">
                                        <font-awesome string-icon="fa-solid fa-note-sticky"></font-awesome>
                                    </span>
                                    <textarea type="text" class="form-control" placeholder="Descripción por el cual se esta facturando"
                                        aria-describedby="basic-addon6" v-model="inputDescription"></textarea>
                                </div>
                                <!-- Mensajes de error en Descripcion-->
                                <div class="form-ErrorInput" v-if="errorMessage.descriptionError != ''">
                                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                                    @{{ errorMessage.descriptionError }}
                                </div>
                            </div>
                        </fieldset>
                        {{-- =====================================
                            CONTROL Y COBRO
                        ====================================== --}}
                        <fieldset class="billing-form-container-fieldset" for="billing-payment">
                            <!-- Numero de factura -->
                            <div class="mb-3" v-if="inputConcept != 0 && inputConcept != 5">
                                <label for="Control">N° de Control</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon7">
                                        <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                                    </span>
                                    <input type="text" class="form-control" id="Control"
                                        aria-describedby="basic-addon7" v-model="inputControl"
                                        placeholder="Ejemplo: CONTROL-1" />
                                </div>
                                <!-- Mensajes de error en Nombre-->
                                <div class="form-ErrorInput" v-if="errorMessage.controlError != ''">
                                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                                    @{{ errorMessage.controlError }}
                                </div>
                            </div>
                            <!-- Fecha de cobro -->
                            <div class="mb-3" v-if="inputConcept != 0 && inputConcept != 4 && inputConcept != 5">
                                <label for="Payment">Fecha de cobro</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon8">
                                        <calendar @to-input="formatPayment"></calendar>
                                    </span>
                                    <input type="text" class="form-control" id="Payment"
                                        aria-describedby="basic-addon8" v-model="inputPayment"
                                        placeholder="Ejemplo: 1999-12-03" disabled />
                                </div>
                                <!-- Mensajes de error en Nombre-->
                                <div class="form-ErrorInput" v-if="errorMessage.paymentError != ''">
                                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                                    @{{ errorMessage.paymentError }}
                                </div>
                            </div>
                        </fieldset>
                        {{-- =====================================
                            OBSERVACIONES
                        ====================================== --}}
                        <fieldset class="billing-form-container-fieldset" for="billing-observation">
                            <!-- Descripcion de la factura -->
                            <div class="mb-3">
                                <label for="Observation">Observaciones</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon9">
                                        <font-awesome string-icon="fa-solid fa-note-sticky"></font-awesome>
                                    </span>
                                    <textarea type="text" class="form-control" placeholder="Comentarios adicionales a la facturacion"
                                        aria-describedby="basic-addon9" v-model="inputObservation"></textarea>
                                </div>
                                <!-- Mensajes de error en Descripcion-->
                                <div class="form-ErrorInput" v-if="errorMessage.observationError != ''">
                                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                                    @{{ errorMessage.observationError }}
                                </div>
                            </div>
                        </fieldset>
                        {{-- =====================================
                            Notas de credito
                        ====================================== --}}
                        <fieldset class="billing-form-container-fieldset" for="billing-credit">
                            <!-- Numero de factura -->
                            <div class="mb-3" v-if="inputConcept == 4">
                                <label for="nullBill">N° de Factura a anular</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon10">
                                        <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                                    </span>
                                    <input type="text" ref="billAssociated" class="form-control" id="nullBill"
                                        aria-describedby="basic-addon10" v-model="inputNullBill"
                                        placeholder="Escriba una factura asociada" @click="emptyInput()"
                                        autocomplete="nope" />
                                    <dropdown-select :string-to-Search="inputNullBill"
                                        :array-object-result="billingNullInfo" column-to-search="billing_number"
                                        :control-list="noInput" @complete-input="autoCompleteBill"></dropdown-select>
                                </div>
                                <!-- Mensajes de error en factura a anular-->
                                <div class="form-ErrorInput" v-if="errorMessage.nullBillError != ''">
                                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                                    @{{ errorMessage.nullBillError }}
                                </div>
                            </div>
                            <!-- Numero de control -->
                            <div class="mb-3" v-if="inputNullBill.length != 0">
                                <label for="nullControl">N° de Control de la factura a anular</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon11">
                                        <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                                    </span>
                                    <input type="text" class="form-control" id="nullControl"
                                        aria-describedby="basic-addon11" v-model="inputNullControl" disabled />
                                </div>
                                <!-- Mensajes de error en el control-->
                                <div class="form-ErrorInput" v-if="errorMessage.nullControlError != ''">
                                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                                    @{{ errorMessage.nullControlError }}
                                </div>
                            </div>
                        </fieldset>
                        {{-- SUBMIT BUTTON --}}
                        <button class="buttonCRUD" :disabled="isClick" v-if="!validate.isValid">
                            <span v-if="isEdit & !isClick">Modificar factura</span>
                            <span v-else-if="!isEdit & !isClick">Crear factura</span>
                            <span v-else-if="isClick">
                                <font-awesome string-icon="fa-solid fa-spinner" is-spin></font-awesome>
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

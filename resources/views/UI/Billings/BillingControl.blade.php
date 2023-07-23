<div id="billing-control">
    <loading :active="!isMounted"></loading>
    <billing-info v-if="isMounted" :info-project="updateModel"
        @init-project="prepareUpdate({{ json_encode(Session::get('billingProject')) }}, '/billings/delete-update-data')">
    </billing-info>
</div>

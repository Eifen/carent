<div id="create-project">
    <loading :active="!isMounted"></loading>
    <form-clients v-if="isMounted"
    :is-click="isClick"
    @return-view="redirectView('/clientes')"
    @submit-form="newClient"></form-clients>
</div>
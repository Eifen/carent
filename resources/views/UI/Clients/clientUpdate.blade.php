<div id="update-client">
    <loading :active="!isMounted"></loading>
    <form-clients v-if="isMounted" :is-click="isClick" is-edit :data-edit="proxyToJson(updateModel)"
        @return-view="redirectView('/clientes')" @submit-form="updateClient"></form-clients>
</div>

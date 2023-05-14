<div id="create-client">
    <loading :active="!isMounted"></loading>
    <form-clients v-if="isMounted"
    :is-click="isClick"
    @return-view="returnClients()"
    @submit-form="newClient"></form-clients>
</div>

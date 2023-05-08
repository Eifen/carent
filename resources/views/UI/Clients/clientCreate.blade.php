<div id="create-client">
    <loading :active="!isMounted"></loading>
    <form-clients v-if="isMounted"
    :is-click="isClick"
    @return-view="returnClients()"></form-clients>
</div>

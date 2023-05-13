<div id="update-client">
    <loading :active="!isMounted"></loading>
    <form-clients v-if="isMounted"
    :is-click="isClick"
    is-edit
    :data-edit="updateModel"
    @return-view="returnClients()"
    @submit-form="updateClient"
    @init-client="prepareUpdate({{ json_encode(Session::get('clientUpdate')) }})"></form-clients>
</div>

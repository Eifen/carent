<div id="update-user">
    <loading :active="!isMounted"></loading>
    <form-users v-if="isMounted"
    :is-click="isEditClick"
    @submit-form="updateUser"
    @encrypt="getEncrypt('{{ Session::get('encrypt-key') }}' , '{{ Session::get('encrypt-iv') }}')"
    @return-view="redirectView()"
    @init-user="prepareUpdate({{ json_encode(Session::get('dataUpdate')) }})"
    :data-edit="updateModel"
    is-edit></form-users>
</div>

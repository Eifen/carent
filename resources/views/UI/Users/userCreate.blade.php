<div class="dashboard-users" id="create-users">
    <loading :active="!isMounted"></loading>
    <form-users v-if="isMounted"
    :is-click="isCreateClick"
    @return-view="redirectView()"
    @submit-form="newUser"
    @encrypt="getEncrypt('{{ Session::get('encrypt-key') }}' , '{{ Session::get('encrypt-iv') }}')"
    @columns1target="editUsuarios"></form-users>
</div>

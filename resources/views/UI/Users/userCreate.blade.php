<div id="create-users">
    <loading :active="!isMounted"></loading>
    <form-users v-if="isMounted"
    :is-click="isCreateClick"
    @return-view="redirectView()"
    @submit-form="newUser"
    @encrypt="getEncrypt('{{ Session::get('encrypt-key') }}' , '{{ Session::get('encrypt-iv') }}')"></form-users>
</div>

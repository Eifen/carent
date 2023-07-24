<div id="update-user">
    <loading :active="!isMounted"></loading>
    <form-users v-if="isMounted" :is-click="isClick" @submit-form="updateUser"
        @encrypt="getEncrypt('{{ Session::get('encrypt-key') }}' , '{{ Session::get('encrypt-iv') }}')"
        @return-view="redirectView('/usuarios')" :data-edit="proxyToJson(updateModel)" is-edit></form-users>
</div>

<div id="config-password">
    <password-control title="Cambio de contraseña" button-title="Cambiar contraseña" :is-click="isClick"
        @change-password="changePassword($event,'{{ Session::get('encrypt-key') }}' , '{{ Session::get('encrypt-iv') }}')"></password-control>
</div>

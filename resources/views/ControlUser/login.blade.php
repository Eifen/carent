<section class="login">
    <div class="login-content" id="app-login" v-cloak>
        <div class="login-content-credentials">
            <img id="credentials-imagen" src="/images/logo-carent.png" alt="Logo CARENT"></img>
            <form id="credentials-form">
                {{--Codigo de usuario--}}
                <div :class="{'input-group': ErrorStyle.base, 'mb-3': !ErrorStyle.HasError.codigo}">
                    <span class="input-group-text" id="basic-addon1">
                        <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                    </span>
                    <input type="text" class="form-control" placeholder="Código de Usuario"
                    aria-label="Codigo" aria-describedby="basic-addon1" id="Codigo" v-model="codigoUsuario.value"/>
                </div>
                {{--Control de errores de Codigo de Usuario--}}
                <span class="form-ErrorInput" v-if="codigoUsuario.IsEmpty">
                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                    @{{ErrorMessage.codigoError}}
                </span>
                {{--Clave del Usuario--}}
                <div :class="{'input-group': ErrorStyle.base, 'mb-3': !ErrorStyle.HasError.password}">
                    <span class="input-group-text" id="basic-addon2">
                        <font-awesome string-icon="fa-solid fa-lock"></font-awesome>
                    </span>
                    <input :type="TypeInputPassword" class="form-control" placeholder="Contraseña"
                    aria-label="Clave" aria-describedby="basic-addon2" id="Clave" v-model="passwordUsuario.value"/>
                    <span class="input-group-text form-eye" id="basic-addon2" @click="changeInput(!state)">
                        {{--Control del ojo--}}
                        <font-awesome :string-icon="controlEye"></font-awesome>
                    </span>
                </div>
                {{--Control de errores de Contraseña de usuario--}}
                <span class="form-ErrorInput" v-if="passwordUsuario.IsEmpty">
                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                    @{{ErrorMessage.passwordError}}
                </span>
                {{--Envio de datos del Formulario--}}
                <button type="button" class="form-button"
                {{--Enviamos la data del session por parametro al componente de VUE--}} 
                @click="iniciarSesion('{{ Session::get('encrypt-key') }}' , '{{ Session::get('encrypt-iv') }}')">Ingresar</button>
            </form>
            {{--Modal para recuperar la contraseña--}}
            <a href="#" id="credentials-forgot-password">Olvidé mi contraseña</a>
        </div>
        <div class="login-content-social">

        </div>
    </div>
</section>
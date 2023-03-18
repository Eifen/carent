<section class="login">
    <div class="login-content" id="app-login">
        <div class="login-content-credentials">
            <img id="credentials-imagen" src="/images/logo-carent.png" alt="Logo CARENT"></img>
            <form id="credentials-form">
                {{--Codigo de usuario--}}
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                    </span>
                    <input type="text" class="form-control" placeholder="Código de Usuario"
                    aria-label="Codigo" aria-describedby="basic-addon1" id="Codigo"/>
                </div>
                {{--Clave del Usuario--}}
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon2">
                        <font-awesome string-icon="fa-solid fa-lock"></font-awesome>
                    </span>
                    <input :type="TypeInputPassword" class="form-control" placeholder="Contraseña"
                    aria-label="Clave" aria-describedby="basic-addon2" id="Clave"/>
                    <span class="input-group-text form-eye" id="basic-addon2" @click="changeInput(!state)">
                        {{--Control del ojo--}}
                        <font-awesome :string-icon="controlEye"></font-awesome>
                    </span>
                </div>
                {{--Envio de datos del Formulario--}}
                <button type="button" class="form-button" id="Submit">Ingresar</button>
            </form>
            {{--Modal para recuperar la contraseña--}}
            <a href="#" id="credentials-forgot-password">Olvidé mi contraseña</a>
        </div>
        <div class="login-content-social">

        </div>
    </div>
</section>

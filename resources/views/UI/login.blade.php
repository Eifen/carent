<section class="login">
    <div class="login-content" id="app-login" v-cloak>
        <div class="login-content-credentials">
            <img id="credentials-imagen" src="/images/logo-carent.png" alt="Logo CARENT"></img>
            <form id="credentials-form">
                {{-- Codigo de usuario --}}
                <div :class="{ 'input-group': ErrorStyle.base, 'mb-3': !ErrorStyle.HasError.codigo }">
                    <span class="input-group-text" id="basic-addon1">
                        <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                    </span>
                    <input type="text" class="form-control" placeholder="Código de Usuario" aria-label="Codigo"
                        aria-describedby="basic-addon1" id="codigoUsuario" v-model="codigoUsuario.value"
                        @input="verifyCode" />
                </div>
                {{-- Control de errores de Codigo de Usuario --}}
                <span class="form-ErrorInput" v-if="codigoUsuario.IsEmpty">
                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                    @{{ ErrorMessage.codigoError }}
                </span>
                {{-- Clave del Usuario --}}
                <div :class="{ 'input-group': ErrorStyle.base, 'mb-3': !ErrorStyle.HasError.password }">
                    <span class="input-group-text" id="basic-addon3">
                        <font-awesome string-icon="fa-solid fa-lock"></font-awesome>
                    </span>
                    <input :type="TypeInputPassword" class="form-control" placeholder="Contraseña" aria-label="Clave"
                        aria-describedby="basic-addon2" id="passwordUsuario" v-model="passwordUsuario.value"
                        @input="verifyPassword()" />
                    <span class="input-group-text form-eye" id="basic-addon4" @click="changeInput()">
                        {{-- Control del ojo --}}
                        <font-awesome :string-icon="controlEye"></font-awesome>
                    </span>
                </div>
                {{-- Control de errores de Contraseña de usuario --}}
                <span class="form-ErrorInput" v-if="passwordUsuario.IsEmpty">
                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                    @{{ ErrorMessage.passwordError }}
                </span>
                {{-- Envio de datos del Formulario --}}
                <button type="button" class="form-button" :class="{ disable: isDisable }" {{-- Enviamos la data del session por parametro al componente de VUE --}}
                    @click="iniciarSesion('{{ Session::get('encrypt-key') }}' , '{{ Session::get('encrypt-iv') }}')">
                    <span v-if="!isClick">Ingresar</span>
                    <span v-if="isClick">
                        <font-awesome string-icon="fa-solid fa-spinner" is-spin></font-awesome>
                    </span>
                </button>
                {{-- Control de Error del inicio de sesión --}}
                <span :class="controlLogin.classMessage" v-if="controlLogin.status">
                    <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                    @{{ controlLogin.message }}
                </span>
            </form>
            {{-- Modal para recuperar la contraseña --}}
            <a href="#" id="credentials-forgot-password" @click="showModal()">Olvidé mi
                contraseña</a>
            <!-- Modal -->
            <div class="modal fade" id="modal-forgot-password" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <password-control style="margin-top:0" title="Recuperar contraseña" button-title="Recuperar"
                            :is-click="isClick" forgot-password @recovery-password="toRecovery"></password-control>
                    </div>
                </div>
            </div>
        </div>
        <div class="login-content-social">
            <div class="login-content-social-icon"><a target="_blank" href="https://www.instagram.com/crowe.ve"
                    title="CroweInstagram">
                    <font-awesome string-icon="fa-brands fa-instagram"></font-awesome>
                </a></div>
            <div class="login-content-social-icon"><a target="_blank" href="https://www.facebook.com/CroweVzla"
                    title="CroweFacebook">
                    <font-awesome string-icon="fa-brands fa-facebook"></font-awesome>
                </a></div>
            <div class="login-content-social-icon"><a target="_blank" href="https://twitter.com/crowe_vzla"
                    title="CroweTwitter">
                    <font-awesome string-icon="fa-brands fa-twitter"></font-awesome>
                </a></div>
            <div class="login-content-social-icon"><a target="_blank"
                    href="https://www.youtube.com/channel/UCx7ekjvHFTuGkenLjap-oFQ" title="CroweYoutube">
                    <font-awesome string-icon="fa-brands fa-youtube"></font-awesome>
                </a></div>
        </div>
    </div>
</section>

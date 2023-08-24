import { createApp } from "vue/dist/vue.esm-bundler";
import { componentsUI, methodsUI, watchUI, CrudUi, dataUI } from "../UIConfig";
import * as bootstrap from "bootstrap";

const usersApp = createApp({
    data() {
        return {
            usersColumn: {
                column1: "Código",
                column2: "Cédula",
                column3: "Nombre",
                column4: "Correo",
                column5: "Estatus",
                settings: {
                    columnS1: "Editar",
                    columnS2: "Asignar menú",
                },
            },
            selectSearch: {
                select1: "Código",
                select2: "Nombre",
                select3: "Cédula",
                select4: "Correo",
            },
            accessUser: {
                userP: false,
                clientP: false,
                projectP: false,
                assignP: false,
                adminP: false,
                closeP: false,
                billingP: false,
            },
            tableTarget: "users",
            controlUserModal: null,
            previewUserInfo: null,
        };
    },
    //Ante de montar, consultamos el tamaño máximo de la páginación
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        CrudUi.getTable("/usuarios/allUsers", this);
        //Configuramos el modal
        this.controlUserModal = new bootstrap.Modal(
            document.getElementById("userInfoModal"),
            { keyboard: false, backdrop: "static" }
        );
    },
    methods: {
        //Metodo dedicados a las configuraciones de la tabla
        editUsuarios(idUsuario) {
            //Seccionamos el ID y luego lo pasamos al controlador
            const paramsDTO = { codigoSQL: idUsuario };
            const routesDTO = {
                post: "/usuarios/update/loadingUser",
                redirect: "/usuarios/update",
            };
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },
        updateAccess() {
            let prepareParams = {
                userP: 0,
                clientP: 0,
                projectP: 0,
                assignP: 0,
                adminP: 0,
                closeP: 0,
                billingP: 0,
            };
            //Acomodamos los permisos
            for (const field in this.previewUserInfo) {
                switch (true) {
                    //Usuarios
                    case field == "userP" && this.previewUserInfo[field]:
                        prepareParams["userP"] = 2;
                        break;
                    //Clientes
                    case field == "clientP" && this.previewUserInfo[field]:
                        prepareParams["clientP"] = 3;
                        break;
                    //Proyectos
                    case field == "projectP" && this.previewUserInfo[field]:
                        prepareParams["projectP"] = 6;
                        break;
                    //Asignacion
                    case field == "assignP" && this.previewUserInfo[field]:
                        prepareParams["assignP"] = 7;
                        break;
                    //Administrativas
                    case field == "adminP" && this.previewUserInfo[field]:
                        prepareParams["adminP"] = 8;
                        break;
                    //Cierre de proyectos
                    case field == "closeP" && this.previewUserInfo[field]:
                        prepareParams["closeP"] = 13;
                        break;
                    //Billings
                    case field == "billingP" && this.previewUserInfo[field]:
                        prepareParams["billingP"] = 10;
                        break;
                }
            }
            //Actualizamos
            const routesSelfDTO = {
                post: "/usuarios/update-access-user",
                redirect: "/usuarios",
                self: this,
            };
            CrudUi.controlCrud(routesSelfDTO, {
                user_access: prepareParams,
                user_code: this.previewUserInfo.código,
            });
        },
        /**
         * MEtodo que muestra informacion del usuario y sus permisos del sistema para ser asignados
         * @param {*} idUsuario
         */
        permisosUsuarios(idUsuario) {
            const getUserId = this.listData
                .map((objetc) => objetc.código)
                .indexOf(idUsuario);
            axios
                .post("usuarios/get-access-user", { user_code: idUsuario })
                .then((request) => {
                    if (request.data != 0) {
                        //Realizamos un for de los permisos
                        for (const key in request.data) {
                            //Solo cambiamos la informacion si el valor es 1
                            this.accessUser[key] =
                                request.data[key] == 1 ? true : false;
                        }
                        this.controlUserModal.show();
                        this.previewUserInfo = {
                            ...this.listData[getUserId],
                            ...this.accessUser,
                        };
                        console.log(this.previewUserInfo);
                    }
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        crearUsuario() {
            window.location.href = "/usuarios/create";
        },
    },
    watch: {
        //Si carga los usuarios desactivamos el login
        listData() {
            this.isMounted = true;
        }, //Desactivamos el loading
    },
    mixins: [componentsUI, methodsUI, watchUI, dataUI],
});

if (document.getElementById("section-users") !== null) {
    usersApp.mount("#section-users");
    window.location.hash = "#01";
}

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
                reportP: false,
                rclosureP: false,
                rdirectiveMP: false,
                rhorasP: false,
                rdirectiveAP: false,
                rproyectosP: false
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
            this.isClick = true;
            //Cada parametro tendra una tupla, si esta en false significa que esta desactivado
            let prepareParams = {
                userP: [2, false],
                clientP: [4, false],
                projectP: [6, false],
                assignP: [7, false],
                adminP: [8, false],
                closeP: [13, false],
                billingP: [10, false],
                reportP: [15, false],
                rclosureP: [16, false],
                rdirectiveMP: [17, false],
                rhorasP: [18, false],
                rdirectiveAP: [19, false],
                rproyectosP: [20, false],
            };
            //Acomodamos los permisos
            for (const field in this.previewUserInfo) {
                switch (true) {
                    //Usuarios
                    case field == "userP" && this.previewUserInfo[field]:
                        prepareParams["userP"][1] = true;
                        break;
                    //Clientes
                    case field == "clientP" && this.previewUserInfo[field]:
                        prepareParams["clientP"][1] = true;
                        break;
                    //Proyectos
                    case field == "projectP" && this.previewUserInfo[field]:
                        prepareParams["projectP"][1] = true;
                        break;
                    //Asignacion
                    case field == "assignP" && this.previewUserInfo[field]:
                        prepareParams["assignP"][1] = true;
                        break;
                    //Administrativas
                    case field == "adminP" && this.previewUserInfo[field]:
                        prepareParams["adminP"][1] = true;
                        break;
                    //Cierre de proyectos
                    case field == "closeP" && this.previewUserInfo[field]:
                        prepareParams["closeP"][1] = true;
                        break;
                    //Billings
                    case field == "billingP" && this.previewUserInfo[field]:
                        prepareParams["billingP"][1] = true;
                        break;
                    //Report
                    case field == "reportP" && this.previewUserInfo[field]:
                        prepareParams["reportP"][1] = true;
                        break;
                    //Reporte de cierre
                    case field == "rclosureP" && this.previewUserInfo[field]:
                        prepareParams["rclosureP"][1] = true;
                        break;
                    //Reporte directivo mensual
                    case field == "rdirectiveMP" && this.previewUserInfo[field]:
                        prepareParams["rdirectiveMP"][1] = true;
                        break;
                    //Reporte de hroas no cargables
                    case field == "rhorasP" && this.previewUserInfo[field]:
                        prepareParams["rhorasP"][1] = true;
                        break;
                    //Reporte directivo mensual
                    case field == "rdirectiveAP" && this.previewUserInfo[field]:
                        prepareParams["rdirectiveAP"][1] = true;
                        break;
                    //Reporte de proyectos
                    case field == "rproyectosP" && this.previewUserInfo[field]:
                        prepareParams["rproyectosP"][1] = true;
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
                    this.previewUserInfo = {
                        ...this.listData[getUserId],
                        ...this.accessUser,
                    };
                    if (request.data != 0) {
                        //Realizamos un for de los permisos
                        for (const key in request.data) {
                            //Solo cambiamos la informacion si el valor es 1
                            this.accessUser[key] =
                                request.data[key] == 1 ? true : false;
                        }
                        this.previewUserInfo = {
                            ...this.listData[getUserId],
                            ...this.accessUser,
                        };
                    }
                    this.controlUserModal.show();
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

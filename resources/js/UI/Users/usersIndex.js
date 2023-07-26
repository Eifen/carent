import { createApp } from "vue/dist/vue.esm-bundler";
import { componentsUI, methodsUI, watchUI, CrudUi, dataUI } from "../UIConfig";

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
                    // columnS2: "Permisos del Sistema",
                },
            },
            selectSearch: {
                select1: "Codigo",
                select2: "Nombre",
                select3: "Cedula",
                select4: "Correo",
            },
            tableTarget: "users",
        };
    },
    //Ante de montar, consultamos el tamaño máximo de la páginación
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        CrudUi.getTable("/usuarios/allUsers", this);
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
        permisosUsuarios(idUsuario) {
            console.log(this.listData[idUsuario]);
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

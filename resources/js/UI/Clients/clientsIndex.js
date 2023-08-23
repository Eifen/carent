import { createApp } from "vue/dist/vue.esm-bundler";
import { componentsUI, methodsUI, watchUI, CrudUi, dataUI } from "../UIConfig";
//Bootstrap
import * as bootstrap from "bootstrap";
import { AXIOSINTERVAL } from "../../app";

const clientsIndex = createApp({
    data() {
        return {
            clientsColumns: {
                column1: "Código",
                column2: "Socio",
                column3: "Razon social",
                column4: "Correo electrónico",
                column5: "Estatus",
                settings: {
                    columnS1: "Editar",
                    columnS2: "Información del cliente",
                },
            },
            selectSearch: {
                select1: "Código",
                select2: "Socio",
                select3: "Razón social",
                select4: "Correo",
            },
            tableTarget: "clients",
            controlClientModal: {}, //Propiedad que controla el modal de la información de clientes
            previewClientInfo: null, //Almacena la informacion del cliente seleccionado para sus vista preeliminar
        };
    },
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        CrudUi.getTable("/clientes/allClients", this);
        //Configuramos el modal
        this.controlClientModal = new bootstrap.Modal(
            document.getElementById("clientInfoModal"),
            { keyboard: false, backdrop: "static" }
        );
    },
    methods: {
        /**
         * Metodo que crea una instancia en Sessión para pasarla temporalmente a un formulario de actualización
         * @param {*} idClient Almacena la ID seleccionada de la lista de clientes
         */
        editClient(idClient) {
            const paramsDTO = { codigoSQL: idClient };
            const routesDTO = {
                post: "/clientes/update/loadingClient",
                redirect: "/clientes/update",
            };
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },
        createClient() {
            window.location.href = "/clientes/create";
        },
        /**
         * Metodo que recibe el codigo del cliente para mostrar información detallada del mismo
         * @param {int} idClient
         */
        clientInfo(idClient) {
            //Reiniciamos el preview
            this.previewClientInfo = null;
            //Abrimos el modal
            this.controlClientModal.show();
            //Cargamos su información
            axios
                .post("/clientes/info-client", { client_code: idClient })
                .then((request) => {
                    this.previewClientInfo = request.data[0];
                })
                .catch((error) => {
                    console.error(error);
                });
        },
    },
    mixins: [componentsUI, methodsUI, watchUI, dataUI],
});

if (document.getElementById("section-clients") !== null) {
    clientsIndex.mount("#section-clients");
    window.location.hash = "#02";
}

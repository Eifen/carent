//Toastify
import { toast } from "vue3-toastify";
import { NOTIFYINTERVAL, AXIOSINTERVAL } from "../../../app";

export const registerAdminDayMethod = {
    methods: {
        /**
         * Metodo que registra una hora administrativa en la base de datos
         * @param {*} childParam Captura una tupla donde [0] es la hora registrada y [1] es la observacion
         * @param {*} adminAssignedId Se trata del id del concepto seleccionado y asignado para cargar
         */
        registerAdminDay(childParam, adminAssignedId) {
            //Preparar dia administrativo
            this.onCharged = true;
            const prepareAdminDay = {
                selectInfo: childParam, //Datos de los input del dia
                assignedId: adminAssignedId, //Id del admin asignado por cargar
                listHour: this.listAdminHourData, //Lista de los conceptos donde el usuario ha cargado horas
            };

            axios
                .post(
                    "/projects/register-hours/add-admin-hour",
                    prepareAdminDay
                )
                .then((request) => {
                    //Verificamos que el response no sea falso
                    if (request.status === 200 && !request.data.response)
                        throw request.data.message;
                    this.listAdminHourData = request.data.message;
                    //Mensaje de confirmacion
                    toast.success("Hora registrada exitosamente", {
                        position: toast.POSITION.TOP_LEFT,
                        autoClose: NOTIFYINTERVAL,
                    });

                    setTimeout(() => {
                        this.onCharged = false;
                    }, AXIOSINTERVAL);
                })
                .catch((errorMessage) => {
                    console.error(errorMessage);
                    toast.error(errorMessage.error, {
                        position: toast.POSITION.TOP_LEFT,
                        autoClose: NOTIFYINTERVAL,
                    });

                    setTimeout(() => {
                        this.listAdminHourData = errorMessage.newList;
                        this.onCharged = false;
                    }, AXIOSINTERVAL);
                });
        },
        /**
         * Metodo que elimina un dia en horas administrativas
         * @param {Array} childParam Captura una tupla donde [0] es la hora registrada y [1] es la observacion
         * @param {int} loadAssignedId ID del proyecto cargada
         */
        unRegisterAdminDay(childParam, loadAssignedId) {
            this.onCharged = true;
            //Distribuimos los parametros en funcion del tipo de operacion
            const prepareDay = {
                assignedId: loadAssignedId,
                date: childParam[0]["day"],
                type: 1,
                loadId: loadAssignedId,
                listHour: this.listAdminHourData, //Lista de las horas administrativas donde el usuario ha cargado horas
            };
            axios
                .post("/projects/register-hours/delete-hour", prepareDay)
                .then((request) => {
                    //Verificamos que el response no sea falso
                    if (request.status === 200 && !request.data.response)
                        throw request.data;
                    this.listAdminHourData = request.data.message;
                    //Mensaje de confirmacion
                    toast.success("Hora eliminada con exito", {
                        position: toast.POSITION.TOP_LEFT,
                        autoClose: NOTIFYINTERVAL,
                    });

                    setTimeout(() => {
                        this.onCharged = false;
                    }, AXIOSINTERVAL);
                })
                .catch((errorMessage) => {
                    console.error(errorMessage);
                    toast.error(errorMessage.error, {
                        position: toast.POSITION.TOP_LEFT,
                        autoClose: NOTIFYINTERVAL,
                    });

                    setTimeout(() => {
                        this.onCharged = false;
                    }, AXIOSINTERVAL);
                });
        },
    },
};

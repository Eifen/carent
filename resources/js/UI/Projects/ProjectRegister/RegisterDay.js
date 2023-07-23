//Toastify
import { toast } from "vue3-toastify";
import { NOTIFYINTERVAL, AXIOSINTERVAL } from "../../../app";

export const registerDayMethods = {
    methods: {
        /**
         * Metodo que registra una hora de proyecto en la base de datos
         * @param {*} childParam Captura una tupla donde [0] es la hora registrada y [1] es la observacion
         * @param {*} projectAssignedId Se trata del id del proyecto seleccionado y asignado para cargar
         */
        registerDay(childParam, projectAssignedId) {
            this.onCharged = true;
            //Preparamos la informacion a enviar
            const prepareDay = {
                selectInfo: childParam, //Datos de los input del dia
                assignedId: projectAssignedId, //Id del proyecto asignado por cargar
                multiSelectProjectInfo: this.gridProjectInfo, //Informacion de los proyectos seleccionados y sus horas cargadas
                listHour: this.listProjectHourData, //Lista de los proyectos donde el usuario ha cargado horas
            };

            axios
                .post("/projects/register-hours/add-hour", prepareDay)
                .then((request) => {
                    //Verificamos que el response no sea falso
                    if (request.status === 200 && !request.data.response)
                        throw request.data.message;
                    this.listProjectHourData = request.data.message;
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
                        this.listProjectHourData = errorMessage.newList;
                        this.onCharged = false;
                    }, AXIOSINTERVAL);
                });
        },
        /**
         * Metodo que elimina un dia en horas proyectos
         * @param {Array} childParam Captura una tupla donde [0] es la hora registrada y [1] es la observacion
         * @param {int} loadAssignedId ID del proyecto cargada
         */
        unRegisterDay(childParam, loadAssignedId) {
            this.onCharged = true;
            //Distribuimos los parametros en funcion del tipo de operacion
            const prepareDay = {
                assignedId: loadAssignedId,
                date: childParam[0]["day"],
                type: 2,
                loadId: loadAssignedId,
                listHour: this.listProjectHourData, //Lista de los proyectos donde el usuario ha cargado horas
            };
            axios
                .post("/projects/register-hours/delete-hour", prepareDay)
                .then((request) => {
                    //Verificamos que el response no sea falso
                    if (request.status === 200 && !request.data.response)
                        throw request.data;
                    this.listProjectHourData = request.data.message;
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

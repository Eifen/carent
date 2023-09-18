import { createApp } from "vue/dist/vue.esm-bundler";
import { componentsUI, dataUI, CrudUi } from "../../UIConfig";
import Multiselect from "@vueform/multiselect";
import { AXIOSINTERVAL, NOTIFYINTERVAL } from "../../../app";
import { Validate } from "../../../Models/ValidateModel";

const modalApp = createApp({
    data() {
        return {
            nameModal: "asignHourModal", //Almacena el id del modal
            controlModal: null, //Se encarga de controlar el id del modal
            managerAssigned: "", //Especifica el gerente asignado a ese proyecto
            projectName: "", //Nombre del proyecto seleccionado
            clientName: "", //Nombre del cliente asociado
            hoursAssigned: 0, //Horas asignadas
            additionalHours: 0, //Horas adicionales
            //Espacio reservado para los inputs
            inputUsersAssigned: [], //Almacena la información del multiselect
            usersPerDepartment: [
                {
                    value: 0,
                    label: "Seleccione uno o más usuarios",
                    disabled: true,
                },
            ], //Muestra las opciones del multiselect
            managerUserAssigned: [], //Array de objetos que almacena la informacion de las horas asignadas
            missinHours: 0, //Horas faltantes por asignar
            isValid: false, //Controla el estado del boton
            isClick: false, //Controla el estado del click
            hoursAssignedError: "", //Controla el mensaje de error
            getDepartmentAssignedId: 0, //Obtiene el ID del departamento asignado
        };
    },
    mounted() {
        this.controlModal = document.getElementById(this.nameModal);
        //Incializamos el listener
        this.controlModal.addEventListener("shown.bs.modal", this.configSelect);
    },
    beforeUnmount() {
        //Removemos el listener
        window.removeEventListener("show.bs.modal", this.openModal);
        this.isMounted = false;
    },
    methods: {
        /**
         * Metodo que se sincroniza con el controlador cada vez que se abre el modal.
         */
        configSelect() {
            this.isMounted = false;
            axios
                .post("/projects/assign/re-assign-data")
                .then((request) => {
                    //Reiniciamos las variables
                    this.inputUsersAssigned = [];
                    this.managerUserAssigned = [];
                    //Si no existe algun error, asociamos la información al departamento
                    request.data["users"].forEach((user) => {
                        //Insertamos una nueva fila en usersPerDepartment
                        this.usersPerDepartment.push({
                            value: user.user_id,
                            label: user.user_name,
                            disabled: false,
                        });
                        //Informacion del gerente
                        if (
                            user["user_id"] ===
                            request.data["project"]["manager_id"]
                        )
                            this.managerAssigned = user["user_name"];
                    });
                    this.usersPerDepartmentOld = this.usersPerDepartment;
                    //Luego procedemos a dar informacion del proyecto
                    this.projectName =
                        request.data["project"]["project_description"];
                    this.hoursAssigned =
                        request.data["project"]["hours_assigned"];
                    //Horas adicionales
                    this.additionalHours = request.data["additional"].reduce(
                        (countTotal, department) =>
                            countTotal + department.additional_hour,
                        0
                    );
                    this.missinHours =
                        this.hoursAssigned + this.additionalHours;

                    //Informacion del cliente
                    this.clientName = request.data["project"]["bussiness_name"];
                    //Capturamos el id del departamento asignado
                    this.getDepartmentAssignedId =
                        request.data["project"]["department_assigned_id"];

                    setTimeout(() => {
                        //Configuramos la estructura de horas asignadas
                        this.configUserPerDepartment(request.data);
                        this.isMounted = true;
                    }, AXIOSINTERVAL);
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        /**
         * Metodo que configura la asignacion de horas
         * @param {Object} projectInfo Objeto proxy que almacena la informacion del request.data de axios
         */
        configUserPerDepartment(projectInfo) {
            //Llenamos la informacion de usuarios seleccionados y informacion de horas
            if (projectInfo["analyst"].length !== 0) {
                //Recorremos la tabla de analistas (projects_users_assigned)
                projectInfo["analyst"].forEach((user) => {
                    //Usuarios seleccionados
                    this.inputUsersAssigned.push(user["user_id"]);
                    //Horas totales cargadas
                    const filterUser = projectInfo["registerHour"].filter(
                        (hour) => hour.user_id == user["user_id"]
                    );
                    const countHours = filterUser.reduce(
                        (total, project) =>
                            total + parseFloat(project.register_hour),
                        0
                    );
                    //Informacion de horas
                    this.managerUserAssigned.push({
                        idUser: user["user_id"],
                        userName: user["user_name"],
                        hoursAssigned: user["assigned_hours"],
                        hourRegister: countHours,
                    });
                    //Restamos las horas
                    this.missinHours =
                        parseInt(this.missinHours) -
                        parseInt(user["assigned_hours"]);
                });
            }
            //En el caso que este vacio, solo seleccionamos al gerente asignado
            if (projectInfo["analyst"].length === 0) {
                this.inputUsersAssigned[0] =
                    projectInfo["project"]["manager_id"];
                //Informacion de horas
                this.inputUsersAssigned.forEach((selectUser) => {
                    const getIndex = this.usersPerDepartment
                        .map((object) => object.value)
                        .indexOf(selectUser);
                    this.managerUserAssigned.push({
                        idUser: selectUser,
                        userName: this.usersPerDepartment[getIndex].label,
                        hoursAssigned: 0,
                    });
                });
            }
        },
        /**
         * Metodo que detecta un cambio en uno de los campos de horas asignadas
         * @param {*} inputEvent Captura un InputEvent del campo que lo activo
         */
        totalHours(inputEvent = null) {
            //Reasignamos las horas totales
            this.missinHours = this.hoursAssigned + this.additionalHours;
            //Calculamos las nuevas horas
            this.managerUserAssigned.forEach((userAssigned) => {
                //Verificamos que cada input coincida con un numero
                if (!Validate.Number(userAssigned["hoursAssigned"]).response) {
                    userAssigned["hoursAssigned"] = "";
                } else {
                    //Si es un numero, restamos
                    this.missinHours =
                        parseInt(this.missinHours) -
                        parseInt(userAssigned["hoursAssigned"]);
                }
            });
        },
        /**
         * Metodo que inserta o actualiza los valores en la tabla de aginación
         */
        asignHoursSubmit() {
            this.isClick = true;
            //Hacemos un llamado a Axios
            CrudUi.controlCrud(
                {
                    post: "/projects/assign/update-asign-projects",
                    redirect: "/projects/assign",
                    self: this,
                },
                {
                    departmentAssignedId: this.getDepartmentAssignedId,
                    infoAsign: this.managerUserAssigned,
                }
            );
        },
    },
    computed: {},
    watch: {
        /**
         * Watcher que me asigna la informacion del multiselect
         */
        inputUsersAssigned(newSelect, oldSelect) {
            //Inicialmente creamos una copia del array
            const copyManager = this.managerUserAssigned;
            console.log(newSelect, oldSelect, copyManager);

            //Recorremos el array anterior
            try {
                oldSelect.forEach((userId) => {
                    //Obtenemos el indice en el array de copia
                    const getNewIndex = newSelect.indexOf(userId);
                    const getOldIndex = copyManager
                        .map((object) => object.idUser)
                        .indexOf(userId);
                    //Si existe el indice y sus horas registradas son mayores que 0 no se puede eliminar
                    if (getOldIndex !== -1 && getNewIndex === -1) {
                        if (copyManager[getOldIndex].hourRegister > 0)
                            throw `No se puede eliminar al usuario ${copyManager[getOldIndex].userName} ya que tiene horas cargadas. Reduzca sus horas equivalente a las que ha cargado`;
                    }
                });
                console.log(this.inputUsersAssigned);
                //Vaciamos el array
                this.managerUserAssigned = [];
                //Recorremos el nuevo multiSelect
                newSelect.forEach((userId) => {
                    //Obtenemos el indice del usuario por departamento
                    const getIndex = this.usersPerDepartment
                        .map((object) => object.value)
                        .indexOf(userId);

                    //Obtenemos el indice de la copia
                    const getCopyIndex = copyManager
                        .map((object) => object.idUser)
                        .indexOf(userId);

                    //Si existe el indice, hacemos push de esa posicion de la copia. Caso contrario hacemos push con nuevos valores
                    if (getCopyIndex !== -1) {
                        this.managerUserAssigned.push(
                            copyManager[getCopyIndex]
                        );
                    } else {
                        this.managerUserAssigned.push({
                            idUser: userId,
                            userName: this.usersPerDepartment[getIndex].label,
                            hoursAssigned: 0,
                        });
                    }
                });
                //Cargamos el conteo de horas restantes
                this.totalHours();
            } catch (errorMessage) {
                this.inputUsersAssigned = [];
                oldSelect.forEach((userId) => {
                    this.inputUsersAssigned.push(userId);
                });
                CrudUi.errorMesssage(errorMessage);
            }
        },
        missinHours(misingHour) {
            try {
                //Configuramos el numero en funcion si es un valor negativo o positivo
                const numberDTO =
                    misingHour < 0
                        ? Validate.Number(misingHour * -1)
                        : Validate.Number(misingHour);
                //Error de not number
                if (!numberDTO.response) throw numberDTO.message;
                //Valor menor que 0
                if (misingHour < 0)
                    throw (
                        "No puede cargar más horas de las estipuladas (Solo " +
                        (this.hoursAssigned + this.additionalHours) +
                        ". Intenta cargar " +
                        (this.hoursAssigned +
                            this.additionalHours +
                            misingHour * -1) +
                        ")"
                    );
                //Si pasa las validaciones
                this.isValid = true;
                this.hoursAssignedError = "";
            } catch (error) {
                //Si ocurre un error
                this.isValid = false;
                this.hoursAssignedError = error;
                console.error(error);
            }
        },
    },
    mixins: [componentsUI, dataUI],
    components: { Multiselect },
});

if (document.getElementById("asignHourModal") !== null) {
    modalApp.mount("#asignHourModal");
}

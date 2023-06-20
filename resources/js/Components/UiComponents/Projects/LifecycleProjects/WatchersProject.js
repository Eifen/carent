import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";

export const projectWatchers = {
    watch: {
        //Watch para el update
        dataEdit(newEdit) {
            //Obtenemos los indices de clientes y usuarios
            const indexDTO = {
                client: this.dataSelect.clients
                    .map((object) => object.client_id)
                    .indexOf(newEdit.project.client_id),
                partner: this.dataSelect.partners
                    .map((object) => object.user_id)
                    .indexOf(newEdit.project.partner_id),
                qualityPartner: this.dataSelect.partners
                    .map((object) => object.user_id)
                    .indexOf(newEdit.project.quality_partner_id),
                manager: this.dataSelect.managers
                    .map((object) => object.user_id)
                    .indexOf(newEdit.project.manager_id),
            };
            console.log(newEdit, "papa", indexDTO);
            //Distribuimos la informacion
            this.inputStatusSelect = newEdit.project.status_id; //Posicion del estado del proyecto
            this.inputCurrenciesSelect = newEdit.project.currency_id; //Select del tipo de moneda
            this.inputCompaniesSelect = newEdit.project.company_id; //Select del tipo de empresa
            this.inputProjectDescription = newEdit.project.project_description; //String que de la descripcion del proyecto
            this.inputClientAssociated =
                this.dataSelect.clients[indexDTO.client].bussiness_name; //String que almacena la información del cliente asociado
            this.inputManagerAssociated =
                this.dataSelect.managers[indexDTO.manager].user_name; //String que almacena la información del manager asociado
            this.inputPartnerAssociated =
                this.dataSelect.partners[indexDTO.partner].user_name; //String que almacena la información del socio asociado
            this.inputQualityPartnerAssociated =
                this.dataSelect.partners[indexDTO.qualityPartner].user_name; //String que almacena la información del socio de calidad asociado
            this.inputHiringDate = newEdit.project.hiring_date; //Fecha de contratacion
            this.inputValue = Number(
                newEdit.project.project_value
            ).toLocaleString("de-DE"); //Monto del proyecto

            //Cargamos la informacion de los departamentos
            for (
                let countDepartment = 0;
                countDepartment < newEdit.departments.length;
                countDepartment++
            ) {
                //Id del departamento
                const indexDepartment = this.dataSelect.departments
                    .map((object) => object.value)
                    .indexOf(
                        newEdit.departments[countDepartment].department_id
                    );
                this.inputDepartments.push(
                    newEdit.departments[countDepartment].department_id
                );

                //Llenamos el multiselect
                this.dataSelect.managersPerDepartment.push({
                    departmentId:
                        newEdit.departments[countDepartment].department_id,
                    departmentName:
                        this.dataSelect.departments[indexDepartment].label,
                    managersDepartment: this.dataSelect.managers.filter(
                        (colums) => {
                            return colums.department_id
                                .toString()
                                .includes(
                                    newEdit.departments[
                                        countDepartment
                                    ].department_id.toString()
                                );
                        }
                    ),
                    hoursAssigned:
                        newEdit.departments[countDepartment].hours_assigned,
                    selectManager:
                        newEdit.departments[countDepartment].manager_id,
                });

                //Sumamos las horas asignadas
                this.inputHoursAssigned =
                    parseInt(this.inputHoursAssigned) +
                    parseInt(
                        newEdit.departments[countDepartment].hours_assigned
                    );
            }
        },
        //Monto
        inputValue(newValue) {
            try {
                const formatNumber = newValue
                    .replace(/\./g, "")
                    .replace(",", ".");
                const numberValid = Validate.Number(formatNumber);
                //Verificamos si es un numero
                if (!numberValid.response) throw numberValid.message;
                //Si cumple con la condicion de numero decimal cambiamos el formato y activamos
                //el control del boton
                if (numberValid.response) {
                    this.inputValue =
                        Number(formatNumber).toLocaleString("de-DE");
                    this.submitButton.valueValid = true;
                    this.messages.error.valueError = "";
                }
            } catch (errorMessage) {
                this.submitButton.valueValid = false;
                this.messages.error.valueError =
                    Exceptions.CatchWarning(errorMessage);
            }
        },
        //Fechas
        inputHiringDate(newDate) {
            this.validateDate({
                dateToValidate: newDate,
                varInput: "inputHiringDate",
                varError: "hiringDateError",
                validInput: [true, "hiringDateValid"],
            });
        },
        //Nombres y Descripciones
        inputProjectDescription(newString) {
            this.inputProjectDescription = newString.toUpperCase();
            this.validateString({
                limitString: this.LimitString.DESCRIPTION,
                stringToValidate: newString,
                varInput: "inputProjectDescription",
                varError: "projectDescriptionError",
                validInput: [true, "projectDescriptionValid"],
            });
        },
        inputClientAssociated(searchClient) {
            //Activamos la Lista
            this.listControl(
                {
                    nameRef: this.dropDownControl.clients.ref,
                    objectRef: "principal",
                    inputRef: "inputClientAssociated",
                },
                "clients"
            );
            //Tras aplicar el control de la lista, procedemos a verificar que cumpla con el formato correcto
            this.validateString({
                limitString: this.LimitString.NAME,
                stringToValidate: searchClient,
                varInput: "inputClientAssociated",
                varError: "clientAssociatedError",
                validInput: [true, "clientAssociatedValid"],
            });
            //Luego verificamos si corresponde a un dato en la tabla
            this.validateTable(
                {
                    table: "clients",
                    column: "bussiness_name",
                    inputValid: "clientAssociatedValid",
                    errorInput: "clientAssociatedError",
                },
                searchClient
            );
        },
        inputManagerAssociated(searchManager) {
            //Activamos la Lista
            this.listControl(
                {
                    nameRef: this.dropDownControl.manager.ref,
                    objectRef: "distribution",
                    inputRef: "inputManagerAssociated",
                },
                "manager"
            );
            //Tras aplicar el control de la lista, procedemos a verificar que cumpla con el formato correcto
            this.validateString({
                limitString: this.LimitString.NAME,
                stringToValidate: searchManager,
                varInput: "inputManagerAssociated",
                varError: "managerError",
                validInput: [true, "managerValid"],
            });
            //Luego verificamos si corresponde a un dato en la tabla
            this.validateTable(
                {
                    table: "managers",
                    column: "user_name",
                    inputValid: "managerValid",
                    errorInput: "managerError",
                },
                searchManager
            );
        },
        inputPartnerAssociated(searchPartner) {
            //Activamos la Lista
            this.listControl(
                {
                    nameRef: this.dropDownControl.partner.ref,
                    objectRef: "distribution",
                    inputRef: "inputPartnerAssociated",
                },
                "partner"
            );
            //Tras aplicar el control de la lista, procedemos a verificar que cumpla con el formato correcto
            this.validateString({
                limitString: this.LimitString.NAME,
                stringToValidate: searchPartner,
                varInput: "inputPartnerAssociated",
                varError: "partnerError",
                validInput: [true, "partnerValid"],
            });
            //Luego verificamos si corresponde a un dato en la tabla
            this.validateTable(
                {
                    table: "partners",
                    column: "user_name",
                    inputValid: "partnerValid",
                    errorInput: "partnerError",
                },
                searchPartner
            );
        },
        inputQualityPartnerAssociated(searchQualityPartner) {
            //Activamos la Lista
            this.listControl(
                {
                    nameRef: this.dropDownControl.qualityPartner.ref,
                    objectRef: "distribution",
                    inputRef: "inputQualityPartnerAssociated",
                },
                "qualityPartner"
            );
            //Tras aplicar el control de la lista, procedemos a verificar que cumpla con el formato correcto
            this.validateString({
                limitString: this.LimitString.NAME,
                stringToValidate: searchQualityPartner,
                varInput: "inputQualityPartnerAssociated",
                varError: "qualityPartnerError",
                validInput: [true, "qualityPartnerValid"],
            });
            //Luego verificamos si corresponde a un dato en la tabla
            this.validateTable(
                {
                    table: "partners",
                    column: "user_name",
                    inputValid: "qualityPartnerValid",
                    errorInput: "qualityPartnerError",
                },
                searchQualityPartner
            );
        },
        inputDepartments(targetDepartment) {
            if (targetDepartment.length > 0)
                this.submitButton.departmentsValid = true;
            if (targetDepartment.length <= 0)
                this.submitButton.departmentsValid = false;
            //Cargamos el array de transferencia para la estructura de horas
            for (
                let cursorDeparment = 0;
                cursorDeparment < targetDepartment.length;
                cursorDeparment++
            ) {
                //Solo cargamos si no existe esa posicion en el DTO
                //Ubicamos el indice del array
                const indexDepartment = this.dataSelect.departments
                    .map((object) => object.value)
                    .indexOf(targetDepartment[cursorDeparment]);
                //Estructuramos el objeto de departamentos
                const departmentDTO = {
                    departmentId: targetDepartment[cursorDeparment],
                    departmentName:
                        this.dataSelect.departments[indexDepartment].label,
                    managersDepartment: this.dataSelect.managers.filter(
                        (colums) => {
                            return colums.department_id
                                .toString()
                                .includes(
                                    targetDepartment[cursorDeparment].toString()
                                );
                        }
                    ),
                    hoursAssigned: 0,
                    selectManager: 0,
                };
                //Cargamos una nueva fila
                if (
                    cursorDeparment in this.dataSelect.managersPerDepartment ===
                    false
                )
                    this.dataSelect.managersPerDepartment.push(departmentDTO);
                //En caso de que exista un cambio de division. Sobreeescribimos
                if (
                    this.dataSelect.managersPerDepartment[cursorDeparment]
                        .departmentId != targetDepartment[cursorDeparment]
                )
                    this.dataSelect.managersPerDepartment[cursorDeparment] =
                        departmentDTO;
            }
        },
        //Watcher para horas asignadas
        inputHoursAssigned() {
            let hoursCount = 0; //Cuenta el numero de divisiones con horas asignadas
            let departmentCount = 0; //Cuenta el numero de divisiones con gerente seleccionado
            //Hacemos un recorrido de todo el array de divisiones. Para activar la bandera debe existir division seleccionada y hora > 0
            for (
                let cursorDeparment = 0;
                cursorDeparment < this.inputDepartments.length;
                cursorDeparment++
            ) {
                //Gerentes
                if (
                    this.dataSelect.managersPerDepartment[cursorDeparment]
                        .selectManager != 0
                )
                    departmentCount++;
                if (
                    this.dataSelect.managersPerDepartment[cursorDeparment]
                        .selectManager == 0
                )
                    departmentCount--;
                //Horas
                if (
                    this.dataSelect.managersPerDepartment[cursorDeparment]
                        .hoursAssigned != 0
                )
                    hoursCount++;
                if (
                    this.dataSelect.managersPerDepartment[cursorDeparment]
                        .hoursAssigned == 0
                )
                    hoursCount--;
            }

            //Validacion para horas y gerentes
            try {
                if (hoursCount != this.inputDepartments.length)
                    throw "MissingHour";
                if (departmentCount != this.inputDepartments.length)
                    this.submitButton.departmentsValid = false;

                //Paso las validaciones
                if (hoursCount == this.inputDepartments.length) {
                    this.messages.error.hoursAssignedError = "";
                    this.submitButton.hoursAssignedValid = true;
                }
                if (departmentCount == this.inputDepartments.length)
                    this.submitButton.departmentsValid = true;
            } catch (errorMessage) {
                //Mostramos el error
                this.messages.error.hoursAssignedError =
                    Exceptions.CatchWarning(errorMessage);
                this.submitButton.hoursAssignedValid = false;
            }
        },
    },
};

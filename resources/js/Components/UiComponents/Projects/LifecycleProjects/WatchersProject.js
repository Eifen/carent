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
            //Horas y montos adicionales
            this.dataSelect.additionalHours = newEdit.additionalHours;
            this.dataSelect.additionalValues = newEdit.additionalValue;
            //Ultimos valores de tablas de horas y montos adicionales
            this.lastValueId = newEdit.lastValue;
            this.lastHoursId = newEdit.lastHour;

            //Almacenamos el ID del proyecto
            this.projectId = newEdit.project.project_id;

            //Cargamos la informacion de las horas totales adicionales y montos
            this.asignHoursValue(newEdit);

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
                //Verificamos si es un numero mayor a 0
                if (!numberValid.response) throw numberValid.message;
                if (newValue <= 0) throw "IsNotNumber";
                //Si cumple con la condicion de numero decimal cambiamos el formato y activamos
                //el control del boton
                if (numberValid.response) {
                    this.inputValue =
                        Number(formatNumber).toLocaleString("de-DE");
                    this.submitButton.valueValid = true;
                    this.messages.error.valueError = "";
                    //Calculamos la nueva tasa promedio unicamente si el campo de horas es valido
                    if (this.submitButton.hoursAssignedValid === true) {
                        this.controlAverage = this.calculateAverageRate(
                            formatNumber,
                            this.inputHoursAssigned
                        );
                        //Asignamos el resultado al campo correspondiente
                        this.inputAverageRate = this.controlAverage;
                    }
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
            //Inicialmente creamos una copia del array
            const copyManager = this.dataSelect.managersPerDepartment;
            //Vaciamos el array
            this.dataSelect.managersPerDepartment = [];

            //Recorremos el nuevo multiSelect
            targetDepartment.forEach((departmentSelect) => {
                //Obtenemos el indice del usuario por departamento
                const getIndex = this.dataSelect.departments
                    .map((object) => object.value)
                    .indexOf(departmentSelect);

                //Obtenemos el indice de la copia
                const getCopyIndex = copyManager
                    .map((object) => object.departmentId)
                    .indexOf(departmentSelect);
                //Sumamos a un valor vacio para activar la validación del campo de horas totales
                this.inputHoursAssigned =
                    parseInt(this.inputHoursAssigned) + " ";

                //Si existe el indice, hacemos push de esa posicion de la copia. Caso contrario hacemos push con nuevos valores
                if (getCopyIndex !== -1) {
                    this.dataSelect.managersPerDepartment.push(
                        copyManager[getCopyIndex]
                    );
                } else {
                    this.dataSelect.managersPerDepartment.push({
                        departmentId: departmentSelect,
                        departmentName:
                            this.dataSelect.departments[getIndex].label,
                        managersDepartment: this.dataSelect.managers.filter(
                            (colums) => {
                                return colums.department_id
                                    .toString()
                                    .includes(departmentSelect.toString());
                            }
                        ),
                        hoursAssigned: "",
                        selectManager: 0,
                    });
                    //Desactivamos la validacion de horas
                    this.submitButton.hoursAssignedValid = false;
                }
            });

            //Ejecutamos el conteo de horas
            this.totalHours(this.dataSelect.managersPerDepartment);
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
                        .hoursAssigned != 0 &&
                    this.dataSelect.managersPerDepartment[cursorDeparment]
                        .hoursAssigned != ""
                )
                    hoursCount++;
                if (
                    this.dataSelect.managersPerDepartment[cursorDeparment]
                        .hoursAssigned == 0 ||
                    this.dataSelect.managersPerDepartment[cursorDeparment]
                        .hoursAssigned == ""
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
                    //Calculamos la nueva tasa promedio unicamente si el campo de monto es valido
                    if (this.submitButton.valueValid === true) {
                        this.controlAverage = this.calculateAverageRate(
                            this.inputValue
                                .replace(/\./g, "")
                                .replace(",", "."),
                            this.inputHoursAssigned
                        );
                        //Asignamos el resultado
                        this.inputAverageRate = this.controlAverage;
                    }
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
        //Watcher para campo de valores adicionales
        additionalInput(newValue, oldValue) {
            //Verificamos si es un número
            try {
                const validNumber = Validate.Number(newValue);
                if (!validNumber.response) throw validNumber.message;
                if (newValue.length === 0) this.additionalInput = 0;
            } catch (error) {
                let countDot = 1; //Contador que cuenta los puntos que tiene el string
                //Volvemos al valor anterior en caso de fallo
                if (newValue.includes(".")) {
                    countDot--;
                }
                //Volvemos al valor anterior solo en caso que no exista un solo .
                if (countDot === 0) {
                    if (!Validate.Number(newValue.replace(/\./, "")).response) {
                        this.additionalInput = oldValue;
                    }
                } else if (countDot !== 0) {
                    this.additionalInput = oldValue;
                }
            }
        },
        //Tasa promedio
        inputAverageRate(newAverage) {
            try {
                //Validamos que el campo coincida con el almacenado en controlAverage
                if (newAverage !== this.controlAverage)
                    throw "El valor no coincide con el calculado";

                //Si pasa las validaciones
                if (newAverage === this.controlAverage) {
                    //Activamos la bandera
                    this.submitButton.averageRateValid = true;
                    this.messages.error.averageRateError = "";
                }
            } catch (errorMessage) {
                this.messages.error.averageRateError = errorMessage;
                this.submitButton.averageRateValid = false;
            }
        },
    },
};

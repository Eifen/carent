import { Validate } from "@/Models/ValidateModel";
import { Exceptions } from "@/Excepciones/Excepciones";

export const projectWatchers = {
    watch: {
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
                        this.controlAverage != 0
                            ? (this.inputAverageRate = this.controlAverage)
                            : (this.submitButton.averageRateValid = true);
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
        inputDepartments(targetDepartment, oldTarget) {
            console.log(targetDepartment, oldTarget)
            if (targetDepartment.length > 0)
                this.submitButton.departmentsValid = true;
            if (targetDepartment.length <= 0)
                this.submitButton.departmentsValid = false;
            let copyArray = targetDepartment
            //Inicialmente creamos una copia del array
            const copyManager = this.dataSelect.managersPerDepartment;
            //Si estamos en edicion verificamos si el valor eliminado no tiene horas cargadas
            if (this.isEdit && oldTarget.length > targetDepartment.length) {
                let departmentRemove = 0;
                oldTarget.forEach(department_id => {
                    const detectRemove = targetDepartment.indexOf(department_id);
                    if (detectRemove == -1) departmentRemove = department_id
                })
                //Si se removio un departamento, revisamos si tiene horas cargadas
                if (departmentRemove != 0) {
                    const indexManager = copyManager.map(object => object.departmentId).indexOf(departmentRemove)
                    if (copyManager[indexManager].registerHour != 0) {
                        alert(`No se puede eliminar el departamento ${copyManager[indexManager].departmentName} por que ya tiene horas cargadas`)
                        //Colocamos el array anterior
                        oldTarget.forEach((department_id, pos) => {
                            this.inputDepartments[pos] = department_id
                        })
                    }
                }
            }
            console.log(copyArray)
            //Vaciamos el array
            this.dataSelect.managersPerDepartment = [];

            //Recorremos el nuevo multiSelect
            copyArray.forEach((departmentSelect) => {
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
                        registerHour: 0
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
                        this.controlAverage != 0
                            ? (this.inputAverageRate = this.controlAverage)
                            : (this.submitButton.averageRateValid = true);
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

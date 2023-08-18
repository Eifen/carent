import { Exceptions } from "@/Excepciones/Excepciones";
export const projectMethods = {
    methods: {
        /**
         * Metodo que envia la data del formulario  al componente padre
         */
        projectEmit() {
            //Almacenamos los indices de cada valor
            const indexTable = {
                client: this.dataSelect.clients
                    .map((object) => object.bussiness_name)
                    .indexOf(this.inputClientAssociated),
                manager: this.dataSelect.managers
                    .map((object) => object.user_name)
                    .indexOf(this.inputManagerAssociated),
                partner: this.dataSelect.partners
                    .map((object) => object.user_name)
                    .indexOf(this.inputPartnerAssociated),
                qualityPartner: this.dataSelect.partners
                    .map((object) => object.user_name)
                    .indexOf(this.inputQualityPartnerAssociated),
            };
            //Configuramos los departamentos
            let departmentDTO = [];
            this.inputDepartments.forEach((department, cursor) => {
                departmentDTO[cursor] =
                    this.dataSelect.managersPerDepartment[cursor];
            });
            //Pasamos los parametros a analizar
            let paramsToEmit = {
                projectDescription: this.inputProjectDescription,
                clientId: this.dataSelect.clients[indexTable.client].client_id,
                statusId: this.inputStatusSelect,
                managerId: this.dataSelect.managers[indexTable.manager].user_id,
                partnerId: this.dataSelect.partners[indexTable.partner].user_id,
                qualityPartnerId:
                    this.dataSelect.partners[indexTable.qualityPartner].user_id,
                currencyId: this.inputCurrenciesSelect,
                companyId: this.inputCompaniesSelect,
                hiringDate: this.inputHiringDate,
                departments: departmentDTO,
                projectValue: this.inputValue
                    .replace(/\./, "")
                    .replace(/,/, "."),
                averageRate: this.inputAverageRate
                    .replace(/\./, "")
                    .replace(/,/, "."),
            };

            //Preparamos los parametros de update en caso de actualizar
            let paramsToUpdate = {
                projectId: this.projectId,
                additionalHours: this.dataSelect.additionalHours,
                additionalValues: this.dataSelect.additionalValues,
            };

            //Si estamos en edición, unimos los dos objetos
            if (this.isEdit)
                paramsToEmit = { ...paramsToEmit, ...paramsToUpdate };
            this.$emit("submit-form", paramsToEmit);
        },
        /**
         * Metodo que controla el estado de la lista
         * @param {Object} refTarget Objeto que captura la informacion del input, debe tener las siguientes propiedades
         * @property {String} nameRef Nombre del ref a que se hace referencia
         * @property {String} objectRef Nombre del objeto que almacena las referencias de ese componente
         * @property {Strinf} inputRef Nombre del v-model donde se almacena la informacion del ref
         * @param {*} inputTarget Captura que tipo de campo es para controlarlo con noInput
         * Puede ser cliente, socio, gerente, entre otros
         * @param {String} refsInfo Direcciona al Refs del componente hijo ubicado en la variable childsRefs, ejemplo "principal"
         */
        listControl(refsInfo, inputTarget) {
            //Controla el estado de la lista
            if (
                this.childsRefs[refsInfo.objectRef][refsInfo.nameRef] !==
                document.activeElement
            ) {
                this.dropDownControl[inputTarget].noInput = false; //Lo desactiva si el input pierde el focus
            } else {
                this.dropDownControl[inputTarget].noInput = true; //Lo activa si el input tiene focus y no esta vacio
                if (this[refsInfo.inputRef].length == 0)
                    this.dropDownControl[inputTarget].noInput = false; //Desactiva si input esta vacio
            }
        },
        /**
         * Metodo que si la informacion seleccionada coincide con un valor en la tabla
         * @param {Object} objectTarget Objeto de configuracion que debe recibir las siguientes propiedades:
         * @property {String} table: Indica a que tabla es redireccionando
         * @property {String} column: Columna de la tabla que va a comparar
         * @property {String} inputValid: Variable de control de validaciones
         * @property {String} errorInput: Variable que controla el mensaje de error
         * @param {String} valueToCompare Captura la informacion del string a comparar
         */
        validateTable(objectTarget, valueToCompare) {
            try {
                const infoDTO = this.dataSelect[objectTarget.table].filter(
                    (columns) => {
                        return columns[objectTarget.column]
                            .toString()
                            .toLowerCase()
                            .includes(valueToCompare.toString().toLowerCase());
                    }
                );
                //Si no pertenece, error
                if (infoDTO.length == 0) throw "NoRefFound";
                //En caso que pertezca, desactivamos el mensaje de error
                if (infoDTO.length != 0)
                    this.messages.error[objectTarget.errorInput] = "";
            } catch (errorMessage) {
                this.submitButton[objectTarget.inputValid] = false;
                this.messages.error[objectTarget.errorInput] =
                    Exceptions.CatchWarning(errorMessage);
            }
        },
        //Insertado de fecha
        insertDate(dateDTO) {
            this.inputHiringDate = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`;
        },
        /**
         * Metodo que se encarga sumar las horas totales del proyecto
         * @param {*} infoDepartments Objeto donde esta almacenada la informacion de las divisiones
         */
        totalHours(infoDepartments) {
            this.inputHoursAssigned = 0; //Reiniciamos el contador
            //Recorremos el arrray y sumamos en el v-model de horas totales
            infoDepartments.forEach((department, cursor) => {
                const numberValid = new RegExp("^([0-9]*)$");
                const testNumber = numberValid.test(
                    department["hoursAssigned"]
                );

                //Si es un numero, sumamos
                if (testNumber && department["hoursAssigned"].length != 0) {
                    this.inputHoursAssigned =
                        parseInt(this.inputHoursAssigned) +
                        parseInt(department["hoursAssigned"]);
                }

                //Si no es un numero, limpiamos
                if (!testNumber) {
                    department["hoursAssigned"] = "";
                    //Mostramos el error
                    this.messages.error.hoursAssignedError =
                        Exceptions.CatchWarning("MissingHour");
                    this.submitButton.hoursAssignedValid = false;
                }
            });
        },
        /**
         * Metodo que captura el evento emitido por el componente hijo del modal
         * @prop {Object} paramsCatch Objeto que recibe de la emicion de los componentes hijos, tienen estas propiedades
         * @prop {array} arrayToAssign Captura la información del array usado para el modal
         * @prop {String} arrayTarget String que direcciona a que array del dataSelect hace referencia
         * @prop {String[]} refs Array de referencia que indica el nombre de cada propiedad
         */
        reAsignInfo(paramsCatch) {
            let objectDTO = {}; //Objeto que se inicializa antes de insertar en cada fila del asignDTO

            //Creamos las propiedades del objeto
            paramsCatch.arrayToAssign.forEach((object, cursor) => {
                const objectValues = Object.values(object); //Crea un array de los valores del objeto

                //Creamos las nuevas propiedades del objeto
                paramsCatch.refs.forEach((ref, cursorRef) => {
                    Object.defineProperty(objectDTO, ref, {
                        value: objectValues[cursorRef],
                        writable: true,
                        configurable: true,
                        enumerable: true,
                    });
                });

                //Buscamos si el array coincide en el codigo
                const findIndex = this.dataSelect[
                    paramsCatch.arrayTarget
                ].findIndex(
                    (object) =>
                        object[paramsCatch.refs[0]] ===
                        objectDTO[paramsCatch.refs[0]]
                );
                //Si encuentra el indice, reemplazamos, caso contrario insertamos
                if (findIndex !== -1) {
                    this.dataSelect[paramsCatch.arrayTarget][findIndex] =
                        objectDTO;
                } else if (findIndex === -1) {
                    this.dataSelect[paramsCatch.arrayTarget].push(objectDTO);
                }

                //Reinicializamos el objecto
                objectDTO = {};
            });
            //Calculamos el total de montos y / o horas adicionales
            this.asignHoursValue({
                additionalHours: this.dataSelect.additionalHours,
                additionalValue: this.dataSelect.additionalValues,
            });
        },
        /**
         * Metodo que se encarga de sumar el total de horas y montos adicionales que esten ACTIVOS (status_id / estatus = 1)
         * @param {Object} newEdit recibe el objeto que tiene contenido horas y montos adicionales en sus propiedades
         */
        asignHoursValue(newEdit) {
            //Reinicializamos los input
            this.inputAdditionalHours = 0;
            this.inputAdditionalValue = 0;

            //Horas
            if (newEdit.additionalHours.length != 0) {
                newEdit.additionalHours.forEach((hour) => {
                    const additionalHourSum =
                        parseInt(this.inputAdditionalHours) +
                        parseInt(hour["additional_hour"]);
                    this.inputAdditionalHours = additionalHourSum;
                });
            }

            //Montos
            if (newEdit.additionalValue.length != 0) {
                newEdit.additionalValue.forEach((value) => {
                    const additionalValueSum =
                        parseFloat(this.inputAdditionalValue) +
                        parseFloat(value["aditional_project_value"]);
                    this.inputAdditionalValue = additionalValueSum;
                });
            }

            //Formateamos
            this.inputAdditionalHours = Number(
                this.inputAdditionalHours
            ).toLocaleString("de-DE");
            this.inputAdditionalValue = Number(
                this.inputAdditionalValue
            ).toLocaleString("de-DE");
        },
        /**
         * Metodo que calcula la tasa promedio del proyecto
         * @param {float} projectValue Monto del proyecto
         * @param {int} totalHours horas totales del proyecto
         * @return {float} Retorna un valor númerico correspondiente a la tasa promedio
         */
        calculateAverageRate(projectValue, totalHours) {
            return Number(projectValue / totalHours).toLocaleString("de-DE");
        },
    },
};

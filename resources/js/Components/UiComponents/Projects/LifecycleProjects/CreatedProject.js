/**
 * Hook created para formulario de proyectos
 * @param {*} self Herda la data del FormProjects
 */
export const createdMixin = (self) => {
    self.inputWatchers = [
        {
            propiedades: [
                "inputStatusSelect",
                "inputCurrenciesSelect",
                "inputCompaniesSelect",
            ],
            watch: (actualTarget) => {
                //Control de banderas
                if (actualTarget >= 1) {
                    if (self.inputStatusSelect != 0)
                        self.submitButton.statusValid = true;
                    if (self.inputCurrenciesSelect != 0)
                        self.submitButton.currenciesValid = true;
                    if (self.inputCompaniesSelect != 0)
                        self.submitButton.companiesValid = true;
                } else {
                    //Control de desactivar banderas
                    if (self.inputStatusSelect == 0)
                        self.submitButton.statusValid = false;
                    if (self.inputCurrenciesSelect == 0)
                        self.submitButton.currenciesValid = false;
                    if (self.inputCompaniesSelect == 0)
                        self.submitButton.companiesValid = false;
                }
            },
        },
    ];
    //Traer a data a traves de solicitud POST
    axios
        .post("/projects/get-params-inits")
        .then((request) => {
            //Asignamos a los select correspondientes
            self.dataSelect.currencies = request.data.currencies;
            self.dataSelect.companies = request.data.companies;
            self.dataSelect.status = request.data.status;
            self.dataSelect.clients = request.data.clients;
            self.dataSelect.partners = request.data.partners;
            self.dataSelect.managers = request.data.managers;

            //Recorremos el array para hacer un formato de objeto
            for (
                let cursor = 0;
                cursor < request.data.departments.length;
                cursor++
            ) {
                if (cursor != 0) {
                    self.dataSelect.departments[cursor] = {
                        value: request.data.departments[cursor].department_id,
                        label: request.data.departments[cursor].department_name,
                        disabled: false,
                    };
                } else {
                    self.dataSelect.departments[cursor] = {
                        value: request.data.departments[cursor].department_id,
                        label: "Seleccione una o varias divisiones",
                        disabled: true,
                    };
                }
            }

            //Cambiamos el estado del monto para que muestre un mensaje de error
            self.inputValue = "";

            //Revisamos si edit existe
            if (self.$props.isEdit) {
                //Obtenemos los indices de clientes y usuarios
                const indexDTO = {
                    client: self.dataSelect.clients
                        .map((object) => object.client_id)
                        .indexOf(self.$props.dataEdit.project.client_id),
                    partner: self.dataSelect.partners
                        .map((object) => object.user_id)
                        .indexOf(self.$props.dataEdit.project.partner_id),
                    qualityPartner: self.dataSelect.partners
                        .map((object) => object.user_id)
                        .indexOf(
                            self.$props.dataEdit.project.quality_partner_id
                        ),
                    manager: self.dataSelect.managers
                        .map((object) => object.user_id)
                        .indexOf(self.$props.dataEdit.project.manager_id),
                };
                //Distribuimos la informacion
                self.inputStatusSelect = self.$props.dataEdit.project.status_id; //Posicion del estado del proyecto
                self.inputCurrenciesSelect =
                    self.$props.dataEdit.project.currency_id; //Select del tipo de moneda
                self.inputCompaniesSelect =
                    self.$props.dataEdit.project.company_id; //Select del tipo de empresa
                self.inputProjectDescription =
                    self.$props.dataEdit.project.project_description; //String que de la descripcion del proyecto
                self.inputClientAssociated =
                    self.dataSelect.clients[indexDTO.client].bussiness_name; //String que almacena la información del cliente asociado
                self.inputManagerAssociated =
                    self.dataSelect.managers[indexDTO.manager].user_name; //String que almacena la información del manager asociado
                self.inputPartnerAssociated =
                    self.dataSelect.partners[indexDTO.partner].user_name; //String que almacena la información del socio asociado
                self.inputQualityPartnerAssociated =
                    self.dataSelect.partners[indexDTO.qualityPartner].user_name; //String que almacena la información del socio de calidad asociado
                self.inputHiringDate = self.$props.dataEdit.project.hiring_date; //Fecha de contratacion
                self.inputValue = Number(
                    self.$props.dataEdit.project.project_value
                ).toLocaleString("de-DE"); //Monto del proyecto
                //Horas y montos adicionales
                self.dataSelect.additionalHours =
                    self.$props.dataEdit.additionalHours;
                self.dataSelect.additionalValues =
                    self.$props.dataEdit.additionalValue;
                //Ultimos valores de tablas de horas y montos adicionales
                self.lastValueId = self.$props.dataEdit.lastValue;
                self.lastHoursId = self.$props.dataEdit.lastHour;

                //Almacenamos el ID del proyecto
                self.projectId = self.$props.dataEdit.project.project_id;

                //Cargamos la informacion de las horas totales adicionales y montos
                self.asignHoursValue(self.$props.dataEdit);

                //Cargamos la informacion de los departamentos
                for (
                    let countDepartment = 0;
                    countDepartment < self.$props.dataEdit.departments.length;
                    countDepartment++
                ) {
                    //Id del departamento
                    const indexDepartment = self.dataSelect.departments
                        .map((object) => object.value)
                        .indexOf(
                            self.$props.dataEdit.departments[countDepartment]
                                .department_id
                        );
                    const indexProjectHour = self.$props.dataEdit.projectsHours.map(object => object.department_id).indexOf(self.$props.dataEdit.departments[countDepartment].department_id)
                    self.inputDepartments.push(
                        self.$props.dataEdit.departments[countDepartment]
                            .department_id
                    );

                    //Llenamos el multiselect
                    self.dataSelect.managersPerDepartment.push({
                        departmentId:
                            self.$props.dataEdit.departments[countDepartment]
                                .department_id,
                        departmentName:
                            self.dataSelect.departments[indexDepartment].label,
                        managersDepartment: self.dataSelect.managers.filter(
                            (colums) => {
                                return colums.department_id
                                    .toString()
                                    .includes(
                                        self.$props.dataEdit.departments[
                                            countDepartment
                                        ].department_id.toString()
                                    );
                            }
                        ),
                        hoursAssigned:
                            self.$props.dataEdit.departments[countDepartment]
                                .hours_assigned,
                        selectManager:
                            self.$props.dataEdit.departments[countDepartment]
                                .manager_id,
                        registerHour: indexProjectHour != -1 ? Number(self.$props.dataEdit.projectsHours[indexProjectHour].total_hours).toLocaleString('de-DE') : 0,
                    });

                    //Sumamos las horas asignadas
                    self.inputHoursAssigned =
                        parseInt(self.inputHoursAssigned) +
                        parseInt(
                            self.$props.dataEdit.departments[countDepartment]
                                .hours_assigned
                        );
                }
            }
        })
        .catch((error) => {
            console.error(error);
        });
};

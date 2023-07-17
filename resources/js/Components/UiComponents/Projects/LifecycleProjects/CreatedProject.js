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
            if (self.isEdit) self.$emit("init-project");
        })
        .catch((error) => {
            console.error(error);
        });
};

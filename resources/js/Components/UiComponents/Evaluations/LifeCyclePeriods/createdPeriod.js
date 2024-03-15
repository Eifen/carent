/**
 * Hook created para formulario de clientes
 * @param {*} self Hereda la data de su padre
 */


export const createdMixin = (self) => {
    // //Espacio de creacion de Watchers globales (DEFINE CUANDO SE CREA EL BOTON DE CREAR PERIODO)
    self.inputWatchers = [
        {
            propiedades: [
                "inputPeriodo",
                "inputFechaDesde",
                "inputFechaHasta",
                "inputTipoSelect",
                "inputMetodoSelect", //Select de Socios
                "inputStatusSelect", //Select del Status
                "inputPeriodoDescripcion",
                "inputObservacion",
            ],
            watch: () => {
                //Control de banderas
                if (self.inputPeriodo != 0)
                    self.submitButton.periodoValid = true;
                //Control de banderas
                if (self.inputFechaDesde != 0)
                    self.submitButton.fechaDesdeValid = true;
                //Control de banderas
                if (self.inputFechaHasta != 0)
                    self.submitButton.fechaHastaValid = true;
                //Control de banderas
                if (self.inputTipoSelect != 0)
                    self.submitButton.selectTipo = true;
                //Control de banderas
                if (self.inputMetodoSelect != 0)
                    self.submitButton.selectMetodo = true;
                //Control de banderas
                if (self.inputStatusSelect != 0)
                    self.submitButton.selectStatus = true;


                //Control de desactivar
                if (self.inputPeriodo == 0)
                    self.submitButton.periodoValid = false;
                //Control de desactivar
                if (self.inputFechaDesde == 0)
                    self.submitButton.fechaDesdeValid = false;
                //Control de desactivar
                if (self.inputFechaHasta == 0)
                    self.submitButton.fechaHastaValid = false;
                //Control de desactivar
                if (self.inputTipoSelect == 0)
                    self.submitButton.selectTipo = false;
                //Control de desactivar
                if (self.inputMetodoSelect == 0)
                    self.submitButton.selectMetodo = false;
                //Control de desactivar
                if (self.inputStatusSelect == 0)
                    self.submitButton.selectStatus = false;

            },
        },
    ];

    //Axios
    axios
        .post("/evaluaciones/periodos/getParamsInits")
        .then((request) => {
            if (request.status !== 200) throw request;
            self.dataSelect.tipos = request.data.dataTipos;
            self.dataSelect.metodos = request.data.dataMetodos;
            self.dataSelect.periodos = request.data.dataPeriodos;
            self.dataSelect.status = request.data.dataStatus;
            //Revisamos si edit existe
            if (self.$props.isEdit) {
                console.log(self.$props.dataEdit);
                //Asignamos la data
                self.inputPeriodo = self.$props.dataEdit.evaluation_period;
                self.inputPeriodoId = self.$props.dataEdit.evaluation_period_id;
                self.inputFechaDesde = self.$props.dataEdit.evaluation_period_date_from;
                self.inputFechaHasta = self.$props.dataEdit.evaluation_period_date_until;
                self.inputTipoSelect = self.$props.dataEdit.evaluation_type_id;//buscar data
                self.inputMetodoSelect = self.$props.dataEdit.evaluation_method_id;//buscar data
                self.inputStatusSelect = self.$props.dataEdit.status_id;
                self.inputPeriodoDescripcion = self.$props.dataEdit.evaluation_period_description;
                self.inputObservacion = self.$props.dataEdit.evaluation_period_observation;
            }
        })
        .catch((error) => {
            console.error(error);
        });
};

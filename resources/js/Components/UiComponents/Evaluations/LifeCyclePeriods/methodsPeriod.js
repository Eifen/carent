export const evaluationMethods = {

    methods: {
        /**
         * Metodo que envia la data del formulario  al componente padre
         */
        evaluationEmit() {
            //Pasamos los parametros a analizar
            let paramsToEmit = {
                "Periodo": this.inputPeriodo,
                "FechaDesde": this.inputFechaDesde,
                "FechaHasta": this.inputFechaHasta,
                "PeriodoDescripcion": this.inputPeriodoDescripcion,
                "PeriodoObservacion": this.inputObservacion,
                "IdTipo": this.inputTipoSelect,
                "IdMetodo": this.inputMetodoSelect,
                "IdStatus": this.inputStatusSelect

                // METODO PARA HARDCODEAR
                // "Periodo": "primer periodo",
                // "FechaDesde": "2023-10-10",
                // "FechaHasta": "2023-10-10",
                // "PeriodoDescripcion": "prueba",
                // "PeriodoObservacion": "prueba",
                // "IdTipo": "1",
                // "IdMetodo": "1",
                // "IdStatus": "1"
            };

            console.log(paramsToEmit);

            this.$emit("submit-form", paramsToEmit);

        },
    },
};

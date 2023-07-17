/**
 * Constante que configura metodos personalizados para la información de proyectos
 */
export const projectInfoMethods = {
    methods: {
        /**
         * Metodo que retorno el valor total de las horas asignadas al proyecto
         * @return {int} Retorna un numero correspondiente a las horas totales
         */
        totalHoursAssigned() {
            //Inicializamos el contador
            let countHours = 0;
            //Recorremos el array y contamos
            this.previewProjectInfo["departments"].forEach((department) => {
                countHours = countHours + department["hours_assigned"];
            });

            //Retornamos el resultante
            return countHours;
        },
        /**
         * Metodo que devuelve un valor numerico sea float o number con el total de horas adicionales o montos adicionales
         * @param {int} typeAdditional Controla el tipo de proceso. 1: Horas adicionales, 2: Montos Adicionales
         */
        totalAdditionalAssigned(typeAdditional) {
            //Inicializamos el contador
            let countAdditional = 0;
            //El total corresponde a unicamente si la longitud del array es > 0
            switch (typeAdditional) {
                case 1:
                    if (this.previewProjectInfo.additionalHours.length != 0) {
                        this.previewProjectInfo.additionalHours.forEach(
                            (hour) => {
                                countAdditional =
                                    countAdditional +
                                    parseInt(hour["additional_hour"]);
                            }
                        );
                    }
                    break;

                case 2:
                    if (this.previewProjectInfo.additionalValue.length != 0) {
                        this.previewProjectInfo.additionalValue.forEach(
                            (value) => {
                                countAdditional =
                                    countAdditional +
                                    parseFloat(
                                        value["aditional_project_value"]
                                    );
                            }
                        );
                    }
                    break;
            }

            //Retornamos el resultante
            return countAdditional;
        },
    },
};

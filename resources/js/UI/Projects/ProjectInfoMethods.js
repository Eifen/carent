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
        /**
         * Metodo que configura el array de objetos de la barra de progreso
         * @param {Object} hoursResponse Recibe el objeto resultante desde el request de Axios
         */
        showProgressHours(hoursResponse) {
            let classBar = ""; //Variable que almacena la clase de la barra
            this.progressBarInfo = [];
            //Recorremos el array de departamentos, divisiones, areas
            hoursResponse.departments.forEach((department) => {
                const getLoadHour = this.showLoadHours(
                    department.department_id
                ); //Horas cargadas
                const getEstimatedHour = this.showEstimatedHours(
                    department.hours_assigned,
                    department.department_id
                ); //Horas estimadas
                //Calculamos el porcentaje
                const percentBar = this.getPercentHour(
                    getLoadHour,
                    getEstimatedHour
                );
                //Representamos el tipo de barra
                switch (true) {
                    //Entre 0 y 25 %
                    case percentBar > 0 && percentBar <= 25:
                        classBar = "progress-bar bg-danger text-dark";
                        break;

                    //Entre 25 y 75 %
                    case percentBar > 25 && percentBar <= 75:
                        classBar = "progress-bar bg-warning text-dark";
                        break;

                    //Entre 75 y 100%
                    case percentBar > 75 && percentBar <= 100:
                        classBar = "progress-bar bg-success text-dark";
                        break;
                }
                //Cargamos el array
                this.progressBarInfo.push({
                    class: classBar,
                    style: `width: ${percentBar}%`,
                    percent: `${percentBar}%`,
                    department_id: department.department_id,
                    department_name: department.department_name,
                    loadHour: parseFloat(getLoadHour) > parseFloat(getEstimatedHour) ? getEstimatedHour : getLoadHour,
                    estimatedHour: getEstimatedHour,
                });
            });
        },
        /**
         * Metodo que se encarga de mostrar las horas cargadas por division
         * @param {int} departmentId El id del departmento
         * @return {int} Devuelve un valor entero de las horas cargadas hasta el momento
         */
        showLoadHours(departmentId) {
            const findIndex = this.previewProjectInfo.projectsHours
                .map((hour) => hour.department_id)
                .indexOf(departmentId);

            //Retornamos el valor de la hora en funcion del indice
            if (findIndex != -1) {
                return this.previewProjectInfo.projectsHours[findIndex]
                    .total_hours; //Si encuentra es porque se han cargado horas
            } else {
                return 0; //Si no encuentra nada es porque no se han cargado horas
            }
        },
        /**
         * Metodo que se encarga de mostrar las horas totales por division
         * @param {int} assignedHour Hora asignada
         * @param {int} departmentId Id del departamento
         */
        showEstimatedHours(assignedHour, departmentId) {
            let additionalCount = 0;
            //Nos aseguramos que el array de horas adicionales no este vacio
            try {
                if (this.previewProjectInfo.additionalHours.length == 0)
                    throw "EmptyArray";
                //Si pasa la validacion sumamos las horas por departamento
                this.previewProjectInfo.additionalHours.forEach((hour) => {
                    additionalCount =
                        additionalCount +
                        (hour.department_id === departmentId
                            ? hour["additional_hour"]
                            : 0);
                });

                //Retornamos la suma
                return assignedHour + additionalCount;
            } catch (error) {
                //Si no tiene valores horas adicionales solo retornamos la hora
                return assignedHour;
            }
        },

        /**
         * Metodo que calcula el porcentaje de la barra
         * @param {int} loadHour Hora cargada
         * @param {int} estimatedHour Hora estimada
         */
        getPercentHour(loadHour, estimatedHour) {
            //Hacemos la division
            const getPercent =
                (parseFloat(loadHour) * 100) / parseFloat(estimatedHour);
            return getPercent >= 100 ? 100 : getPercent.toFixed(0);
        },
        totalHoursRegistered() {
            let totalHours = 0
            this.previewProjectInfo["projectsHours"].forEach(project => {
                const findAssigned = this.previewProjectInfo["departments"].find(department => department.department_id === project.department_id)
                const hours = parseFloat(project.total_hours) > parseFloat(findAssigned.hours_assigned) ? parseFloat(findAssigned.hours_assigned) : parseFloat(project.total_hours)
                totalHours += parseFloat(hours)
            })

            return totalHours;
        },
        averageFinal() {
            if (parseFloat(this.totalHoursRegistered()) == 0) return '0';
            return (parseFloat(this.previewProjectInfo.project.project_value) + this.totalAdditionalAssigned(2)) / (parseFloat(this.totalHoursRegistered()))
        }
    },
};

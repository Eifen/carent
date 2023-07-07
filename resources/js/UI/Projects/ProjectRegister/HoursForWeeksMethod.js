export const hoursForWeeksMethod = {
    methods: {
        /**
         * Metodo que distribuye la información de las horas a lo largo del CRUD
         * @param {Object} dateCompare Almacena el intervalo de fechas de la semana hábil
         * @param {Object} loadProjectHours Objecto comparativo de los datos almacenados en la BDD sobre las horas cargadas a proyectos
         * @param {Object} loadAdminHours Objecto comparativo de los datos almacenados en la BDD sobre las horas cargadas administrativas
         */
        hoursWeeksDistribution(dateCompare, loadProjectHours, loadAdminHours) {
            this.listDayData = [];
            //Distribuimos los días de la semana
            console.log(dateCompare, loadProjectHours, loadAdminHours);
            dateCompare.forEach((day) => {
                this.listDayData.push({
                    name: this.dayNames[day["day_of_week"]],
                    date: day["register_date"],
                });
            });
            //Asignamos las horas a proyectos
            this.listProjectHourData = loadProjectHours;

            //Asignamos las horas administrativas
            this.listAdminHourData = loadAdminHours;

            //Cargamos la informacion del multiselect
            this.projectAssociatedToCharge.forEach((project) => {
                //Hacemos push en el objeto de multiselect
                this.inputProjectsMultiSelect.push({
                    value: project.user_assigned_id,
                    label:
                        project.project_description +
                        " PARA " +
                        project.bussiness_name,
                    disabled: false,
                });
            });
        },
        /**
         * Metodo que se encarga de contar el total de horas a proyecto
         * @param {*} projectAssignedId Captura el user_assigned_id asociado (Asignacion de horas)
         * @return {Number} retorna el total de horas asignadas a proyectos
         */
        countProjectHours(projectAssignedId) {
            let countProject = 0; //Contador de horas
            this.listProjectHourData.forEach((project) => {
                if (project.user_assigned_id === projectAssignedId) {
                    //Solo sumamos si la id coincide
                    countProject = countProject + project.register_hour;
                }
            });

            return countProject; //Valor resultante
        },
    },
    watch: {
        /**
         * Watcher que se encarga de capturar los cambios en el selector
         */
        inputProjectSelect(newMultiSelect) {
            let hoursLoad = 0; // Variable de control para las horas cargadas
            this.gridProjectInfo = [];

            //Obtenemos los indices
            newMultiSelect.forEach((select) => {
                //Cargamos las horas a proyectos
                const getLoadIndex = this.listProjectHourData
                    .map((projectAssign) => {
                        return projectAssign.user_assigned_id;
                    })
                    .indexOf(select);
                //Obtenemos el indice del proyecto
                const getProjectIndex = this.projectAssociatedToCharge
                    .map((project) => {
                        return project.user_assigned_id;
                    })
                    .indexOf(select);

                //Obtenemos el indice del multiselect
                const getSelectIndex = this.inputProjectsMultiSelect
                    .map((project) => {
                        return project.value;
                    })
                    .indexOf(select);

                //Si el indice de horas cargadas coincide, contamos todas sus horas cargadas
                if (getLoadIndex !== -1)
                    hoursLoad = this.countProjectHours(select);

                //Cargamos la informacion de la grilla
                this.gridProjectInfo.push({
                    userId: select,
                    hoursAssigned:
                        this.projectAssociatedToCharge[getProjectIndex]
                            .assigned_hours,
                    hoursLoad: hoursLoad,
                    description:
                        this.inputProjectsMultiSelect[getSelectIndex].label,
                });

                //Limpiamos el contador
                hoursLoad = 0;
            });

            console.log(this.gridProjectInfo);
        },
    },
};

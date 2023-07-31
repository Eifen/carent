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

            //Cargamos la informacion del multiselect de horas administrativas
            this.conceptHourArray.forEach((admin) => {
                //Hacemos push en el objeto
                this.inputAdminMultiSelect.push({
                    value: admin.admin_hours_id,
                    label: admin.concept_description.toString().toUpperCase(),
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
                    countProject =
                        countProject + parseFloat(project.register_hour);
                }
            });

            return countProject; //Valor resultante
        },
    },
    watch: {
        /**
         * Watcher que se encarga de capturar los cambios en el selector
         */
        inputProjectSelect: {
            handler(newMultiSelect) {
                let hoursLoad = 0; // Variable de control para las horas cargadas
                this.gridProjectInfo = [];

                //Obtenemos los indices
                newMultiSelect.forEach((select) => {
                    //Obtenemos el indice del proyecto
                    const getProjectIndex = this.projectAssociatedToCharge
                        .map((project) => {
                            return project.user_assigned_id;
                        })
                        .indexOf(select);

                    //Horas totales cargadas
                    hoursLoad = this.countProjectHours(select);

                    //Cargamos la informacion de la grilla
                    this.gridProjectInfo.push({
                        projectAssignedId: select, //El Id del proyecto asignado
                        clientName:
                            this.projectAssociatedToCharge[getProjectIndex]
                                .bussiness_name, // Cliente asociado al proyecto
                        hoursAssigned:
                            this.projectAssociatedToCharge[getProjectIndex]
                                .assigned_hours, //Horas totales asignadas
                        hoursLoad: hoursLoad, //Horas totales cargadas
                        description:
                            this.projectAssociatedToCharge[getProjectIndex]
                                .project_description, //Nombre del proyecto
                        hoursDiff:
                            this.projectAssociatedToCharge[getProjectIndex]
                                .assigned_hours - hoursLoad,
                    });

                    //Limpiamos el contador
                    hoursLoad = 0;
                });
                console.log(this.gridProjectInfo);
            },
            deep: true,
        },
        /**
         * Watcher que se encarga de capturar los cambios en el selector de horas administrativas
         */
        inputAdminSelect: {
            handler(newMultiSelect) {
                this.gridAdminInfo = [];

                //Obtenemos los indices
                newMultiSelect.forEach((select) => {
                    //Obtenemos el indice del concepto
                    const getAdminIndex = this.conceptHourArray
                        .map((admin) => {
                            return admin.admin_hours_id;
                        })
                        .indexOf(select);

                    //Cargamos la informacion de la grilla
                    this.gridAdminInfo.push({
                        adminHourId: select, //El Id del concepto asignado
                        description:
                            this.conceptHourArray[getAdminIndex]
                                .concept_description, //Nombre del concepto
                    });
                });
            },
            deep: true,
        },
    },
};

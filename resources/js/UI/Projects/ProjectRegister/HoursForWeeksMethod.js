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
            this.projectAssociatedToCharge.forEach((project) => {});
        },
    },
};

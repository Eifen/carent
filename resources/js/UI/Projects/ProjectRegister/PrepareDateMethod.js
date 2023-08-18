export const preparDateMethod = {
    methods: {
        /**
         * Metodo que configura las opciones para el selector de años
         */
        prepareYear() {
            //Obtenemos el año, mes y semana actual
            const getActualYear = new Date().getFullYear();

            //Procedemos a llenar el select para años
            for (let year = this.yearInitial; year <= getActualYear; year++) {
                //Cargamos
                this.inputYearOptions.push(year);
            }
        },

        /**
         * Metodo que configura las opciones de los meses en funcion del año seleccionado
         * @param {*} yearSelected Año seleccionado
         */
        prepareMonth(yearSelected) {
            //Limpiamos los meses
            this.inputMonthOptions = ["Seleccione un mes"];
            //Obtenemos el mes actual
            const getActualYear = new Date().getFullYear();
            let getActualMonth =
                yearSelected === getActualYear ? new Date().getMonth() : 11;
            //Si el año seleccionado es el inicial, llenara a partir del mes inicial (Julio); caso contrario desde Enero
            const indexMonth =
                yearSelected === this.yearInitial ? this.monthInitial : 0;

            //Si el indice inicial del for es mayor que el mes actual, colocamos el mes actual igual al indice
            if (indexMonth > getActualMonth) getActualMonth = indexMonth;

            //Procedemos a llenar los meses
            for (let month = indexMonth; month <= getActualMonth; month++) {
                //Cargamos
                this.inputMonthOptions.push(this.monthNames[month]);
            }
        },

        /**
         * Metodo que configura las opciones de las semanas en funcion del mes seleccionado
         * @param {*} monthIndex Captura el mes en formato de su indice (Ejemplo: Enero => monthIndex = 0)
         */
        prepareWeek(monthIndex) {
            const getActualMonth = new Date().getMonth();
            let limitWeek = []; //Almacena el array por semanas
            let lastDay = new Date(
                this.inputYearOptions[this.inputYearSelect],
                monthIndex + 1,
                0
            ); //Almacena el ultimo dia del mes
            let startDate = new Date(
                this.inputYearOptions[this.inputYearSelect],
                monthIndex,
                1
            ); //Intervalos inicial
            let endDate = new Date(
                this.inputYearOptions[this.inputYearSelect],
                monthIndex,
                lastDay.getDate()
            ); //Intervalo final

            //Comparamos si el mes actual coincide con el mes capturado. Caso correcto, procedemos a sacar el día de la semana
            if (getActualMonth === monthIndex) {
                //Calculamos la semana en funcion del año y el mes
                //Configuramos el dia actual
                lastDay = new Date();
                endDate = new Date(
                    this.inputYearOptions[this.inputYearSelect],
                    getActualMonth,
                    lastDay.getDate()
                );
                //Devolvemos el array y lo pasamos a la carga de semanas
                limitWeek = this.getWeeksFromDateRange(startDate, endDate);
            } else {
                //Caso contrario, calculamos las semanas en funcion de algo general, del primero del mes al ultimo
                limitWeek = this.getWeeksFromDateRange(startDate, endDate);
            }

            //Recorremos el array y asignamos la semana
            limitWeek.forEach((week) => {
                this.inputWeekOptions.push(week);
            });
        },
    },
};

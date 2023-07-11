export const weeksFromDateMethod = {
    methods: {
        /**
         * Metodo que itera sobre un intervalo de fecha y devuelve su cantidad de semanadas
         * @param {*} startDate Fecha de inicio del intervalo
         * @param {*} endDate Fecha final del intervalo
         * @returns Array con la informacion por semana de sus rangos de fecha
         */
        getWeeksFromDateRange(startDate, endDate) {
            const weeks = [];

            // Obtener el primer día del mes de la fecha de inicio
            const firstDayOfMonth = new Date(
                startDate.getFullYear(),
                startDate.getMonth(),
                1
            );

            // Calcular la fecha del primer dia del mes
            let firstDayOfWeek = new Date(firstDayOfMonth);

            // Iterar sobre las semanas
            let weekNumber = 1;
            while (firstDayOfWeek <= endDate) {
                // Calcular la fecha del final de semana
                let lastDayOfWeek = new Date(firstDayOfWeek);

                //Nos movemos unicamente hasta llegar al domingo
                if (lastDayOfWeek.getDay() != 0) {
                    //Desplaza el dia en caso de que el mes no empiece el domingo
                    lastDayOfWeek.setDate(
                        lastDayOfWeek.getDate() + (7 - lastDayOfWeek.getDay())
                    );
                }

                //Si la fecha coincide con el fin de mes, se detiene ahi el ciclo
                if (lastDayOfWeek > endDate) {
                    lastDayOfWeek = endDate;
                }

                // Agregar el intervalo de fechas a la semana correspondiente
                const convertStartDate = new Date(firstDayOfWeek);
                const convertEndDate = new Date(lastDayOfWeek);

                //Agrupamos el dia, mes y ano
                const initYear = convertStartDate.getFullYear();
                const endYear = convertEndDate.getFullYear();
                //Dia
                const initDay = convertStartDate
                    .getDate()
                    .toString()
                    .padStart(2, "0"); //Si es menor a 10 colocara un 0
                const endDay = convertEndDate
                    .getDate()
                    .toString()
                    .padStart(2, "0"); //Si es menor a 10 colocara un 0 como prefijo
                //Mes
                const initMonth = (convertStartDate.getMonth() + 1)
                    .toString()
                    .padStart(2, "0"); //Mes inicial
                const endMonth = (convertEndDate.getMonth() + 1)
                    .toString()
                    .padStart(2, "0"); //Mes Final

                //Lo asociamos al array de objetos a devolver
                weeks.push({
                    userId: this.projectAssociatedToCharge[0].user_id,
                    initDateRange: `${initYear}-${initMonth}-${initDay}`,
                    finishDateRange: `${endYear}-${endMonth}-${endDay}`,
                    //Modificamos el mensaje para el select de semana
                    message: `Semana del ${convertStartDate.getDate()} de ${
                        this.monthNames[convertStartDate.getMonth()]
                    }`,
                });

                // Mover la fecha a la siguiente semana
                const intervalDate =
                    firstDayOfWeek.getDay() === 0 ? 7 : firstDayOfWeek.getDay();
                //Si es domingo resta 7, caso contrario resta el dia

                firstDayOfWeek.setDate(
                    firstDayOfWeek.getDate() + (7 - intervalDate) + 1
                );

                // Incrementar el número de semana
                weekNumber++;
            }
            return weeks;
        },
    },
};
